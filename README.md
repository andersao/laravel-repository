# Laravel Repositories

[![Total Downloads](https://poser.pugx.org/prettus/laravel-repository/downloads.svg)](https://packagist.org/packages/prettus/laravel-repository)
[![Latest Stable Version](https://poser.pugx.org/prettus/laravel-repository/v/stable.svg)](https://packagist.org/packages/prettus/laravel-repository)
[![Latest Unstable Version](https://poser.pugx.org/prettus/laravel-repository/v/unstable.svg)](https://packagist.org/packages/prettus/laravel-repository)
[![License](https://poser.pugx.org/prettus/laravel-repository/license.svg)](https://packagist.org/packages/prettus/laravel-repository)

Laravel Repositories é utilizados para abstrair a camadas de banco de dados, tornando a aplicação mais flexivel e de fácil manutenção.

## Instalação

Edite o seu arquivo composer.json e adicione "prettus/laravel-repository": "dev-master" nas dependencias.
 
    "require": {
        "prettus/laravel-repository": "dev-master"
    }

Execute o comando composer update para atualizar as dependencias do seu projeto

Abra o arquivo app/config/app.php e adicione o provider abaixo

    'Prettus\Repository\RepositoryServiceProvider',

## Metódos do repositório

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

## Utilizando o Repositório

### Criar um Validator ( Opcional )

Veja mais detalhes em https://github.com/andersao/laravel-validator

    <?php
    
    use \Prettus\Validator\LaravelValidator;
    
    class PostValidator extends LaravelValidator {
    
        protected $rules = [
            'title' => 'required',
            'text'  => 'min:3',
            'author'=> 'required'
        ];
    
    }

### Criar um repositório

    <?php
    
    use Prettus\Repository\Eloquent\RepositoryBase;
    
    class PostRepository extends RepositoryBase {
    
        public function __construct(Post $model, PostValidator $validator)
        {
            parent::__construct($model, $validator);
        }
        
    }
    
### Usando o repositório em um controller

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
    
## Filtro no repositório

A interface RepositoryRequestFilterableInterface prove o metódo requestFilter(), esse metódo aplica um filtro no repositório
a partir de parâmetros enviados na requisição. Dessa forma é possível realizar busca e ordenar resultados somente passando dados
por parâmetros.

As parâmetros aceitos são:

- search ( Valor a ser buscado nos campos definidos no repositorio )
- searchFields ( Campos a serem buscados nessa requisição )
- filter ( Campos retornados )
- orderBy
- sortedBy 

Exemplo de utilização:

- ?search=Lorem&orderBy=nome
- ?search=Lorem&searchFields=nome:like;email:=
- ?filter=nome,email

### Aplicando o filtro da requisição

    public function index()
    {
        $posts = $this->repository->requestFilter()->all();
    
        return Response::json(array(
            'data'   =>$posts
        ));
    }
    
    
# Autor

Anderson Andrade - <contato@andersonandra.de>