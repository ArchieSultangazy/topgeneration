<?php


namespace App\Achievement\Strategies;


use App\Achievement\Exceptions\AchievementExistsException;
use App\Contracts\AchievementStrategyInterface;
use App\Entities\Achievement;
use App\Models\UserAchievement;
use App\User;
use Illuminate\Http\Request;

class FilledProfileInfoAchievementStrategy implements AchievementStrategyInterface
{
    const TYPE_JSON = 1,
          TYPE_ARRAY = 2;

    private $user;
    private $request;
    private $achievement;
    private $requiredFields = [
        'firstname',
        'lastname',
        'email',
        'avatar',
        'status',
        'about',
        'birthday',
        'contacts',
        'region_id',
        'job',
        'specializations'
    ];

    public function __construct(User $user, Request $request)
    {
        $this->user = $user;
        $this->request = $request;
    }

    public function execute()
    {
        if ($this->exists()) {
            if (!$this->check()) {
                $this->delete();
                return;
            }

            throw new AchievementExistsException('Filled profile achievement exists');
        }

        if (!$this->check()) {
            return;
        }

        $achievement = new UserAchievement();
        $achievement->user_id = $this->user->id;
        $achievement->achievement_id = Achievement::FILLED_PROFILE_INFO;
        $achievement->save();
    }

    public function exists()
    {
        $this->achievement = $this->user
                                  ->achievements()
                                  ->where('achievement_id', Achievement::FILLED_PROFILE_INFO);

        return $this->achievement->exists();
    }

    public function check()
    {
        //TODO: refactor
        if (empty($this->request->input('firstname', null))) {
            return false;
        }

        if (empty($this->request->input('lastname', null))) {
            return false;
        }

        if (empty($this->request->input('email', null))) {
            return false;
        }

        if (empty($this->request->input('status', null))) {
            return false;
        }

        if (empty($this->request->input('about', null))) {
            return false;
        }

        if (empty($this->request->input('birth_date', null))) {
            return false;
        }

        if (!$this->checkNested($this->request->input('specializations', null))) {
            return false;
        }

        if (!$this->checkNested($this->request->input('contacts', null))) {
            return false;
        }

        if (!$this->checkNested($this->request->input('job', null), self::TYPE_ARRAY)) {
            return false;
        }

        if (empty($this->request->input('region_id', null))) {
            return false;
        }

        return true;
    }

    public function delete()
    {
        $this->achievement->delete();
    }


    private function checkNested($data, $type = self::TYPE_JSON)
    {
        if ($type == self::TYPE_JSON) {
            $data = json_decode($data, true);
        }

        if (!$data) {
            return false;
        }

        $data = collect($data);

        $result = true;

        $data->each(function ($value, $key) use (&$result) {
            if (empty($value)) {
                $result = false;
            }
        });

        return $result;
    }
}