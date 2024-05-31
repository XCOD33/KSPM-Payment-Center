@extends('templates.dashboard.app')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        {{-- <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Dashboard</h1>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section> --}}

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                @if (auth()->user()->hasRole('super-admin'))
                    <div class="row">
                        <div class="col-12">
                            <h4 class="mt-3">Data Pengguna</h4>
                            <div class="row">
                                <div class="col-md-4 col-sm-6 col-12">
                                    <a href="{{ route('manage.users.index') }}"
                                        class="display-block text-decoration-none text-dark">
                                        <div class="info-box">
                                            <div class="info-box-icon bg-info">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="info-box-content">
                                                <span class="info-box-text text-black">Jumlah User</span>
                                                <span class="info-box-number text-black">{{ $total_users }}</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-4 col-sm-6 col-12">
                                    <a href="{{ route('manage.roles.index') }}"
                                        class="display-block text-decoration-none text-dark">
                                        <div class="info-box">
                                            <div class="info-box-icon bg-info">
                                                <i class="fas fa-user-tag"></i>
                                            </div>
                                            <div class="info-box-content">
                                                <span class="info-box-text text-black">Jumlah Role</span>
                                                <span class="info-box-number text-black">{{ $total_roles }}</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-4 col-sm-12 col-12">
                                    <a href="{{ route('manage.position.index') }}"
                                        class="display-block text-decoration-none text-dark">
                                        <div class="info-box">
                                            <div class="info-box-icon bg-info">
                                                <i class="fas fa-crown"></i>
                                            </div>
                                            <div class="info-box-content">
                                                <span class="info-box-text text-black">Jumlah Position</span>
                                                <span class="info-box-number text-black">{{ $total_positions }}</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <h4 class="mt-3">Data Pembayaran</h4>
                            <div class="row">
                                <div class="col-md-4 col-sm-6 col-12">
                                    <a href="{{ route('pembayaran.index') }}"
                                        class="display-bloc text-decoration-none text-dark">
                                        <div class="info-box">
                                            <div class="info-box-icon bg-warning">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="info-box-content">
                                                <span class="info-box-text text-black">Jumlah Pembayaran</span>
                                                <span class="info-box-number text-black">{{ $total_pembayarans }}</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-4 col-sm-6 col-12">
                                    <div class="info-box">
                                        <div class="info-box-icon bg-warning">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="info-box-content">
                                            <span class="info-box-text text-black">User Belum Membayar</span>
                                            <span class="info-box-number text-black">{{ $total_users_belum_bayar }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6 col-12">
                                    <div class="info-box">
                                        <div class="info-box-icon bg-warning">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="info-box-content">
                                            <span class="info-box-text text-black">User Sudah Membayar</span>
                                            <span class="info-box-number text-black">{{ $total_users_bayar }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="info-box">
                                <div class="info-box-icon bg-warning">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="info-box-content">
                                    <span class="info-box-text text-black">Total Uang Belum Diterima</span>
                                    <span class="info-box-number text-black">Rp
                                        {{ number_format($total_uang_belum_terkumpul, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="info-box">
                                <div class="info-box-icon bg-warning">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="info-box-content">
                                    <span class="info-box-text text-black">Total Uang Diterima</span>
                                    <span class="info-box-number text-black">Rp
                                        {{ number_format($total_uang_terkumpul, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="row">
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-receipt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Tagihanku</span>
                                    <span class="info-box-number">{{ $active_bill }}</span>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-money-bill-wave-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Tagihanku</span>
                                    <span class="info-box-number">Rp
                                        {{ number_format($total_active_bill, 0, ',', '.') }}</span>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-receipt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Tagihan Dibayar</span>
                                    <span class="info-box-number">{{ $paid_bill }}</span>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-3 col-sm-6 col-12">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-money-bill-wave-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Tagihan Dibayar</span>
                                    <span class="info-box-number">Rp
                                        {{ number_format($total_paid_bill, 0, ',', '.') }}</span>
                                </div>

                            </div>

                        </div>

                    </div>
                @endif
            </div>
        </section>
        <!-- /.content -->
    </div>
@endsection
