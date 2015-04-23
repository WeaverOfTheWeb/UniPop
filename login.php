<?php
require_once 'core/init.php';
ob_start();
require_once 'includes/header.php';

if(Session::exists('verified')){
	echo Session::flash('verified');
}

$user = new User();

if($user->isLoggedIn()){
	Redirect::to('index.php');
}

if(Input::exists()){
	if(Token::check(Input::get('token'))){
		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'username' => array('required' => true),
			'password' => array('required' => true)
		));
		
		if($validation->passed()){
			
			$remember = (Input::get('remember') === 'on') ? true : false;
			$login = $user->login(Input::get('username'), Input::get('password'), $remember);
			
			if($login) {
                Redirect::to('index.php');
			} else {
				echo 'Login failed';
			}
		} else {
			echo "<div id='login_error'>";
			foreach($validation->errors() as $errors){
				echo "<p>{$errors}</p>";
			}
			echo "</div>";
		}
	}
}
?>
<div id="large_logo"></div>
<div id="login_cont">
	<form action="" method="post">
		<input type="email" name="username" id="username" placeholder="Your Email Address" />
		<input type="password" name="password" id="password" placeholder="Password" />
		<input type="hidden" name="token" value="<?php echo Token::generate(); ?>" />
		<input type="submit" value="Login" />
		<div id="check_btn">
			<label for="remember">Remember Me</label>
			<input type="checkbox" name="remember" id="remember" />
		</div>
	</form>
	<input type="button" id="sign_btn" value="Sign Up" />
</div>
<?php
require_once 'includes/footer.php';
ob_end_flush();
?>