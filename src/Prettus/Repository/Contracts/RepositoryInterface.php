<?php namespace Prettus\Repository\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\Paginator;

/**
 * Interface RepositoryInterface
 * @package Prettus\Repository\Contracts
 */
interface RepositoryInterface {

    /**
     * Reset internal Query
     *
     * @return $this
     */
    public function resetScope();

    /**
     * Find data by id
     *
     * @param $id
     * @param array $columns
     * @return Model|null
     */
    public function find($id, $columns = array('*'));

    /**
     * Find data by field and value
     *
     * @param $field
     * @param $value
     * @param array $columns
     * @return Model|null
     */
    public function findByField($field, $value, $columns = array('*'));

    /**
     * Retrieve all data of repository
     *
     * @param array $columns
     * @return Collection
     */
    public function all($columns = array('*'));

    /**
     * Retrieve all data of repository, paginated
     * @param null $limit
     * @param array $columns
     * @return Paginator
     */
    public function paginate($limit = null, $columns = array('*'));

    /**
     * Save a new entity in repository
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes);

    /**
     * Update a entity in repository by id
     *
     * @param array $attributes
     * @param $id
     * @return Model
     */
    public function update(array $attributes, $id);

    /**
     * Delete a entity in repository by id
     *
     * @param $id
     * @return bool
     */
    public function delete($id);

    /**
     * Get repository model
     *
     * @return Model
     */
    public function getModel();
}