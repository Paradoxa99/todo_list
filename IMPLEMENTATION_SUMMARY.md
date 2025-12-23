# Implementation Summary: Role-Based Access Control

## What Was Done

### 1. Database Schema Update

**File:** `nhiemvu.sql`

- Added `role` column to `users` table with ENUM('admin', 'user')
- Default value: 'user'
- Admin user has role = 'admin'

### 2. Authentication & Authorization Functions

**File:** `functions.php`

- ✅ `isAdmin($userId)` - Check if user is admin
- ✅ `getUserRole($userId)` - Get user's role

### 3. Task Access Control

**File:** `functions.php`

- **Updated `getUserTasks()`**

  - Admin: Returns ALL tasks
  - Users: Returns only their own tasks (created or assigned)

- **Updated `canEditTask()`**

  - Admin: Can edit ANY task
  - Users: Can edit only tasks they created or are assigned to

- **Updated `searchTasks()`**

  - Admin: Searches across ALL tasks
  - Users: Searches only their own tasks

- **Updated `getTasksForCalendar()`**
  - Admin: Sees all tasks in calendar
  - Users: Sees only their own tasks in calendar

### 4. Dashboard Access

**File:** `dashboard.php`

- **Admin View:**

  - Shows all tasks (no filtering by default)
  - Displays 3 filter tabs: "Tất cả nhiệm vụ", "Nhiệm vụ của tôi", "Nhiệm vụ được giao"
  - Can click tabs to filter tasks

- **User View:**
  - Shows only their own tasks
  - No filter tabs (all view disabled)
  - Clear heading: "Nhiệm vụ của bạn" (Your Tasks)

### 5. Task Creation Restrictions

**File:** `add_task.php`

- **Admin:**

  - Can create tasks
  - Can assign to ANY user
  - Sees full assignment field

- **User:**
  - Can create tasks (for themselves only)
  - CANNOT assign to other users
  - Assignment field is HIDDEN
  - Shows info message: "Chỉ Admin có thể giao nhiệm vụ cho người khác"

### 6. Task Editing & Deletion

**File:** `edit_task.php`

- **Admin:**

  - Can edit any task
  - Can delete any task
  - Delete button always visible

- **User:**
  - Can edit only tasks they created or are assigned to
  - Can delete only tasks they CREATED
  - Delete button only visible to creator/admin
  - Gets error message if trying to delete unauthorized task

### 7. Task Detail Protection

**File:** `task_detail.php`

- **Admin:**

  - Can view all task details
  - Can edit any task

- **User:**
  - Can view only:
    - Tasks they CREATED
    - Tasks they are ASSIGNED to
  - Attempting to view unauthorized tasks → redirects to dashboard
  - Can edit button only for allowed tasks

### 8. Status Change Permission

**File:** `ajax_change_status.php`

- Already protected by `canEditTask()` function
- Uses same permission logic as edit functionality

## User Permissions Matrix

| Action            | Admin  | User (Creator) | User (Assigned) | User (Other) |
| ----------------- | ------ | -------------- | --------------- | ------------ |
| View Dashboard    | ✅ All | ✅ Own         | ✅ Own          | ❌           |
| View Task Details | ✅ All | ✅             | ✅              | ❌           |
| Create Task       | ✅     | ✅             | ✅              | ✅           |
| Assign to Others  | ✅     | ❌             | ❌              | ❌           |
| Edit Task         | ✅ All | ✅             | ✅              | ❌           |
| Delete Task       | ✅ All | ✅             | ❌              | ❌           |
| Change Status     | ✅ All | ✅             | ✅              | ❌           |
| Search All Tasks  | ✅     | ❌ (Own)       | ❌ (Own)        | ❌ (Own)     |
| View Calendar All | ✅     | ❌ (Own)       | ❌ (Own)        | ❌ (Own)     |

## Files Modified

1. **nhiemvu.sql** - Added role column to users table
2. **functions.php** - Added role functions and updated task access logic
3. **dashboard.php** - Updated to show different views based on role
4. **add_task.php** - Restricted task assignment to admin only
5. **edit_task.php** - Added better permission checks
6. **task_detail.php** - Added access control for viewing tasks

## New Helper Functions

```php
// Check if user is admin
isAdmin($userId)

// Get user's role
getUserRole($userId)
```

## Default Credentials

| User  | Email             | Password | Role  |
| ----- | ----------------- | -------- | ----- |
| admin | admin@example.com | admin123 | admin |
| user1 | user1@example.com | pass123  | user  |
| user2 | user2@example.com | pass123  | user  |
| user3 | user3@example.com | pass123  | user  |
| user4 | user4@example.com | pass123  | user  |
| user5 | user5@example.com | pass123  | user  |

## Testing Workflow

### Test as Admin (admin/admin123):

1. ✅ Login as admin
2. ✅ See all tasks on dashboard
3. ✅ See filter tabs
4. ✅ Create task and assign to user1
5. ✅ Edit any task
6. ✅ Delete any task
7. ✅ Change status of any task
8. ✅ View all task details
9. ✅ Search finds all tasks

### Test as User (user1/pass123):

1. ✅ Login as user1
2. ✅ See only their own tasks
3. ✅ No filter tabs visible
4. ✅ Create new task (but cannot assign)
5. ✅ Edit task they created
6. ✅ Delete task they created
7. ✅ Change status of their tasks
8. ✅ Cannot view tasks from other users
9. ✅ Cannot delete tasks assigned to them (only edit)
10. ✅ Search returns only their tasks

## Security Features

✅ Backend validation on every action
✅ Session-based access control
✅ Database permission checks
✅ Clear error messages
✅ Redirect unauthorized access attempts
✅ No sensitive data exposure
✅ HTML encoding for XSS prevention
