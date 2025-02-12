<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KSPM UTY</title>

    @include('templates.dashboard.css')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    @if (session()->has('error'))
        @php
            alert()->error('Error', session()->get('error'));
        @endphp
    @endif

    @if (session()->has('success'))
        @php
            alert()->success('Success', session()->get('success'));
        @endphp
    @endif

    @if (session()->has('warning'))
        @php
            alert()
                ->warning('Warning', session()->get('warning'))
                ->persistent(true, false)
                ->toToast();
        @endphp
    @endif

    @if ($errors->any())
        @php
            alert()->html('Error', implode('<br>', $errors->all()), 'error');
        @endphp
    @endif

    <!-- Site wrapper -->
    <div class="wrapper">
        <!-- Navbar -->
        @include('templates.dashboard.navbar')
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        @include('templates.dashboard.sidebar')

        <!-- Content Wrapper. Contains page content -->
        @yield('content')
        <!-- /.content-wrapper -->

        @include('templates.dashboard.footer')
    </div>
    <!-- ./wrapper -->

    @include('templates.dashboard.js')


    <script>
        $('.select2').select2({
            theme: 'bootstrap4'
        })
        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        })
        $('.text-editor').summernote({
            'height': 300,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['fontsize', ['fontsize']],
                ['para', ['ul', 'ol', 'paragraph']]
            ]
        })

        function copyToClipboard(elementId) {
            console.log('copy')
            var textToCopy = document.getElementById(elementId).textContent;
            var temp = document.createElement("textarea");
            temp.value = textToCopy;
            document.body.appendChild(temp);
            temp.select();
            document.execCommand("copy");
            document.body.removeChild(temp);

            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Link sudah disalin ke clipboard',
            })
        }

        function changePassword() {
            $('#modalLogout').modal('hide')
            $('#modalChangePassword').modal('show')
        }

        // change password
        if ($('#modalChangePassword').length) {
            $('#modalChangePassword').on('hidden.bs.modal', function(e) {
                $('#modalLogout').modal('show')
            })
        }

        $(document).ready(function() {
            var password_changed = '{{ $password_changed ?? '' }}';
            if (password_changed == 'false') {
                $('#modalChangePassword').modal('show')
                $('#closeModalChangePassword').hide()
                $('#modalChangePassword input[name="old_password"]').val('password')
                $('#modalChangePassword input[name="old_password"]').attr('readonly', true)
            }
        })
    </script>

    @yield('js')
</body>

</html>
