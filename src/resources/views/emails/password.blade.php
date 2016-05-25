@extends(config('auth.layout'))

@section(config('auth.section'))

	<p>
		{{ $user->getName() }},<br /><br />

		You may reset your <a href="{{ $user->getPasswordResetUrl() }}">{{ Site::name() }}</a> password by <a href="{{ $user->getPasswordResetUrl() }}" target="_blank">clicking on this link</a>. If the link doesn't work, you may copy and paste the following URL into your browser:
	</p>

	<p><strong>{{ $user->getPasswordResetUrl() }}</strong></p>

@stop