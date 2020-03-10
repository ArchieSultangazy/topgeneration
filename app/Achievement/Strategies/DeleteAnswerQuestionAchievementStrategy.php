<?php


namespace App\Achievement\Strategies;


use App\Achievement\Exceptions\AchievementNotExistsException;
use App\Contracts\AchievementStrategyInterface;
use App\Entities\Achievement;
use App\User;

class DeleteAnswerQuestionAchievementStrategy implements AchievementStrategyInterface
{
    /** @var User $user */
    private $user;
    private $achievement;

    /**
     * DeleteAnswerQuestionAchievementStrategy constructor.
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
            throw new AchievementNotExistsException('Delete answer achievement does not exist.');
        }

        $this->achievement->first()->delete();
    }

    public function exists()
    {
        $this->achievement = $this->user
                                  ->achievements()
                                  ->where('achievement_id', Achievement::QUESTION_ANSWER);

        return $this->achievement->exists();
    }
}