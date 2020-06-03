<?php

namespace VCComponent\Laravel\Post\Test\Unit;

use VCComponent\Laravel\Post\Test\Stubs\Models\Post;
use VCComponent\Laravel\Post\Test\TestCase;

class PostQueryTraitTestCase extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('post.namespace', 'post-management');
        $app['config']->set('post.models', [
            'post' => Post::class,
        ]);
        $app['config']->set('post.transformers', [
            'post' => \VCComponent\Laravel\Post\Transformers\PostTransformer::class,
        ]);
        $app['config']->set('post.auth_middleware', [
            'admin'    => [
                'middleware' => '',
                'except'     => [],
            ],
            'frontend' => [
                'middleware' => '',
                'except'     => [],
            ],
        ]);
    }
}
