# Post Manager Package for Laravel

- [Post Manager Package for Laravel](#post-manager-package-for-laravel)
  - [Installation](#installation)
    - [Composer](#composer)
    - [Service Provider](#service-provider)
    - [Config and Migration](#config-and-migration)
    - [Environment](#environment)
  - [Configuration](#configuration)
    - [URL namespace](#url-namespace)
    - [Model and Transformer](#model-and-transformer)
    - [Auth middleware](#auth-middleware)
  - [Post-type](#post-type)
  - [Routes](#routes)


Post management package for managing post in laravel framework

## Installation

### Composer

To include the package in your project, Please run following command.

```
composer require vicoders/postmanager
```

### Service Provider

In your  `config/app.php`  add the following Service Providers to the end of the  `providers`  array:

```php
'providers' => [
        ...
    VCComponent\Laravel\Post\Providers\PostComponentProvider::class,
    VCComponent\Laravel\Post\Providers\PostComponentRouteProvider::class,
],
```

### Config and Migration

Run the following commands to publish configuration and migration files.

```
php artisan vendor:publish --provider="VCComponent\Laravel\Post\Providers\PostComponentProvider"
php artisan vendor:publish --provider="Dingo\Api\Provider\LaravelServiceProvider"
php artisan vendor:publish --provider "Prettus\Repository\Providers\RepositoryServiceProvider"
```
Create tables:

```
php artisan migrate
```

### Environment

In `.env` file, we need some configuration.

```
API_PREFIX=api
API_VERSION=v1
API_NAME="Your API Name"
API_DEBUG=false
```

## Configuration

### URL namespace

To avoid duplication with your application's api endpoints, the package has a default namespace for its routes which is  `post-management`. For example:

    {{url}}/api/post-management/admin/posts
You can modify the package url namespace to whatever you want by modifying the `POST_COMPONENT_NAMESPACE` variable in `.env` file.

    POST_COMPONENT_NAMESPACE="your-namespace"

### Model and Transformer

You can use your own model and transformer class by modifying the configuration file `config\post.php`

```php
'models'          => [
    'post' => App\Entities\Post::class,
],

'transformers'    => [
    'post' => App\Transformers\PostTransformer::class,
],
```
Your `Post` model class must implements `VCComponent\Laravel\Post\Contracts\PostSchema` and `VCComponent\Laravel\Post\Contracts\PostManagement`

```php
<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;
use VCComponent\Laravel\Post\Contracts\PostManagement;
use VCComponent\Laravel\Post\Contracts\PostSchema;
use VCComponent\Laravel\Post\Traits\PostManagementTrait;
use VCComponent\Laravel\Post\Traits\PostSchemaTrait;

class Post extends Model implements Transformable, PostSchema, PostManagement
{
    use TransformableTrait, PostSchemaTrait, PostManagementTrait;

    const STATUS_PENDING = 1;
    const STATUS_ACTIVE  = 2;

    protected $fillable = [
        'title',
        'description',
        'content',
    ];
}
```

### Auth middleware

Configure auth middleware in configuration file `config\post.php`

```php
'auth_middleware' => [
        'admin'    => [
            'middleware' => 'jwt.auth',
            'except'     => ['index'],
        ],
        'frontend' => [
            'middleware' => 'jwt.auth',
            'except'     => ['index'],
        ],
],
```

## Post-type

By default, the package provide `posts` post-type. If you want to define additional `post-type`, feel free to add the `post-type` name to `postTypes()` method in your `Post` model class.
```php
public function postTypes()
{
    return [
        'about',
    ];
}
```
If your `post-type` has additional fields, just add the `schema` of your `post-type` to the `Post` model class.

```php
public function aboutSchema()
{
    return [
        'information' => [
            'type' => 'text',
            'rule' => ['nullable'],
        ],
        'contact' => [
            'type' => 'text',
            'rule' => ['required'],
        ],
    ];
}
```
By default, the package will show you a default view. If you want to change the view `post-type` name to `postTypes()`, just add the `view` of your `post-type` to the `Post` controller class.

```php
public function viewAbout()
{
    return 'pages.about-view';
}
```

## Routes

The api endpoint should have these format:
| Verb   | URI                                            |
| ------ | ---------------------------------------------- |
| GET    | /api/{namespace}/admin/{post-type}             |
| GET    | /api/{namespace}/admin/{post-type}/{id}        |
| POST   | /api/{namespace}/admin/{post-type}             |
| PUT    | /api/{namespace}/admin/{post-type}/{id}        |
| DELETE | /api/{namespace}/admin/{post-type}/{id}        |
| PUT    | /api/{namespace}/admin/{post-type}/status/bulk |
| PUT    | /api/{namespace}/admin/{post-type}/status/{id} |
| ----   | ----                                           |
| GET    | /api/{namespace}/{post-type}                   |
| GET    | /api/{namespace}/{post-type}/{id}              |
| POST   | /api/{namespace}/{post-type}                   |
| PUT    | /api/{namespace}/{post-type}/{id}              |
| DELETE | /api/{namespace}/{post-type}/{id}              |
| PUT    | /api/{namespace}/{post-type}/status/bulk       |
| PUT    | /api/{namespace}/{post-type}/status/{id}       |
