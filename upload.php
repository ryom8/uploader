<?php
    date_default_timezone_set('Asia/Tokyo');
    $now = date('Y-m-d H:i:s');
    $loginFlg = false;
    $masterFlg = false;
    $dl_key = '';
    $dl_count = 0;
    $timeStamp = date('YmdHis');
    $filename = $timeStamp. '.dlkey';
    $file = '';
    $keyName = '';
    $ext = '';
    $allowFlg = false;
    $message = '';
    $dl_limit = '';
    $DL_target = 'DLother';

    $num = 'updata' .$_POST['num'];

    require_once('allowExts.php');

    session_start();
    session_regenerate_id(true);
    if(isset($_SESSION['user'])){
        $user = $_SESSION['user'];
        $loginFlg = true;
        // DL可能回数は、masterの場合は回数を無制限にし、それ以外のログインユーザーは5回にする
        if($user == 'master' || $user == 'cstm0003'){
            $dl_count = -1;
            $masterFlg = true;
        }else{
            $dl_count = 5;
            $dl_limit = date('Y-m-d H:i:s',strtotime("+1 week",time()));
        }
    }else{
        // ログインしていない場合はDL回数が2回に制限される
        $user = 'GUEST';
        $dl_count = 2;
        $dl_limit = date('Y-m-d H:i:s',strtotime("+1 day",time()));
    }

    if(isset($_FILES['file']['name'])){
        $file = htmlspecialchars($_FILES['file']['name']);        

        if($_FILES['file']['size'] > $maxSize){
            echo 'ファイル容量がオーバーしています';
            goto end;
        }

        // 拡張子、ファイル名の取得
        $ext = getExt($file);
        $file = pathinfo($file,PATHINFO_FILENAME);

        // キーであるか、それ以外の拡張子であるかの判定を行う
        if($ext == 'dlkey'){
            $keyName = $file;
        }else{
            $keyName = $timeStamp. '.dlkey';
            foreach($allowExts as $allow){
                if($allow == $ext){
                    $allowFlg = true;
                    $dl_key = base64_encode(openssl_random_pseudo_bytes(32));
                    break;
                }
            }            
        }
    }

    try{
        require_once('./DBInfo.php');
        $pdo = new PDO(DBInfo::DSN, DBInfo::USER, DBInfo::PASSWORD);                        
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql1 = 'SELECT * FROM file_db WHERE user = "' .$user. '"';

        $st1 = $pdo->query($sql1);
        $co = $st1->fetchall();

        $count = count($co);
        // masterの場合は回数制限を撤廃するため0にして処理を行う
        if($masterFlg == true){
            $count = 0;
        }

        if($dl_key != ''){
            if($count >= 10){
                $message = 'アップロード数制限のためアップロードができません';
            }else if($count >= 5 && $loginFlg == true){
                $message = 'アップロード数制限のためアップロードができません';
            }else{
                // キーファイルの作成
                file_put_contents($keyPath.$keyName,$dl_key);

                if (is_uploaded_file($_FILES["file"]["tmp_name"])) {
                    $newFileName = $timeStamp . '.' . $ext;
                    if (move_uploaded_file($_FILES["file"]["tmp_name"], 'files/' . $newFileName)) {

                        echo $_FILES["file"]["name"] . 'をアップロードしました！⇒　<button id="' .$keyName. '" class="DLkey button">キーのダウンロード</button>';
                        echo <<< FDL
                        <script>
                            $('#{$num}').prepend('<label><input type="checkbox" name="send[]" class="sendFile" value="{$timeStamp}"> ');
                            $('#{$num}').append('</label>');
                        
                        </script>
FDL;

                    } else {
                        echo "ファイルをアップロードできません。";
                        goto end;
                    }
                } else {
                echo "ファイルが選択されていません。";
                goto end;
                }

                $sql2 = 'INSERT INTO file_db SET user = ?, dl_key = ?, file = ?, timestamp = ?, dl_limit = ?, dl_count = ?';

                $statement = $pdo->prepare($sql2);

                $statement->bindValue(1, $user);
                $statement->bindValue(2, $dl_key);
                $statement->bindValue(3, $file. '.' .$ext);
                $statement->bindValue(4, $timeStamp);
                $statement->bindValue(5, $dl_limit);
                $statement->bindValue(6, $dl_count);

                $pdo->beginTransaction();
        
                $statement->execute();

                $pdo->commit();
            }

        }else if($ext == 'dlkey'){

            if (is_uploaded_file($_FILES['file']['tmp_name'])) {
                $newKey = $keyName . '.txt';
                $newKeyPath = 'works/' . $newKey;
                if (move_uploaded_file($_FILES['file']['tmp_name'], $newKeyPath)) {
                    $fp = fopen($newKeyPath,'rb');
                    $key = fgets($fp);
                    fclose($fp);
                    unlink($newKeyPath);

                    $sql3 = 'SELECT user,file,dl_count FROM file_db WHERE dl_key = "' .$key. '"';
                    $statement = $pdo->prepare($sql3);
                    $statement->execute();

                    if($row = $statement->fetch()){
                        $dl_count = $row[2];
                        if($row[0] == $user){
                            $DL_target = 'DLown';
                        }
                        $DL_file = $row[1];
                    }else{
                        echo 'ERROR：キーが正しくないか、既に消去されています。';
                        goto end;
                    }

                    echo 'ファイルのダウンロードはこちら！⇒　<button id="' .$DL_file. '" class="' .$DL_target. ' button" value="' .$row[1]. '">' .$DL_file. '</button>';

                } else {
                    echo "ファイルをアップロードできません。";
                    goto end;
                }
            } else {
            echo "ファイルが選択されていません。";
            goto end;
            }
        }


    }catch(PDOException $e){
        $code = $e->getCode();
        $message = $e->getMessage();
        print("{$code}/{$message}<br/>");        
    }

    end:


