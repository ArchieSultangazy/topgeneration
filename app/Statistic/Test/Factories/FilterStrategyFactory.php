<?php


namespace App\Statistic\Test\Factories;


use App\Statistic\Test\Contracts\FilterStrategyContract;
use App\Statistic\Test\Contracts\FilterStrategyFactoryContract;
use App\Statistic\Test\Exceptions\FilterStrategyDoesntExistException;

/**
 * Class FilterStrategyFactory
 * @package App\Statistic\Test\Factories
 */
class FilterStrategyFactory implements FilterStrategyFactoryContract
{
    /**
     * @param string $class
     * @return FilterStrategyContract
     * @throws FilterStrategyDoesntExistException
     */
    public function getFilter(string $class): FilterStrategyContract
    {
        if (!class_exists($class)) {
            throw new FilterStrategyDoesntExistException('Filter strategy doesnt exist');
        }

        return new $class;
    }
}