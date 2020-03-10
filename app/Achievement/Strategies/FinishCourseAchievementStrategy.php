<?php


namespace App\Achievement\Strategies;


use App\Achievement\Exceptions\CourseIsNotFinishedException;
use App\Contracts\AchievementStrategyInterface;
use App\Entities\Achievement;
use App\Models\UserAchievement;
use App\User;

/**
 * Class FinishCourseAchievementStrategy
 * @package App\Achievement\Strategies
 */
class FinishCourseAchievementStrategy implements AchievementStrategyInterface
{
    const MUST_EQUALS = 1;

    /** @var User $user */
    private $user;
    private $lessonsFinished;

    /**
     * FinishCourseAchievementStrategy constructor.
     * @param User $user
     * @param $lessonsFinished
     */
    public function __construct(User $user, $lessonsFinished)
    {
        $this->user = $user;
        $this->lessonsFinished = $lessonsFinished;
    }

    /**
     * @throws CourseIsNotFinishedException
     */
    public function execute()
    {
        if (!$this->isCourseFinished()) {
            throw new CourseIsNotFinishedException("Course is not finished");
        }

        $achievement = new UserAchievement();
        $achievement->user_id = $this->user->id;
        $achievement->achievement_id = Achievement::END_COURSE;
        $achievement->save();
    }

    /**
     * @return bool
     */
    public function isCourseFinished()
    {
        return $this->lessonsFinished == self::MUST_EQUALS;
    }
}