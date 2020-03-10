<?php


namespace App\Achievement\Strategies;


use App\Achievement\Exceptions\AchievementExistsException;
use App\Contracts\AchievementStrategyInterface;
use App\Entities\Achievement;
use App\Models\UserAchievement;
use App\User;

class UploadPhotoAchievementStrategy implements AchievementStrategyInterface
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function execute()
    {
        if ($this->exists()) {
            throw new AchievementExistsException('Achievement already exists.');
        }

        $achievement = new UserAchievement();
        $achievement->user_id = $this->user->id;
        $achievement->achievement_id = Achievement::UPLOAD_PROFILE_PHOTO;
        $achievement->save();
    }

    public function exists()
    {
        $achievement = $this->user
                            ->achievements()
                            ->where('achievement_id', Achievement::UPLOAD_PROFILE_PHOTO);

        return $achievement->exists();
    }
}