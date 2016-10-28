<?php
//Логика отображения страницы (только перенаправляет на другие страницы)========

include_once 'buyapic_header.php';

if( !isset($_GET['action']) ) {
    header('Location: buyapic_index.php?action=main&page=1');
}

else {
    switch ($_GET['action'])
    {
        case 'main':
            if (!isset($_SESSION['authorized']) ) {
                $_SESSION['userInfo'] = [ 'userId'=>'anonim' ];
            } else {
                $_SESSION['userInfo'] = $dbConnectionObject->getUserInfoDB($_COOKIE['id']);
            }
            
            $picturesAmount = $dbConnectionObject->countShownPicturesDB (); //всего картинок
            $pageNumber = $_GET['page'];                                //номер страницы
            $picturesOnPage = 5;                           //картинок выводить на странице
            
            $pagesAmount = intdiv ( $picturesAmount, $picturesOnPage ) + 1; //количество страниц
            $lastPageAmount = $picturesAmount % $picturesOnPage;             //картинок на последней странице

            //Если последняя страница
            if( $pageNumber == $pagesAmount ) {
                $_SESSION['pictureList'] = $dbConnectionObject->
                    getShownPicturesDB( $picturesOnPage*($pageNumber-1), $lastPageAmount );
            } else {
                $_SESSION['pictureList'] = $dbConnectionObject->
                    getShownPicturesDB( $picturesOnPage*($pageNumber-1), $picturesOnPage );
            }
            $_SESSION['pagesAmount'] = $pagesAmount;
            
            unset($_GET['action']);
            include 'buyapic_main.html';
            break;
        case 'authorization':
            if (!isset($_SESSION['authorized']) ) {
                $_SESSION['userInfo'] = [ 'userId'=>'anonim' ];
                unset($_GET['action']);
                include 'buyapic_authorization.html';
            } else {
                unset($_GET['action']);
                include 'buyapic_main.html';
            }
            break;
        case 'registration':
            if (!isset($_SESSION['authorized']) ) {
                $_SESSION['userInfo'] = [ 'userId'=>'anonim' ];
                unset($_GET['action']);
                include 'buyapic_registration.html';
            } else {
                unset($_GET['action']);
                include 'buyapic_main.html';
            }
            break;
        case 'show_userdetails':
            if (!isset($_SESSION['authorized']) ) {
                $_SESSION['userInfo'] = [ 'userId'=>'anonim' ];
            }
            $_SESSION['pageInfo'] = $dbConnectionObject->getUserInfoDB($_GET['id']);
            unset($_GET['action']);
            include 'buyapic_userdetails.html';
            break;
        case 'config_userdetails':
            if (!isset($_SESSION['authorized']) ) {
                $_SESSION['userInfo'] = [ 'userId'=>'anonim' ];
                unset($_GET['action']);
                include 'buyapic_main.html';
            } else {
                unset($_GET['action']);
                include 'buyapic_config_userdetails.html';
            }
            break;
        case 'add_picture':
            if (!isset($_SESSION['authorized']) ) {
                $_SESSION['userInfo'] = [ 'userId'=>'anonim' ];
                unset($_GET['action']);
                include 'buyapic_main.html';
            } else {
                unset($_GET['action']);
                if ( isset($_SESSION['pictureInfo']['pictureId']) ) {
                    unset($_SESSION['pictureInfo']);
                }
                $_SESSION['pictureInfo']['show'] = 'add';
                include 'buyapic_config_picture.html';
            }
            break;
        case 'show_my_pictures':
            if (!isset($_SESSION['authorized']) ) {
                $_SESSION['userInfo'] = [ 'userId'=>'anonim' ];
                unset($_GET['action']);
                include 'buyapic_main.html';
            } else {
                $_SESSION['pictureList'] = $dbConnectionObject->getUserPicturesDB ($_COOKIE['id']);
                unset($_GET['action']);
                include 'buyapic_my_pictures.html';
            }
            break;
        case 'view_picture':
            if (!isset($_SESSION['authorized']) ) {
                $_SESSION['userInfo'] = [ 'userId'=>'anonim' ];
            }
            $_SESSION['pictureInfo'] = $dbConnectionObject->getPictureInfoDB ($_GET['id']);
            unset($_GET['action']);
            include 'buyapic_one_picture.html';
            break;
        case 'config_picture':
            if (!isset($_SESSION['authorized']) ) {
                $_SESSION['userInfo'] = [ 'userId'=>'anonim' ];
                unset($_GET['action']);
                include 'buyapic_main.html';
            } else {
                if ( isset($_GET['id']) ) {
                    $_SESSION['pictureInfo'] = $dbConnectionObject
                            ->getPictureInfoDB ($_GET['id']);
                } else {
                    $_SESSION['pictureInfo'] = $dbConnectionObject
                            ->getPictureInfoDB ($_SESSION['pictureInfo']['pictureId']);
                }
                $_SESSION['pictureInfo']['oldPreviewLink'] = $_SESSION['pictureInfo']['previewLink'];
                $_SESSION['pictureInfo']['oldHDLink'] = $_SESSION['pictureInfo']['hdLink'];
                unset($_GET['action']);
                $_SESSION['pictureInfo']['show'] = 'change';
                include 'buyapic_config_picture.html';
            }
            break;
    }
}
?>