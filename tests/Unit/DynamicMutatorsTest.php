<?php

namespace TwoThirds\Testing\Unit;

use TwoThirds\Testing\TestCase;
use Illuminate\Database\Eloquent\Model;
use TwoThirds\EloquentTraits\DynamicMutators;

class DynamicMutatorsTest extends TestCase
{
    /**
     * @test
     */
    public function gettersAreRegistered()
    {
        $class = $this->getClass();
        $class->setTestGetter();

        $this->assertContains('testGetter', call_user_func([$class, 'getDynamicGetters']));
        $this->assertEquals('testGetter called for foobar', $class->foobar);
    }

    /**
     * @test
     */
    public function gettersDontInterruptBaseGetter()
    {
        $class = $this->getClass([
            'barbaz' => 'abc123',
        ]);
        $class->setTestGetter();

        $this->assertContains('testGetter', call_user_func([$class, 'getDynamicGetters']));
        $this->assertEquals('abc123', $class->barbaz);
    }

    /**
     * @test
     */
    public function settersAreRegistered()
    {
        $class = $this->getClass();
        $class->setTestSetter();

        $this->assertContains('testSetter', call_user_func([$class, 'getDynamicSetters']));
        $class->foobar = 'foobar';
        $this->assertEquals('testSetter set to foobar', $class->getAttributes()['foobar']);
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
            use DynamicMutators;

            protected static $unguarded = true;

            public function setTestGetter()
            {
                static::registerGetter('testGetter');
            }

            public function setTestSetter()
            {
                static::registerSetter('testSetter');
            }

            protected function testGetter($key)
            {
                if ($key === 'foobar') {
                    return 'testGetter called for foobar';
                }

                return null;
            }

            protected function testSetter($value)
            {
                return "testSetter set to $value";
            }
        };
    }
}
