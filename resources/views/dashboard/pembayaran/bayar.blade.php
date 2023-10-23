@extends('templates.dashboard.app')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Tagihan Pembayaran</h1>
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
                                <h3 class="card-title">Tagihan Pembayaran : {{ $pembayaran->name }}</h3>
                                {{-- <button class="btn btn-sm btn-success ml-auto" id="btnAddModal" data-toggle="modal"
                                    data-target="#addModal">
                                    <i class="fas fa-plus"></i> Tambah Pembayaran
                                </button> --}}
                            </div>
                            <div class="card-body row">
                                <img src="{{ asset('dist/img/kspm-logo.jpeg') }}" alt="Logo KSPM"
                                    class="img-fluid mx-auto d-block mb-5 col-xl-1" style="width: 100px;">
                                <div class="d-flex justify-content-between col-xl-12 row-cols-2 mb-3     border-bottom">
                                    <div class="col mb-3">
                                        <h4 class="h5">Tagihan Pembayaran : <b>{{ $pembayaran->name }}</b>
                                        </h4>
                                        <h4 class="h5">Jumlah Tagihan :
                                            <b>Rp {{ number_format(intval($pembayaran->nominal), 0, ',', '.') }}</b>
                                        </h4>
                                    </div>
                                    <div class="col">
                                        <p class="h5 text-right">Status : <span
                                                class="badge {{ $pembayaranUser == null ? 'badge-danger' : 'badge-success' }}">{{ $pembayaranUser == null ? 'Belum Dibayar' : 'Lunas' }}</span>
                                        </p>
                                        <p class="h5 text-right">Terakhir Pembayaran :
                                            {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $pembayaran->expired_at)->format('d-M-Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="p-3 mb-5 col-xl-12">
                                    {!! $pembayaran->description !!}
                                </div>
                                <div class="col-xl-3 mx-auto">
                                    <button class="btn btn-success btn-block" onclick="bayar('{{ $pembayaran->uuid }}')"><i
                                            class="fas fa-money-bill-alt mr-1"></i> Bayar</button>
                                </div>
                                {{-- <div class="col-xl-12 text-right">
                                    <a href="{{ route('pembayaran.index') }}" class="btn btn-sm btn-secondary"><i
                                            class="fas fa-chevron-left mr-1"></i>
                                        Kembali</a>
                                </div> --}}
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
@endsection

@section('js')
    <script>
        function bayar(uuid = null) {
            $.ajax({
                url: "{{ route('pembayaran.bayar_post') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    uuid: uuid
                },
                success: function(response) {
                    if (response.status == 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            window.location.href = "{{ route('pembayaran.index') }}";
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: xhr.responseJSON.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            })
        }
    </script>
@endsection
