<!DOCTYPE html>
<html>
<head>
	<title>UniPop</title>
	<meta name="viewport" content="width=device-width, user-scalable=no">
	<script src="js/jquery.1.11.2.min.js"></script>
	<link rel="stylesheet" type="text/css" href="css/style.css" />
	<script src="js/functions.js"></script>
	<script src="js/timeago.js"></script>
	<script>
		jQuery(document).ready(function() {
			jQuery("abbr.timeago").timeago();
			
			/*var netStatus = setInterval(function(){
				if(!navigator.onLine){
					$("#offline").show();
				} else {
					$("#offline").hide();
				}
			},500);*/
		});
	</script>
<?php
	$file = basename($_SERVER['PHP_SELF']);
	if($file == "login.php" || $file == "register.php" || $file == "verify.php"){
		echo "<style>body{background:#2A3B61;}</style>";
	}
?>
</head>
<body>