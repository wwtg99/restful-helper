Restful query parser trait for Laravel eloquent model
=====================================================

Add filters, sorts, fields and pagination for restful query.

# Installation
```
composer require wwtg99/restful-helper
```

# Parse query
This trait parse url queries for eloquent model. 
Suppose you have a resource controller in route `/users` and connect to User model.

### filters
Add filterableFields in your model.
```
$filterableFields = ['role'];
```
Then use `/users?role=admin` to get user with role admin.

### sorts
Use `/users?sort=-role,created_at` to sort by role desc and created_at asc, comma(,) to separate.

### select fields
Use `/users?fields=name,role,created_at` to show only name, role and created_at.
Also can config `$selectableFields` to restrict selectable fields.
```
$selectableFields = ['name', 'role'];
```
Then only name and role can be selected to show.

### pagination
Use `/users?limit=10&offset=10` to limit 10 and offset 10 records, offset can be omitted, default 0.
Also can use `page` and `page_size`, `/users?page=2&page_size=15`, page_size can be omitted, default 15.

# Usage
1. Add RestHelper trait in eloquent model.
```
use RestHelperTrait;
```

2. Use index method.
```
$user = User::index()->get();
// Or, use custom inputs array
$user = User::index(request()->all())->get();
```

3. Also combine with other methods.
```
$user = User::index()->where('name', 'admin')->get();
```
