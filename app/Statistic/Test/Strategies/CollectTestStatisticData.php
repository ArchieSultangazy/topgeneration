<?php


namespace App\Statistic\Test\Strategies;


use App\Models\CL\Test;
use App\Models\UserResults;
use App\Statistic\Test\Contracts\CollectTestStatisticContract;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CollectTestStatisticData implements CollectTestStatisticContract
{
    /** @var Test $test */
    private $test;
    /** @var UserResults $userResults */
    private $userResults;
    /** @var Collection $queryResult */
    private $queryResult;

    public function __construct(Test $test)
    {
        $this->test = $test;
    }

    public function run()
    {
        $this->boot()
             ->storeResult()
             ->storeQuestionsCount()
             ->storeFinishedTime();
    }

    /**
     * @return $this
     */
    public function boot()
    {
        $this->userResults = $this->test->userResults()->finished()->has('user')->with(['user']);

        return $this;
    }

    public function storeResult()
    {
        $this->queryResult = $this->userResults->get();

        return $this;
    }

    public function storeQuestionsCount()
    {
        $this->queryResult->each(function ($value) {
            $value->questionsCount = $this->test->questions()->count();
        });

        return $this;
    }

    public function storeFinishedTime()
    {
        $this->queryResult->each(function ($value) {
           $value->finishedTime = gmdate('H:i:s', (Carbon::parse($value->finished_at)->timestamp - Carbon::parse($value->created_at)->timestamp));
        });

        return $this;
    }

    public function getResult()
    {
        return $this->queryResult;
    }
}