<?php


namespace App\Achievement\Strategies;


use App\Contracts\AchievementStrategyInterface;
use App\Entities\Achievement;
use App\Models\UserAchievement;
use App\User;

class ViewLessonAchievementStrategy implements AchievementStrategyInterface
{
    /** @var User $user */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function execute()
    {
        $achievement = new UserAchievement();
        $achievement->user_id = $this->user->id;
        $achievement->achievement_id = Achievement::VIEW_LESSON;
        $achievement->save();
    }
}