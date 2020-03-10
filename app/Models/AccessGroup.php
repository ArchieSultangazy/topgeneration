<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(
 *  definition="AccessGroup",
 *  @SWG\Property(property="id", type="integer"),
 *  @SWG\Property(property="name", type="string"),
 *  @SWG\Property(property="description", type="string"),
 * )
 */
class AccessGroup extends Model
{
	protected $table = 'access_group';

	protected $fillable = [
		'name',
		'description',
	];

	public function users()
	{
		return $this->belongsToMany(User::class, 'access_user', 'group_id', 'user_id');
	}
}
