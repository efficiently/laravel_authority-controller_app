<html>
    <body>
        <div class="nav">
            <ul class="nav">
                @if ( Auth::guest() )
                    <li>{{ HTML::linkRoute('sessions.create', 'Login') }}</li>
                @else
                    <li>
                        {{ Form::open(['method' => 'DELETE', 'route' => ['sessions.destroy', Auth::user()->id]]) }}
                            {{ Form::submit('Logout', ['class' => 'btn btn-danger']) }}
                        {{ Form::close() }}
                    </li>
                @endif
            </ul>
        </div>

        <h1>Laravel and AuthorityController Quickstart</h1>

        {{-- Success-Messages --}}
        @if ($message = Session::get('success'))
            <div class="alert alert-success alert-block">
                {{{ $message }}}
            </div>
        @endif

        @yield('content')
    </body>
</html>
