<?php

    require_once('delete_file.php');
    require_once('./DBInfo.php');

    if(isset($_POST['target'])){
        $target = $_POST['target'];
        $name = $_POST['timestamp'];
        $pdo = new PDO(DBInfo::DSN, DBInfo::USER, DBInfo::PASSWORD); 
        deleteFile($target,$name,$pdo);
    }else{
        echo 'ERROR：エラーが発生しました';
    } 

    ?>
