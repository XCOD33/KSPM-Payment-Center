@extends('templates.dashboard.app')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Pembayaran</h1>
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
                            <div class="card-header">
                                <h3 class="card-title">Daftar Pembayaran</h3>
                            </div>
                            <div class="card-body">
                                <div id="container">
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
                                                <th>Aksi</th>
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

    {{-- modal edit --}}
    <div class="modal fade" id="editModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="hidden" name="editUuid">
                        <label for="editName">Nama Pembayaran</label>
                        <input type="text" name="editName" id="editName" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="editNominal">Nominal</label>
                        <input type="text" name="editNominal" id="editNominal" class="form-control">
                        <p class="text-sm text-danger to_idr"></p>
                    </div>
                    <div class="mb-3">
                        <label for="editExpiredAt">Tanggal Terakhir Pembayaran</label>
                        <div class="input-group date" id="divEditExpiredAt" data-target-input="nearest">
                            <input type="text" name="editExpiredAt" id="editExpiredAt"
                                class="form-control datetimepicker-input" data-target="#divEditExpiredAt">
                            <div class="input-group-append" data-target="#divEditExpiredAt" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editCreatedBy">Dibuat Oleh</label>
                        <input type="text" name="editCreatedBy" id="editCreatedBy" class="form-control" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="editStatus" class="d-block">Status</label>
                        <input type="checkbox" name="editStatus" id="editStatus" data-bootstrap-switch>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label for="editDescription">Keterangan</label>
                        <textarea name="editDescription" id="editDescription" class="text-editor">

                        </textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="updateData()">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            initTable();
            $('#divEditExpiredAt').datetimepicker({
                icons: {
                    time: 'far fa-clock'
                },
                locale: 'id',
                format: 'DD-MM-YYYY HH:mm',
            })
            $('input[name=editNominal]').on('keyup', function() {
                num_to_idr($(this).val())
            })
        })

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
                ajax: "{{ route('pembayaran.get_pembayaran') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'nominal',
                        name: 'nominal'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'expired_at',
                        name: 'expired_at'
                    },
                    {
                        data: 'created_by',
                        name: 'created_by'
                    },
                    {
                        data: 'status',
                        render: function(data, type, row) {
                            return `
                                <span class="badge badge-${row.status == 'Aktif' ? 'success' : 'danger'}">${row.status}</span>
                            `
                        }
                    },
                    {
                        data: 'id',
                        render: function(data, type, row) {
                            return `
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#showDetailModal" onclick="showDetail('${row.uuid}')"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editModal" onclick="showEdit('${row.uuid}')"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger" onclick="showDelete('${row.uuid}')"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            `
                        }
                    },
                ]
            });
        }

        function num_to_idr(num = null) {
            let toIdr = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(num)
            $('.to_idr').html(`${toIdr}`)
        }

        function showDetail() {
            console.log('show detail')
        }

        function showEdit(uuid = null) {
            $.ajax({
                url: "{{ route('pembayaran.edit') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    uuid: uuid
                },
                success: function(res) {
                    $('#editModalLabel').html(`Edit Pembayaran - ${res.data.name}`)
                    $('input[name=editUuid]').val(res.data.uuid)
                    $('#editName').val(res.data.name)
                    $('#editNominal').val(res.data.nominal)
                    $('#editExpiredAt').val(res.data.expired_at)
                    $('#editCreatedBy').val(res.data.created_by_name)
                    $('#editDescription').summernote('code', '')
                    $('#editDescription').summernote('editor.pasteHTML', res.data.description)
                    $('#editStatus').bootstrapSwitch('state', res.data.status == 'active' ? true : false)
                    $('.to_idr').html(num_to_idr(res.data.nominal))
                },
            })
        }

        function updateData() {
            $.ajax({
                url: "{{ route('pembayaran.update') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    uuid: $('input[name=editUuid]').val(),
                    name: $('#editName').val(),
                    nominal: $('#editNominal').val(),
                    expired_at: $('#editExpiredAt').val(),
                    description: $('#editDescription').val(),
                    status: $('#editStatus').bootstrapSwitch('state') == true ? 'active' : 'inactive'
                },
                success: function(res) {
                    if (res.status == 'success') {
                        $('#editModal').modal('hide')
                        initTable()
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
            })
        }
    </script>
@endsection
