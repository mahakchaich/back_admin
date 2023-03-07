<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PanierResource extends JsonResource
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
            'ancien_prix' => $this->ancien_prix,
            'nouveau_prix' => $this->nouveau_prix,
            'date_debut' => $this->date_debut,
            'date_fin' => $this->date_fin,
            'quantity' => $this->quantity,
            'remaining_quantity' => $this->remaining_quantity,
            'image' => $this->image,
            'categorie' => $this->categorie,
            'status' => $this->status,
        ];
    }
}
