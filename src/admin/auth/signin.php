<?php
session_start(); // セッション開始

require('../../dbconnect.php'); // データベース接続

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 入力されたemailとpasswordを取得
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 入力されたemailを使ってデータベースからユーザーを取得
    $stmt = $pdo->prepare('SELECT * FROM user WHERE email = :email');
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // ユーザーが見つかった場合、ハッシュ化されたpasswordと入力されたpasswordを比較
        if (password_verify($password, $user['password'])) {
            // パスワードが一致した場合、セッションにユーザー情報を保存
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];

            // ログイン成功後、リダイレクト
            header("Location:  ../../admin/index.php");
            exit;
        } else {
            // パスワードが一致しない場合
            $error = "メールアドレスまたはパスワードが間違っています。";
        }
    } else {
        // ユーザーが存在しない場合
        $error = "メールアドレスまたはパスワードが間違っています。";
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン画面</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#f7ede2]">
    <header class="bg-[#60b7b8] p-4 flex justify-between items-center">
        <img src="../../assets/img/logo.svg" alt="">
        <a href="#" class=" hover:text-gray-600">ログアウト</a>
    </header>
    <main class="pl-14 pt-4 border-black pr-5">
        <div class="text-5xl pb-8">ログイン</div>
        <form action="" method="POST">
            <div class="flex flex-col">
                <label for="email" class="pb-3">Email</label>
                <input type="email" id="email" name="email" class="p-1 border border-gray-300 rounded mb-6" required>
            </div>
            <div class="flex flex-col">
                <label for="password" class="pb-3">パスワード</label>
                <input type="password" id="password" name="password" class="p-1 border border-gray-300 rounded mb-6" required>
            </div>
            <input type="submit" value="ログイン" class="bg-[#76B5AB] text-white py-1 px-2  rounded">
        </form>
    </main>
</body>
</html>