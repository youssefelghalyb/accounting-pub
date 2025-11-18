# Controllers and API Documentation

This document provides comprehensive documentation for all controllers, routes, and API endpoints in the application.

## Table of Contents
- [HR Module Controllers](#hr-module-controllers)
  - [DepartmentController](#departmentcontroller)
  - [EmployeeController](#employeecontroller)
  - [LeaveTypeController](#leavetypecontroller)
  - [LeaveController](#leavecontroller)
  - [DeductionController](#deductioncontroller)
  - [AdvanceController](#advancecontroller)
- [Settings Module Controllers](#settings-module-controllers)
  - [OrganizationSettingsController](#organizationsettingscontroller)
  - [LanguageController](#languagecontroller)
- [Form Validation](#form-validation)

---

## HR Module Controllers

All HR module routes are prefixed with `/hr` and use the naming convention `hr.{resource}.{action}`.

### DepartmentController

**Location:** `Modules/HR/app/Http/Controllers/DepartmentController.php`

#### Routes

| Method | URI | Route Name | Action | Description |
|--------|-----|------------|--------|-------------|
| GET | /hr/departments | hr.departments.index | index() | List all departments |
| GET | /hr/departments/create | hr.departments.create | create() | Show create form |
| POST | /hr/departments | hr.departments.store | store() | Create new department |
| GET | /hr/departments/{id} | hr.departments.show | show() | Show department details |
| GET | /hr/departments/{id}/edit | hr.departments.edit | edit() | Show edit form |
| PUT/PATCH | /hr/departments/{id} | hr.departments.update | update() | Update department |
| DELETE | /hr/departments/{id} | hr.departments.destroy | destroy() | Delete department |

#### Methods

##### `index()`
Lists all departments with employee count.

**Request:** None

**Response:** View with departments collection
```php
$departments = Department::withCount('employees')->get();
return view('hr::departments.index', compact('departments'));
```

**View Data:**
- `departments`: Collection of Department models with employee count

---

##### `create()`
Shows the department creation form.

**Request:** None

**Response:** View with form

---

##### `store(StoreDepartmentRequest $request)`
Creates a new department.

**Request Body:**
```json
{
    "name": "Engineering",
    "description": "Engineering department",
    "color": "#FF5733"
}
```

**Validation Rules:**
- `name`: required, string, max:255
- `description`: nullable, string
- `color`: nullable, string, regex:/^#[0-9A-F]{6}$/i

**Response:** Redirect to departments.index with success message

---

##### `show(Department $department)`
Shows department details including all employees.

**Request:** None

**Response:** View with department and employees
```php
$department->load('employees');
return view('hr::departments.show', compact('department'));
```

**View Data:**
- `department`: Department model with employees relation

---

##### `edit(Department $department)`
Shows the department edit form.

**Request:** None

**Response:** View with department data

---

##### `update(UpdateDepartmentRequest $request, Department $department)`
Updates an existing department.

**Request Body:**
```json
{
    "name": "Engineering & Technology",
    "description": "Updated description",
    "color": "#00FF00"
}
```

**Validation Rules:** Same as `store()`

**Response:** Redirect to departments.index with success message

---

##### `destroy(Department $department)`
Deletes a department (sets employees' department_id to NULL).

**Request:** None

**Response:** Redirect to departments.index with success message

**Side Effects:**
- All employees in this department have their `department_id` set to NULL

---

### EmployeeController

**Location:** `Modules/HR/app/Http/Controllers/EmployeeController.php`

#### Routes

| Method | URI | Route Name | Action | Description |
|--------|-----|------------|--------|-------------|
| GET | /hr/employees | hr.employees.index | index() | List all employees |
| GET | /hr/employees/create | hr.employees.create | create() | Show create form |
| POST | /hr/employees | hr.employees.store | store() | Create new employee |
| GET | /hr/employees/{id} | hr.employees.show | show() | Show employee details |
| GET | /hr/employees/{id}/edit | hr.employees.edit | edit() | Show edit form |
| PUT/PATCH | /hr/employees/{id} | hr.employees.update | update() | Update employee |
| DELETE | /hr/employees/{id} | hr.employees.destroy | destroy() | Delete employee |

#### Methods

##### `index(Request $request)`
Lists all employees with optional filtering and statistics.

**Query Parameters:**
- `department_id` (optional): Filter by department
- `search` (optional): Search by name or employee code

**Response:** View with employees and statistics
```php
$employees = Employee::with('department')
    ->when($request->department_id, fn($q) => $q->where('department_id', $request->department_id))
    ->when($request->search, fn($q) => $q->where(fn($query) =>
        $query->where('first_name', 'like', "%{$request->search}%")
              ->orWhere('last_name', 'like', "%{$request->search}%")
              ->orWhere('employee_code', 'like', "%{$request->search}%")
    ))
    ->get();

$stats = [
    'total_employees' => Employee::count(),
    'total_salary' => Employee::sum('salary'),
    'average_salary' => Employee::avg('salary'),
];

return view('hr::employees.index', compact('employees', 'departments', 'stats'));
```

**View Data:**
- `employees`: Filtered employee collection
- `departments`: All departments for filter dropdown
- `stats`: Statistics object

---

##### `create()`
Shows the employee creation form.

**Response:** View with departments for dropdown

---

##### `store(StoreEmployeeRequest $request)`
Creates a new employee.

**Request Body:**
```json
{
    "employee_code": "EMP001",
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "phone": "+1234567890",
    "hire_date": "2025-01-15",
    "salary": "5000.00",
    "position": "Senior Developer",
    "department_id": 1,
    "status": "active"
}
```

**Validation Rules:**
- `employee_code`: required, string, unique:employees
- `first_name`: required, string, max:255
- `last_name`: required, string, max:255
- `email`: required, email, unique:employees
- `phone`: nullable, string
- `hire_date`: required, date
- `salary`: required, numeric, min:0
- `position`: nullable, string
- `department_id`: nullable, exists:departments,id
- `status`: nullable, string

**Response:** Redirect to employees.index with success message

**Side Effects:**
- Auto-calculates `daily_rate` = salary / 30

---

##### `show(Employee $employee)`
Shows employee details with comprehensive information.

**Response:** View with employee data and related information
```php
$employee->load([
    'department',
    'leaves.leaveType',
    'deductions',
    'advances.settlements'
]);

$currentMonth = now();
$salaryBreakdown = [
    'base_salary' => $employee->salary,
    'total_deductions' => $employee->getTotalDeductions($currentMonth),
    'net_salary' => $employee->calculateNetSalary($currentMonth),
];

$leaveStats = LeaveType::all()->map(function($leaveType) use ($employee) {
    return [
        'type' => $leaveType->name,
        'used' => $leaveType->getTotalDaysUsedThisYear($employee->id),
        'available' => $leaveType->max_days_per_year - $leaveType->getTotalDaysUsedThisYear($employee->id),
    ];
});

return view('hr::employees.show', compact('employee', 'salaryBreakdown', 'leaveStats'));
```

**View Data:**
- `employee`: Employee with relations
- `salaryBreakdown`: Salary calculation details
- `leaveStats`: Leave balance for all leave types

---

##### `edit(Employee $employee)`
Shows the employee edit form.

**Response:** View with employee and departments

---

##### `update(UpdateEmployeeRequest $request, Employee $employee)`
Updates an existing employee.

**Request Body:** Same as `store()`

**Validation Rules:** Same as `store()` but email and employee_code unique except current employee

**Response:** Redirect to employees.show with success message

---

##### `destroy(Employee $employee)`
Deletes an employee.

**Request:** None

**Response:** Redirect to employees.index with success message

**Side Effects:**
- Cascades to delete all leaves, deductions, advances

---

### LeaveTypeController

**Location:** `Modules/HR/app/Http/Controllers/LeaveTypeController.php`

#### Routes

| Method | URI | Route Name | Action | Description |
|--------|-----|------------|--------|-------------|
| GET | /hr/leave-types | hr.leave-types.index | index() | List all leave types |
| GET | /hr/leave-types/create | hr.leave-types.create | create() | Show create form |
| POST | /hr/leave-types | hr.leave-types.store | store() | Create new leave type |
| GET | /hr/leave-types/{id}/edit | hr.leave-types.edit | edit() | Show edit form |
| PUT/PATCH | /hr/leave-types/{id} | hr.leave-types.update | update() | Update leave type |
| DELETE | /hr/leave-types/{id} | hr.leave-types.destroy | destroy() | Delete leave type |

#### Methods

##### `store(StoreLeaveTypeRequest $request)`
Creates a new leave type.

**Request Body:**
```json
{
    "name": "Annual Leave",
    "description": "Yearly vacation days",
    "max_days_per_year": 21,
    "is_paid": true,
    "is_active": true,
    "color": "#4CAF50"
}
```

**Validation Rules:**
- `name`: required, string, max:255
- `description`: nullable, string
- `max_days_per_year`: nullable, integer, min:0
- `is_paid`: required, boolean
- `is_active`: required, boolean
- `color`: nullable, string, regex:/^#[0-9A-F]{6}$/i

---

### LeaveController

**Location:** `Modules/HR/app/Http/Controllers/LeaveController.php`

#### Routes

| Method | URI | Route Name | Action | Description |
|--------|-----|------------|--------|-------------|
| GET | /hr/leaves | hr.leaves.index | index() | List all leaves |
| GET | /hr/leaves/create | hr.leaves.create | create() | Show create form |
| POST | /hr/leaves | hr.leaves.store | store() | Create new leave |
| GET | /hr/leaves/{id} | hr.leaves.show | show() | Show leave details |
| GET | /hr/leaves/{id}/edit | hr.leaves.edit | edit() | Show edit form |
| PUT/PATCH | /hr/leaves/{id} | hr.leaves.update | update() | Update leave |
| DELETE | /hr/leaves/{id} | hr.leaves.destroy | destroy() | Delete leave |
| POST | /hr/leaves/{id}/approve | hr.leaves.approve | approve() | Approve leave |
| POST | /hr/leaves/{id}/reject | hr.leaves.reject | reject() | Reject leave |

#### Methods

##### `index(Request $request)`
Lists all leave requests with optional filtering.

**Query Parameters:**
- `status` (optional): Filter by status (pending, approved, rejected)
- `employee_id` (optional): Filter by employee
- `leave_type_id` (optional): Filter by leave type

**Response:** View with leaves collection
```php
$leaves = Leave::with(['employee', 'leaveType', 'approvedBy'])
    ->when($request->status, fn($q) => $q->where('status', $request->status))
    ->when($request->employee_id, fn($q) => $q->where('employee_id', $request->employee_id))
    ->when($request->leave_type_id, fn($q) => $q->where('leave_type_id', $request->leave_type_id))
    ->orderBy('created_at', 'desc')
    ->get();
```

---

##### `store(StoreLeaveRequest $request)`
Creates a new leave request.

**Request Body:**
```json
{
    "employee_id": 1,
    "leave_type_id": 1,
    "start_date": "2025-12-01",
    "end_date": "2025-12-05",
    "reason": "Family vacation"
}
```

**Validation Rules:**
- `employee_id`: required, exists:employees,id
- `leave_type_id`: required, exists:leave_types,id
- `start_date`: required, date, after_or_equal:today
- `end_date`: required, date, after_or_equal:start_date
- `reason`: nullable, string

**Response:** Redirect to leaves.index with success message

**Side Effects:**
- Auto-calculates `total_days`
- Sets status to 'pending'

---

##### `approve(Leave $leave, Request $request)`
Approves a leave request.

**Request Body:**
```json
{
    "approved_by": 2
}
```

**Validation Rules:**
- `approved_by`: required, exists:employees,id

**Response:** Redirect back with success message

**Side Effects:**
- Sets status to 'approved'
- Sets `approved_by` and `approved_at`
- Creates deduction if leave is unpaid

**Error Conditions:**
- Returns error if leave is not in 'pending' status
- Returns error if leave overlaps with approved leaves

---

##### `reject(RejectLeaveRequest $request, Leave $leave)`
Rejects a leave request.

**Request Body:**
```json
{
    "approved_by": 2,
    "rejection_reason": "Insufficient staff during this period"
}
```

**Validation Rules:**
- `approved_by`: required, exists:employees,id
- `rejection_reason`: required, string

**Response:** Redirect back with success message

**Side Effects:**
- Sets status to 'rejected'
- Saves rejection reason

---

### DeductionController

**Location:** `Modules/HR/app/Http/Controllers/DeductionController.php`

#### Routes

| Method | URI | Route Name | Action | Description |
|--------|-----|------------|--------|-------------|
| GET | /hr/deductions | hr.deductions.index | index() | List all deductions |
| GET | /hr/deductions/create | hr.deductions.create | create() | Show create form |
| POST | /hr/deductions | hr.deductions.store | store() | Create new deduction |
| GET | /hr/deductions/{id}/edit | hr.deductions.edit | edit() | Show edit form |
| PUT/PATCH | /hr/deductions/{id} | hr.deductions.update | update() | Update deduction |
| DELETE | /hr/deductions/{id} | hr.deductions.destroy | destroy() | Delete deduction |

#### Methods

##### `store(StoreDeductionRequest $request)`
Creates a new deduction.

**Request Body (Days-based):**
```json
{
    "employee_id": 1,
    "type": "days",
    "days": 3,
    "deduction_date": "2025-11-15",
    "reason": "Absence without notice",
    "notes": "Additional notes"
}
```

**Request Body (Amount-based):**
```json
{
    "employee_id": 1,
    "type": "amount",
    "amount": 100.00,
    "deduction_date": "2025-11-15",
    "reason": "Equipment damage",
    "notes": "Additional notes"
}
```

**Validation Rules:**
- `employee_id`: required, exists:employees,id
- `type`: required, in:days,amount,unpaid_leave,advance_recovery
- `days`: required_if:type,days, integer, min:1
- `amount`: required_if:type,amount, numeric
- `deduction_date`: required, date
- `reason`: nullable, string
- `notes`: nullable, string
- `leave_id`: nullable, exists:leaves,id
- `advance_id`: nullable, exists:employee_advances,id

**Response:** Redirect to deductions.index with success message

**Side Effects:**
- If `days` is provided, auto-calculates `amount` = days × employee.daily_rate

---

### AdvanceController

**Location:** `Modules/HR/app/Http/Controllers/AdvanceController.php`

#### Routes

| Method | URI | Route Name | Action | Description |
|--------|-----|------------|--------|-------------|
| GET | /hr/advances | hr.advances.index | index() | List all advances |
| GET | /hr/advances/create | hr.advances.create | create() | Show create form |
| POST | /hr/advances | hr.advances.store | store() | Create new advance |
| GET | /hr/advances/{id} | hr.advances.show | show() | Show advance details |
| GET | /hr/advances/{id}/edit | hr.advances.edit | edit() | Show edit form |
| PUT/PATCH | /hr/advances/{id} | hr.advances.update | update() | Update advance |
| DELETE | /hr/advances/{id} | hr.advances.destroy | destroy() | Delete advance |
| GET | /hr/advances/{id}/settlements/create | hr.advances.settlements.create | createSettlement() | Show settlement form |
| POST | /hr/advances/{id}/settlements | hr.advances.settlements.store | storeSettlement() | Create settlement |
| GET | /hr/advances/{advance}/settlements/{settlement}/edit | hr.advances.settlements.edit | editSettlement() | Show edit settlement form |
| PUT | /hr/advances/{advance}/settlements/{settlement} | hr.advances.settlements.update | updateSettlement() | Update settlement |
| DELETE | /hr/advances/{advance}/settlements/{settlement} | hr.advances.settlements.destroy | destroySettlement() | Delete settlement |
| POST | /hr/advances/{id}/convert-to-deduction | hr.advances.convert | convertToDeduction() | Convert to deduction |

#### Methods

##### `index(Request $request)`
Lists all advances with optional filtering.

**Query Parameters:**
- `status` (optional): Filter by status
- `employee_id` (optional): Filter by employee
- `type` (optional): Filter by type
- `overdue` (optional): Show only overdue advances

**Response:** View with advances collection
```php
$advances = Advance::with(['employee', 'issuedBy', 'settlements'])
    ->when($request->status, fn($q) => $q->where('status', $request->status))
    ->when($request->employee_id, fn($q) => $q->where('employee_id', $request->employee_id))
    ->when($request->type, fn($q) => $q->where('type', $request->type))
    ->when($request->overdue, fn($q) => $q->overdue())
    ->orderBy('issue_date', 'desc')
    ->get();
```

---

##### `store(StoreAdvanceRequest $request)`
Creates a new advance.

**Request Body:**
```json
{
    "employee_id": 1,
    "amount": 1000.00,
    "issue_date": "2025-11-15",
    "expected_settlement_date": "2025-11-30",
    "type": "salary_advance",
    "purpose": "Emergency medical expenses",
    "notes": "To be recovered from salary",
    "issued_by": 2
}
```

**Validation Rules:**
- `employee_id`: required, exists:employees,id
- `amount`: required, numeric, min:0
- `issue_date`: required, date
- `expected_settlement_date`: nullable, date, after_or_equal:issue_date
- `type`: required, in:cash,salary_advance,petty_cash,travel,purchase
- `purpose`: nullable, string
- `notes`: nullable, string
- `issued_by`: nullable, exists:employees,id

**Response:** Redirect to advances.index with success message

**Side Effects:**
- Auto-generates `advance_code` (e.g., ADV-2025-001)
- Sets status to 'pending'

---

##### `show(Advance $advance)`
Shows advance details with settlements.

**Response:** View with advance and settlement data
```php
$advance->load(['employee', 'issuedBy', 'settlements.receivedBy', 'deductions']);

$summary = [
    'original_amount' => $advance->amount,
    'total_cash_returned' => $advance->total_cash_returned,
    'total_spent' => $advance->total_spent,
    'outstanding_balance' => $advance->outstanding_balance,
    'status' => $advance->status,
];

return view('hr::advances.show', compact('advance', 'summary'));
```

---

##### `storeSettlement(StoreSettlementRequest $request, Advance $advance)`
Creates a settlement for an advance.

**Request Body:**
```json
{
    "cash_returned": 300.00,
    "amount_spent": 700.00,
    "settlement_date": "2025-11-20",
    "settlement_notes": "Submitted receipts for expenses",
    "receipt_file": "(file upload)",
    "received_by": 2
}
```

**Validation Rules:**
- `cash_returned`: nullable, numeric, min:0
- `amount_spent`: nullable, numeric, min:0
- `settlement_date`: required, date
- `settlement_notes`: nullable, string
- `receipt_file`: nullable, file, mimes:pdf,jpg,png, max:2048
- `received_by`: nullable, exists:employees,id

**Response:** Redirect to advance.show with success message

**Side Effects:**
- Auto-generates `settlement_code` (e.g., SET-2025-001)
- Updates parent advance status
- If total accounted ≥ advance amount, marks advance as 'settled'

---

##### `convertToDeduction(Advance $advance)`
Converts outstanding balance to salary deduction.

**Request:** None

**Response:** Redirect back with success message

**Side Effects:**
- Creates a deduction with type 'advance_recovery'
- Sets advance status to 'settled_via_deduction'
- Sets `actual_settlement_date` to today

**Error Conditions:**
- Returns error if advance is already settled
- Returns error if outstanding balance is 0 or negative

---

## Settings Module Controllers

### OrganizationSettingsController

**Location:** `Modules/Settings/app/Http/Controllers/OrganizationSettingsController.php`

#### Routes

| Method | URI | Route Name | Action | Description |
|--------|-----|------------|--------|-------------|
| GET | /settings/organization | settings.organization | index() | Show settings form |
| PUT/PATCH | /settings/organization | settings.organization.update | update() | Update settings |

#### Methods

##### `index()`
Shows the organization settings form.

**Response:** View with current settings

---

##### `update(Request $request)`
Updates organization settings.

**Request Body:**
```json
{
    "organization_name": "Acme Corporation",
    "address": "123 Business St, City, Country",
    "phone": "+1234567890",
    "email": "info@acme.com",
    "website": "https://acme.com",
    "logo": "(file upload)",
    "timezone": "UTC",
    "date_format": "Y-m-d",
    "time_format": "H:i:s",
    "default_language": "en",
    "available_languages": ["en", "ar", "fr"],
    "currency": "USD",
    "currency_symbol": "$",
    "enable_notifications": true,
    "enable_audit_logs": true,
    "primary_color": "#1E40AF",
    "secondary_color": "#7C3AED",
    "ceo_name": "John Doe",
    "ceo_email": "ceo@acme.com",
    "ceo_phone": "+1234567890"
}
```

**Validation Rules:**
- `organization_name`: nullable, string, max:255
- `address`: nullable, string
- `email`: nullable, email
- `website`: nullable, url
- `logo`: nullable, image, max:2048
- `timezone`: nullable, string
- `date_format`: nullable, string
- `default_language`: nullable, string
- `available_languages`: nullable, array
- `enable_notifications`: nullable, boolean
- `enable_audit_logs`: nullable, boolean
- `primary_color`: nullable, string, regex:/^#[0-9A-F]{6}$/i
- `secondary_color`: nullable, string, regex:/^#[0-9A-F]{6}$/i

**Response:** Redirect back with success message

**Side Effects:**
- If logo is uploaded, stores in `storage/app/public/logos/`
- Updates or creates singleton settings record

---

### LanguageController

**Location:** `Modules/Settings/app/Http/Controllers/LanguageController.php`

#### Routes

| Method | URI | Route Name | Action | Description |
|--------|-----|------------|--------|-------------|
| POST | /language/switch | language.switch | switch() | Switch application language |

#### Methods

##### `switch(Request $request)`
Switches the application language.

**Request Body:**
```json
{
    "language": "ar"
}
```

**Validation Rules:**
- `language`: required, string, in:en,ar,fr

**Response:** Redirect back

**Side Effects:**
- Sets session locale
- Updates user preference (if authenticated)

---

## Form Validation

All controllers use dedicated FormRequest classes for validation located in `Modules/{Module}/app/Http/Requests/`.

### HR Module Requests

#### StoreEmployeeRequest / UpdateEmployeeRequest
```php
[
    'employee_code' => 'required|string|unique:employees,employee_code',
    'first_name' => 'required|string|max:255',
    'last_name' => 'required|string|max:255',
    'email' => 'required|email|unique:employees,email',
    'phone' => 'nullable|string',
    'hire_date' => 'required|date',
    'salary' => 'required|numeric|min:0',
    'position' => 'nullable|string',
    'department_id' => 'nullable|exists:departments,id',
    'status' => 'nullable|string',
]
```

#### StoreDepartmentRequest / UpdateDepartmentRequest
```php
[
    'name' => 'required|string|max:255',
    'description' => 'nullable|string',
    'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
]
```

#### StoreLeaveRequest / UpdateLeaveRequest
```php
[
    'employee_id' => 'required|exists:employees,id',
    'leave_type_id' => 'required|exists:leave_types,id',
    'start_date' => 'required|date|after_or_equal:today',
    'end_date' => 'required|date|after_or_equal:start_date',
    'reason' => 'nullable|string',
]
```

#### RejectLeaveRequest
```php
[
    'approved_by' => 'required|exists:employees,id',
    'rejection_reason' => 'required|string',
]
```

#### StoreLeaveTypeRequest / UpdateLeaveTypeRequest
```php
[
    'name' => 'required|string|max:255',
    'description' => 'nullable|string',
    'max_days_per_year' => 'nullable|integer|min:0',
    'is_paid' => 'required|boolean',
    'is_active' => 'required|boolean',
    'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
]
```

#### StoreDeductionRequest / UpdateDeductionRequest
```php
[
    'employee_id' => 'required|exists:employees,id',
    'type' => 'required|in:days,amount,unpaid_leave,advance_recovery',
    'days' => 'required_if:type,days|integer|min:1',
    'amount' => 'required_if:type,amount|numeric',
    'deduction_date' => 'required|date',
    'reason' => 'nullable|string',
    'notes' => 'nullable|string',
    'leave_id' => 'nullable|exists:leaves,id',
    'advance_id' => 'nullable|exists:employee_advances,id',
]
```

#### StoreAdvanceRequest / UpdateAdvanceRequest
```php
[
    'employee_id' => 'required|exists:employees,id',
    'amount' => 'required|numeric|min:0',
    'issue_date' => 'required|date',
    'expected_settlement_date' => 'nullable|date|after_or_equal:issue_date',
    'type' => 'required|in:cash,salary_advance,petty_cash,travel,purchase',
    'purpose' => 'nullable|string',
    'notes' => 'nullable|string',
    'issued_by' => 'nullable|exists:employees,id',
]
```

#### StoreSettlementRequest / UpdateSettlementRequest
```php
[
    'cash_returned' => 'nullable|numeric|min:0',
    'amount_spent' => 'nullable|numeric|min:0',
    'settlement_date' => 'required|date',
    'settlement_notes' => 'nullable|string',
    'receipt_file' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048',
    'received_by' => 'nullable|exists:employees,id',
]
```

---

## Response Formats

### Success Responses

All successful operations redirect with flash messages:

```php
return redirect()
    ->route('hr.employees.index')
    ->with('success', 'Employee created successfully');
```

### Error Responses

Validation errors return to previous page with errors:

```php
return redirect()
    ->back()
    ->withErrors($validator)
    ->withInput();
```

### View Data Conventions

- Collections use plural names: `$employees`, `$departments`
- Single models use singular names: `$employee`, `$department`
- Statistics use `$stats` or specific names like `$salaryBreakdown`
- Dropdown data uses `${resource}s` format

---

## Middleware

All routes use the following middleware:
- `web`: Web middleware group (sessions, CSRF)
- `auth`: Authentication required

Module routes are auto-loaded from `Modules/{Module}/routes/web.php`.

---

## Notes

- All controllers use resource routing conventions
- Form validation is separated into Request classes
- Controllers follow thin controller pattern (business logic in models)
- All monetary calculations use decimal precision
- File uploads are handled with Laravel's storage system
- Flash messages use session for user feedback
- All dates use Carbon for manipulation
