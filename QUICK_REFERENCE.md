# Quick Reference: Role-Based Access Control

## TL;DR (Too Long; Didn't Read)

### What's New?

- **Admin users** see and can manage **ALL tasks**
- **Regular users** see and can manage **ONLY THEIR OWN tasks**

### How to Use?

#### Login as Admin (admin/admin123):

- You see ALL tasks on the dashboard
- You can create tasks for anyone
- You can edit/delete any task
- You can search ALL tasks

#### Login as User (user1-user5/pass123):

- You see only YOUR tasks
- You can create tasks (but only for yourself)
- You can edit/delete only YOUR tasks
- You can search only YOUR tasks
- You CANNOT see other users' tasks

## Database Change

```sql
ALTER TABLE users ADD COLUMN role ENUM('admin', 'user') DEFAULT 'user';
UPDATE users SET role = 'admin' WHERE username = 'admin';
```

## Code Changes at a Glance

### New Functions (functions.php)

```php
isAdmin($userId)           // Returns true if user is admin
getUserRole($userId)       // Returns 'admin' or 'user'
```

### Updated Functions (functions.php)

```php
getUserTasks($userId, $type)  // Now respects role
canEditTask($taskId, $userId) // Now respects role
searchTasks($userId, $query)  // Now respects role
getTasksForCalendar(...)      // Now respects role
```

### Dashboard Changes (dashboard.php)

```
Admin View:
├── Filter Tabs: All | My | Assigned
└── Shows ALL tasks

User View:
├── No tabs (disabled for users)
└── Shows ONLY their tasks
```

### Task Creation (add_task.php)

```
Admin:
├── Can assign to any user
└── Sees assignment field

User:
├── Cannot assign to others
├── Assignment field is HIDDEN
└── Shows info message
```

### Task Editing (edit_task.php)

```
Admin:
├── Can edit ANY task
└── Delete button always visible

User:
├── Can edit own tasks only
└── Delete button only for creator
```

## Permission Quick Check

| Feature         | Admin | User     |
| --------------- | ----- | -------- |
| View All Tasks  | ✅    | ❌       |
| View Own Tasks  | ✅    | ✅       |
| Create Tasks    | ✅    | ✅       |
| Assign Tasks    | ✅    | ❌       |
| Edit Any Task   | ✅    | ❌       |
| Edit Own Task   | ✅    | ✅       |
| Delete Any Task | ✅    | ❌       |
| Delete Own Task | ✅    | ✅       |
| Change Status   | ✅    | ✅ (own) |
| Search All      | ✅    | ❌       |
| Search Own      | ✅    | ✅       |

## Testing Commands

### Test Admin Access:

```bash
1. Go to login.php
2. Username: admin
3. Password: admin123
4. You should see ALL tasks and filter tabs
```

### Test User Access:

```bash
1. Go to login.php
2. Username: user1
3. Password: pass123
4. You should see only YOUR tasks, no filter tabs
```

## Troubleshooting

**Q: User sees "You don't have permission" message**
A: They're trying to access a task they don't own or aren't assigned to. This is correct behavior.

**Q: Admin can't assign tasks to users**
A: Make sure the username/email in the assignment field matches exactly with a user in the database.

**Q: Can't see the role column in database**
A: Run the migration: `ALTER TABLE users ADD COLUMN role ENUM('admin', 'user') DEFAULT 'user';`

**Q: Regular user should be able to edit but can't**
A: Check if they are either the creator (creator_id) or assigned (task_assignments) to the task.

## Files Changed

- ✅ nhiemvu.sql - Database schema
- ✅ functions.php - Core logic
- ✅ dashboard.php - View layer
- ✅ add_task.php - Task creation
- ✅ edit_task.php - Task editing
- ✅ task_detail.php - Task viewing
- ⚠️ ajax_change_status.php - No changes (already protected)
- ⚠️ search.php - No changes (uses updated functions)
- ⚠️ calendar.php - No changes (uses updated functions)

## Next Steps

1. **Update Database:** Run the migration SQL if you have existing data
2. **Test Access:** Login as admin and user to verify permissions
3. **Review Code:** Check the updated files to understand the implementation
4. **Monitor:** Watch for any permission errors in logs

## Support

For detailed information, see:

- `IMPLEMENTATION_SUMMARY.md` - Full details of all changes
- `ROLE_BASED_ACCESS_CONTROL.md` - Feature documentation
- `MIGRATION_GUIDE.md` - Database migration steps
