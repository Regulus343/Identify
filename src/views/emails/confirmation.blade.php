@extends(config('auth.layout'))

@section(config('auth.section'))

	<p>
		<strong>{{ $user->getName() }}</strong>, you have successfully signed up for a free {{ Site::get('name') }} membership! You will not be able to user your account until you <a href="{{ URL::to('activate/'.$user->id.'/'.$user->activation_code) }}" target="_blank">activate it by clicking on this link</a>. If the link doesn't work, copy and paste the following URL into your browser:
	</p>

	<p><strong>{{ URL::to('activate/'.$user->id.'/'.$user->activation_code) }}</strong></p>

	<p>Your username is <strong>{{ $user->username }}</strong>.</p>

@stop