<?php namespace Prettus\Repository\Contracts;

/**
 * Interface RepositorySortableInterface
 * @package Prettus\Repository\Contracts
 */
interface RepositorySortableInterface {

    /**
     * Order results by field and sorter
     *
     * @param $field
     * @param string $sort
     * @return $this
     */
    public function orderBy($field, $sort = 'ASC');

    /**
     * Order results by field and ascending order
     *
     * @param $field
     * @return $this
     */
    public function orderByAsc($field);

    /**
     * Order results by field and descending  order
     *
     * @param $field
     * @return $this
     */
    public function orderByDesc($field);
}