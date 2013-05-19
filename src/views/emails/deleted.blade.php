@extends(Config::get('identify::layout'))

@section(Config::get('identify::section'))

	<p><strong>{{ $user->getName() }}</strong>, you have successfully deleted your account.</p>

@stop