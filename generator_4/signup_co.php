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
    <div class="a-title">質問ジェネレータ　登録完了</div>

    <div class="a-msg"></div>

    <!-- ボックスフォーム -->
    <div class="box-form-signup-co">
        新規登録が完了しました
        <br>質問ジェネレータをお楽しみください
    </div>
</div>

</article>
</body>
</html>