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
            return $arr;
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
}
?>

