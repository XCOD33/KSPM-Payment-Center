@extends('templates.dashboard.app')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Pembayaranku</h1>
                    </div>
                    <div class="col-sm-6">
                        {{--  --}}
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">

            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <!-- Default box -->
                        <div class="card card-outline card-primary">
                            <div class="card-header row d-flex justify-content-between">
                                <h3 class="card-title">Daftar Pembayaranku</h3>
                            </div>
                            <div class="card-body">
                                <div id="container" class="table-responsive">
                                    <table class="table table-bordered table-hover" id="main_table">
                                        <thead>
                                            <tr class="text-center">
                                                <th>#</th>
                                                <th>Nama Pembayaran</th>
                                                <th>Nominal</th>
                                                <th>Tanggal Pembuatan</th>
                                                <th>Tanggal Terakhir Pembayaran</th>
                                                <th>Dibuat Oleh</th>
                                                <th>Status</th>
                                                <th>Status Pembayaran</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                </div>
            </div>
        </section>
        <!-- /.content -->
    </div>

    {{-- viewInvoiceModal --}}
    <div class="modal fade" id="editModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Bukti Pembayaran</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="invoice">

                </div>
                <div class="modal-footer">
                    <input type="hidden" name="uuid_pembayaran_users">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary float-right" style="margin-right: 5px;"
                        onclick="generatePdf()">
                        <i class="fas fa-download"></i> Generate PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            initTable();
        });

        function initTable() {
            $('#container').html('')
            $('#container').html(`
                <table class="table table-bordered table-hover data-table" id="main_table">
                    <thead>
                        <tr class="text-center">
                            <th>#</th>
                            <th>Nama Pembayaran</th>
                            <th>Nominal</th>
                            <th>Tanggal Pembuatan</th>
                            <th>Tanggal Terakhir Pembayaran</th>
                            <th>Dibuat Oleh</th>
                            <th>Status</th>
                            <th>Status Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            `)

            $('#main_table').DataTable({
                processing: true,
                serverSide: true,
                paging: true,
                searching: true,
                ordering: true,
                responsive: true,
                autoWidth: true,
                ajax: "{{ route('pembayaranku.pembayarans') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        className: 'text-center',
                    },
                    {
                        data: 'name',
                        name: 'name',
                        className: 'text-center'
                    },
                    {
                        data: 'nominal',
                        name: 'nominal',
                        className: 'text-center'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        className: 'text-center'
                    },
                    {
                        data: 'expired_at',
                        name: 'expired_at',
                        className: 'text-center'
                    },
                    {
                        data: 'created_by',
                        name: 'created_by',
                        className: 'text-center'
                    },
                    {
                        data: 'status',
                        className: 'text-center',
                        render: function(data, type, row) {
                            return `
                                <span class="badge badge-${row.status == 'Aktif' ? 'success' : 'danger'}">${row.status}</span>
                            `
                        }
                    },
                    {
                        data: 'status_payment',
                        name: 'status_payment',
                        className: 'text-center',
                        render: function(data, type, row) {
                            return `<span class="badge badge-${row.status_payment == 'PAID' ? 'success' : 'secondary'}">${row.status_payment}</span>`
                        }
                    },
                    {
                        data: 'id',
                        name: 'id',
                        render: function(data, type, row) {
                            if (row.status_payment != 'PAID') {
                                return `
                                    <div class="btn-group">
                                        <a href="pembayaran/${row.url}" target="_blank" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>
                                    </div>
                                    `
                            } else {
                                return `
                                    <div class="btn-group">
                                        <a href="pembayaran/${row.url}" target="_blank" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>
                                        <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editModal" onclick="showInvoice('${row.uuid}')"><i class="fas fa-receipt"></i></button>
                                    </div>
                                    `
                            }
                        }
                    }
                ]
            });
        }

        function showInvoice(uuid = null) {
            $.ajax({
                url: "{{ route('pembayaranku.invoice') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    uuid: uuid,
                },
                success: function(response) {
                    console.log(response.data.nominal)
                    $('#invoice').html(`
                        <div class="invoice p-3 mb-3">
                            <div class="row">
                                <div class="col-12">
                                    <h4>
                                        <i class="fas fa-globe"></i> KSPM UTY
                                        <small class="float-right">Tanggal: ${moment(response.data.pembayaran_users[0].created_at).format("DD-MMM-YYYY HH:mm")}</small>
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
                                        <strong>${response.data.pembayaran_users[0].user.name}</strong><br>
                                        Phone: ${response.data.pembayaran_users[0].user.phone}<br>
                                        Email: ${response.data.pembayaran_users[0].user.email}
                                    </address>
                                </div>

                                <div class="col-sm-4 invoice-col">
                                    <b class="text-uppercase">Invoice #${response.data.pembayaran_users[0].invoice_id}</b><br>
                                    <br>
                                    <b>Order ID:</b> ${response.data.pembayaran_users[0].uuid}<br>
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
                                                <td>${response.data.name}</td>
                                                <td>#${response.data.uuid}</td>
                                                <td>${accounting.formatMoney(response.data.nominal, 'Rp', 0, '.', ',')}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-6">
                                    <p class="lead">Payment Methods:</p>
                                    <div class="badge badge-success">${response.data.pembayaran_users[0].payment_method_code}</div>
                                </div>

                                <div class="col-6">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <tbody>
                                                <tr>
                                                    <th style="width:50%">Subtotal:</th>
                                                    <td>${ accounting.formatMoney(response.data.nominal, 'Rp', 0, '.', ',') }</td>
                                                </tr>
                                                <tr>
                                                    <th>Fee Pembayaran:</th>
                                                    <td>${ accounting.formatMoney(response.data.pembayaran_users[0].total_fee, 'Rp', 0, '.', ',') }</td>
                                                </tr>
                                                <tr>
                                                    <th>Total:</th>
                                                    <td>${accounting.formatMoney(response.data.pembayaran_users[0].total, 'Rp', 0, '.', ',')}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `)
                    $('input[name="uuid_pembayaran_users"]').val(response.data.pembayaran_users[0].uuid)
                },
                error: function(err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: err.message,
                    })
                }
            })
        }

        function generatePdf() {
            var uuid = $('input[name="uuid_pembayaran_users"]').val()
            $.ajax({
                url: "{{ url('/dashboard/pembayaranku/print-invoice') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    uuid: uuid,
                },
                success: function(response) {
                    if (response.status == 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'PDF berhasil di generate',
                        }).then((result) => {
                            window.location.href =
                                "{{ url('/dashboard/pembayaranku/view-invoice') }}/" + response
                                .invoice_id
                        })
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.message,
                        })
                    }
                },
                error: function(err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: err.message,
                    })
                }
            })
        }
    </script>
@endsection
