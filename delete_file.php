<?php

    function deleteFile($target,$name,$pdo){
        require_once('allowExts.php');
        try{

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql1 = 'SELECT * FROM file_db WHERE timestamp = "' .$name. '"';
            $st1 = $pdo->prepare($sql1);
            $st1->execute();

            if($row = $st1->fetch()){

                // キーの削除
                $keyFile = $keyPath . $target . '.dlkey';
                unlink($keyFile);

                // ファイルの削除
                $ext = getExt($row[0]);
                $fileName = $filePath . $target . '.' . $ext;
                unlink($fileName);

                $sql2 = 'DELETE FROM file_db WHERE timestamp = "' .$name. '"';
                $st2 = $pdo->prepare($sql2);
                $st2->execute();

                $sql3 = 'DELETE FROM file_permit WHERE timestamp = "' .$name. '"';
                $st3 = $pdo->prepare($sql3);
                $st3->execute();

                echo 'ファイルは正常に削除されました！';

            }else{
                echo 'ERROR：データが存在しないか、削除されています';
            }   


        }catch(PDOException $e){
            $code = $e->getCode();
            $message = $e->getMessage();
            print("{$code}/{$message}<br/>");        
        }
    }    

    ?>