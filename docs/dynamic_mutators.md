# Dynamic Mutators

The `DynamicMutators` trait is used to allow other traits to dynamically hook into the Eloquent Model attribute setter and getters.

### Add the `DynamicMutators` trait to your model

```php
namespace App;

use Illuminate\Database\Eloquent\Model;
use TwoThirds\EloquentTraits\DynamicMutators;

class Post extends Model
{
    use DynamicMutators;
    ...
}
```
