<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'categories_id',
        'tags',
    ];

    public function galleries()
    {
        // relasi one to many
        return $this->hasMany(ProductGallery::class, 'products_id', 'id');
    }

    public function category()
    {
        // relasi one to many
        return $this->BelongsTo(ProductCategory::class, 'categories_id', 'id');
    }
}
