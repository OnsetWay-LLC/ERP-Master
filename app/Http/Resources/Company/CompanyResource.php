<?php

namespace App\Http\Resources\Company;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,

            'country' => $this->country,
            'currency_code' => $this->currency_code,

            'established_at' => $this->established_at?->format('Y-m-d'),

            'fax' => $this->fax,
            'phone' => $this->phone,
            'email' => $this->email,

            'departments_count' => $this->departments_count,

            'working_days' => $this->whenLoaded('workingDays', function () {
                return $this->workingDays
                    ->sortBy(fn ($item) => array_search($item->day, [
                        'saturday',
                        'sunday',
                        'monday',
                        'tuesday',
                        'wednesday',
                        'thursday',
                        'friday',
                    ]))
                    ->values()
                    ->map(fn ($day) => [
                        'id' => $day->id,
                        'day' => $day->day,
                        'is_working_day' => $day->is_working_day,
                    ]);
            }),

            'created_at' => $this->created_at?->format('Y-m-d H:i'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i'),
        ];
    }
}