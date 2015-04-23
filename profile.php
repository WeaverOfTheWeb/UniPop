<?php
require_once 'core/init.php';
ob_start();
require_once 'includes/header.php';

if(!$userID = Input::get('user')) {
    Redirect::to('index.php');
} else {
    $user = new User();
	
	if($user->hasPermission('banned')){
		Redirect::to('index.php');
		return false;
	}

    if($user->data()->id !== $userID) {
        Redirect::to('index.php');
    } else {
        $data = $user->data();
		require_once 'includes/nav.php';
?>
<div id="profile_cont">
	<img src="images/users/profile_pics/<?php echo $data->id; ?>.jpg" onerror="this.onerror=null;this.src='images/blank_prof.jpg';" alt="Profile Pic" />
	<br /><br />
	<p>Hello <?php echo escape($data->name); ?>!</p>
	<br />
<?php
    }
}

?>
	<input id="update_btn" onclick="window.location.assign('update.php')" type="button" value="Edit Profile" />
	<input id="changepass_btn" onclick="window.location.assign('changepassword.php')" type="button" value="Change Password" />
	<br /><br />
	<h4>Your Threads</h4>
	<br />
	<ul class="threads">
<?php

$threads = DB::getInstance();
$threads->get('threads', array('user_id', '=', $user->data()->id));

if($threads->count()){
	foreach($threads->results() as $item){
		echo "<li data-id='$item->id'>$item->thread_title</li>";
	}
} else {
	echo "<li>You have no threads..</li>";
}

?>
	</ul>
	<br /><br />
	<h4>Threads you have recently participated in..</h4>
	<br />
	<ul class="posts">
<?php

$comments = DB::getInstance();
$comments->query("SELECT * FROM comments WHERE user_id = ? LIMIT 0,5", array($user->data()->id));

if($comments->count()){
	foreach($comments->results() as $item){
		$thread = DB::getInstance();
		$thread->get('threads', array('id', '=', $item->thread_id));
		echo "<li data-id='$item->thread_id'>$item->comment - ".$thread->first()->thread_title ."</li>";
	}
} else {
	echo "<li>No activity available..</li>";
}
?>
	</ul>
</div>
<?php
require_once 'includes/footer.php';
ob_end_flush();