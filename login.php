<?php
	session_start();

	// DB接続情報
	define( "DSN", "mysql:dbname=ge3a_db;host=127.0.0.1" );
	define( "DBUSER", "ge3a" );
	define( "DBPASS", "ge3a" );

	
	if (isset($_COOKIE['remember_token'])) {
		$username=token_user($_COOKIE['remember_token']);

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
		

		if(login($username,$password)){
			
			if ($_POST['remember']) {
				remember_token($username);
			}			

			$_SESSION['username'] = $username;

		       header("Location: MyPage.php");
	        	exit;
			
		}
		else{
			$error="メールアドレスかパスワードが違います";
		}
	}
	 

	function login($username,$password){ //login２を関数にする
	
		try{
			// DB接続＆PDO生成
			$pdo = new PDO( DSN, DBUSER, DBPASS );
			// プレースホルダは:user_account、:user_password

		
			$sql = "SELECT user_password FROM user_tbl WHERE user_account = :user_account ";
			

			$stmt = $pdo->prepare( $sql );	// 事前準備で解析・コンパイル

			$stmt->bindValue( ":user_account", $username );
		
			$stmt->execute();	// 実行
			
			$row = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($row === false) {
			    return false;
			}

			$passwordHash = $row["user_password"];

			if(password_verify($password,$passwordHash)){
				return true;
			}else{
				return false;
			}
				


		
		
		
		}catch( PDOException $ex ){
			die( "Error:" . $ex->getMessage() );
		}
	}

	function remember_token($username){
		try{
			// DB接続＆PDO生成
			$pdo = new PDO( DSN, DBUSER, DBPASS );

			$sql="SELECT user_id FROM user_tbl WHERE user_account=:user_account";
			$stmt=$pdo->prepare($sql);
			$stmt->bindValue(":user_account",$username);
			$stmt->execute();


			$row=$stmt->fetch();
			if ($row === false) {
 			   return false;
			}
			$userid=$row[0];

			$token=bin2hex(random_bytes(32));
			$createDate=date("Y-m-d H:i:s");
			$expiresDate=date("Y-m-d H:i:s",strtotime("+1 day"));

			$sql ="INSERT INTO remember_token_tbl(rt_user_id,rt_remember_token,rt_create_at,rt_expires_at)  VALUES(:user_id,:remember_token, :create_at,:expires_at)";

		 	$stmt = $pdo->prepare($sql);//事前準備で解析・コンパイル
	
			$stmt->bindValue(":user_id",$userid);
			$stmt->bindValue(":remember_token" , $token); 
			$stmt->bindValue(":create_at" , $createDate); 
			$stmt->bindValue(":expires_at" , $expiresDate); 			
		
			$stmt->execute();

			setcookie("remember_token",$token,$expires_at);

		}catch( PDOException $ex ){
			die( "Error:" . $ex->getMessage() );
		}
	}

	function token_user($token){
				try{
			// DB接続＆PDO生成
			$pdo = new PDO( DSN, DBUSER, DBPASS );

			$sql="SELECT rt_user_id FROM remember_token_tbl WHERE rt_remember_token=:remember_token";
			$stmt=$pdo->prepare($sql);
			$stmt->bindValue(":remember_token",$token);
			$stmt->execute();

			$row=$stmt->fetch();
			if ($row === false) {
			    return false;
			}

			$userid=$row[0];


			$sql ="SELECT user_account FROM user_tbl WHERE user_id=:user_id";

		 	$stmt = $pdo->prepare($sql);//事前準備で解析・コンパイル
	
			$stmt->bindValue(":user_id",$userid); 			
		
			$stmt->execute();

			$row=$stmt->fetch();
			$user_account=$row[0];			

			return $user_account;

		}catch( PDOException $ex ){
			die( "Error:" . $ex->getMessage() );
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


