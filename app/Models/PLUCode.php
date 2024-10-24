<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
}
