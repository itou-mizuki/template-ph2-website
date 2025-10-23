<?php
// DB接続（dbconnect.php で接続処理が行われている想定）
require('../../dbconnect.php');
$questions = $pdo->query("SELECT * FROM questions")->fetchAll(PDO::FETCH_ASSOC);
$choices = $pdo->query("SELECT * FROM choices")->fetchAll(PDO::FETCH_ASSOC);

foreach ($questions as $qKey => $question) {
    $question["choices"] = [];
    foreach ($choices as $cKey => $choice) {
        if ($choice["question_id"] == $question["id"]) {
            $question["choices"][] = $choice;
        }
    }
    $questions[$qKey] = $question;
};


// パラメータの取得
$id = $_GET['id'];

// フォームがPOST送信されたときに処理を行う
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // フォームデータを受け取る（未入力対策に null 合体演算子を使用）
    $question_content = $_POST['question_content'] ?? '';
    $choice1 = $_POST['choice1'] ?? '';
    $choice2 = $_POST['choice2'] ?? '';
    $choice3 = $_POST['choice3'] ?? '';
    $correct_answer = $_POST['correct_answer'] ?? '';
    $supplement = $_POST['supplement'] ?? null; // null 許容

    // 必須チェック
    if ($question_content === '' || $choice1 === '' || $choice2 === '' || $choice3 === '' || $correct_answer === '') {
        echo 'すべてのフィールドを入力してください。';
        exit;
    }

    // 画像アップロード処理（DBにはファイル名のみ保存）
    $image_name = null; // アップロードなしの場合は null を格納
    if (isset($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
        if ((int)$_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            echo '画像のアップロードに失敗しました。';
            exit;
        }

        // 拡張子チェック（許可する形式のみ）
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_ext, true)) {
            echo '対応していない画像形式です（jpg/jpeg/png/gif/webp を使用してください）。';
            exit;
        }

        // 保存用のランダムファイル名を作成（拡張子維持）
        $image_name = uniqid(mt_rand(), true) . '.' . $ext;

        // 保存ディレクトリの絶対パス
        $save_dir = dirname(__FILE__) . '/../../assets/img/quiz/';
        if (!is_dir($save_dir)) {
            // フォルダがない場合は作成（再帰的）
            if (!mkdir($save_dir, 0777, true) && !is_dir($save_dir)) {
                echo '画像保存用ディレクトリを作成できませんでした。';
                exit;
            }
        }

        $destination = $save_dir . $image_name;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
            echo '画像の保存に失敗しました。';
            exit;
        }
        @chmod($destination, 0644);
    }

    try {
        // 以降の処理をトランザクションでまとめる
        $pdo->beginTransaction();

        // 質問の内容を更新
        if ($image_name === null) {
            // 画像を更新しない場合、既存の画像名を使用
            $stmt = $pdo->prepare('UPDATE questions SET content = ?, supplement = ? WHERE id = ?');
            $stmt->execute([$question_content, $supplement, $id + 1]);
        } else {
            // 画像がアップロードされている場合、新しい画像名で更新
            $stmt = $pdo->prepare('UPDATE questions SET content = ?, image = ?, supplement = ? WHERE id = ?');
            $stmt->execute([$question_content, $image_name, $supplement, $id + 1]);
        }

// 選択肢の更新（選択肢1）
$stmtChoice = $pdo->prepare('UPDATE choices SET name = ?, valid = ? WHERE question_id = ? AND id = ?');
$stmtChoice->execute([$choice1, $correct_answer === '1' ? 1 : 0, $id, 1]);

// 選択肢2
$stmtChoice->execute([$choice2, $correct_answer === '2' ? 1 : 0, $id, 2]);

// 選択肢3
$stmtChoice->execute([$choice3, $correct_answer === '3' ? 1 : 0, $id, 3]);
        $pdo->commit();

        // 成功したら一覧へ
        header('Location: ../index.php');
        exit;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo 'エラーが発生しました: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        exit;
    }
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
        <img src="../../assets/img/logo.svg" alt="">
        <a href="#" class=" hover:text-gray-600">ログアウト</a>
    </header>

    <main class="flex">
        <nav class="bg-[#e8ddc9] flex flex-col w-36 h-screen gap-3 pl-3 pt-5 ">
            <a href="" class="text-blue-500 hover:text-blue-700">ユーザー登録</a>
            <a href="../index.php" class="text-blue-500 hover:text-blue-700">問題一覧</a>
            <a href="./create.php" class=" text-blue-500 hover:text-blue-700">問題作成</a>
        </nav>
        <form action="" method="POST" enctype="multipart/form-data" class="pl-8 pt-6 border-black pr-5 flex flex-col w-full ">
    <h1 class="text-5xl mb-8">問題編集</h1>
    <label for="question_content" class="mb-2">問題文：</label>
    <input type="text" name="question_content" placeholder="問題文を入力してください" class="p-1 border border-gray-300 rounded mb-6" value="<?= $questions[$id]["content"]; ?>">
    
    <label for="choice1" class="mb-2">選択肢：</label>
    <div class="flex mb-6">
        <input type="text" name="choice1" placeholder="選択肢1を入力してください" class="flex-1 p-1 border border-gray-300 rounded" value="<?= $questions[$id]["choices"][0]['name']; ?>">
        <input type="text" name="choice2" placeholder="選択肢2を入力してください" class="flex-1 p-1 border border-gray-300 rounded" value="<?= $questions[$id]["choices"][1]['name']; ?>">
        <input type="text" name="choice3" placeholder="選択肢3を入力してください" class="flex-1 p-1 border border-gray-300 rounded" value="<?= $questions[$id]["choices"][2]['name']; ?>">
    </div>
    
    <label for="correct_answer" class="mb-2">正解の選択肢</label>
    <div class="mb-6 flex">
        <label for="answer1" class="pr-2 flex gap-2">
            <input type="radio" id="answer1" name="correct_answer" value="1" <?= $questions[$id]["choices"][0]['valid'] == 1 ? 'checked' : ''; ?>><p>選択肢1</p>
        </label>
        <label for="answer2" class="pr-2 flex gap-2">
            <input type="radio" id="answer2" name="correct_answer" value="2" <?= $questions[$id]["choices"][1]['valid'] == 1 ? 'checked' : ''; ?>><p>選択肢2</p>
        </label>
        <label for="answer3" class="pr-2 flex gap-2">
            <input type="radio" id="answer3" name="correct_answer" value="3" <?= $questions[$id]["choices"][2]['valid'] == 1 ? 'checked' : ''; ?>><p>選択肢3</p>
        </label>
    </div>
    
    <label for="image" class="mb-2">問題の画像</label>
    <input type="file" name="image" accept="image/*" class="p-1 border border-gray-200 rounded mb-6" value="<?php $questions[$id]["image"]; ?>">
    
    <label for="supplement" class="mb-2">補足：</label>
    <input type="text" name="supplement" placeholder="補足を入力してください" class="p-1 border border-gray-300 rounded mb-7" value="<?php $questions[$id]["supplement"]; ?>">
    
    <input type="submit" value="更新" class="bg-[#76B5AB] text-white p-1">
    
</form>
    </main>
</body>
</html>