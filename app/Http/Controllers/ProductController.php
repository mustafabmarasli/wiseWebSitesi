<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductViewRecorder;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display product detail page.
     */
    public function detail(Request $request, $slug, ProductViewRecorder $viewRecorder)
    {
        $product = Product::with('category')->where('slug', $slug)->firstOrFail();

        $viewRecorder->record($product, $request);

        // Fetch related products in the same category
        $relatedProducts = Product::with('category')->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        return view('detail', compact('product', 'relatedProducts'));
    }

    /**
     * Display products under a category.
     */
    public function category(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $query = Product::with('category')->where('category_id', $category->id);

        // Bu kategoriyle sınırlı arama. Genel arama tüm kanalı tarıyor;
        // ziyaretçi zaten bir kategorinin içindeyse yalnızca orada aramak
        // istiyor olabilir — bunun için genel aramaya geri dönmesi gerekmesin.
        if ($request->filled('q')) {
            $term = $request->get('q');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', '%' . $term . '%')
                  ->orWhere('description', 'LIKE', '%' . $term . '%');
            });
        }

        // Optional filtering by min/max price
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sorting
        $sort = $request->get('sort', 'popular');
        if ($sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } elseif ($sort === 'rating') {
            $query->orderBy('rating', 'desc');
        } else {
            $query->orderBy('rating', 'desc'); // Default to popular/high-rated
        }

        $products = $query->paginate(9)->withQueryString();
        
        $isHealth = $category->channel === 'health';
        $categories = Category::where('channel', $category->channel)->get();

        return view('category', compact('category', 'products', 'categories', 'sort'));
    }

    /**
     * Handle global product search.
     */
    public function search(Request $request)
    {
        $searchTerm = $request->get('q');
        $channel = $request->get('channel', 'electronics');
        
        $query = Product::with('category');

        if ($searchTerm) {
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('description', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        $isHealth = ($channel === 'health');
        $categories = Category::where('channel', $isHealth ? 'health' : 'electronics')->get();
        $categoryIds = $categories->pluck('id');
        $query->whereIn('category_id', $categoryIds);

        $products = $query->paginate(12)->withQueryString();

        return view('category', [
            // Sahte kategori nesnesi, görünümlerin okuduğu TÜM alanları taşımalı.
            // `channel` eksik olduğu için layout "Undefined property" ile patlıyordu.
            'category' => (object) [
                // Blade `{{ }}` zaten kaçırıyor; burada ikinci kez kaçırmak
                // tırnak/& karakterlerini ekranda &quot; olarak gösteriyordu.
                'name' => $searchTerm
                    ? 'Arama Sonuçları: "' . $searchTerm . '"'
                    : 'Tüm Ürünler',
                'description' => 'Aramanız ile eşleşen ürünler listeleniyor.',
                'slug' => 'arama',
                'channel' => $isHealth ? 'health' : 'electronics',
            ],
            'products' => $products,
            'categories' => $categories,
            'sort' => 'popular'
        ]);
    }
}
