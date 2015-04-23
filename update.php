<?php
require_once 'core/init.php';
ob_start();
require_once 'includes/header.php';

$user = new User();

if(Session::exists('update_details') || Session::exists('image_error')){
	if(Session::exists('update_details')){
		echo Session::flash('update_details');
	} else if(Session::exists('image_error')){
		echo Session::flash('image_error');
	}
}

if(!$user->isLoggedIn()){
	Redirect::to('login.php');
}

if(Input::exists()){
	if(Token::check(Input::get('token'))){
		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'name' => array(
				'required' => true,
				'min' => 2,
				'max' => 50
			)
		));
		
		if($validation->passed()){
			try{
				$user->update(array(
					'name' => Input::get('name')
				));
			} catch(Exception $e){
				die($e->getMessage());
			}
			
			Session::flash('update_details', "<div id='update_error'>Your details have been updated successfully!</div>");
				
			Redirect::to('update.php');
		} else {
			echo "<div id='update_error'>";
			foreach($validation->errors() as $error){
				echo $error, ". ";
			}
			echo "</div>";
		}
	}
}
require_once 'includes/nav.php';
?>
<div id="profile_cont">
	<img src="images/users/profile_pics/<?php echo $user->data()->id ?>.jpg" onerror="this.onerror=null;this.src='images/blank_prof.jpg';" alt="User profile pic" />

	<form action="" method="post">
		<input type="text" name="name" value="<?php echo escape($user->data()->name); ?>" />
		<input type="hidden" name="token" value="<?php echo Token::generate(); ?>" />
		<input type="submit" value="Update Name" />
	</form>

	<form name="image_upload" enctype="multipart/form-data" method="post" action="image.php">
		<input type="file" size="32" name="image" value="">
		<input type="submit" id="Submit" value="Upload Profile Pic">
	</form>
</div>
<?php
require_once 'includes/footer.php';
ob_end_flush();
?>