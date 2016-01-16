@extends(config('auth.layout'))

@section(config('auth.section'))

	<p><strong>{{ $user->getName() }}</strong>, a user account for {{ Site::get('name') }} has been created for you.</p>

	@if (!$user->isActivated())

		<p>
			You will not be able to use your account until you <a href="{{ url('auth/activate/'.$user->id.'/'.$user->activation_code) }}" target="_blank">activate it by clicking on this link</a>. If the link doesn't work, copy and paste the following URL into your browser:
		</p>

		<p><strong>{{ url('auth/activate/'.$user->id.'/'.$user->activation_code) }}</strong></p>

	@endif

	<p>Your username is <strong>{{ $user->username }}</strong>.</p>

@stop