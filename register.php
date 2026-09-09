<?php
	session_start();
	// POSTデータを受け取り
	$username = $_POST["username"]??"";

	$password = $_POST["password"]??"";
	
	
	// DB接続情報
	define( "DSN", "mysql:dbname=ge3a_db;host=127.0.0.1" );
	define( "DBUSER", "ge3a" );
	define( "DBPASS", "ge3a" );

	if($_SERVER["REQUEST_METHOD"]==="POST"){

	$errors=[];


	try{
	

	$username=trim($username);


	if(empty($username))	{
		$errors[] =  "入力してください<br />";

	}

	else if(!preg_match('/^[!-~]+@[!-~]+$/',$username)){
		$errors[] = "メールアドレスではありません<br />";

	}
	else if(strpos($username," ")==true||preg_match('/^[^ -~｡-ﾟ]+$/',$username)){
		$errors[] =  "使用できない文字が含まれています<br />";
	}



	$password=trim($password);


	if(empty($password))	{
		$errors[] =  "入力してください<br />";

	}


	if(strlen($password)<8||preg_match('/[A-Z]/', $password)==false||preg_match('/[a-z]/', $password)==false){
		$errors[] = "パスワードは8文字以上にして大文字と小文字を含めてください<br />";
	}

	if(preg_match('/^[^ -~｡-ﾟ]+$/',$password)==true){
		$errors[] = "使用できない文字が含まれています<br />";
	}


	if (count($errors) > 0) {
    		$num = count($errors);
	}

	else{
	//DB接続&PDO生成
	$pdo = new PDO(DSN,DBUSER,DBPASS);

	$sqlcheck ="SELECT* FROM user_tbl WHERE user_account = :user_account" ;
	
	$stmt = $pdo->prepare($sqlcheck);

	$stmt->bindValue(":user_account" , $username); //？の場合は、bindValue(1,$username)

	$stmt->execute();


	$result = $stmt->fetchAll();
		if( count($result) == 1 ){
			$errors[]="使われたメールアドレスです<br />";
		}
		else{

			$sqlreg ="INSERT INTO user_tbl(user_account,user_password)  VALUES(:user_account , :user_password)";

			$passwordHash=password_hash($password,PASSWORD_DEFAULT);

		 	$stmt = $pdo->prepare($sqlreg);//事前準備で解析・コンパイル
	
			$stmt->bindValue(":user_account" , $username); //？の場合は、bindValue(1,$username)
			$stmt->bindValue(":user_password" , $passwordHash); //？の場合は、bindValue(2,$username)

			if($stmt->execute()){//実行
				$_SESSION['username']=$username;
				header("Location:Mypage.php");
				exit;
			}

		}
	}
	}catch(PDOException $ex){
		die("Error:". $ex->getMessage());
	}
  }



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
       </div>
   <?php endif; ?>
        <input type="submit" value="登録">
    </form>
      <div class="form-footer">
	 既にアカウントをお持ちの方は<a href="login.php" class="btn btn-outline">ログイン</a>
　　 </div>
</body>
</html>
