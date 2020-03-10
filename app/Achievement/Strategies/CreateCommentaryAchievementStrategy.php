<?php


namespace App\Achievement\Strategies;


use App\Contracts\AchievementStrategyInterface;
use App\Entities\Achievement;
use App\Models\UserAchievement;
use App\User;

/**
 * Class CreateCommentaryAchievementStrategy
 * @package App\Achievement\Strategies
 */
class CreateCommentaryAchievementStrategy implements AchievementStrategyInterface
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function execute()
    {
        $achievement = new UserAchievement();
        $achievement->user_id = $this->user->id;
        $achievement->achievement_id = Achievement::WRITE_A_COMMENTARY;
        $achievement->save();
    }
}