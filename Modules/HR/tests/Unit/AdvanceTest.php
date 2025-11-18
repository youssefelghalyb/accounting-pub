<?php

namespace Modules\HR\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HR\app\Models\Advance;
use Modules\HR\app\Models\AdvanceSettlement;
use Modules\HR\app\Models\Employee;
use Modules\HR\app\Models\Deduction;
use Carbon\Carbon;

class AdvanceTest extends TestCase
{
    use RefreshDatabase;

    protected Employee $employee;
    protected Employee $issuer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');

        $this->employee = Employee::factory()->create();
        $this->issuer = Employee::factory()->create();
    }

    /** @test */
    public function it_can_create_an_advance()
    {
        $advance = Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
            'issue_date' => '2025-11-01',
            'expected_settlement_date' => '2025-11-30',
            'type' => 'salary_advance',
            'status' => 'pending',
            'purpose' => 'Emergency medical expenses',
            'issued_by' => $this->issuer->id,
        ]);

        $this->assertDatabaseHas('employee_advances', [
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
        ]);

        $this->assertEquals('salary_advance', $advance->type);
        $this->assertEquals('pending', $advance->status);
    }

    /** @test */
    public function advance_code_must_be_unique()
    {
        Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
            'issue_date' => '2025-11-01',
            'type' => 'cash',
            'status' => 'pending',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 500.00,
            'issue_date' => '2025-11-02',
            'type' => 'cash',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function it_belongs_to_an_employee()
    {
        $advance = Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
            'issue_date' => '2025-11-01',
            'type' => 'cash',
            'status' => 'pending',
        ]);

        $this->assertInstanceOf(Employee::class, $advance->employee);
        $this->assertEquals($this->employee->id, $advance->employee->id);
    }

    /** @test */
    public function it_belongs_to_an_issuer()
    {
        $advance = Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
            'issue_date' => '2025-11-01',
            'type' => 'cash',
            'status' => 'pending',
            'issued_by' => $this->issuer->id,
        ]);

        $this->assertInstanceOf(Employee::class, $advance->issuedBy);
        $this->assertEquals($this->issuer->id, $advance->issuedBy->id);
    }

    /** @test */
    public function it_has_many_settlements()
    {
        $advance = Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
            'issue_date' => '2025-11-01',
            'type' => 'cash',
            'status' => 'pending',
        ]);

        AdvanceSettlement::create([
            'settlement_code' => 'SET-2025-001',
            'employee_id' => $this->employee->id,
            'advance_id' => $advance->id,
            'cash_returned' => 300.00,
            'amount_spent' => 200.00,
            'settlement_date' => '2025-11-15',
        ]);

        AdvanceSettlement::create([
            'settlement_code' => 'SET-2025-002',
            'employee_id' => $this->employee->id,
            'advance_id' => $advance->id,
            'cash_returned' => 500.00,
            'settlement_date' => '2025-11-20',
        ]);

        $this->assertCount(2, $advance->settlements);
    }

    /** @test */
    public function it_has_many_deductions()
    {
        $advance = Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
            'issue_date' => '2025-11-01',
            'type' => 'cash',
            'status' => 'settled_via_deduction',
        ]);

        Deduction::create([
            'employee_id' => $this->employee->id,
            'advance_id' => $advance->id,
            'type' => 'advance_recovery',
            'amount' => 1000.00,
            'deduction_date' => '2025-11-30',
            'reason' => 'Advance recovery',
        ]);

        $this->assertCount(1, $advance->deductions);
    }

    /** @test */
    public function it_calculates_total_cash_returned()
    {
        $advance = Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
            'issue_date' => '2025-11-01',
            'type' => 'cash',
            'status' => 'pending',
        ]);

        AdvanceSettlement::create([
            'settlement_code' => 'SET-2025-001',
            'employee_id' => $this->employee->id,
            'advance_id' => $advance->id,
            'cash_returned' => 300.00,
            'settlement_date' => '2025-11-15',
        ]);

        AdvanceSettlement::create([
            'settlement_code' => 'SET-2025-002',
            'employee_id' => $this->employee->id,
            'advance_id' => $advance->id,
            'cash_returned' => 200.00,
            'settlement_date' => '2025-11-20',
        ]);

        $this->assertEquals(500.00, $advance->total_cash_returned);
    }

    /** @test */
    public function it_calculates_total_spent()
    {
        $advance = Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
            'issue_date' => '2025-11-01',
            'type' => 'cash',
            'status' => 'pending',
        ]);

        AdvanceSettlement::create([
            'settlement_code' => 'SET-2025-001',
            'employee_id' => $this->employee->id,
            'advance_id' => $advance->id,
            'amount_spent' => 400.00,
            'settlement_date' => '2025-11-15',
        ]);

        AdvanceSettlement::create([
            'settlement_code' => 'SET-2025-002',
            'employee_id' => $this->employee->id,
            'advance_id' => $advance->id,
            'amount_spent' => 300.00,
            'settlement_date' => '2025-11-20',
        ]);

        $this->assertEquals(700.00, $advance->total_spent);
    }

    /** @test */
    public function it_calculates_total_accounted()
    {
        $advance = Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
            'issue_date' => '2025-11-01',
            'type' => 'cash',
            'status' => 'pending',
        ]);

        AdvanceSettlement::create([
            'settlement_code' => 'SET-2025-001',
            'employee_id' => $this->employee->id,
            'advance_id' => $advance->id,
            'cash_returned' => 300.00,
            'amount_spent' => 400.00,
            'settlement_date' => '2025-11-15',
        ]);

        AdvanceSettlement::create([
            'settlement_code' => 'SET-2025-002',
            'employee_id' => $this->employee->id,
            'advance_id' => $advance->id,
            'cash_returned' => 200.00,
            'amount_spent' => 100.00,
            'settlement_date' => '2025-11-20',
        ]);

        // total_accounted = cash_returned + amount_spent = 500 + 500 = 1000
        $this->assertEquals(1000.00, $advance->total_accounted);
    }

    /** @test */
    public function it_calculates_outstanding_balance()
    {
        $advance = Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
            'issue_date' => '2025-11-01',
            'type' => 'cash',
            'status' => 'pending',
        ]);

        AdvanceSettlement::create([
            'settlement_code' => 'SET-2025-001',
            'employee_id' => $this->employee->id,
            'advance_id' => $advance->id,
            'cash_returned' => 300.00,
            'amount_spent' => 200.00,
            'settlement_date' => '2025-11-15',
        ]);

        // outstanding = 1000 - (300 + 200) = 500
        $this->assertEquals(500.00, $advance->outstanding_balance);
    }

    /** @test */
    public function it_calculates_overpayment_amount()
    {
        $advance = Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
            'issue_date' => '2025-11-01',
            'type' => 'cash',
            'status' => 'pending',
        ]);

        AdvanceSettlement::create([
            'settlement_code' => 'SET-2025-001',
            'employee_id' => $this->employee->id,
            'advance_id' => $advance->id,
            'cash_returned' => 600.00,
            'amount_spent' => 600.00,
            'settlement_date' => '2025-11-15',
        ]);

        // overpayment = abs(outstanding_balance) when negative
        // outstanding = 1000 - 1200 = -200
        // overpayment = 200
        $this->assertEquals(200.00, $advance->overpayment_amount);
    }

    /** @test */
    public function it_calculates_overdue_days()
    {
        $advance = Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
            'issue_date' => '2025-11-01',
            'expected_settlement_date' => Carbon::now()->subDays(10),
            'type' => 'cash',
            'status' => 'pending',
        ]);

        $this->assertEquals(10, $advance->overdue_days);
    }

    /** @test */
    public function overdue_days_is_zero_when_not_overdue()
    {
        $advance = Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
            'issue_date' => '2025-11-01',
            'expected_settlement_date' => Carbon::now()->addDays(10),
            'type' => 'cash',
            'status' => 'pending',
        ]);

        $this->assertEquals(0, $advance->overdue_days);
    }

    /** @test */
    public function it_checks_if_advance_is_overdue()
    {
        $overdueAdvance = Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
            'issue_date' => '2025-11-01',
            'expected_settlement_date' => Carbon::now()->subDays(5),
            'type' => 'cash',
            'status' => 'pending',
        ]);

        $this->assertTrue($overdueAdvance->isOverdue());

        $notOverdueAdvance = Advance::create([
            'advance_code' => 'ADV-2025-002',
            'employee_id' => $this->employee->id,
            'amount' => 500.00,
            'issue_date' => '2025-11-01',
            'expected_settlement_date' => Carbon::now()->addDays(5),
            'type' => 'cash',
            'status' => 'pending',
        ]);

        $this->assertFalse($notOverdueAdvance->isOverdue());
    }

    /** @test */
    public function it_checks_if_advance_has_deduction()
    {
        $advance = Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
            'issue_date' => '2025-11-01',
            'type' => 'cash',
            'status' => 'pending',
        ]);

        $this->assertFalse($advance->hasDeduction());

        Deduction::create([
            'employee_id' => $this->employee->id,
            'advance_id' => $advance->id,
            'type' => 'advance_recovery',
            'amount' => 1000.00,
            'deduction_date' => '2025-11-30',
            'reason' => 'Advance recovery',
        ]);

        $advance->refresh();

        $this->assertTrue($advance->hasDeduction());
    }

    /** @test */
    public function it_can_convert_to_deduction()
    {
        $advance = Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
            'issue_date' => '2025-11-01',
            'type' => 'salary_advance',
            'status' => 'pending',
        ]);

        $deduction = $advance->convertToDeduction();

        $this->assertInstanceOf(Deduction::class, $deduction);
        $this->assertEquals('advance_recovery', $deduction->type);
        $this->assertEquals(1000.00, $deduction->amount);
        $this->assertEquals($advance->id, $deduction->advance_id);

        $advance->refresh();

        $this->assertEquals('settled_via_deduction', $advance->status);
        $this->assertNotNull($advance->actual_settlement_date);
    }

    /** @test */
    public function it_updates_status_based_on_settlements()
    {
        $advance = Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
            'issue_date' => '2025-11-01',
            'type' => 'cash',
            'status' => 'pending',
        ]);

        $this->assertEquals('pending', $advance->status);

        // Partial settlement
        AdvanceSettlement::create([
            'settlement_code' => 'SET-2025-001',
            'employee_id' => $this->employee->id,
            'advance_id' => $advance->id,
            'cash_returned' => 300.00,
            'settlement_date' => '2025-11-15',
        ]);

        $advance->updateStatus();

        $this->assertEquals('partial_settlement', $advance->status);

        // Full settlement
        AdvanceSettlement::create([
            'settlement_code' => 'SET-2025-002',
            'employee_id' => $this->employee->id,
            'advance_id' => $advance->id,
            'cash_returned' => 400.00,
            'amount_spent' => 300.00,
            'settlement_date' => '2025-11-20',
        ]);

        $advance->updateStatus();

        $this->assertEquals('settled', $advance->status);
    }

    /** @test */
    public function scope_pending_returns_only_pending_advances()
    {
        Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
            'issue_date' => '2025-11-01',
            'type' => 'cash',
            'status' => 'pending',
        ]);

        Advance::create([
            'advance_code' => 'ADV-2025-002',
            'employee_id' => $this->employee->id,
            'amount' => 500.00,
            'issue_date' => '2025-11-02',
            'type' => 'cash',
            'status' => 'settled',
        ]);

        Advance::create([
            'advance_code' => 'ADV-2025-003',
            'employee_id' => $this->employee->id,
            'amount' => 750.00,
            'issue_date' => '2025-11-03',
            'type' => 'cash',
            'status' => 'pending',
        ]);

        $pendingAdvances = Advance::pending()->get();

        $this->assertCount(2, $pendingAdvances);
        $this->assertTrue($pendingAdvances->every(fn($adv) => $adv->status === 'pending'));
    }

    /** @test */
    public function scope_overdue_returns_only_overdue_advances()
    {
        Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
            'issue_date' => '2025-11-01',
            'expected_settlement_date' => Carbon::now()->subDays(5),
            'type' => 'cash',
            'status' => 'pending',
        ]);

        Advance::create([
            'advance_code' => 'ADV-2025-002',
            'employee_id' => $this->employee->id,
            'amount' => 500.00,
            'issue_date' => '2025-11-02',
            'expected_settlement_date' => Carbon::now()->addDays(5),
            'type' => 'cash',
            'status' => 'pending',
        ]);

        $overdueAdvances = Advance::overdue()->get();

        $this->assertCount(1, $overdueAdvances);
    }

    /** @test */
    public function it_casts_dates_to_carbon()
    {
        $advance = Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.00,
            'issue_date' => '2025-11-01',
            'expected_settlement_date' => '2025-11-30',
            'type' => 'cash',
            'status' => 'pending',
        ]);

        $this->assertInstanceOf(Carbon::class, $advance->issue_date);
        $this->assertInstanceOf(Carbon::class, $advance->expected_settlement_date);
    }

    /** @test */
    public function it_casts_amount_to_decimal()
    {
        $advance = Advance::create([
            'advance_code' => 'ADV-2025-001',
            'employee_id' => $this->employee->id,
            'amount' => 1000.50,
            'issue_date' => '2025-11-01',
            'type' => 'cash',
            'status' => 'pending',
        ]);

        $this->assertEquals('1000.50', $advance->amount);
    }
}
