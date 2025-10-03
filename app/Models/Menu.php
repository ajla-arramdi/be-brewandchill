<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;
      protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
    ];

    // Relasi ke Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
