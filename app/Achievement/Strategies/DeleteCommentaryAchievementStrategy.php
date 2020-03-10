<?php


namespace App\Achievement\Strategies;


use App\Achievement\Exceptions\AchievementNotExistsException;
use App\Contracts\AchievementStrategyInterface;
use App\Entities\Achievement;
use App\User;

/**
 * Class DeleteCommentaryAchievementStrategy
 * @package App\Achievement\Strategies
 */
class DeleteCommentaryAchievementStrategy implements AchievementStrategyInterface
{
    /** @var User $user */
    private $user;
    /** @var \App\Models\Achievement $achievement */
    private $achievement;

    /**
     * DeleteCommentaryAchievementStrategy constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @throws AchievementNotExistsException
     */
    public function execute()
    {
        if (!$this->exists()) {
            throw new AchievementNotExistsException('Write a commentary achievement does not exist.');
        }

        $this->achievement->first()->delete();
    }

    /**
     * @return bool
     */
    public function exists()
    {
        $this->achievement = $this->user
                            ->achievements()
                            ->where('achievement_id', Achievement::WRITE_A_COMMENTARY);

        return $this->achievement->exists();
    }
}