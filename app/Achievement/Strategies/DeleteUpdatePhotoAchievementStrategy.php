<?php


namespace App\Achievement\Strategies;


use App\Achievement\Exceptions\AchievementNotExistsException;
use App\Contracts\AchievementStrategyInterface;
use App\Entities\Achievement;
use App\User;

/**
 * Class DeleteUpdatePhotoAchievementStrategy
 * @package App\Achievement\Strategies
 */
class DeleteUpdatePhotoAchievementStrategy implements AchievementStrategyInterface
{
    /**
     * @var User
     */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @throws AchievementNotExistsException
     */
    public function execute()
    {
        $achievement = $this->user
                            ->achievements()
                            ->where('achievement_id', Achievement::UPLOAD_PROFILE_PHOTO)
                            ->first();

        if (!$achievement) {
            throw new AchievementNotExistsException('Upload avatar achievement does not exist');
        }

        $achievement->delete();
    }
}