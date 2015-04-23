<?php
require_once 'core/init.php';
ob_start();
require_once 'includes/header.php';

$user = new User();

if(!$user->isLoggedIn()){
	Redirect::to('login.php');
}

if($user->hasPermission('banned')){
	Redirect::to('index.php');
	return false;
} else {
	require_once 'includes/nav.php';
	if(Input::get('id')){
		$title = DB::getInstance();
		$title->get('threads', array('id', '=', Input::get('id')));
		
		if($title->count()){
			foreach($title->results() as $item){
				$date1 = new DateTime($item->creation_date);
				$date2 = new DateTime("now");
				$interval = $date1->diff($date2);
				
				$name = new User($item->user_id);
				
				echo "<div id='title'>
				<img src='images/users/profile_pics/$item->user_id.jpg' onerror=\"this.onerror=null;this.src='images/blank_prof.jpg';\" alt='user profile pic' /><span id='thread_title'>",
				escape($item->thread_title),
				"</span><span id='name_time'>",
				escape($name->data()->name),
				' - ',
				"<abbr class='timeago' title='".escape($item->creation_date)."'></abbr>" . ($item->user_id === $user->data()->id ? "<a id='delete_thread' data-id='".Input::get('id')."'><img class='sml_icn' src='images/icons/bin.svg' alt='delete thread' /></a>" : '') .
				($item->user_id === $user->data()->id ? '' : "<a id='report_thread' data-id='".Input::get('id')."'><img class='sml_icn' src='images/icons/report.svg' alt='report thread' /></a>")."</span></div><div class='clear'></div>";
			}
		} else {
			Redirect::to(404);
		}
?>
	<ul id="comments">
<?php
		$comments = DB::getInstance();
		$comments->get('comments', array('thread_id', '=', Input::get('id')));
		
		if($comments->count()){
			foreach($comments->results() as $item){
				$date1 = new DateTime($item->creation_date);
				$date2 = new DateTime("now");
				$interval = $date1->diff($date2);
				
				$repName = new User($item->user_id);
				
				echo "<li>
					<img class='profile_pic' src='images/users/profile_pics/". $repName->data()->id .".jpg' onerror=\"this.onerror=null;this.src='images/blank_prof.jpg';\" alt='user profile picture' />
					<span class='comment' style=''>$item->comment</span><br /><br /><span class='comment_name'>".$repName->data()->name ." - " .
					"<abbr class='timeago' title='".escape($item->creation_date)."'></abbr>" . ($item->user_id === $user->data()->id ? " </span><a class='delete_comment' data-id='$item->id' data-thread='$item->thread_id'><img class='sml_icn' src='images/icons/bin.svg' alt='delete comment' /></a>" : '') .
					($item->user_id === $user->data()->id ? '' : "<a class='report_comment' data-id='$item->id' data-thread='".Input::get('id')."'><img class='sml_icn' src='images/icons/report.svg' alt='report comment' /></a>") .
					"</li>";
			}
		} else {
			echo "<li><div id='no_comment'>Post a reply!</div></li>";
		}
		
	} else {
		Redirect::to(404);
	}
	
	if(Input::exists()){
		if(Token::check(Input::get('token'))){
			$validate = new Validate();
			$validation = $validate->check($_POST, array(
				'comment' => array(
					'required' => true,
					'min' => 2,
					'max' => 240
				)
			));
			
			if($validation->passed()){
				try {
					$db = DB::getInstance();
					$db->insert('comments', array(
						'user_id' => $user->data()->id,
						'thread_id' => Input::get('id'),
						'comment' => Input::get('comment'),
						'creation_date' => date('Y-m-d H:i:s')
					));
					
					Redirect::to('thread.php?id='.Input::get('id'));
				} catch(Excaeption $e){
					die($e->getMessage());
				}
			} else {
				echo "<div id='thread_error'>";
				foreach($validation->errors() as $error){
					echo $error, ". ";
				}
				echo "</div>";
			}
		}
	}
	
	if(Input::get('delete') && Input::get('id')){
		$delete = DB::getInstance();
		$delete->get('threads', array('id', '=', Input::get('id')));
		
		if($delete->count()){
			if($user->data()->id === $delete->first()->user_id){
				$delete->delete('threads', array('id', '=', Input::get('delete')));
			}
		}
	}
	
	if(Input::get('comment') && Input::get('id')){
		$comment = DB::getInstance();
		$comment->query("SELECT * FROM comments WHERE id = ?", array(Input::get('comment')));
		
		if($comment->count()){
			$reported_id = $comment->first()->user_id;
			
			$check = DB::getInstance();
			$check->query("SELECT * FROM `reports` WHERE `reported_user` = ? AND `reporter_id` = ? AND `thread_id` = ? AND `comment_id` = ?", array($reported_id, $user->data()->id, Input::get('id'), Input::get('comment')));
			
			if($check->count() === 0){
				$report = DB::getInstance();
				$report->get('comments', array('id', '=', Input::get('comment')));
				try {
					$report->insert('reports', array(
						'reported_user' => $reported_id,
						'reporter_id' => $user->data()->id,
						'reported_comment' => "Comment - ".$comment->first()->comment,
						'comment_id' => Input::get('comment'),
						'thread_id' => Input::get('id'),
						'creation_date' => date('Y-m-d H:i:s')
					));
				} catch(Excaeption $e){
					die($e->getMessage());
				}
				
				$ban = DB::getInstance();
				$ban->query("SELECT * FROM reports WHERE reported_user = ?", array($reported_id));
				
				if($ban->count() >= 25){
					$ban->update('users', $reported_id,
						array('user_group' => 4)
					);
				}
			}
		}
	}
	
	if(Input::get('report_thread') && Input::get('id')){
		$thread = DB::getInstance();
		$thread->query("SELECT * FROM threads WHERE id = ?", array(Input::get('id')));
		
		if($thread->count()){
			$reported_id = $thread->first()->user_id;
			
			$check = DB::getInstance();
			$check->query("SELECT * FROM `reports` WHERE `reported_user` = ? AND `reporter_id` = ? AND `thread_id` = ?", array($reported_id, $user->data()->id, Input::get('id')));
			
			if($check->count()){
				// Do Nothing
			} else {
				$report = DB::getInstance();
				$report->get('threads', array('id', '=', Input::get('id')));
				
				try {
					$report->insert('reports', array(
						'reported_user' => $reported_id,
						'reporter_id' => $user->data()->id,
						'reported_comment' => "Thread - ".$report->first()->thread_title,
						'thread_id' => Input::get('id'),
						'creation_date' => date('Y-m-d H:i:s')
					));
				} catch(Excaeption $e){
					die($e->getMessage());
					return;
				}
				
				$ban = DB::getInstance();
				$ban->query("SELECT * FROM reports WHERE reported_user = ?", array($reported_id));
				
				if($ban->count() >= 25){
					$ban->update('users', $reported_id,
						array('user_group' => 4)
					);
				}
			}
		}
	}
	
	if(Input::get('delete_comment') && Input::get('id')){
		$delete = DB::getInstance();
		$delete->query("SELECT * FROM comments WHERE id = ?", array(Input::get('delete_comment')));
		
		if($delete->count()){
			if($user->data()->id === $delete->first()->user_id){
				$delete->delete('comments', array('id', '=', Input::get('delete_comment')));
			}
		}
	}
}

?>
	</ul>
	<div id="reply_cont">
		<form action="" method="post">
			<input type="text" name="comment" autocomplete="off" placeholder="Type a comment.." />
			<input type="submit" value="" />
			<input type="hidden" name="token" value="<?php echo Token::generate(); ?>" />
		</form>
	</div>

<?php
require_once 'includes/footer.php';
ob_end_flush();