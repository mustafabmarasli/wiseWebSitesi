<div class="p-4 bg-white rounded-xl shadow-sm border border-slate-100">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-sm font-black text-slate-800">Tüm Verileri Dışa Aktar</h3>
            <p class="text-xs text-slate-500 mt-0.5">Tek tıkla tüm istatistikleri CSV olarak indirin</p>
        </div>
        <button wire:click="exportAll" class="bg-trendyol hover:bg-trendyolDark text-white px-5 py-2.5 rounded-lg text-xs font-extrabold transition-all shadow-sm hover:shadow flex items-center gap-2">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Excel İndir (CSV)</span>
        </button>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
        <div class="bg-slate-50 rounded-lg p-3 border border-slate-100">
            <div class="text-slate-400 font-semibold">Toplam Ciro</div>
            <div class="text-lg font-black text-slate-800 mt-0.5">₺{{ number_format($stats['total_revenue'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-slate-50 rounded-lg p-3 border border-slate-100">
            <div class="text-slate-400 font-semibold">Toplam Sipariş</div>
            <div class="text-lg font-black text-slate-800 mt-0.5">{{ $stats['total_orders'] }}</div>
        </div>
        <div class="bg-slate-50 rounded-lg p-3 border border-slate-100">
            <div class="text-slate-400 font-semibold">Toplam Müşteri</div>
            <div class="text-lg font-black text-slate-800 mt-0.5">{{ $stats['total_users'] }}</div>
        </div>
        <div class="bg-slate-50 rounded-lg p-3 border border-slate-100">
            <div class="text-slate-400 font-semibold">Ort. Sepet</div>
            <div class="text-lg font-black text-slate-800 mt-0.5">₺{{ number_format($stats['avg_order_value'], 2, ',', '.') }}</div>
        </div>
    </div>
</div>