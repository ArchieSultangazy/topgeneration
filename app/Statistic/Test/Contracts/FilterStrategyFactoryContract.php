<?php


namespace App\Statistic\Test\Contracts;

/**
 * Interface FilterStrategyFactoryContract
 * @package App\Statistic\Test\Contracts
 */
interface FilterStrategyFactoryContract
{
    /**
     * @param string $class
     * @return FilterStrategyContract
     */
    public function getFilter(string $class): FilterStrategyContract;
}