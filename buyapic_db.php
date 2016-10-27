<?php
//Файл функций для работы с базой данных========================================

class BuyAPicDataBaseConnection
{
    //Данные для подключения к БД
    private $dsn = 'mysql:dbname=buyapic;host=127.0.0.1';
    private $user = 'root';
    private $password = 'dreamJ0b';
    private $opt = array( PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                          PDO::ATTR_PERSISTENT         => true );
    
    //Подключение к БД
    private function connectDB()
    {
        try {
            $dbh = new PDO($this->dsn, $this->user, $this->password, $this->opt);
        } catch (PDOException $e) {
            echo 'Подключение к базе данных не удалось: ' . $e->getMessage();
        }
        return $dbh;
    }
    
//    //Делает выборку по заранее подготовленному запросу
//    private function selectDB( string $selectText, array $valuesArray )
//    {
//        $dbh = $this->connectDB();
//        $stmt = $dbh->prepare($selectText);
//        $stmt->execute($valuesArray);
//        if ( $row = $stmt->fetch(PDO::FETCH_LAZY) ) {
//            foreach ( $row as $key => $value ) {
//                if( isset($value) && $key != 'queryString' ) {
//                    $arr[$key]=$value;
//                }
//            }
//            $dbh = NULL;
//            return $arr;
//        }
//        //Если ничего не найдено
//        return NULL;
//    }
    
    //Делает выборку по заранее подготовленному запросу
    private function selectDB( string $selectText, array $valuesArray )
    {
        $dbh = $this->connectDB();
        $stmt = $dbh->prepare($selectText);
        $stmt->execute($valuesArray);
        if ( $arr = $stmt->fetchAll() ) {
            $dbh = NULL;
            return $arr;
        }
        //Если ничего не найдено
        return NULL;
    }
    
    //Вносит изменения в базу данных
    private function changeDB( string $selectText, array $valuesArray )
    {
        $dbh = $this->connectDB();
        try {
            $stmt = $dbh->prepare($selectText);
            $stmt->execute($valuesArray);
        } catch (PDOException $e) {
            echo 'Вставка в базу данных не удалась: ' . $e->getMessage();
        }
        $dbh = NULL;
        return TRUE;
    }
    
    //Подготавливает выражение для изменения значения поля таблицы User
    public function changeUserDB ($fieldName, $id, $value)
    {
        $selectText = 'UPDATE user SET '.$fieldName.' = :value '
                    . 'WHERE userid = :userid';
        $valuesArray = array( 'userid' => $id, 'value' => $value );
        $this->changeDB($selectText, $valuesArray);
        return TRUE;
    }
    
    //Подготавливает выражение для изменения значения поля таблицы Picture
    public function changePictureDB ($fieldName, $id, $value)
    {
        $selectText = 'UPDATE picture SET '.$fieldName.' = :value '
                    . 'WHERE pictureid = :pictureid';
        $valuesArray = array( 'pictureid' => $id, 'value' => $value );
        $this->changeDB($selectText, $valuesArray);
        return TRUE;
    }
    
    //Подготавливает выражение для удаления значения поля таблицы User
    public function deleteFromUserDB ($fieldName, $id)
    {
        $selectText = 'UPDATE user SET '.$fieldName.' = NULL '
                    . 'WHERE user.userid = :userid';
        $valuesArray = array( 'userid' => $id );
        $this->changeDB($selectText, $valuesArray);
        return TRUE;
    }

    //Возвращает id и hash пользователя по email
    public function getAuthorizationDataDB ( $email )
    {
        $selectText = 'SELECT userid, hash FROM user WHERE email = :email';
        $valuesArray['email'] = $email;
        if ( $arr = $this->selectDB ( $selectText, $valuesArray ) ) {
            return $arr[0];
        }
        //Если в базе такого email нет
        return NULL;
    }
    
    //Возвращает информацию о пользователе по его id
    public function getUserInfoDB ( $userid )
    {
        $selectText = 'SELECT name, email, photolink, selfinfo, webpage, '
                    . 'bankaccount, registered '
                    . 'FROM user WHERE userid = :userid';
        $valuesArray['userid'] = $userid;
        if ( $arr = $this->selectDB ( $selectText, $valuesArray ) ) 
        {
            $info['userName'] = $arr[0]['name'];
            $info['email'] = $arr[0]['email'];
            $info['photoLink'] = isset($arr[0]['photolink']) ? 
                                  $arr[0]['photolink'] : "/uploads/avatars/1.png";
            $info['selfInfo'] = isset($arr[0]['selfinfo']) ? $arr[0]['selfinfo'] : "-";
            $info['webPage'] = isset($arr[0]['webpage']) ?
                                                        $arr[0]['webpage'] : "-";
            $info['bankAccount'] = isset($arr[0]['bankaccount']) ?
                                                    $arr[0]['bankaccount'] : "-";
            $info['registered'] = isset($arr[0]['registered']) ?
                         date("Y-m-d H:i:s", intval($arr[0]['registered'])) : "-";
            return $info;
        }     
        //Если в базе такого id нет
        return NULL;
    }
    
    //Возвращает данные картинок пользователя по его id
    public function getUserPicturesDB ( $userid )
    {
        $selectText = 'SELECT pictureid, previewlink, publicationdate, '
                    . 'description, price, picturestatus '
                    . 'FROM picture WHERE userid = :userid';
        $valuesArray['userid'] = $userid;
        
        $dbh = $this->connectDB();
        $stmt = $dbh->prepare($selectText);
        $stmt->execute($valuesArray);
        
        if ( $arr = $stmt->fetchAll(PDO::FETCH_UNIQUE) ) {
            $dbh = NULL;
            return $arr;
        }
        //Если ничего не найдено
        return NULL;
    }
    
    //Возвращает данные картинок пользователя по его id
    public function getPictureInfoDB ( $pictureid )
    {
        $selectText = 'SELECT pictureid, previewlink, hdlink, publicationdate, '
                    . 'description, price, picturestatus '
                    . 'FROM picture WHERE pictureid = :pictureid';
        $valuesArray['pictureid'] = $pictureid;
        
        if ( $arr = $this->selectDB ( $selectText, $valuesArray ) ) 
        {
            $info['pictureId'] = $arr[0]['pictureid'];
            $info['previewLink'] = $arr[0]['previewlink'];
            $info['HDLink'] = $arr[0]['hdlink'];
            $info['publicationDate'] = $dt = date("Y-m-d_H-i-s", $arr[0]['publicationdate']);
            $info['description'] = $arr[0]['description'];
            $info['price'] = $arr[0]['price'];
            $info['pictureStatus'] = $arr[0]['picturestatus'];
            
            return $info;
        }
        //Если ничего не найдено
        return NULL;
    }
    
    //Проверяет, не использован ли уже email
    public function isEmailAvailableDB ( $email )
    {
        $selectText = 'SELECT userid, hash FROM user WHERE email = :email';
        $valuesArray['email'] = $email;
        if ( !$this->selectDB ( $selectText, $valuesArray ) ) {
            return TRUE;
        }
        //Если в базе такого email нет
        return FALSE;
    }
    
    //Регистрирует нового художника
    public function addNewArtistDB ( $name, $email, $hash, $dts)
    {
        $selectText = "INSERT INTO user "
                . "(accesslevel, name, email, hash, artiststatus, registered) "
                . "VALUES "
                . "('Artist', :name, :email, :hash, 'Awaiting', :registered)";
            $valuesArray = array ( 'name' => $name, 
                                   'email' => $email, 
                                   'hash' => $hash,
                                   'registered' => $dts );
        $this->changeDB ( $selectText, $valuesArray );
    }
    
    //Регистрирует новую картину
    public function addNewPictureDB ( $userid, $previewLink, $hdLink, $date, 
                                      $description, $price )
    {
        $selectText = "INSERT INTO picture "
                    . "(userid, previewlink, hdlink, publicationdate, "
                    . "description, price, picturestatus) "
                    . "VALUES "
                    . "(:userid, :previewlink, :hdlink, :publicationdate, "
                    . ":description, :price, 'Active')";
            $valuesArray = array ( 'userid' => $userid, 
                                   'previewlink' => $previewLink, 
                                   'hdlink' => $hdLink, 
                                   'publicationdate' => $date, 
                                   'description' => $description,
                                   'price' => $price );
        $this->changeDB ( $selectText, $valuesArray );
    }
}
?>

