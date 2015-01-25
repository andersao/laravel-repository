<?php namespace Prettus\Repository\Contracts;

use Illuminate\Http\Request;

/**
 * Interface RepositoryRequestFilterableInterface
 * @package Prettus\Repository\Contracts
 */
interface RepositoryRequestFilterableInterface {

    /**
     * Apply filter from the request
     *
     * @param Request $request
     * @return $this
     */
    public function requestFilter(Request $request = null);

}