<?php

namespace TwoThirds\Testing\Unit;

use Mockery;
use Mockery\MockInterface;
use TwoThirds\Testing\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use TwoThirds\EloquentTraits\DeferredRelationships;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DeferredRelationshipsTest extends TestCase
{
    /**
     * Instance of the model we're testing against
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Mocked Relationship
     *
     * @var \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    protected $relationship;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->relationship = Mockery::mock(BelongsToMany::class);
        $this->model        = $this->instantiateModel($this->relationship);
    }

    /**
     * @test
     */
    public function modelSaveRunsDeferredRelationships()
    {
        $this->relationship
            ->shouldReceive('attach')
            ->with([1, 2, 3]);

        $this->model->attach('relation', [1, 2, 3]);

        $this->model->pretendSave();

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function togglePassesThroughRelationshipToggle()
    {
        $this->relationship
            ->shouldReceive('toggle')
            ->with('arg1', 'arg2', 'arg3');

        $this->model->toggle('relation', 'arg1', 'arg2', 'arg3');

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function detachPassesThroughRelationshipDetach()
    {
        $this->relationship
            ->shouldReceive('detach')
            ->with('arg1', 'arg2', 'arg3');

        $this->model->detach('relation', 'arg1', 'arg2', 'arg3');

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function attachOnNonExistantModelDefersRelationship()
    {
        $this->relationship
            ->shouldNotReceive('attach');

        $this->model->attach('relation', [1, 2, 3]);

        $relationships = $this->model->getDeferredRelationships();

        $this->assertArrayHasKey('relation', $relationships['attach']);
        $this->assertEquals([[1, 2, 3]], $relationships['attach']['relation']);
    }

    /**
     * @test
     */
    public function attachOnExistingModelAttachesRelationship()
    {
        $this->model->pretendExists();

        $this->relationship
            ->shouldReceive('attach')
            ->with([1, 2, 3]);

        $this->model->attach('relation', [1, 2, 3]);

        $relationships = $this->model->getDeferredRelationships();
        $this->assertArrayNotHasKey('relation', $relationships['attach']);
    }

    /**
     * @test
     */
    public function syncOnNonExistantModelDefersRelationship()
    {
        $this->relationship
            ->shouldNotReceive('sync');

        $this->model->sync('relation', [1, 2, 3]);

        $relationships = $this->model->getDeferredRelationships();

        $this->assertArrayHasKey('relation', $relationships['sync']);
        $this->assertEquals([[1, 2, 3]], $relationships['sync']['relation']);
    }

    /**
     * @test
     */
    public function syncOnExistingModelSyncsRelationship()
    {
        $this->model->pretendExists();

        $this->relationship
            ->shouldReceive('sync')
            ->with([1, 2, 3]);

        $this->model->sync('relation', [1, 2, 3]);

        $relationships = $this->model->getDeferredRelationships();
        $this->assertArrayNotHasKey('relation', $relationships['sync']);
    }

    /**
     * @test
     */
    public function syncWithoutDetachingOnNonExistantModelDefersRelationship()
    {
        $this->relationship
            ->shouldNotReceive('syncWithoutDetaching');

        $this->model->syncWithoutDetaching('relation', [1, 2, 3]);

        $relationships = $this->model->getDeferredRelationships();

        $this->assertArrayHasKey('relation', $relationships['syncWithoutDetaching']);
        $this->assertEquals([[1, 2, 3]], $relationships['syncWithoutDetaching']['relation']);
    }

    /**
     * @test
     */
    public function syncWithoutDetachingOnExistingModelSyncsWithoutDetachingRelationship()
    {
        $this->model->pretendExists();

        $this->relationship
            ->shouldReceive('syncWithoutDetaching')
            ->with([1, 2, 3]);

        $this->model->syncWithoutDetaching('relation', [1, 2, 3]);

        $relationships = $this->model->getDeferredRelationships();
        $this->assertArrayNotHasKey('relation', $relationships['syncWithoutDetaching']);
    }

    /**
     * @test
     */
    public function syncHasManyOnNonExistentModelDefersRelationship()
    {
        $this->relationship = Mockery::mock(HasMany::class);
        $this->relationship
            ->shouldNotReceive('sync');

        $this->model->sync('relation', [1, 2, 3]);

        $relationships = $this->model->getDeferredRelationships();

        $this->assertArrayHasKey('relation', $relationships['sync']);
        $this->assertEquals([[1, 2, 3]], $relationships['sync']['relation']);
    }

    /**
     * Instantiates a model based on an anonymous model class
     *
     * @param \Illuminate\Database\Eloquent\Relations\Relation $relationship
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function instantiateModel(Relation $relationship)
    {
        return new class($relationship) extends Model {
            use DeferredRelationships;

            /**
             * The mocked relationship that we need to test against
             *
             * @var \Mockery\MockInterface
             */
            protected $mockedRelationship;

            /**
             * Instantiate the model
             *
             * @param \Mockery\MockInterface $mockedRelationship
             */
            public function __construct(MockInterface $mockedRelationship)
            {
                parent::__construct();

                $this->mockedRelationship = $mockedRelationship;
            }

            /**
             * The pretend relationship
             *
             * @return \Mockery\MockInterface
             */
            public function relation()
            {
                return $this->mockedRelationship;
            }

            /**
             * Gets the array of deferred relationships from the trait
             *
             * @return array
             */
            public function getDeferredRelationships()
            {
                return $this->deferredRelationships;
            }

            /**
             * Pretend the model has been saved by setting the exists flag
             *
             * @return void
             */
            public function pretendExists()
            {
                $this->exists = true;
            }

            /**
             * Pretend to save the model by triggering the 'saved' event
             *
             * @return void
             */
            public function pretendSave()
            {
                $this->fireModelEvent('saved', false);
            }
        };
    }
}
