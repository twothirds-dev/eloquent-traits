<?php

namespace TwoThirds\Testing\Unit;

use Exception;
use TwoThirds\Testing\TestCase;
use Illuminate\Database\Eloquent\Model;
use TwoThirds\EloquentTraits\DefaultSort;

class DefaultSortTest extends TestCase
{
    /**
     * @test
     */
    public function traitDefinesSortScope()
    {
        $model = $this->instantiateModel();

        $this->assertArrayHasKey('sort', $model->getGlobalScopes());
    }

    /**
     * @test
     */
    public function noSortRemovesScope()
    {
        $model = $this->instantiateModel();

        $this->assertArrayHasKey('sort', $model->getGlobalScopes());

        $builder = $model->noSort();

        $this->assertContains('sort', $builder->removedScopes());
    }

    /**
     * @test
     */
    public function traitThrowsErrorWithUndefinedDefault()
    {
        $model = $this->instantiateBadModel();

        try {
            $model->newQuery()->applyScopes();
        } catch (Exception $exception) {
            $this->assertInstanceOf(Exception::class, $exception);
            $this->assertEquals(
                'DefaultSort trait requires a static property called defaultSort',
                $exception->getMessage()
            );

            return;
        }

        $this->fail('Failed to catch exception on improperly defined model using DefaultSort trait');
    }

    /**
     * @test
     */
    public function emptyDefaultSortAppliesNoSorts()
    {
        $model = $this->instantiateModel([]);

        $builder = $model->newQuery()->applyScopes();

        $this->assertNull($builder->getQuery()->orders);
    }

    /**
     * @test
     */
    public function basicSortsApplyAsc()
    {
        $model = $this->instantiateModel([
            'foobar',
            'barbaz',
        ]);

        $builder = $model->newQuery()->applyScopes();

        $this->assertEquals([[
            'column'    => 'foobar',
            'direction' => 'asc',
        ], [
            'column'    => 'barbaz',
            'direction' => 'asc',
        ]], $builder->getQuery()->orders);
    }

    /**
     * @test
     */
    public function associativeSortsAppliesDirection()
    {
        $model = $this->instantiateModel([
            'barbaz' => 'asc',
            'foobar' => 'desc',
        ]);

        $builder = $model->newQuery()->applyScopes();

        $this->assertEquals([[
            'column'    => 'barbaz',
            'direction' => 'asc',
        ], [
            'column'    => 'foobar',
            'direction' => 'desc',
        ]], $builder->getQuery()->orders);
    }

    /**
     * Instantiates a model based on an anonymous model class
     *
     * @param array $defaultSort
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function instantiateModel(array $defaultSort = null)
    {
        return new class($defaultSort) extends Model {
            use DefaultSort;

            protected static $defaultSort;

            /**
             * Instantiate the model
             *
             * @param array|null $defaultSort
             */
            public function __construct(array $defaultSort = null)
            {
                parent::__construct();

                static::$defaultSort = $defaultSort;
            }
        };
    }

    /**
     * Instantiates a bad model based on an anonymous model class
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function instantiateBadModel()
    {
        return new class() extends Model {
            use DefaultSort;
        };
    }
}
