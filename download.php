<?php
    session_start();
    session_regenerate_id(true);
    $name = '';
    $keyFlg = false;
    $origin = '';
    $key = '';

    require_once('allowExts.php');

    if(isset($_POST['file'])){
        $file = $_POST['file'];

        if(isset($_POST['name'])){
            $name = $_POST['name'];
            if($name != 'DLkey'){                
                $DLfile = $name;
                $origin = $filePath.$file. '.' .getExt($name);
                $key = $keyPath.$file. '.dlkey';
            }else{
                $DLfile = $file;
                $keyFlg = true;
                $origin = $keyPath.$DLfile;
            }
        }

        if(isset($_POST['flg'])){
            $reduceFlg = $_POST['flg'];
        }else{
            echo 'データの出力に失敗しました';
            goto end;
        }
    }else{
        echo 'データの出力に失敗しました';
    }

    if(isset($_SESSION['user'])){
        $user = $_SESSION['user'];
    }else{
        $reduceFlg = true;
    }

    if($reduceFlg == true && $keyFlg == false){
        try{
            require_once('./DBInfo.php');
            $pdo = new PDO(DBInfo::DSN, DBInfo::USER, DBInfo::PASSWORD);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql1 = 'SELECT dl_count FROM file_db WHERE timestamp = "' .$file. '"';
            $statement = $pdo->prepare($sql1);
            $statement->execute();
            $dl_count = $statement->fetchColumn();
            $dl_count--;

            if($dl_count < 0){
                // dl_countが0未満の時は何もしない

            }else if($dl_count == 0){
                // dl_countが0になった場合、データ削除を行う
                $sql2 = 'DELETE FROM file_db WHERE timestamp = "' .$file. '"';
                $st2 = $pdo->prepare($sql2);
                $st2->execute();

                unlink($origin);

                $sql3 = 'DELETE FROM file_permit WHERE timestamp = "' .$file. '"';
                $st3 = $pdo->prepare($sql3);
                $st3->execute();

                unlink($key);

            }else{
                // dl_countを1減らす処理を行う
                $sql2 = 'UPDATE file_db SET dl_count = ' .$dl_count. ' WHERE timestamp = "' .$file. '"';
                $statement = $pdo->prepare($sql2);
                $statement->execute();

            }

        }catch(PDOException $e){
            $code = $e->getCode();
            $message = $e->getMessage();
            print("{$code}/{$message}<br/>");        
        }
    }

    header('Content-Type: application/force-download');
    header('Content-Length: '.filesize($origin));
    header('Content-Disposition: attachment; filename="' .$DLfile. '"');

    readfile($origin);

    end:

    ?>
