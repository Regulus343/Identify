@extends('errors.layout')

@section('content')

	<h1>401: Unauthorized</h1>

	<p>You are not authorized to access the requested page.</p>

	@include('errors.partials.dev_info')

@stop