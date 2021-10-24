<?php

session_start();
//クロスサイトリクエストフォージェリ（CSRF）対策
// $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
// $token = $_SESSION['token'];
//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');


//変数設定
$question = "生成ボタンを押してください";
$now_review_average = "";

$_SESSION['ch_zokusei'] = 0;
$_SESSION['ch_h'] = 0;

$_SESSION['min'] = 0;
$_SESSION['max'] = 5;


$msg = "";
$name = isset($_SESSION['name']) ? $_SESSION['name'] : NULL;


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



//テーブル作成
$sql = "CREATE TABLE IF NOT EXISTS generator_3"
." ("
."id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,"
."question VARCHAR(128) NOT NULL,"
."name VARCHAR(128) NOT NULL,"
."created_at DATETIME,"

."zokusei tinyint NOT NULL,"
."h tinyint(1) NOT NULL,"
."tokumei tinyint(1) NOT NULL,"


."inappropriate tinyint(1) DEFAULT 0,"

."review_total INT DEFAULT 3,"
."review_count INT DEFAULT 1,"
."review_average DOUBLE(3,2) DEFAULT 3"

.");";

$stmt = $pdo->query($sql);

$s_zokusei = $_SESSION['ch_zokusei'];
$s_h = $_SESSION['ch_h'];

$min = $_SESSION['min'];
$max = $_SESSION['max'];


//DBから質問とIDを取り出す
$sql = "SELECT id, question, name, review_average, tokumei FROM generator_3
WHERE zokusei=:zokusei AND h=:h
AND review_average >=:min AND review_average <=:max
ORDER BY RAND() LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':zokusei', $s_zokusei, PDO::PARAM_INT);
$stmt->bindValue(':h', $s_h, PDO::PARAM_INT);
$stmt->bindValue(':min', $min, PDO::PARAM_STR);
$stmt->bindValue(':max', $max, PDO::PARAM_STR);
$stmt->execute();

$question_array = $stmt->fetch();

//条件に合う質問がなかった場合
if(empty($question_array)){

    $question= '条件に合う質問が見つかりませんでした';


//条件に合う質問がある場合    
}else{

    $question = $question_array['question'];
    $question_id = $question_array['id'];
    $question_tokumei = $question_array['tokumei'];
    $question_name = $question_array['tokumei'] == 1 ? "非公開": $question_array['name'];
    $question_review_average = $question_array['review_average'];

}



//「生成」が押されたら
if(isset($_POST['g_submit'])){

    //属性
    $_SESSION['ch_zokusei'] = isset($_POST['s_zokusei']) ? $_POST['s_zokusei'] : 0;
    $_SESSION['ch_h'] = isset($_POST['s_h']) ? $_POST['s_h'] : 0;
    //フォームのチェックボックスを保存するため、値をセッションに保存
    

    //評価
    $_SESSION['min'] = isset($_POST['min']) ? $_POST['min'] : 0;
    $_SESSION['max'] = isset($_POST['max']) ? $_POST['max'] : 5;
    //フォームのチェックボックスを保存するため、値をセッションに保存
    


    //DBから質問とIDを取り出す
    $sql = "SELECT id, question, name, review_average, tokumei FROM generator_3
    WHERE zokusei=:zokusei AND h=:h
    AND review_average >=:min AND review_average <=:max
    ORDER BY RAND() LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':zokusei', $_SESSION['ch_zokusei'], PDO::PARAM_INT);
    $stmt->bindValue(':h', $_SESSION['ch_h'], PDO::PARAM_INT);
    $stmt->bindValue(':min', $_SESSION['min'], PDO::PARAM_STR);
    $stmt->bindValue(':max', $_SESSION['max'], PDO::PARAM_STR);


    $stmt->execute();

    $question_array = $stmt->fetch();
    
    //条件に合う質問がなかった場合
    if(empty($question_array)){

        $question= '条件に合う質問が見つかりませんでした';
        $question_review_average ="---";
        $question_name ="---";
    
    //条件に合う質問がある場合    
    }else{
    
        $question = $question_array['question'];
        $question_id = $question_array['id'];
        $question_tokumei = $question_array['tokumei'];
        $question_name = $question_array['tokumei'] == 1 ? "非公開": $question_array['name'];
        $question_review_average = $question_array['review_average'];

        $_SESSION['question_id'] = $question_id;

        
    }
}



//評価が押されたら
//生成ボタンを押して出した質問でないと評価が反映されない。つまり
//サイトにはじめに訪れたとき、評価を押した後の次の質問には評価が反映されない
if(isset($_POST['r_submit'])){
    if(empty($_SESSION['question_id'])){

    }else{

        $review = isset($_POST['review']) ? $_POST['review'] : 0;
        $inapp = isset($_POST['inapp']) ? $_POST['inapp'] : 0;
        $count = $review != 0 ? 1 : 0;

        $sql = "SELECT inappropriate, review_total, review_count, review_average FROM generator_3
        WHERE id=:id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $_SESSION['question_id'], PDO::PARAM_INT);
        $stmt->execute();

        $question_review = $stmt->fetch();
        $review_total = $question_review["review_total"];
        $review_count = $question_review["review_count"];
        $review_average = $question_review["review_average"];
        $inappropriate = $question_review["inappropriate"];

        //DBの内容をUPDATEする
        $now_review_total = $review_total + $review;
        $now_review_count = $review_count + $count;
        $now_review_average = $now_review_total / $now_review_count;
        $now_inappropriate = $inappropriate + $inapp;
        

        //質問をnowシリーズにupdateする
        $sql = "UPDATE generator_3 
        SET inappropriate=:inappropriate, 
        review_total=:review_total, 
        review_count=:review_count, 
        review_average=:review_average
        WHERE id=:id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':inappropriate', $now_inappropriate, PDO::PARAM_STR);
        $stmt->bindValue(':review_total', $now_review_total, PDO::PARAM_STR);
        $stmt->bindValue(':review_count', $now_review_count, PDO::PARAM_INT);
        $stmt->bindValue(':review_average', $now_review_average, PDO::PARAM_STR);
        $stmt->bindValue(':id', $_SESSION['question_id'], PDO::PARAM_INT);

        $stmt->execute();

        unset($_SESSION['question_id']);
    }


}


//リセットが押されたら
// if(isset($_POST['ch_reset'])){

//     $_SESSION['ch_mazime'] = 1;
//     $_SESSION['ch_enjoy'] = 1;
//     $_SESSION['ch_h'] = 0;
//     $_SESSION['min'] = 0;
//     $_SESSION['max'] = 5;

    
// }


?>



<!DOCTYPE html>
<html lang = "ja">
<head>
    <meta charset="UTF-8">
    <title>質問ジェネレータ　質問で会話を繋ぐ</title>
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



<article>
<div class="question-container">
    <div class="a-title-question">質問を生成する</div>
    <div class="a-msg"></div>

    
        <!-- 質問表示欄 -->
        <div class="box-form-question">質問内容</div>
        
        <div class="box-generator">
            <div class="question"><?=htmlspecialchars($question, ENT_QUOTES)?></div>

            <div class="question-data">
                <P>評価：<?php if(isset($question_review_average)) echo $question_review_average?></P>
                <p>投稿者：<?php if(isset($question_name)) echo $question_name?></p>
            </div>
        </div>

   

    <!-- 質問生成オプションフォーム -->
    <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="post">

        <!-- ラジオボタン フォーム -->
        <button class="form-button-generation" type="submit" name="g_submit">質問を生成する</button>
                

        <!-- 質問生成オプション -->
        <div class="question-option-title">質問生成オプション</div>

        <div class="box-form-question">質問属性</div>
        <div class="radio-question">
            <input type="radio" id="mazime" name="s_zokusei" value="0"
            <?php $z_checked = $_SESSION['ch_zokusei'] == 0 ? "checked" : ""; echo $z_checked ?>>
            <label for="mazime">真面目</label>

            <input type="radio" id="enjoy" name="s_zokusei" value="1" 
            <?php $z_checked = $_SESSION['ch_zokusei'] == 1 ? "checked" : ""; echo $z_checked?>>
            <label for="enjoy">おもしろ</label>
        </div>

        <div class="box-form-question">下ネタ</div>
        <div class="radio-question">
            <input type="radio" id="hukumu" name="s_h" value="0"
            <?php $h_checked = $_SESSION['ch_h'] == 0 ? "checked" : ""; echo $h_checked?>>
            <label for="hukumu">含まない</label>

            <input type="radio" id="hukumanai" name="s_h" value="1"
            <?php $h_checked = $_SESSION['ch_h'] == 1 ? "checked" : ""; echo $h_checked?>>
            <label for="hukumanai">含む</label>
        </div>

        <div class="box-form-question">評価</div>
        <div class="radio-question">
            <input type="number" step="0.01" name="min" value="<?php echo $_SESSION['min']; ?>">
            <P>〜</P>
            <input type="number" step="0.01" name="max" value="<?php echo $_SESSION['max']; ?>">

        </div>
        
        
       
    </form>


    <!-- 質問評価フォーム -->
    <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="post">
        <div class="box-form-question">質問評価</div>

        <div class="review">
            <div class="radio-question-review">
                <input type="radio" id="5" name="review" value="5">
                <label for="5">5</label>

                <input type="radio" id="4" name="review" value="4">
                <label for="4">4</label>

                <input type="radio" id="3" name="review" value="3">
                <label for="3">3</label>

                <input type="radio" id="2" name="review" value="2">
                <label for="2">2</label>

                <input type="radio" id="1" name="review" value="1">
                <label for="1">1</label>

                <input type="checkbox" id="inapp" name="inapp" value="1">
                <label for="inapp">不適切</label>
            </div>

            <button class="form-button-review" type="submit" name="r_submit">評価</button>
        </div>
    </form>

<div>
</article>

</body>
</html>