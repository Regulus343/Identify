<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| User Create/Update Setup
	|--------------------------------------------------------------------------
	|
	| The data setup for creating and updating user. You may create different
	| types for different situations. There are 2 types already here by
	| default which are "standard" and "password". This data is
	| held in the config file so composer update won't overwrite your custom
	| settings that will vary based on your users table setup.
	|
	*/
	'dataSetup' => array(
		'standard' => array(
			'username'   => trim(Input::get('username')),
			'email'      => trim(Input::get('email')),
			'first_name' => ucfirst(trim(Input::get('first_name'))),
			'last_name'  => ucfirst(trim(Input::get('last_name'))),
			'website'    => (Input::get('website') == 'http://') ? '' : 'http://'.str_replace('http://', '', trim(strtolower(Input::get('website')))),
		),

		'password' => array(
			'password'   => Input::get('password'),
		),
	),
);