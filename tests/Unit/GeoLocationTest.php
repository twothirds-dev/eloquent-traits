<?php

namespace TwoThirds\Testing\Unit;

use phpmock\phpunit\PHPMock;
use TwoThirds\Testing\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use TwoThirds\EloquentTraits\GeoLocation;

class GeoLocationTest extends TestCase
{
    use PHPMock;

    /**
     * @test
     */
    public function hasWithGeoScope()
    {
        $class = $this->getClass();

        $builder = $class->withGeo()->applyScopes();

        $this->assertContains(
            'customers.*',
            $builder->getQuery()->columns
        );

        $this->assertContains(
            'astext(location) as location',
            $builder->getQuery()->columns
        );
    }

    /**
     * @test
     */
    public function returnsProperLocationAddress()
    {
        $class = $this->getClass([
            'address' => '1234 Main street',
        ]);

        $this->assertEquals('1234 Main street is my address', $class->locationAddress());
    }

    /**
     * @test
     */
    public function detectsChangesOnSaving()
    {
        $class = $this->getClass([
            'address'  => '1234 Original street',
            'location' => [10, 20],
        ]);

        // Pretend that this is coming from the database
        $class->syncOriginal();

        // Set a new address and presume that the google api will get called with it
        $class->address = '2345 New street';
        $this->googleApiShouldBeCalled($class, 'OK', [123, 234]);

        $class->fireSaving();
        $this->assertEquals([123, 234], $class->location);
    }

    /**
     * @test
     */
    public function getsNullLocation()
    {
        $class = $this->getClass([
            'location' => null,
        ]);

        $this->assertNull($class->location);
    }

    /**
     * @test
     */
    public function parsesPointLocation()
    {
        $class = $this->getClass([
            'location' => 'POINT(1234 2345)',
        ]);

        $this->assertEquals([1234, 2345], $class->location);
    }

    /**
     * @test
     */
    public function setsArrayLocation()
    {
        $class = $this->getClass();

        $class->location = [1234, 2345];

        $location = $class->getAttributes()['location'];

        $this->assertInstanceOf(Expression::class, $location);
        $this->assertEquals('POINT(1234,2345)', $location->getValue());
    }

    /**
     * @test
     */
    public function setsStringLocation()
    {
        $class = $this->getClass();

        $class->location = 'POINT(1234,2345)';

        $location = $class->getAttributes()['location'];

        $this->assertEquals('POINT(1234,2345)', $location);
    }

    /**
     * @test
     */
    public function scopeDistanceFromByString()
    {
        $builder = $this->getClass()
            ->newQuery()
            ->distanceFrom('1234,2345');

        $this->assertInstanceOf(Expression::class, $builder->getQuery()->columns[2]);
        $this->assertEquals('st_distance(location,POINT(1234,2345)) as distance', $builder->getQuery()->columns[2]->getValue());

        $this->assertContains(
            [
                'type' => 'Raw',
                'sql'  => '(0 - distance) DESC',
            ],
            $builder->getQuery()->orders
        );
    }

    /**
     * @test
     */
    public function scopeDistanceFromByArray()
    {
        $builder = $this->getClass()
            ->newQuery()
            ->distanceFrom([1234, 2345]);

        $this->assertInstanceOf(Expression::class, $builder->getQuery()->columns[2]);
        $this->assertEquals('st_distance(location,POINT(1234,2345)) as distance', $builder->getQuery()->columns[2]->getValue());

        $this->assertContains(
            [
                'type' => 'Raw',
                'sql'  => '(0 - distance) DESC',
            ],
            $builder->getQuery()->orders
        );
    }

    /**
     * @test
     */
    public function locationUpdateCanBeDisabled()
    {
        $model = $this->getClass([
            'location' => 'foobar',
        ]);

        $this->googleApiShouldntBeCalled();

        $model::$autoLocationUpdate = false;

        $model->updateLocation();
    }

    /**
     * Return a properly configured class
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     *
     * @param array $attributes
     */
    protected function getClass(array $attributes = [])
    {
        return new class($attributes) extends Model {
            use GeoLocation;

            protected $table = 'customers';

            protected static $unguarded = true;

            public function locationAddress() : string
            {
                return $this->address . ' is my address';
            }

            public function fireSaving()
            {
                $this->fireModelEvent('saving');
            }
        };
    }

    /**
     * Mocks out the expected call to the google maps api and returns tue expected
     *
     * @param /Illuminate\Database\Eloquent\Model $model
     * @param string $status
     * @param array $location
     *
     * @return $this
     */
    protected function googleApiShouldBeCalled($model, $status = 'OK', $location = [123, 234])
    {
        $this->getFunctionMock('TwoThirds\EloquentTraits', 'file_get_contents')
            ->expects($this->once())
            ->with(
                'http://maps.google.com/maps/api/geocode/json?address=' . urlencode($model->locationAddress())
            )
            ->willReturn(json_encode([
                'status'  => $status,
                'results' => [['geometry' => [
                    'location' => ['lat' => $location[0], 'lng' => $location[1]],
                ]]],
            ]));

        return $this;
    }

    /**
     * Asserts that the google api is not fetched
     *
     * @return $this
     */
    protected function googleApiShouldntBeCalled()
    {
        $this->getFunctionMock('TwoThirds\EloquentTraits', 'file_get_contents')
            ->expects($this->never());

        return $this;
    }
}
