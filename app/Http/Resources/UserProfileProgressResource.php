<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileProgressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = $this->resource ? [
            'id' => $this->id,
            'ruName' => $this->ru_name,
            'kkName' => $this->kk_name,
            'enName' => $this->en_name,
            'key' => $this->key,
            'points' => $this->points,
            'isAchieved' => $this->isAchieved,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'deletedAt' => $this->deleted_at,
        ] : [];

        return $data;
    }
}
