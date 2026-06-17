<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TimeSpent extends Model
{
    protected $table = 'user_time_spent';

    // UUID primary key
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'time_spent',
        'url',
    ];

    protected $casts = [
        'time_spent' => 'integer',
        'user_id' => 'string',
        'url' => 'string',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
