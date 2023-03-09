<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => $this->password,
            'image' => $this->image,
            'category' => $this->category,
            'openingtime' => $this->openingtime,
            'closingtime' => $this->closingtime,
            'boxs' => BoxResource::collection($this->whenLoaded('boxs')),
        ];
    }
}
