<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'order_id'     => $this->order_id,
            'session_id'   => $this->order_session_id,
            'amount'       => (float) $this->amount,
            'method'       => $this->method,
            'status'       => $this->status,
            'reference'    => $this->reference,
            'notes'        => $this->notes,
            'collected_by' => $this->whenLoaded('collector', fn() => [
                'id'   => $this->collector->id,
                'name' => $this->collector->name,
            ]),
            'order'        => $this->whenLoaded('order', fn() => [
                'id'       => $this->order->id,
                'total'    => (float) $this->order->total,
                'status'   => $this->order->status,
                'employee' => $this->order->user?->name,
            ]),
            'created_at'   => $this->created_at->toISOString(),
        ];
    }
}
