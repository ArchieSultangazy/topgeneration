<?php


namespace App\Entities;

use App\Contracts\AchievementStrategyInterface;

/**
 * Class Achievements
 * @package App\Entities
 */
class Achievement
{
    /**
     * Achievements list.
     */
    const REGISTRATION = 1,
          VIEW_LESSON = 2,
          END_TEST = 3,
          END_COURSE = 4,
          QUESTION_ANSWER = 5,
          QUESTION_ASK = 6,
          WRITE_A_COMMENTARY = 7,
          FILLED_PROFILE_INFO = 8,
          AUTHORIZATION = 9,
          UPLOAD_PROFILE_PHOTO = 10,
          FILLED_REGION_INFO = 11,
          FILLED_BRIEF_INFO = 12;
    /**
     * @var AchievementStrategyInterface $actionStrategy
     */
    private $actionStrategy;

    /**
     * Achievement constructor.
     * @param AchievementStrategyInterface $achievementStrategy
     */
    public function __construct(AchievementStrategyInterface $achievementStrategy)
    {
        $this->actionStrategy = $achievementStrategy;
    }

    /**
     * @throws \App\Achievement\Exceptions\AchievementExistsException
     */
    public function run()
    {
        $this->actionStrategy->execute();
    }

    /**
     * @param AchievementStrategyInterface $achievementStrategy
     */
    public function setNext(AchievementStrategyInterface $achievementStrategy)
    {
        $this->actionStrategy = $achievementStrategy;
    }
}