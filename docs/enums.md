# Field Enumeration

The `Enums` trait is a really useful way to allow you to pre-define all of the valid values for a given field on a model and enforce that their values are set appropriately. This basically allows you to treat a field as a menu without the database overhead of dealing with true enum fields or lookup tables.

### Add the `DynamicMutators` and `Enums` traits to your model

```php
namespace App;

use TwoThirds\EloquentTraits\Enums;
use Illuminate\Database\Eloquent\Model;
use TwoThirds\EloquentTraits\DynamicMutators;

class Post extends Model
{
    use DynamicMutators, Enums;
    ...
}
```

### Sample Usage

Model `App\Post` has an enumerated field called `status` that we want to enforce specific values for.

```php
class Post extends Model
{
    use Enums;

    // Define all of the valid options in an array as a protected property that
    //    starts with 'enum' followed by the plural studly cased field name
    protected $enumStatuses = [
        'Draft',
        'Scheduled',
        'Published',
        'Archived'
    ];

    // Alternately, if you use an associative array, the keys can be used
    //    to set the value as well. The full string will still be stored in the database.
    /* protected $enumStatuses = [
        'dr' => 'Draft',
        'sc' => 'Scheduled',
        'pu' => 'Published',
        'ar' => 'Archived'
    ]; */
    ...
```

Once you've defined this `$enum` property on the model, any time that field is set on any instance, a validation process will run to enforce that the value is being set properly:

```php
$post = new App\Post;
$post->status = 'Something Invalid';
// Throws an InvalidEnumException
```

```php
$post = App\Post::first();
$post->status = 'Draft';
// Sets the value to Draft as expected
```

```php
// Key values will always work to set the value as well,
//   so using the non-associative array example, this will set status to 'Draft'
$post = App\Post::create([
  'status' => 0
]);

// Using the associative array example, this will set status to 'Published'
$post = App\Post::create([
  'status' => 'pu'
]);
```

Enumerations work really well in blade files too. Simply use the `getEnum` static helper:

```php
@foreach( App\Post::getEnum('status') as $key => $value)
    {{ $key }}: {{ $value }}
@endforeach
```

Or use them with the [LaravelCollective](https://laravelcollective.com/docs/5.4/html) form builder:

```php
{{ Form::select('status', App\Post::getEnum('status')) }}
```

In a database factory, it's common to need a random enum for your field:

```php
/**
 * @var \Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(App\Post::class, function (Faker\Generator $faker) {
    return [
        ...
        'status' => App\Post::randomEnum('status'),
        ...
    ];
});
```
