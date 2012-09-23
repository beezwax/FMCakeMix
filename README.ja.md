FMCakeMix
=========

FMCakeMixは、MVCフレームワークであるCakePHP用のFileMakerデータソースドライバー
です。FMCakeMixを利用すると、SQLデータベースと同じようにFileMakerとCakePHPを統
合できます。すなわち、モダンなWebアプリケーションフレームワークを使って、
FileMakerデータベースと連係するWebアプリケーションを迅速に開発できるようになる
のです。

CakePHPに関する詳細については次のWebサイトを参照してください：
http://cakephp.org/

FMCakeMixの使い方についてはUser Guide.pdf（英文）を参照してください。

注意点
------

このソフトウェアは開発途上版であることに留意し、各自で検証しながら自己責任の下で
使用してください。フィードバックや不具合の修正等も歓迎しています。なお、現時点で
は本ドライバーはCakePHP 3.0系統を対象として開発をしています。

インストール
------------

http://cakephp.org/ からCakePHPをダウンロードして、Webサイトのマニュアルにある
インストール手順にならってインストールおよび設定を行います。

FX.phpは、Chris Hansen氏が中心になって開発した、PHPからFileMaker Proデータベー
スに接続するためのライブラリクラスです。FMCakeMixは、FileMaker Proデータベース
に接続する際に内部的にFX.phpを利用しています。http://www.iviking.org/FX.php/ か
らFX.phpのファイルをダウンロードして、FX.php、FX_Error.php、ObjectiveFX.php、
FX_constants.phpおよびimage_proxy.phpのファイルとdatasource_classesフォルダを
app/Vendorフォルダの直下に配置します。

FileMaker Proデータベースとの接続にXMLを利用しているため、XMLを使用したカスタム
Web公開機能をサポートしているFileMaker ServerもしくはFileMaker Server Advanced
でデータベースをホストしなければなりません。手順についてはFileMaker Serverに付
属のマニュアルを参照してください。

CakePHP 2.0の場合には、app/Model/Datasource/DatabaseフォルダにFilemaker.php
ファイルを配置します。おそらくDatasourceフォルダにおいてDatabaseという名称の
ディレクトリを作成する必要があるでしょう。

サポート
-------

免責事項については下記のライセンス条項をご覧ください。（つまり、自力で頑張って
ということです。）

とはいえ、[プロジェクトサイト](https://projects.beezwax.net/projects/show/cake-fm-driver)
を通じて多少のサポートはできるかもしれません。

クレジット
------

* 作者：Alex Gibbons <alex_g@beezwax.net>

謝辞
------

Thanks to [Beezwax Datatools, Inc.](http://beezwax.net)

MIT License
-----------

Copyright (c) 2009 Beezwax Datatools, Inc., Alex Gibbons

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
of the Software, and to permit persons to whom the Software is furnished to do
so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
