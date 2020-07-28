<?php
    session_start();
    session_regenerate_id(true);

    mb_language('Japanese'); 
    mb_internal_encoding('UTF-8');

    require_once('allowExts.php');
    
    $page = 'https://ryom.work/uploader/';

    $val = array();
    $target = array();
    $name = array();
    $errorflg = false;
    $errorMsg = '';
    $sendKey = array();

    if(isset($_SESSION['name'])){
        $username = $_SESSION['name'];
    }else{
        $username = 'ユーザー';
    }

    if(isset($_POST['send'])){

        $i = 0;
        foreach($_POST['send'] as $value){
            $val[$i] = $value;
            $sendKey[$i] = $value . '.dlkey';
            $i++;
        }

    }else{
        $errorflg = true;
    }

    if(isset($_POST['target'])){

        $j = 0;
        foreach($_POST['target'] as $tar){

            if(empty($tar)){
                
            }else if(strpos($tar,'@') === false){
                echo $tar. "：正しいメールアドレスを入力してください！\n";
            }else{
                $target[$j] = $tar;
                $j++;
            }
        }

    }else{
        $errorflg = true;
    }

    if($errorflg == true || $j == 0){
        echo 'error';
    }else{
        try{
            require_once('./DBInfo.php');
            $pdo = new PDO(DBInfo::DSN, DBInfo::USER, DBInfo::PASSWORD);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql1 = 'SELECT lastname,firstname,customerID from member WHERE email = "';

            $j = 0;
            foreach($target as $t){
                $userFlg = false;
                $sql = $sql1 . $t . '"';
                $st1 = $pdo->query($sql);
                $st1->execute();

                if($row = $st1->fetch()){
                    $tar = $row[0] . $row[1];
                    foreach($val as $v){
                        $sql2 = 'INSERT INTO file_permit SET timestamp = ?, permit_user = ?';

                        $st2 = $pdo->prepare($sql2);

                        $st2->bindValue(1, $v);
                        $st2->bindValue(2, $row[2]);

                        $pdo->beginTransaction();        
                        $st2->execute();
                        $pdo->commit();

                        $userFlg = true;
                    }
                }else{
                    $tar = $t;
                }

                $mailto = $t;
	            $returnMail = 'info@hogehoge.com';
 
	            $name = 'ファイルアップローダ';
	            $mail = 'info@hogehoge.com';
                $subject = 'キーファイルが届きました';

                $header = "Content-Type: multipart/mixed;boundary=\"__BOUNDARY__\"\n";
                $header .= "Return-Path: " . $returnMail . " \n";
                $header .= "From: " . $returnMail ." \n";
                $header .= "Sender: " . $t ." \n";
                $header .= "Reply-To: " . $mailto . " \n";

                $text = <<< EOM
{$tar}　様

{$username}様よりファイルが届きました。
添付されておりますキーファイルを、ホームページのアップローダにキーをドラッグ＆ドロップ
することでデータを受け取ることができます。
※会員登録を行っていない場合、ダウンロードの回数に制限があります。ご注意ください


EOM;

                if($userFlg == true){
                    $text .= "ホームページからログインすることでもデータを受け取ることができます。\n";
                }

                if($username == 'ユーザー'){
                    $text .= '匿名の方からのファイルのため、先にセキュリティスキャンをすることをお勧めいたします。';
                }

$text .= <<< EOM


============
ファイルアップローダ
{$page}

EOM;

                $body = "--__BOUNDARY__\n";
                $body .= "Content-Type: text/plain; charset=\"ISO-2022-JP\"\n\n";
                $body .= $text . "\n";
                $body .= "--__BOUNDARY__\n";

                foreach($sendKey as $kp){
                    $keys = $keyPath . $kp; 
                    $body .= "Content-Type: application/octet-stream; name=\"{$kp}\"\n";
                    $body .= "Content-Disposition: attachment; filename=\"{$kp}\"\n";
                    $body .= "Content-Transfer-Encoding: base64\n";
                    $body .= "\n";
                    $body .= chunk_split(base64_encode(file_get_contents($keys)));
                    $body .= "--__BOUNDARY__\n";
                }
                $j++;                
            }

            if(mb_send_mail($t,$subject,$body,$header,'-f '. $returnMail)){
                echo "入力されたユーザー様にキーを送信しました！";
            }

        }catch(PDOException $e){
            $code = $e->getCode();
            $message = $e->getMessage();
            print("{$code}/{$message}<br/>");        
        }
    }

    ?>
