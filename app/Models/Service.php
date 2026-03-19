<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    use HasFactory;

    protected $table = 'service';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'price',
    ];

    public function trains(): BelongsToMany
    {
        return $this->belongsToMany(Train::class, 'service_train', 'service_id', 'train_id');
    }
}
