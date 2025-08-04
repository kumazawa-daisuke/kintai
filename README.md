# 勤怠管理アプリ
## 環境構築
### Dockerビルド
1. git clone git@github.com:kumazawa-daisuke/kintai.git
2. DockerDesktopアプリを立ち上げる
3. docker-compose up -d --build
### Laravel環境構築
1. docker-compose exec php bash
2. composer install
3. 「.env.example」ファイルを「.env」ファイルに命名を変更。または新しく.envファイルを作成
4. .envに以下の環境変数を追加

DB_CONNECTION=mysql  
DB_HOST=mysql  
DB_PORT=3306  
DB_DATABASE=laravel_db  
DB_USERNAME=laravel_user  
DB_PASSWORD=laravel_pass  

MAIL_MAILER=smtp  
MAIL_HOST=mailhog  
MAIL_PORT=1025  
MAIL_USERNAME=null  
MAIL_PASSWORD=null  
MAIL_ENCRYPTION=null  
MAIL_FROM_ADDRESS=example@example.com  
MAIL_FROM_NAME="${APP_NAME}"  

5.アプリケーションキーの作成  
php artisan key:generate  
6.マイグレーションの実行  
php artisan migrate  
7.シーディングの実行  
php artisan db:seed  
  
## テスト用アカウント  
管理者  
メールアドレス:admin@example.com    
パスワード:password  
  
一般  
名前:テスト太郎  
メールアドレス:test1@example.com  
パスワード:password  
  
名前:サンプル花子  
メールアドレス:test2@example.com  
パスワード:password  
  
## 備考  
・ダウンロードしたCSVファイルを直接開くと文字化けする可能性があるため空のエクセルファイルからデータタブ→[テキストまたはCSVから]→[UTF-8]を選んで開くようにしてください  
・同じブラウザでの2人以上のログインは避けてください。別ブラウザでログインするか、ログアウトしてから別のユーザーでログインするようにしてください  
  
## 使用技術(実行環境)
・PHP7.4.9  
・Laravel8.83.8  
・MySQL8.0.26  
・MailHog（開発用メールサーバ）  

## ER図
<img width="1001" height="911" alt="E-R kintai" src="https://github.com/user-attachments/assets/d58908f0-eec4-4210-af1d-1c2a5a3208f6" />

## URL
・開発環境：http://localhost/  
・phpMyAdmin:http://localhost:8080/  
・mailhog:http://localhost:8025/
