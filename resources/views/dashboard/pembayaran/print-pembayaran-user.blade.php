<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Print Pembayaran - {{ $pembayaran->name }}</title>

    @include('templates.dashboard.css')
</head>

<body>

    <div class="container my-3">

        <h3 class="text-center mb-3">Report Pembayaran : {{ $pembayaran->name }}</h3>

        <table class="table table-bordered text-xs">
            <thead>
                <tr class="text-center">
                    <th>#</th>
                    <th>Nama User</th>
                    <th>NIM</th>
                    <th>Jabatan</th>
                    <th>Metode Pembayaran</th>
                    <th>Total Fee</th>
                    <th>Subtotal</th>
                    <th>Total</th>
                    <th>Tanggal Pembayaran</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $data)
                    <tr>
                        <td class="text-center">
                            {{ $loop->iteration }}
                        </td>
                        <td>
                            {{ $data->name }}
                        </td>
                        <td class="text-center">
                            {{ $data->nim }}
                        </td>
                        <td class="text-center">
                            {{ $data->position }}
                        </td>
                        <td class="text-center">
                            {{ $data->payment_method }}
                        </td>
                        <td class="text-center">
                            Rp{{ number_format($data->total_fee, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            Rp{{ number_format($data->subtotal, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            Rp{{ number_format($data->total, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            {{ $data->created_at != null ? $data->created_at->format('d-M-Y H:i') : '-' }}
                        </td>
                        <td class="text-center">
                            {{ $data->status }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="text-center">
                    <th colspan="5">Total</th>
                    <th>Rp{{ number_format($sum['total_fee'], 0, ',', '.') }}</th>
                    <th>Rp{{ number_format($sum['subtotal'], 0, ',', '.') }}</th>
                    <th>Rp{{ number_format($sum['total'], 0, ',', '.') }}</th>
                    <th colspan="2"></th>
                </tr>
            </tfoot>
        </table>

        <p class="text-right">Tanggal Print : {{ now()->format('d-M-Y H:i') }}</p>
    </div>

    @include('templates.dashboard.js')

    <script>
        $(document).ready(function() {
            window.print();
        });

        function goBack() {
            window.history.back();
        }
    </script>
</body>

</html>
