<?php
namespace App\Http\Resources\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;



class RoleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,

            'permissions' => $this->permissions->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                ];
            }),

            'created_at' => $this->created_at,
        ];
    }
}
