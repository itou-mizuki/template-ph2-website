<?php
session_start(); // セッション開始

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    // ログインしていない場合、signin.phpにリダイレクト
    header("Location: ../../admin/auth/signin.php");
    exit; // リダイレクト後はスクリプトを終了
}

require('../dbconnect.php');
$questions = $pdo->query("SELECT * FROM questions")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // SQL命令を準備する
  $stmt = $pdo->prepare('DELETE FROM questions WHERE id = :id');

  // DELETE命令にポストデータの内容をセットする
  $stmt->bindValue(':id', $_POST['id']);

  // 削除後にページをリダイレクト
  header("Location: index.php");
  
  // SQL命令を実行する
  $stmt->execute();
} 
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>問題一覧</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#f7ede2]">
    <header class="bg-[#60b7b8] p-4 flex justify-between items-center">
        <img src="../assets/img/logo.svg" alt="">
        <a href="/admin/auth/signout.php" class=" hover:text-gray-600">ログアウト</a>
    </header>

    <main class="flex">
        <nav class="bg-[#e8ddc9] flex flex-col w-36 h-screen gap-3 pl-3 pt-5  ">
            <a href="" class="text-blue-500 hover:text-blue-700">ユーザー登録</a>
            <a href="./index.php" class="text-blue-500 hover:text-blue-700">問題一覧</a>
            <a href="./questions/create.php" class=" text-blue-500 hover:text-blue-700">問題作成</a>
        </nav>
        <div class="pl-8 pt-6 border-black pr-5">
            <h1 class="text-5xl mb-8">問題一覧</h1>
            <div class="flex gap-3 pl-5 border-b border-black">
                <div>ID</div>
                <div>問題</div>
            </div>
<div class="mb-3 pb-3 mt-1 w-full">
    <ul class="mb-3 pb-3 mt-1 w-full">
        <?php for ($i = 0; $i < count($questions); $i++) { ?>
            <li class="flex justify-between items-center mb-3 border-b">
                <div class="flex flex-1 pb-3">
                    <p class="mr-5"><?= $i+1 ?></p>
                    <!-- Add border to the a tag and remove block styles to apply the border only to text -->
                    <a href="./questions/edit.php?id=<?= $questions[$i]['id']-1 ?>" class=" underline text-blue-500 hover:text-blue-700 "> <?= $questions[$i]["content"]; ?></a>
                </div>
                <form action="index.php" method="POST">
                    <input type="hidden" name="id" value="<?=$questions[$i]['id']?>">
                    <input type="submit" value="削除">
                </form>
            </li>
        <?php } ?>
    </ul>
</div>
        </div>
    </main>
</body>
</html>