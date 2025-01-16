<?php

namespace App\Http\Resources;


use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {


        return [
            "id"           => $this->id,
            "name"         => $this->name,
            "first_name"       => $this->FirstName,
            "last_name"        => $this->LastName,
            "username"     => $this->username,
            "email"        => $this->email,
            "branch_id"    => $this->branch_id,
            "balance"          => AppLibrary::flatAmountFormat($this->balance),
            "currency_balance" => AppLibrary::currencyAmountFormat($this->balance),
            "phone"        => $this->phone === null ? '' : $this->phone,
            "status"       => $this->status,
            "image"        => $this->image,
            "role_id"          => $this->myRole,
            "order"            => $this->orders->count(),
            "country_code" => $this->country_code,
            "messages"     => $this->messages->count(),
            'create_date'      => AppLibrary::date($this->created_at),
            'update_date'      => AppLibrary::date($this->updated_at),
        ];
    }
}
