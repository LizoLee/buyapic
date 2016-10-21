<?php
//Файл функций для работы с базой данных========================================

error_reporting(-1);
if ( !session_id() ) { session_start(); }

class BuyAPic
{
    //Данные для подключения к БД
    private $dsn = 'mysql:dbname=buyapic;host=127.0.0.1';
    private $user = 'root';
    private $password = 'dreamJ0b';
    private $opt = array(
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT         => true
    );
    
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
        if ( $row = $stmt->fetch(PDO::FETCH_LAZY) ) {
            foreach ( $row as $key => $value ) {
                if( isset($value) && $key != 'queryString' ) {
                    $arr[$key]=$value;
                }
            }
            $dbh = NULL;
            return $arr;
        }
        //Если ничего не найдено
        return NULL;
    }
    
    //Добавляет новый элемент в БД
    private function insertDB( string $selectText, array $valuesArray )
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
    
    //Выбирает, какой метод должен обработать массив $argsArray
    public function __call(string $methodName, array $argsArray)
    {
        $args = preg_split('/(?<=\w)(?=[A-Z])/', $methodName);
        $action = array_shift($args);
        $propertyName = implode('', $args);
        
        switch ($action) {
            case 'get':
                break;
            case 'change':
                switch ($propertyName) {
                    case 'NameDB':
                        $selectText = 'UPDATE user SET name = :name '
                            . '         WHERE user.userid = :userid';
                        $valuesArray = array( 'userid' => $argsArray[0], 
                                              'name' => $argsArray[1] );
                        $this->changeDB($selectText, $valuesArray);
                        break;
                    case 'WebPageDB':
                        $selectText = 'UPDATE user SET webpage = :webpage '
                            . '         WHERE user.userid = :userid';
                        $valuesArray = array( 'userid' => $argsArray[0], 
                                              'webpage' => $argsArray[1] );
                        $this->changeDB($selectText, $valuesArray);
                        break;
                    case 'BankAccountDB':
                        $selectText = 'UPDATE user SET bankaccount = :bankaccount '
                            . '         WHERE user.userid = :userid';
                        $valuesArray = array( 'userid' => $argsArray[0], 
                                              'bankaccount' => $argsArray[1] );
                        $this->changeDB($selectText, $valuesArray);
                        break;
                    case 'PhotoLinkDB':
                        $selectText = 'UPDATE user SET photolink = :photolink '
                            . '         WHERE user.userid = :userid';
                        $valuesArray = array( 'userid' => $argsArray[0], 
                                              'photolink' => $argsArray[1] );
                        $this->changeDB($selectText, $valuesArray);
                        break;
                    case 'SelfInfoDB':
                        $selectText = 'UPDATE user SET selfinfo = :selfinfo '
                            . '         WHERE user.userid = :userid';
                        $valuesArray = array( 'userid' => $argsArray[0], 
                                              'selfinfo' => $argsArray[1] );
                        $this->changeDB($selectText, $valuesArray);
                        break;
                    case 'PasswordDB':
                        $selectText = 'UPDATE user SET hash = :hash '
                            . '         WHERE user.userid = :userid';
                        $valuesArray = array( 'userid' => $argsArray[0], 
                                              'hash' => $argsArray[1] );
                        $this->changeDB($selectText, $valuesArray);
                        break;
                }
                break;
            case 'delete':
                switch ($propertyName) {
                    case 'WebPageDB':
                        $selectText = 'UPDATE user SET webpage = NULL '
                                    . 'WHERE user.userid = :userid';
                        $valuesArray = array( 'userid' => $argsArray[0] );
                        $this->changeDB($selectText, $valuesArray);
                        break;
                    case 'BankAccountDB':
                        $selectText = 'UPDATE user SET bankaccount = NULL '
                                    . 'WHERE user.userid = :userid';
                        $valuesArray = array( 'userid' => $argsArray[0] );
                        $this->changeDB($selectText, $valuesArray);
                        break;
                    case 'PhotoLinkDB':
                        $selectText = 'UPDATE user SET photolink = NULL '
                                    . 'WHERE user.userid = :userid';
                        $valuesArray = array( 'userid' => $argsArray[0] );
                        $this->changeDB($selectText, $valuesArray);
                        break;
                }
                break;
            }
        
        return TRUE;
    }

        //Возвращает максимальный из уже используемых id
    private function getMaxIdDB ()
    {
        $dbh = $this->connectDB();
        $maxIdU = $dbh->query('SELECT MAX(userid) FROM user')
                ->fetch(PDO::FETCH_LAZY);
        $maxIdP = $dbh->query('SELECT MAX(pictureid) FROM picture')
                ->fetch(PDO::FETCH_LAZY);
        $maxIdBR = $dbh->query('SELECT MAX(buyingrequestid) FROM buyingrequest')
                ->fetch(PDO::FETCH_LAZY);
        return max ( $maxIdU["MAX(userid)"], $maxIdP["MAX(pictureid)"], 
                $maxIdBR["MAX(buyingrequestid)"] );
    }

    //Возвращает id и hash пользователя name
    public function getAuthorizationDataDB ( $email )
    {
        $selectText = 'SELECT userid, hash FROM user WHERE email = :email';
        $valuesArray['email'] = $email;
        if ( $arr = $this->selectDB ( $selectText, $valuesArray ) ) {
            return $arr;
        }
        //Если в базе такого email нет
        return NULL;
    }
    
    //Возвращает имя пользователя name по его id
    public function getNameDB ( $userid )
    {
        $selectText = 'SELECT name FROM user WHERE userid = :userid';
        $valuesArray['userid'] = $userid;
        if ( $arr = $this->selectDB ( $selectText, $valuesArray ) ) {
            return $arr['name'];
        }        
        //Если в базе такого id нет
        return NULL;
    }
    
    //Возвращает информацию о пользователе по его id
    public function getUserInfoDB ( $userid )
    {
        $selectText = 'SELECT name, email, photolink, selfinfo, webpage, '
                    . 'bankaccount, registered '
                    . 'FROM user WHERE userid = :userid';
        $valuesArray['userid'] = $userid;
        if ( $arr = $this->selectDB ( $selectText, $valuesArray ) ) {
            $info['userName'] = $arr['name'];
            $info['email'] = $arr['email'];
            $info['photoLink'] = isset($arr['photolink']) ? 
                                  $arr['photolink'] : "/uploads/avatars/1.png";
            $info['selfInfo'] = isset($arr['selfinfo']) ? $arr['selfinfo'] : "-";
            $info['webPage'] = isset($arr['webpage']) ?
                                                        $arr['webpage'] : "-";
            $info['bankAccount'] = isset($arr['bankaccount']) ?
                                                    $arr['bankaccount'] : "-";
            $info['registered'] = isset($arr['registered']) ?
                         date("Y-m-d H:i:s", intval($arr['registered'])) : "-";
            return $info;
        }        
        //Если в базе такого id нет
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
    
    //Добавляет нового пользователя ( $name, $email, $hash ) для художника,
    //( $email ) для покупателя
    public function addNewUserDB ()
    {
        $id = $this->getMaxIdDB() + 1;
        $argsArray = func_get_args();
        if ( func_num_args() == 1 ) {
            $selectText = "INSERT INTO user (userid, accesslevel, email) " .
                          "VALUES (:userid, 'Buyer', :email)";
            $valuesArray = array ( 'userid' => $id, 
                               'email' => $argsArray[0] );
        } else {
            $selectText = "INSERT INTO " . 
                "user (userid, accesslevel, name, email, hash, "
                    . "artiststatus, registered) " .
                "VALUES " . 
                "(:userid, 'Artist', :name, :email, :hash, "
                    . "'Ожидает модерации', :registered )";
            $valuesArray = array ( 'userid' => $id, 
                               'name' => $argsArray[0], 
                               'email' => $argsArray[1], 
                               'hash' => $argsArray[2],
                               'registered' => $argsArray[3] );
        }
        $this->insertDB ( $selectText, $valuesArray );
    }
}
?>

