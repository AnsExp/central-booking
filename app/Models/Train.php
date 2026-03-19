<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Train extends Model
{
    use HasFactory;

    protected $table = 'train';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'capacity',
        'code',
        'status',
        'type',
    ];

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_train', 'train_id', 'service_id');
    }

    public function metadataEntries(): HasMany
    {
        return $this->hasMany(Meta::class, 'meta_id', 'id');
    }

    public function routes(): BelongsToMany
    {
        return $this->belongsToMany(Route::class, 'route_train', 'train_id', 'route_id');
    }
}
