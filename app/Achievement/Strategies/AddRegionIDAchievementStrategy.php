<?php


namespace App\Achievement\Strategies;


use App\Contracts\AchievementStrategyInterface;
use App\Entities\Achievement;
use App\Models\UserAchievement;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Class AddRegionIDAchievementStrategy
 * @package App\Achievement\Strategies
 */
class AddRegionIDAchievementStrategy implements AchievementStrategyInterface
{
    private $user;
    private $request;
    /** @var Builder */
    private $achievement;

    /**
     * AddRegionIDAchievementStrategy constructor.
     * @param User $user
     * @param Request $request
     */
    public function __construct(User $user, Request $request)
    {
        $this->user = $user;
        $this->request = $request;
    }

    public function execute()
    {
        if ($this->exists()) {
            if (!$this->hasRegion()) {
                $this->remove();
            }

            return;
        }

        if (!$this->hasRegion()) {
            return;
        }

        $achievement = new UserAchievement();
        $achievement->user_id = $this->user->id;
        $achievement->achievement_id = Achievement::FILLED_REGION_INFO;
        $achievement->save();
    }

    /**
     * @return bool
     */
    public function exists()
    {
        $this->achievement = $this->user->achievements()
                   ->where('achievement_id', Achievement::FILLED_REGION_INFO);

        return $this->achievement->exists();
    }

    /**
     * @return bool
     */
    public function hasRegion()
    {
        return !empty($this->request->input('region_id', null));
    }

    /**
     * @throws \Exception
     */
    public function remove()
    {
        $this->achievement->first()->delete();
    }
}