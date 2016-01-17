@extends(config('auth.layout'))

@section(config('auth.section'))

	<p><strong>{{ $user->getName() }}</strong>, you have successfully deleted your <a href="{{ url('') }}">{{ Site::name() }}</a> account.</p>

@stop