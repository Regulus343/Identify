@extends(config('auth.layout'))

@section(config('auth.section'))

	<p>
		{{ $user->getName() }},<br /><br />

		You have successfully closed your <a href="{{ url('') }}">{{ Site::name() }}</a> account.
	</p>

@stop