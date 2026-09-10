<?php
	session_start();
		// DB接続情報
		define( "DSN", "mysql:dbname=ge3a_db;host=127.0.0.1" );
		define( "DBUSER", "ge3a" );
		define( "DBPASS", "ge3a" );

	class userClass{//ユーザーの機能をクラス化
	
	private $name;//ユーザーネーム

	private $error;//エラー内容

    private $pdo;
  
	public function __construct(){//コンストラクタ

    //userClassでデータベースと接続しセッションを開始する
	
    // DB接続＆PDO生成
		try {
        $this->pdo = new PDO(DSN, DBUSER, DBPASS);
		} catch (PDOException $ex) {
		die("DB接続エラー:" . $ex->getMessage());
		}
	   $this->error=[];
	}

	public function __destruct(){//デストラクタ

	}

	public function login($username,$password){//ログイン機能
		try{
			// プレースホルダは:user_account、:user_password
			$sql = "SELECT user_password FROM user_tbl WHERE user_account = :user_account ";
			$stmt = $this->pdo->prepare( $sql );	// 事前準備で解析・コンパイ
			$stmt->bindValue( ":user_account", $username );
			$stmt->execute();	// 実行
			
			$row = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($row === false) {
				$this->setError("メールアドレスかパスワードが違います");
			    return false;
			}

			$passwordHash = $row["user_password"];

			if(password_verify($password,$passwordHash)){
				return true;
			}else{
				$this->setError("メールアドレスかパスワードが違います");
				return false;
			}
				
		}catch( PDOException $ex ){
			die( "Error:" . $ex->getMessage() );
		}
	}

  public function register($username,$password){//ユーザー登録機能										
     $username=trim($username);

	if(empty($username))	{
		$this->setError("入力してください<br />");
	}else if(!preg_match('/^[!-~]+@[!-~]+$/',$username)){
		$this->setError("メールアドレスではありません<br />");
	}else if(strpos($username," ")==true||preg_match('/^[^ -~｡-ﾟ]+$/',$username)){
		$this->setError("使用できない文字が含まれています<br />");
	}

	$password=trim($password);


	if(empty($password))	{
		$this->setError("入力してください<br />");
	}

	if(strlen($password)<8||preg_match('/[A-Z]/', $password)==false||preg_match('/[a-z]/', $password)==false){
		$this->setError("パスワードは8文字以上にして大文字と小文字を含めてください<br />");
	}

	if(preg_match('/[^ -~｡-ﾟ]/',$password)==true){
		$this->setError("使用できない文字が含まれています<br />");
	}


	if ($this->hasErrors()) {
    		return false;
	}
	try{

	$sqlcheck ="SELECT* FROM user_tbl WHERE user_account = :user_account" ;
	$stmt = $this->pdo->prepare($sqlcheck);
	$stmt->bindValue(":user_account" , $username); //？の場合は、bindValue(1,$username)
	$stmt->execute();
		
	$result = $stmt->fetchAll();
		if( count($result) == 1 ){
			$this->setError("使われたメールアドレスです<br />");
		}
		else{
			$sqlreg ="INSERT INTO user_tbl(user_account,user_password)  VALUES(:user_account , :user_password)";

			$passwordHash=password_hash($password,PASSWORD_DEFAULT);

		 	$stmt = $this->pdo->prepare($sqlreg);//事前準備で解析・コンパイル
	
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

  public function rememberToken($username){  
      try{
			$sql="SELECT user_id FROM user_tbl WHERE user_account=:user_account";
			$stmt=$this->pdo->prepare($sql);
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

		 	$stmt =$this->pdo->prepare($sql);//事前準備で解析・コンパイル
	
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

  public function tokenUser($token){
    try{
			$sql="SELECT rt_user_id FROM remember_token_tbl WHERE rt_remember_token=:remember_token";
			$stmt=$this->pdo->prepare($sql);
			$stmt->bindValue(":remember_token",$token);
			$stmt->execute();

			$row=$stmt->fetch();
			if ($row === false) {
			    return false;
			}

			$userid=$row[0];


			$sql ="SELECT user_account FROM user_tbl WHERE user_id=:user_id";

		 	$stmt = $this->pdo->prepare($sql);//事前準備で解析・コンパイル
	
			$stmt->bindValue(":user_id",$userid); 			
		
			$stmt->execute();

			$row=$stmt->fetch();
			$user_account=$row[0];			

			return $user_account;

		}catch( PDOException $ex ){
			die( "Error:" . $ex->getMessage() );
		}
	
  }

 //エラーをセットする
  public function setError($error){
    	$this->error[]=$error;
  }

    // エラーがあるかどうか
    public function hasErrors(){
        return count($this->error) > 0;
    }

    // 溜まったエラーを全部取得する
    public function getErrors(){
        return $this->error;
    }
}
?>
