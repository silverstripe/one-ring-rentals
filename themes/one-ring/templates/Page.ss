<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="en"> <!--<![endif]-->
<head>
	<% base_tag %>
	$MetaTags(false)
	<title>One Ring Rentals: $Title</title>	
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
	
	<!-- IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	  <script src="http:/html5shim.googlecode.com/svn/trunk/html5.js"></script> 
	<![endif]-->

	<link rel="apple-touch-icon" sizes="57x57" href="themes/one-ring/images/favicon/apple-icon-57x57.png">
	<link rel="apple-touch-icon" sizes="60x60" href="themes/one-ring/images/favicon/apple-icon-60x60.png">
	<link rel="apple-touch-icon" sizes="72x72" href="themes/one-ring/images/favicon/apple-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="76x76" href="themes/one-ring/images/favicon/apple-icon-76x76.png">
	<link rel="apple-touch-icon" sizes="114x114" href="themes/one-ring/images/favicon/apple-icon-114x114.png">
	<link rel="apple-touch-icon" sizes="120x120" href="themes/one-ring/images/favicon/apple-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="144x144" href="themes/one-ring/images/favicon/apple-icon-144x144.png">
	<link rel="apple-touch-icon" sizes="152x152" href="themes/one-ring/images/favicon/apple-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="180x180" href="themes/one-ring/images/favicon/apple-icon-180x180.png">
	<link rel="icon" type="image/png" sizes="192x192"  href="themes/one-ring/images/favicon/android-icon-192x192.png">
	<link rel="icon" type="image/png" sizes="32x32" href="themes/one-ring/images/favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="96x96" href="themes/one-ring/images/favicon/favicon-96x96.png">
	<link rel="icon" type="image/png" sizes="16x16" href="themes/one-ring/images/favicon/favicon-16x16.png">
</head>
<body>
	<div id="wrapper">

		<header id="header">
			<% include TopBar %>			
			<% include MainNav %>
		</header>
		
		$Layout
				
		<% include Footer %>
	</div>
</body>
</html>