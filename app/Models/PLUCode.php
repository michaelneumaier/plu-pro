<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PLUCode extends Model
{
    use HasFactory, SoftDeletes;

    // Disable auto-incrementing since 'id' comes from CSV
    public $incrementing = false;

    // Specify the primary key type
    protected $keyType = 'integer';

    // Define the table name if not following Laravel's naming convention
    protected $table = 'plu_codes';

    // Mass assignable attributes
    protected $fillable = [
        'id',
        'plu',
        'type',
        'category',
        'commodity',
        'variety',
        'size',
        'measures_na',
        'measures_row',
        'restrictions',
        'botanical',
        'aka',
        'status',
        'link',
        'notes',
        'updated_by',
        'language',
    ];

    public function listItems()
    {
        return $this->hasMany(ListItem::class, 'plu_code_id');
    }

    /**
     * Get the URL for the regular PLU page
     */
    public function getUrl(): string
    {
        return url("/{$this->plu}");
    }

    /**
     * Get the URL for the organic PLU page
     */
    public function getOrganicUrl(): string
    {
        return url("/9{$this->plu}");
    }

    /**
     * Get SEO-friendly title for this PLU
     */
    public function getSeoTitle($isOrganic = false): string
    {
        $prefix = $isOrganic ? 'Organic ' : '';
        $pluDisplay = $isOrganic ? "9{$this->plu}" : $this->plu;

        return "{$prefix}PLU Code {$pluDisplay}: {$this->variety} - Price Lookup Code Information";
    }
}
