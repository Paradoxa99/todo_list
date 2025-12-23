# Database Migration Guide

## If You Already Have an Existing Database

If you already have the `nhiemvu` database set up, follow these steps to add role-based access control:

### Step 1: Add the Role Column

Run this SQL command in phpMyAdmin or MySQL command line:

```sql
ALTER TABLE users ADD COLUMN role ENUM('admin', 'user') DEFAULT 'user';
```

### Step 2: Set Admin Role

Set the admin user (typically ID 1) to have admin role:

```sql
UPDATE users SET role = 'admin' WHERE username = 'admin';
```

Or if you know the ID:

```sql
UPDATE users SET role = 'admin' WHERE id = 1;
```

### Step 3: Verify

Check that the role column was added correctly:

```sql
SELECT id, username, email, role FROM users;
```

You should see output like:

```
| id | username | email | role |
|----|----------|-------|------|
| 1  | admin    | admin@example.com | admin |
| 2  | user1    | user1@example.com | user |
| 3  | user2    | user2@example.com | user |
```

### Step 4: Update Code

Make sure you have the latest versions of:

- `functions.php` - Contains new `isAdmin()` and role-checking logic
- `dashboard.php` - Updated to show different views for admin vs users
- `add_task.php` - Updated to restrict task assignment to admin only
- `edit_task.php` - Updated with better permission checks
- `task_detail.php` - Updated to prevent unauthorized access
- All other PHP files - Use the updated functions

## Fresh Installation

If you're starting fresh, simply run the updated `nhiemvu.sql` file which includes the role column from the start.

```sql
source nhiemvu.sql;
```

## Verification Checklist

After migration, verify:

- [ ] Admin can see all tasks on dashboard
- [ ] Admin sees filter tabs (all, my, assigned)
- [ ] Admin can create and assign tasks to other users
- [ ] Admin can edit any task
- [ ] Admin can delete any task
- [ ] Regular users see only their own tasks
- [ ] Regular users don't see filter tabs
- [ ] Regular users cannot assign tasks to others
- [ ] Regular users cannot edit tasks they don't own
- [ ] Regular users cannot view unauthorized task details
- [ ] Status changes work correctly for authorized users

## Rollback (If Needed)

If you need to remove the role column:

```sql
ALTER TABLE users DROP COLUMN role;
```

But note that the code will need to be reverted to the previous version as well.
