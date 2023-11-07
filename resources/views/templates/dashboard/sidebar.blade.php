<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="../../index3.html" class="brand-link">
        <img src="../../dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light">AdminLTE 3</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="../../dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block" data-toggle="modal"
                    data-target="#modalLogout">{{ auth()->user()->name }}</a>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search"
                    aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
       with font-awesome or any other icon font library -->
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}"
                        class="nav-link {{ Request::routeIs('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Dashboard
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link {{ Request::routeIs('pembayaran.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-money-bill"></i>
                        <p>
                            Pembayaran
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @role('super-admin')
                            <li class="nav-item {{ Request::routeIs('pembayaran.*') ? 'menu-open' : '' }}">
                                <a href="{{ route('pembayaran.index') }}"
                                    class="nav-link {{ Request::routeIs('pembayaran.index') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Daftar Pembayaran</p>
                                </a>
                            </li>
                        @endrole
                        <li class="nav-item {{ Request::routeIs('pembayaranku.*') ? 'menu-open' : '' }}">
                            <a href="{{ route('pembayaranku.index') }}"
                                class="nav-link {{ Request::routeIs('pembayaranku.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Pembayaranku</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @if (auth()->user()->getRoleNames()->first() == 'super-admin')
                    <li class="nav-item {{ Request::routeIs('manage.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ Request::routeIs('manage.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cog"></i>
                            <p>
                                Manage
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('manage.users.index') }}"
                                    class="nav-link {{ Request::routeIs('manage.users.index') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Users</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('manage.roles.index') }}"
                                    class="nav-link {{ Request::routeIs('manage.roles.index') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Roles</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('manage.permission.index') }}"
                                    class="nav-link {{ Request::routeIs('manage.permission.index') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Permission</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="../charts/uplot.html" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>uPlot</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>

<div class="modal fade" id="modalLogout" tabindex="-1" aria-labelledby="modalLogoutLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLogoutLabel">Detail Akun</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <td>Name</td>
                            <td>{{ auth()->user()->name }}</td>
                            </td>
                        </tr>
                        <tr>
                            <td>ID Anggota</td>
                            <td>{{ auth()->user()->member_id }}</td>
                            </td>
                        </tr>
                        <tr>
                            <td>Role</td>
                            <td>{{ auth()->user()->getRoleNames()->first() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <form action="{{ route('logout') }}" class="modal-footer" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary btn-block">Logout</button>
            </form>
        </div>
    </div>
</div>
