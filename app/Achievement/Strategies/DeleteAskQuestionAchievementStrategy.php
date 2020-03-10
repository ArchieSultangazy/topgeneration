<?php


namespace App\Achievement\Strategies;


use App\Achievement\Exceptions\AchievementNotExistsException;
use App\Contracts\AchievementStrategyInterface;
use App\Entities\Achievement;
use App\User;

class DeleteAskQuestionAchievementStrategy implements AchievementStrategyInterface
{
    private $user;
    /** @var \App\Models\Achievement $achievement */
    private $achievement;

    /**
     * DeleteAskQuestionAchievementStrategy constructor.
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
            throw new AchievementNotExistsException('Question ask achievement does not exist.');
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
                                  ->where('achievement_id', Achievement::QUESTION_ASK);

        return $this->achievement->exists();
    }
}