# VtuberUTALIST


## 説明

これは、VtuberやVsingerが配信で歌った歌を集計して、表示するWebサービスです。日本語の利用者を想定しています。
少数のリスナーや、歌唱者自身が履歴を確認するためのサービスです。大規模なアクセスは想定していません。その場合は、静的ページに作り変えるか、CDNに対応できるようにクライアントサイドのスクリプトに改造する必要があるでしょう


管理者認証にcartalyst/sentinelを同梱しています
このサービスで、データベースを操作できるのは管理者1名です


必要なサーバーサービス
PHP8.1〜8.3,
MariaDB(MySQL)


## Description

This is a web service that compiles and displays songs sung by Vtubers and Vsingers during their live streams. It is intended for Japanese users.

This service is intended for a small number of listeners or for singers themselves to check their history. Large-scale access is not anticipated. In that case, it would be necessary to either rebuild it as a static page or modify the client-side script to support a CDN.


Cartalyst/Sentinel is included for administrator authentication.
Only one administrator can operate the database using this service.

Required server services:
PHP ​​8.1-8.3,
MariaDB (MySQL)
