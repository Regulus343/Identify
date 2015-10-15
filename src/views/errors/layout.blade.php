<!DOCTYPE html>
<html lang="en-US">
<head>
	<title>Error</title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link href="http://fonts.googleapis.com/css?family=Lato:400" rel="stylesheet" type="text/css">
	<style type="text/css">
		body {
			display: table;
			margin: 120px 0 0 0;
			padding: 0;
			width: 100%;
			height: 100%;
			color: #555;
			font-weight: 100;
			font-family: 'Lato';
		}

		.container {
			text-align: center;
			display: table-cell;
			vertical-align: middle;
		}

		h1 { font-size: 3em; }

		.dev-info { margin: 0 auto; padding: 6px; width: 520px; border: 1px dashed #ccc; text-align: left; }
		.dev-info .label { display: inline-block; width: 200px; }
	</style>
</head>
<body>
	<div class="container">

		@yield('content')

	</div><!-- /.container -->
</body>
</html>