# Geo Location

The `GeoLocation` trait automatically maintains a [MySql Point](https://dev.mysql.com/doc/refman/5.7/en/gis-class-point.html) field based on an address. The `DistanceFrom` scope also allows you to sort records by distance from a provided point on the map.

### Add a Point Field

In a database migration, you will need to add a 'location' point field to your table.

```
DB::statement('ALTER TABLE customers ADD location POINT AFTER zipcode');
```

### Add the `GeoLocation` trait to your model

The `GeoLocation` trait needs a `locationAddress` method defined on your model. This method should return a string that represents the mailing address that will be used to lookup the latitude and longitude.

```php
namespace App;

use TwoThirds\EloquentTraits\GeoLocation;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use GeoLocation;

    public function locationAddress() : string
    {
        return sprintf(
            '%s %s, %s %s',
            $this->address,
            $this->city,
            $this->state,
            $this->zipcode
        );
    }
    ...
}
```
### Using your Google Api Key

For best results, you'll want to apply for an [api key](https://developers.google.com/maps/documentation/geocoding/get-api-key) and then add the following to your `config/services.php` file:

```php
<?php
return [
    'google-maps' => [
        'api-key' => env('GOOGLE_MAPS_API_KEY'),
    ],
];
```

### Automatic maintenance of the location field

Once setup, your model will automatically update the point field latitude and longitude whenever the value of `locationAddress` changes.

```php
$customer = App\Customer::find(1);

$customer->address = '123 Main St.';

$customer->save();
```

If you don't want the location to be updated, you can set the `$autoLocationUpdate` static property to `false`

```php
App\Customer::$autoLocationUpdate = true;
```

### Getting the latitude and longitude from the model

Laravel doesn't know how to handle Point fields natively, so if you would like to get the values of the latitude and longitude, you can use the `withGeo` scope. This will make the location field output text.

```php
echo App\Customer::withGeo()->find(1)->location;
// ["41.2812977", "-95.9424159"]

echo App\Customer::find(1)->location;
// null
```

### Setting latitude and longitude directly

There is a setter on the `location` field that allows setting the field by string or array:

```php
$customer->location = ['41.2812977', '-95.9424159'];
// or
$customer->location = '41.2812977, -95.9424159';
```

### Sorting your records by distance

The `distanceFrom` scope allows you to sort your records by distance from a point on the map. You can pass either an array or a string.

```php
App\Customer::distanceFrom(['41.2812977', '-95.9424159'])->get();
// or
App\Customer::distanceFrom('41.2812977, -95.9424159')->get();
```

### Using with SqLite

SqLite doesn't have all of the geolocation functions built in, so in order for this trait to not fail completely, we have to fake them. This is primarily for testing purposes. Just add the following package to your development dependencies:

```
composer require --dev anthonymartin/geo-location
```

NOTE: This does not add proper geo spatial support to SqLite in any way. This just prevents your tests from going down in a [blaze of glory](https://www.youtube.com/watch?v=MfmYCM4CS8o).
