<?php


namespace App\Statistic\Test\Strategies;

use App\Models\CL\Test;
use App\Statistic\Test\Contracts\CollectTestStatisticContract;
use App\Statistic\Test\Contracts\FilterStrategyContract;
use App\Statistic\Test\Contracts\FilterStrategyFactoryContract;
use App\Statistic\Test\Exceptions\UserResultsIsNotSetException;
use App\Statistic\Test\Factories\FilterStrategyFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Class CollectTestBriefStatisticData
 * @package App\Statistic\Test\Strategies
 */
class CollectTestBriefStatisticData implements CollectTestStatisticContract
{
    /** @var Request $request */
    private $request;
    /** @var Test $test */
    private $test;
    /** @var Builder $userResults */
    private $userResults;
    /** @var Collection $result */
    private $result;
    /** @var Collection $queryResult */
    private $queryResult;
    /** @var FilterStrategyFactoryContract */
    private $filterStrategyFactory;
    /** @var FilterStrategyContract */
    private $filterStrategy;

    /**
     * CollectTestBriefStatisticData constructor.
     * @param Request $request
     * @param Test $test
     */
    public function __construct(Request $request, Test $test)
    {
        $this->request = $request;
        $this->test = $test;
        $this->result = new Collection();
        $this->filterStrategyFactory = new FilterStrategyFactory();
    }

    public function run()
    {
        $this->boot()
             ->getFilterStrategy()
             ->applyFilter()
             ->storeResult()
             ->calculateUsersComplete()
             ->calculateUsersReached()
             ->calculateAvgResult()
             ->calculateAvgTries()
             ->calculateAvgCompleteTime();
    }

    /**
     * @return $this
     */
    public function boot()
    {
        $this->userResults = $this->test->userResults()->finished();

        return $this;
    }

    /**
     * @throws UserResultsIsNotSetException
     */
    public function applyFilter()
    {
        if (!$this->userResults) {
            throw new UserResultsIsNotSetException('User results is not set');
        }

        if (!$this->filterStrategy) {
            return $this;
        }

        $this->filterStrategy->setQuery($this->userResults);
        $this->filterStrategy->filter();

        return $this;
    }

    private function getFilterStrategy()
    {
        $result = null;

        switch ($this->request->input('period')) {
            case self::YEAR:
                $result = $this->filterStrategyFactory->getFilter(self::YEAR_FILTER_STRATEGY);
                break;
            case self::MONTH:
                $result =  $this->filterStrategyFactory->getFilter(self::MONTH_FILTER_STRATEGY);
                break;
            case self::ALL:
                break;
        }

        $this->filterStrategy = $result;

        return $this;
    }

    public function storeResult()
    {
        $this->queryResult = $this->userResults->get();

        return $this;
    }

    public function getResult()
    {
        return $this->result;
    }
    //TODO: refactor if statistic criteries increases.
    public function calculateUsersReached()
    {
        $this->result->usersReached = $this->queryResult->pluck('user_id')->unique()->count();

        return $this;
    }

    public function calculateUsersComplete()
    {
        $completed = $this->queryResult->filter(function ($value) {
            return $value->success == 1;
        });

        $this->result->usersComplete = $completed->pluck('user_id')->unique()->count();

        return $this;
    }

    public function calculateAvgResult()
    {
        $this->result->avgResult = $this->queryResult->sum('result') / $this->queryResult->count();

        return $this;
    }
    
    public function calculateAvgTries()
    {

        $tries = $this->queryResult->groupBy('user_id');

        $this->result->avgTries = $tries->sum(function ($value) {
                return count($value);
            }) / $tries->count();

        return $this;
    }

    public function calculateAvgCompleteTime()
    {
        $this->result->avgCompleteTime = gmdate('H:i:s', $this->queryResult->sum(function ($value) {
            return Carbon::parse($value->finished_at)->timestamp - Carbon::parse($value->created_at)->timestamp;
        }) / $this->queryResult->count());

        return $this;
    }
}