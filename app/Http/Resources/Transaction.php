<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Transaction extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        return [
            'id' => $this->id,
            'owner' => $this->owner,
            'type' => $this->type,
            'approved' => $this->approved,
            'amount' => $this->amount,
            'description' => $this->description,
            'balance_after' => $this->balance_after,
            'pre_transaction' => $this->pre_transaction,
            'order'=> $this->order,
            'date'=> $this->date,
            'imgurl' => $this->imgurl,
          ];
    }
}