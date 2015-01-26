<?php namespace Prettus\Repository\Eloquent;

use \Config;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Contracts\RepositoryRelationshipInterface;
use Prettus\Repository\Contracts\RepositoryRequestFilterableInterface;
use Prettus\Repository\Contracts\RepositorySortableInterface;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class Repository
 * @package Prettus\Repository\Eloquent
 */
abstract class Repository implements RepositoryInterface, RepositoryRequestFilterableInterface, RepositoryRelationshipInterface, RepositorySortableInterface {

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

    /**
     * @var array
     */
    protected $repositoryFieldsSearchable;

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
        if( $this->query instanceof \Illuminate\Database\Eloquent\Builder ){
            return $this->query->get($columns);
        }

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

    /**
     * Apply filter from the request
     *
     * @param Request $request
     * @return $this
     */
    public function requestFilter(Request $request = null){

        if( is_null($request) ){
            $request = app('Illuminate\Http\Request');
        }

        $search         = $request->get( Config::get('prettus-repository::config.filter.params.search','search') , null);
        $searchFields   = $request->get( Config::get('prettus-repository::config.filter.params.searchFields','searchFields') , null);
        $filter         = $request->get( Config::get('prettus-repository::config.filter.params.filter','filter') , null);
        $orderBy        = $request->get( Config::get('prettus-repository::config.filter.params.orderBy','orderBy') , null);
        $sortedBy       = $request->get( Config::get('prettus-repository::config.filter.params.sortedBy','sortedBy') , 'asc');
        $sortedBy       = !empty($sortedBy) ? $sortedBy : 'asc';

        if( $search && is_array($this->repositoryFieldsSearchable) && count($this->repositoryFieldsSearchable) ){

            $searchFields = is_array($searchFields) || is_null($searchFields) ? $searchFields : array($searchFields);
            $fields       = $this->parserFieldsSearch($this->repositoryFieldsSearchable, $searchFields);
            $isFirstField = true;

            $_searchParams  = explode(';', $search);
            $searchData     = array();
            $searchDataFields = array();
            $queryForceAndWhere = false;

            if( is_array($_searchParams) ){
                foreach($_searchParams as $_search){
                    $_data = explode(':', $_search);
                    if( count($_data) == 2 ){
                        $queryForceAndWhere = true;
                        $searchData[$_data[0]] = $_data[1];
                        $searchDataFields[]    = $_data[0];
                    }else{
                        $searchData[] = $_search;
                    }
                }

                if( count($searchDataFields) ){
                    $fields  = $this->parserFieldsSearch($fields, $searchDataFields);
                }
            }

            foreach($fields as $field=>$condition){

                if(is_numeric($field)){
                    $field = $condition;
                    $condition = "=";
                }

                $condition  = trim(strtolower($condition));

                if( isset($searchData[$field]) ){
                    $value = $condition == "like" ? "%{$searchData[$field]}%" : $searchData[$field];
                }else{
                    $value = $condition == "like" ? "%{$search}%" : $search;
                }

                if( $isFirstField || $queryForceAndWhere ){
                    $this->query = $this->query->where($field,$condition,$value);
                    $isFirstField = false;
                }else{
                    $this->query = $this->query->orWhere($field,$condition,$value);
                }
            }

        }

        if( $orderBy && !empty($orderBy) ){
            $this->query = $this->query->orderBy($orderBy, $sortedBy);
        }

        if( $filter && !empty($filter) ){

            if( is_string($filter) ){
                $filter = explode(';', $filter);
            }

            $this->query = $this->query->select($filter);
        }

        return $this;
    }

    /**
     * @param array $fields
     * @param array $searchFields
     * @return array
     * @throws \Exception
     */
    protected function parserFieldsSearch(array $fields = array(), array $searchFields =  null){

        if( !is_null($searchFields) && count($searchFields) ){

            $acceptedConditions = Config::get('prettus-repository::config.filter.acceptedConditions', array('=','like') );
            $originalFields = $fields;
            $fields = [];

            foreach($originalFields as $field=>$condition){

                if(is_numeric($field)){
                    $field = $condition;
                    $condition = "=";
                }

                $_searchFieldIndex = array_search($field, $searchFields);
                $_searchField      = $searchFields[$_searchFieldIndex];
                $_searchFieldParts = explode(':', $_searchField);

                if( count($_searchFieldParts) == 2 ){
                    if( in_array($_searchFieldParts[1],$acceptedConditions) ){
                        $field     = $_searchFieldParts[0];
                        $condition = $_searchFieldParts[1];
                        $searchFields[$_searchFieldIndex] = $field;
                    }
                }

                if( in_array($field, $searchFields) ){
                    $fields[$field] = $condition;
                }
            }

            if( count($fields) == 0 ){
                throw new \Exception( trans('prettus-repository::repository.fields_not_accepted', array('fields'=>implode(',', $searchFields))) );
            }
        }

        return $fields;
    }
}