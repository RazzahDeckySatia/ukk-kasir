<?php

namespace App\Http\Controllers;

use App\Exports\salesimport;
use App\Models\customers;
use App\Models\detail_sales;
use App\Models\saless;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade as PDF;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as FacadesExcel;

class DetailSalesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentDate = Carbon::now()->toDateString();
    
        // Hitung jumlah transaksi hari ini
        $todaySalesCount = detail_sales::whereDate('created_at', $currentDate)->count();
        
        // Ambil seluruh data penjualan tanpa batasan bulan atau tahun
        $sales = detail_sales::selectRaw('DATE(created_at) AS date, COUNT(*) AS total')
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->get();
        
        $detail_sales = detail_sales::with('saless', 'product')->get();
        
        // Ubah hasil query menjadi array terstruktur
        $labels = $sales->pluck('date')->map(fn($date) => Carbon::parse($date)->format('d M Y'))->toArray();
        $salesData = $sales->pluck('total')->toArray();
    
        $productShell = detail_sales::with('product')
            ->selectRaw('product_id, SUM(amount) as total_amount')
            ->groupBy('product_id')
            ->get();
        
        // Hitung total penjualan semua produk
        $totalSales = $productShell->sum('total_amount');
    
        // Ambil nama produk sebagai label dan hitung persentase penjualan
        $labelspieChart = $productShell->map(fn($item) => $item->product->name)->toArray();
        $salesDatapieChart = $productShell->map(fn($item) => round(($item->total_amount / $totalSales) * 100, 2))->toArray();
        
        return view('module.dashboard.index', compact('labels', 'salesData', 'detail_sales', 'todaySalesCount', 'productShell', 'labelspieChart', 'salesDatapieChart'));
    }

    /**
     * Display the specified resource.
     */
public function show(Request $request, $id)
{
    // Ambil sale berdasarkan id
    $sale = saless::with('detail_sales.product', 'customer')->findOrFail($id);

    // Jika poin digunakan (member), lakukan pengurangan poin
    if ($request->check_poin) {
        $customer = customers::where('id', $request->customer_id)->first();

        // Pastikan poin mencukupi sebelum digunakan
        $point_value = 100; // 1 poin = Rp. 100
        $max_discount = $sale->total_price; // Diskon maksimal adalah total harga
        $available_discount = $customer->point * $point_value; // Diskon berdasarkan poin yang dimiliki

        // Hitung diskon yang akan digunakan
        $discount = min($available_discount, $max_discount);

        // Hitung poin yang digunakan
        $points_used = floor($discount / $point_value);

        // Hitung harga setelah diskon
        $final_price = $sale->total_price - $discount;

        // Perbarui data penjualan
        $sale->update([
            'total_point' => $points_used,
            'total_pay' => $sale->total_pay - $discount,
            'total_return' => $sale->total_return + $discount,
            'discount' => $discount,
            'final_price' => $final_price,
        ]);

        // Kurangi poin pelanggan
        $customer->update([
            'name' => $request->name ? $request->name : $customer->name,
            'point' => $customer->point - $points_used,
        ]);
    }

    // Jika nama customer diubah, perbarui data customer
    if ($request->name) {
        $customer = customers::where('id', $request->customer_id)->first();
        $customer->update([
            'name' => $request->name,
        ]);
    }

    // Tambahkan poin yang diperoleh dari transaksi (5% dari harga setelah diskon)
    if ($sale->customer) {
        $points_earned = floor(($sale->final_price * 5) / 100 / 100); // 5% dari transaksi, dibagi 100 karena 1 poin = Rp. 100
        $sale->customer->update([
            'point' => $sale->customer->point + $points_earned,
        ]);
    }

    return view('module.pembelian.print-sale', compact('sale'));
}

    /**
     * Download PDF for the specified sale.
     */
    public function downloadPDF($id)
    {
        try {
            // Ambil data penjualan dengan relasi
            $sale = saless::with(['detail_sales.product', 'customer', 'user'])->findOrFail($id);

            // Tentukan nilai per poin (contoh: 1 poin = Rp. 100)
            $point_value = 100;

            // Gunakan total_point yang disimpan saat poin digunakan
            $harga_setelah_poin = $sale->total_price - $sale->discount;

            // Ambil poin yang digunakan (bukan yang tersisa di customer)
            $poin_member = $sale->total_point ?? 0;

            // Load view `download.blade.php` dengan data penjualan
            $pdf = FacadePdf::loadView('module.pembelian.download', compact(
                'sale',
                'point_value',
                'harga_setelah_poin',
                'poin_member'
            ));

            Log::info('PDF berhasil diunduh untuk transaksi dengan ID ' . $id);

            // Unduh file PDF
            return $pdf->download('Bukti_Penjualan_' . $id . '.pdf');
        } catch (\Exception $e) {
            Log::error('Gagal mengunduh PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengunduh PDF');
        }
    }

    /**
     * Export sales data to Excel.
     */
    public function exportexcel()
    {
        try {
            // Ekspor data penjualan menggunakan class salesimport
            return FacadesExcel::download(new salesimport, 'Penjualan.xlsx');
        } catch (\Exception $e) {
            Log::error('Gagal mengekspor Excel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengekspor Excel');
        }
    }

    /**
     * Export sales data for admin to Excel.
     */
    public function exportExcelAdmin()
    {
        try {
            // Ekspor data pembelian menggunakan class salesimport
            return FacadesExcel::download(new salesimport, 'Data_Pembelian_Admin.xlsx');
        } catch (\Exception $e) {
            Log::error('Gagal mengekspor Excel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengekspor Excel');
        }
    }
}