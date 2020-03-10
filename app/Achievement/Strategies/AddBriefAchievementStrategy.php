<?php


namespace App\Achievement\Strategies;


use App\Achievement\Exceptions\AchievementExistsException;
use App\Contracts\AchievementStrategyInterface;
use App\Entities\Achievement;
use App\Models\UserAchievement;
use App\User;
use Illuminate\Http\Request;

/**
 * Class AddBriefAchievementStrategy
 * @package App\Achievement\Strategies
 */
class AddBriefAchievementStrategy implements AchievementStrategyInterface
{
    private $user;
    private $request;
    /** @var UserAchievement */
    private $achievement;

    /**
     * AddBriefAchievementStrategy constructor.
     * @param User $user
     * @param Request $request
     */
    public function __construct(User $user, Request $request)
    {
        $this->user = $user;
        $this->request = $request;
    }

    /**
     * @throws AchievementExistsException
     */
    public function execute()
    {
        if ($this->exists()) {
            if (!$this->hasBrief()) {
                $this->remove();

                return;
            }

            throw new AchievementExistsException('Achievement already exists');
        }

        $achievement = new UserAchievement();
        $achievement->user_id = $this->user->id;
        $achievement->achievement_id = Achievement::FILLED_BRIEF_INFO;
        $achievement->save();
    }

    /**
     * @return mixed
     */
    public function exists()
    {
        $this->achievement = UserAchievement::where([
            ['user_id', $this->user->id],
            ['achievement_id', Achievement::FILLED_BRIEF_INFO]
        ]);

        return $this->achievement->exists();
    }

    /**
     * @return bool
     */
    public function hasBrief()
    {
        return !empty($this->request->input('about'));
    }

    /**
     * @throws \Exception
     */
    public function remove()
    {
        $this->achievement->delete();
    }
}