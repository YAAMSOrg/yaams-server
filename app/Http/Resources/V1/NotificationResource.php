<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class NotificationResource extends JsonResource
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
            // Stable short key (e.g. `pirepFiled`) instead of the PHP class
            // name, so moving or renaming a class never breaks clients.
            'type' => Str::camel(class_basename($this->type)),
            'title' => $this->data['title'] ?? null,
            'message' => $this->data['message'] ?? null,
            'url' => $this->data['url'] ?? null,
            'readAt' => $this->read_at,
            'createdAt' => $this->created_at,
        ];
    }
}
