@extends(Config::get('identify::layout'))

@section(Config::get('identify::section'))

	<h1>{{ $user->username }}</h1>

@stop