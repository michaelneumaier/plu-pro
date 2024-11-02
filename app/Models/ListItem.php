<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListItem extends Model
{
    protected $fillable = ['plu_code_id', 'user_list_id', 'inventory_level'];

    public function userList()
    {
        return $this->belongsTo(UserList::class);
    }

    public function pluCode()
    {
        return $this->belongsTo(PLUCode::class, 'plu_code_id');
    }
}
