<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BriefTestStatisticResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource ? [
            'usersReached' => $this->usersReached,
            'usersComplete' => $this->usersComplete,
            'avgTries' => $this->avgTries,
            'avgResult' => $this->avgResult,
            'avgCompleteTime' => $this->avgCompleteTime,
        ] : [];
    }
}
