=== Salon Booking ===
Contributors: kuu(Tanaka Hisao)
Tags: hair salon,salon,appointment,booking,beauty apps,reservation,dental clinic,hospital,mutilingual,散髪予約,美容院予約,美容室予約,サロン予約,エステ予約,予約システム,予約管理
Requires at least: 4.0
Tested up to: 4.0
Stable tag: 1.5.13
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Salon Booking enables the reservation to one-on-one business.

== Description ==

Salon Booking enables the reservation to one-on-one business  
between a client and a staff member, 
namely those businesses like hair salon, hospital, dental clinic and so on..

Salon Booking requires neither member registration to make reservations  
from on the Web sites, nor loses prospective clients  
who hesitate to register personal information. 

To prevent the wrongful registration and reservation,  
the reservation procedure is devised for the clients  
with no registration at all as follows;  
"tentative reservation",  
"response to the e-mail address who tentatively reserved",  
and "confirmation by the client on the confirmation screen on the Web sites".

And if a client agrees to register,  
the reservation is done at once and  
the change of the reservation is also becoming easier, 
which might be an additional incentive for the clients to register.

The interface for the reservation is easy and like that of Google Calendar.  
The change of the reservation is possible by means of drag and drop,  
which enables also the staff member phoned by a client for the change  
of the request can easily change the schedule accordingly.

Salon Booking is also capable of the personnel management of the staff member  
on the shift control and time recording.  
Of course the possible time ranges of reservation  
and the attendance of the staff member co-relates automatically. 

Salon Booking can also record the actual performance against the reservation  
and capable of compiling the information on the demands from the clients  
and working results.  
So, it is very useful in improving the service quality and the operation management of staff.

As for the security, all the measures are included in this system,  
namely against identity frauds, malicious requests to reserve, SQL injection attacks and so.

このプラグインは、散髪屋さんや美容院の予約を  
Ｗｅｂ上から可能にするものです。  
散髪屋さん・美容院をターゲットにしていますが、  
顧客に対して、スタッフが１対１でサービスを提供する職業、  
例えば病院、歯医者、ネイルサロン、マッサージなどでも  
使用可能だと思います。

世間の予約システムは会員登録が必須となっている場合が多いですが、  
登録自体が面倒だったり、個人情報を登録することに躊躇したりと、  
結果として顧客を逃している場合があるかと思います。


このプラグインでは、会員登録をしなくとも予約できます。  
会員登録を躊躇する顧客も逃しません。多分。。。  
会員登録していない顧客の予約は、  
「仮予約」→「予約した人へのメール」→  
「確定画面による予約完了」の流れをとることにより、  
あやしい連中の予約を防ぎます。

会員登録してもらえば、すぐに予約完了となりますし、  
予約に対する変更も可能になりますので、  
２回目以降は、会員登録してもらえるかと。

予約はグーグルカレンダーのようなインタフェースで入力できます。  
予約の変更はドラッグ＆ドロップで可能にしているので、  
スタッフが電話等で受け付ける際の時間調整でも便利かと思います。

スタッフのシフト管理や出退勤管理も同時に行えます。  
予約可能時間は出退勤管理と連動します。  
例えば、スタッフが午前休みの場合は、  
その日の午前のスタッフに対する予約はできなくなります。

予約に対する実績も登録できるので、  
お客さまの要望などや作業時間などの情報を蓄積することにより、  
サービスの向上につなげていくことができます。  
そのうち連動した統計や会計の機能をつくります。多分。。。  

セキュリティに関しても、なりすまし、悪意のあるリクエスト、  
ＳＱＬインジェクション攻撃等に対して考慮しています。

以下の２点を、このプラグインでなくして欲しいっす。  

1. 電話での空いている時間のやり取り  
2. 髪を切ってもらっている間に掛かってくる電話による中断  

== Installation ==

1. New installation
* Upload `salon-booking` to the `/wp-content/plugins/` directory.
* Activate the plugin through the 'Plugins' menu in WordPress.

2. Upgrade the plugin through the 'Plugins' menu in WordPress
* Click "update now" of the plugin.
* Deactivate the plugin.
* Activate the plugin.
  
* ＜注意＞  
プラグイン画面から更新した場合、  
一度「無効化」→「有効化」してください。 


== Frequently Asked Questions ==
1. [Docs](http://salon.mallory.jp/en/?page_id=80)
2. [ドキュメント](https://salon.mallory.jp/?page_id=143)

== Screenshots ==

1. 予約画面（PC）
2. 予約画面（スマートフォン）
3. 管理画面（環境設定）
4. 管理画面（スタッフ情報）
5. 管理画面（出退勤）

== Changelog ==
= 1.5.13 =
* 変更: 予約の確定方法をメールによる確定以外に、スタッフによる確定と即確定の２つを追加  

= 1.5.12 =
* 変更: 予約画面で表示するタブの指定を環境設定から可能にする設定を追加  
* 変更: nonceのチェックエラーになる場合に、セッションＩＤを使用しない環境設定を追加  
* 変更: スタッフが終了時間を設定できるオプション（メニューの時間で終了時間を決めない）を、店情報に追加  
* バグ: メニューのない店舗をスタッフ情報の一覧で割り当てるとメニュー情報が表示されないことへの対処  
* バグ: 弱い権限のスタッフの場合の予約時の不具合への対処  
* バグ: スマホ画面で必須項目が効いていないことへの対処  
* バグ: 表示等もろもろ  


= 1.5.11 =
* バグ: タップで反応しないことへの対処  


= 1.5.10 =
* 変更: 店舗の設定を、処理内で単一と分ける意味がなくなったため無条件で複数店舗にする。  
* バグ: メニューのデータを使用しているスタッフがいても、連続して削除するとエラーにならないことへの対処  
* バグ: 店舗のデータを全件削除して、再表示すると画面が崩れることへの対処  


= 1.5.9 =
* バグ: 0円の登録ができない対処  

= 1.5.8 =
* 変更: インストール時のコメントがエラーになる対処  

= 1.5.7 =
* 変更: チェックボックスの判定が有効にならない場合があることへの対応  
* バグ: 余計なタグ  

= 1.5.6 =
* 変更: Twenty Fifteenで画面がくずれることへの対応  


= 1.5.5 =
* バグ: ２４時超えの営業時間で、月末に２４時を超えた勤務時間を登録する際の不具合  
* バグ: ２４時超えの営業時間で、スマートフォンで表示がおかしくなる不具合  
* 変更: スマートフォン画面では読ませないようにしていたscirpt及びcss（軽量化）をやめ


= 1.5.4 =
* バグ: ２４時超えの営業時間で、２４時を超えた場合に前日表示する際、前日ではなく翌日を表示している不具合  


= 1.5.3 =
* 変更: ２４時超えの営業時間で、操作する時間が２４時を超えた場合、前日の日を初期表示する  
* 変更: 月タブ表示で、締切時間を有効にした場合でも当日入力可能にする。  
* 変更: その他いろいろ。  


= 1.5.2 =
* 追加: 夕方開店の翌朝閉店のような２４時を超えての営業時間への対応  
* 追加: 未作成のヘルプ  
* 変更: メニューや画面の名称を変更  


= 1.5.1 =
* 追加: 予約の登録・変更・確定等があった場合のお知らせメッセージ
* 追加: 顧客カルテ機能（顧客カルテ画面と項目情報画面）
* 変更: スタッフが１人の場合は、その人を選択状態にする。
* バグ: 店舗の閉店時間24時の場合、勤務時間のTO時間を24:00にすると表示されなくなる不具合  


= 1.4.11 =
* バグ: 休みなしの場合に表示がおかしくなる  


= 1.4.10 =
* バグ: 誰も扱えないメニューを一覧から消す論理の誤り  
* バグ: スマートフォン画面でのクーポン選択の不具合  
* バグ: メールのタイトルが変更できない  
* 変更: 実績画面で一覧にスタッフ名を表示  
* 変更: 出退勤の変則勤務時間をBOOKINGの登録に反映  
* 変更: UPLOADディレクトリを755へ  
* 変更: メニューの所要時間を10分単位に10分から240分までに  
* 変更: date.getTimeStamp,addをやめ  
* 変更: メールをtext形式に  


= 1.4.9 =
* バグ: クーポンなしでの登録エラー  
* バグ: 保守管理者なし指定での、スタッフセレクトの表示  
* バグ: 予約のステータスの矛盾など・・・
* 変更: 管理画面でのキャンセルと削除の区別  
* 変更: フロント画面でのキャンセルの扱い  
* 変更: AUTO_INCREMENTの取得をwpdbから  
