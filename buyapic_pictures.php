<?php
//Логика работы с картинrами====================================================

include_once 'buyapic_header.php';
include_once 'buyapic_functions.php';

if ( isset($_POST['notForFree']) ) {
    unset($_SESSION['error']['checkPrice']);
    if ( $_SESSION['pictureInfo']['show'] == 'add' ) {
        header('Location: buyapic_index.php?action=add_picture');
    } else if ( $_SESSION['pictureInfo']['show'] == 'change' ) {
        header('Location: buyapic_index.php?action=config_picture');
    }
} 
else if ( isset($_POST['choosePreview']) ) {

    $tmpFolder = 'uploads/tmp/' . $_COOKIE['id'];
    if ( !is_dir($tmpFolder) ) {
        mkdir ($tmpFolder);
    }
    
    if ( !($_SESSION['error']['uploadPreview']=checkUploadPicture('newPreview')) ) {
        unset($_SESSION['error']['uploadPreview']);
        $dt = date("Y-m-d_H-i-s");
        $uploadTMPfile = $tmpFolder . '/preview_' . $dt ;
        if (!move_uploaded_file($_FILES['newPreview']['tmp_name'], $uploadTMPfile)) {
            $_SESSION['error']['uploadPreview'] = 'Не удалось загрузить превью';
        } else {
            $_SESSION['pictureInfo']['previewLink'] = $uploadTMPfile;
        }
        unset($_FILES);
    }
    header('Location: buyapic_index.php?action=add_picture');
}
else if ( isset($_POST['chooseHD']) ) {
    
    $tmpFolder = 'uploads/tmp/' . $_COOKIE['id'];
    if ( !is_dir($tmpFolder) ) {
        mkdir ($tmpFolder);
    }
    
    if ( !($_SESSION['error']['uploadHD']=checkUploadPicture('newHD')) ) {
        unset($_SESSION['error']['uploadHD']);
        $dt = date("Y-m-d_H-i-s");
        $uploadTMPfile = $tmpFolder . '/picture_' . $dt ;
        if (!move_uploaded_file($_FILES['newHD']['tmp_name'], $uploadTMPfile)) {
            $_SESSION['error']['uploadHD'] = 'Не удалось загрузить высокое разрешение';
        } else {
            $_SESSION['pictureInfo']['hdLink'] = $uploadTMPfile;
        }
        unset($_FILES);
    }
    header('Location: buyapic_index.php?action=add_picture');
}
else if ( isset($_POST['addPicture']) ) {
    
    if ( !isset ($_SESSION['pictureInfo']['previewLink']) ) {
        $_SESSION['error']['upload'] = 'Выберите превью';
        $_SESSION['pictureInfo']['price'] = round((1.0 * $_POST['price']), 2);
        $_SESSION['pictureInfo']['description'] = $_POST['description'];
        header('Location: buyapic_index.php?action=add_picture');
    } else if( !isset ($_SESSION['pictureInfo']['hdLink']) ) {
        $_SESSION['error']['upload'] = 'Выберите высокое разрешение';
        $_SESSION['pictureInfo']['price'] = round((1.0 * $_POST['price']), 2);
        $_SESSION['pictureInfo']['description'] = $_POST['description'];
        header('Location: buyapic_index.php?action=add_picture');
    } else if ( isset($_POST['price']) && $_POST['price'] == 0) {
        if( !isset($_SESSION['error']['checkPrice'])) {
            $_SESSION['error']['checkPrice'] = 'Вы указали цену равной нулю.<br>'
                . 'Хотите ли Вы, чтобы Ваша работа распространялась бесплатно?';
            $_SESSION['pictureInfo']['price'] = 0;
            $_SESSION['pictureInfo']['description'] = $_POST['description'] ;
        }
        header('Location: buyapic_index.php?action=add_picture');
    } else {
        unset($_SESSION['error']['checkPrice']);
        
        if ( isset($_POST['price']) ) {
            $_SESSION['pictureInfo']['price'] = round((1.0 * $_POST['price']), 2);
            $_SESSION['pictureInfo']['description'] = $_POST['description'];
        }
        
        $dt = date_create();
        $dts = date_timestamp_get($dt);
        $dt = date("Y-m-d_H-i-s", $dts);
        
        $previewFolder = 'uploads/preview/' . $_COOKIE['id'];
        if ( !is_dir($previewFolder) ) {
            mkdir ($previewFolder);
        }
        $previewLink = $previewFolder . '/_' . $dt ;
        
        $hdFolder = 'uploads/HD/' . $_SESSION['userInfo']['email'];
        if ( !is_dir($hdFolder) ) {
            mkdir ($hdFolder);
        }
        $hdLink = $hdFolder . '/_' . $dts ;
        
        if (!rename($_SESSION['pictureInfo']['previewLink'], $previewLink)) {
            $_SESSION['error']['upload'] = 'Не удалось загрузить превью';
        } else if (!rename($_SESSION['pictureInfo']['hdLink'], $hdLink)) {
            $_SESSION['error']['upload'] = 'Не удалось загрузить высокое разрешение';
        } else {
            $dbConnectionObject->addNewPictureDB ( $_COOKIE['id'], 
                                        $previewLink, 
                                        $hdLink, 
                                        $dts, 
                                        $_SESSION['pictureInfo']['description'], 
                                        $_SESSION['pictureInfo']['price'] );

            unset ($_SESSION['pictureInfo']);
            $tmpFolder = 'uploads/tmp/' . $_COOKIE['id'];
            deleteFolder($tmpFolder);
            header('Location: buyapic_index.php?action=show_my_pictures');
        }
    }
}
else if ( isset($_POST['setPictureStatus']) )
{
    $dbConnectionObject->changePictureDB ('picturestatus', 
            $_POST['pictureId'], $_POST['pictureStatus']);
    header('Location: buyapic_index.php?action=show_my_pictures');
}
else if ( isset($_POST['changePreview']) )
{
    
    if ( !($_SESSION['error']['uploadPreview']=checkUploadPicture('newPreview')) ) {
        unset($_SESSION['error']['uploadPreview']);
        $dt = date_create();
        $dts = date_timestamp_get($dt);
        $dt = date("Y-m-d_H-i-s", $dts);
        
        $previewFolder = 'uploads/preview/' . $_COOKIE['id'];
        $previewLink = $previewFolder . '/_' . $dt ;
        
        if (!move_uploaded_file($_FILES['newPreview']['tmp_name'], $previewLink)) {
            $_SESSION['error']['uploadPreview'] = 'Не удалось загрузить превью';
        } else {
            unlink($_SESSION['pictureInfo']['previewLink']);
            $_SESSION['pictureInfo']['previewLink'] = $previewLink;
            $dbConnectionObject->changePictureDB('previewlink', 
                                    $_SESSION['pictureInfo']['pictureId'], 
                                    $_SESSION['pictureInfo']['previewLink']);
        }
        unset($_FILES);
    }
    header('Location: buyapic_index.php?action=config_picture');
}
else if ( isset($_POST['changeHD']) )
{
    
    if ( !($_SESSION['error']['uploadHD']=checkUploadPicture('newHD')) ) {
        unset($_SESSION['error']['uploadHD']);
        $dt = date_create();
        $dts = date_timestamp_get($dt);
        
        $hdFolder = 'uploads/HD/' . $_SESSION['userInfo']['email'];
        $hdLink = $hdFolder . '/_' . $dts ;
        
        if (!move_uploaded_file($_FILES['newHD']['tmp_name'], $hdLink)) {
            $_SESSION['error']['uploadHD'] = 'Не удалось загрузить высокое разрешение';
        } else {
            unlink($_SESSION['pictureInfo']['hdLink']);
            $_SESSION['pictureInfo']['hdLink'] = $hdLink;
            $dbConnectionObject->changePictureDB('hdlink', 
                                    $_SESSION['pictureInfo']['pictureId'], 
                                    $_SESSION['pictureInfo']['hdLink']);
        }
        unset($_FILES);
    }
    header('Location: buyapic_index.php?action=config_picture');
}
else if ( isset($_POST['changePrice']) )
{
    if ( $_POST['price'] == 0) {
        if( !isset($_SESSION['error']['checkPrice'])) {
            $_SESSION['error']['checkPrice'] = 'Вы указали цену равной нулю.<br>'
                . 'Хотите ли Вы, чтобы Ваша работа распространялась бесплатно?';
            $_SESSION['pictureInfo']['price'] = 0;
        }
    } else {
        unset($_SESSION['error']['checkPrice']);
        
        if ( isset($_POST['price']) ) {
            $_SESSION['pictureInfo']['price'] = round((1.0 * $_POST['price']), 2);
        }
        $dbConnectionObject->changePictureDB('price', 
                                    $_SESSION['pictureInfo']['pictureId'], 
                                    $_SESSION['pictureInfo']['price']);

        header('Location: buyapic_index.php?action=config_picture');
    }
}
else if ( isset($_POST['changeDescription']) )
{
        $_SESSION['pictureInfo']['description'] = $_POST['description'];
        $dbConnectionObject->changePictureDB('description', 
                                    $_SESSION['pictureInfo']['pictureId'], 
                                    $_SESSION['pictureInfo']['description']);

        header('Location: buyapic_index.php?action=config_picture');
}
else if ( isset($_POST['request']) )
{
    //Проверяем корректность email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
    {
        $_SESSION['error'] = [ 'block'=>'request', 
                'message'=>'Введен некорректный email' ];
    }
    else if( $_SESSION['userInfo']['userId'] == 'anonim') {
        //Исключение использования чужого email неавторизованным пользователем
        if ( $dbConnectionObject->isEmailAvailableDB($_POST['email']) == 'Artist'
              || $dbConnectionObject->isEmailAvailableDB($_POST['email']) == 'Moderator' ) 
            {
            $_SESSION['error'] = [ 'block'=>'request', 
                        'message'=>'Указанный email зарегистрирован, пройдите авторизацию' ];
        }
        else if ( $dbConnectionObject->isEmailAvailableDB($_POST['email']) == 'Buyer') 
        {
            $buyer = $dbConnectionObject->getAuthorizationDataDB ($_POST['email']);
            //Исключение повторения заявки
            if ( $dbConnectionObject->checkRequestDB ($buyer['userId'], 
                                        $_SESSION['pictureInfo']['pictureId']) )
            {
                $_SESSION['error'] = [ 'block'=>'request', 
                            'message'=>'Вы уже подали заявку на покупку этой работы' ];
            }
            else {
                $dt = date_create();
                $dts = date_timestamp_get($dt);
                $dbConnectionObject->addNewRequestDB ( $buyer['userId'], 
                                $_SESSION['pictureInfo']['pictureId'], $dts );
            }
        }
        else if ( $dbConnectionObject->isEmailAvailableDB($_POST['email']) == NULL) 
        {
            $dt = date_create();
            $dts = date_timestamp_get($dt);
            $dbConnectionObject->addNewBuyerDB ($_POST['email'], $dts);
            $buyer = $dbConnectionObject->getAuthorizationDataDB ($_POST['email']);
            $dbConnectionObject->addNewRequestDB ( $buyer['userId'], 
                                    $_SESSION['pictureInfo']['pictureId'], $dts );
        }
    }
    else if ( $_SESSION['userInfo']['userId'] != 'anonim' ) {
        //Исключение использования чужого email авторизованным пользователем
        if( $_SESSION['userInfo']['email'] != $_POST['email'] ) 
        {
            $_SESSION['error'] = [ 'block'=>'request', 
                        'message'=>'Вы прошли авторизацию под другим email' ];
        }
        else
        {
            //Исключение повторения заявки
            if ( $dbConnectionObject->checkRequestDB ($_SESSION['userInfo']['userId'], 
                                        $_SESSION['pictureInfo']['pictureId']) )
            {
                $_SESSION['error'] = [ 'block'=>'request', 
                            'message'=>'Вы уже подали заявку на покупку этой работы' ];
            }
            else {
                $dt = date_create();
                $dts = date_timestamp_get($dt);
                $dbConnectionObject->addNewRequestDB ( $_SESSION['userInfo']['userId'], 
                                $_SESSION['pictureInfo']['pictureId'], $dts );
            }
        }
    }
    header('Location: '.$_SERVER["HTTP_REFERER"]);
}
?>