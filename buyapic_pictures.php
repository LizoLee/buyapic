<?php
//Логика работы с картинами художника===========================================

include_once 'buyapic_header.php';
include_once 'buyapic_functions.php';
$tmpFolder = 'uploads/tmp/' . $_COOKIE['id'];
if ( !is_dir($tmpFolder) ) {
    mkdir ($tmpFolder);
}
if ( isset($_POST['notForFree']) ) {
    unset($_SESSION['error']['checkPrice']);
} else if ( isset($_POST['choosePreview']) ) {
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
} else if ( isset($_POST['chooseHD']) ) {
    if ( !($_SESSION['error']['uploadHD']=checkUploadPicture('newHD')) ) {
        unset($_SESSION['error']['uploadHD']);
        $dt = date("Y-m-d_H-i-s");
        $uploadTMPfile = $tmpFolder . '/picture_' . $dt ;
        if (!move_uploaded_file($_FILES['newHD']['tmp_name'], $uploadTMPfile)) {
            $_SESSION['error']['uploadHD'] = 'Не удалось загрузить высокое разрешение';
        } else {
            $_SESSION['pictureInfo']['HDLink'] = $uploadTMPfile;
        }
        unset($_FILES);
    }   
} else if ( isset($_POST['changePicture']) ) {
    
    if ( !isset ($_SESSION['pictureInfo']['previewLink']) ) {
        $_SESSION['error']['upload'] = 'Выберите превью';
    } else if( !isset ($_SESSION['pictureInfo']['HDLink']) ) {
        $_SESSION['error']['upload'] = 'Выберите высокое разрешение';        
    } else if ( $_POST['price'] == 0) {
        if( !isset($_SESSION['error']['checkPrice'])) {
            $_SESSION['error']['checkPrice'] = 'Вы указали цену равной нулю.<br>'
                . 'Хотите ли Вы, чтобы Ваша работа распространялась бесплатно?';
            $_SESSION['pictureInfo']['price'] = 0;
            $_SESSION['pictureInfo']['description'] = $_POST['description'] ;
        } else {
            $_SESSION['pictureInfo']['price'] = round((1.0 * $_POST['price']), 2);
            $_SESSION['pictureInfo']['description'] = $_POST['description'];
        }
    } else {
        unset($_SESSION['error']['checkPrice']);
        
        $dt = date_create();
        $dts = date_timestamp_get($dt);
        $dt = date("Y-m-d_H-i-s", $dts);
        
        $previewFolder = 'uploads/preview/' . $_COOKIE['id'];
        if ( !is_dir($previewFolder) ) {
            mkdir ($previewFolder);
        }
        $previewLink = $previewFolder . '/_' . $dt ;
        $hdFolder = 'uploads/HD/' . $_SESSION['pageInfo']['email'];
        if ( !is_dir($hdFolder) ) {
            mkdir ($hdFolder);
        }
        $hdLink = $hdFolder . '/_' . $dts ;
        
        if (!rename($_SESSION['pictureInfo']['previewLink'], $previewLink)) {
            $_SESSION['error']['upload'] = 'Не удалось загрузить превью';
        } else if (!rename($_SESSION['pictureInfo']['HDLink'], $hdLink)) {
            $_SESSION['error']['upload'] = 'Не удалось загрузить высокое разрешение';
        } else {
            $dbConnectionObject->addNewPictureDB ( $_COOKIE['id'], 
                                        $previewLink, 
                                        $hdLink, 
                                        $dts, 
                                        $_SESSION['pictureInfo']['description'], 
                                        $_SESSION['pictureInfo']['price'] );
                
            unset ($_SESSION['pictureInfo']);
            deleteFolder($tmpFolder);
            header('Location: buyapic_index.php?action=add_picture');
        }
    }
}

header('Location: buyapic_index.php?action=add_picture');
?>
