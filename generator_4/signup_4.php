
<?php

session_start();
//クロスサイトリクエストフォージェリ（CSRF）対策
// $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
// $token = $_SESSION['token'];

// //クロスサイトリクエストフォージェリ（CSRF）対策
// $toke_byte = openssl_random_pseudo_bytes(16);
// $csrf_token = bin2hex($toke_byte);
// // 生成したトークンをセッションに保存します
// $_SESSION['csrf_token'] = $csrf_token;



//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

//成功・エラーメッセージの初期化
$errors = array();

//DB接続
$dsn = "mysql:host=localhost;dbname=********;charset=utf8mb4";
$user = "********";
$password = "********";


try{
    $pdo = new PDO($dsn, $user, $password,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

}catch(PDOException $e){
    echo "接続失敗". $e->getMessage();
    exit;

}

//テーブルを作成する
$sql = "CREATE TABLE IF NOT EXISTS user"
." ("
."id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,"
."name VARCHAR(128) NOT NULL,"
."password VARCHAR(128) NOT NULL,"
."mail VARCHAR(128) NOT NULL,"
."status INT(1) NOT NULL DEFAULT 2,"
."created_at DATETIME,"
."updated_at DATETIME"
.");";

$stmt = $pdo->query($sql);



if(empty($_GET)) {
	header("Location: registration_mail");
	exit();
}else{
	//GETデータを変数に入れる
	$urltoken = isset($_GET["urltoken"]) ? $_GET["urltoken"] : NULL;
	//メール入力判定
	// if ($urltoken == ''){
	// 	$errors['urltoken'] = "トークンがありません。";
	// }else{
		
        // DB接続	
        //flagが0の未登録者 or 仮登録日から24時間以内
        $sql = "SELECT mail FROM pre_user WHERE urltoken=(:urltoken) AND flag =0 AND date > now() - interval 24 hour";
        $stm = $pdo->prepare($sql);
        $stm->bindValue(':urltoken', $urltoken, PDO::PARAM_STR);
        $stm->execute();
        
        //レコード件数取得
        $row_count = $stm->rowCount();
        
        //24時間以内に仮登録され、本登録されていないトークンの場合
        if( $row_count ==1){
            $mail_array = $stm->fetch();
            $mail = $mail_array["mail"];
            $_SESSION['mail'] = $mail;
        }else{
            $errors['urltoken_timeover'] = "このURLはご利用できません。有効期限が過ぎたかURLが間違えている可能性がございます。もう一度登録をやりなおして下さい。";
            //データベース接続切断
            $stm = null;
        }
	
}




// 送信(submit)押した後の処理
if(isset($_POST['submit'])){

    // if (isset($_POST["csrf_token"]) && $_POST["csrf_token"] !== $_SESSION['csrf_token']) {

    //     $errors['token'] = 'トークンエラーです';
            
    // }else{


        //エラー処理
        //名前が空欄の場合
        if (empty($_POST['name'])) {
            $errors['name'] = '名前を入力してください';
        }
        
        // パスワードが空欄の場合
        if (empty($_POST['ps']) || empty($_POST['ps_co'])){
            $errors['ps'] = 'パスワードを入力してください';
        

        //パスワードが一致しない場合
        }else{
            $name = isset($_POST['name']) ? $_POST['name'] : NULL;
            $ps = isset($_POST['ps']) ? $_POST['ps'] : NULL;
            $ps_co = isset($_POST['ps_co']) ? $_POST['ps_co'] : NULL;
            
            if($ps != $ps_co){
                $errors['ps_co'] = '入力されたパスワードが一致しません';
                
            }


            //ユーザー名がすでに登録されていないか確認
            //DB確認        
            $sql = "SELECT id FROM user WHERE name=:name";
            $stm = $pdo->prepare($sql);
            $stm->bindValue(':name', $name, PDO::PARAM_STR);
            
            $stm->execute();
            $result = $stm->fetch(PDO::FETCH_ASSOC);


            //user テーブルに同じメールアドレスがある場合、エラー表示
            if(isset($result["id"])){
                $errors['user_check'] = "このユーザー名はすでに利用されています";
            }
            
        
    }
        

        //エラーがない場合、userテーブルにインサート
        if (count($errors) === 0){//===でデータ型まで一致しているかチェック


            //パスワードのハッシュ化(暗号化的な)
            //password_hash(ハッシュ化したいパスワード , アルゴリズム(どの仕組みでハッシュ化するか) , オプション)
            $password_hash =  password_hash($ps, PASSWORD_DEFAULT);

            //userにレコードをいれる
            
            $sql = "INSERT INTO user (name,password,mail,status,created_at,updated_at) VALUES (:name,:password_hash,:mail,1,now(),now())";
            $stm = $pdo->prepare($sql);
            $stm->bindValue(':name', $name, PDO::PARAM_STR);
            $stm->bindValue(':mail', $_SESSION['mail'], PDO::PARAM_STR);
            $stm->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            $stm->execute();

            //pre_userのflagを1にする(トークンの無効化)
            $sql = "UPDATE pre_user SET flag=1 WHERE mail=:mail";
            $stm = $pdo->prepare($sql);
            //pre_userのプレースホルダへ実際の値を設定する
            $stm->bindValue(':mail', $mail, PDO::PARAM_STR);
            $stm->execute();



            header( "refresh:1;url=http://localhost:8888/generator_4/signup_co.php" );
                
        }

}

?>

<!DOCTYPE html>
<html lang = "ja">
<head>
    <meta charset="UTF-8">
    <title>新規登録 - 質問ジェネレータ</title>
    <link rel="stylesheet" href="generator.css">
</head>

<body>
<header>
    <!-- タイトル -->
    <div class="area-header-title">質問ジェネレータ</div>

    <!-- ナビゲーション -->
    <nav class="area-header-nav">
        <ul class="list-header-nav">
            <li class="link-header-nav"><a href="http://localhost:8888/generator_4/generator_4.php">質問生成</a></li>
            <li class="link-header-nav"><a href="http://localhost:8888/generator_4/question_post.php">質問投稿</a></li>

            <?php if(isset($_SESSION['name'])):?>
            <li class="link-header-nav">
                <a href="#"><span class="login-name"><?php echo $_SESSION['name'];?></span>でログイン中</a></li>
            <li class="button-header-nav"><a href="http://localhost:8888/generator_4/login_4.php">ログアウト</a></li>
            <?php else: ?>
            <li class="button-header-nav"><a href="http://localhost:8888/generator_4/signup_mail_4.php">新規登録</a></li>
            <li class="button-header-nav"><a href="http://localhost:8888/generator_4/login_4.php">ログイン</a></li>
            <?php endif; ?>
            
        </ul>
    </nav>
</header>



<!-- 記事 -->
<article>
<div class="container">
    <div class="a-title">質問ジェネレータ　新規登録 - 登録情報の入力</div>

    <div class="a-msg">仮登録ありがとおうございました。以下のフォームから登録ユーザー情報を入力すると登録が完了となります。ユーザー名とパスワードはログイン時に必要となりますので、お忘れのないようお願いします。
        <br><br><br> 
        ユーザー名はと投稿した質問が表示された際に、投稿者として表示されます。（非公開にすることも可能）
    </div>

     

    <!-- ボックスフォーム -->
    <div class="box-form-signup">
        <div class="box-form-msg">メールアドレスの入力▶︎仮登録メールの送受信▶︎登録情報の入力</div>

        <!-- エラーメッセージ -->
        <?php if(count($errors) > 0):?>
            <ul class="error-msg">
                
                <?php foreach($errors as $value):?>
                    <li>
                        <?php echo $value;?>
                    </li>
                <?php endforeach; ?>
        
            </ul>
        <?php endif; ?>

        <!-- メール入力フォーム -->
        <div class="box-form-mail">
            
            <form action="" method="post">
            <input type="hidden" name="csrf_token" value="<?=$csrf_token?>">

                <div class="box-form-text">ユーザー名</div>
                <input class="form-mail" type="text" name="name" value="">

                <div class="box-form-text">パスワード</div>
                <input class="form-mail" type="password" name="ps" value="">

                <div class="box-form-text">パスワードの確認</div>
                <input class="form-mail" type="password" name="ps_co" value="">

                <button class="form-button" type="submit" name="submit">送信</button>
               
            </form>
        </div>
    </div>
</div>

</article>
</body>
</html>