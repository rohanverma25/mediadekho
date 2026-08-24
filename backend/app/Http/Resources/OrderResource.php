<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'currency' => $this->currency,
            'subtotal' => (float) $this->subtotal,
            'discount_total' => (float) $this->discount_total,
            'tax_total' => (float) $this->tax_total,
            'grand_total' => (float) $this->grand_total,
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'category' => $item->category,
                'quantity' => $item->quantity,
                'list_price' => (float) $item->list_price,
                'discount_amount' => (float) $item->discount_amount,
                'unit_price' => (float) $item->unit_price,
                'tax_percentage' => (float) $item->tax_percentage,
                'tax_amount' => (float) $item->tax_amount,
                'line_total' => (float) $item->line_total,
                'slug' => $item->inventory?->slug,
            ])),
        ];
    }
}
