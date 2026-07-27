<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Redirect to the main landing page portal.
     */
    public function index()
    {
        return redirect()->route('landing');
    }

    /**
     * Display the Electronics Store.
     */
    public function electronics()
    {
        $channel = 'electronics';
        $channelTitle = 'Elektronik';
        
        $categories = Category::where('channel', 'electronics')->get();
        $categoryIds = $categories->pluck('id');

        // 1. All Products
        $allProducts = Product::with('category')->whereIn('category_id', $categoryIds)->get();

        // 2. Popular Products (first 8 by satis_sayisi desc)
        $popularProducts = Product::with('category')->whereIn('category_id', $categoryIds)
            ->orderBy('satis_sayisi', 'desc')
            ->take(8)
            ->get();

        // 3. Discounted Products (where eski_fiyat > price)
        $discountedProducts = Product::with('category')->whereIn('category_id', $categoryIds)
            ->whereNotNull('eski_fiyat')
            ->whereColumn('eski_fiyat', '>', 'price')
            ->get();

        // 4. Showcase Products (featured with fallback)
        $showcaseProducts = Product::with('category')
            ->whereIn('category_id', $categoryIds)
            ->where('is_featured', true)
            ->orderByDesc('stock')
            ->take(2)
            ->get();

        if ($showcaseProducts->count() < 2) {
            $excludeIds = $showcaseProducts->pluck('id')->toArray();
            $fallback = Product::with('category')
                ->whereIn('category_id', $categoryIds)
                ->whereNotIn('id', $excludeIds)
                ->orderByDesc('satis_sayisi')
                ->get()
                ->sortByDesc(fn ($p) => $p->stock > 0)
                ->take(2 - $showcaseProducts->count());

            $showcaseProducts = $showcaseProducts->concat($fallback);
        }

        return view('home', compact('categories', 'popularProducts', 'discountedProducts', 'allProducts', 'showcaseProducts', 'channel', 'channelTitle'));
    }

    /**
     * Display the Health/Medical Store.
     */
    public function health()
    {
        $channel = 'health';
        $channelTitle = 'Sağlık & Lens';

        $categories = Category::where('channel', 'health')->get();
        $categoryIds = $categories->pluck('id');

        // 1. All Products
        $allProducts = Product::with('category')->whereIn('category_id', $categoryIds)->get();

        // 2. Popular Products (first 8 by satis_sayisi desc)
        $popularProducts = Product::with('category')->whereIn('category_id', $categoryIds)
            ->orderBy('satis_sayisi', 'desc')
            ->take(8)
            ->get();

        // 3. Discounted Products (where eski_fiyat > price)
        $discountedProducts = Product::with('category')->whereIn('category_id', $categoryIds)
            ->whereNotNull('eski_fiyat')
            ->whereColumn('eski_fiyat', '>', 'price')
            ->get();

        // 4. Showcase Products (featured with fallback)
        $showcaseProducts = Product::with('category')
            ->whereIn('category_id', $categoryIds)
            ->where('is_featured', true)
            ->orderByDesc('stock')
            ->take(2)
            ->get();

        if ($showcaseProducts->count() < 2) {
            $excludeIds = $showcaseProducts->pluck('id')->toArray();
            $fallback = Product::with('category')
                ->whereIn('category_id', $categoryIds)
                ->whereNotIn('id', $excludeIds)
                ->orderByDesc('satis_sayisi')
                ->get()
                ->sortByDesc(fn ($p) => $p->stock > 0)
                ->take(2 - $showcaseProducts->count());

            $showcaseProducts = $showcaseProducts->concat($fallback);
        }

        return view('home', compact('categories', 'popularProducts', 'discountedProducts', 'allProducts', 'showcaseProducts', 'channel', 'channelTitle'));
    }
}
