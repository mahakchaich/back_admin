<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

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
            'long' => $this->long,
            'lat' => $this->lat,
            'adress' => $this->adress,
            'boxs' => BoxResource::collection($this->whenLoaded('boxs')),
            'is_liked' => $this->did_liked()
        ];
    }

    private function did_liked(){
        
        if(Auth::user()){
            return $this->likes()->where('user_id', Auth::user()->id)->count() ? 1 : 0;
        }

        return 0;
    }
}
