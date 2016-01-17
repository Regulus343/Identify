@extends(config('auth.layout'))

@section(config('auth.section'))

	<p>
		<strong>{{ $user->getName() }}</strong>, you may reset your <a href="{{ url('') }}">{{ Site::name() }}</a> password by <a href="{{ URL::to('reset-password/'.$user->id.'/'.$user->reset_password_code) }}" target="_blank">clicking on this link</a>. If the link doesn't work, copy and paste the following URL into your browser:
	</p>

	<p><strong>{{ url('reset-password/'.$user->id.'/'.$user->reset_password_code) }}</strong></p>

@stop