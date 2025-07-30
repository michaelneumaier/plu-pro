<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListItem extends Model
{
    protected $fillable = [
        'user_list_id', 
        'item_type', 
        'plu_code_id', 
        'upc_code_id', 
        'inventory_level', 
        'organic'
    ];

    protected $casts = [
        'organic' => 'boolean',
        'inventory_level' => 'decimal:1',
    ];

    /**
     * Get the user list that owns this item
     */
    public function userList()
    {
        return $this->belongsTo(UserList::class);
    }

    /**
     * Get the PLU code if this is a PLU item
     */
    public function pluCode()
    {
        return $this->belongsTo(PLUCode::class, 'plu_code_id');
    }

    /**
     * Get the UPC code if this is a UPC item
     */
    public function upcCode()
    {
        return $this->belongsTo(UPCCode::class, 'upc_code_id');
    }

    /**
     * Get the actual item (PLU or UPC) based on item_type
     */
    public function getItemAttribute()
    {
        return $this->item_type === 'plu' ? $this->pluCode : $this->upcCode;
    }

    /**
     * Get the display name for this item
     */
    public function getDisplayNameAttribute()
    {
        if ($this->item_type === 'plu') {
            return $this->pluCode->variety ?? $this->pluCode->commodity;
        }
        return $this->upcCode->name;
    }

    /**
     * Get the display code for this item
     */
    public function getDisplayCodeAttribute()
    {
        if ($this->item_type === 'plu') {
            return $this->organic ? '9' . $this->pluCode->plu : $this->pluCode->plu;
        }
        return $this->upcCode->upc;
    }

    /**
     * Get the commodity for this item
     */
    public function getCommodityAttribute()
    {
        return $this->item_type === 'plu' 
            ? $this->pluCode->commodity 
            : $this->upcCode->commodity;
    }

    /**
     * Get the category for this item
     */
    public function getCategoryAttribute()
    {
        return $this->item_type === 'plu' 
            ? $this->pluCode->category 
            : $this->upcCode->category;
    }

    /**
     * Scope to filter by item type
     */
    public function scopeByItemType($query, $type)
    {
        return $query->where('item_type', $type);
    }

    /**
     * Scope to get items with inventory
     */
    public function scopeWithInventory($query)
    {
        return $query->where('inventory_level', '>', 0);
    }
}
