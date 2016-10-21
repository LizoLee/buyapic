<?php
//Логика приложения. Регистрация================================================

error_reporting(-1);
if ( !session_id() ) { session_start(); }

//var_dump($_POST);

include_once 'buyapic_db.php';
$newUser = new BuyAPic();

//Имя и пароль могут быть любыми

//Исключение невалидного email
if ( !filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ) {
    $_SESSION['error'] = [ 'block'=>'registration', 
                'message'=>'Введен некорректный email' ];
//    foreach ( $_POST as $key => $value )
//    {
//        $_SESSION['error']['autocomplete'][$key]=$value;
//    }
    unset($_POST);
    header('Location: buyapic_index.php');
}
//Исключение уже используемого email
else if( !( $newUser->isEmailAvailableDB($_POST['email']) ) ) {
    $_SESSION['error'] = [ 'block'=>'registration', 
                'message'=>'Указанный email уже используется' ];
    unset($_POST);
    header('Location: buyapic_index.php');
}
//Исключение ошибки при подтверждении пароля
else if ( !( $_POST['newPassword'] == $_POST['newPasswordConfirm'] ) ) {
    $_SESSION['error'] = [ 'block'=>'registration', 
                'message'=>'Пароль и подтверждение не совпадают' ];
    unset($_POST);
    header('Location: buyapic_index.php');
}
//Создание нового пользователя
else {
    include_once 'buyapic_functions.php';
    $hash = makeHash($_POST['newPassword']);
    $dt = date_create();
    $dts = date_timestamp_get($dt);
    $newUser ->addNewUserDB( $_POST['newUsername'], $_POST['email'], $hash, $dts );
    $id_hash = $newUser->getAuthorizationDataDB ($_POST['email']);
    setcookie("id", $id_hash['userid']);
    unset($_POST);
    header('Location: buyapic_index.php');
}    
//var_dump($_POST);
//include 'buyapic_registration.html';
?>