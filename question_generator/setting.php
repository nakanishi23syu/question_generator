<?php

// メール情報
// メールホスト名・gmailでは smtp.gmail.com
define('MAIL_HOST','smtp.gmail.com');//変更

// メールユーザー名・アカウント名・メールアドレスを@込でフル記述
define('MAIL_USERNAME','**********');//変更

// メールパスワード・上で記述したメールアドレスに即したパスワード
define('MAIL_PASSWORD','**********');//変更

// SMTPプロトコル(sslまたはtls)
define('MAIL_ENCRPT','ssl');

// 送信ポート(ssl:465, tls:587)
define('SMTP_PORT', 465);

// メールアドレス・ここではメールユーザー名と同じでOK
define('MAIL_FROM','**********');//変更

// 表示名
define('MAIL_FROM_NAME','質問ジェネレータ');//変更

// メールタイトル
define('MAIL_SUBJECT','【質問ジェネレータ】仮登録完了メール');
