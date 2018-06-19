## DeferredRelationships

The DeferredRelationships Trait is an eloquent model add-on that will allow you to attach / sync related models before your model exists. When creating a model, normally you would have to persist to the database before you can perform an attach / sync operation on a relationship. This allows you to defer any operations until after save.

One of the biggest benefits to this is that when combined with simple attribute setters, you can create a model with all of it's related models using data directly from a form request.

<a id="add-the-deferredrelationships-trait-to-your-model"></a>
### Add the DeferredRelationships trait to your model

```php
use TwoThirds\EloquentTraits\DeferredRelationships;

class Project extends Model
{
    use DeferredRelationships;
    ...
}
```

<a id="sample-usage-1"></a>
### Sample Usage

The Project model has a ManyToMany relationship to the Users model via `owners`

```php
class Project extends Model
{
    use DeferredRelationships;

    public function owners()
    {
        return $this->belongsToMany('App\User');
    }
}
```

Lets suppose that you have a form request that submits something like this:

```
[
    'name'        => 'Awesome New Project',
    'description' => 'Lorem ipsum dolor sit amet.',
    'owners'      => [10, 25, 32],
]
```

Traditionally, you would have to do something like this in a controller:

```php
class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, $rules);

        $project = Project::create($request->only('name', 'description'));

        $project->owners()->sync($request->owners);

        return redirect(route('project.show', $project));
    }
}
```

Instead, with DeferredRelationships, we can create an attribute setter for owners that will sync the relationship as soon as the save event happens:


```php
class Project extends Model
{
    use DeferredRelationships;

    public function owners()
    {
        return $this->belongsToMany('App\User');
    }

    public function setWatchersAttribute(array $owners)
    {
        $this->sync('owners', $owners);
    }
}
```

Then in our controller we can simply validate our data and create the project with it.

```php
class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $project = Project::create($this->validate($request, $rules));

        return redirect(route('project.show', $project));
    }
}
```

The primary benefit for this is that we're allowed to separate concerns and let the Project determine how it manages it's relationships. If we were to have a lot of different ways to add / update owners on a project, then all of that logic would be scattered throughout different controller methods.

