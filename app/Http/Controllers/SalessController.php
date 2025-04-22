<?php

namespace App\Http\Controllers;

use App\Models\customers;
use App\Models\detail_sales;
use App\Models\products;
use App\Models\saless;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $saless = saless::with('customer', 'user', 'detail_sales')->orderBy('id', 'desc')->get();
        return view('module.pembelian.index', compact('saless'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = products::all();
        return view('module.pembelian.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$request->has('shop')) {
            return back()->with('error', 'Pilih produk terlebih dahulu!');
        }

        session()->forget('shop');

        $selectedProducts = $request->shop;

        if (!is_array($selectedProducts)) {
            return back()->with('error', 'Format data tidak valid!');
        }

        $filteredProducts = collect($selectedProducts)
            ->mapWithKeys(function ($item) {
                $parts = explode(';', $item);
                if (count($parts) > 3) {
                    $id = $parts[0];
                    return [$id => $item];
                }
                return [];
            })
            ->values()
            ->toArray();

        session(['shop' => $filteredProducts]);

        return redirect()->route('sales.post');
    }

    public function post()
    {
        $shop = session('shop', []);
        return view('module.pembelian.detail', compact('shop'));
    }

    public function createsales(Request $request)
    {
        $request->validate([
            'total_pay' => 'required',
        ], [
            'total_pay.required' => 'Berapa jumlah uang yang dibayarkan?',
        ]);

        $newPrice = (int) preg_replace('/\D/', '', $request->total_price); // Total harga awal
        $newPay = (int) preg_replace('/\D/', '', $request->total_pay); // Total pembayaran
        $newreturn = $newPay - $newPrice; // Kembalian

        $discount = 0; // Diskon awal
        $finalPrice = $newPrice; // Harga setelah diskon

        if ($request->member === 'Member') {
            $existCustomer = customers::where('no_hp', $request->no_hp)->first();
            $pointEarned = floor($newPrice / 100); // 1 poin untuk setiap Rp. 100

            if ($existCustomer) {
                if ($request->use_points) {
                    $pointsUsed = min($existCustomer->point, floor($newPrice / 100));
                    $discount = $pointsUsed * 100; // 1 poin = Rp. 100
                    $finalPrice = $newPrice - $discount;

                    $existCustomer->update([
                        'point' => $existCustomer->point - $pointsUsed,
                    ]);
                }

                $existCustomer->update([
                    'point' => $existCustomer->point + $pointEarned,
                ]);

                $customer_id = $existCustomer->id;
            } else {
                $existCustomer = customers::create([
                    'name' => "",
                    'no_hp' => $request->no_hp,
                    'point' => $pointEarned,
                ]);
                $customer_id = $existCustomer->id;
            }

            $sales = saless::create([
                'sale_date' => Carbon::now()->format('Y-m-d'),
                'total_price' => $newPrice,
                'total_pay' => $newPay,
                'total_return' => $newreturn,
                'customer_id' => $customer_id,
                'user_id' => Auth::id(),
                'point' => $pointEarned,
                'total_point' => $existCustomer->point,
                'discount' => $discount,
                'final_price' => $finalPrice,
            ]);
        } else {
            $sales = saless::create([
                'sale_date' => Carbon::now()->format('Y-m-d'),
                'total_price' => $newPrice,
                'total_pay' => $newPay,
                'total_return' => $newreturn,
                'customer_id' => $request->customer_id,
                'user_id' => Auth::id(),
                'point' => 0,
                'total_point' => 0,
                'discount' => 0,
                'final_price' => $newPrice,
            ]);
        }

        $detailSalesData = [];
        foreach ($request->shop as $shopItem) {
            $item = explode(';', $shopItem);
            $productId = (int) $item[0];
            $amount = (int) $item[3];
            $subtotal = (int) $item[4];

            $detailSalesData[] = [
                'sale_id' => $sales->id,
                'product_id' => $productId,
                'amount' => $amount,
                'subtotal' => $subtotal,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $product = products::find($productId);
            if ($product) {
                $newStock = $product->stock - $amount;
                if ($newStock < 0) {
                    return redirect()->back()->withErrors(['error' => 'Stok tidak mencukupi untuk produk ' . $product->name]);
                }
                $product->update(['stock' => $newStock]);
            }
        }
        detail_sales::insert($detailSalesData);

        if ($request->member === 'Member') {
            return redirect()->route('sales.create.member', ['id' => $sales->id])
                ->with('message', 'Silahkan daftar sebagai member');
        } else {
            return redirect()->route('sales.print.show', ['id' => $sales->id])->with('message', 'Silahkan Print');
        }
    }

    public function createmember($id)
    {
        $sale = saless::with('detail_sales.product')->findOrFail($id);
        $notFirst = saless::where('customer_id', $sale->customer->id)->count() != 1 ? true : false;
        return view('module.pembelian.view-member', compact('sale', 'notFirst'));
    }

    public function edit(saless $saless)
    {
        //
    }

    public function update(Request $request, saless $saless)
    {
        //
    }

    public function destroy(saless $saless)
    {
        //
    }
}