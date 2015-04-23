<?php
require_once 'core/init.php';
require_once 'functions/resize.php';

$user = new User();

// Source - http://www.w3schools.com/php/php_file_upload.asp

$target_dir = "images/users/profile_pics/";
$target_file = $target_dir . basename($_FILES["image"]["name"]);
$uploadOk = 1;
$error = "";
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if($check !== false) {
		$error .= "File is an image - " . $check["mime"] . ".. ";
        $uploadOk = 1;
    } else {
		$error .= "File is not an image.. ";
        $uploadOk = 0;
    }
}

if ($_FILES["image"]["size"] > 500000){
    $error .= "Sorry, your file is too large.. ";
    $uploadOk = 0;
}

if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
    $error .= "Sorry, only JPG, JPEG, PNG & GIF files are allowed.. ";
    $uploadOk = 0;
}

if ($uploadOk == 0) {
    Session::flash('image_error', "<div id='update_error'>".$error." Sorry, your file was not uploaded..</div>");
	Redirect::to('update.php');
} else {
	$file1 = $target_dir.$user->data()->id .".".$imageFileType;
	$file2 = $target_dir.$user->data()->id;
    if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $user->data()->id . "." .$imageFileType)) {
		Session::flash('image_error', "<div id='update_error'>Profile picture updated successfully!</div>");
		image_resize($file1, $file2.".jpg", 150, 150, 1);
		Redirect::to('update.php');
	} else {
        Session::flash('image_error', "<div id='update_error'>Sorry, there was an error uploading your file..</div>");
		Redirect::to('update.php');
    }
}
?>