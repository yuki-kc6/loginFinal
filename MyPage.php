<?php
	session_start();
	
	if(!isset($_SESSION['username'])){
		header("Location: login.php");
		exit;
	}

	$username=$_SESSION['username']; 


	if(isset($_POST['logout']))
	{
		session_unset();
		setcookie("remember_token", "", time() - 3600);
		header("Location: login.php");
		exit;
	}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>マイページ</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="site-logo">KAITE</div>

    <div class="container">
        <div class="profile-header">
            <div>
                <div class="profile-name"><?= htmlspecialchars($username) ?>さん</div>
                <div class="profile-bio">ようこそ、マイページへ</div>
            </div>
        </div>

        <form action="MyPage.php" method="post">
            <input type="submit" name="logout" value="ログアウト" class="btn btn-outline">
        </form>
    </div>
</body>
</html>


