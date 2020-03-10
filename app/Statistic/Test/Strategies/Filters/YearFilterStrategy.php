<?php


namespace App\Statistic\Test\Strategies\Filters;


use App\Statistic\Test\Contracts\FilterStrategyContract;

class YearFilterStrategy extends AbstractFilterStrategy implements FilterStrategyContract
{
    public function filter()
    {
        $this->query->whereRaw('YEAR(created_at)', (int) date('Y', time()));
    }
}