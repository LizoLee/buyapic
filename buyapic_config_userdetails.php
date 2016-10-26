<?php
//Личный кабинет. Конфигурация профиля==========================================

include_once 'buyapic_header.php';

if ( isset($_POST['changeBasic']) ){
    if ( $_POST['userName'] != "" ){
        $dbConnectionObject->changeUserDB('Name', $_COOKIE['id'], $_POST['userName']);
    }
    if ( $_POST['webPage'] != "" ){
        $dbConnectionObject->changeUserDB('WebPage', $_COOKIE['id'], $_POST['webPage']);
    }
    if ( $_POST['bankAccount'] != "" ){
        $dbConnectionObject->changeUserDB('BankAccount', $_COOKIE['id'], $_POST['bankAccount']);
    }
}
else if ( isset($_POST['deleteWebPage']) ){
    $dbConnectionObject->deleteFromUserDB('WebPage', $_COOKIE['id']);
}
else if ( isset($_POST['deleteBankAccount']) ){
    $dbConnectionObject->deleteFromUserDB('BankAccount',$_COOKIE['id']);
}
else if ( isset($_POST['deleteAvatar']) ){
    $dbConnectionObject->deleteFromUserDB('PhotoLink', $_COOKIE['id']);
    unlink($_SESSION['pageInfo']['photoLink']);
}
else if ( isset($_POST['changeAvatar']) ){
    include_once 'buyapic_functions.php';
    if ( !($_SESSION['error']['upload']=checkUploadPicture('newAvatar')) ) {
        unset($_SESSION['error']['upload']);
        $dt = date("Y-m-d_H-i-s");
        $uploadfile = 'uploads/avatars/' . $_COOKIE['id'] . '_avatar_' . $dt ;
        if (!move_uploaded_file($_FILES['newAvatar']['tmp_name'], $uploadfile)) {
            $_SESSION['error']['upload'] = 'Не удалось загрузить файл';
        } else {
            $dbConnectionObject->changeUserDB('PhotoLink', $_COOKIE['id'], $uploadfile);
            unlink($_SESSION['pageInfo']['photoLink']);
        }
    }    
}
else if ( isset($_POST['changeSelfInfo']) ){
    $dbConnectionObject->changeUserDB('SelfInfo', $_COOKIE['id'], trim($_POST['selfInfo']));
}
else if ( isset($_POST['changePassword']) ) {
    include_once 'buyapic_functions.php';
    $id_hash = $dbConnectionObject->getAuthorizationDataDB ($_SESSION['pageInfo']['email']);
    if ( check_password($id_hash['hash'], $_POST['oldPassword']) && 
                    ( $_POST['newPassword'] == $_POST['newPasswordConfirm'] ) )
    {
        $hash = makeHash($_POST['newPassword']);
        $dbConnectionObject->changeUserDB('hash', $_COOKIE['id'], $hash);
    } else {
        $_SESSION['error'] = [ 'block'=>'authorization', 
                'message'=>'Пароль не изменен' ];
    }
}

unset($_POST);
unset($_FILES);
$_SESSION['pageInfo'] = $dbConnectionObject->getUserInfoDB($_COOKIE['id']);
header('Location: buyapic_index.php?action=config_userdetails');
?>

