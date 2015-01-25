# Laravel Repositories

## Installation

Edit your project's composer.json file to require prettus/repository.
 
    "prettus/laravel-repository": "dev-master"

Execute comp

Open app/config/app.php, and add a new item to the providers array.

    'Prettus\Repository\RepositoryServiceProvider',

## Repository methods

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

## Creating a Validator

See https://github.com/andersao/laravel-validator

    <?php
    
    use \Prettus\Validator\LaravelValidator;
    
    class PostValidator extends LaravelValidator {
    
        protected $rules = [
            'title' => 'required',
            'text'  => 'min:3',
            'author'=> 'required'
        ];
    
    }

## Creating a repository

    <?php
    
    use Prettus\Repository\Eloquent\RepositoryBase;
    
    class PostRepository extends RepositoryBase {
    
        public function __construct(Post $model, PostValidator $validator)
        {
            parent::__construct($model, $validator);
        }
        
    }
    
## Using the repository

    <?php
    
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