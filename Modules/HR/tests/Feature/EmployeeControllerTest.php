<?php

namespace Modules\HR\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HR\app\Models\Employee;
use Modules\HR\app\Models\Department;
use App\Models\User;

class EmployeeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');

        // Create and authenticate a user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_displays_employees_index_page()
    {
        $department = Department::create(['name' => 'Engineering']);

        Employee::factory()->count(3)->create([
            'department_id' => $department->id,
        ]);

        $response = $this->get(route('hr.employees.index'));

        $response->assertStatus(200);
        $response->assertViewIs('hr::employees.index');
        $response->assertViewHas('employees');
    }

    /** @test */
    public function it_displays_employee_create_page()
    {
        $response = $this->get(route('hr.employees.create'));

        $response->assertStatus(200);
        $response->assertViewIs('hr::employees.create');
    }

    /** @test */
    public function it_can_create_an_employee()
    {
        $department = Department::create(['name' => 'Engineering']);

        $employeeData = [
            'employee_code' => 'EMP001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+1234567890',
            'hire_date' => '2025-01-15',
            'salary' => 5000.00,
            'position' => 'Senior Developer',
            'department_id' => $department->id,
            'status' => 'active',
        ];

        $response = $this->post(route('hr.employees.store'), $employeeData);

        $response->assertRedirect(route('hr.employees.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('employees', [
            'employee_code' => 'EMP001',
            'email' => 'john.doe@example.com',
        ]);
    }

    /** @test */
    public function it_validates_employee_code_is_required()
    {
        $employeeData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'hire_date' => '2025-01-15',
            'salary' => 5000.00,
        ];

        $response = $this->post(route('hr.employees.store'), $employeeData);

        $response->assertSessionHasErrors('employee_code');
    }

    /** @test */
    public function it_validates_employee_code_is_unique()
    {
        Employee::factory()->create([
            'employee_code' => 'EMP001',
            'email' => 'existing@example.com',
        ]);

        $employeeData = [
            'employee_code' => 'EMP001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'hire_date' => '2025-01-15',
            'salary' => 5000.00,
        ];

        $response = $this->post(route('hr.employees.store'), $employeeData);

        $response->assertSessionHasErrors('employee_code');
    }

    /** @test */
    public function it_validates_email_is_unique()
    {
        Employee::factory()->create([
            'employee_code' => 'EMP001',
            'email' => 'duplicate@example.com',
        ]);

        $employeeData = [
            'employee_code' => 'EMP002',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'duplicate@example.com',
            'hire_date' => '2025-01-15',
            'salary' => 5000.00,
        ];

        $response = $this->post(route('hr.employees.store'), $employeeData);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function it_displays_employee_details()
    {
        $employee = Employee::factory()->create();

        $response = $this->get(route('hr.employees.show', $employee->id));

        $response->assertStatus(200);
        $response->assertViewIs('hr::employees.show');
        $response->assertViewHas('employee');
    }

    /** @test */
    public function it_displays_employee_edit_page()
    {
        $employee = Employee::factory()->create();

        $response = $this->get(route('hr.employees.edit', $employee->id));

        $response->assertStatus(200);
        $response->assertViewIs('hr::employees.edit');
        $response->assertViewHas('employee');
    }

    /** @test */
    public function it_can_update_an_employee()
    {
        $employee = Employee::factory()->create([
            'employee_code' => 'EMP001',
            'email' => 'old@example.com',
        ]);

        $updateData = [
            'employee_code' => 'EMP001',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'new@example.com',
            'hire_date' => $employee->hire_date->format('Y-m-d'),
            'salary' => 6000.00,
            'position' => 'Lead Developer',
        ];

        $response = $this->put(route('hr.employees.update', $employee->id), $updateData);

        $response->assertRedirect(route('hr.employees.show', $employee->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'first_name' => 'Jane',
            'email' => 'new@example.com',
            'salary' => 6000.00,
        ]);
    }

    /** @test */
    public function it_can_delete_an_employee()
    {
        $employee = Employee::factory()->create();

        $response = $this->delete(route('hr.employees.destroy', $employee->id));

        $response->assertRedirect(route('hr.employees.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('employees', [
            'id' => $employee->id,
        ]);
    }

    /** @test */
    public function it_filters_employees_by_department()
    {
        $department1 = Department::create(['name' => 'Engineering']);
        $department2 = Department::create(['name' => 'Marketing']);

        Employee::factory()->count(3)->create(['department_id' => $department1->id]);
        Employee::factory()->count(2)->create(['department_id' => $department2->id]);

        $response = $this->get(route('hr.employees.index', ['department_id' => $department1->id]));

        $response->assertStatus(200);
        $employees = $response->viewData('employees');
        $this->assertCount(3, $employees);
    }

    /** @test */
    public function it_searches_employees_by_name()
    {
        Employee::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'employee_code' => 'EMP001',
            'email' => 'john@example.com',
        ]);

        Employee::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'employee_code' => 'EMP002',
            'email' => 'jane@example.com',
        ]);

        $response = $this->get(route('hr.employees.index', ['search' => 'John']));

        $response->assertStatus(200);
        $employees = $response->viewData('employees');
        $this->assertCount(1, $employees);
        $this->assertEquals('John', $employees->first()->first_name);
    }

    /** @test */
    public function unauthenticated_users_cannot_access_employees()
    {
        auth()->logout();

        $response = $this->get(route('hr.employees.index'));

        $response->assertRedirect(route('login'));
    }
}
