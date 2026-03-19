<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meta extends Model
{
    use HasFactory;

    protected $table = 'meta';

    public $timestamps = false;

    protected $fillable = [
        'meta_id',
        'meta_key',
        'meta_format',
        'meta_value',
    ];

    protected $casts = [
        'meta_value' => 'array',
    ];

    public function train(): BelongsTo
    {
        return $this->belongsTo(Train::class, 'meta_id');
    }
}
