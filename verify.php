<?php
require_once 'core/init.php';
ob_start();
require_once 'includes/header.php';

if(Input::get('vertok')){
	if(Token::check(Input::get('token'))){
		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'username' => array(
				'required' => true,
				'email' => true,
				'verify' => 'users'
			)
		));
		
		if($validation->passed()){
			$user = new User(Input::get('username'));
			if($user->data()->token === Input::get('vertok')){
				if(!$user->hasPermission('verified')){
					try{
						$db = DB::getInstance();
						$db->update('users', $user->data()->id, array(
							'user_group' => 1,
							'token' => ''
						));
						
						Session::flash('verified', 'You account has been successfully verified, now you can log in!');
						Redirect::to('index.php');
					} catch(Exception $e){
						die($e->getMessage());
					}
				} else {
					echo "<div id='login_error'>This account has already been verified!</div>";
				}
			} else {
				echo "<div id='login_error'>Tokens don't match..</div>";
			}
			
		} else {
			echo "<div id='login_error'>";
			foreach($validation->errors() as $error){
				echo $error, ". ";
			}
			echo "</div>";
		}
	}
} else {
	Redirect::to('login.php');
}
?>
<div id="large_logo"></div>
<div id="login_cont">
<form action="" method="post">
	<div class="field">
		<input type="text" name="username" placeholder="Insert your email address" />
	</div>
	
	<input type="submit" value="Verify" />
	<input type="hidden" name="token" value="<?php echo Token::generate(); ?>" />
</form>
</div>
<?php
require_once 'includes/footer.php';
ob_end_flush();