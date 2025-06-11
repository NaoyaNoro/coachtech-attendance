# coachtech 勤怠管理アプリ
## プロジェクトの概要
ある企業が開発した独自の勤怠管理アプリ

## Dockerビルド
1. リポジトリの複製
   ```
   git clone git@github.com:NaoyaNoro/coachtech-market.git
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
| 認証機能（一般ユーザー）  | RegisterTest  | `php artisan test tests/Feature/RegistreTest.php`|
| ログイン認証機能（一般ユーザー）  | LoginTest  |`php artisan test tests/Feature/LoginTest.php` |
| ログイン認証機能（管理者）  | AdminLoginTest  | `php artisan test tests/Feature/AdminLoginTest.php`　|
| 日時取得機能  | IndexTest(Duskを使用)  |`php artisan dusk tests/Browser/IndexTest.php` |
| ステータス確認機能  | StatusTest  | `php artisan test tests/Feature/statusTest.php`|
| 出勤機能  | ClockInTest  | `php artisan test tests/Feature/ClockInTest.php`|
| 休憩機能  | BreakTest  | `php artisan test tests/Feature/BreakTest.php`|
| 退勤機能  | ClockOutTest  | `php artisan test tests/Feature/ClockOutTest.php`|
| 勤怠一覧情報取得機能（一般ユーザー）  | AttendanceListTest  | `php artisan test tests/Feature/AdmainListTest.php`|
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
   php artisan test --filter RegisterTest
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
> * 商品詳細情報取得では「必要な情報が表示される」「複数選択されたカテゴリーが表示されているか」の２項目あります。これらはDetailTest内でのtest_detail_productという関数で一度にテストしています。
> * 商品購入機能では「「購入する」ボタンを押下すると購入が完了する」「購入した商品は商品一覧画面にて「sold」と表示される」「「プロフィール/購入した商品一覧」に追加されている」の3項目あります。これらはPurchaseTest内でのtest_purchase_stripe_paymentという関数で一度にテストしています。
   
## DUSKの設定
> [!NOTE]
> Laravel Duskは，ブラウザテストを自動化するためのツールである。支払い方法選択機能では，JavaScriptを使うことで支払い方法が即座に小計画面に反映されるようになっている。Laravelの通常の単体テストでは，バックエンドのロジックを検証できるが，JavaScriptを含む動作は確認できない。そこでDuskを使うことで，実際のブラウザを起動して，JavaScript を含むフロントエンドの動作をテストできる。

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
   ```
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
13. 支払い方法選択機能のテストを行う
    ```
    php artisan dusk --filter=PurchaseMethodTest
    ```
14. テスト終了後，本番環境への切り替え
    ```
    export $(grep -v '^#' .env | xargs)
    ```
15. キャッシュのクリア
    ```
    php artisan config:clear
    php artisan cache:clear
    ```
>　7で`php artisan key:generate --env=dusk`を実行してもアプリケーションキーがうまく作成できないときがあります。その場合は，`php artisan key:generate --show`で手動でアプリケーションキーを作成して，`APP_KEY=`の後に表記してください。

## 諸注意
* 勤怠一覧画面(一般ユーザー)とスタッフ別勤怠一覧画面(管理者)のところでは各勤怠について「詳細ボタン」があります。この「詳細ボタン」は勤怠が確定する(退勤がなされる)しているものだけに表示される仕様にしています。つまり勤務中の場合には，修正が提出できない状態にしています。
  
## 使用技術
* php 7.4.9
* Laravel 8.83.8
* MySQL 8.0.26
* Stripe  v16.6.0
* MailHog 1.0.1
* Dusk v6.25.2

## ER図
![er(coachtech_msrket)](https://github.com/user-attachments/assets/4727a7d6-7eef-4b2e-9360-6de55950bcd6)



## URL
* 開発環境:http://localhost
* phpmyadmin:http://localhost:8080/
* MailHog:http://localhost:8025/

