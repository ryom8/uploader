<?php

    $myPage = basename($_SERVER['PHP_SELF']);
    $loginFlg = false;
    $name = '';
    $forYou = array();
    $myFile = array();

    require_once('allowExts.php');

    // ログイン状況の確認
    session_start();
    session_regenerate_id(true);
    if(isset($_SESSION['name'])){
        $name = $_SESSION['name'];
        $loginFlg = true;
        $bClass = 'nLogin';
        $user = $_SESSION['user'];

        try{
            require_once('./DBInfo.php');
            $pdo = new PDO(DBInfo::DSN, DBInfo::USER, DBInfo::PASSWORD);                        
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql1 = 'SELECT timestamp FROM file_permit WHERE permit_user = "' .$user. '"';
            $st1 = $pdo->prepare($sql1);
            $st1->execute();
            $h = 0;
            while($row = $st1->fetch()){
                $forYou[$h][0] = $row[0];
                $sq = 'SELECT file,dl_limit FROM file_db WHERE timestamp ="' .$row[0]. '"';
                $st = $pdo->prepare($sq);
                $st->execute();
                if($ro = $st->fetch()){
                    $forYou[$h][1] = $ro[0];
                    $forYou[$h][2] = $ro[1];
                }
                $h++;
            }

    
            $sql2 = 'SELECT file,timestamp,dl_limit,dl_count FROM file_db WHERE user = "' .$user. '"';
    
            $st2 = $pdo->prepare($sql2);
            $st2->execute();
            $j = 0;
            while($row = $st2->fetch()){
                for($k=0;$k<4;$k++){
                    $myFile[$j][$k] = $row[$k];
                }
                $j++;
            }
    
        }catch(PDOException $e){
            $code = $e->getCode();
            $message = $e->getMessage();
            print("{$code}/{$message}<br/>");        
        }

    }else{
        $bClass = 'nLogoff';
    }

?>

<!document html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<link href="index.css" rel="stylesheet" media="all">
    <link href="https://fonts.googleapis.com/css?family=Noto+Serif+JP&display=swap" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" src="index.js"></script>

	<title>ファイルアップローダ</title>
</head>
<body>
    <header>
        <h1><a href="<?php echo $myPage; ?>">ファイルアップローダ</a></h1>
    </header>

    <div class="center">

        <div class="center" id="members">
            <?php
                if($loginFlg == true){
                    echo '<button id="l_logout" class="button">ログアウト</button>';
                    echo '<button id="l_howto" class="button">当サイトについて</button>';
                }else{
                    echo '<button id="l_new" class="button">新規登録</button>';                
                    echo '<button id="l_login" class="button">ログイン</button>';
                    echo '<button id="l_howto" class="button">当サイトについて</button>';
                }
                ?>
        </div>
        
        <div id="uploader">        
        ここにファイル又はキー(dlkey)を<br>ドラッグ＆ドロップしてください
        </div>
        <?php            

            echo '<p class="right">アップロード可能形式：';
            foreach($allowExts as $ex){
                echo $ex. ',';
            }
            echo '最大容量：' .$maxMB. 'MBまで</p>';
        ?>

        <div id="sendFile">
        送信先（メールアドレスを入力）：
        <?php
            for($i=1;$i<=3;$i++){
                echo '<input type="email" name="target[]" pattern=".+@.+\..+" class="nameForm target' .$i. '"';
                if($i==1){
                    echo ' required';
                }
                echo '> ';
            }
        ?>
            <button id="send">送信する</button><br>
        チェックされたファイルを、メールにてキーを受け取ることができます</div>
        <div id="status1"></div>

        <?php

        if($loginFlg == true){
            echo '<div id="forYou">';
            echo '<h4>★' .$name. '様宛ファイル一覧</h4>';
            if($h == 0){
                echo '貴方宛に送られたファイルはありません。';
            }else{
                echo '<table class="fileList"><tr><th>No.</th><th>ファイル名</th><th>ダウンロード期限</th></tr>';
                $p = 1;
                foreach($forYou as $you){

                    $dl_limit = dlLimit($you[2]);

                    echo '<tr><td>' .$p. '</td>';
                    echo '<td><button id="' .$you[0]. '" class="DLown button1" value="' .$you[1]. '">' .$you[1]. '</button></td>';
                    echo '<td>' .$dl_limit. '</td>';
                    $p++;
                }
                echo '</table>';
            }
            echo '</div><div id="myFile">';
            echo '<h4>★アップロードファイル一覧</h4>';
            if($j == 0){
                echo 'アップロードされたファイルはありません。';
            }else{
                echo '<table class="fileList"><tr><th>No.</th><th>ファイル名</th><th>キー</th><th>ダウンロード期限</th><th>残り回数</th><th>ファイル削除</th></tr>';
                $q = 1;
                foreach($myFile as $my){
                    $key = $my[1]. '.dlkey';

                    $dl_limit = dlLimit($my[2]);
                    if($my[3] < 0){
                        $dl_num = '-';
                    }else{
                        $dl_num = $my[3];
                    }

                    echo '<tr><td>' .$q. '</td>';
                    echo '<td><button id="' .$my[1]. '" class="DLown button1" value="' .$my[0]. '">' .$my[0]. '</button></td>';
                    echo '<td><button id="' .$key. '" class="DLkey button2">DLKEY</button></td>';
                    echo '<td>' .$dl_limit. '</td>';
                    echo '<td>' .$dl_num. '回</td>';
                    echo '<td><button id="' .$my[0]. '" class="delete button3" value="' .$my[1]. '">削除</button></td></tr>';
                    $q++;
                }
                echo '</table>';
            }
            echo '</div>';
        }

        function dlLimit($t){
            $t = str_replace('-','/',$t);
            if((substr($t,0,1)) == 0){
                return '期限なし';
            }else{
                return substr($t,0,16);
            }
        }

        ?>
        <p class="right">※第三者にデータを受け渡す場合は、キーを渡してください<br>※自分のデータは、期限内であれば何度でもDL可能です</p>
    </div>

    <footer>
        <p>ファイルアップローダ Ver.1.0</p>
    </footer>
</body>
</html>