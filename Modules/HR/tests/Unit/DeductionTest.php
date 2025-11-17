<?php

namespace Modules\HR\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HR\app\Models\Deduction;
use Modules\HR\app\Models\Employee;
use Modules\HR\app\Models\Leave;
use Modules\HR\app\Models\LeaveType;
use Modules\HR\app\Models\Advance;
use Carbon\Carbon;

class DeductionTest extends TestCase
{
    use RefreshDatabase;

    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');

        $this->employee = Employee::factory()->create([
            'salary' => 6000.00,
        ]);
    }

    /** @test */
    public function it_can_create_a_deduction()
    {
        $deduction = Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'amount',
            'amount' => 100.00,
            'deduction_date' => '2025-11-15',
            'reason' => 'Equipment damage',
        ]);

        $this->assertDatabaseHas('deductions', [
            'employee_id' => $this->employee->id,
            'amount' => 100.00,
        ]);

        $this->assertEquals('amount', $deduction->type);
        $this->assertEquals(100.00, $deduction->amount);
    }

    /** @test */
    public function it_belongs_to_an_employee()
    {
        $deduction = Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'amount',
            'amount' => 100.00,
            'deduction_date' => '2025-11-15',
            'reason' => 'Test',
        ]);

        $this->assertInstanceOf(Employee::class, $deduction->employee);
        $this->assertEquals($this->employee->id, $deduction->employee->id);
    }

    /** @test */
    public function it_belongs_to_a_leave()
    {
        $leaveType = LeaveType::create([
            'name' => 'Unpaid Leave',
            'is_paid' => false,
            'is_active' => true,
        ]);

        $leave = Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $leaveType->id,
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

        $this->assertInstanceOf(Leave::class, $deduction->leave);
        $this->assertEquals($leave->id, $deduction->leave->id);
    }

    /** @test */
    public function it_belongs_to_an_advance()
    {
        $advance = Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
            'issue_date' => '2025-11-01',
            'type' => 'salary_advance',
            'status' => 'settled_via_deduction',
        ]);

        $deduction = Deduction::create([
            'employee_id' => $this->employee->id,
            'advance_id' => $advance->id,
            'type' => 'advance_recovery',
            'amount' => 1000.00,
            'deduction_date' => '2025-11-30',
            'reason' => 'Advance recovery',
        ]);

        $this->assertInstanceOf(Advance::class, $deduction->advance);
        $this->assertEquals($advance->id, $deduction->advance->id);
    }

    /** @test */
    public function it_auto_calculates_amount_from_days()
    {
        // Employee salary is 6000, so daily_rate = 200
        $deduction = Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'days',
            'days' => 3,
            'deduction_date' => '2025-11-15',
            'reason' => 'Absence',
        ]);

        // amount should be auto-calculated as days × daily_rate = 3 × 200 = 600
        $this->assertEquals(600.00, $deduction->amount);
    }

    /** @test */
    public function it_allows_negative_amounts_for_bonuses()
    {
        $deduction = Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'amount',
            'amount' => -500.00,
            'deduction_date' => '2025-11-15',
            'reason' => 'Performance bonus',
        ]);

        $this->assertEquals(-500.00, $deduction->amount);
    }

    /** @test */
    public function it_checks_if_deduction_is_from_leave()
    {
        $leaveType = LeaveType::create([
            'name' => 'Unpaid Leave',
            'is_paid' => false,
            'is_active' => true,
        ]);

        $leave = Leave::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-05',
            'total_days' => 5,
            'status' => 'approved',
        ]);

        $deductionWithLeave = Deduction::create([
            'employee_id' => $this->employee->id,
            'leave_id' => $leave->id,
            'type' => 'unpaid_leave',
            'amount' => 1000.00,
            'deduction_date' => '2025-12-01',
            'reason' => 'Unpaid leave deduction',
        ]);

        $this->assertTrue($deductionWithLeave->isFromLeave());

        $deductionWithoutLeave = Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'amount',
            'amount' => 100.00,
            'deduction_date' => '2025-11-15',
            'reason' => 'Other deduction',
        ]);

        $this->assertFalse($deductionWithoutLeave->isFromLeave());
    }

    /** @test */
    public function it_checks_if_deduction_is_from_advance()
    {
        $advance = Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
            'issue_date' => '2025-11-01',
            'type' => 'salary_advance',
            'status' => 'settled_via_deduction',
        ]);

        $deductionWithAdvance = Deduction::create([
            'employee_id' => $this->employee->id,
            'advance_id' => $advance->id,
            'type' => 'advance_recovery',
            'amount' => 1000.00,
            'deduction_date' => '2025-11-30',
            'reason' => 'Advance recovery',
        ]);

        $this->assertTrue($deductionWithAdvance->isFromAdvance());

        $deductionWithoutAdvance = Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'amount',
            'amount' => 100.00,
            'deduction_date' => '2025-11-15',
            'reason' => 'Other deduction',
        ]);

        $this->assertFalse($deductionWithoutAdvance->isFromAdvance());
    }

    /** @test */
    public function scope_for_employee_filters_by_employee()
    {
        $employee2 = Employee::factory()->create();

        Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'amount',
            'amount' => 100.00,
            'deduction_date' => '2025-11-15',
            'reason' => 'Test 1',
        ]);

        Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'amount',
            'amount' => 150.00,
            'deduction_date' => '2025-11-20',
            'reason' => 'Test 2',
        ]);

        Deduction::create([
            'employee_id' => $employee2->id,
            'type' => 'amount',
            'amount' => 200.00,
            'deduction_date' => '2025-11-25',
            'reason' => 'Test 3',
        ]);

        $deductions = Deduction::forEmployee($this->employee->id)->get();

        $this->assertCount(2, $deductions);
        $this->assertTrue($deductions->every(fn($d) => $d->employee_id === $this->employee->id));
    }

    /** @test */
    public function scope_for_year_filters_by_year()
    {
        Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'amount',
            'amount' => 100.00,
            'deduction_date' => '2025-11-15',
            'reason' => 'Test 1',
        ]);

        Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'amount',
            'amount' => 150.00,
            'deduction_date' => '2024-11-20',
            'reason' => 'Test 2',
        ]);

        Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'amount',
            'amount' => 200.00,
            'deduction_date' => '2025-06-10',
            'reason' => 'Test 3',
        ]);

        $deductions2025 = Deduction::forYear(2025)->get();

        $this->assertCount(2, $deductions2025);
    }

    /** @test */
    public function scope_for_month_filters_by_month()
    {
        Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'amount',
            'amount' => 100.00,
            'deduction_date' => '2025-11-15',
            'reason' => 'Test 1',
        ]);

        Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'amount',
            'amount' => 150.00,
            'deduction_date' => '2025-11-20',
            'reason' => 'Test 2',
        ]);

        Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'amount',
            'amount' => 200.00,
            'deduction_date' => '2025-10-10',
            'reason' => 'Test 3',
        ]);

        $deductionsNovember = Deduction::forMonth(2025, 11)->get();

        $this->assertCount(2, $deductionsNovember);
    }

    /** @test */
    public function scope_by_type_filters_by_type()
    {
        Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'amount',
            'amount' => 100.00,
            'deduction_date' => '2025-11-15',
            'reason' => 'Test 1',
        ]);

        Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'days',
            'days' => 2,
            'amount' => 400.00,
            'deduction_date' => '2025-11-20',
            'reason' => 'Test 2',
        ]);

        Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'amount',
            'amount' => 150.00,
            'deduction_date' => '2025-11-25',
            'reason' => 'Test 3',
        ]);

        $amountDeductions = Deduction::byType('amount')->get();

        $this->assertCount(2, $amountDeductions);
        $this->assertTrue($amountDeductions->every(fn($d) => $d->type === 'amount'));
    }

    /** @test */
    public function scope_date_range_filters_by_date_range()
    {
        Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'amount',
            'amount' => 100.00,
            'deduction_date' => '2025-11-15',
            'reason' => 'Test 1',
        ]);

        Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'amount',
            'amount' => 150.00,
            'deduction_date' => '2025-11-20',
            'reason' => 'Test 2',
        ]);

        Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'amount',
            'amount' => 200.00,
            'deduction_date' => '2025-11-25',
            'reason' => 'Test 3',
        ]);

        $start = Carbon::parse('2025-11-18');
        $end = Carbon::parse('2025-11-30');

        $deductionsInRange = Deduction::dateRange($start, $end)->get();

        $this->assertCount(2, $deductionsInRange);
    }

    /** @test */
    public function it_casts_deduction_date_to_carbon()
    {
        $deduction = Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'amount',
            'amount' => 100.00,
            'deduction_date' => '2025-11-15',
            'reason' => 'Test',
        ]);

        $this->assertInstanceOf(Carbon::class, $deduction->deduction_date);
    }

    /** @test */
    public function it_casts_amount_to_decimal()
    {
        $deduction = Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'amount',
            'amount' => 100.50,
            'deduction_date' => '2025-11-15',
            'reason' => 'Test',
        ]);

        $this->assertEquals('100.50', $deduction->amount);
    }

    /** @test */
    public function it_has_type_name_accessor()
    {
        $deduction = Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'unpaid_leave',
            'amount' => 500.00,
            'deduction_date' => '2025-11-15',
            'reason' => 'Test',
        ]);

        // This would return a human-readable name
        $this->assertNotNull($deduction->type_name);
    }

    /** @test */
    public function it_has_type_color_accessor()
    {
        $deduction = Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'amount',
            'amount' => 100.00,
            'deduction_date' => '2025-11-15',
            'reason' => 'Test',
        ]);

        // This would return a color code for the type
        $this->assertNotNull($deduction->type_color);
    }

    /** @test */
    public function deleting_employee_cascades_to_deductions()
    {
        $deduction = Deduction::create([
            'employee_id' => $this->employee->id,
            'type' => 'amount',
            'amount' => 100.00,
            'deduction_date' => '2025-11-15',
            'reason' => 'Test',
        ]);

        $deductionId = $deduction->id;
        $this->employee->delete();

        $this->assertDatabaseMissing('deductions', ['id' => $deductionId]);
    }
}
