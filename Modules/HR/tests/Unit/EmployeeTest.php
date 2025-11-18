<?php

namespace Modules\HR\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HR\app\Models\Employee;
use Modules\HR\app\Models\Department;
use Modules\HR\app\Models\Leave;
use Modules\HR\app\Models\Deduction;
use Modules\HR\app\Models\Advance;
use Modules\HR\app\Models\LeaveType;
use Carbon\Carbon;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /** @test */
    public function it_can_create_an_employee()
    {
        $employee = Employee::create([
            'employee_code' => 'EMP001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+1234567890',
            'hire_date' => '2025-01-15',
            'salary' => 5000.00,
            'position' => 'Developer',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('employees', [
            'employee_code' => 'EMP001',
            'email' => 'john.doe@example.com',
        ]);

        $this->assertEquals('John', $employee->first_name);
        $this->assertEquals('Doe', $employee->last_name);
        $this->assertEquals(5000.00, $employee->salary);
    }

    /** @test */
    public function employee_code_must_be_unique()
    {
        Employee::create([
            'employee_code' => 'EMP001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'hire_date' => '2025-01-15',
            'salary' => 5000.00,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Employee::create([
            'employee_code' => 'EMP001',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'hire_date' => '2025-01-15',
            'salary' => 6000.00,
        ]);
    }

    /** @test */
    public function email_must_be_unique()
    {
        Employee::create([
            'employee_code' => 'EMP001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'hire_date' => '2025-01-15',
            'salary' => 5000.00,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Employee::create([
            'employee_code' => 'EMP002',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'john@example.com',
            'hire_date' => '2025-01-15',
            'salary' => 6000.00,
        ]);
    }

    /** @test */
    public function it_belongs_to_a_department()
    {
        $department = Department::create([
            'name' => 'Engineering',
            'description' => 'Engineering Department',
        ]);

        $employee = Employee::create([
            'employee_code' => 'EMP001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'hire_date' => '2025-01-15',
            'salary' => 5000.00,
            'department_id' => $department->id,
        ]);

        $this->assertInstanceOf(Department::class, $employee->department);
        $this->assertEquals('Engineering', $employee->department->name);
    }

    /** @test */
    public function it_has_many_leaves()
    {
        $employee = Employee::factory()->create();
        $leaveType = LeaveType::create([
            'name' => 'Annual Leave',
            'is_paid' => true,
            'is_active' => true,
        ]);

        Leave::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-05',
            'total_days' => 5,
            'status' => 'pending',
        ]);

        Leave::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2025-12-10',
            'end_date' => '2025-12-12',
            'total_days' => 3,
            'status' => 'approved',
        ]);

        $this->assertCount(2, $employee->leaves);
    }

    /** @test */
    public function it_has_many_deductions()
    {
        $employee = Employee::factory()->create([
            'salary' => 6000.00,
        ]);

        Deduction::create([
            'employee_id' => $employee->id,
            'type' => 'amount',
            'amount' => 100.00,
            'deduction_date' => '2025-11-15',
            'reason' => 'Test deduction',
        ]);

        Deduction::create([
            'employee_id' => $employee->id,
            'type' => 'days',
            'days' => 2,
            'amount' => 400.00,
            'deduction_date' => '2025-11-20',
            'reason' => 'Absence',
        ]);

        $this->assertCount(2, $employee->deductions);
    }

    /** @test */
    public function it_has_many_advances()
    {
        $employee = Employee::factory()->create();

        Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $employee->id,
            'amount' => 1000.00,
            'issue_date' => '2025-11-01',
            'type' => 'salary_advance',
            'status' => 'pending',
        ]);

        Advance::create([
            'advance_code' => 'ADV-2025-002',
            'employee_id' => $employee->id,
            'amount' => 500.00,
            'issue_date' => '2025-11-15',
            'type' => 'cash',
            'status' => 'pending',
        ]);

        $this->assertCount(2, $employee->advances);
    }

    /** @test */
    public function it_returns_full_name_accessor()
    {
        $employee = Employee::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $employee->full_name);
    }

    /** @test */
    public function it_calculates_daily_rate()
    {
        $employee = Employee::factory()->create([
            'salary' => 6000.00,
        ]);

        $this->assertEquals(200.00, $employee->daily_rate);
    }

    /** @test */
    public function it_calculates_years_of_service()
    {
        $employee = Employee::factory()->create([
            'hire_date' => Carbon::now()->subYears(3)->subMonths(6),
        ]);

        $this->assertEquals(3, $employee->years_of_service);
    }

    /** @test */
    public function it_gets_pending_leaves()
    {
        $employee = Employee::factory()->create();
        $leaveType = LeaveType::create([
            'name' => 'Annual Leave',
            'is_paid' => true,
            'is_active' => true,
        ]);

        Leave::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-05',
            'total_days' => 5,
            'status' => 'pending',
        ]);

        Leave::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2025-12-10',
            'end_date' => '2025-12-12',
            'total_days' => 3,
            'status' => 'approved',
        ]);

        Leave::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2025-12-20',
            'end_date' => '2025-12-22',
            'total_days' => 3,
            'status' => 'pending',
        ]);

        $pendingLeaves = $employee->pending_leaves;

        $this->assertCount(2, $pendingLeaves);
        $this->assertTrue($pendingLeaves->every(fn($leave) => $leave->status === 'pending'));
    }

    /** @test */
    public function it_calculates_total_deductions_for_month()
    {
        $employee = Employee::factory()->create([
            'salary' => 6000.00,
        ]);

        Deduction::create([
            'employee_id' => $employee->id,
            'type' => 'amount',
            'amount' => 100.00,
            'deduction_date' => '2025-11-15',
            'reason' => 'Deduction 1',
        ]);

        Deduction::create([
            'employee_id' => $employee->id,
            'type' => 'amount',
            'amount' => 150.00,
            'deduction_date' => '2025-11-20',
            'reason' => 'Deduction 2',
        ]);

        // Deduction from different month (should not be counted)
        Deduction::create([
            'employee_id' => $employee->id,
            'type' => 'amount',
            'amount' => 200.00,
            'deduction_date' => '2025-10-15',
            'reason' => 'October deduction',
        ]);

        $month = Carbon::parse('2025-11-01');
        $totalDeductions = $employee->getTotalDeductions($month);

        $this->assertEquals(250.00, $totalDeductions);
    }

    /** @test */
    public function it_calculates_net_salary()
    {
        $employee = Employee::factory()->create([
            'salary' => 6000.00,
        ]);

        Deduction::create([
            'employee_id' => $employee->id,
            'type' => 'amount',
            'amount' => 500.00,
            'deduction_date' => '2025-11-15',
            'reason' => 'Deduction',
        ]);

        $month = Carbon::parse('2025-11-01');
        $netSalary = $employee->calculateNetSalary($month);

        $this->assertEquals(5500.00, $netSalary);
    }

    /** @test */
    public function it_gets_leave_balance_for_leave_type()
    {
        $employee = Employee::factory()->create();
        $leaveType = LeaveType::create([
            'name' => 'Annual Leave',
            'max_days_per_year' => 21,
            'is_paid' => true,
            'is_active' => true,
        ]);

        // Create approved leaves
        Leave::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-05',
            'total_days' => 5,
            'status' => 'approved',
        ]);

        Leave::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2025-08-01',
            'end_date' => '2025-08-03',
            'total_days' => 3,
            'status' => 'approved',
        ]);

        // Pending leave (should not be counted in balance calculation)
        Leave::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-05',
            'total_days' => 5,
            'status' => 'pending',
        ]);

        $balance = $employee->getLeaveBalance($leaveType->id, 2025);

        $this->assertEquals(13, $balance); // 21 - 8 = 13
    }

    /** @test */
    public function it_casts_hire_date_to_carbon()
    {
        $employee = Employee::factory()->create([
            'hire_date' => '2025-01-15',
        ]);

        $this->assertInstanceOf(Carbon::class, $employee->hire_date);
    }

    /** @test */
    public function it_casts_salary_to_decimal()
    {
        $employee = Employee::factory()->create([
            'salary' => 5000.50,
        ]);

        $this->assertEquals('5000.50', $employee->salary);
    }

    /** @test */
    public function deleting_employee_cascades_to_related_records()
    {
        $employee = Employee::factory()->create();
        $leaveType = LeaveType::create([
            'name' => 'Annual Leave',
            'is_paid' => true,
            'is_active' => true,
        ]);

        Leave::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-05',
            'total_days' => 5,
            'status' => 'pending',
        ]);

        Deduction::create([
            'employee_id' => $employee->id,
            'type' => 'amount',
            'amount' => 100.00,
            'deduction_date' => '2025-11-15',
            'reason' => 'Test',
        ]);

        $employeeId = $employee->id;
        $employee->delete();

        $this->assertDatabaseMissing('employees', ['id' => $employeeId]);
        $this->assertDatabaseMissing('leaves', ['employee_id' => $employeeId]);
        $this->assertDatabaseMissing('deductions', ['employee_id' => $employeeId]);
    }
}
