# Database Schema Documentation

This document provides a comprehensive overview of the database schema for the HR and Accounting Management System.

## Table of Contents
- [Overview](#overview)
- [HR Module Tables](#hr-module-tables)
- [Settings Module Tables](#settings-module-tables)
- [Entity Relationship Diagram](#entity-relationship-diagram)
- [Indexes and Constraints](#indexes-and-constraints)

## Overview

The application uses a modular database structure with two main modules:
- **HR Module**: Manages employees, departments, leaves, deductions, and advances
- **Settings Module**: Manages organization-wide settings and configurations

## HR Module Tables

### departments

Stores organizational departments and their information.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | bigint unsigned | No | Primary key |
| name | varchar(255) | No | Department name |
| description | text | Yes | Department description |
| color | varchar(7) | Yes | Department color code (hex) |
| created_at | timestamp | Yes | Record creation timestamp |
| updated_at | timestamp | Yes | Record update timestamp |

**Relationships:**
- Has many `employees`

---

### employees

Stores employee information and employment details.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | bigint unsigned | No | Primary key |
| employee_code | varchar(255) | No | Unique employee identifier |
| first_name | varchar(255) | No | Employee first name |
| last_name | varchar(255) | No | Employee last name |
| email | varchar(255) | No | Employee email (unique) |
| phone | varchar(255) | Yes | Employee phone number |
| hire_date | date | No | Date of hire |
| salary | decimal(10,2) | No | Monthly salary |
| daily_rate | decimal(10,2) | Yes | Daily rate (auto-calculated) |
| position | varchar(255) | Yes | Job position/title |
| department_id | bigint unsigned | Yes | Foreign key to departments |
| status | varchar(255) | Yes | Employee status |
| created_at | timestamp | Yes | Record creation timestamp |
| updated_at | timestamp | Yes | Record update timestamp |

**Indexes:**
- Unique: `employee_code`, `email`
- Foreign key: `department_id` references `departments(id)` ON DELETE SET NULL

**Relationships:**
- Belongs to `department`
- Has many `leaves`, `deductions`, `advances`, `advance_settlements`

---

### leave_types

Defines types of leave available in the organization.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | bigint unsigned | No | Primary key |
| name | varchar(255) | No | Leave type name |
| description | text | Yes | Leave type description |
| max_days_per_year | integer | Yes | Maximum days allowed per year (null = unlimited) |
| is_paid | boolean | No | Whether the leave is paid (default: true) |
| is_active | boolean | No | Whether the leave type is active (default: true) |
| color | varchar(7) | Yes | Color code for UI display |
| created_at | timestamp | Yes | Record creation timestamp |
| updated_at | timestamp | Yes | Record update timestamp |

**Relationships:**
- Has many `leaves`

---

### leaves

Stores leave requests and their approval status.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | bigint unsigned | No | Primary key |
| employee_id | bigint unsigned | No | Foreign key to employees |
| leave_type_id | bigint unsigned | No | Foreign key to leave_types |
| start_date | date | No | Leave start date |
| end_date | date | No | Leave end date |
| total_days | integer | No | Total days of leave |
| reason | text | Yes | Reason for leave |
| status | enum | No | Status: pending, approved, rejected |
| approved_by | bigint unsigned | Yes | Foreign key to employees (approver) |
| approved_at | timestamp | Yes | Approval timestamp |
| rejection_reason | text | Yes | Reason for rejection |
| deduction_applied | boolean | No | Whether deduction was applied (default: false) |
| created_at | timestamp | Yes | Record creation timestamp |
| updated_at | timestamp | Yes | Record update timestamp |

**Indexes:**
- Foreign keys: `employee_id`, `leave_type_id`, `approved_by` reference `employees(id)`

**Relationships:**
- Belongs to `employee`, `leave_type`, `approved_by` (employee)
- Has one `deduction`

---

### deduction_types

Defines types of deductions that can be applied.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | bigint unsigned | No | Primary key |
| name | varchar(255) | No | Deduction type name |
| description | text | Yes | Deduction type description |
| calculation_type | enum | No | fixed or percentage |
| default_amount | decimal(10,2) | Yes | Default deduction amount |
| created_at | timestamp | Yes | Record creation timestamp |
| updated_at | timestamp | Yes | Record update timestamp |

**Relationships:**
- Referenced by deductions logic

---

### deductions

Stores employee deductions and bonuses (negative amounts).

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | bigint unsigned | No | Primary key |
| employee_id | bigint unsigned | No | Foreign key to employees |
| type | enum | No | Type: days, amount, unpaid_leave, advance_recovery |
| days | integer | Yes | Number of days for day-based deductions |
| amount | decimal(10,2) | No | Deduction amount (can be negative for bonuses) |
| deduction_date | date | No | Date of deduction |
| leave_id | bigint unsigned | Yes | Foreign key to leaves (if deduction is from leave) |
| advance_id | bigint unsigned | Yes | Foreign key to employee_advances (if recovery) |
| reason | varchar(255) | Yes | Reason for deduction |
| notes | text | Yes | Additional notes |
| created_at | timestamp | Yes | Record creation timestamp |
| updated_at | timestamp | Yes | Record update timestamp |

**Indexes:**
- Foreign keys: `employee_id`, `leave_id`, `advance_id`

**Relationships:**
- Belongs to `employee`, `leave`, `advance`

**Business Logic:**
- Amount is auto-calculated from days × employee daily_rate if days are provided
- Negative amounts represent bonuses

---

### employee_advances

Stores cash advances given to employees.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | bigint unsigned | No | Primary key |
| advance_code | varchar(255) | No | Unique advance code (e.g., ADV-2025-001) |
| employee_id | bigint unsigned | No | Foreign key to employees |
| amount | decimal(10,2) | No | Advance amount |
| issue_date | date | No | Date advance was issued |
| expected_settlement_date | date | Yes | Expected settlement date |
| actual_settlement_date | date | Yes | Actual settlement date |
| type | enum | No | Type: cash, salary_advance, petty_cash, travel, purchase |
| status | enum | No | Status: pending, partial_settlement, settled, settled_via_deduction |
| purpose | text | Yes | Purpose of advance |
| notes | text | Yes | Additional notes |
| issued_by | bigint unsigned | Yes | Foreign key to employees (issuer) |
| created_at | timestamp | Yes | Record creation timestamp |
| updated_at | timestamp | Yes | Record update timestamp |

**Indexes:**
- Unique: `advance_code`
- Foreign keys: `employee_id`, `issued_by`

**Relationships:**
- Belongs to `employee`, `issued_by` (employee)
- Has many `settlements`, `deductions`

**Business Logic:**
- Auto-generates advance codes in format: ADV-YYYY-###
- Status is auto-updated based on settlements

---

### advance_settlements

Stores settlement records for employee advances.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | bigint unsigned | No | Primary key |
| settlement_code | varchar(255) | No | Unique settlement code (e.g., SET-2025-001) |
| employee_id | bigint unsigned | No | Foreign key to employees |
| advance_id | bigint unsigned | Yes | Foreign key to employee_advances |
| cash_returned | decimal(10,2) | Yes | Amount of cash returned |
| amount_spent | decimal(10,2) | Yes | Amount spent (with receipts) |
| settlement_date | date | No | Date of settlement |
| settlement_notes | text | Yes | Settlement notes |
| receipt_file | varchar(255) | Yes | Path to receipt file |
| received_by | bigint unsigned | Yes | Foreign key to employees (receiver) |
| created_at | timestamp | Yes | Record creation timestamp |
| updated_at | timestamp | Yes | Record update timestamp |

**Indexes:**
- Unique: `settlement_code`
- Foreign keys: `employee_id`, `advance_id`, `received_by`

**Relationships:**
- Belongs to `employee`, `advance`, `received_by` (employee)

**Business Logic:**
- Auto-generates settlement codes in format: SET-YYYY-###
- Updates parent advance status upon settlement

---

## Settings Module Tables

### organization_settings

Stores organization-wide settings and configurations.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | bigint unsigned | No | Primary key |
| organization_name | varchar(255) | Yes | Organization name |
| address | text | Yes | Organization address |
| phone | varchar(255) | Yes | Organization phone |
| email | varchar(255) | Yes | Organization email |
| website | varchar(255) | Yes | Organization website |
| logo_path | varchar(255) | Yes | Path to organization logo |
| timezone | varchar(255) | Yes | Default timezone |
| date_format | varchar(255) | Yes | Date format (e.g., Y-m-d) |
| time_format | varchar(255) | Yes | Time format (e.g., H:i:s) |
| default_language | varchar(255) | Yes | Default language code |
| available_languages | text | Yes | JSON array of available languages |
| currency | varchar(255) | Yes | Currency code |
| currency_symbol | varchar(255) | Yes | Currency symbol |
| enable_notifications | boolean | Yes | Enable notifications (default: true) |
| enable_audit_logs | boolean | Yes | Enable audit logs (default: true) |
| primary_color | varchar(255) | Yes | Primary brand color |
| secondary_color | varchar(255) | Yes | Secondary brand color |
| ceo_name | varchar(255) | Yes | CEO name |
| ceo_email | varchar(255) | Yes | CEO email |
| ceo_phone | varchar(255) | Yes | CEO phone |
| created_at | timestamp | Yes | Record creation timestamp |
| updated_at | timestamp | Yes | Record update timestamp |

**Business Logic:**
- Typically contains only one record (singleton pattern)
- available_languages is cast to array for JSON handling

---

## Entity Relationship Diagram

```
┌─────────────────┐
│  departments    │
│─────────────────│
│ id (PK)         │
│ name            │
│ description     │
│ color           │
└────────┬────────┘
         │
         │ 1:N
         │
         ▼
┌─────────────────┐         ┌─────────────────┐
│   employees     │◄────────┤  leave_types    │
│─────────────────│  N:1    │─────────────────│
│ id (PK)         │         │ id (PK)         │
│ employee_code   │         │ name            │
│ first_name      │         │ max_days_per_yr │
│ last_name       │         │ is_paid         │
│ email           │         │ is_active       │
│ salary          │         └─────────────────┘
│ daily_rate      │                  ▲
│ department_id   │                  │
└────┬───┬───┬────┘                  │
     │   │   │                       │
     │   │   │ 1:N                   │
     │   │   └──────────┐            │
     │   │              │            │
     │   │ 1:N          ▼            │
     │   │        ┌─────────────┐    │
     │   │        │   leaves    │────┘
     │   │        │─────────────│
     │   │        │ id (PK)     │
     │   │        │ employee_id │
     │   │        │ leave_type  │
     │   │        │ start_date  │
     │   │        │ end_date    │
     │   │        │ status      │
     │   │        │ approved_by │
     │   │        └──────┬──────┘
     │   │               │
     │   │               │ 1:1
     │   │               │
     │   │ 1:N           ▼
     │   │        ┌─────────────┐
     │   │        │ deductions  │
     │   │        │─────────────│
     │   └───────►│ id (PK)     │
     │            │ employee_id │
     │            │ type        │
     │            │ amount      │
     │            │ leave_id    │
     │            │ advance_id  │
     │            └──────▲──────┘
     │                   │
     │ 1:N               │
     │                   │
     ▼                   │
┌─────────────────┐     │
│ employee_       │     │
│ advances        │─────┘
│─────────────────│
│ id (PK)         │
│ advance_code    │
│ employee_id     │
│ amount          │
│ issue_date      │
│ status          │
│ issued_by       │
└────────┬────────┘
         │
         │ 1:N
         │
         ▼
┌─────────────────┐
│ advance_        │
│ settlements     │
│─────────────────│
│ id (PK)         │
│ settlement_code │
│ employee_id     │
│ advance_id      │
│ cash_returned   │
│ amount_spent    │
│ received_by     │
└─────────────────┘
```

## Indexes and Constraints

### Primary Keys
All tables use auto-incrementing `id` as the primary key.

### Unique Constraints
- `employees.employee_code`
- `employees.email`
- `employee_advances.advance_code`
- `advance_settlements.settlement_code`

### Foreign Key Constraints

#### HR Module
- `employees.department_id` → `departments.id` (ON DELETE SET NULL)
- `leaves.employee_id` → `employees.id` (ON DELETE CASCADE)
- `leaves.leave_type_id` → `leave_types.id` (ON DELETE CASCADE)
- `leaves.approved_by` → `employees.id` (ON DELETE SET NULL)
- `deductions.employee_id` → `employees.id` (ON DELETE CASCADE)
- `deductions.leave_id` → `leaves.id` (ON DELETE SET NULL)
- `deductions.advance_id` → `employee_advances.id` (ON DELETE SET NULL)
- `employee_advances.employee_id` → `employees.id` (ON DELETE CASCADE)
- `employee_advances.issued_by` → `employees.id` (ON DELETE SET NULL)
- `advance_settlements.employee_id` → `employees.id` (ON DELETE CASCADE)
- `advance_settlements.advance_id` → `employee_advances.id` (ON DELETE SET NULL)
- `advance_settlements.received_by` → `employees.id` (ON DELETE SET NULL)

### Database Indexes

For optimal query performance, the following indexes are recommended:

```sql
-- Employees
CREATE INDEX idx_employees_department ON employees(department_id);
CREATE INDEX idx_employees_status ON employees(status);

-- Leaves
CREATE INDEX idx_leaves_employee ON leaves(employee_id);
CREATE INDEX idx_leaves_status ON leaves(status);
CREATE INDEX idx_leaves_dates ON leaves(start_date, end_date);

-- Deductions
CREATE INDEX idx_deductions_employee ON deductions(employee_id);
CREATE INDEX idx_deductions_date ON deductions(deduction_date);
CREATE INDEX idx_deductions_type ON deductions(type);

-- Advances
CREATE INDEX idx_advances_employee ON employee_advances(employee_id);
CREATE INDEX idx_advances_status ON employee_advances(status);
CREATE INDEX idx_advances_issue_date ON employee_advances(issue_date);

-- Settlements
CREATE INDEX idx_settlements_advance ON advance_settlements(advance_id);
CREATE INDEX idx_settlements_date ON advance_settlements(settlement_date);
```

## Data Types and Validation

### Enums

**leaves.status:**
- `pending` - Leave request is pending approval
- `approved` - Leave request has been approved
- `rejected` - Leave request has been rejected

**deductions.type:**
- `days` - Deduction based on number of days
- `amount` - Fixed amount deduction
- `unpaid_leave` - Deduction from unpaid leave
- `advance_recovery` - Recovery of employee advance

**employee_advances.type:**
- `cash` - Cash advance
- `salary_advance` - Advance on salary
- `petty_cash` - Petty cash advance
- `travel` - Travel-related advance
- `purchase` - Purchase-related advance

**employee_advances.status:**
- `pending` - Advance not yet settled
- `partial_settlement` - Partially settled
- `settled` - Fully settled with cash/receipts
- `settled_via_deduction` - Settled through salary deduction

**deduction_types.calculation_type:**
- `fixed` - Fixed amount deduction
- `percentage` - Percentage-based deduction

### Decimal Precision

All monetary values use `decimal(10,2)` for precise financial calculations:
- `employees.salary`
- `employees.daily_rate`
- `deductions.amount`
- `deduction_types.default_amount`
- `employee_advances.amount`
- `advance_settlements.cash_returned`
- `advance_settlements.amount_spent`

## Business Rules

1. **Employee Daily Rate**: Auto-calculated as `salary / 30` when salary is updated
2. **Deduction Amount**: If `days` is provided, amount = `days × employee.daily_rate`
3. **Leave Deduction**: Unpaid leaves automatically create a deduction record
4. **Advance Codes**: Auto-generated in format `ADV-YYYY-###` (sequential per year)
5. **Settlement Codes**: Auto-generated in format `SET-YYYY-###` (sequential per year)
6. **Advance Status**: Auto-updated based on settlement amounts
7. **Leave Balance**: Calculated by comparing total days used vs. `leave_types.max_days_per_year`

## Notes

- All timestamps use Laravel's `created_at` and `updated_at` convention
- Soft deletes are not implemented (hard deletes are used)
- The system uses Laravel's default `users` table for authentication (separate from `employees`)
- Color codes are stored in hexadecimal format (e.g., `#FF5733`)
