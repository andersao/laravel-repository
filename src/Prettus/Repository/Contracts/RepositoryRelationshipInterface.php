<?php namespace Prettus\Repository\Contracts;

/**
 * Interface RepositoryRelationshipInterface
 * @package Prettus\Repository\Contracts
 */
interface RepositoryRelationshipInterface {

    /**
     * Load relations
     *
     * @param array $relations
     * @return $this
     */
    public function with(array $relations);
}