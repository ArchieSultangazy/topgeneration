<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/**
 * @SWG\Definition(
 *  definition="Sms Verification",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="phone", type="string"),
 *  @SWG\Property(property="code", type="string"),
 *  @SWG\Property(property="status", type="string"),
 *  @SWG\Property(property="created_at", type="string"),
 *  @SWG\Property(property="updated_at", type="string"),
 * )
 */
class SmsVerification extends Model
{
    use Notifiable;

    const STATUS_PENDING = 'pending';
    const STATUS_VERIFIED = 'verified';

    protected $fillable = [
        'phone',
        'code',
        'status',
    ];

    public function routeNotificationForSmscru()
    {
        return $this->phone;
    }
}
