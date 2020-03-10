<?php


namespace App\Statistic\Test\Contracts;

use Illuminate\Database\Eloquent\Builder;

/**
 * Interface FilterStrategyContract
 * @package App\Statistic\Test\Contracts
 */
interface FilterStrategyContract
{
    public function setQuery(Builder $q);

    /**
     * @return mixed
     */
    public function filter();
}