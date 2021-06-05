<?php

namespace VCComponent\Laravel\Post\Test;

use Cviebrock\EloquentSluggable\ServiceProvider;
use Dingo\Api\Provider\LaravelServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use VCComponent\Laravel\Post\Entities\Post;
use VCComponent\Laravel\Post\Providers\PostComponentProvider;
use VCComponent\Laravel\Post\Providers\PostComponentRouteProvider;
use VCComponent\Laravel\Post\Transformers\PostTransformer;
class TestCase extends OrchestraTestCase
{
    /**
     * Load package service provider
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return \VCComponent\Laravel\Post\Providers\PostComponentProvider
     */
    protected function getPackageProviders($app)
    {
        return [
            PostComponentProvider::class,
            PostComponentRouteProvider::class,
            LaravelServiceProvider::class,
            ServiceProvider::class,
        ];
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__ . '/../src/database/factories');
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:TEQ1o2POo+3dUuWXamjwGSBx/fsso+viCCg9iFaXNUA=');
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('post.namespace', 'post-management');
        $app['config']->set('post.models', [
            'post' => \VCComponent\Laravel\Post\Test\Stubs\Models\Post::class,
        ]);
        $app['config']->set('post.transformers', [
            'post' => \VCComponent\Laravel\Post\Transformers\PostTransformer::class,
        ]);
        $app['config']->set('post.auth_middleware', [
            'admin'    => [
                'middleware' => ''
            ],
            'frontend' => [
                'middleware' => ''
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
