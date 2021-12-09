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
  - [Query functions provide](#query-functions-provide)
    - [Repository](#repository)
        - [List of query functions](#list-of-query-functions)
        - [Use](#use)
        - [For example](#for-example)
    - [Entity](#entity)
        - [List of query functions-Entity](#list-of-query-functions-entity)
        - [Use-Entity](#use-entity)
        - [For example-Entity](#for-example-entity)
  - [Table of contents](#table-of-contents)
    - [List of TOC functions](#list-of-toc-functions)
    - [Using of TOC](#using-of-toc)
    - [Example of TOC](#example-of-toc)
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
## Query functions provide
### Repository
#### List of query functions
Find  By Field 
```php
public function findByField($field, $value = null, $type = 'posts')
```
Get posts of post type by array of conditions
```php
public function findByWhere(array $where, $type = 'posts', $number = 10, $order_by = 'order', $order = 'asc');

public function findByWherePaginate(array $where, $type = 'posts', $number = 10, $order_by = 'order', $order = 'asc');
// Get posts of post type by array of conditions with paginate
```
Get image list of the article in size
```php
public function getPostMedias( $post_id, $image_dimension='')
```
Get all posts by post type
```php
public function getPostsAll( $type = 'posts') 
```
Get posts by id
```php
public function getPostByID($post_id)
```
Get article link
```php
public function getPostUrl($post_id)
```
Get list of related articles posts
```php
public function getRelatedPosts($post_id, array $where, $number = 10, $order_by = 'order', $order = 'asc', $columns = ['*']);

public function getRelatedPostsPaginate($post_id, array $where, $number = 10, $order_by = 'order', $order = 'asc', $columns = ['*']);
// get a list of related posts with pagination
```
Get list of posts of a category
```php
public function getPostsWithCategory($category_id, array $where, $number = 10, $order_by = 'order', $order = 'asc', $columns = ['*']);
public function getPostsWithCategoryPaginate($category_id, array $where, $number = 10, $order_by = 'order', $order = 'asc', $columns = ['*']);
// get list of articles of a pagination category
```
Search articles by keyword
```php
public function getSearchResult($key_word, array $list_field = ['title'],array $where, $category_id = 0,$number = 10,$order_by = 'order', $order = 'asc', $columns = ['*']);
public function getSearchResultPaginate($key_word, array $list_field  = ['title'], array $where, $category_id = 0, $number = 10, $order_by = 'order', $order = 'asc', $columns = ['*']);
// Search articles by keyword with pagination
```
#### Use
At controller use `PostRepository` and add function `__construct`
```php
use VCComponent\Laravel\Post\Repositories\PostRepository;
```
```php
public function __construct(PostRepository $postRepo)
{
    $this->postRepo = $postRepo;
}
```
#### For example
```php
$postField = $this->postRepo->findByField('title', 'about');
// get the post of the post type posts with the title about

$postWhere = $this->postRepo->findByWhere(['status' => 1, 'is_hot' => 1]);
// get posts belonging to post type posts with field is_hot = 1 and status = 1

$postWhere = $this->postRepo->findByWhere(['status' => 1, 'is_hot' => 1]);
// get posts belonging to post type posts with field is_hot = 1 and status = 1 with paginate

$postsType = $this->postRepo->getPostsAll('about');
// get articles belonging to post type about

$postById = $this->postRepo->getPostByID(1);
// get posts with id = 1
$postById = $this->postRepo->getPostMedias(2)
// get a list of images of posts with id = 2
$postUrl = $this->postRepo->getPostUrl(1);
// get the post link with id = 1

$postRelated = $this->postRepo->getRelatedPosts(2, ['type' => 'about'], 0);
// get all posts related to post with id = 2 and belong to post type about 

$postRelatedPaginate = $this->postRepo->getRelatedPosts(2, ['type' => 'about']);
// get all posts that are related to post with id = 2 and belong to post type about with pagination

$postWithCategory = $this->postRepo->getPostsWithCategory(2, ['status' => 1]);
// get all posts in category id = 2 and posts with field status = 1

$postWithCategoryPaginate = $this->postRepo->getPostsWithCategoryPaginate(2, ['status' => 1]);
// get all posts of category id = 2 and posts with field status = 1 with pagination

$postResult = $this->postRepo->getSearchResult('hi', ['title','content'],['type' => 'posts']);
// get all posts that contain "hi" in title or content field and belong to post type posts

$postResult = $this->postRepo->getSearchResult('hi', ['title','content'],['status' => 1],3);
// get all posts that contain "hi" in title or content field and have status = 1 field and belong to category with id = 3

$postResult = $this->postRepo->getSearchResultPaginate('hi', ['title','content'],['status' => 1],3);
// get all posts that contain "hi" in title or content field and have status = 1 field and belong to category with id = 3 with pagination
```

### Entity
#### List of query functions-Entity
Scope a query to only include posts of a given type.
```php
public function scopeOfType($query, $type)
```

Get post collection by type.
```php
public static function getByType($type = 'posts')
```

Get post by type with pagination.
```php
public static function getByTypeWithPagination($type = 'posts', $per_page = 15)
```

Get post by type and id.
```php
public static function findByType($id, $type = 'posts')
```

Get post meta data.
```php
public function getMetaField($key)
```

Scope a query to only include hot posts.
```php
public function scopeIsHot($query)
```

Scope a query to only include publisded posts.
```php
public function scopeIsPublished($query)
```

Scope a query to sort posts by order column.
```php
public function scopeSortByOrder($query, $order = 'desc')
```

Scope a query to sort posts by published_date column.
```php
public function scopeSortByPublishedDate($query, $order = 'desc')
```

Scope a query to search posts of given key word. This function is also able to scope with categories, or tags.
```php
public function scopeOfSearching($query, $search, $with_category = false, $with_tag = false)
```

Scope a query to include related posts. This function is also able to scope with categories, or tags.
```php
public function scopeOfRelatingTo($query, $post, $with_category = false, $with_tag = false)
```
#### Use-Entity
Use Trait.
```php
namespace App\Model;

use VCComponent\Laravel\Post\Traits\PostQueryTrait;

class Post 
{
    use PostQueryTrait;
    \\
}
```

Extend `VCComponent\Laravel\Post\Entities\Post` Entity.
```php
namespace App\Model;

use VCComponent\Laravel\Post\Entities\Post as BasePost;

class Post extends BasePost
{
    \\
}
```
#### For example-Entity

```php
Post::ofType('posts')->IsPublised()->isHost()->search('Hello world', false, true)->sortByPublishedDate('desc')->paginate(12);
```

## Table of contents
The package provides a function for `posts` entity to get table of contents by posts content.

### List of TOC functions
```php 
$toc = $this->getTableOfContents(2, 4);
// get a html source code of the table of contents which has top level header equals to <h2> and has 4 levels depth
```

### Using of TOC
At `Post` entity extend BasePost
```php
use VCComponent\Laravel\Post\Entities\Post as BasePost;
// [...]
class Post extends BasePost
{
   // [...]
}
```
Or import TableOfContentsTrait and use it in `Post` entity
```php
use VCComponent\Laravel\Post\Traits\TableOfContentsTrait;

class Post
{
    use TableOfContentsTrait;
}
```
The package also provide a styled view blade that can be included in post-detail page
```php
@include('post-manager::table-of-contents', ['custom_class' => 'default_table_of_contents','top_level' => 1, 'depth' => 6])
//Variables are not necessary to be provided, all variables has their default value.
```
Remember publish the `PostComponentProvider`, and import scss file in the main css to use the default style css.
```cmd
php artisan vendor:publish --provider="VCComponent\Laravel\Post\Providers\PostComponentProvider"
```
```sass
@import "./table_of_contents/table_of_contents.scss";
```
### Example of TOC
```php
@include('post-manager::table-of-contents', ['custom_class' => 'default_table_of_contents','top_level' => 1, 'depth' => 6])
//get a table of contents with header is h1 and 6 level depth
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


