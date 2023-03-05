<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommandeResource extends JsonResource
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
            'date_cmd' => $this->date_cmd,
            'heure_cmd' => $this->heure_cmd,
            'user_id' => $this->user_id,
            'user_email' => $this->user->email,
            'total_prix' => $this->total_prix,
            'statut' => $this->statut,
            'commande_paniers' => $this->commandePaniers,
        ];
    }
}
