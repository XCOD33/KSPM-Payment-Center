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
                            <div class="card-header row d-flex justify-content-between">
                                <h3 class="card-title">Daftar Pembayaran</h3>
                                <button class="btn btn-sm btn-success ml-auto" id="btnAddModal" data-toggle="modal"
                                    data-target="#addModal">
                                    <i class="fas fa-plus"></i> Tambah Pembayaran
                                </button>
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

    <div class="modal fade" id="detailModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Detail Pembayaran</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive" id="detailModalBody">

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- modal add --}}
    <div class="modal fade" id="addModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Tambah Pembayaran</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addName">Nama Pembayaran</label>
                        <input type="text" name="addName" id="addName" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="addNominal">Nominal</label>
                        <input type="number" name="addNominal" id="addNominal" class="form-control">
                        <p class="text-sm text-danger to_idr"></p>
                    </div>
                    <div class="mb-3">
                        <label for="addExpiredAt">Tanggal Terakhir Pembayaran</label>
                        <div class="input-group date" id="divAddExpiredAt" data-target-input="nearest">
                            <input type="text" name="addExpiredAt" id="addExpiredAt"
                                class="form-control datetimepicker-input" data-target="#divAddExpiredAt">
                            <div class="input-group-append" data-target="#divAddExpiredAt" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="addCreatedBy">Dibuat Oleh</label>
                        <select name="addCreatedBy" id="addCreatedBy" class="form-control select2">
                            <option value="">-- Pilih --</option>
                            @foreach (\App\Models\User::with('position')->get() as $user)
                                <option value="{{ $user->uuid }}"
                                    {{ $user->uuid == auth()->user()->uuid ? 'selected' : '' }}>{{ $user->name }} -
                                    {{ $user->position->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 row d-flex justify-content-between align-items-end">
                        <div class="col-9">
                            <label for="addForPosition">Pembayaran untuk Divisi</label>
                            <select name="addForPosition" multiple="multiple" id="addForPosition"
                                class="form-control select2" data-placeholder="Pilih divisi">
                                @foreach (\App\Models\Position::all() as $position)
                                    <option value="{{ $position->uuid }}">{{ $position->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="addSelectAll">
                                <label class="form-check-label" for="addSelectAll">Pilih Semua Divisi</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="addStatus" class="d-block">Status</label>
                        <input type="checkbox" name="addStatus" id="addStatus" data-bootstrap-switch>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label for="addDescription">Keterangan</label>
                        <textarea name="addDescription" id="addDescription" class="text-editor">

                        </textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button class="btn btn-primary" onclick="store()">Save</button>
                </div>
            </div>
        </div>
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
                        <input type="number" name="editNominal" id="editNominal" class="form-control">
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
                    <div class="mb-3 row d-flex justify-content-between align-items-end">
                        <div class="col-9">
                            <label for="editForPosition">Pembayaran untuk Divisi</label>
                            <select name="editForPosition" multiple="multiple" id="editForPosition"
                                class="form-control select2" data-placeholder="Pilih divisi">
                                @foreach (\App\Models\Position::all() as $position)
                                    <option value="{{ $position->uuid }}">{{ $position->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="editSelectAll">
                                <label class="form-check-label" for="editSelectAll">Pilih Semua Divisi</label>
                            </div>
                        </div>
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
            $('.date').datetimepicker({
                icons: {
                    time: 'far fa-clock'
                },
                locale: 'id',
                format: 'DD-MM-YYYY HH:mm',
            })
            $('input[name=editNominal]').on('keyup', function() {
                num_to_idr($(this).val())
            })
            $('#btnAddModal').on('click', function() {
                $('#addDescription').summernote('code', '')
                $('#addExpiredAt').val("{{ now()->addWeeks(1)->format('d-m-Y H:i') }}")
                num_to_idr($('#addNominal').val())

                $('input[name=addNominal]').on('keyup', function() {
                    num_to_idr($(this).val())
                })
            })
            $('#addSelectAll').on('click', function() {
                if ($(this).is(':checked')) {
                    $('#addForPosition').val($('#addForPosition option').map(function() {
                        return $(this).val()
                    }).get()).trigger('change')
                } else {
                    $('#addForPosition').val('').trigger('change')
                }
            })

            $('#addModal').on('hidden.bs.modal', function() {
                $('.to_idr').html('')
            })
            $('#editModal').on('hidden.bs.modal', function() {
                $('.to_idr').html('')
            })

            $('#editModal').on('show.bs.modal', function() {
                $('#editSelectAll').on('click', function() {
                    if ($(this).is(':checked')) {
                        $('#editForPosition').val($('#editForPosition option').map(function() {
                            return $(this).val()
                        }).get()).trigger('change')
                    } else {
                        $('#editForPosition').val('').trigger('change')
                    }
                })
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
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#detailModal" onclick="showDetail('${row.uuid}')"><i class="fas fa-eye"></i></button>
                                    <a href="pembayaran/${row.url}" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-link"></i></a>
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

        function store() {
            $.ajax({
                url: "{{ route('pembayaran.store') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    name: $('#addName').val(),
                    nominal: $('#addNominal').val(),
                    expired_at: $('#addExpiredAt').val(),
                    description: $('#addDescription').val(),
                    created_by: $('#addCreatedBy').val(),
                    positions: $('#addForPosition').val(),
                    status: $('#addStatus').bootstrapSwitch('state') == true ? 'active' : 'inactive'
                },
                success: function(res) {
                    if (res.status == 'success') {
                        $('#addModal').modal('hide')
                        initTable()
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                        })
                        $('#addName').val('')
                        $('#addNominal').val('')
                        $('#addExpiredAt').val('')
                        $('#addDescription').summernote('code', '')
                        $('#addCreatedBy').val('')
                        $('#addStatus').bootstrapSwitch('state', false)
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

        function showDetail(uuid = null) {
            $.ajax({
                url: "{{ route('pembayaran.detail') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    uuid: uuid
                },
                success: function(res) {
                    $('#detailModalLabel').html(`Detail Pembayaran - ${res.data.name}`)
                    $('#detailModalBody').html(`
                        <table class="table table-bordered table-hover data-table" id="detail_table">
                            <thead>
                                <tr class="text-center">
                                    <th>#</th>
                                    <th>Nama User</th>
                                    <th>NIM</th>
                                    <th>Posisi</th>
                                    <th>Tanggal Pembayaran</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    `)

                    $('#detail_table').DataTable({
                        processing: true,
                        serverSide: true,
                        paging: true,
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
                                data: 'created_at',
                                name: 'created_at'
                            },
                            {
                                data: 'status',
                                name: 'status'
                            },
                            {
                                data: 'id',
                                render: function(data, type, row) {
                                    return `
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#detailModal" onclick="showDetail('${row.uuid}')"><i class="fas fa-eye"></i></button>
                                    <a href="pembayaran/${row.url}" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-link"></i></a>
                                    <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editModal" onclick="showEdit('${row.uuid}')"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger" onclick="showDelete('${row.uuid}')"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            `
                                }
                            },
                        ]
                    })
                },
            })
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
                    $('#editForPosition').val(res.data.positions).change()
                    if (res.data.positions.length == $('#editForPosition option').length) {
                        $('#editSelectAll').prop('checked', true)
                    } else {
                        $('#editSelectAll').prop('checked', false)
                    }
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
                    positions: $('#editForPosition').val(),
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
                error: function(err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: err.message,
                    })
                }
            })
        }

        function showDelete(uuid = null) {
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
                        url: "{{ route('pembayaran.delete') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            uuid: uuid
                        },
                        success: function(res) {
                            if (res.status == 'success') {
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
