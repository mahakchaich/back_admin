<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BoxResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return parent::toArray($request);
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'oldprice' => $this->oldprice,
            'newprice' => $this->newprice,
            'startdate' => $this->startdate,
            'enddate' => $this->enddate,
            'quantity' => $this->quantity,
            'remaining_quantity' => $this->remaining_quantity,
            'image' => $this->image,
            'category' => $this->category,
            'status' => $this->status,
        ];
    }
}
