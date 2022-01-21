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
    <div class="a-title">質問ジェネレータ　新規登録</div>

    <div class="a-msg"></div>

     

    <!-- ボックスフォーム -->
    <div class="box-form-co">
        <div class="box-form-msg">メールアドレスの入力▶︎仮登録メールの送受信▶︎登録情報の入力</div>

        <!-- メール入力フォーム -->
        <div class="box-form-mail-co">
            <div>以下のメールアドレスに仮登録メールが送信されました</div>
            <div class="form-mail-co">
            
                <?php
                    session_start();
                    $mail = isset($_SESSION['mail']) ? $_SESSION['mail'] : NULL;
                    echo $mail;  
                
                ?>

            </div>
        </div>

        <div class="box-form-msg-last">24時間以内にメールに記載されたURLからご登録ください</div>
    </div>
</div>

</article>
</body>
</html>

