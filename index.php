<?php
require_once 'core/init.php';
ob_start();
require_once 'includes/header.php';

$user = new User();

if(Input::get('title')){
	if(Token::check(Input::get('token'))){
		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'title' => array(
				'required' => true,
				'min' => 4,
				'max' => 40
			)
		));
		
		if($validation->passed()){
			try {
				$db = DB::getInstance();
				$db->insert('threads', array(
					'user_id' => $user->data()->id,
					'thread_title' => Input::get('title'),
					'university' => $user->data()->university,
					'creation_date' => date('Y-m-d H:i:s')
				));
				
				$db->query("SELECT * FROM threads WHERE user_id = ? ORDER BY creation_date DESC", array($user->data()->id));
				Redirect::to('thread.php?id='.$db->first()->id);
			} catch(Exception $e){
				die($e->getMessage());
			}
		} else {
			echo "<div id='update_error'>";
			foreach($validation->errors() as $error){
				echo $error, ". ";
			}
			echo "</div>";
		}
	}
}

if(Input::get('verify')){
	$hash = Hash::unique();
	$user->update(array(
		'token' => $hash
	));
	
	mail(
		$user->data()->username, 
		'UniPop Account Verification',
		
		"<p>Hello ".$user->data()->name."!</p>
		<p>Thank you for signing up to UniPop, click <a href='http://myendoftheweb.com/unipop/verify.php?vertok={$hash}' target='_blank'>here</a> to verify your account!</p>
		<p>The UniPop Team</p>",
		
		'From: no-reply@myendoftheweb.com' . "\r\n" .
		'Reply-To: no-reply@myendoftheweb.com' . "\r\n" .
		"MIME-Version: 1.0\r\n" . 
		"Content-Type: text/html; charset=ISO-8859-1\r\n" .
		'X-Mailer: PHP/' . phpversion()
	);
}

if(!$user->isLoggedIn()){
	Redirect::to('login.php');
}

if($user->isLoggedIn()){
	require_once 'includes/header.php';
	require_once 'includes/nav.php';
	
	if($user->hasPermission('verified') && !$user->hasPermission('banned')){
?>
	<div id='search'>
		<form action="" name="search_form" method="post">
			<input name="search" type="text" placeholder="Search" autocomplete="off" />
			<input type="hidden" name="token2" value="<?php echo Token::generate(); ?>" />
			<img id="search_btn" src="images/icons/search.svg" alt="" />
		</form>
	</div>
	<div id="create_btn"></div>
	<div id="create_thread">
		<form action="" method="post">
			<input type="text" name="title" placeholder="Thread Title" autocomplete="off" />
			<input type="hidden" name="token" value="<?php echo Token::generate(); ?>" /><br />
			<input type="button" id="close_btn" value="Cancel" />
			<input type="submit" value="Create Thread" />
		</form>
	</div>
<?php
	}
?>
	<ul id="feed">
<?php
	if(!$user->hasPermission('verified')){
		echo "<ul id='feed' class='unverified'>
			<li>Please verify your account!<br /><br />
		<a href='?verify=1'>Click here</a> to resend your confirmation email.</li>
		</ul>";
	} else if($user->hasPermission('banned')){
		echo "<ul id='feed' class='unverified'>
			<li>Your account has been banned.</li>
		</ul>";
		return false;
	} else if(Input::get('search')){
			$validate = new Validate();
			$validation = $validate->check($_POST, array(
				'search' => array(
					'required' => true,
					'min' => 4
				)
			));
			
			if($validation->passed()){
				$term = explode(" ", Input::get('search'));
				$likes = " thread_title LIKE ";
				$count = 0;
				
				try{
					foreach($term as $item){
						if($count !== (count($term)-1)){
							$likes.= " '%$item%' OR thread_title LIKE ";
							$count++;
						} else {
							$likes.= "'%$item%' ";
						}
					}
				
					$db = DB::getInstance();
					$db->query("SELECT * FROM threads WHERE $likes AND university = ?", array($user->data()->university));
					
					if($db->count()){
						foreach($db->results() as $item){
							$user = new User($item->user_id);

							echo "<li data-id='" . $item->id . "'>
								<img src='images/users/profile_pics/" . $item->user_id . ".jpg' class='profile_pic' onerror=\"this.onerror=null;this.src='images/blank_prof.jpg';\" /> <span class='title'>" . escape($item->thread_title) . "</span> <span class='username'>" . escape($user->data()->name) . "</span> <span class='time'><abbr class='timeago' title='" . escape($item->creation_date) . "'></abbr></span></li>";
						}
					} else {
						echo "<li data-id='0' class='unverified'>No items found..</li>";
					}
				} catch(Exception $e){
					die($e->getMessage());
				}
			} else {
				$error = "";
				foreach($validation->errors() as $errors){
					$error = $errors;
				}
				echo "<li class='unverified' data-id='0'>A $error</script>";
			}
	} else {
		$db = DB::getInstance();
		
		$db->query("SELECT * FROM threads WHERE university = ? AND creation_date > DATE_SUB('".date("Y-m-d H:i:s")."', INTERVAL 48 HOUR) ORDER BY creation_date DESC", array($user->data()->university));
		
		if($db->count()){
			foreach($db->results() as $item){
				$user = new User($item->user_id);
				
				echo "<li data-id='" . $item->id . "'>
				<img src='images/users/profile_pics/" . $item->user_id . ".jpg' class='profile_pic' onerror=\"this.onerror=null;this.src='images/blank_prof.jpg';\" /> <span class='title'>" . escape($item->thread_title) . "</span> <span class='username'>" . escape($user->data()->name) . "</span> <span class='time'><abbr class='timeago' title='" . escape($item->creation_date) . "'></abbr></span>
				</li>";
			}
		} else {
			echo "<li data-id='0' class='nothreads'>There are no threads available..</li>";
		}
	}

}
?>
	</ul>
<?php
require_once 'includes/footer.php';
ob_end_flush();
?>