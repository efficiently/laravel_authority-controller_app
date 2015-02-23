@extends('app')

@section('content')
  <h2>Users list</h2>
  <ul>
  @foreach($users as $user)
    <li>{{{ $user->name }}}</li>
  @endforeach
  </ul>
@stop
