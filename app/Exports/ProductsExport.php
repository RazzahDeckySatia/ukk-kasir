<?php

namespace App\Exports;

use App\Models\products;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return products::select('id', 'name', 'price', 'stock')->get();
    }

    public function headings(): array
    {
        return ['ID', 'Nama Produk', 'Harga', 'Stok'];
    }
}