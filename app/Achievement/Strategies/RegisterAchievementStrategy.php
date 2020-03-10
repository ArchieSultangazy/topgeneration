<?php


namespace App\Achievement\Strategies;


use App\Achievement\Exceptions\AchievementExistsException;
use App\Contracts\AchievementStrategyInterface;
use App\Entities\Achievement;
use App\User;
use App\Models\UserAchievement;

/**
 * Class RegisterAchievementStrategy
 * @package App\Achievement\Strategies
 */
class RegisterAchievementStrategy implements AchievementStrategyInterface
{
    /**
     * @var User $user
     */
    private $user;

    /**
     * RegisterAchievementStrategy constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @throws AchievementExistsException
     */
    public function execute()
    {
        if ($this->exists()) {
            throw new AchievementExistsException("Achievement already exists");
        }

        $achievement = new UserAchievement();
        $achievement->user_id = $this->user->id;
        $achievement->achievement_id = Achievement::REGISTRATION;
        $achievement->save();
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return $this->user
                    ->achievements()
                    ->where('achievement_id', Achievement::REGISTRATION)
                    ->exists();
    }
}