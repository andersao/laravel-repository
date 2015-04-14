###This package is abandoned and no longer maintained. The author suggests using the https://github.com/andersao/l5-repository package instead.

# Laravel 4 - Repositories

[![Build Status](https://travis-ci.org/andersao/laravel-repository.svg)](https://travis-ci.org/andersao/laravel-repository.svg)
[![Total Downloads](https://poser.pugx.org/prettus/laravel-repository/downloads.svg)](https://packagist.org/packages/prettus/laravel-repository)
[![Latest Stable Version](https://poser.pugx.org/prettus/laravel-repository/v/stable.svg)](https://packagist.org/packages/prettus/laravel-repository)
[![Latest Unstable Version](https://poser.pugx.org/prettus/laravel-repository/v/unstable.svg)](https://packagist.org/packages/prettus/laravel-repository)
[![License](https://poser.pugx.org/prettus/laravel-repository/license.svg)](https://packagist.org/packages/prettus/laravel-repository)

Laravel 4 Repositories is used to abstract the data layer, making our application more flexible to maintain.

See [Laravel 5 - Repositories](https://github.com/andersao/l5-repository)

## Installation

Add this line "prettus/laravel-repository": "2.0.*" in your composer.json.

```json
"require": {
    "prettus/laravel-repository": "2.0.*"
}
```

Issue composer update

Add to app/config/app.php service provider array:

```php
    'Prettus\Repository\RepositoryServiceProvider',
```

Publish Configuration

```shell
php artisan config:publish prettus/laravel-repository
```

## Methods

### Repository

- scopeReset()
- find($id, $columns = ['*'])
- findByField($field, $value, $columns = ['*'])
- all($columns = array('*'))
- paginate($limit = null, $columns = ['*'])
- create(array $attributes)
- update(array $attributes, $id)
- delete($id)
- getModel()
- with(array $relations);
- pushCriteria(Criteria $criteria)
- getCriteria()
- getByCriteria(Criteria $criteria)
- skipCriteria()

### Criteria

- apply($query)

## Usage

### Create a Model

Create your model normally , but it is important to define the attributes that can be filled from the input form data.

```php
class Post extends Eloquent { // or Ardent, Or any other Model Class

    protected $fillable = [
        'title',
        'author',
        ...
     ];

     ...
}
```

### Create a Repository

```php
use Prettus\Repository\Eloquent\Repository;

class PostRepository extends Repository {

    public function __construct(Post $model)
    {
        parent::__construct($model);
    }   
    
}
```

### Use methods

```php
class PostsController extends BaseController {

    /**
     * @var PostRepository
     */
    protected $repository;

    public function __construct(PostRepository $repository){
        $this->repository = $repository;
    }
    
    ....
}
```

Find all results in Repository

```php
$posts = $this->repository->all();
```

Find all results in Repository with pagination

```php
$posts = $this->repository->paginate($limit = null, $columns = ['*']);
```

Find by result by id

```php
$post = $this->repository->find($id);
```

Create new entry in Repository

```php
$post = $this->repository->create( Input::all() );
```

Update entry in Repository

```php
$post = $this->repository->update( Input::all(), $id );
```

Delete entry in Repository

```php
$this->repository->delete($id)
```


### Create a Criteria

Criteria is a way to change the repository of the query by applying specific conditions according to their need . You can add multiple Criteria in your repository

```php
class MyCriteria implements \Prettus\Repository\Contracts\Criteria {

    public function apply($query)
    {
        $query = $query->where('user_id','=', Auth::user()->id );
        return $query;
    }
}
```

### Using the Criteria in a Controller

```php

class PostsController extends BaseController {

    /**
     * @var PostRepository
     */
    protected $repository;

    public function __construct(PostRepository $repository){
        $this->repository = $repository;
    }


    public function index()
    {
        $this->repository->pushCriteria(new MyCriteria());
        $posts = $this->repository->all();
		...
    }

}
```

Getting results from criteria

```php
$posts = $this->repository->getByCriteria(new MyCriteria());
```

Setting Criteria default in Repository

```php
use Prettus\Repository\Eloquent\Repository;

class PostRepository extends Repository {

    public function __construct(Post $model)
    {
        parent::__construct($model);
    }
    
    public function boot(){
        $this->pushCriteria(new MyCriteria());
        $this->pushCriteria(new AnotherCriteria());
        ...
    }
    
}
```

### Skip criteria defined in the repository

Use *skipCriteria* before any method in the repository

```php

$posts = $this->repository->skipCriteria()->all();

```

### Using the RequestCriteria

RequestCriteria is a standard Criteria implementation. It enables filters perform in the repository from parameters sent in the request.

You can perform a dynamic search , filtering the data and customize queries

To use the Criteria in your repository , you can add a new criteria in the boot method of your repository , or directly use in your controller , in order to filter out only a few requests

####Enabling in your Repository

```php
use Prettus\Repository\Eloquent\Repository;
use Prettus\Repository\Criteria\RequestCriteria;

class PostRepository extends Repository {

	/**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'email'
    ];

    public function __construct(Post $model)
    {
        parent::__construct($model);
    }
    
    public function boot(){
        $this->pushCriteria(app('Prettus\Repository\Criteria\RequestCriteria'));
        ...
    }
    
}
```

Remember, you need to define which fields from the model can are searchable.

In your repository set **$fieldSearchable** with their fields searchable.

```php
protected $fieldSearchable = [
	'name',
	'email'
];
```

You can set the type of condition will be used to perform the query , the default condition is "**=**"

```php
protected $fieldSearchable = [
	'name'=>'like',
	'email', // Default Condition "="
	'your_field'=>'condition'
];
```

####Enabling in your Controller

```php
	public function index()
    {
        $this->repository->pushCriteria(app('Prettus\Repository\Criteria\RequestCriteria'));
        $posts = $this->repository->all();
		...
    }
```

#### Example the Crirteria

Request all data without filter by request

*http://prettus.local/users*

```json
[
    {
        "id": 1,
        "name": "Anderson Andrade",
        "email": "email@gmail.com",
        "created_at": "-0001-11-30 00:00:00",
        "updated_at": "-0001-11-30 00:00:00"
    },
    {
        "id": 2,
        "name": "Lorem Ipsum",
        "email": "lorem@ipsum.com",
        "created_at": "-0001-11-30 00:00:00",
        "updated_at": "-0001-11-30 00:00:00"
    },
    {
        "id": 3,
        "name": "Laravel",
        "email": "laravel@gmail.com",
        "created_at": "-0001-11-30 00:00:00",
        "updated_at": "-0001-11-30 00:00:00"
    }
]
```

Conducting research in the repository

*http://prettus.local/users?search=Anderson%20Andrade*

or

*http://prettus.local/users?search=Anderson&searchFields=name:like*

or

*http://prettus.local/users?search=email@gmail.com&searchFields=email:=*

```json
[
    {
        "id": 1,
        "name": "Anderson Andrade",
        "email": "email@gmail.com",
        "created_at": "-0001-11-30 00:00:00",
        "updated_at": "-0001-11-30 00:00:00"
    }
]
```

Filtering fields

*http://prettus.local/users?filter=id;name*

```json
[
    {
        "id": 1,
        "name": "Anderson Andrade"
    },
    {
        "id": 2,
        "name": "Lorem Ipsum"
    },
    {
        "id": 3,
        "name": "Laravel"
    }
]
```

Sorting the results

*http://prettus.local/users?filter=id;name&orderBy=id&sortedBy=desc*

```json
[
    {
        "id": 3,
        "name": "Laravel"
    },
    {
        "id": 2,
        "name": "Lorem Ipsum"
    },
    {
        "id": 1,
        "name": "Anderson Andrade"
    }
]
```

####Overwrite params name

You can change the name of the parameters in the configuration file **config/repository-criteria.php**

# Author

Anderson Andrade - <contato@andersonandra.de>
