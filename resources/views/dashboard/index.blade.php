@extends('templates.dashboard.app')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Dashboard</h1>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
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
                                <span class="info-box-number">Rp {{ number_format($total_active_bill, 0, ',', '.') }}</span>
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
                                <span class="info-box-number">Rp {{ number_format($total_paid_bill, 0, ',', '.') }}</span>
                            </div>

                        </div>

                    </div>

                </div>
            </div>
        </section>
        <!-- /.content -->
    </div>
@endsection
