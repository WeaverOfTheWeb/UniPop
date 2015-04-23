$(document).ready(function(){
	if($("#login_error").css('display') !== 'none'){
		setTimeout(function(){
			$("#login_error").fadeOut(500);
		},1500);
	}
	if($("#update_error").css('display') !== 'none'){
		setTimeout(function(){
			$("#update_error").fadeOut(500);
		},1500);
	}
	if($("#thread_error").css('display') !== 'none'){
		setTimeout(function(){
			$("#thread_error").fadeOut(500);
		},1500);
	}
	
	$("#reg_back").click(function(){
		window.location.href = 'login.php';
	});
	
	$("#remember").change(function(){
		if($(this).is(':checked')){
			$("#check_btn").toggleClass('checked');
		} else {
			$("#check_btn").toggleClass('checked');
		}
	});
	
	$("#sign_btn").click(function(){
		window.location.href = 'register.php';
	});
	
	$("#home").click(function(){
		window.location.href = 'index.php';
	});
	
	$("#comments").on("click", ".report_comment", function(){
		if(confirm('Do you want to report this comment?')){
			$.ajax({
				url: "thread.php?id="+$(this).data('thread')+"&comment="+$(this).data('id'),
			}).done(function(data){
				alert('Comment was reported.');
			}).error(function(jqXHR, textStatus, http){
				alert('Unable to report comment..');
				console.log(textStatus+" : "+http);
			});
		}
	});
	
	$("#title").on("click", "#report_thread", function(){
		if(confirm('Do you want to report this thread?')){
			$.ajax({
				url: "thread.php?id="+$(this).data('id')+"&report_thread="+$(this).data('id'),
			}).done(function(data){
				alert('Thread was reported.');
			}).error(function(jqXHR, textStatus, http){
				alert('Unable to report thread..');
				console.log(textStatus+" : "+http);
			});
		}
	});
	
	$("#title").on("click", "#delete_thread", function(){
		if(confirm('Do you want to delete this thread?')){
			$.ajax({
				url: "thread.php?id="+$(this).data('id')+"&delete="+$(this).data('id'),
			}).done(function(data){
				window.location.href = 'index.php';
			}).error(function(jqXHR, textStatus, http){
				alert('Could not delete comment..');
				console.log(textStatus+" : "+http);
			});
		}
	});
	
	$("#comments").on("click", ".delete_comment", function(){
		var elem = $(this);
		if(confirm('Do you want to delete this comment?')){
			$.ajax({
				url: "thread.php?id="+$(this).data('thread')+"&delete_comment="+$(this).data('id'),
			}).done(function(data){
				elem.closest("li").fadeOut();
			}).error(function(jqXHR, textStatus, http){
				alert('Could not delete comment..');
				console.log(textStatus+" : "+http);
			});
		}
		e.preventDefault();
	});
	
	$("#back_btn").click(function(){
		window.location.href = 'index.php';
	});

	$("#nav_prof").click(function(){
		$("#nav_list").slideToggle(250);
	});
	$("#nav_ref").click(function(){location.reload(true);});
	
	$("#feed, .posts, .threads").on("click", "li", function(){
		if($(this).data('id') > 0){
			window.location.href = 'thread.php?id='+$(this).data('id');
		}
	});
	
	$("#create_btn").click(function(){
		$("#create_thread").toggleClass('slide');
	});
	$("#close_btn").click(function(){
		$("#create_thread").toggleClass('slide');
	});
});