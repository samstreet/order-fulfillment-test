<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\OrderItem
 */
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
            'product_name' => $this->product_name,
            'quantity' => $this->quantity,
            'unit_price' => [
                'value' => (float) $this->unit_price,
                'formatted' => $this->formatted_unit_price,
            ],
            'subtotal' => [
                'value' => (float) $this->subtotal,
                'formatted' => $this->formatted_subtotal,
            ],
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
