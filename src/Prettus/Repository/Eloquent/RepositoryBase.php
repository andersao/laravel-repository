<?php namespace Prettus\Repository\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Contracts\RepositoryRelationshipInterface;
use Prettus\Repository\Contracts\RepositorySortableInterface;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class RepositoryBase
 * @package Prettus\Repository\Eloquent
 */
abstract class RepositoryBase implements RepositoryInterface, RepositoryRelationshipInterface, RepositorySortableInterface {

    /**
     * @var Model
     */
    protected $model = null;

    /**
     * @var ValidatorInterface
     */
    protected $validator = null;

    /**
     * @var Model
     */
    protected $query;

    public function __construct(Model $model, ValidatorInterface $validator = null){
        $this->model     = $model;
        $this->validator = $validator;
        $this->resetScope();
    }

    /**
     * Call the boot repository
     *
     */
    protected function boot()
    {
    }

    /**
     * Reset internal Query
     *
     * @return $this
     */
    public function resetScope()
    {
        $this->query = $this->model;
        $this->boot();
        return $this;
    }

    /**
     * Find data by id
     *
     * @param $id
     * @param array $columns
     * @return Model|null
     */
    public function find($id, $columns = array('*'))
    {
        return $this->query->find($id, $columns);
    }

    /**
     * Find data by field and value
     *
     * @param $field
     * @param $value
     * @param array $columns
     * @return Model|null
     */
    public function findByField($field, $value, $columns = array('*'))
    {
        return $this->query->where($field,'=',$value)->first();
    }

    /**
     * Retrieve all data of repository
     *
     * @param array $columns
     * @return Collection
     */
    public function all($columns = array('*'))
    {
        return $this->query->all($columns);
    }

    /**
     * Retrieve all data of repository, paginated
     * @param null $limit
     * @param array $columns
     * @return Paginator
     */
    public function paginate($limit = null, $columns = array('*'))
    {
        return $this->query->paginate($limit);
    }

    /**
     * Save a new entity in repository
     *
     * @throws ValidatorException
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes)
    {
        if( !is_null($this->validator) )
        {
            $this->validator->with($attributes)
                ->passesOrFail( ValidatorInterface::RULE_CREATE );
        }

        return $this->query->create($attributes);
    }

    /**
     * Update a entity in repository by id
     *
     * @throws ValidatorException
     * @param array $attributes
     * @param $id
     * @return Model
     */
    public function update(array $attributes, $id)
    {
        if( !is_null($this->validator) )
        {
            $this->validator->with($attributes)
                ->setId($id)
                ->passesOrFail( ValidatorInterface::RULE_UPDATE );
        }

        $model = $this->find($id);
        $model->fill($attributes);
        $model->save();

        return $model;
    }

    /**
     * Delete a entity in repository by id
     *
     * @param $id
     * @return bool
     */
    public function delete($id)
    {
        return (bool) $this->query->destroy($id);
    }

    /**
     * Get repository model
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Load relations
     *
     * @param array $relations
     * @return $this
     */
    public function with(array $relations)
    {
        $this->query = $this->query->with($relations);
        return $this;
    }

    /**
     * Order results by field and sorter
     *
     * @param $field
     * @param string $sort
     * @return $this
     */
    public function orderBy($field, $sort = 'ASC')
    {
        $this->query = $this->query->orderBy($field, $sort);
        return $this;
    }

    /**
     * Order results by field and ascending order
     *
     * @param $field
     * @return $this
     */
    public function orderByAsc($field)
    {
        return $this->orderBy($field,'ASC');
    }

    /**
     * Order results by field and descending  order
     *
     * @param $field
     * @return $this
     */
    public function orderByDesc($field)
    {
        return $this->orderBy($field,'DESC');
    }
}