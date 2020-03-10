<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/**
 * @SWG\Definition(
 *  definition="Password Reset",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="phone", type="string"),
 *  @SWG\Property(property="token", type="string"),
 *  @SWG\Property(property="created_at", type="string"),
 *  @SWG\Property(property="updated_at", type="string"),
 * )
 */
class PasswordReset extends Model
{
    use Notifiable;

    protected $fillable = [
        'phone',
        'code',
    ];

    public function routeNotificationForSmscru()
    {
        return $this->phone;
    }
}
