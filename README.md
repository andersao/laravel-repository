# Laravel Repositories

[![Total Downloads](https://poser.pugx.org/prettus/laravel-repository/downloads.svg)](https://packagist.org/packages/prettus/laravel-repository)
[![Latest Stable Version](https://poser.pugx.org/prettus/laravel-repository/v/stable.svg)](https://packagist.org/packages/prettus/laravel-repository)
[![Latest Unstable Version](https://poser.pugx.org/prettus/laravel-repository/v/unstable.svg)](https://packagist.org/packages/prettus/laravel-repository)
[![License](https://poser.pugx.org/prettus/laravel-repository/license.svg)](https://packagist.org/packages/prettus/laravel-repository)

Laravel Repositories is used to abstract the data layer, making our application more flexible to maintain.

## Installation

Add this line "prettus/laravel-repository": "1.0.*" in your composer.json.

```json
"require": {
    "prettus/laravel-repository": "1.0.*"
}
```

Issue composer update

Add to app/config/app.php service provider array:

```
    'Prettus\Repository\RepositoryServiceProvider',
```
## Methods

### RepositoryInterface

- find($id, $columns = array('*'))
- findByField($field, $value, $columns = array('*'))
- all($columns = array('*'))
- paginate($limit = null, $columns = array('*'))
- create(array $attributes)
- update(array $attributes, $id)
- delete($id)
- getModel()
    
### RepositoryRelationshipInterface

- with(array $relations);

### RepositorySortableInterface

- orderBy($field, $sort = 'ASC');
- orderByAsc($field);
- orderByDesc($field);

### RepositoryRequestFilterableInterface

- requestFilter(Request $request = null);

## Utilisation

### Create a validator class ( Optional )

For more details: https://github.com/andersao/laravel-validator

```php
use \Prettus\Validator\LaravelValidator;

class PostValidator extends LaravelValidator {

    protected $rules = [
        'title' => 'required',
        'text'  => 'min:3',
        'author'=> 'required'
    ];

}
```

### Create a Repository

```php
use Prettus\Repository\Eloquent\RepositoryBase;

class PostRepository extends RepositoryBase {

    public function __construct(Post $model, PostValidator $validator)
    {
        parent::__construct($model, $validator);
    }
    
}
```

### Using the Repository in a Controller

```php

use \Prettus\Validator\Exceptions\ValidatorException;

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
        $posts = $this->repository->all();

        return Response::json(array(
            'data'   =>$posts
        ));
    }


    public function show($id)
    {
        $post = $this->repository->find($id);

        return Response::json($post->toArray());
    }

    public function store()
    {

        try {

            $post = $this->repository->create( Input::all() );

            return Response::json(array(
                'message'=>'Post created',
                'data'   =>$post->toArray()
            ));

        } catch (ValidatorException $e) {

            return Response::json(array(
                'error'   =>true,
                'message' =>$e->getMessage()
            ));

        }
    }

    public function update($id)
    {

        try{

            $post = $this->repository->update( Input::all(), $id );

            return Response::json(array(
                'message'=>'Post created',
                'data'   =>$post->toArray()
            ));

        }catch (ValidatorException $e){

            return Response::json(array(
                'error'   =>true,
                'message' =>$e->getMessage()
            ));

        }

    }

    public function destroy($id){

        if( $this->repository->delete($id) )
        {
            return Response::json(array(
                'message' =>'Post deleted'
            ));
        }

    }
}
```

## Filters

The RepositoryRequestFilterableInterface interface comes with the method requestFilter(Request params). 

The default parameters are:

- search ( Value to be searched by the repository )
- searchFields ( Fields to be searched in )
- filter ( Returned fields )
- orderBy
- sortedBy 

Examples:

- http://server.local?search=Lorem&orderBy=name
- http://server.local?search=Lorem&searchFields=name:like;email:=
- http://server.local?filter=name;email

### Applying a filter

```php
public function index()
{
    $posts = $this->repository->requestFilter()->all();

    return Response::json(array(
        'data'   =>$posts
    ));
}
```

# Author

Anderson Andrade - <contato@andersonandra.de>
