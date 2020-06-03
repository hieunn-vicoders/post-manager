<?php

namespace VCComponent\Laravel\Post\Test\Feature\PostTypeWithSchema;

use VCComponent\Laravel\Post\Test\Stubs\Models\WithSchemaAttributes\Post;
use VCComponent\Laravel\Post\Test\TestCase;

class PostTypeWithSchemaTestCase extends TestCase
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
        $app['config']->set('api', [
            'standardsTree'      => 'x',
            'subtype'            => '',
            'version'            => 'v1',
            'prefix'             => 'api',
            'domain'             => null,
            'name'               => null,
            'conditionalRequest' => true,
            'strict'             => false,
            'debug'              => true,
            'errorFormat'        => [
                'message'     => ':message',
                'errors'      => ':errors',
                'code'        => ':code',
                'status_code' => ':status_code',
                'debug'       => ':debug',
            ],
            'middleware'         => [
            ],
            'auth'               => [
            ],
            'throttling'         => [
            ],
            'transformer'        => \Dingo\Api\Transformer\Adapter\Fractal::class,
            'defaultFormat'      => 'json',
            'formats'            => [
                'json' => \Dingo\Api\Http\Response\Format\Json::class,
            ],
            'formatsOptions'     => [
                'json' => [
                    'pretty_print' => false,
                    'indent_style' => 'space',
                    'indent_size'  => 2,
                ],
            ],
        ]);
    }
}
