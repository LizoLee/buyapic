<?php
//Личный кабинет================================================================

error_reporting(-1);
if ( !session_id() ) { session_start(); }
 include_once 'buyapic_db.php';
 $change = new BuyAPic;
//echo "Личный кабинет ".$_COOKIE['id'];
if ( isset($_POST['changeBasic']) ){
    if ( $_POST['userName'] != "" ){
        $change->changeNameDB($_COOKIE['id'], $_POST['userName']);
    }
    if ( $_POST['webPage'] != "" ){
        $change->changeWebPageDB($_COOKIE['id'], $_POST['webPage']);
    }
    if ( $_POST['bankAccount'] != "" ){
        $change->changeBankAccountDB($_COOKIE['id'], $_POST['bankAccount']);
    }
}
else if ( isset($_POST['deleteWebPage']) ){
    $change->deleteWebPageDB($_COOKIE['id']);
}
else if ( isset($_POST['deleteBankAccount']) ){
    $change->deleteBankAccountDB($_COOKIE['id']);
}
else if ( isset($_POST['deleteAvatar']) ){
    $change->deletePhotoLinkDB($_COOKIE['id']);
}
else if ( isset($_POST['changeAvatar']) ){
    
    if ( !($_FILES['newAvatar']['type'] == 'image/jpeg') ) {
        $_SESSION['error']['upload'] = 'Картинка должна быть в формате jpeg';
    } else {
        $uploadfile = 'uploads/avatars/' . crypt ( $_COOKIE['id'], 'rl' );
        if (!move_uploaded_file($_FILES['newAvatar']['tmp_name'], $uploadfile)) {
            $_SESSION['error']['upload'] = 'Не удалось загрузить файл';
        }
        $change->changePhotoLinkDB($_COOKIE['id'], $uploadfile);
//        print_r($_FILES);
    }    
}
else if ( isset($_POST['changeSelfInfo']) ){
    $change->changeSelfInfoDB($_COOKIE['id'], trim($_POST['selfInfo']));
}
else if ( isset($_POST['changePassword']) ){
    
}

unset($_POST);
unset($_FILES);
$_SESSION['pageInfo'] = $change->getUserInfoDB($_COOKIE['id']);

header('Location: buyapic_config_userdetails.html');
?>

