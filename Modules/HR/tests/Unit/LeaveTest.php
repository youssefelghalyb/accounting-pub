<?php

namespace Modules\HR\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HR\app\Models\Leave;
use Modules\HR\app\Models\LeaveType;
use Modules\HR\app\Models\Employee;
use Modules\HR\app\Models\Deduction;
use Carbon\Carbon;

class LeaveTest extends TestCase
{
    use RefreshDatabase;

    protected Employee $employee;
    protected Employee $approver;
    protected LeaveType $leaveType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');

        $this->employee = Employee::factory()->create();
        $this->approver = Employee::factory()->create();
        $this->leaveType = LeaveType::create([
            'name' => 'Annual Leave',
            'max_days_per_year' => 21,
            'is_paid' => true,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_can_create_a_leave()
    {
        $leave = Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-05',
            'total_days' => 5,
            'reason' => 'Family vacation',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('leaves', [
            'employee_id' => $this->employee->id,
            'status' => 'pending',
        ]);

        $this->assertEquals(5, $leave->total_days);
        $this->assertEquals('pending', $leave->status);
    }

    /** @test */
    public function it_belongs_to_an_employee()
    {
        $leave = Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-05',
            'total_days' => 5,
            'status' => 'pending',
        ]);

        $this->assertInstanceOf(Employee::class, $leave->employee);
        $this->assertEquals($this->employee->id, $leave->employee->id);
    }

    /** @test */
    public function it_belongs_to_a_leave_type()
    {
        $leave = Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-05',
            'total_days' => 5,
            'status' => 'pending',
        ]);

        $this->assertInstanceOf(LeaveType::class, $leave->leaveType);
        $this->assertEquals('Annual Leave', $leave->leaveType->name);
    }

    /** @test */
    public function it_belongs_to_an_approver()
    {
        $leave = Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-05',
            'total_days' => 5,
            'status' => 'approved',
            'approved_by' => $this->approver->id,
            'approved_at' => now(),
        ]);

        $this->assertInstanceOf(Employee::class, $leave->approvedBy);
        $this->assertEquals($this->approver->id, $leave->approvedBy->id);
    }

    /** @test */
    public function it_has_one_deduction()
    {
        $unpaidLeaveType = LeaveType::create([
            'name' => 'Unpaid Leave',
            'is_paid' => false,
            'is_active' => true,
        ]);

        $leave = Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $unpaidLeaveType->id,
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-05',
            'total_days' => 5,
            'status' => 'approved',
        ]);

        $deduction = Deduction::create([
            'employee_id' => $this->employee->id,
            'leave_id' => $leave->id,
            'type' => 'unpaid_leave',
            'amount' => 1000.00,
            'deduction_date' => '2025-12-01',
            'reason' => 'Unpaid leave deduction',
        ]);

        $this->assertInstanceOf(Deduction::class, $leave->deduction);
        $this->assertEquals($deduction->id, $leave->deduction->id);
    }

    /** @test */
    public function it_calculates_total_days()
    {
        $leave = new Leave([
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-05',
        ]);

        $totalDays = $leave->calculateTotalDays();

        $this->assertEquals(5, $totalDays);
    }

    /** @test */
    public function it_can_be_approved()
    {
        $leave = Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-05',
            'total_days' => 5,
            'status' => 'pending',
        ]);

        $result = $leave->approve($this->approver->id);

        $this->assertTrue($result);
        $this->assertEquals('approved', $leave->status);
        $this->assertEquals($this->approver->id, $leave->approved_by);
        $this->assertNotNull($leave->approved_at);
    }

    /** @test */
    public function it_can_be_rejected()
    {
        $leave = Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-05',
            'total_days' => 5,
            'status' => 'pending',
        ]);

        $result = $leave->reject($this->approver->id, 'Insufficient staff during this period');

        $this->assertTrue($result);
        $this->assertEquals('rejected', $leave->status);
        $this->assertEquals('Insufficient staff during this period', $leave->rejection_reason);
    }

    /** @test */
    public function it_creates_deduction_when_unpaid_leave_is_approved()
    {
        $unpaidLeaveType = LeaveType::create([
            'name' => 'Unpaid Leave',
            'is_paid' => false,
            'is_active' => true,
        ]);

        $leave = Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $unpaidLeaveType->id,
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-05',
            'total_days' => 5,
            'status' => 'pending',
        ]);

        $leave->approve($this->approver->id);

        $this->assertDatabaseHas('deductions', [
            'employee_id' => $this->employee->id,
            'leave_id' => $leave->id,
            'type' => 'unpaid_leave',
        ]);

        $this->assertTrue($leave->deduction_applied);
    }

    /** @test */
    public function it_does_not_create_deduction_for_paid_leave()
    {
        $leave = Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id, // paid leave
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-05',
            'total_days' => 5,
            'status' => 'pending',
        ]);

        $leave->approve($this->approver->id);

        $this->assertDatabaseMissing('deductions', [
            'employee_id' => $this->employee->id,
            'leave_id' => $leave->id,
        ]);

        $this->assertFalse($leave->deduction_applied);
    }

    /** @test */
    public function scope_pending_returns_only_pending_leaves()
    {
        Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-05',
            'total_days' => 5,
            'status' => 'pending',
        ]);

        Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2025-12-10',
            'end_date' => '2025-12-12',
            'total_days' => 3,
            'status' => 'approved',
        ]);

        Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2025-12-20',
            'end_date' => '2025-12-22',
            'total_days' => 3,
            'status' => 'pending',
        ]);

        $pendingLeaves = Leave::pending()->get();

        $this->assertCount(2, $pendingLeaves);
        $this->assertTrue($pendingLeaves->every(fn($leave) => $leave->status === 'pending'));
    }

    /** @test */
    public function scope_approved_returns_only_approved_leaves()
    {
        Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-05',
            'total_days' => 5,
            'status' => 'pending',
        ]);

        Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2025-12-10',
            'end_date' => '2025-12-12',
            'total_days' => 3,
            'status' => 'approved',
        ]);

        Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2025-12-15',
            'end_date' => '2025-12-17',
            'total_days' => 3,
            'status' => 'approved',
        ]);

        $approvedLeaves = Leave::approved()->get();

        $this->assertCount(2, $approvedLeaves);
        $this->assertTrue($approvedLeaves->every(fn($leave) => $leave->status === 'approved'));
    }

    /** @test */
    public function scope_rejected_returns_only_rejected_leaves()
    {
        Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-05',
            'total_days' => 5,
            'status' => 'rejected',
        ]);

        Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2025-12-10',
            'end_date' => '2025-12-12',
            'total_days' => 3,
            'status' => 'approved',
        ]);

        $rejectedLeaves = Leave::rejected()->get();

        $this->assertCount(1, $rejectedLeaves);
        $this->assertEquals('rejected', $rejectedLeaves->first()->status);
    }

    /** @test */
    public function scope_for_year_filters_leaves_by_year()
    {
        Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-05',
            'total_days' => 5,
            'status' => 'approved',
        ]);

        Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2024-06-01',
            'end_date' => '2024-06-05',
            'total_days' => 5,
            'status' => 'approved',
        ]);

        Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2025-03-01',
            'end_date' => '2025-03-03',
            'total_days' => 3,
            'status' => 'approved',
        ]);

        $leaves2025 = Leave::forYear(2025)->get();

        $this->assertCount(2, $leaves2025);
    }

    /** @test */
    public function scope_for_month_filters_leaves_by_month()
    {
        Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-05',
            'total_days' => 5,
            'status' => 'approved',
        ]);

        Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2025-11-01',
            'end_date' => '2025-11-05',
            'total_days' => 5,
            'status' => 'approved',
        ]);

        Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2025-12-15',
            'end_date' => '2025-12-17',
            'total_days' => 3,
            'status' => 'approved',
        ]);

        $leavesDecember = Leave::forMonth(2025, 12)->get();

        $this->assertCount(2, $leavesDecember);
    }

    /** @test */
    public function it_checks_if_leave_can_be_approved()
    {
        $leave = Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-05',
            'total_days' => 5,
            'status' => 'pending',
        ]);

        $this->assertTrue($leave->canBeApproved());

        $leave->update(['status' => 'approved']);

        $this->assertFalse($leave->canBeApproved());
    }

    /** @test */
    public function it_checks_if_leave_is_active()
    {
        // Future leave
        $futureLeave = Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(15),
            'total_days' => 6,
            'status' => 'approved',
        ]);

        $this->assertFalse($futureLeave->isActive());

        // Past leave
        $pastLeave = Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->subDays(5),
            'total_days' => 6,
            'status' => 'approved',
        ]);

        $this->assertFalse($pastLeave->isActive());

        // Current leave
        $currentLeave = Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => Carbon::now()->subDays(2),
            'end_date' => Carbon::now()->addDays(3),
            'total_days' => 6,
            'status' => 'approved',
        ]);

        $this->assertTrue($currentLeave->isActive());
    }

    /** @test */
    public function it_casts_dates_to_carbon()
    {
        $leave = Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-05',
            'total_days' => 5,
            'status' => 'pending',
        ]);

        $this->assertInstanceOf(Carbon::class, $leave->start_date);
        $this->assertInstanceOf(Carbon::class, $leave->end_date);
    }

    /** @test */
    public function it_casts_deduction_applied_to_boolean()
    {
        $leave = Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-05',
            'total_days' => 5,
            'status' => 'pending',
            'deduction_applied' => false,
        ]);

        $this->assertIsBool($leave->deduction_applied);
        $this->assertFalse($leave->deduction_applied);
    }
}
