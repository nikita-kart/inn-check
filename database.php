<?php

function db_connection(){
    define("HOST", "localhost");
    define("USER", "root");
    define("PASSWORD", "");
    define("DB_NAME", "inn_check");
    $db_connect = mysqli_connect(HOST, USER, PASSWORD, DB_NAME);
    
    return $db_connect;
}

function db_add_inn_info($inn, $message, $date){
    if (!isset($inn) || !isset($message) || !isset($date)) return;

    $connection = db_connection();

    $query = "
    INSERT INTO results (inn, message, date)
    VALUES ('$inn', '$message', '$date');
    ";

    $sql = mysqli_query($connection, $query) or die(mysqli_error($connection));
}

function db_get_inn_info($inn){
    $connection = db_connection();

    $query = "
    SELECT * FROM results
    WHERE inn = '$inn';
    ";

    $sql = mysqli_query($connection, $query) or die(mysqli_error($connection));
    $result = mysqli_fetch_assoc($sql);
    //var_dump($result);
    if ($result === null){
        //записи нет
        return false;
    }
    else if ($result["date"] != date("Y-m-d")){
        //запись устарела
        $query = "
        DELETE FROM results WHERE inn = '$inn';
        ";
        mysqli_query($connection, $query) or die(mysqli_error($connection));
        return false;
    }
    
    return $result["message"];
}

?>