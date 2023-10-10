@extends('templates.dashboard.app')

@section('content')
    @php
        $role = null;
        $position = null;
    @endphp

    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Manage Users</h1>
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
                    <div class="col-md-8">
                        <!-- Default box -->
                        <div class="card card-outline card-primary">
                            <div class="card-header d-flex justify-content-between">
                                <h3 class="card-title">Daftar Users</h3>
                                <a href="{{ route('manage.users.download_excel') . '?dl=all' }}"
                                    class="btn btn-sm btn-primary d-inline-block ml-auto"><i
                                        class="fas fa-download mr-1"></i> Download</a>
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
                                                <th>Tahun Aktif</th>
                                                <th>Divisi</th>
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
                                    <div class="mb-3">
                                        <label for="position">Divisi</label>
                                        <select name="position" id="position" class="form-control select2">
                                            <option value="" selected disabled>-- Pilih Divisi --</option>
                                            @foreach (\App\Models\Position::get() as $item)
                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="year">Tahun Aktif</label>
                                        <select name="year" id="year" class="form-control select2">
                                            <option value="" selected disabled>-- Pilih Tahun --</option>
                                            @for ($i = 2010; $i <= date('Y'); $i++)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-block">Tambah</button>
                                </form>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <button class="btn btn-sm btn-info btn-block" data-toggle="modal"
                                    data-target="#uploadFileModal"><i class="fas fa-file-upload mr-1"></i>
                                    Upload
                                    File Excel</button>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('manage.users.download_excel') }}"
                                    class="btn btn-sm btn-secondary btn-block"><i class="fas fa-file-download mr-1"></i>
                                    Download File Excel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /.content -->
    </div>

    <div class="modal fade" id="uploadFileModal" tabindex="-1" aria-labelledby="uploadFileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="{{ route('manage.users.upload_excel') }}"
                enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadFileModalLabel">Upload File Excel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-danger text-sm">Pastikan kamu telah mendownload format upload file <a
                            href="{{ route('manage.users.download_excel') }}">disini</a>.</p>
                    <div class="custom-file">
                        <input type="file" name="excel" id="excel" class="custom-file-input"
                            accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                        <label for="excel" class="custom-file-label">Choose File</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save changes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="{{ route('manage.users.update') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User : <span id="nameUser"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nameEdit" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="nameEdit" name="nameEdit"
                            placeholder="Masukkan nama">
                    </div>
                    <div class="mb-3">
                        <label for="member_idEdit" class="form-label">ID Anggota</label>
                        <input type="text" class="form-control" id="member_idEdit" name="member_idEdit"
                            placeholder="Masukkan ID Anggota" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="passwordEdit">Password</label>
                        <input type="text" class="form-control" id="passwordEdit" name="passwordEdit"
                            placeholder="Masukkan password">
                    </div>
                    <div class="mb-3">
                        <label for="roleEdit" class="form-label">Role</label>
                        <select class="form-control select2" id="roleEdit" name="roleEdit" style="width: 100%;">
                            <option value="" disabled>-- Pilih role --</option>
                            @foreach (\Spatie\Permission\Models\Role::get() as $item)
                                <option value="{{ $item->name }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="positionEdit">Divisi</label>
                        <select name="positionEdit" id="positionEdit" class="form-control select2">
                            <option value="" selected disabled>-- Pilih Divisi --</option>
                            @foreach (\App\Models\Position::get() as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="yearEdit">Tahun Aktif</label>
                        <select name="yearEdit" id="yearEdit" class="form-control select2">
                            <option value="" selected disabled>-- Pilih Tahun --</option>
                            @for ($i = 2010; $i <= date('Y'); $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="uuid" id="uuid">
                    <button type="submit" class="btn btn-success btn-block">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel"
        aria-hidden="true">
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
                    <input type="hidden" name="uuid" id="uuid" class="uuidDelete">
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
            $('#excel').on('change', function() {
                var fileName = $(this).val().split('\\').pop()
                $('.custom-file-label').text(fileName)
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
                            <th>Tahun Aktif</th>
                            <th>Divisi</th>
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
                        data: 'year',
                        name: 'year',
                    },
                    {
                        data: 'position',
                        name: 'position',
                    },
                    {
                        data: 'id',
                        render: function(data, type, row) {
                            return `
                                <div class="btn-group" role="group" aria-label="Basic example">
                                    <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editUserModal" onclick="getUserDetail('${row.uuid}')"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteUserModal" onclick="sendUuid('${row.uuid}')"><i class="fas fa-trash"></i></button>
                                </div>
                            `
                        }
                    }
                ]
            });
        }

        function sendUuid(uuid = null) {
            $('.uuidDelete').val(uuid);
        }

        function getUserDetail(uuid = null) {
            $.ajax({
                url: "{{ route('manage.users.detail') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    uuid: uuid
                },
                success: function(res) {
                    $('#nameUser').html(res.name);
                    $('#uuid').val(res.uuid);
                    $('#nameEdit').val(res.name);
                    $('#member_idEdit').val(res.member_id);
                    $('#passwordEdit').val(res.password);
                    $('#roleEdit').val(res.role);
                    $('#roleEdit').trigger('change');
                    $('#positionEdit').val(res.position);
                    $('#positionEdit').trigger('change');
                    $('#yearEdit').val(res.year);
                    $('#yearEdit').trigger('change');
                }
            })
        }
    </script>
@endsection
