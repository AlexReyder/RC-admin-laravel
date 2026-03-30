<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flat extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'flats';

    protected $fillable = [
        'rooms_number',
        'rooms_number_true',
        'floor',
        'square',
        'entrance_number',
        'living_square',
        'ceiling_height',
        'plan',
        'sold',
        'building',
        'number',
        'price',
        'price_m2',
        'floor_position',
        'finish_date',
        'finishing',
        'action',
        'action_price_m2',
        'title',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'square' => 'decimal:2',
            'living_square' => 'decimal:2',
            'ceiling_height' => 'decimal:2',
            'sold' => 'boolean',
            'action' => 'boolean',
        ];
    }
}