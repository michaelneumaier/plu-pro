<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UPCCode extends Model
{
    use SoftDeletes;

    protected $table = 'upc_codes';

    protected $fillable = [
        'upc',
        'name',
        'description',
        'brand',
        'category',
        'commodity',
        'image_url',
        'has_image',
        'kroger_categories',
        'api_data',
    ];

    protected $casts = [
        'has_image' => 'boolean',
        'kroger_categories' => 'array',
        'api_data' => 'array',
    ];

    /**
     * Get all list items that contain this UPC code
     */
    public function listItems()
    {
        return $this->hasMany(ListItem::class);
    }

    /**
     * Get the display name for this UPC item
     */
    public function getDisplayNameAttribute()
    {
        return $this->name;
    }

    /**
     * Get the display code for this UPC item
     */
    public function getDisplayCodeAttribute()
    {
        return $this->upc;
    }

    /**
     * Scope to search UPC codes by term
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('upc', 'like', '%' . $term . '%')
              ->orWhere('name', 'like', '%' . $term . '%')
              ->orWhere('description', 'like', '%' . $term . '%')
              ->orWhere('brand', 'like', '%' . $term . '%');
        });
    }

    /**
     * Scope to filter by category
     */
    public function scopeByCategory($query, $category)
    {
        if ($category) {
            return $query->where('category', $category);
        }
        return $query;
    }

    /**
     * Scope to filter by commodity
     */
    public function scopeByCommodity($query, $commodity)
    {
        if ($commodity) {
            return $query->where('commodity', $commodity);
        }
        return $query;
    }
}
