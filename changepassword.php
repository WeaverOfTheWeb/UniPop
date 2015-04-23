<?php
require_once 'core/init.php';
ob_start();
require_once 'includes/header.php';

if(Session::exists('update')){
	echo "<div id='update_error'>".Session::flash('update')."</div>";
}

$user = new User();

if(!$user->isLoggedIn()){
	Redirect::to('login.php');
}

if(Input::exists()){
	if(Token::check(Input::get('token'))){
		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'password_current' => array(
				'required' => true,
				'min' => 6
			),
			'password_new' => array(
				'required' => true,
				'min' => 6
			),
			'password_new_again' => array(
				'required' => true,
				'min' => 6,
				'matches' => 'password_new'
			)
		));
		
		if($validation->passed()){
			if(Hash::make(Input::get('password_current'), $user->data()->salt) !== $user->data()->password){
				echo "<div id='update_error'>Your current password is wrong..</div>";
			} else {
				$salt = Hash::salt(32);
				$user->update(array(
					'password' => Hash::make(Input::get('password_new'), $salt),
					'salt' => $salt
				));
				
				Session::flash('update', 'Your password has been changed successfully');
				Redirect::to('changepassword.php');
			}
		} else {
			echo "<div id='password_error'>Error updating password..</div>";
		}
	}
}
require_once 'includes/nav.php';
?>
<div id="profile_cont">
	<form action="" method="post">
		<input type="password" name="password_current" placeholder="Current Password" />
		
		<input type="password" name="password_new" placeholder="New Password" />
		
		<input type="password" name="password_new_again" placeholder="Re-enter New Password" />
		
		<input type="submit" value="Update Password" />
		<input type="hidden" name="token" value="<?php echo Token::generate(); ?>" />
	</form>
</div>
<?php
require_once 'includes/footer.php';
ob_end_flush();
?>