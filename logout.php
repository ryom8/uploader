<?php
    session_start();
 
    header("Content-type: text/html; charset=utf-8");
 
//セッション変数を全て解除
    $_SESSION = array();
 
//セッションクッキーの削除
    if (isset($_COOKIE["PHPSESSID"])) {
	    setcookie("PHPSESSID", '', time() - 1800, '/');
    }
 
    //セッションを破棄する
    session_destroy();

?>

<html lang="ja">
<head>
	<meta charset="utf-8">
	<link href="index.css" rel="stylesheet" media="all">
    <link href="https://fonts.googleapis.com/css?family=Noto+Serif+JP&display=swap" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" src="login.js"></script>

	<title>ご利用ありがとうございました</title>
</head>
<body>
<header>
<h1>ログアウトしました</h1>
</header>
<div class="subwindow">

<p>ログアウトしました。<br>ウィンドウを閉じてください</p>

<button id="wclose" class="button2">閉じる</button>

</div>
</body>
</html>