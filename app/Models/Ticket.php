<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    protected $table = 'ticket';

    public $timestamps = false;

    protected $fillable = [
        'total_amount',
        'purchase_date',
        'travel_date',
        'train_id',
        'route_id',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class, 'route_id');
    }

    public function train(): BelongsTo
    {
        return $this->belongsTo(Train::class, 'train_id');
    }
}
