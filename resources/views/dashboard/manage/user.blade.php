@extends('templates.dashboard.app')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Manage Users</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">Layout</a></li>
                            <li class="breadcrumb-item active">Fixed Layout</li>
                        </ol>
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
                                <h3 class="card-title">Daftar Users</h3>
                            </div>
                            <div class="card-body">
                                <div id="viewTable" class="table-responsive">
                                    <table class="table table-bordered table-hover" id="table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Nama</th>
                                                <th>ID Anggota</th>
                                                <th>Roles</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- /.card -->
                    </div>
                    <div class="col-md-4">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Tambah User</h3>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('manage.users.create') }}" method="post">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nama</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                            placeholder="Masukkan nama">
                                    </div>
                                    <div class="mb-3">
                                        <label for="member_id" class="form-label">ID Anggota</label>
                                        <input type="text" class="form-control" id="member_id" name="member_id"
                                            placeholder="Masukkan ID Anggota">
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="text" class="form-control" id="password" name="password"
                                            placeholder="Masukkan password">
                                    </div>
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Role</label>
                                        <select class="form-control select2" id="role" name="role"
                                            style="width: 100%;">
                                            <option value="" selected disabled>-- Pilih role --</option>
                                            @foreach (\Spatie\Permission\Models\Role::get() as $item)
                                                <option value="{{ $item->name }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-block">Tambah</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /.content -->
    </div>

    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel">Delete User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure want to delete this user?
                </div>
                <form action="{{ route('manage.users.delete') }}" method="post" class="modal-footer">
                    @csrf
                    <input type="hidden" name="uuid" id="uuid">
                    <button type="submit" class="btn btn-danger btn-block">Delete</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            initTable();
            $('select[name="role"]').select2({
                theme: 'bootstrap4'
            })
        });

        function initTable() {
            $('#viewTable').html('')
            $('#viewTable').html(
                `
                <table class="table table-bordered table-hover" id="example1">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>ID Anggota</th>
                            <th>Roles</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
                `
            );

            $('#example1').DataTable({
                processing: true,
                serverSide: true,
                paging: true,
                searching: true,
                ordering: true,
                responsive: true,
                ajax: "{{ route('manage.get_users') }}",
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
                        data: 'roles',
                        name: 'roles',
                    },
                    {
                        data: 'id',
                        render: function(data, type, row) {
                            return `
                                <a href="users/view/${row.uuid}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteUserModal" onclick="sendUuid('${row.uuid}')"><i class="fas fa-trash"></i></button>
                            `
                        }
                    }
                ]
            });
        }

        function sendUuid(uuid) {
            $('#uuid').val(uuid);
        }
    </script>
@endsection
