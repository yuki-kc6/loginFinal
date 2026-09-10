<?php
require_once "userClass.php";
$user=new userClass();

	
	if (isset($_COOKIE['remember_token'])) {
		$username=$user->tokenUser($_COOKIE['remember_token']);

		  if($username != false){
		  $_SESSION['username'] = $username;
 		   header("Location: MyPage.php");
    	   	   exit;
		}
	}
	if(isset($_SESSION['username'])){
	 	header("Location: MyPage.php");
    	  	exit;
	}
	$username=$_POST['username'] ?? "";
	$password=$_POST['password']?? "";
	$remember= $_POST['remember']??"";
	$error = "";

	if($_SERVER["REQUEST_METHOD"] === "POST"){
		

		if($user->login($username,$password)){
			
			if ($_POST['remember']) {
				$user->rememberToken($username);
			}			

			$_SESSION['username'] = $username;
		       header("Location: MyPage.php");
	        	exit;
			
		}
		else{
			$errors=$user->getErrors();
			$error = $errors[0] ?? "メールアドレスかパスワードが違います";
		}
	}
	 

?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
   <div class="site-logo">KAITE</div>
    <form action="login.php" method="post">
        <h2>ログイン</h2>
        <label for="username">メールアドレス:</label>
        <input type="text" value="<?= htmlspecialchars($username) ?>" id="username" name="username" required>
        <label for="password">パスワード:</label>
        <input type="password" id="password" name="password" required>
	<label for="text">ログイン情報を記憶しますか</label>
	<input type="checkbox" name="remember" value="true">	
        <input type="submit" value="ログイン">		
	<?php if ($error != ""): ?>
    		<p style="color:red;">
        	<?= htmlspecialchars($error) ?>
		    </p>
		<?php endif; ?>
    </form>
      <div class="form-footer">
	 アカウントをお持ちでない方は<a href="register.php" class="btn btn-outline">新規登録</a>
　　 </div>
</body>
</html>


