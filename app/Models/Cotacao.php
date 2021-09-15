<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cotacao extends Model
{
    use HasFactory;
    protected $fillable = [
        'm_simbolo',
        'compra',
        'venda',
    ];
}
