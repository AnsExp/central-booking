<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personal extends Model
{
    use HasFactory;

    protected $table = 'personal';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'role',
        'train_id',
    ];

    public function train()
    {
        return $this->belongsTo(Train::class, 'train_id');
    }
}
