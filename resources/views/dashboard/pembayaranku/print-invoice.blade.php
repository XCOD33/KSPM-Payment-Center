<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Print Invoice</title>

    @include('templates.dashboard.css')
</head>

<body>
    <div class="invoice p-3 mb-3">
        <div class="row">
            <div class="col-12">
                <h4>
                    <i class="fas fa-globe"></i> KSPM UTY
                    <small class="float-right">Tanggal: {{ $data->created_at->format('d-M-Y H:i') }}</small>
                </h4>
            </div>

        </div>

        <div class="row invoice-info">
            <div class="col-sm-4 invoice-col">
                Dari
                <address>
                    <strong>Tim Bendahara</strong><br>
                    Kelompok Studi Pasar Modal<br>
                    Universitas Teknologi Yogyakarta<br>
                    Student Center lt.1 kampus 1 UTY Jombor<br>
                    Email: gibei.uty@gmail.com
                </address>
            </div>

            <div class="col-sm-4 invoice-col">
                Kepada
                <address>
                    <strong>{{ $data->user->name }}</strong><br>
                    Phone: {{ $data->user->phone }}<br>
                    Email: {{ $data->user->email }}
                </address>
            </div>

            <div class="col-sm-4 invoice-col">
                <b class="text-uppercase">Invoice #{{ $data->invoice_id }}</b><br>
                <br>
                <b>Order ID:</b> {{ $data->uuid }}<br>
            </div>

        </div>


        <div class="row">
            <div class="col-12 table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nama Pembayaran</th>
                            <th>Serial #</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $data->pembayaran->name }}</td>
                            <td>#{{ $data->pembayaran->uuid }}</td>
                            <td>Rp{{ number_format($data->pembayaran->nominal, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <div class="row">

            <div class="col-6">
                <p class="lead">Payment Methods:</p>
                <div class="badge badge-success">{{ $data->payment_method_code }}</div>
            </div>

            <div class="col-6">
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th style="width:50%">Subtotal:</th>
                                <td>Rp{{ number_format($data->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>Fee Pembayaran:</th>
                                <td>Rp{{ number_format($data->total_fee, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>Total:</th>
                                <td>Rp{{ number_format($data->total, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    @include('templates.dashboard.js')

    <script>
        $(document).ready(function() {
            window.print();
        });
    </script>
</body>

</html>
