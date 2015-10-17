@if (Session::get('developer') && config('auth.unauthorized_route.name'))

	<div class="dev-info">

		<div>
			<span class="label">Matched Route Name:</span>
			<strong>{{ config('auth.unauthorized_route.name') }}</strong>
		</div>

		<div>
			<span class="label">Required Permissions:</span>
			<strong>{{ implode(', ', config('auth.unauthorized_route.permissions')) }}</strong>

			@if (config('auth.unauthorized_route.all_permissions_required'))

				<small>(All Required)</small>

			@endif
		</div>

		<div>
			<span class="label">Current Permissions:</span>
			<strong>{{ implode(', ', Auth::getPermissions()) }}</strong>
		</div>

		<div>
			<span class="label">Current User:</span>
			<strong>{{ Auth::user()->name }}</strong>
			<small>(ID: {{ Auth::user()->id }})</small>
		</div>

	</div><!-- /.info -->

@endif