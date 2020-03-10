<?php


namespace App\Contracts;

use App\Achievement\Exceptions\AchievementExistsException;

/**
 * Interface AchievementStrategyInterface
 * @package App\Contracts
 */
interface AchievementStrategyInterface
{
    /**
     * @throws AchievementExistsException
     */
    public function execute();
}