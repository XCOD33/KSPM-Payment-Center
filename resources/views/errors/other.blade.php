@extends('errors.app')

@section('content')
    <section class="content">
        <div class="error-page">
            <h2 class="headline text-warning"> {{ $code }}</h2>
            <div class="error-content">
                <h3><i class="fas fa-exclamation-triangle text-warning"></i> {{ $message }}.</h3>
                <p>
                    Oops, there's a problem.
                    Meanwhile, you may <a href="{{ route('dashboard') }}">return to dashboard</a> or try using the search
                    form.
                </p>
            </div>

        </div>

    </section>
@endsection
