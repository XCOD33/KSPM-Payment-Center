@extends('templates.dashboard.app')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Manage Jabatan</h1>
                    </div>
                    <div class="col-sm-6">
                        <!-- Right -->
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">

            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-8">
                        <!-- Default box -->
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Daftar Jabatan</h3>
                            </div>
                            <div class="card-body">
                                <div id="viewTable">
                                    <table class="table bordered table-hover" id="table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Role</th>
                                                <th>Total Users</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                    <div class="col-md-4">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Buat Jabatan Baru</h3>
                            </div>
                            <form class="card-body" action="{{ route('manage.position.create') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="name">Nama Jabatan</label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        placeholder="Nama Jabatan">
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Tambah Jabatan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /.content -->
    </div>

    {{-- modal view --}}
    <div class="modal fade" id="viewModal" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel">Jabatan : <span class="modalViewTitle"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body row">
                    <div class="col-md-8">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title">
                                    Daftar User</span>
                                </h3>
                            </div>
                            <div class="card-body" id="modalViewTable">
                                <input type="hidden" name="idPosition">
                                <table class="table table-bordered table-hover" id="viewPositionTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nama</th>
                                            <th>NIM</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title">
                                    Tambahkan User</span>
                                </h3>
                            </div>
                            <div id="formAddUserPosition" class="card-body">
                                @csrf
                                <div class="mb-3">
                                    <input type="hidden" name="position">
                                    <label for="new_user">Nama Anggota</label>
                                    <div id="positionContainer">
                                        <select name="new_user" id="new_user" class="select2">
                                            @foreach (\App\Models\User::where('position_id', null)->get() as $item)
                                                <option value="{{ $item->uuid }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <button id="submitAddUserPosition" type="button" class="btn btn-primary btn-block"
                                    onclick="addUserPosition()">Tambahkan
                                    User</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    {{-- modal edit --}}
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" action="{{ route('manage.position.update') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Position : <span id="modalEditTitle"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <label for="editName">Nama</label>
                    <input type="text" name="name" id="editName" class="form-control" placeholder="Nama Role">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <input type="hidden" name="id">
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            initTable()
        })

        function initTable() {
            $('#viewTable').html('')
            $('#viewTable').html(
                `
                    <table class="table table-bordered table-hover" id="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Role</th>
                                <th>Total Users</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                `
            )

            $('#table').DataTable({
                processing: true,
                serverSide: true,
                paging: true,
                searching: true,
                ordering: true,
                responsive: true,
                ajax: "{{ route('manage.position.get_position') }}",
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
                        data: 'total_users',
                        className: 'text-center',
                        render: function(data, type, row) {
                            return `
                                <div class="badge badge-success">${row.total_users}</div
                            `
                        }
                    },
                    {
                        data: 'id',
                        className: 'text-center',
                        render: function(data, type, row) {
                            return `
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#viewModal" onclick="viewPosition('${row.id}')"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editModal" onclick="editPosition('${row.id}')"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal" onclick="deletePosition('${row.id}')"><i class="fas fa-trash"></i></button>
                                </div>
                            `
                        }
                    }
                ]
            })
        }

        function viewPosition(id = null) {
            $.ajax({
                url: "{{ route('manage.position.view') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                success: function(res) {
                    $('input[name="idPosition"]').val(res.position.id)
                    $('.modalViewTitle').html(res.position.name)
                    $('input[name="position"]').val(res.position.uuid)

                    $('#viewModalTable').html('')
                    $('#viewModalTable').html(
                        `
                    <table class="table table-bordered table-hover" id="viewPositionTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>NIM</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                `
                    )

                    $('#viewPositionTable').DataTable().destroy()
                    $('#viewPositionTable').DataTable({
                        processing: true,
                        serverSide: true,
                        paging: true,
                        searching: true,
                        ordering: true,
                        responsive: true,
                        ajax: {
                            url: "{{ route('manage.position.view_position') }}",
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                id: id
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
                                data: 'id',
                                render: function(data, type, row) {
                                    return `
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-danger" onclick="deleteUserPosition('${row.uuid}')"><i class="fas fa-trash"></i></button>
                                </div>
                            `
                                }
                            }
                        ]
                    })
                }
            })
        }

        function addUserPosition() {
            $('#submitAddUserRole').html('Loading...')
            $('#submitAddUserRole').attr('disabled', true)
            $.ajax({
                url: "{{ route('manage.position.add_user') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    position: $('input[name="position"]').val(),
                    new_user: $('#new_user').val()
                },
                success: function(res) {
                    $('#submitAddUserPosition').html('Tambahkan User')
                    $('#submitAddUserPosition').attr('disabled', false)
                    $('#new_user').val('')
                    $('#new_user').trigger('change')
                    $('option[value="' + res.user.uuid + '"]').remove()
                    viewPosition($('input[name="idPosition"]').val())
                },
                error: function(err) {
                    $('#submitAddUserPosition').html('Tambahkan User')
                    $('#submitAddUserPosition').attr('disabled', false)
                    $('#new_user').val('')
                    $('#new_user').trigger('change')
                    Swal.fire({
                        title: 'Gagal!',
                        text: err.responseJSON.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    })
                }
            })
        }

        function editPosition(id = null) {
            $.ajax({
                url: "{{ route('manage.position.edit') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                success: function(res) {
                    $('#modalEditTitle').html(res.position.name)
                    $('#editName').val(res.position.name)
                    $('input[name="id"]').val(res.position.id)
                }
            })
        }

        function deletePosition(id = null) {
            Swal.fire({
                title: "Apakah anda yakin?",
                text: "Jabatan yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yakin',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('manage.position.delete') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: id
                        },
                        success: function(res) {
                            initTable()
                        }
                    })
                }
            })
        }

        function deleteUserPosition(id = null) {
            Swal.fire({
                title: 'Apakah anda yakin?',
                text: 'User yang dihapus dari jabatan tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yakin',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('manage.position.remove_user') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: id
                        },
                        success: function(res) {
                            viewPosition($('input[name="idPosition"]').val())
                            var select = $('#new_user')
                            select.empty()
                            select.attr('name', 'new_user')
                            select.attr('id', 'new_user')
                            select.addClass('select2')
                            select.select2({
                                theme: 'bootstrap4'
                            })
                            $.each(res.user, function(index, user) {
                                select.append('<option value="' + user.uuid + '">' + user.name +
                                    '</option>');
                            });
                        }
                    })
                }
            })
        }
    </script>
@endsection
