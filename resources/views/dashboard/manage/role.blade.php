@extends('templates.dashboard.app')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Manage Roles</h1>
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
                    <div class="col-8">
                        <!-- Default box -->
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Daftar Roles</h3>
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
                    <div class="col-4">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Buat Role Baru</h3>
                            </div>
                            <form class="card-body" action="{{ route('manage.roles.create') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="name">Nama Role</label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        placeholder="Nama Role">
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Tambah Role</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /.content -->
    </div>

    {{-- modal view --}}
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel">Role : <span class="modalViewTitle"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body row">
                    <div class="col-8">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title">
                                    Daftar User</span>
                                </h3>
                            </div>
                            <div class="card-body" id="modalViewTable">
                                <input type="hidden" name="idRole">
                                <table class="table table-bordered table-hover" id="viewRoleTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nama</th>
                                            <th>ID Anggota</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title">
                                    Tambahkan User</span>
                                </h3>
                            </div>
                            <div id="formAddUserRole" class="card-body">
                                @csrf
                                <div class="mb-3">
                                    <input type="hidden" name="role">
                                    <label for="new_user">Nama Anggota</label>
                                    <div id="roleContainer">
                                        <select name="new_user" id="new_user" class="select2">
                                            @foreach (\App\Models\User::doesntHave('roles')->get() as $item)
                                                <option value="{{ $item->uuid }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <button id="submitAddUserRole" type="button" class="btn btn-primary btn-block"
                                    onclick="addUserRole()">Tambahkan
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
            <form class="modal-content" action="{{ route('manage.roles.update') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Role : <span id="modalEditTitle"></span></h5>
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
                ajax: "{{ route('manage.roles.get_roles') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'total_users',
                        render: function(data, type, row) {
                            return `
                                <div class="badge badge-success">${row.total_users}</div
                            `
                        }
                    },
                    {
                        data: 'id',
                        render: function(data, type, row) {
                            return `
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#viewModal" onclick="viewRole('${row.id}')"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editModal" onclick="editRole('${row.id}')"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal" onclick="deleteRole('${row.id}')"><i class="fas fa-trash"></i></button>
                                </div>
                            `
                        }
                    }
                ]
            })
        }

        function viewRole(id = null) {
            $.ajax({
                url: "{{ route('manage.roles.view') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                success: function(res) {
                    $('input[name="idRole"]').val(res.role.id)
                    $('.modalViewTitle').html(res.role.name)
                    $('input[name="role"]').val(res.role.name)

                    $('#viewModalTable').html('')
                    $('#viewModalTable').html(
                        `
                    <table class="table table-bordered table-hover" id="viewRoleTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>ID Anggota</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                `
                    )

                    $('#viewRoleTable').DataTable().destroy()
                    $('#viewRoleTable').DataTable({
                        processing: true,
                        serverSide: true,
                        paging: true,
                        searching: true,
                        ordering: true,
                        responsive: true,
                        ajax: {
                            url: "{{ route('manage.roles.view_roles') }}",
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
                                data: 'member_id',
                                name: 'member_id'
                            },
                            {
                                data: 'id',
                                render: function(data, type, row) {
                                    return `
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-danger" onclick="deleteUserRole('${row.uuid}')"><i class="fas fa-trash"></i></button>
                                </div>
                            `
                                }
                            }
                        ]
                    })
                }
            })
        }

        function addUserRole() {
            $('#submitAddUserRole').html('Loading...')
            $('#submitAddUserRole').attr('disabled', true)
            $.ajax({
                url: "{{ route('manage.roles.add_user') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    role: $('input[name="role"]').val(),
                    new_user: $('#new_user').val()
                },
                success: function(res) {
                    $('#submitAddUserRole').html('Tambahkan User')
                    $('#submitAddUserRole').attr('disabled', false)
                    $('#new_user').val('')
                    $('#new_user').trigger('change')
                    $('option[value="' + res.user.uuid + '"]').remove()
                    viewRole($('input[name="idRole"]').val())
                }
            })
        }

        function editRole(id = null) {
            $.ajax({
                url: "{{ route('manage.roles.edit') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                success: function(res) {
                    $('#modalEditTitle').html(res.role.name)
                    $('#editName').val(res.role.name)
                    $('input[name="id"]').val(res.role.id)
                }
            })
        }

        function deleteRole(id = null) {
            Swal.fire({
                title: "Apakah anda yakin?",
                text: "Role yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yakin',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('manage.roles.delete') }}",
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

        function deleteUserRole(id = null) {
            Swal.fire({
                title: 'Apakah anda yakin?',
                text: 'User yang dihapus dari role tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yakin',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('manage.roles.remove_user') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: id
                        },
                        success: function(res) {
                            console.log('berhasil hapus')
                            viewRole($('input[name="idRole"]').val())
                            $('#roleContainer').html('')
                            $('#roleContainer').html(`
                                <select name="new_user" id="new_user" class="form-control">
                                    @foreach (\App\Models\User::doesntHave('roles')->get() as $item)
                                        <option value="{{ $item->uuid }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            `)
                            $('select[name="new_user"]').select2()
                        }
                    })
                }
            })
        }
    </script>
@endsection
