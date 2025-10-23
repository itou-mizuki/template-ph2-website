<?php
// データベース接続情報
$dsn = 'mysql:host=db;dbname=posse;charset=utf8';  // データベース接続のためのDSN
$user = 'root';  // ユーザー名
$password = 'root';  // パスワード

try {
    // PDOでデータベースに接続
    $pdo = new PDO($dsn, $user, $password);
    // エラーモードを例外に設定（エラー時に例外を投げる）
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 接続成功のメッセージ（開発時のみ）
    // echo "データベースに接続成功";

} catch (PDOException $e) {
    // エラーが発生した場合にエラーメッセージを表示
    echo '接続失敗: ' . $e->getMessage();
    exit;  // 接続エラーが発生した場合は処理を終了
}
?>
