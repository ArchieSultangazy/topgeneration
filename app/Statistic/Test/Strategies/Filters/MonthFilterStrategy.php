<?php


namespace App\Statistic\Test\Strategies\Filters;


use App\Statistic\Test\Contracts\FilterStrategyContract;

class MonthFilterStrategy extends AbstractFilterStrategy implements FilterStrategyContract
{
    public function filter()
    {
        $this->query->whereRaw('MONTH(created_at)', (int) date('m', time()));
    }
}