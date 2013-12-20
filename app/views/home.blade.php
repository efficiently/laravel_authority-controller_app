@extends('layouts.application')

@section('content')
  <h2>Welcome</h2>
  @if (Authority::can('read', 'User'))
    {{ HTML::linkRoute('users.index', "See users list") }}
  @endif
@stop
