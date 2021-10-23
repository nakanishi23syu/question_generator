<?php

session_start();
// //クロスサイトリクエストフォージェリ（CSRF）対策
// $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
// $token = $_SESSION['token'];

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');


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

//エラーメッセージの初期化
$errors = array();
$message = "";


//テーブルを作成する
$sql = "CREATE TABLE IF NOT EXISTS pre_user"
." ("
."id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,"
."urltoken VARCHAR(128) NOT NULL,"
."mail VARCHAR(50) NOT NULL,"
."date DATETIME NOT NULL,"
."flag TINYINT(1) NOT NULL DEFAULT 0"
.");";
$stmt = $pdo->query($sql);





//送信ボタンクリックした後の処理
if (isset($_POST['submit'])) {

    //if (isset($_POST["token"]) && $_POST["token"] === $_SESSION['token']) {
        
        //エラー処理
        //メールアドレス空欄の場合
        if (empty($_POST['mail']) & empty($_POST['mail-co'])) {
            $errors['mail'] = 'メールアドレスを入力してください';
        }else{

            
            $pre_mail = isset($_POST['mail']) ? $_POST['mail'] : NULL;
            $pre_mail_co = isset($_POST['mail-co']) ? $_POST['mail-co'] : NULL;
            $_SESSION['mail'] = $pre_mail;
            
            if($pre_mail != $pre_mail_co){
                $errors['mail-co'] = '入力されたメールアドレスが一致しません';
                
                 //メールアドレス構文チェック
                if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $pre_mail)){
                    $errors['mail_check'] = "メールアドレスの形式が正しくありません";
                }
                
            }

            //DB確認        
            $sql = "SELECT id FROM user WHERE mail=:mail";
            $stm = $pdo->prepare($sql);
            $stm->bindValue(':mail', $pre_mail, PDO::PARAM_STR);
            
            $stm->execute();
            $result = $stm->fetch(PDO::FETCH_ASSOC);


            //user テーブルに同じメールアドレスがある場合、エラー表示
            if(isset($result["id"])){
                $errors['user_check'] = "このメールアドレスはすでに利用されています";
                
            }
            
        }



    //エラーがない場合、pre_userテーブルにインサート
        if (count($errors) === 0){//===でデータ型まで一致しているかチェック
            $urltoken = hash('sha256',uniqid(rand(),1));
            $url = "http://localhost:8888/generator_4/signup_4.php?urltoken=".$urltoken;
           
            //pre_userテーブルにインサート
            $sql = "INSERT INTO pre_user (urltoken, mail, date, flag) VALUES (:urltoken, :mail, now(), '0')";
            $stm = $pdo->prepare($sql);
            $stm->bindValue(':urltoken', $urltoken, PDO::PARAM_STR);
            $stm->bindValue(':mail', $pre_mail, PDO::PARAM_STR);
            $stm->execute();
            $pdo = null;




            //メールを送る
                            
            require 'src/Exception.php';
            require 'src/PHPMailer.php';
            require 'src/SMTP.php';
            require 'setting.php';

            // PHPMailerのインスタンス生成
            $mail = new PHPMailer\PHPMailer\PHPMailer();

            $mail->isSMTP(); // SMTPを使うようにメーラーを設定する
            $mail->SMTPAuth = true;
            $mail->Host = MAIL_HOST; // メインのSMTPサーバー（メールホスト名）を指定
            $mail->Username = MAIL_USERNAME; // SMTPユーザー名（メールユーザー名）
            $mail->Password = MAIL_PASSWORD; // SMTPパスワード（メールパスワード）
            $mail->SMTPSecure = MAIL_ENCRPT; // TLS暗号化を有効にし、「SSL」も受け入れます
            $mail->Port = SMTP_PORT; // 接続するTCPポート

            // メール内容設定
            $mail->CharSet = "UTF-8";
            $mail->Encoding = "base64";
            $mail->setFrom(MAIL_FROM,MAIL_FROM_NAME);
            $mail->addAddress($pre_mail, $pre_mail); //受信者（送信先）を追加する//変更
            //$mail->addReplyTo('xxxxxxxxxx@xxxxxxxxxx','返信先');
            //$mail->addCC('xxxxxxxxxx@xxxxxxxxxx'); // CCで追加
            //$mail->addBcc('xxxxxxxxxx@xxxxxxxxxx'); // BCCで追加
            $mail->Subject = MAIL_SUBJECT; // メールタイトル
            $mail->isHTML(true);    // HTMLフォーマットの場合はコチラを設定します
            $body =
            'この度は「質問ジェネレータ」に仮会員登録して頂きまして<br/>
            誠にありがとうございます。<br/><br/>
            
            ご本人様確認のため、下記URLへ「24時間以内」にアクセスし<br/>
            アカウントの本登録を完了させて下さい。<br/><br/>
            【質問ジェネレータ 登録確認ページ】<br/>'
            .$url.
            
            
            '<br/><br/>※当メール送信後、24時間を超過しますと、セキュリティ保持のため有効期限切れとなります。<br/>
            　その場合は再度、最初からお手続きをお願い致します。<br/><br/>
            
            ※お使いのメールソフトによってはURLが途中で改行されることがあります。<br/>
            　その場合は、最初の「http://」から末尾の英数字までをブラウザに<br/>
            　直接コピー＆ペーストしてアクセスしてください。<br/><br/>
            
            ※当メールは送信専用メールアドレスから配信されています。<br/>
            　このままご返信いただいてもお答えできませんのでご了承ください。<br/><br/>
            
            ※当メールに心当たりの無い場合は、誠に恐れ入りますが<br/>
            　破棄して頂けますよう、よろしくお願い致します。<br/>';

            $mail->Body  = $body; // メール本文

            // メール送信の実行
            if(!$mail->send()) {
                echo 'メッセージは送られませんでした！';
                echo 'Mailer Error: ' . $mail->ErrorInfo;
            } else {
                // セッション変数を全て解除
                

                // //クッキーの削除(クッキー名"PHPSESSID"がセットされていたら削除)
                // if (isset($_COOKIE["PHPSESSID"])) {
                //     //クッキー名"PHPSESSID"に　''(空白)をセットする
                //     //クッキーの有効期限は今から-1800秒後（実質削除？）
                //     //最後の'/'の意味はわからないので時間があるときに調べます。
                //     setcookie("PHPSESSID", '', time() - 1800, '/');
                // }

                //セッションを破棄する
            
                header( "refresh:1;url=http://localhost:8888/generator_4/signup_mail_co.php" );
                
            }
        }
    //}
}

?>




<!DOCTYPE html>
<html lang = "ja">
<head>
    <meta charset="UTF-8">
    <title>新規登録 - 質問ジェネレータ</title>
    <link rel="stylesheet" href="sign_up.css">
</head>

<body>
<header>
    <!-- タイトル -->
    <div class="area-header-title">新規登録 - 質問ジェネレータ</div>

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
    <div class="a-title">質問ジェネレータ　新規登録</div>

    <div class="a-msg">質問ジェネレータでは様々な人に質問を登録していただきたいと思っています。様々な質問が登録されることで、誰かに話題提供し、気まずい時間をなくすことができると思っています。</div>

     

    <!-- ボックスフォーム -->
    <div class="box-form">
        
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
            
            <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="post">

                <div class="box-form-text">メールアドレス</div>
                <input class="form-mail" type="text" name="mail" value="">

                <div class="box-form-text">メールアドレスの確認</div>
                <input class="form-mail" type="text" name="mail-co" value="">

                <button class="from-button" type="submit" name="submit">送信</button>
               
            </form>
        </div>
    </div>

  
</div>

</article>
</body>
</html>