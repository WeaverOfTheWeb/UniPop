<?php
require_once 'core/init.php';
ob_start();
require_once 'includes/header.php';

$user = new User();

if($user->hasPermission('admin')){
	require_once 'includes/nav.php';
	
	if(Input::exists()){
		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'username' => array(
				'required' => true,
				'email' => true
			)
		));
		
		if($validation->passed()){
			$db = DB::getInstance();
			$db->query("SELECT * FROM users WHERE username = ?", array(Input::get('username')));
			
			if($db->count()){
				$user = new User($db->first()->id);
				$id = $db->first()->id;
				
				if(Input::get('unblock') == 1){
					if(!$user->hasPermission('banned')){
						echo "<div id='update_error'>
								User hasn't been banned.
							  </div>";
					} else {
						$db->update(
							'users', 
							$id, 
							array(
								'user_group' => 1
							)
						);
						
						echo "<div id='update_error'>
								User has been unbanned.
							  </div>";
					}
				} else {
					if($user->hasPermission('banned')){
						echo "<div id='update_error'>
								User is already banned.
							  </div>";
					} else {
						$db->update(
							'users', 
							$id, 
							array(
								'user_group' => 4
							)
						);
						
						echo "<div id='update_error'>
								User has been banned.
							  </div>";
					}
				}
			}
		} else {
			echo "<div id='update_error'>";
			foreach($validation->errors() as $error){
				echo $error;
			}
			echo "</div>";
		}
	}
} else {
	Redirect::to('index.php');
}
?>
<div id="profile_cont">
	<h5>Ban User</h5>
	<form action="" method="post">
		<input type="text" name="username" placeholder="User Email to Ban" />
		<input type="hidden" name="unblock" value="0" />
		<input type="submit" value="Ban User" />
	</form>
	<br />
	<h5>UnBan User</h5>
	<form action="" method="post">
		<input type="text" name="username" placeholder="User Email to UnBan" />
		<input type="hidden" name="unblock" value="1" />
		<input type="submit" value="UnBan User" />
	</form>
</div>
<?php
require_once 'includes/footer.php';
ob_end_flush();