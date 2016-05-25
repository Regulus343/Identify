@extends(config('auth.layout'))

@section(config('auth.section'))

	<p>
		{{ $user->getName() }},<br /><br />

		Your user account has been registered at <a href="{{ $user->getActivationUrl() }}">{{ Site::name() }}</a>. Please <a href="{{ $user->getActivationUrl() }}">click here to activate your account</a>. If the link doesn't work, you may copy and paste the following URL into your browser:
	</p>

	<p><strong>{{ $user->getActivationUrl() }}</strong></p>

	<p>
		After you have activated your account, you may log in with either <strong>{{ $user->username }}</strong> or <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>.
	</p>

@stop