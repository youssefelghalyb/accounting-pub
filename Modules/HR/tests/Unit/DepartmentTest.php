<?php

namespace Modules\HR\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HR\app\Models\Department;
use Modules\HR\app\Models\Employee;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /** @test */
    public function it_can_create_a_department()
    {
        $department = Department::create([
            'name' => 'Engineering',
            'description' => 'Software Engineering Department',
            'color' => '#FF5733',
        ]);

        $this->assertDatabaseHas('departments', [
            'name' => 'Engineering',
            'description' => 'Software Engineering Department',
        ]);

        $this->assertEquals('Engineering', $department->name);
        $this->assertEquals('#FF5733', $department->color);
    }

    /** @test */
    public function it_has_many_employees()
    {
        $department = Department::create([
            'name' => 'Engineering',
        ]);

        Employee::create([
            'employee_code' => 'EMP001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'hire_date' => '2025-01-15',
            'salary' => 5000.00,
            'department_id' => $department->id,
        ]);

        Employee::create([
            'employee_code' => 'EMP002',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'hire_date' => '2025-02-01',
            'salary' => 6000.00,
            'department_id' => $department->id,
        ]);

        $this->assertCount(2, $department->employees);
        $this->assertInstanceOf(Employee::class, $department->employees->first());
    }

    /** @test */
    public function it_returns_total_employees_count()
    {
        $department = Department::create([
            'name' => 'Engineering',
        ]);

        Employee::factory()->count(5)->create([
            'department_id' => $department->id,
        ]);

        $this->assertEquals(5, $department->total_employees);
    }

    /** @test */
    public function it_calculates_total_salary_cost()
    {
        $department = Department::create([
            'name' => 'Engineering',
        ]);

        Employee::create([
            'employee_code' => 'EMP001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'hire_date' => '2025-01-15',
            'salary' => 5000.00,
            'department_id' => $department->id,
        ]);

        Employee::create([
            'employee_code' => 'EMP002',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'hire_date' => '2025-02-01',
            'salary' => 6000.00,
            'department_id' => $department->id,
        ]);

        Employee::create([
            'employee_code' => 'EMP003',
            'first_name' => 'Bob',
            'last_name' => 'Johnson',
            'email' => 'bob@example.com',
            'hire_date' => '2025-03-01',
            'salary' => 4500.00,
            'department_id' => $department->id,
        ]);

        $this->assertEquals(15500.00, $department->total_salary_cost);
    }

    /** @test */
    public function total_employees_returns_zero_when_no_employees()
    {
        $department = Department::create([
            'name' => 'Engineering',
        ]);

        $this->assertEquals(0, $department->total_employees);
    }

    /** @test */
    public function total_salary_cost_returns_zero_when_no_employees()
    {
        $department = Department::create([
            'name' => 'Engineering',
        ]);

        $this->assertEquals(0, $department->total_salary_cost);
    }

    /** @test */
    public function deleting_department_sets_employee_department_id_to_null()
    {
        $department = Department::create([
            'name' => 'Engineering',
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

        $this->assertEquals($department->id, $employee->department_id);

        $department->delete();

        $employee->refresh();

        $this->assertNull($employee->department_id);
    }

    /** @test */
    public function name_is_required()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Department::create([
            'description' => 'Test Department',
        ]);
    }

    /** @test */
    public function description_is_optional()
    {
        $department = Department::create([
            'name' => 'Engineering',
        ]);

        $this->assertNull($department->description);
        $this->assertDatabaseHas('departments', [
            'name' => 'Engineering',
            'description' => null,
        ]);
    }

    /** @test */
    public function color_is_optional()
    {
        $department = Department::create([
            'name' => 'Engineering',
        ]);

        $this->assertNull($department->color);
    }

    /** @test */
    public function it_can_update_department_details()
    {
        $department = Department::create([
            'name' => 'Engineering',
            'description' => 'Old description',
            'color' => '#FF0000',
        ]);

        $department->update([
            'name' => 'Engineering & Technology',
            'description' => 'Updated description',
            'color' => '#00FF00',
        ]);

        $this->assertEquals('Engineering & Technology', $department->name);
        $this->assertEquals('Updated description', $department->description);
        $this->assertEquals('#00FF00', $department->color);

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'name' => 'Engineering & Technology',
            'color' => '#00FF00',
        ]);
    }

    /** @test */
    public function it_has_timestamps()
    {
        $department = Department::create([
            'name' => 'Engineering',
        ]);

        $this->assertNotNull($department->created_at);
        $this->assertNotNull($department->updated_at);
    }
}
