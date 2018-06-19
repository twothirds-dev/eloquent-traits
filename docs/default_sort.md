# Default Sort

Apply the `DefaultSort` trait to your model when you want to apply a basic set of default sorts to your model.

### Add the `DefaultSort` trait to your model

```php
namespace App;

use TwoThirds\EloquentTraits\DefaultSort;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use DefaultSort;
    ...
}
```

### Sample Usage

In order for the `DefaultSort` to work, you must define a static '$defaultSort' property on the model. This needs to be an array of columns to sort by. Multiple columns are supported. With a basic array, the columns will be sorted ascending. To customize the direction, use an associative array with the column names as keys and the direction as the values.

```php
class Post extends Model
{
    use DefaultSort;

    protected static $defaultSort = [
        'name',
        'status',
    ];
}
```

```php
class Post extends Model
{
    use DefaultSort;

    protected static $defaultSort = [
        'name'   => 'desc',
        'status' => 'asc',
    ];
}
```

This sort will be applied to all queries made on the model. If you would like to make a query without sorting, simply use the `noSort` method:

```php
App\Post::noSort()->get();
```
