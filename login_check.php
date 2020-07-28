<?php

    $errorFlg = false;
    $ftitle = 'ログインに失敗しました';
    $body = '';

    session_start();
    if ($_POST['token'] != $_SESSION['token']){
        echo "不正アクセスの可能性あり";
        exit();
    }

    if(isset($_POST['email']) == true){
        $email = $_POST['email'];        
    }else{
        $errorFlg = true;
    }

    if(isset($_POST['password']) == true){
        $password = $_POST['password'];
    }else{
        $errorFlg = true;
    }

    if($errorFlg == false){
        header('X-FRAME-OPTIONS: SAMEORIGIN');

        try{

            require_once('./DBInfo.php');
            $pdo = new PDO(DBInfo::DSN, DBInfo::USER, DBInfo::PASSWORD);                        
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           
		    $statement = $pdo->prepare("SELECT * FROM member WHERE email=(:email)");
		    $statement->bindValue(':email', $email, PDO::PARAM_STR);
            $statement->execute();

		    if($row = $statement->fetch()){
 
                $password_hash = $row[password];
 
			    //パスワードが一致
			    if (password_verify($password, $password_hash)) {
				
                    //セッションハイジャック対策
                    session_regenerate_id(true);
				
                    $_SESSION['email'] = $email;
                    $_SESSION['name'] = $row['lastname'] . $row['firstname'];
                    $_SESSION['user'] = $row['customerID'];
                    $ftitle = 'ログインしました！';
                    $body = 'ログインに成功しました。<br>ウィンドウを閉じてください';

	    		}else{
                    $errorFlg = true;
                    $body = 'パスワードが間違っています。<br>再度入力して下さい。';
		    	}
    		}else{
                $errorFlg = true;
	    		$body = 'IDまたはパスワードが間違っています。<br>再度入力して下さい。';
    		}
        }catch(PDOException $e){
            if(isset($pdo) == true && $pdo->inTransaction() == true){
                $errorFlg = true;
                $body = 'エラーが発生しました。<br>お手数ですが、再度やり直してください。';
            }
        }  
    }  	
    
    ?>

<html lang="ja">
<head>
	<meta charset="utf-8">
	<link href="index.css" rel="stylesheet" media="all">
    <link href="https://fonts.googleapis.com/css?family=Noto+Serif+JP&display=swap" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" src="login.js"></script>

	<title><?php echo $ftitle; ?></title>
</head>
<body>
<header>
<h1><?php echo $ftitle; ?></h1>
</header>
<div class="subwindow">

<p><?php echo $body; ?></p>

<button id="wclose" class="button2">閉じる</button>

</div>
</body>
</html>