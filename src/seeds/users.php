<?php

$defaultPassword = Hash::make('password');
$date            = date('Y-m-d H:i:s');

return array(

	array(
		'username'     => 'Admin',
		'password'     => $defaultPassword,
		'email'        => 'admin@localhost',
		'first_name'   => 'Admin',
		'last_name'    => 'Istrator',
		'description'  => 'Anything goes here. Discuss anything you want.',
		'active'       => true,
		'activated_at' => $dateActivated,
		'test'         => false,
	),

	array(
		'username'     => 'TestUser',
		'password'     => $defaultPassword,
		'email'        => 'test@localhost',
		'first_name'   => 'Test',
		'last_name'    => 'Userone',
		'active'       => true,
		'activated_at' => $dateActivated,
		'test'         => true,
	),

	array(
		'username'     => 'TestUser2',
		'password'     => $defaultPassword,
		'email'        => 'test2@localhost',
		'first_name'   => 'Test',
		'last_name'    => 'Usertwo',
		'active'       => true,
		'activated_at' => $dateActivated,
		'test'         => true,
	),

);