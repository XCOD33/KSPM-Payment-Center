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
                                                class="badge {{ $pembayaranUser && $pembayaranUser->status == 'PAID' ? 'badge-success' : 'badge-secondary' }}">{{ $pembayaranUser->status ?? 'UNPAID' }}</span>
                                        </p>
                                        <p class="h5 text-right">Terakhir Pembayaran :
                                            {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $pembayaran->expired_at)->format('d-M-Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="p-3 mb-5 col-xl-12">
                                    {!! $pembayaran->description !!}
                                </div>
                                @if ($pembayaranUser && $pembayaranUser->status == 'PAID')
                                    <div class="col-xl-6 mx-auto">
                                        <div class="alert alert-success" role="alert">
                                            <h4 class="alert-heading">Pembayaran Berhasil!</h4>
                                            <p>Terima kasih telah melakukan pembayaran. Pembayaran anda telah kami
                                                terima.</p>
                                            <hr>
                                            <p class="mb-0">Silahkan cek email anda untuk mendapatkan bukti
                                                pembayaran.</p>
                                        </div>
                                    </div>
                                @else
                                    <div class="col-xl-3 mx-auto">
                                        <div class="mb-3">
                                            <select name="channel" id="channel" class="form-control select2">
                                                <option selected disabled>Pilih Channel Pembayaran</option>
                                            </select>
                                        </div>
                                        <button id="btnBayar" class="btn btn-success btn-block" disabled><i
                                                class="fas fa-money-bill-alt mr-1"></i> Bayar</button>
                                    </div>
                                @endif
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
        $(document).ready(function() {
            getChannelPembayarans();
            $('#btnBayar').click(function() {
                bayar("{{ $pembayaran->uuid }}");
                e.preventDefault();
            });
        })

        function getChannelPembayarans() {
            $.ajax({
                url: "{{ route('pembayaran.channel') }}",
                type: "GET",
                success: function(response) {
                    if (response.status == 'success') {
                        console.log(response.data.data)
                        let channel = response.data.data;
                        let html = '';
                        html += `<option selected disabled>Pilih Channel Pembayaran</option>`;
                        channel.forEach(function(item) {
                            html += `<option value="${item.code}">${item.name}</option>`;
                        })
                        $('#channel').html(html);
                        $('#channel').change(function() {
                            if ($(this).val() != '') {
                                $('#btnBayar').removeAttr('disabled');
                            } else {
                                $('#btnBayar').attr('disabled', 'disabled');
                            }
                        })
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

        function bayar(uuid = null) {
            $.ajax({
                url: "{{ route('pembayaran.bayar_post') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    uuid: uuid,
                    channel: $('#channel').val(),
                    phoneNumber: $('#phoneNumber').val()
                },
                success: function(response) {
                    console.log(response.data.data)
                    if (response.status == 'success') {
                        Swal.fire({
                            icon: 'info',
                            title: 'Melanjutkan Pembayaran',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            window.location.href = response.data.data.checkout_url;
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
