<?php
require_once 'core/init.php';
ob_start();
require_once 'includes/header.php';

if(Session::exists('registered')){
	echo Session::flash('registered');
}

$user = new User();
if($user->isLoggedIn()){
	Redirect::to('index.php');
	return false;
}
	
if(Input::exists()){
	if(Token::check(Input::get('token'))){
		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'name' => array(
				'required' => true,
				'min' => 2,
				'max' => 20
			),
			'username' => array(
				'email' => true,
				'required' => true,
				'max' => 64,
				'unique' => 'users'
			),
			'username_again' => array(
				'matches' => 'username'
			),
			'password' => array(
				'required' => true,
				'min' => 6
			),
			'password_again' => array(
				'matches' => 'password'
			),
			'university' => array(
				'required' => true
			)
		));
		
		if($validation->passed()){
			$user = new User();
			
			$salt = Hash::salt('32');
			$hash = Hash::unique();
			
			try {
				$user->create(array(
					'username' => Input::get('username'),
					'password' => Hash::make(Input::get('password'), $salt),
					'salt' => $salt,
					'name' => Input::get('name'),
					'joined' => date('Y-m-d H:i:s'),
					'user_group' => 3,
					'university' => Input::get('university'),
					'token' => $hash
				));
				
				mail(
					Input::get('username'), 
					'UniPop Account Verification',
					
					"<p>Hello ".Input::get('name')."!</p>
					<p>Thank you for signing up to UniPop, click <a href='http://myendoftheweb.com/unipop/verify.php?vertok={$hash}' target='_blank'>here</a> to verify your account!</p>
					<p>The UniPop Team</p>",
					
					'From: no-reply@myendoftheweb.com' . "\r\n" .
					'Reply-To: no-reply@myendoftheweb.com' . "\r\n" .
					"MIME-Version: 1.0\r\n" . 
					"Content-Type: text/html; charset=ISO-8859-1\r\n" .
					'X-Mailer: PHP/' . phpversion()
				);
				
				Session::flash('registered', "<div id='login_error'>You have been successfully registered. A verification email has been sent to you!</div>");
				Redirect::to('register.php');
				
			} catch(Excaeption $e){
				die($e->getMessage());
			}
		} else {
			echo "<div id='login_error'>";
			foreach($validation->errors() as $error){
				echo "<p>",$error,"</p>";
			}
			echo "</div>";
		}
	}
}
?>
<div id="large_logo"></div>
<div id="login_cont">
	<div id="login_sign_up">
		<form action="" method="post">
			<input type="text" name="name" id="name" value="<?php echo escape(Input::get('name')); ?>" placeholder="Full name" />

			<input type="text" name="username" id="username" value="<?php echo escape(Input::get('username')); ?>" placeholder="Student Email Address (.ac.uk)" />

			<input type="text" name="username_again" id="username_again" value="<?php echo escape(Input::get('username_again')); ?>" placeholder="Re-enter Your Student Email Address (.ac.uk)" />

			<input type="password" name="password" id="password" placeholder="Choose a password" />

			<input type="password" name="password_again" id="password_again" placeholder="Re-enter your password" />

			<select id="university" name="university" required>
				<option value="" selected disabled>-- Select a University --</option>
				<option value="abdn">University of Aberdeen</option>
				<option value="abertay">University of Abertay Dundee</option>
				<option value="st-andrews">University of St Andrews</option>
				<option value="dundee">University of Dundee</option>
				<option value="ed">University of Edinburgh</option>
				<option value="napier">Edinburgh Napier University</option>
				<option value="gla">University of Glasgow</option>
				<option value="gcu">Glasgow Caledonian University</option>
				<option value="hw">Heriot-Watt University</option>
				<option value="uhi">University of the Highlands and Islands</option>
				<option value="qmu">Queen Margaret University</option>
				<option value="rgu">The Robert Gordon University</option>
				<option value="stir">University of Stirling</option>
				<option value="strath">University of Strathclyde</option>
				<option value="uws">University of the West of Scotland</option>
			</select>
			
			<input type="hidden" name="token" value="<?php echo Token::generate(); ?>" />
			<input type="submit" value="Register" />
		</form>
	</div>
	<input type="button" id="reg_back" value="Back" />
</div>
<?php
require_once 'includes/footer.php';
ob_end_flush();
?>