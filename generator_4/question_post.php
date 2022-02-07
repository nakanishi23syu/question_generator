<?php 
session_start();
//クロスサイトリクエストフォージェリ（CSRF）対策
// $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
// $token = $_SESSION['token'];
//クリックジャッキング対策

// //クロスサイトリクエストフォージェリ（CSRF）対策
// $toke_byte = openssl_random_pseudo_bytes(16);
// $csrf_token = bin2hex($toke_byte);
// // 生成したトークンをセッションに保存します
// $_SESSION['csrf_token'] = $csrf_token;

//クリックジャッキング対策
header('X-FRAME-OPTIONS: DENY');

//エスケープ処理
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}


$_SESSION['ch_zokusei_p'] = 0;
$_SESSION['ch_h_p'] = 0;
$_SESSION['ch_tokumei_p'] = 0;


$name = isset($_SESSION['name']) ? $_SESSION['name'] : NULL;
$msg ="";


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

$errors = array();



//「質問を投稿する」が押されたら
if(isset($_POST['submit'])){
    // if (isset($_POST["csrf_token"]) && $_POST["csrf_token"] !== $_SESSION['csrf_token']) {

    //     $errors['token'] = 'トークンエラーです';
            
    // }else{
    
        $p_question = isset($_POST['question']) ? htmlspecialchars($_POST['question'], ENT_QUOTES, 'UTF-8') : NULL;
        $zokusei = isset($_POST['zokusei']) ? $_POST['zokusei'] : 0;
        $h = isset($_POST['h']) ? $_POST['h'] : 0;
        $tokumei = isset($_POST['tokumei']) ? $_POST['tokumei'] : 0;

        $_SESSION['ch_zokusei_p']  = $zokusei;
        $_SESSION['ch_h_p']  = $h;
        $_SESSION['ch_tokumei_p']  = $tokumei;

        //質問が未入力だった場合
        if(empty($_POST['question'])){
            $errors['no-question'] = '質問を入力してください';

        }else{


            $sql = "INSERT INTO generator_3 (question, name, created_at, zokusei, h, tokumei)
            VALUES (:quetion, :name, now(), :zokusei, :h, :tokumei)";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':quetion', $p_question, PDO::PARAM_STR);
            $stmt->bindValue(':name', $name, PDO::PARAM_STR);
            $stmt->bindValue(':zokusei', $zokusei, PDO::PARAM_INT);
            $stmt->bindValue(':h', $h, PDO::PARAM_INT);
            $stmt->bindValue(':tokumei', $tokumei, PDO::PARAM_INT);

            $stmt->execute();

            $msg = "質問を投稿しました";
        }
    
    
}

?>



<!DOCTYPE html>
<html lang = "ja">
<head>
    <meta charset="UTF-8">
    <title>質問する - 質問ジェネレータ</title>
    <link rel="stylesheet" href="generator.css">
</head>

<body>
<?php if(empty($_SESSION['name'])):?>
    <meta http-equiv="refresh"content="0;URL=http://localhost:8888/generator_4/login_4.php">
<?php else: ?>

    


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



<article>

<div class="question-container">
    <div class="a-title-question">質問を投稿する</div>
    <div class="a-msg-post"><?php echo $msg;?></div>

    <!-- エラーメッセージ -->
    <?php if(count($errors) > 0):?>
            <ul class="error-msg-post">
                
                <?php foreach($errors as $value):?>
                    <li>
                        <?php echo $value;?>
                    </li>
                <?php endforeach; ?>
        
            </ul>
    <?php endif; ?>

    <!-- 質問投稿フォーム -->
    <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="post">
    <input type="hidden" name="csrf_token" value="<?=$csrf_token?>">

        <!-- 質問フォーム -->
        <div class="box-form-question">質問内容</div>
        <textarea class="form-question"  name="question" cols="" rows="" placeholder="質問を記入"></textarea>

        <!-- ラジオボタン フォーム -->
        <div class="box-form-question">質問属性</div>
        <div class="radio-question">
            <input type="radio" id="mazime" name="zokusei" value="0"
            <?php $z_checked = $_SESSION['ch_zokusei_p'] == 0 ? "checked" : ""; echo $z_checked ?>>
            <label for="mazime">真面目</label>

            <input type="radio" id="enjoy" name="zokusei" value="1"
            <?php $z_checked = $_SESSION['ch_zokusei_p'] == 1 ? "checked" : ""; echo $z_checked?>>
            <label for="enjoy">おもしろ</label>
        </div>

        <div class="box-form-question">下ネタ</div>
        <div class="radio-question">
            <input type="radio" id="hukumu" name="h" value="0"
            <?php $h_checked = $_SESSION['ch_h_p'] == 0 ? "checked" : ""; echo $h_checked?>>
            <label for="hukumu">含まない</label>

            <input type="radio" id="hukumanai" name="h" value="1"
            <?php $h_checked = $_SESSION['ch_h_p'] == 1 ? "checked" : ""; echo $h_checked?>>
            <label for="hukumanai">含む</label>
        </div>

        <div class="box-form-question">名前</div>
        <div class="radio-question">
            <input type="radio" id="koukai" name="tokumei" value="0"
            <?php $t_checked = $_SESSION['ch_tokumei_p'] == 0 ? "checked" : ""; echo $t_checked?>>
            <label for="koukai">公開</label>

            <input type="radio" id="hikoukai" name="tokumei" value="1"
            <?php $t_checked = $_SESSION['ch_tokumei_p'] == 1 ? "checked" : ""; echo $t_checked?>>
            <label for="hikoukai">非公開</label>
        </div>


        <button class="form-button-question" type="submit" name="submit">質問を投稿する</button>
    
    </form>
    
</div>

</article>

<?php endif; ?>
</body>
</html>