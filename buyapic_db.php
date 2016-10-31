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

    //Проверяет, не использован ли уже email
    public function isEmailAvailableDB ( $email )
    {
        $selectText = 'SELECT accessLevel FROM user WHERE email = :email';
        $valuesArray['email'] = $email;
        if ( $arr = $this->selectDB ( $selectText, $valuesArray ) ) {
            return $arr[0]['accessLevel'];
        }
        return NULL;
    }
    
    //Возвращает id и hash пользователя по email
    public function getAuthorizationDataDB ( $email )
    {
        $selectText = 'SELECT userId, hash FROM user WHERE email = :email';
        $valuesArray['email'] = $email;
        if ( $arr = $this->selectDB ( $selectText, $valuesArray ) ) {
            return $arr[0];
        }
        //Если в базе такого email нет
        return NULL;
    }
    
    //Возвращает информацию о пользователе по его id
    public function getUserInfoDB ( $userId )
    {
        $selectText = 'SELECT userId, accessLevel, name AS userName, email, '
                    . 'photoLink, selfInfo, webPage, bankAccount, registered, '
                    . 'artistStatus AS status '
                    . 'FROM user WHERE userId = :userId';
        $valuesArray['userId'] = $userId;
        if ( $arr = $this->selectDB ( $selectText, $valuesArray ) ) 
        {
            $arr[0]['photoLink'] = isset($arr[0]['photoLink']) ?
                                                    $arr[0]['photoLink'] : "uploads/avatars/1.png";
            $arr[0]['selfInfo'] = isset($arr[0]['selfInfo']) ?
                                                    $arr[0]['selfInfo'] : "-";
            $arr[0]['webPage'] = isset($arr[0]['webPage']) ?
                                                    $arr[0]['webPage'] : "-";
            $arr[0]['bankAccount'] = isset($arr[0]['bankAccount']) ?
                                                    $arr[0]['bankAccount'] : "-";
            $arr[0]['registered'] = date("Y-m-d H:i:s", intval($arr[0]['registered']));
            return $arr[0];
        }     
        //Если в базе такого id нет
        return NULL;
    }
    
    //Возвращает данные картинок пользователя по его id
    public function getUserPicturesDB ( $userId )
    {
        $selectText = 'SELECT pictureId, previewLink, publicationDate, '
                    . 'description, price, pictureStatus '
                    . 'FROM picture WHERE userId = :userId '
                    . 'ORDER BY publicationdate DESC';
        $valuesArray['userId'] = $userId;
        
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
    public function getPictureInfoDB ( $pictureId )
    {
        $selectText = 'SELECT u.userId, u.name AS userName, '
                    . 'p.pictureId, p.previewLink, p.hdLink, p.publicationDate, '
                    . 'p.description, p.price, p.pictureStatus '
                    . 'FROM picture p INNER JOIN user u ON u.userId = p.userId '
                    . 'WHERE p.pictureId=:pictureId';
        $valuesArray['pictureId'] = $pictureId;
        
        if ( $arr = $this->selectDB ( $selectText, $valuesArray ) ) 
        {
            return $arr[0];
        }
        //Если ничего не найдено
        return NULL;
    }
    
    //Возвращает количество отображаемых картинок
    public function countShownPicturesDB ()
    {
        $dbh = $this->connectDB();
        $stmt = $dbh->query( 'SELECT count(*) FROM picture p INNER JOIN user u '
                           . 'ON u.UserID=p.UserID AND u.ArtistStatus!="Blocked" '
                           . 'WHERE p.picturestatus = "Active" '
                           . 'AND u.ArtistStatus = "Active"' )->fetch();        
        return (int) $stmt['count(*)'];
    }
    
    //Возвращает количество зарегистрированных художников
    public function countArtistsDB ()
    {
        $dbh = $this->connectDB();
        $stmt = $dbh->query( 'SELECT count(*) FROM user '
                           . 'WHERE accesslevel = "Artist"' )->fetch();        
        return (int) $stmt['count(*)'];
    }
    
    //Возвращает количество картин
    public function countPicturesDB ()
    {
        $dbh = $this->connectDB();
        $stmt = $dbh->query( 'SELECT count(*) FROM picture' )->fetch();        
        return (int) $stmt['count(*)'];
    }
    
    //Возвращает количество заявок
    public function countRequestsDB ()
    {
        $dbh = $this->connectDB();
        $stmt = $dbh->query( 'SELECT count(*) FROM buyingrequest' )->fetch();        
        return (int) $stmt['count(*)'];
    }

    //Возвращает все отображаемые на главной странице картины
    public function getShownPicturesDB ( $from, $number )
    {
        $dbh = $this->connectDB();
        $selectText = 'SELECT p.pictureId, p.previewLink, p.hdLink, '
                    . 'p.description, p.price FROM picture p INNER JOIN user u '
                    . 'ON u.userId=p.userId AND u.artistStatus="Active" '
                    . 'WHERE pictureStatus = "Active" '
                    . 'ORDER BY publicationDate DESC LIMIT '.$from.', '.$number;
        $stmt = $dbh->prepare($selectText);
        $stmt->execute();
        
        if ( $arr = $stmt->fetchAll(PDO::FETCH_UNIQUE) ) {
            $dbh = NULL;
            return $arr;
        }
        //Если ничего не найдено
        return NULL;
    }
    
    //Возвращает список зарегистрированных художников
    public function getArtistsDB ( $userid )
    {
        $selectText = 'SELECT userid, name, email, photolink, '
                    . 'selfinfo, webpage, bankaccount, registered, artiststatus '
                    . 'FROM user WHERE accesslevel = "Artist"';
        
        $dbh = $this->connectDB();
        if ( $arr = $dbh->query( $selectText )->fetchAll(PDO::FETCH_UNIQUE) ) {
            foreach ($arr as $key => $value) {
                $info[$key]['userName'] = $value['name'];
                $info[$key]['email'] = $value['email'];
                $info[$key]['status'] = $value['artiststatus'];
                $info[$key]['photoLink'] = isset($value['photolink']) ? 
                                 $value['photolink'] : "/uploads/avatars/1.png";
                $info[$key]['selfInfo'] = isset($value['selfinfo']) ? 
                                                       $value['selfinfo'] : "-";
                $info[$key]['webPage'] = isset($value['webpage']) ?
                                                        $value['webpage'] : "-";
                $info[$key]['bankAccount'] = isset($value['bankaccount']) ?
                                                    $value['bankaccount'] : "-";
                $info[$key]['registered'] = isset($value['registered']) ? 
                                                     $value['registered'] : "-";
            }
            return $info;
        }     
        //Если в базе такого id нет
        return NULL;
    }
    
    //Возвращает все картины
    public function getAllPicturesDB ( $from, $number )
    {
        $dbh = $this->connectDB();
        $selectText = 'SELECT p.pictureid, p.previewlink, '
                    . 'p.publicationdate, p.description, p.price, '
                    . 'p.picturestatus, u.userid, u.name, u.artiststatus '
                    . 'FROM picture p INNER JOIN user u ON u.UserID = p.UserID '
                    . 'ORDER BY publicationdate DESC LIMIT '.$from.', '.$number;
        $stmt = $dbh->prepare($selectText);
        $stmt->execute();
        
        if ( $arr = $stmt->fetchAll(PDO::FETCH_UNIQUE) ) {
            foreach ($arr as $key => $value) {
                $info[$key]['previewLink'] = $value['previewlink'];
                $info[$key]['publicationDate'] = $value['publicationdate'];
                $info[$key]['description'] = $value['description'];
                $info[$key]['price'] = $value['price'];
                $info[$key]['pictureStatus'] = $value['picturestatus'];
                $info[$key]['userId'] = $value['userid'];
                $info[$key]['userName'] = $value['name'];
                $info[$key]['userStatus'] = $value['artiststatus'];
            }
            $dbh = NULL;
            return $info;
        }
        //Если ничего не найдено
        return NULL;
    }
    
    //Возвращает все заявки
    public function getRequestsDB ( $from, $number )
    {
        $dbh = $this->connectDB();
        $selectText = 'SELECT b.buyingRequestId, b.userID AS buyerId, '
                    . 'ub.email AS buyerEmail, ub.accessLevel AS buyerAccessLevel, '
                    . 'b.pictureId, p.previewLink, p.price, '
                    . 'p.userId AS artistId, ua.name AS artistName, '
                    . 'b.requestDate, b.buyingStatus '
                    . 'FROM buyingrequest b, user ub, picture p, user ua '
                    . 'WHERE p.pictureID = b.pictureID AND ub.userId = b.userId '
                    . 'AND ua.userId = p.userId ORDER BY b.buyingRequestId DESC '
                    . 'LIMIT '.$from.', '.$number;
        $stmt = $dbh->prepare($selectText);
        $stmt->execute();
        
        if ( $arr = $stmt->fetchAll(PDO::FETCH_UNIQUE) ) {
            $dbh = NULL;
            return $arr;
        }
        //Если ничего не найдено
        return NULL;
    }

    //Регистрирует нового художника
    public function addNewArtistDB ( $name, $email, $hash, $dts)
    {
        $user = $this->isEmailAvailableDB($email);
        if ( $user == NULL ) {
            $selectText = "INSERT INTO user "
                        . "(accesslevel, name, email, hash, artiststatus, registered) "
                        . "VALUES "
                        . "('Artist', :name, :email, :hash, 'Awaiting', :registered)";
            $valuesArray = array ( 'name' => $name, 
                                   'email' => $email, 
                                   'hash' => $hash,
                                   'registered' => $dts );
        } else if ( $user == 'Buyer' ) {
            $selectText = "UPDATE user SET accesslevel = 'Artist', name = :name, "
                        . "hash = :hash, registered = :registered "
                        . "WHERE email = :email";
            $valuesArray = array ( 'name' => $name, 
                                   'email' => $email, 
                                   'hash' => $hash,
                                   'registered' => $dts );
        }
        $this->changeDB ( $selectText, $valuesArray );
    }
    
    //Регистрирует нового покупателя
    public function addNewBuyerDB ( $email, $dts)
    {
        $selectText = "INSERT INTO user (accesslevel, email, registered) "
                    . "VALUES ('Buyer', :email, :registered)";
        $valuesArray = array ( 'email' => $email, 
                               'registered' => $dts );
        $this->changeDB ( $selectText, $valuesArray );
    }
        
    //Регистрирует новоую заявку
    public function addNewRequestDB ( $buyerid, $pictureid, $dts)
    {
        $selectText = "INSERT INTO buyingrequest (userid, pictureid, buyingstatus, requestdate) "
                    . "VALUES (:buyerid, :pictureid, 'NotPaid', :requestdate)";
        $valuesArray = array ( 'buyerid' => $buyerid,
                               'pictureid' => $pictureid, 
                               'requestdate' => $dts );
        $this->changeDB ( $selectText, $valuesArray );
    }
            
    //Проверяет наличие заявки
    public function checkRequestDB ($buyerid, $pictureid)
    {
        $selectText = 'SELECT buyingRequestId FROM buyingrequest '
                    . 'WHERE userId = :userid AND pictureId = :pictureid';
        $valuesArray = array ( 'userid' => $buyerid,
                               'pictureid' => $pictureid );
        if ( $arr = $this->selectDB ( $selectText, $valuesArray ) ) {
            return $arr[0]['buyingRequestId'];
        }
        return NULL;
    }
            
    //Проверяет наличие заявки
    public function requestPaidDB ( $requestId )
    {
        $selectText = "UPDATE buyingrequest SET buyingstatus = 'Paid' "
                    . "WHERE buyingrequestid = :requestId";
        $valuesArray ['requestId'] = $requestId;
        $this->changeDB($selectText, $valuesArray);
        return TRUE;
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

