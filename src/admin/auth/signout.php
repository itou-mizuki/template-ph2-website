<?php
// セッションの開始
session_start();

// セッション変数を空の配列に設定して、すべてのセッションデータをクリア
$_SESSION = array();

// サーバ側のセッションを破棄
session_destroy();

// 画面遷移
header('Location: http://localhost:8080/admin/index.php');
exit;