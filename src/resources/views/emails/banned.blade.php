@extends(config('auth.layout'))

@section(config('auth.section'))

	<p>
		<strong>{{ $user->getName() }}</strong>, you have been banned from {{ Site::get('name') }}. If you have any questions as to why you've been banned or believe you may have been banned in error, please reply to this email.
	</p>

@stop