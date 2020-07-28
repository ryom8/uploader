<?php
        date_default_timezone_set('Asia/Tokyo');
        $now = date('Y-m-d H:i:s');
        require_once('allowExts.php');

    try{
        require_once('./DBInfo.php');
        $pdo = new PDO(DBInfo::DSN, DBInfo::USER, DBInfo::PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql1 = 'SELECT timestamp,file FROM file_db WHERE dl_limit < "' .$now. '" AND dl_count > 0';
        $st1 = $pdo->prepare($sql1);
        $st1->execute();

        // ファイルとキーの削除
        while($row = $st1->fetch()){
            $file = $filePath.$row[0]. getExt($row[1]);
            unlink($file);

            $key = $keyPath.$row[0]. '.dlkey';
            unlink($key);

            $sql2 = 'DELETE FROM file_permit WHERE timestamp = "' .$row[0]. '"';
            $st2 = $pdo->prepare($sql2);
            $st2->execute();
        }

        $sql3 = 'DELETE FROM file_db WHERE dl_limit < "' .$now. '" AND dl_count > 0';
        $st3 = $pdo->prepare($sql3);
        $st3->execute();    

    }catch(PDOException $e){
        $code = $e->getCode();
        $message = $e->getMessage();
        print("{$code}/{$message}<br/>");        
    }    

    ?>
