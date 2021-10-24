<?php

session_start();
//クロスサイトリクエストフォージェリ（CSRF）対策
// $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
// $token = $_SESSION['token'];
//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

// unset($_SESSION['id']);
// unset($_SESSION['name']);

$_SESSION = array();

$errors = array();

//DB接続
$dsn = "mysql:host=localhost;dbname=*****;charset=utf8mb4";
$user = "*********";
$password = "********";



try{
    $pdo = new PDO($dsn, $user, $password,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
}catch(PDOException $e){
    echo "接続失敗". $e->getMessage();
    exit;

}


//ログインボタンが押されたら
if (isset($_POST["submit"])) {

    //if (isset($_POST["token"]) && $_POST["token"] === $_SESSION['token']) {

        //エラー処理
        //メールアドレスが空欄の場合
        if (empty($_POST['mail']) || (empty($_POST['ps']))) {
            $errors['mail_password'] = 'メールアドレス・パスワードが未入力です。';
            
            //メールアドレス・パスワードが一致しているか
        }else{
        
            $mail = isset($_POST['mail']) ? $_POST['mail'] : NULL;
            $ps = isset($_POST['ps']) ? $_POST['ps'] : NULL;



            //パスワードをを判定する
            $sql = 'SELECT id, name ,password FROM user WHERE mail = :mail';
            $stmt = $pdo->prepare($sql);
            $stmt ->bindValue(':mail', $mail, PDO::PARAM_STR);
            $stmt -> execute();

            $results = $stmt->fetch();

            
            //DBにメールアドレスがなかった場合
            if(empty($results)){

                $errors['mail_check'] = 'メールアドレス、もしくはパスワードが違います';
            
            }else{

                // パスワードとハッシュが一致している場合
                if(password_verify($ps, $results['password'])){

                    //question_postで使うセッション
                    $_SESSION['id'] =$results['id'];
                    $_SESSION['name'] =$results['name'];

                    //ログイン日をアップデート
                    $sql = "UPDATE user SET updated_at = now() WHERE name = :name AND password = :password";
                    $stmt = $pdo->prepare($sql);
                    $stmt ->bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt ->bindParam(':password', $ps, PDO::PARAM_STR);
                    $stmt->execute();

                    
                    //質問ジェネレーターにリダイレクト
                    header("Location: http://localhost:8888/generator_4/question_post.php");


                //パスワードが一致していない場合
                }else{
                    $errors['password_check'] = 'メールアドレス、もしくはパスワードが違います';
                    
                }
                
            }

        }
    
}
                    
?>

<!DOCTYPE html>
<html lang = "ja">
<head>
    <meta charset="UTF-8">
    <title>ログイン - 質問ジェネレータ</title>
    <link rel="stylesheet" href="sign_up.css">
</head>

<body>
<header>
    <!-- タイトル -->
    <div class="area-header-title">質問ジェネレータ</div>

    <!-- ナビゲーション -->
    <nav class="area-header-nav">
        <ul class="list-header-nav">
            <li class="link-header-nav"><a href="http://localhost:8888/generator_4/generator_4.php">質問を生成する</a></li>
            <li class="link-header-nav"><a href="http://localhost:8888/generator_4/question_post.php">質問を投稿する</a></li>

            <?php if(isset($_SESSION['name'])):?>
            <li class="link-header-nav">
                <a href="#"><span class="login-name"><?php echo $_SESSION['name'];?></span>でログインしています</a></li>
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
    <div class="a-title">質問ジェネレータ　ログイン</div>

    <div class="a-msg">質問を投稿するには、ログインするか新規登録が必要です。アカウントをまだ取得していない方は
        <a href="http://localhost:8888/generator_4/signup_mail_4.php">新規登録 </a>
        から新しく登録をお願いします。登録・利用ともに完全無料です。</div>

     

    <!-- ボックスフォーム -->
    <div class="box-form-login">


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
        
        <!-- ログインフォーム -->
        <div class="box-form-mail">
            
            <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="post">

                <div class="box-form-text">メールアドレス</div>
                <input class="form-mail" type="text" name="mail" value="">

                <div class="box-form-text">パスワード</div>
                <input class="form-mail" type="text" name="ps" value="">

                <button class="form-button-login" type="submit" name="submit">ログイン</button>
               
            </form>
        </div>
    </div>
</div>

</article>
</body>
</html>