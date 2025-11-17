# HR Module Overview

## Introduction

The HR (Human Resources) Module is a comprehensive employee management system built as part of the accounting application. It handles all aspects of employee lifecycle management, leave tracking, salary deductions, and employee advances.

**Location:** `Modules/HR/`

**Version:** 1.0

**Laravel Version:** 12.x

---

## Module Structure

```
Modules/HR/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # All HTTP controllers
│   │   └── Requests/             # Form validation classes
│   ├── Models/                   # Eloquent models
│   └── Providers/                # Service providers
├── config/                       # Module configuration
├── database/
│   ├── migrations/               # Database migrations
│   └── seeders/                  # Database seeders
├── resources/
│   ├── views/                    # Blade templates
│   └── lang/                     # Language files
├── routes/
│   └── web.php                   # Web routes
├── tests/
│   ├── Feature/                  # Feature tests
│   └── Unit/                     # Unit tests
└── module.json                   # Module metadata
```

---

## Core Features

### 1. Employee Management

**Purpose:** Manage employee records, personal information, and employment details.

**Key Features:**
- Employee profiles with personal and employment information
- Unique employee codes
- Department assignment
- Position and salary tracking
- Employment status management
- Years of service calculation
- Daily rate auto-calculation

**Controllers:** `EmployeeController`

**Models:** `Employee`

**Routes:**
- `hr.employees.index` - List all employees
- `hr.employees.create` - Create new employee
- `hr.employees.show` - View employee details
- `hr.employees.edit` - Edit employee
- `hr.employees.destroy` - Delete employee

---

### 2. Department Management

**Purpose:** Organize employees into departments.

**Key Features:**
- Department creation and management
- Employee count per department
- Total salary cost per department
- Color coding for visual distinction
- Department descriptions

**Controllers:** `DepartmentController`

**Models:** `Department`

**Routes:**
- `hr.departments.index` - List all departments
- `hr.departments.create` - Create new department
- `hr.departments.show` - View department with employees
- `hr.departments.edit` - Edit department
- `hr.departments.destroy` - Delete department

---

### 3. Leave Management

**Purpose:** Handle employee leave requests, approvals, and tracking.

**Key Features:**
- Multiple leave types (annual, sick, unpaid, etc.)
- Leave request submission
- Approval workflow
- Leave rejection with reasons
- Leave balance tracking
- Automatic deduction for unpaid leaves
- Leave overlap detection
- Annual leave quota management

**Controllers:** `LeaveController`, `LeaveTypeController`

**Models:** `Leave`, `LeaveType`

**Key Workflows:**

#### Leave Request Process
1. Employee submits leave request with dates and reason
2. System calculates total days
3. Status set to 'pending'
4. Manager reviews request
5. Manager approves or rejects
6. If unpaid leave is approved, deduction is auto-created
7. Leave balance is updated

#### Leave Approval Rules
- Leave must be in 'pending' status
- Cannot overlap with existing approved leaves
- Must be within leave type quota (if applicable)
- Requires approver employee ID

**Routes:**
- `hr.leaves.index` - List all leave requests
- `hr.leaves.create` - Create leave request
- `hr.leaves.show` - View leave details
- `hr.leaves.approve` - Approve leave
- `hr.leaves.reject` - Reject leave with reason

---

### 4. Salary Deductions

**Purpose:** Track and manage employee salary deductions and bonuses.

**Key Features:**
- Multiple deduction types:
  - Days-based deductions (absence, late arrivals)
  - Fixed amount deductions
  - Unpaid leave deductions (auto-created)
  - Advance recovery deductions
- Bonus tracking (negative deductions)
- Automatic calculation from days × daily rate
- Monthly deduction reports
- Linked to leaves and advances

**Controllers:** `DeductionController`

**Models:** `Deduction`, `DeductionType`

**Deduction Types:**
- `days` - Deduction based on number of days absent
- `amount` - Fixed monetary deduction
- `unpaid_leave` - Auto-created from unpaid leave approvals
- `advance_recovery` - Recovery of employee advances

**Routes:**
- `hr.deductions.index` - List all deductions
- `hr.deductions.create` - Create manual deduction
- `hr.deductions.edit` - Edit deduction
- `hr.deductions.destroy` - Delete deduction

---

### 5. Employee Advances

**Purpose:** Manage cash advances given to employees and their settlements.

**Key Features:**
- Multiple advance types:
  - Cash advances
  - Salary advances
  - Petty cash
  - Travel advances
  - Purchase advances
- Auto-generated unique advance codes (ADV-YYYY-###)
- Settlement tracking (cash returned + receipts)
- Partial settlement support
- Overdue advance detection
- Conversion to salary deduction
- Settlement history

**Controllers:** `AdvanceController`

**Models:** `Advance`, `AdvanceSettlement`

**Key Workflows:**

#### Advance Issuance Process
1. Advance is issued to employee
2. System generates unique advance code
3. Expected settlement date is set
4. Status set to 'pending'

#### Settlement Process
1. Employee returns cash and/or submits receipts
2. Settlement record created with amounts
3. System calculates outstanding balance
4. Advance status auto-updates:
   - `pending` - No settlements
   - `partial_settlement` - Some amount settled
   - `settled` - Fully settled
   - `settled_via_deduction` - Converted to salary deduction

#### Convert to Deduction
1. Manager chooses to convert advance to deduction
2. System creates deduction record with type 'advance_recovery'
3. Advance status set to 'settled_via_deduction'
4. Settlement date set to today

**Advance Statuses:**
- `pending` - Not yet settled
- `partial_settlement` - Partially settled
- `settled` - Fully settled with cash/receipts
- `settled_via_deduction` - Converted to salary deduction

**Routes:**
- `hr.advances.index` - List all advances
- `hr.advances.create` - Issue new advance
- `hr.advances.show` - View advance with settlements
- `hr.advances.settlements.create` - Create settlement
- `hr.advances.settlements.edit` - Edit settlement
- `hr.advances.convert` - Convert to salary deduction

---

## Data Flow

### Employee Salary Calculation

```
Base Salary
    ↓
Calculate Daily Rate (salary / 30)
    ↓
Get Monthly Deductions
    ↓
    ├── Days-based deductions (days × daily_rate)
    ├── Fixed amount deductions
    ├── Unpaid leave deductions
    └── Advance recovery deductions
    ↓
Net Salary = Base Salary - Total Deductions
```

### Leave Request Flow

```
Employee Submits Leave Request
    ↓
Status: Pending
    ↓
Manager Reviews
    ↓
    ├── Approve
    │   ├── Check leave balance
    │   ├── Check overlaps
    │   ├── Set status: Approved
    │   └── If unpaid → Create Deduction
    │
    └── Reject
        ├── Set status: Rejected
        └── Save rejection reason
```

### Advance Settlement Flow

```
Advance Issued
    ↓
Status: Pending
    ↓
Employee Returns/Spends Money
    ↓
Settlement Created
    ↓
    ├── Cash Returned
    └── Amount Spent (with receipts)
    ↓
Calculate Outstanding Balance
    ↓
Update Advance Status
    ↓
    ├── Fully Settled → settled
    ├── Partially Settled → partial_settlement
    └── Not Settled → pending
    ↓
    (Optional) Convert to Deduction
    ↓
Status: settled_via_deduction
```

---

## Business Rules

### Employee Rules
1. Employee code must be unique
2. Email must be unique
3. Daily rate is auto-calculated as `salary / 30`
4. When department is deleted, employees' department_id is set to NULL
5. Deleting an employee cascades to delete leaves, deductions, advances

### Leave Rules
1. Leave start date must be today or in the future
2. Leave end date must be >= start date
3. Total days is auto-calculated
4. Leave requests default to 'pending' status
5. Only pending leaves can be approved or rejected
6. Approved leaves cannot overlap
7. Unpaid leaves auto-create deductions
8. Leave balance = max_days_per_year - total_approved_days
9. Unlimited leave types have NULL max_days_per_year

### Deduction Rules
1. If days are provided, amount = days × employee.daily_rate
2. Negative amounts represent bonuses
3. Deductions linked to leaves cannot be edited (system-generated)
4. Deductions linked to advances are auto-created

### Advance Rules
1. Advance codes are auto-generated in format: ADV-YYYY-###
2. Settlement codes are auto-generated in format: SET-YYYY-###
3. Outstanding balance = advance amount - (cash returned + amount spent)
4. Status is auto-updated based on settlements
5. Overdue advances are those past expected_settlement_date
6. Converting to deduction creates a deduction record and marks advance as settled
7. Advances can have multiple settlements (partial payments)

---

## Database Relationships

### Entity Relationships

```
Department (1) ─────── (*) Employee
                          ↓
                          ├─── (*) Leave ─── (1) LeaveType
                          │         ↓
                          │         └─── (1) Deduction
                          │
                          ├─── (*) Deduction
                          │         ↓
                          │         └─── (1) Advance
                          │
                          └─── (*) Advance
                                    ↓
                                    └─── (*) AdvanceSettlement
```

### Self-Referencing Relationships

- `leaves.approved_by` → `employees.id`
- `advances.issued_by` → `employees.id`
- `advance_settlements.received_by` → `employees.id`

---

## Key Calculations

### Daily Rate
```php
$dailyRate = $employee->salary / 30;
```

### Leave Total Days
```php
$totalDays = Carbon::parse($endDate)->diffInDays(Carbon::parse($startDate)) + 1;
```

### Days-Based Deduction Amount
```php
$amount = $days * $employee->daily_rate;
```

### Net Salary
```php
$netSalary = $employee->salary - $employee->getTotalDeductions($month);
```

### Advance Outstanding Balance
```php
$outstanding = $advance->amount - ($advance->total_cash_returned + $advance->total_spent);
```

### Leave Balance
```php
$balance = $leaveType->max_days_per_year - $leaveType->getTotalDaysUsedThisYear($employeeId);
```

---

## Security Considerations

### Access Control
- All routes require authentication (`auth` middleware)
- Form validation through dedicated Request classes
- CSRF protection on all forms
- File upload validation (size, type)

### Data Validation
- Unique constraints on employee_code and email
- Foreign key constraints maintain data integrity
- Date validation prevents past-dated requests
- Amount validation prevents negative values (except bonuses)

### Audit Trail
- All models have `created_at` and `updated_at` timestamps
- Leave approval records approver and timestamp
- Advance and settlement codes provide traceability
- Rejection reasons are logged

---

## Common Use Cases

### 1. Calculate Employee Monthly Salary

```php
$employee = Employee::find($id);
$month = Carbon::parse('2025-11-01');

$baseSalary = $employee->salary;
$deductions = $employee->getTotalDeductions($month);
$netSalary = $employee->calculateNetSalary($month);
```

### 2. Approve Leave Request

```php
$leave = Leave::find($id);
$leave->approve($managerId);
// Auto-creates deduction if unpaid
```

### 3. Issue and Settle Advance

```php
// Issue advance
$advance = Advance::create([
    'employee_id' => $employeeId,
    'amount' => 1000,
    'type' => 'salary_advance',
    // ... other fields
]);
// Code auto-generated: ADV-2025-001

// Create settlement
$settlement = $advance->settlements()->create([
    'cash_returned' => 300,
    'amount_spent' => 700,
    'settlement_date' => now(),
]);
// Code auto-generated: SET-2025-001
// Advance status auto-updated to 'settled'
```

### 4. Convert Advance to Deduction

```php
$advance = Advance::find($id);
$deduction = $advance->convertToDeduction();
// Creates deduction with type 'advance_recovery'
// Marks advance as 'settled_via_deduction'
```

### 5. Get Leave Balance

```php
$employee = Employee::find($employeeId);
$leaveType = LeaveType::find($leaveTypeId);
$balance = $employee->getLeaveBalance($leaveType->id, 2025);
```

---

## Testing

The module includes comprehensive PHPUnit tests:

### Unit Tests
- Model relationship tests
- Accessor/mutator tests
- Business logic tests
- Calculation tests

### Feature Tests
- Controller integration tests
- Form validation tests
- Workflow tests (leave approval, advance settlement)
- Authorization tests

**Test Location:** `Modules/HR/tests/`

**Run Tests:**
```bash
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

---

## Future Enhancements

### Planned Features
1. Payroll generation and export
2. Employee performance reviews
3. Time and attendance tracking
4. Employee documents management
5. Salary history tracking
6. Leave carryover functionality
7. Email notifications for leave approvals
8. Dashboard analytics and reports
9. Export to PDF/Excel
10. Multi-currency support

### Technical Improvements
1. API endpoints for mobile app
2. Real-time notifications
3. Advanced reporting dashboard
4. Bulk operations support
5. Import employees from CSV/Excel
6. Integration with accounting module
7. Automated backup and restore
8. Role-based permissions

---

## Configuration

**Module Configuration:** `Modules/HR/module.json`

```json
{
    "name": "HR",
    "alias": "hr",
    "description": "Human Resources Management Module",
    "keywords": ["hr", "employees", "leaves", "payroll"],
    "priority": 1,
    "providers": [
        "Modules\\HR\\Providers\\HRServiceProvider"
    ],
    "files": []
}
```

---

## Dependencies

### Laravel Packages
- `nwidart/laravel-modules` - Modular architecture
- `laravel/framework` - Core framework

### PHP Requirements
- PHP 8.2+
- MySQL 8.0+ or PostgreSQL 13+

---

## Support and Documentation

For more information, see:
- [Database Schema](../schema/DATABASE_SCHEMA.md)
- [Model Relations](../schema/MODEL_RELATIONS.md)
- [Controllers and API](../CONTROLLERS_API.md)

---

## License

This module is part of the Accounting Application and follows the same license.
