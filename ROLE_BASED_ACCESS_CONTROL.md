# Role-Based Access Control Implementation

## Overview

This document describes the role-based access control (RBAC) system implemented for the Nhiệm Vụ (Task Management) application.

## Features

### 1. **User Roles**

- **Admin**: Full access to all tasks
- **User**: Limited access to own tasks only

### 2. **Access Permissions**

#### Admin User

- ✅ View all tasks in the system
- ✅ Create tasks for themselves
- ✅ Create tasks and assign to other users
- ✅ Edit any task
- ✅ Delete any task
- ✅ Change status of any task
- ✅ View task history and assignments for all tasks
- ✅ Search across all tasks
- ✅ View all tasks in calendar

#### Regular User

- ✅ View only their own tasks (created or assigned to them)
- ✅ Create tasks for themselves only
- ❌ Cannot assign tasks to other users
- ✅ Edit only their own tasks
- ✅ Delete only their own created tasks
- ✅ Change status of only their own tasks
- ✅ View task history and assignments for their own tasks
- ✅ Search only their own tasks
- ✅ View only their own tasks in calendar

### 3. **Database Changes**

#### New Column: `users.role`

```sql
ALTER TABLE users ADD COLUMN role ENUM('admin', 'user') DEFAULT 'user';
```

Default value: `'user'`
The admin account has role set to `'admin'`

### 4. **Code Changes**

#### functions.php

**New Functions:**

- `isAdmin($userId)` - Check if user is admin
- `getUserRole($userId)` - Get user's role

**Modified Functions:**

- `getUserTasks()` - Now returns all tasks for admin, only user's tasks for regular users
- `canEditTask()` - Admin can edit any task, users can only edit their own
- `searchTasks()` - Admin searches all tasks, users search only their own
- `getTasksForCalendar()` - Admin sees all tasks, users see only their own

#### dashboard.php

- Shows all tasks for admin (with filter tabs)
- Shows only user's tasks for regular users (without filter tabs)
- Regular users see "Nhiệm vụ của bạn" (Your Tasks) header
- Admin sees filter tabs: "Tất cả nhiệm vụ", "Nhiệm vụ của tôi", "Nhiệm vụ được giao"

#### add_task.php

- Regular users cannot assign tasks to others
- Shows warning message for non-admin users about task assignment restriction
- Only admin can see/use the "assignments" field
- Regular users can only create tasks for themselves

#### edit_task.php

- Only admin or task creator can delete tasks
- Delete button only visible to admin or creator
- Better access control with clear error messages

#### task_detail.php

- Regular users cannot view tasks they're not assigned to or didn't create
- Admin can view all tasks
- Attempts to view unauthorized tasks redirect to dashboard

#### ajax_change_status.php

- Already protected by `canEditTask()` function
- Admin can change status of any task
- Users can only change status of their own tasks

#### nhiemvu.sql

- Added `role` column to users table
- All test users have `role = 'user'`
- Admin user has `role = 'admin'`

## Default Test Accounts

| Username | Email             | Password | Role  |
| -------- | ----------------- | -------- | ----- |
| admin    | admin@example.com | admin123 | admin |
| user1    | user1@example.com | pass123  | user  |
| user2    | user2@example.com | pass123  | user  |
| user3    | user3@example.com | pass123  | user  |
| user4    | user4@example.com | pass123  | user  |
| user5    | user5@example.com | pass123  | user  |

## Security Considerations

1. **Backend Validation**: All access control is enforced on the backend with PHP checks
2. **Session-based**: Uses PHP sessions to track logged-in user
3. **Database Checks**: Every action validates user permissions against the database
4. **Clear Error Messages**: Users get feedback when they try to access unauthorized resources

## Testing Recommendations

### As Admin User:

1. Login as admin/admin123
2. Dashboard shows all tasks with filter tabs
3. Can create tasks and assign to any user
4. Can edit and delete any task
5. Can view all task details
6. Can search all tasks

### As Regular User:

1. Login as user1/pass123
2. Dashboard shows only their tasks without filter tabs
3. Can create new tasks but cannot assign to others
4. Can only edit their own tasks
5. Can only delete tasks they created
6. Cannot view tasks they're not assigned to
7. Can only search their own tasks

## API Endpoints

All AJAX endpoints (like `ajax_change_status.php`) are protected by:

- Login check: `isLoggedIn()`
- Task permission check: `canEditTask($taskId, $userId)`
- Proper error responses with JSON

## Future Enhancements

1. Add role management interface for admin
2. Add more granular roles (Manager, Reviewer, etc.)
3. Add permission history logging
4. Add role-based UI customization
5. Add bulk operations for admin users
