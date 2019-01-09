<?php

namespace TwoThirds\Testing\Unit;

use Exception;
use TwoThirds\Testing\TestCase;
use TwoThirds\EloquentTraits\Enums;
use Illuminate\Database\Eloquent\Model;
use TwoThirds\EloquentTraits\DynamicMutators;
use TwoThirds\EloquentTraits\Exceptions\InvalidEnumException;

class EnumsTest extends TestCase
{
    /**
     * Model to test against
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->model = $this->instantiateModel();
        $this->class = get_class($this->model);
    }

    /**
     * @test
     */
    public function missingDynamicMutatorsTraitThrowsException()
    {
        try {
            $this->instantiateBadModel();
        } catch (Exception $exception) {
            $this->assertEquals(
                'The Enums trait requires the DynamicMutators trait as a dependency.',
                $exception->getMessage()
            );

            return;
        }

        $this->fail('Failed to throw excpetion when missing DynamicMutators class.');
    }

    /**
     * @test
     */
    public function validGetEnumSimpleReturnsCorrectArray()
    {
        $result = $this->class::getEnum('status');

        $this->assertInternalType('array', $result);
        $this->assertContains('Started', $result);
        $this->assertContains('In Progress', $result);
        $this->assertContains('Complete', $result);
    }

    /**
     * @test
     */
    public function randomEnumReturnsValidElement()
    {
        $element = $this->class::randomEnum('status');

        $this->assertContains($element, [
            'Started',
            'In Progress',
            'Complete',
        ]);
    }

    /**
     * @test
     */
    public function randomEnumFromUndefinedFieldReturnsFalse()
    {
        $this->assertFalse(
            $this->class::randomEnum('foobar')
        );
    }

    /**
     * @test
     */
    public function validGetEnumAssocReturnsCorrectArray()
    {
        $result = $this->class::getEnum('city');

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('om', $result);
        $this->assertEquals('Omaha', $result['om']);
        $this->assertArrayHasKey('ny', $result);
        $this->assertEquals('New York', $result['ny']);
        $this->assertArrayHasKey('sf', $result);
        $this->assertEquals('San Francisco', $result['sf']);
    }

    /**
     * @test
     */
    public function gettingAnInvalidEnumFieldReturnsFalse()
    {
        $this->assertFalse(
            $this->class::getEnum('invalid')
        );
    }

    /**
     * @test
     */
    public function whenYouSetAValidAttributeByValueItShouldTotallyWorkBruh()
    {
        $this->model->status = 'Started';
        $this->assertEquals('Started', $this->model->status);

        $this->model->status = 'In Progress';
        $this->assertEquals('In Progress', $this->model->status);

        $this->model->status = 'Complete';
        $this->assertEquals('Complete', $this->model->status);
    }

    /**
     * @test
     */
    public function whenYouSetAValidAttributeByIndexItShouldTotallyWorkBruh()
    {
        $this->model->status = 0;
        $this->assertEquals('Started', $this->model->status);

        $this->model->status = 1;
        $this->assertEquals('In Progress', $this->model->status);

        $this->model->status = 2;
        $this->assertEquals('Complete', $this->model->status);
    }

    /**
     * @test
     */
    public function whenYouSetAValidAttributeByKeyItShouldTotallyWorkBruh()
    {
        $this->model->city = 'om';
        $this->assertEquals('Omaha', $this->model->city);

        $this->model->city = 'ny';
        $this->assertEquals('New York', $this->model->city);

        $this->model->city = 'sf';
        $this->assertEquals('San Francisco', $this->model->city);
    }

    /**
     * @test
     */
    public function setInvalidAttributeByValueThrowsError()
    {
        try {
            $this->model->status = 'foobar';
        } catch (InvalidEnumException $exception) {
            $this->assertNull($this->model->status);

            return;
        }

        $this->fail('Failed to throw excpetion when setting invalid enum value');
    }

    /**
     * @test
     */
    public function setInvalidAttributeByIndexThrowsError()
    {
        try {
            $this->model->status = 5;
        } catch (InvalidEnumException $exception) {
            $this->assertNull($this->model->status);

            return;
        }

        $this->fail('Failed to throw excpetion when setting invalid enum index');
    }

    /**
     * @test
     */
    public function setInvalidAttributeByKeyThrowsError()
    {
        try {
            $this->model->city = 'aa';
        } catch (InvalidEnumException $exception) {
            $this->assertNull($this->model->status);

            return;
        }

        $this->fail('Failed to throw excpetion when setting invalid enum key');
    }

    /**
     * @test
     */
    public function getValidKeyBasic()
    {
        $this->model->status = 'Started';
        $this->assertEquals(0, $this->model->getEnumKey('status'));

        $this->model->status = 'In Progress';
        $this->assertEquals(1, $this->model->getEnumKey('status'));

        $this->model->status = 'Complete';
        $this->assertEquals(2, $this->model->getEnumKey('status'));
    }

    /**
     * @test
     */
    public function getValidKeyAssoc()
    {
        $this->model->city = 'Omaha';
        $this->assertEquals('om', $this->model->getEnumKey('city'));

        $this->model->city = 'New York';
        $this->assertEquals('ny', $this->model->getEnumKey('city'));

        $this->model->city = 'San Francisco';
        $this->assertEquals('sf', $this->model->getEnumKey('city'));
    }

    /**
     * @test
     */
    public function getUnsetKeyReturnsNull()
    {
        $this->assertNull(
            $this->model->getEnumKey('status')
        );
    }

    /**
     * @test
     */
    public function getInvalidKeyReturnsFalse()
    {
        $this->assertFalse(
            $this->model->getEnumKey('invalid')
        );
    }

    /**
     * @test
     */
    public function enumsCanBeReturnedForSelectFields()
    {
        $this->assertEquals(
            $this->class::getEnum('city'),
            $this->class::getSelectEnum('city')
        );

        $this->assertEquals(
            [
                'Started'     => 'Started',
                'In Progress' => 'In Progress',
                'Complete'    => 'Complete',
            ],
            $this->class::getSelectEnum('status')
        );
    }

    /**
     * Instantiates a model based on an anonymous model class
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function instantiateBadModel()
    {
        return new class extends Model {
            use Enums;
        };
    }

    /**
     * Instantiates a model based on an anonymous model class
     *
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function instantiateModel()
    {
        return new class extends Model {
            use DynamicMutators, Enums;

            /**
             * The attributes that are mass assignable.
             *
             * @var array
             */
            protected $fillable = [
                'status',
                'city',
            ];

            /**
             * Enumeratable values for Status
             *
             * @var array
             */
            protected $enumStatuses = [
                'Started',
                'In Progress',
                'Complete',
            ];

            /**
             * Enumeratable values for Cities
             *
             * @var array
             */
            protected $enumCities = [
                'om' => 'Omaha',
                'ny' => 'New York',
                'sf' => 'San Francisco',
            ];
        };
    }
}
