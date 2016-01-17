@extends(config('auth.layout'))

@section(config('auth.section'))

	<p>
		<strong>{{ $user->getName() }}</strong>, you have been banned from <a href="{{ url('') }}">{{ Site::name() }}</a>. If you have any questions as to why you've been banned or believe you may have been banned in error, please reply to this email.
	</p>

@stop