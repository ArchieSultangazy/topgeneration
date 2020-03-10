<?php


namespace App\Statistic\Test\Strategies\Filters;


use Illuminate\Database\Eloquent\Builder;

/**
 * Class AbstractFilterStrategy
 * @package App\Statistic\Test\Strategies\Filters
 */
abstract class AbstractFilterStrategy
{
    /** @var Builder $query */
    protected $query;

    /**
     * @param Builder $query
     * @return $this
     */
    public function setQuery(Builder $query)
    {
        $this->query = $query;

        return $this;
    }
}