# question_generator（質問ジェネレータ）

会話のネタを提供し、気まずい時間をなくすことを目的とした質問生成ツールです。<br>
調整完了次第、公開する予定です。

## 使い方
![generator_manual_01](/manual/manual_01.png)
![generator_manual_02](/manual/manual_02.png)
![generator_manual_03](/manual/manual_03.png)
![generator_manual_04](/manual/manual_04.png)
![generator_manual_05](/manual/manual_05.png)
![generator_manual_06](/manual/manual_06.png)
![generator_manual_07](/manual/manual_07.png)
![generator_manual_08](/manual/manual_08.png)
![generator_manual_09](/manual/manual_09.png)
![generator_manual_10](/manual/manual_10.png)

 

 
## スクリーンショット 
 
![generattor_4.php](generator_4.php.png)
![question_post.php](question_post.php.png)


 
 
## 使い方
 

**質問生成機能(generator_4.php)**<br>
「質問を生成するボタン」をクリックすることで、DBに登録された質問をランダムで1つ表示することができます。<br>
その際、質問生成オプションの3つ条件（真面目/おもしろ、下ネタを含む/含まない、評価）で表示させる質問を絞り込むことができます。<br><br>

**質問評価機能(generator_4.php)**<br>
「質問評価」の6つのラジオボタンを選択し、「評価ボタン」を押すと質問を評価することができます。<br>
評価の計算方法は 今までの評価の合計 ÷　　今まで評価された回数 です。<br>
また、質問を投稿した際、デフォルトで評価は3、評価された回数は1となります。<br><br>

**質問投稿機能（question_post.php）**<br>
質問をフォームに記入し、属性、下ネタ、名前の公開を設定して「質問を投稿するボタン」を押すことで、DBに質問を登録することができます。<br>
質問を投稿する際に、ログインが必須となっており、ログインしていない状態でこのページに行くと、ログイン画面に遷移します。
 
## 著者
 
* Nakanishi
* 23卒
* [Twitter](https://twitter.com/Nakana_design)
