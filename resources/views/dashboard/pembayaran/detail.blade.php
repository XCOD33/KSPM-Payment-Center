@extends('templates.dashboard.app')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>{{ $pembayaran->name }}</h1>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Detail Pembayaran</h3>
                            <a href="{{ $backUrl }}" class="btn btn-sm btn-primary"><i
                                    class="fas fa-angle-left mr-2"></i>
                                Kembali</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table table-responsive" id="dt-container">
                            <table class="table table-striped" id="dt-data">
                                <thead>
                                    <tr>
                                        <th class="text-center">No</th>
                                        <th class="text-center">Nama</th>
                                        <th class="text-center">NIM</th>
                                        <th class="text-center">Jabatan</th>
                                        <th class="text-center">Roles</th>
                                        <th class="text-center">Metode Pembayaran</th>
                                        <th class="text-center">Total Fee</th>
                                        <th class="text-center">Subtotal</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            dataTable()
        });

        function dataTable() {
            $('#dt-container').html('');
            $('#dt-container').html(`
                <table class="table table-striped text-sm" id="dt-data">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th class="text-center">Nama</th>
                            <th class="text-center">NIM</th>
                            <th class="text-center">Jabatan</th>
                            <th class="text-center">Roles</th>
                            <th class="text-center">Metode Pembayaran</th>
                            <th class="text-center">Total Fee</th>
                            <th class="text-center">Subtotal</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Tanggal Pembayaran</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="align-middle">Total:</th>
                            <th class="align-middle"></th>
                            <th class="align-middle"></th>
                            <th class="align-middle"></th>
                            <th class="align-middle"></th>
                            <th colspan="3" class="align-middle"></th>
                        </tr>
                    </tfoot>
                </table>
            `);
            $('#dt-data').DataTable({
                processing: true,
                serverSide: true,
                pageLength: -1, // Menampilkan semua baris
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ], // Opsi untuk jumlah baris per halaman
                order: [
                    [9, 'desc']
                ], // Urutan default berdasarkan kolom ke-9 (created_at)
                ajax: "{{ route('pembayaran.detail_pembayaran', $pembayaran->uuid) }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'nim',
                        name: 'nim'
                    },
                    {
                        data: 'position',
                        name: 'position'
                    },
                    {
                        data: 'roles',
                        name: 'roles'
                    },
                    {
                        data: 'payment_method',
                        name: 'payment_method',
                        className: 'text-center'
                    },
                    {
                        data: 'total_fee',
                        name: 'total_fee',
                        className: 'text-center',
                        render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ')
                    },
                    {
                        data: 'subtotal',
                        name: 'subtotal',
                        className: 'text-center',
                        render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ')
                    },
                    {
                        data: 'total',
                        name: 'total',
                        className: 'text-center',
                        render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ')
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        className: 'text-center'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        className: 'text-center'
                    },
                    {
                        data: 'id',
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (row.status != 'Sudah Bayar') {
                                return `
                                    <button class="btn btn-sm btn-success" onclick="updateStatus('{{ $pembayaran->uuid }}', '${row.nim}')"><i class="fas fa-check"></i></button>
                                `
                            } else {
                                return `
                                    <button class="btn btn-sm btn-danger" onclick="detailDelete('{{ $pembayaran->uuid }}', '${row.merchant_ref}')"><i class="fas fa-trash-alt"></i></button>
                                `
                            }
                        }
                    }
                ],
                footerCallback: function(row, data, start, end, display) {
                    var api = this.api(),
                        data;

                    // menghitung member yang sudah membayar
                    var dataPaid = api.column(5, {
                        page: 'current'
                    }).data();
                    var result = data.reduce(function(acc, value) {
                        if (value.merchant_ref !== '-') {
                            acc.totalPaid++;
                        } else {
                            acc.totalNotPaid++;
                        }
                        return acc;
                    }, {
                        totalPaid: 0,
                        totalNotPaid: 0
                    })

                    console.log(result.totalPaid);
                    console.log(result.totalNotPaid);

                    // Menghitung total Total Fee
                    totalFee = api.column(6, {
                        page: 'current'
                    }).data().reduce(function(a, b) {
                        return a + parseFloat(b);
                    }, 0);

                    // Menghitung total Subtotal
                    subtotal = api.column(7, {
                        page: 'current'
                    }).data().reduce(function(a, b) {
                        return a + parseFloat(b);
                    }, 0);

                    // Menghitung total Total
                    total = api.column(8, {
                        page: 'current'
                    }).data().reduce(function(a, b) {
                        return a + parseFloat(b);
                    }, 0);

                    // Menambahkan total ke dalam footer
                    $(api.column(5).footer()).html(
                        `<span class="badge badge-success">Sudah Membayar : ${result.totalPaid}</span> <span class="badge badge-danger">Belum Membayar : ${result.totalNotPaid}</span>`
                    );
                    $(api.column(6).footer()).html(accounting.formatMoney(totalFee, 'Rp', 0,
                        '.', ','));
                    $(api.column(7).footer()).html(accounting.formatMoney(subtotal, 'Rp', 0,
                        '.', ','));
                    $(api.column(8).footer()).html(accounting.formatMoney(total, 'Rp', 0,
                        '.', ','));
                },
            });
        }

        function updateStatus(uuid = null, nim = null) {
            Swal.fire({
                title: 'Apakah anda yakin?',
                text: "Anda akan mengubah status pembayaran ini menjadi 'Lunas'!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, ubah!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('pembayaran.edit_status') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            uuid: uuid,
                            nim: nim
                        },
                        success: function(res) {
                            if (res.status == 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: res.message,
                                })
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: res.message,
                                })
                            }
                        },
                        error: function(err) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: err.message,
                            })
                        }
                    })
                }
            })
        }

        function detailDelete(uuid = null, merchant_ref = null) {
            console.log(merchant_ref)
            Swal.fire({
                title: 'Apakah anda yakin?',
                text: "Anda akan menghapus data pembayaran ini!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('pembayaran.detail_delete') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            merchant_ref: merchant_ref
                        },
                        success: function(res) {
                            if (res.status == 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: res.message,
                                })
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: res.message,
                                })
                            }
                        },
                        error: function(err) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: err.message,
                            })
                        }
                    })
                }
            })
        }
    </script>
@endsection
