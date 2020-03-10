<?php


namespace App\Statistic\Test\Contracts;

use App\Statistic\Test\Strategies\Filters\MonthFilterStrategy;
use App\Statistic\Test\Strategies\Filters\YearFilterStrategy;

/**
 * Interface CollectTestStatisticContract
 * @package App\Statistic\Test\Contracts
 */
interface CollectTestStatisticContract
{
    const ALL = 'all';
    const YEAR = 'year';
    const MONTH = 'month';

    const YEAR_FILTER_STRATEGY = YearFilterStrategy::class;
    const MONTH_FILTER_STRATEGY = MonthFilterStrategy::class;

    /**
     * @return mixed
     */
    public function run();
}