Restful query parser trait for Laravel eloquent model
=====================================================

Add helper traits for restful query.

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

Supported operators:
- equal: /users?id=1
- equal or greater than: /users?id>=1
- equal or less than: /users?id<=2
- not equal: /users?id!=1
- like: /users?id*=1

### sorts
Use `/users?sort=-role,created_at` to sort by role desc and created_at asc, comma(,) to separate.

### select fields
Use `/users?fields=name,role,created_at` to show only name, role and created_at.
Also can config `$selectableFields` to restrict selectable fields.
```
$selectableFields = ['name', 'role'];
```
Then only name and role can be selected to show.

Use `fields=count` to count results `/users?fields=count`.

### pagination
Use `/users?limit=10&offset=10` to limit 10 and offset 10 records, offset can be omitted, default 0.
Also can use `page` and `page_size`, `/users?page=2&page_size=15`, page_size can be omitted, default 15.

# Usage
## Eloquent model trait

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

## Controller trait

1. Add RestfulController trait in resource controller.
```
use RestfulControllerTrait;
```

2. Implement getModel method.
```
protected function getModel()
{
    return User::query();
}
```

Model should use RestHelperTrait to have index function.

3. Add controller to routes
```
Route::resource('/path', 'controller');
```

4. Each resource method (index, show, store, update, destroy) has three parts.
    1. parse requests
    2. handle action
    3. response

Override these template functions to change the default behaviors.

Add $creatableFields = ['field1', 'field2'] to restrict fields to store.
Add $updateableFields = ['field1', 'field2'] to restrict fields to update.

# Batch Process
Add new route for batch process.
Batch process read content body as json object.

- Get resources: 
```
{"GET": [1,2,3]}
```
Use key `GET` and id array as value. Return data or error in the same sequence as query.

- Create resources: 
```
{"CREATE": [{"name":"aaa"}, {"name":"bbb"}]}
```
Use key `CREATE` and object array as value. Return data or error in the same sequence as query.

- Update resources: 
```
{"UPDATE": {"1": {"name":"ccc"}, "2": {"name": "ddd"}}}
```
Use key `UPDATE`, id and data pairs as value. Return data or error in the same sequence as query.

- Delete resources:
```
{"DELETE": [1,2,3]}
```
Use key `DELETE` and id array as value. Return {"code": 204} or error in the same sequence as query.

## Batch Usage

1. Add `RestfulControllerTrait` in your Controller
```
class TestController extends Controller
{
    use RestfulControllerTrait;

    protected $creatableFields = ['name', 'email', 'password'];

    protected $updateableFields = ['email'];

    protected function getModel()
    {
        return User::query();
    }
}
```
2. Add route
```
Route::match(['GET', 'POST'], 'test/batch', 'TestController@batch');
```
3. Send batch query
POST /test/batch
{"GET":[1,2],"CREATE":[{"name":"aaa","email":"aa@a.com"}],"UPDATE":{"1":{"name":"bb"},"2":{"email":"b@b.com"}},"DELETE":[5,6]}

Return
{
"GET": [{"id":1, "name":"a", "email": "a@a.com"}, {"id":1, "name":"a", "email": "a@a.com"}],
"CREATE": [{"id": 3, "name":"aaa", "email":"aa@a.com"}],
"UPDATE": {"1": ["id":1, "name": "bb", "email": "a@a.com"], "2": ["id":2, "name":"a", "email":"b@b.com"],
"DELETE": [{"code":204},{"code":204}]
}
