<?php


namespace App\Achievement\Strategies;


use App\Achievement\Exceptions\AchievementExistsException;
use App\Contracts\AchievementStrategyInterface;
use App\Entities\Achievement;
use App\Models\UserAchievement;
use App\User;

class LoginAchievementStrategy implements AchievementStrategyInterface
{
    private $user;
    private $achievement;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function execute()
    {
        $this->achievement = $this->user
                            ->achievements()
                            ->where('achievement_id', Achievement::AUTHORIZATION)
                            ->orderBy('created_at', 'DESC')
                            ->first();

        if (!$this->valid()) {
            throw new AchievementExistsException("Achievement already exists.");
        }

        $achievement = new UserAchievement();
        $achievement->user_id = $this->user->id;
        $achievement->achievement_id = Achievement::AUTHORIZATION;
        $achievement->save();
    }

    public function valid()
    {
        $result = true;

        if (!$this->achievement) {
            return $result;
        }

        $result = ($this->achievement->created_at)->timestamp < strtotime(date('d.m.Y', time()));

        return $result;

    }
}