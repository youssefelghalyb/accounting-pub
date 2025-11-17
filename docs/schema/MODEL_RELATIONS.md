# Model Relations Documentation

This document describes all Eloquent model relationships, accessors, mutators, and business logic methods in the application.

## Table of Contents
- [HR Module Models](#hr-module-models)
  - [Employee](#employee)
  - [Department](#department)
  - [Leave](#leave)
  - [LeaveType](#leavetype)
  - [Deduction](#deduction)
  - [Advance](#advance)
  - [AdvanceSettlement](#advancesettlement)
- [Settings Module Models](#settings-module-models)
  - [OrganizationSetting](#organizationsetting)

---

## HR Module Models

### Employee

**Location:** `Modules/HR/app/Models/Employee.php`

#### Relationships

```php
// One-to-Many (Inverse)
department(): BelongsTo
    → Returns: Department
    → Foreign Key: department_id
    → Description: The department this employee belongs to

// One-to-Many
leaves(): HasMany
    → Returns: Collection<Leave>
    → Description: All leave requests made by this employee

deductions(): HasMany
    → Returns: Collection<Deduction>
    → Description: All deductions applied to this employee

advances(): HasMany
    → Returns: Collection<Advance>
    → Description: All advances issued to this employee

advanceSettlements(): HasMany
    → Returns: Collection<AdvanceSettlement>
    → Description: All advance settlements made by this employee
```

#### Accessors

```php
getFullNameAttribute(): string
    → Returns: "{first_name} {last_name}"
    → Usage: $employee->full_name

getDailyRateAttribute(): float
    → Returns: salary / 30
    → Description: Calculated daily rate for deduction purposes
    → Usage: $employee->daily_rate

getYearsOfServiceAttribute(): int
    → Returns: Number of years since hire_date
    → Usage: $employee->years_of_service

getPendingLeavesAttribute(): Collection
    → Returns: Collection of leaves with status 'pending'
    → Usage: $employee->pending_leaves
```

#### Business Methods

```php
calculateNetSalary(Carbon $month): float
    → Parameters: $month - The month to calculate salary for
    → Returns: Net salary after deductions
    → Logic: salary - getTotalDeductions($month)

getTotalDeductions(Carbon $month): float
    → Parameters: $month - The month to get deductions for
    → Returns: Sum of all deductions for the given month
    → Logic: Sums deductions.amount for the given month

getLeaveBalance(int $leaveTypeId, int $year): int
    → Parameters:
        - $leaveTypeId - The leave type to check
        - $year - The year to check balance for
    → Returns: Remaining leave days available
    → Logic: max_days_per_year - total_days_used
```

#### Fillable Attributes

```php
[
    'employee_code',
    'first_name',
    'last_name',
    'email',
    'phone',
    'hire_date',
    'salary',
    'position',
    'department_id',
    'status',
]
```

#### Casts

```php
[
    'hire_date' => 'date',
    'salary' => 'decimal:2',
    'daily_rate' => 'decimal:2',
]
```

---

### Department

**Location:** `Modules/HR/app/Models/Department.php`

#### Relationships

```php
// One-to-Many
employees(): HasMany
    → Returns: Collection<Employee>
    → Description: All employees in this department
```

#### Accessors

```php
getTotalEmployeesAttribute(): int
    → Returns: Count of employees in this department
    → Usage: $department->total_employees

getTotalSalaryCostAttribute(): float
    → Returns: Sum of all employee salaries in this department
    → Usage: $department->total_salary_cost
```

#### Fillable Attributes

```php
[
    'name',
    'description',
    'color',
]
```

---

### Leave

**Location:** `Modules/HR/app/Models/Leave.php`

#### Relationships

```php
// Many-to-One
employee(): BelongsTo
    → Returns: Employee
    → Foreign Key: employee_id
    → Description: The employee who requested this leave

leaveType(): BelongsTo
    → Returns: LeaveType
    → Foreign Key: leave_type_id
    → Description: The type of leave requested

approvedBy(): BelongsTo
    → Returns: Employee
    → Foreign Key: approved_by
    → Description: The employee who approved this leave

// One-to-One
deduction(): HasOne
    → Returns: Deduction
    → Description: The deduction created for this leave (if unpaid)
```

#### Scopes

```php
scopePending(Builder $query): Builder
    → Returns: Leaves with status 'pending'
    → Usage: Leave::pending()->get()

scopeApproved(Builder $query): Builder
    → Returns: Leaves with status 'approved'
    → Usage: Leave::approved()->get()

scopeRejected(Builder $query): Builder
    → Returns: Leaves with status 'rejected'
    → Usage: Leave::rejected()->get()

scopeForYear(Builder $query, int $year): Builder
    → Parameters: $year - The year to filter by
    → Returns: Leaves for the specified year
    → Usage: Leave::forYear(2025)->get()

scopeForMonth(Builder $query, int $year, int $month): Builder
    → Parameters:
        - $year - The year
        - $month - The month (1-12)
    → Returns: Leaves for the specified month
    → Usage: Leave::forMonth(2025, 11)->get()
```

#### Business Methods

```php
calculateTotalDays(): int
    → Returns: Number of days between start_date and end_date
    → Logic: Uses Carbon diffInDays()
    → Note: Auto-called on save

createDeductionIfUnpaid(): void
    → Returns: void
    → Description: Creates a deduction if leave type is unpaid
    → Logic: Checks if is_paid = false, creates deduction with type 'unpaid_leave'

approve(int $approvedBy): bool
    → Parameters: $approvedBy - Employee ID of approver
    → Returns: Success status
    → Logic:
        - Sets status to 'approved'
        - Sets approved_by and approved_at
        - Creates deduction if unpaid

reject(int $approvedBy, string $reason): bool
    → Parameters:
        - $approvedBy - Employee ID of person rejecting
        - $reason - Rejection reason
    → Returns: Success status
    → Logic: Sets status to 'rejected', saves rejection_reason

canBeApproved(): bool
    → Returns: Whether this leave can be approved
    → Logic: Checks if status is 'pending' and doesn't overlap with approved leaves

isActive(): bool
    → Returns: Whether this leave is currently active
    → Logic: Checks if today is between start_date and end_date
```

#### Fillable Attributes

```php
[
    'employee_id',
    'leave_type_id',
    'start_date',
    'end_date',
    'total_days',
    'reason',
    'status',
    'approved_by',
    'approved_at',
    'rejection_reason',
    'deduction_applied',
]
```

#### Casts

```php
[
    'start_date' => 'date',
    'end_date' => 'date',
    'approved_at' => 'datetime',
    'deduction_applied' => 'boolean',
]
```

---

### LeaveType

**Location:** `Modules/HR/app/Models/LeaveType.php`

#### Relationships

```php
// One-to-Many
leaves(): HasMany
    → Returns: Collection<Leave>
    → Description: All leave requests of this type
```

#### Business Methods

```php
getTotalDaysUsedThisYear(int $employeeId): int
    → Parameters: $employeeId - The employee to check for
    → Returns: Total approved leave days used this year
    → Logic: Sums total_days for approved leaves of this type

hasUnlimitedDays(): bool
    → Returns: Whether this leave type has unlimited days
    → Logic: Checks if max_days_per_year is null
```

#### Fillable Attributes

```php
[
    'name',
    'description',
    'max_days_per_year',
    'is_paid',
    'is_active',
    'color',
]
```

#### Casts

```php
[
    'is_paid' => 'boolean',
    'is_active' => 'boolean',
]
```

---

### Deduction

**Location:** `Modules/HR/app/Models/Deduction.php`

#### Relationships

```php
// Many-to-One
employee(): BelongsTo
    → Returns: Employee
    → Foreign Key: employee_id
    → Description: The employee this deduction applies to

leave(): BelongsTo
    → Returns: Leave
    → Foreign Key: leave_id
    → Description: The leave that caused this deduction (if applicable)

advance(): BelongsTo
    → Returns: Advance
    → Foreign Key: advance_id
    → Description: The advance being recovered (if applicable)
```

#### Scopes

```php
scopeForEmployee(Builder $query, int $employeeId): Builder
    → Parameters: $employeeId - The employee ID
    → Returns: Deductions for the specified employee
    → Usage: Deduction::forEmployee(1)->get()

scopeForYear(Builder $query, int $year): Builder
    → Parameters: $year - The year to filter by
    → Returns: Deductions for the specified year
    → Usage: Deduction::forYear(2025)->get()

scopeForMonth(Builder $query, int $year, int $month): Builder
    → Parameters:
        - $year - The year
        - $month - The month (1-12)
    → Returns: Deductions for the specified month
    → Usage: Deduction::forMonth(2025, 11)->get()

scopeByType(Builder $query, string $type): Builder
    → Parameters: $type - The deduction type
    → Returns: Deductions of the specified type
    → Usage: Deduction::byType('unpaid_leave')->get()

scopeDateRange(Builder $query, Carbon $start, Carbon $end): Builder
    → Parameters:
        - $start - Start date
        - $end - End date
    → Returns: Deductions within the date range
    → Usage: Deduction::dateRange($start, $end)->get()
```

#### Accessors

```php
getTypeNameAttribute(): string
    → Returns: Human-readable type name
    → Logic: Converts enum to readable string
    → Usage: $deduction->type_name

getTypeColorAttribute(): string
    → Returns: Color code for the deduction type
    → Logic: Maps type to color (e.g., 'unpaid_leave' → 'red')
    → Usage: $deduction->type_color
```

#### Business Methods

```php
isFromLeave(): bool
    → Returns: Whether this deduction is from a leave
    → Logic: Checks if leave_id is not null

isFromAdvance(): bool
    → Returns: Whether this deduction is from an advance recovery
    → Logic: Checks if advance_id is not null
```

#### Model Events

```php
creating(Deduction $deduction): void
    → Triggered: Before creating a new deduction
    → Logic: If days is set, calculates amount = days × employee.daily_rate
```

#### Fillable Attributes

```php
[
    'employee_id',
    'type',
    'days',
    'amount',
    'deduction_date',
    'leave_id',
    'advance_id',
    'reason',
    'notes',
]
```

#### Casts

```php
[
    'amount' => 'decimal:2',
    'deduction_date' => 'date',
    'days' => 'integer',
]
```

---

### Advance

**Location:** `Modules/HR/app/Models/Advance.php`

#### Relationships

```php
// Many-to-One
employee(): BelongsTo
    → Returns: Employee
    → Foreign Key: employee_id
    → Description: The employee who received this advance

issuedBy(): BelongsTo
    → Returns: Employee
    → Foreign Key: issued_by
    → Description: The employee who issued this advance

// One-to-Many
settlements(): HasMany
    → Returns: Collection<AdvanceSettlement>
    → Description: All settlement records for this advance

deductions(): HasMany
    → Returns: Collection<Deduction>
    → Description: Deductions created for this advance recovery
```

#### Scopes

```php
scopePending(Builder $query): Builder
    → Returns: Advances with status 'pending'
    → Usage: Advance::pending()->get()

scopeOverdue(Builder $query): Builder
    → Returns: Advances past expected_settlement_date
    → Usage: Advance::overdue()->get()

scopeForEmployee(Builder $query, int $employeeId): Builder
    → Parameters: $employeeId - The employee ID
    → Returns: Advances for the specified employee
    → Usage: Advance::forEmployee(1)->get()

scopeByType(Builder $query, string $type): Builder
    → Parameters: $type - The advance type
    → Returns: Advances of the specified type
    → Usage: Advance::byType('travel')->get()

scopeThisMonth(Builder $query): Builder
    → Returns: Advances issued this month
    → Usage: Advance::thisMonth()->get()
```

#### Accessors

```php
getTotalCashReturnedAttribute(): float
    → Returns: Sum of cash_returned from all settlements
    → Usage: $advance->total_cash_returned

getTotalSpentAttribute(): float
    → Returns: Sum of amount_spent from all settlements
    → Usage: $advance->total_spent

getTotalAccountedAttribute(): float
    → Returns: total_cash_returned + total_spent
    → Usage: $advance->total_accounted

getOutstandingBalanceAttribute(): float
    → Returns: amount - total_accounted
    → Logic: Positive if employee still owes, negative if overpaid
    → Usage: $advance->outstanding_balance

getOverpaymentAmountAttribute(): float
    → Returns: Amount overpaid (if outstanding_balance is negative)
    → Usage: $advance->overpayment_amount

getOverdueDaysAttribute(): int
    → Returns: Days overdue (if past expected_settlement_date)
    → Usage: $advance->overdue_days

getStatusColorAttribute(): string
    → Returns: Color code for status
    → Logic: Maps status to color
    → Usage: $advance->status_color

getTypeColorAttribute(): string
    → Returns: Color code for type
    → Logic: Maps type to color
    → Usage: $advance->type_color
```

#### Business Methods

```php
convertToDeduction(): Deduction
    → Returns: Created Deduction model
    → Description: Converts outstanding balance to salary deduction
    → Logic:
        - Creates deduction with type 'advance_recovery'
        - Sets status to 'settled_via_deduction'
        - Updates actual_settlement_date

hasDeduction(): bool
    → Returns: Whether this advance has been converted to deduction
    → Logic: Checks if deductions exist with type 'advance_recovery'

isOverdue(): bool
    → Returns: Whether this advance is overdue
    → Logic: Checks if expected_settlement_date has passed and status is pending

updateStatus(): void
    → Returns: void
    → Description: Auto-updates status based on settlements
    → Logic:
        - If fully settled → 'settled'
        - If partially settled → 'partial_settlement'
        - Otherwise → 'pending'
```

#### Model Events

```php
creating(Advance $advance): void
    → Triggered: Before creating a new advance
    → Logic: Auto-generates advance_code in format ADV-YYYY-###

saving(Advance $advance): void
    → Triggered: Before saving
    → Logic: Calls updateStatus() to ensure status is current
```

#### Fillable Attributes

```php
[
    'advance_code',
    'employee_id',
    'amount',
    'issue_date',
    'expected_settlement_date',
    'actual_settlement_date',
    'type',
    'status',
    'purpose',
    'notes',
    'issued_by',
]
```

#### Casts

```php
[
    'amount' => 'decimal:2',
    'issue_date' => 'date',
    'expected_settlement_date' => 'date',
    'actual_settlement_date' => 'date',
]
```

---

### AdvanceSettlement

**Location:** `Modules/HR/app/Models/AdvanceSettlement.php`

#### Relationships

```php
// Many-to-One
employee(): BelongsTo
    → Returns: Employee
    → Foreign Key: employee_id
    → Description: The employee settling the advance

advance(): BelongsTo
    → Returns: Advance
    → Foreign Key: advance_id
    → Description: The advance being settled

receivedBy(): BelongsTo
    → Returns: Employee
    → Foreign Key: received_by
    → Description: The employee who received the settlement
```

#### Model Events

```php
creating(AdvanceSettlement $settlement): void
    → Triggered: Before creating a new settlement
    → Logic: Auto-generates settlement_code in format SET-YYYY-###

saved(AdvanceSettlement $settlement): void
    → Triggered: After saving
    → Logic: Updates parent advance status
```

#### Fillable Attributes

```php
[
    'settlement_code',
    'employee_id',
    'advance_id',
    'cash_returned',
    'amount_spent',
    'settlement_date',
    'settlement_notes',
    'receipt_file',
    'received_by',
]
```

#### Casts

```php
[
    'cash_returned' => 'decimal:2',
    'amount_spent' => 'decimal:2',
    'settlement_date' => 'date',
]
```

---

## Settings Module Models

### OrganizationSetting

**Location:** `Modules/Settings/app/Models/OrganizationSetting.php`

#### Relationships

None - This is a singleton model (typically one record only)

#### Business Methods

```php
isLanguageAvailable(string $languageCode): bool
    → Parameters: $languageCode - Language code to check
    → Returns: Whether the language is in available_languages
    → Usage: $settings->isLanguageAvailable('en')
```

#### Fillable Attributes

```php
[
    'organization_name',
    'address',
    'phone',
    'email',
    'website',
    'logo_path',
    'timezone',
    'date_format',
    'time_format',
    'default_language',
    'available_languages',
    'currency',
    'currency_symbol',
    'enable_notifications',
    'enable_audit_logs',
    'primary_color',
    'secondary_color',
    'ceo_name',
    'ceo_email',
    'ceo_phone',
]
```

#### Casts

```php
[
    'enable_notifications' => 'boolean',
    'enable_audit_logs' => 'boolean',
    'available_languages' => 'array',
]
```

---

## Relationship Summary

### One-to-Many Relationships

| Parent Model | Child Model | Relationship Method | Foreign Key |
|--------------|-------------|---------------------|-------------|
| Department | Employee | employees() | department_id |
| Employee | Leave | leaves() | employee_id |
| Employee | Deduction | deductions() | employee_id |
| Employee | Advance | advances() | employee_id |
| Employee | AdvanceSettlement | advanceSettlements() | employee_id |
| LeaveType | Leave | leaves() | leave_type_id |
| Leave | Deduction | deduction() | leave_id |
| Advance | AdvanceSettlement | settlements() | advance_id |
| Advance | Deduction | deductions() | advance_id |

### Self-Referencing Relationships

| Model | Relationship | Description |
|-------|--------------|-------------|
| Employee | approvedBy (in Leave) | Employee who approved a leave |
| Employee | issuedBy (in Advance) | Employee who issued an advance |
| Employee | receivedBy (in AdvanceSettlement) | Employee who received settlement |

---

## Common Query Patterns

### Getting Employee with All Relations

```php
$employee = Employee::with([
    'department',
    'leaves.leaveType',
    'deductions.leave',
    'advances.settlements',
])->find($id);
```

### Getting Monthly Salary Report

```php
$employee = Employee::find($id);
$netSalary = $employee->calculateNetSalary(now());
$totalDeductions = $employee->getTotalDeductions(now());
```

### Getting Pending Leave Requests

```php
$pendingLeaves = Leave::pending()
    ->with(['employee', 'leaveType'])
    ->get();
```

### Getting Overdue Advances

```php
$overdueAdvances = Advance::overdue()
    ->with(['employee', 'issuedBy'])
    ->get();
```

### Getting Employee Leave Balance

```php
$employee = Employee::find($id);
$leaveType = LeaveType::find($leaveTypeId);
$balance = $employee->getLeaveBalance($leaveType->id, date('Y'));
```

---

## Model Event Summary

| Model | Event | Action |
|-------|-------|--------|
| Deduction | creating | Auto-calculates amount from days × daily_rate |
| Advance | creating | Auto-generates advance_code |
| Advance | saving | Updates status based on settlements |
| AdvanceSettlement | creating | Auto-generates settlement_code |
| AdvanceSettlement | saved | Updates parent advance status |
| Leave | saving | Calculates total_days automatically |

---

## Notes

- All models use Laravel's `$fillable` property for mass assignment protection
- Monetary values are cast to `decimal:2` for precision
- Date fields are cast to Carbon instances for easy manipulation
- Boolean fields use native boolean casting
- Models follow Laravel naming conventions (singular, PascalCase)
- Foreign keys follow Laravel conventions (model_id)
