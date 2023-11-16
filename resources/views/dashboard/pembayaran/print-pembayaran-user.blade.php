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

    <div id="container-table" class="container my-3">

    </div>

    @include('templates.dashboard.js')

    <script>
        $(document).ready(function() {
            showDetail('{{ $pembayaran->uuid }}');
        });

        function goBack() {
            window.history.back();
        }

        function showDetail(uuid = null) {
            $.ajax({
                url: "{{ route('pembayaran.detail') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    uuid: uuid
                },
                success: function(res) {
                    $('#container-table').html(`
                        <table class="table table-bordered table-hover data-table text-sm" id="detail_table">
                            <thead>
                                <tr class="text-center">
                                    <th>#</th>
                                    <th>Nama User</th>
                                    <th>NIM</th>
                                    <th>Jabatan</th>
                                    <th>Roles</th>
                                    <th>Metode Pembayaran</th>
                                    <th>Total Fee</th>
                                    <th>Subtotal</th>
                                    <th>Total</th>
                                    <th>Tanggal Pembayaran</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="align-middle">Total:</th>
                                    <th class="align-middle"></th>
                                    <th class="align-middle"></th>
                                    <th class="align-middle"></th>
                                    <th class="align-middle"></th>
                                    <th colspan="2" class="align-middle"></th>
                                </tr>
                            </tfoot>
                        </table>
                    `)

                    var table = $('#detail_table').DataTable({
                        processing: true,
                        serverSide: true,
                        paging: false,
                        searching: true,
                        ordering: true,
                        responsive: true,
                        autoWidth: true,
                        ajax: {
                            url: "{{ route('pembayaran.get_pembayaran_user') }}",
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                uuid: uuid
                            }
                        },
                        columns: [{
                                data: 'DT_RowIndex',
                                name: 'DT_RowIndex',
                                className: 'text-center'
                            },
                            {
                                data: 'name',
                                name: 'name'
                            },
                            {
                                data: 'nim',
                                name: 'nim',
                                className: 'text-center'
                            },
                            {
                                data: 'position',
                                name: 'position',
                                className: 'text-center'
                            },
                            {
                                data: 'roles',
                                name: 'roles',
                                className: 'text-center'
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
                                render: function(data, type, row) {
                                    return `
                                        ${accounting.formatMoney(row.total_fee, 'Rp', 0, '.', ',')}
                                    `
                                }
                            },
                            {
                                data: 'subtotal',
                                name: 'subtotal',
                                className: 'text-center',
                                render: function(data, type, row) {
                                    return `
                                        ${accounting.formatMoney(row.subtotal, 'Rp', 0, '.', ',')}
                                    `
                                }
                            },
                            {
                                data: 'total',
                                name: 'total',
                                className: 'text-center',
                                render: function(data, type, row) {
                                    return `
                                        ${accounting.formatMoney(row.total, 'Rp', 0, '.', ',')}
                                    `
                                }
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
                    table.on('draw.dt', function() {
                        window.print();
                    });
                },
            })

        }


        // function print() {
        //     var css = '@page { size: landscape; }',
        //         head = document.head || document.getElementsByTagName('head')[0],
        //         style = document.createElement('style');

        //     style.type = 'text/css';
        //     style.media = 'print';

        //     if (style.styleSheet) {
        //         style.styleSheet.cssText = css;
        //     } else {
        //         style.appendChild(document.createTextNode(css));
        //     }

        //     head.appendChild(style);

        //     window.print();
        // }
    </script>
</body>

</html>
