<div id="nav">
	<div id="nav_prof">
		<img src ="images/users/profile_pics/<?php echo $user->data()->id; ?>.jpg" onerror="this.onerror=null;this.src='images/blank_prof.jpg';" alt="User profile pic" />
		<div>&dtrif;</div>
	</div>
	<div id="logo"></div>
<?php
	if(basename($_SERVER['PHP_SELF']) == "thread.php" || basename($_SERVER['PHP_SELF']) == "index.php"){
		echo "<div id='nav_ref'></div>";
	} else {
		echo "<div id='home'></div>";
	}
?>
</div>
<ul id="nav_list">
<?php
	if($user->hasPermission('admin')){
		echo "<li><a href='admin.php'>Admin</a></li>";
	}
?>
	<li><a href="profile.php?user=<?php echo $user->data()->id; ?>">Profile</a></li>
	<li><a href="logout.php">Log Out</a></li>
</ul>
<?php
	if(basename($_SERVER['PHP_SELF']) == "thread.php"){
		echo "<div id='back_btn'>Back</div>";
	}
?>