<?php

namespace App\Http\Resources;

use App\Http\Resources\BoxResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CommandResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'price' => $this->price,
            'status' => $this->status,
            'user' => new UserResource($this->user),
            'boxs' => BoxResource::collection($this->whenLoaded('boxs')),
            'created_at' => $this->created_at,
        ];
    }
}
