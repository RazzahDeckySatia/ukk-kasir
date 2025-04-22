<?php

namespace App\Exports;

use App\Models\saless;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class salesimport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Ambil data untuk diekspor.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Izinkan admin dan employee untuk mengakses data
        if (Auth::user()->role === 'employee' || Auth::user()->role === 'admin') {
            return saless::with('customer', 'user', 'detail_sales.product')->orderBy('id', 'desc')->get();
        }

        // Jika bukan admin atau employee, kembalikan koleksi kosong
        return collect([]);
    }

    /**
     * Tambahkan header untuk file Excel.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID Transaksi',
            'Nama Pembeli',
            'No HP Pembeli',
            'Poin Pembeli',
            'Produk',
            'Total Harga',
            'Total Bayar',
            'Total Diskon Poin',
            'Total Kembalian',
            'Tanggal Pembelian',
        ];
    }

    /**
     * Mapping data untuk setiap baris di file Excel.
     *
     * @param mixed $item
     * @return array
     */
    public function map($item): array
    {
        return [
            $item->id,
            optional($item->customer)->name ?? 'Bukan Member',
            optional($item->customer)->no_hp ?? '-',
            optional($item->customer)->point ?? 0,
            $item->detail_sales->map(function ($detail) {
                return optional($detail->product)->name
                    ? optional($detail->product)->name . ' (' . $detail->amount . ' pcs)'
                    : 'Produk tidak tersedia';
            })->implode(', '), // Menggabungkan semua produk
            $item->total_price,
            $item->total_pay,
            $item->total_price - ($item->total_point ?? 0), // Total harga setelah diskon poin
            $item->total_return,
            $item->created_at->format('d-m-Y H:i:s'), // Format tanggal pembelian
        ];
    }
}