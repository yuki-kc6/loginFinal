<?php
	require_once "userClass.php";
	$user = new userClass();
	// POSTデータを受け取り
	$username = $_POST["username"]??"";
	$password = $_POST["password"]??"";
	
	if($_SERVER["REQUEST_METHOD"]==="POST"){

	if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($user->register($username, $password)) {
        $_SESSION['username'] = $username;
        header("Location:Mypage.php");
        exit;
    }
}

$errors = $user->getErrors();


	

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登録</title>
    <link rel="stylesheet" href="style.css">
    </head>
<body>
   <div class="site-logo">KAITE</div>
    <form action="register.php" method="post">
        <h2>登録</h2>
        <label for="username">メールアドレス:</label>
        <input type="text" value="<?= htmlspecialchars($username) ?>" id="username" name="username" required>
        <label for="password">パスワード:</label>
        <input type="password" id="password" name="password" required>
	  <?php if (!empty($errors)): ?>
           <?php foreach ($errors as $e): ?>
               <p><?= $e ?></p>
           <?php endforeach; ?>
   <?php endif; ?>
        <input type="submit" value="登録">
    </form>
      <div class="form-footer">
	 既にアカウントをお持ちの方は<a href="login.php" class="btn btn-outline">ログイン</a>
　　 </div>
</body>
</html>
