<?php
//Логика приложения. Регистрация================================================

include_once 'buyapic_header.php';

//Имя и пароль могут быть любыми

//Исключение невалидного email
if ( !filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ) {
    $_SESSION['error'] = [ 'block'=>'registration', 
                'message'=>'Введен некорректный email' ];
    unset($_POST);
    header('Location: buyapic_index.php?action=registration');
}
//Исключение уже используемого email
else if( $dbConnectionObject->isEmailAvailableDB($_POST['email']) == 'Artist'
        || $dbConnectionObject->isEmailAvailableDB($_POST['email']) == 'Moderator') {
    $_SESSION['error'] = [ 'block'=>'registration', 
                'message'=>'Указанный email уже используется' ];
    unset($_POST);
    header('Location: buyapic_index.php?action=registration');
}
//Исключение ошибки при подтверждении пароля
else if ( !( $_POST['newPassword'] == $_POST['newPasswordConfirm'] ) ) {
    $_SESSION['error'] = [ 'block'=>'registration', 
                'message'=>'Пароль и подтверждение не совпадают' ];
    unset($_POST);
    header('Location: buyapic_index.php?action=registration');
}
//Создание нового пользователя
else {
    include_once 'buyapic_functions.php';
    $hash = makeHash($_POST['newPassword']);
    $dt = date_create();
    $dts = date_timestamp_get($dt);
    $dbConnectionObject ->addNewArtistDB( $_POST['newUsername'], $_POST['email'], $hash, $dts );
    if( $id_hash = $dbConnectionObject->getAuthorizationDataDB ($_POST['email']) ) {
        setcookie("id", $id_hash['userId']);
        $_SESSION['authorized'] = TRUE;
        unset($_POST);
        header('Location: buyapic_index.php');
    }
    else {
        $_SESSION['authorized'] = FALSE;
        $_SESSION['error'] = [ 'block'=>'registration', 
                'message'=>'Вставка в базу данных не удалась' ];
        unset($_POST);
        header('Location: buyapic_index.php?action=registration');
    }
}    
//var_dump($_POST);
//include 'buyapic_registration.html';
?>