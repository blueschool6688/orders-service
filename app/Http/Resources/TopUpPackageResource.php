<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TopUpPackageResource extends JsonResource
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
            'id'=> $this->id??null,
            'name'=> $this->name??null,
            'points'=>$this->points??null,
            'bonus'=>$this->bonus??null,
            'price'=>$this->price??null,
            'status'=>$this->status??null,
        ];
    }
}
