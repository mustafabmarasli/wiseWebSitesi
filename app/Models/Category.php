<?php

namespace App\Models;

use App\Models\Concerns\ResolvesImagePaths;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;
    use ResolvesImagePaths;

    protected $fillable = [
        'name',
        'slug',
        'channel',
        'google_product_category',
        'description',
        'image_path',
    ];

    /**
     * Get the products for the category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
