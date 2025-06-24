# coachtech 勤怠管理アプリ
## プロジェクトの概要
ある企業が開発した独自の勤怠管理アプリ

## Dockerビルド
1. リポジトリの複製
   ```
   git clone git@github.com:NaoyaNoro/coachtech-attendance.git
   ```
3. DockerDesktopアプリを立ち上げる
4. dockerをビルドする<br>
   ```
   docker-compose up -d --build
   ```
>3を実行するときに，`no matching manifest for linux/arm64/v8 in the manifest list entries` というようなエラーが出ることがあります。この場合，docker-compose.ymlのmysqlサービスとphp myadminのサービスの箇所に `platform: linux/amd64` というような表記を追加してください。

## Laravel環境構築
1. PHPコンテナ内にログインする
   ```
   docker-compose exec php bash
   ```
2. composerコマンドを使って必要なコマンドのインストール
   ```
   composer install
   ``` 
4. .env.exampleファイルから.envを作成
   ```
   cp .env.example .env
   ```
6. 環境変数を変更<br>
   ```
   DB_HOST=mysql
   DB_PORT=3306 
   DB_DATABASE=laravel_db
   DB_USERNAME=laravel_user
   DB_PASSWORD=laravel_pass
   ```
7. アプリケーションキーの作成
   ```
   php artisan key:generate
   ```
8. キャッシュのクリア
   ```
   php artisan config:clear
   php artisan cache:clear
   php artisan config:cache
   ```
9. マイグレーションの実行<br>
    ```
    php artisan migrate
    ```
10. シーディングの実行<br>
    ```
    php artisan db:seed
    ```
11. 保存した画像が正しく表示できない場合は，strageに保存したデータを再登録する<br>
    ```
    php artisan storage:link
    ```

## シーディング情報
* Admin(管理者)に関するログイン情報
1. http://localhost/admin/login にアクセスする
2. メールアドレス：`boss@sample.com`<br>
   パスワード：`boss0000`<br>
   を入力
3. 「管理者ログインする」を押す

* user(一般user)に関するログイン情報
1. http://localhost/login にアクセスする
2. メールアドレス：`user1@sample.com`<br>
   パスワード：`user10000`<br>
   または<br>
   メールアドレス：`user2@sample.com`<br>
   パスワード：`user20000`<br>
3. 「ログインする」を押す

* ログインについては，上記のuserまたはadminでログインしてください。
* 勤怠情報のシーディングについては，シードを実行した月の「前月」のデータが入るように設定されています。例えば，2025年６月にシーディングを行った結果，2025年5月にデータが反映されます。これは同じ月にシーディングしてしまうと，本日以降の日付に勤怠情報が入力されてしまいバグが発生してしまうからです。

## MailHogの設定
1. MailHogのインストール
   ```
   docker run --name mailhog -d --platform linux/amd64 -p 1025:1025 -p 8025:8025 mailhog/mailhog
   ```
2. env.の環境変数を修正
   ```
   MAIL_MAILER=smtp
   MAIL_HOST=host.docker.internal
   MAIL_PORT=1025
   MAIL_USERNAME=""
   MAIL_PASSWORD=""
   MAIL_ENCRYPTION=null
   MAIL_FROM_ADDRESS=no-reply@example.com
   MAIL_FROM_NAME="${APP_NAME}"
   ```
3. PHPコンテナ内にログインする 
   ```
   docker-compose exec php bash
   ```
4. キャッシュのクリア
   ```
   php artisan config:clear
   php artisan cache:clear
   php artisan config:cache
   ```
5. 会員登録後，`認証はこちらから`というボタンを押すと，MailHogのページに遷移するので，そこで`Verify Email Address`をクリックする
6. ページ遷移後`Verify Email Address`というボタンを押すと，メール認証が行われて，勤怠登録画面に遷移する

## 単体テストの設定
1. MySQLコンテナ内にログインする
   ```
   docker-compose exec mysql bash
   ```
3. rootユーザーでログインする。(PW:root)
   ```
   mysql -u root -p
   ```
5. demo_testデータベースの新規作成を行う。
   ```
   CREATE DATABASE demo_test;
   ```
6. rootとlaravel_userにdemo_testへの権限を与える
   ```
   GRANT ALL PRIVILEGES ON demo_test.* TO 'root'@'%';
   GRANT ALL PRIVILEGES ON demo_test.* TO 'laravel_user'@'%';
   FLUSH PRIVILEGES;
   ```
7. configディレクトリ内のdatabases.phpのconnectionsに以下を追加(今回は記載済み)<br>
   ```
   'mysql_test' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => 'demo_test',
            'username' => 'root',
            'password' => 'root',
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
             PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
    ```
8. PHPコンテナ内にログインする 
   ```
   docker-compose exec php bash
   ```
9. .envファイルから.env.testingを作成
   ```
   cp .env .env.testing
   ```
10. .env.testingを以下のように設定(KEYの設定は空にしておく)
    ```
    APP_ENV=testing
    APP_KEY=
    DB_CONNECTION=mysql_test
    DB_DATABASE=demo_test
    DB_USERNAME=root
    DB_PASSWORD=root
    ```
11. テスト用のアプリケーションキーの作成
    ```
    php artisan key:generate --env=testing
    ```
12. テスト環境への切り替え
    ```
    export $(grep -v '^#' .env.testing | xargs)
    ```
13. キャッシュのクリア
    ```
    php artisan config:clear
    php artisan cache:clear
    ```
14. phpunit.xmlのphp箇所に以下を追加(今回は記載済み)
    ```
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="mysql_test"/>
    <env name="DB_DATABASE" value="demo_test"/>
    <env name="SESSION_DRIVER" value="array"/>
    ```
15. テスト用データベースdemo_testのマイグレーション
    ```
    php artisan migrate --env=testing
    ```
> 9で`php artisan key:generate --env=testing`を実行してもアプリケーションキーがうまく作成できないときがあります。その場合は，`php artisan key:generate --show`で手動でアプリケーションキーを作成して，`APP_KEY=`の後に表記してください。

## 単体テストの実施
1. テスト項目一覧

| テスト項目 | テストファイル名| 実行コマンド
|----------|----------|----------|
| 認証機能（一般ユーザー）  | RegisterTest  | `php artisan test tests/Feature/RegisterTest.php`|
| ログイン認証機能（一般ユーザー）  | LoginTest  |`php artisan test tests/Feature/LoginTest.php` |
| ログイン認証機能（管理者）  | AdminLoginTest  | `php artisan test tests/Feature/AdminLoginTest.php`　|
| 日時取得機能  | IndexTest(Duskを使用)  |`php artisan dusk tests/Browser/IndexTest.php` |
| ステータス確認機能  | StatusTest  | `php artisan test tests/Feature/StatusTest.php`|
| 出勤機能  | ClockInTest  | `php artisan test tests/Feature/ClockInTest.php`|
| 休憩機能  | BreakTest  | `php artisan test tests/Feature/BreakTest.php`|
| 退勤機能  | ClockOutTest  | `php artisan test tests/Feature/ClockOutTest.php`|
| 勤怠一覧情報取得機能（一般ユーザー）  | AttendanceListTest  | `php artisan test tests/Feature/AttendanceListTest.php`|
| 勤怠詳細情報取得機能（一般ユーザー）  | DetailTest  |`php artisan test tests/Feature/DetailTest.php` |
| 勤怠詳細情報修正機能（一般ユーザー）  | CorrectTest  | `php artisan test tests/Feature/CorrectTest.php` |
| 勤怠一覧情報取得機能（管理者）  | AdminListTest  | `php artisan test tests/Feature/AdminListTest.php`|
| 勤怠詳細情報取得・修正機能（管理者）  | AdminDetailTest  | `php artisan test tests/Feature/AdminDetailTest.php`|
| ユーザー情報取得機能（管理者）  | AdminStaffTest  | `php artisan test tests/Feature/AdminStaffTest.php`|
| 勤怠情報修正機能（管理者）  | AdminCorrectTest  | `php artisan test tests/Feature/AdminCorrectTest.php`|
| メール認証機能  | VerifyEmailTest <br> VerifyEmailTest(Duskを使用) | `php artisan test tests/Feature/VerifyEmailTest.php` <br> `php artisan dusk tests/Browser/VerifyEmailTest.php`|


2. 各項目のテストを実施<br>
   <例>会員登録機能をテストするとき
   ```
   php artisan test tests/Feature/RegisterTest.php
   ```
   <例>同時にテストするとき<br>
   ```
   php artisan test
   ```
3. テスト終了後，本番環境への切り替え
   ```
   export $(grep -v '^#' .env | xargs)
   ```
4. キャッシュのクリア
    ```
    php artisan config:clear
    php artisan cache:clear
    ```
> * Feature/VerifyEmailTest.phpでは，「会員登録後、認証メールが送信される」という一つの項目のテストが入っています。以下のDuskTestで残りの二つをテストしています。
> * テストケースのID6(出勤機能)の「出勤ボタンが正しく機能する」というテスト項目についてです。期待挙動としては，『処理後に画面上に表示されるステータスが「勤務中」になる』とありますが，正しくは『「出勤中」になる』だと思います。これで変更してテストを行っています。
> * テストケースのID11(勤怠詳細情報修正機能（一般ユーザー）)の「休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される」と「休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される」というテスト項目についてです。期待挙動としては，『「出勤時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される』とありますが，正しくは『「休憩時間が勤務時間外です」というバリデーションメッセージが表示される』だと思います。これで変更してテストを行っています。
> * 上と同様ですが，テストケースのID13(勤怠詳細情報取得・修正機能（管理者）)の「休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される」と「休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される」というテスト項目についてです。期待挙動としては，『「出勤時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される』とありますが，正しくは『「休憩時間が勤務時間外です」というバリデーションメッセージが表示される』だと思います。これで変更してテストを行っています。
> * テストケースID12(勤怠一覧情報取得機能（管理者）)について，「その日になされた全ユーザーの勤怠情報が正確に確認できる」と「遷移した際に現在の日付が表示される」の二つのテストは同時に行っています。
   
## DUSKの設定
> [!NOTE]
> Laravel Duskは，ブラウザテストを自動化するためのツールです。日時取得機能では，JavaScriptを使うことで現在の時間が，画面に反映されるようになっています。またメール認証機能ではMailHogの画面での操作を行うことにより，メールの認証が完了する仕組みです。Laravelの通常の単体テストでは，バックエンドのロジックを検証できますが，JavaScriptなどを含む動作は確認できません。そこでDuskを使うことで，実際のブラウザを起動して，JavaScript を含むフロントエンドの動作をテストできるようになっています。

1. PHPコンテナ内にログインする
   ```
   docker-compose exec php bash
   ```
2. Duskのインストール
   ```
   composer require --dev laravel/dusk
   php artisan dusk:install
   ```
3. .envファイルから.env.dusk.localを作成
   ```
   cp .env .env.dusk.local
   ```
4. .env.dusk.localを以下のように設定(KEYの設定は空にしておく)
   ```
   APP_ENV=dusk.local
   APP_KEY=
   APP_DEBUG=true
   APP_URL=http://nginx

   DB_CONNECTION=mysql_test
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=demo_test
   DB_USERNAME=laravel_user
   DB_PASSWORD=laravel_pass

   MAILHOG_URL=http://host.docker.internal:8025/
   ```
> MAILHOG_URLのURLは必ず指定してください。そうしないとテストが失敗します。
5. testディレクトリ内のDuskTestCase.phpを以下のように修正する
   ```
   protected function driver()
    {
        $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            '--headless', // GUI なしのヘッドレスモード
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--window-size=1920,1080',
        ]);

        return RemoteWebDriver::create(
            'http://selenium:4444/wd/hub', // Selenium サーバー
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY,
                $options
            )
        );
    }
   ```
6. テスト環境への切り替え
   ```
   export $(grep -v '^#' .env.testing | xargs)
   ```
7. テスト用のアプリケーションキーの作成
   ```
   php artisan key:generate --env=dusk
   ```
8. コンテナを出る
   ```
   exit
   ```
9. dockerを一度停止する
   ```
   docker-compose down
   ```
10. 再度dockerをビルドする
    ```
    docker-compose up -d --build
    ```
11. PHPコンテナ内にログインする 
    ```
    docker-compose exec php bash
    ```
12. キャッシュのクリア
    ```
    php artisan config:clear
    php artisan cache:clear
    ```
13. 日時取得機能とメール認証機能のテストを行う
    ```
    php artisan dusk tests/Browser/IndexTest.php
    ```
    ```
    php artisan dusk tests/Browser/VerifyEmailTest.php
    ```
15. テスト終了後，本番環境への切り替え
    ```
    export $(grep -v '^#' .env | xargs)
    ```
16. キャッシュのクリア
    ```
    php artisan config:clear
    php artisan cache:clear
    ```
>　7で`php artisan key:generate --env=dusk`を実行してもアプリケーションキーがうまく作成できないときがあります。その場合は，`php artisan key:generate --show`で手動でアプリケーションキーを作成して，`APP_KEY=`の後に表記してください。

## 諸注意
* 勤怠一覧画面(一般ユーザー)，スタッフ別勤怠一覧画面(管理者)，勤怠一覧画面(管理者)のところでは各勤怠について「詳細ボタン」があります。この「詳細ボタン」は勤怠が確定(退勤がなされる)しているものだけ表示される仕様にしています。つまり勤務中の場合には，修正が提出できない状態にしています。
* 機能要件のNo.11(管理者ユーザーは各勤怠の詳細を確認・修正することができる)についてです。管理者は直接、ユーザーの勤怠を修正できるということでした。ここで入力した備考については，管理者及びユーザーの勤怠詳細画面で確認することができています。
* ユーザーから申請が出された勤怠については，管理者が直接，勤怠情報を修正してしまいますと，大変ややこしいです。そこで，申請が出されている勤怠については，修正ボタンを表示せず，「*申請中の勤怠のため修正はできません。」と文字が表示されるようになっています。
* ユーザーが申請を出し，管理者に承認された勤怠については，userに再申請されることや，管理者に直接修正できない仕様になっています。このような勤怠については，ユーザーの詳細画面では「修正済み」という非活性のボタン，管理者の詳細画面では「承認済み」という非活性のボタンが表示されるようになっています。
* 管理者のログインについてです。こちらは自前のルートを準備していますが，ログインの操作についてはFortifyServideProviderが担っています。
* 勤怠詳細画面の休憩の番号です。Figmaでは「休憩，休憩２，休憩３・・・」となっていましたが，番号がいきなり２から始まるのは不自然なので，「休憩１，休憩２，休憩３・・・」というように１始まりに変更しています。
* 機能要件のFN038の管理者の項目編集機能ですが，『「日付」「出勤・退勤」「休憩」「備考」の4つの項目において，修正したい内容を記載できるフィールドになっていること』とあります。しかしここで日付を修正してしまいますと，日付を選択している意味がなくなってしまします。Figmaを確認すると、日付には変更できるフィールドがありませんでしたので，日付の修正はできない仕様にしています。
* 機能要件のFN050の申請詳細取得機能ですが，『2. 詳細画面の内容が，正しく実際の打刻内容が反映されていること』とありますが，管理者は一般ユーザーの申請された修正時間が妥当かを判断して，承認します。そのような観点からここで表示されるべきなのは，一般ユーザーの承認してほしい時間であるべきです。ここでは，実際の打刻内容ではなく一般ユーザーによる申請時間を表示しています。
## 使用技術
* php 7.4.9
* Laravel 8.83.8
* MySQL 8.0.26
* MailHog 1.0.1
* Dusk v6.25.2

## ER図
![er(attendance)](https://github.com/user-attachments/assets/5ddad4fc-e2d2-464c-bcdd-45f7ff7af8a4)







## URL
* 開発環境:http://localhost
* phpmyadmin:http://localhost:8080/
* MailHog:http://localhost:8025/

