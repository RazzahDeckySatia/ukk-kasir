<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Penjualan</title>
    <style>
        #receipt {
            box-shadow: 5px 10px 15px rgba(0, 0, 0, 0.5);
            padding: 20px;
            margin: 30px auto 0 auto;
            width: 500px;
            background: #fff;
        }

        h2 {
            font-size: .9rem;
        }

        p {
            font-size: .8rem;
            color: #666;
            line-height: 1.2rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 5px 0 5px 15px;
            border: 1px solid #eee;
        }

        .tabletitle {
            font-size: .9rem;
            background: #eee;
        }

        .itemtext {
            font-size: .8rem;
        }

        #legalcopy {
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div id="receipt">
        <h2>Indo April</h2>
        <div style="display: flex; justify-content: space-between;">
            <div>
                <small>
                    MMember Status: {{ $sale->customer ? 'Member' : 'Bukan Member' }}<br>
                    No. HP: {{ $sale->customer->no_hp ?? '-' }}<br>
                    Bergabung Sejak: 
                    {{ $sale->customer ? \Carbon\Carbon::parse($sale->customer->created_at)->timezone('Asia/Jakarta')->translatedFormat('d F Y') : '-' }}<br>
                    Poin Member: {{ number_format($poin_member) }}
                </small>
            </div>
        </div>

        <div style="margin-top: 20px">
            <table>
                <tr class="tabletitle">
                    <td>Nama Produk</td>
                    <td>QTy</td>
                    <td>Harga</td>
                    <td>Sub Total</td>
                </tr>
                @foreach ($sale->detail_sales as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->amount }}</td>
                        <td>Rp. {{ number_format($item->product->price, 0, ',', '.') }}</td>
                        <td>Rp. {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="tabletitle">
                    <td></td>
                    <td></td>
                    <td>Total Harga</td>
                    <td>Rp. {{ number_format($sale->total_price, 0, ',', '.') }}</td>
                </tr>
                <tr class="tabletitle">
                    <td>Poin Diterima</td>
                    <td>{{ $sale->point }}</td>
                    <td>Harga Setelah Poin</td>
                    <td>Rp. {{ number_format($harga_setelah_poin, 0, ',', '.') }}</td>
                </tr>
                <tr class="tabletitle">
                    <td></td>
                    <td></td>
                    <td>Total Kembalian</td>
                    <td>Rp. {{ number_format($sale->total_return, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div id="legalcopy">
            <center>
                <p>{{ \Carbon\Carbon::parse($sale->created_at)->timezone('Asia/Jakarta')->format('d F Y H:i:s') }} | {{ $sale->user->name }}</p>
                <p><strong>Terima kasih atas pembelian Anda!</strong></p>
            </center>
        </div>
    </div>
</body>

</html>
