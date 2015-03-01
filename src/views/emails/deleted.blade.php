@extends(config('auth.layout'))

@section(config('auth.section'))

	<p><strong>{{ $user->getName() }}</strong>, you have successfully deleted your account.</p>

@stop