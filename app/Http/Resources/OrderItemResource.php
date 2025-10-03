<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'menu_id' => $this->menu_id,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'menu' => $this->whenLoaded('menu', function () {
                return [
                    'id' => $this->menu->id,
                    'name' => $this->menu->name,
                    'price' => $this->menu->price,
                    'description' => $this->menu->description,
                ];
            }),
        ];
    }
}
