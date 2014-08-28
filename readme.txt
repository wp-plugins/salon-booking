=== Salon Booking ===
Contributors: kuu(Tanaka Hisao)
Tags: hair salon,salon,appointment,booking,beauty apps,reservation,dental clinic,hospital,mutilingual,散髪予約,美容院予約,美容室予約,サロン予約,エステ予約,予約システム,予約管理
Requires at least: 3.8
Tested up to: 3.8.1
Stable tag: 1.4.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

散髪屋さん・美容院向けの予約システムです。Salon Booking enables the reservation to one-on-one business.

== Description ==

このプラグインは、散髪屋さんや美容院の予約を  
Ｗｅｂ上から可能にするものです。  
散髪屋さん・美容院をターゲットにしていますが、  
顧客に対して、スタッフが１対１でサービスを提供する職業、  
例えば病院、歯医者、ネイルサロン、マッサージなどでも  
使用可能だと思います。

Salon Booking enables the reservation to one-on-one business  
between a client and a staff member, 
namely those businesses like hair salon, hospital, dental clinic and so on..

世間の予約システムは会員登録が必須となっている場合が多いですが、  
登録自体が面倒だったり、個人情報を登録することに躊躇したりと、  
結果として顧客を逃している場合があるかと思います。

Salon Booking requires neither member registration to make reservations  
from on the Web sites, nor loses prospective clients  
who hesitate to register personal information. 

このプラグインでは、会員登録をしなくとも予約できます。  
会員登録を躊躇する顧客も逃しません。多分。。。  
会員登録していない顧客の予約は、  
「仮予約」→「予約した人へのメール」→  
「確定画面による予約完了」の流れをとることにより、  
あやしい連中の予約を防ぎます。

To prevent the wrongful registration and reservation,  
the reservation procedure is devised for the clients  
with no registration at all as follows;  
"tentative reservation",  
"response to the e-mail address who tentatively reserved",  
and "confirmation by the client on the confirmation screen on the Web sites".

会員登録してもらえば、すぐに予約完了となりますし、  
予約に対する変更も可能になりますので、  
２回目以降は、会員登録してもらえるかと。

And if a client agrees to register,  
the reservation is done at once and  
the change of the reservation is also becoming easier, 
which might be an additional incentive for the clients to register.

予約はグーグルカレンダーのようなインタフェースで入力できます。  
予約の変更はドラッグ＆ドロップで可能にしているので、  
スタッフが電話等で受け付ける際の時間調整でも便利かと思います。

The interface for the reservation is easy and like that of Google Calendar.  
The change of the reservation is possible by means of drag and drop,  
which enables also the staff member phoned by a client for the change  
of the request can easily change the schedule accordingly.

スタッフのシフト管理や出退勤管理も同時に行えます。  
予約可能時間は出退勤管理と連動します。  
例えば、スタッフが午前休みの場合は、  
その日の午前のスタッフに対する予約はできなくなります。

Salon Booking is also capable of the personnel management of the staff member  
on the shift control and time recording.  
Of course the possible time ranges of reservation  
and the attendance of the staff member co-relates automatically. 

予約に対する実績も登録できるので、  
お客さまの要望などや作業時間などの情報を蓄積することにより、  
サービスの向上につなげていくことができます。  
そのうち連動した統計や会計の機能をつくります。多分。。。  

Salon Booking can also record the actual performance against the reservation  
and capable of compiling the information on the demands from the clients  
and working results.  
So, it is very useful in improving the service quality and the operation management of staff.

セキュリティに関しても、なりすまし、悪意のあるリクエスト、  
ＳＱＬインジェクション攻撃等に対して考慮しています。

As for the security, all the measures are included in this system,  
namely against identity frauds, malicious requests to reserve, SQL injection attacks and so.

以下の２点を、このプラグインでなくして欲しいっす。  

1. 電話での空いている時間のやり取り  
2. 髪を切ってもらっている間に掛かってくる電話による中断  

== Installation ==

1. Upload `salon-booking` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

3. Already Installed,input mail text again.


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
= 1.4.8 =
* Added: function of "Promotion".
* Fixed: CSS of "month tab".
* Changed: "lang" directory to "language" directory.

= 1.4.7 =
* Added: "Deadline of reservations" at the screen of "configuration setting".


= 1.4.6 =
* Added: the screen of "mail".
* Fixed: and so on.

= 1.4.5 =
* Added: Display a self-introduce of staff member at the screen of "Reservation(PC)".
* Fixed: and so on.

= 1.4.4 =
* Changed: When a customer login,"name","tel" or "mail" can't be changed.
* Added: Check direct address input by customer login id.
* Fixed: After remove the staff member,same name regist again.
* Fixed: Working data can't delete.

= 1.4.3 =
* Added: Check of staff and menu at the server side of the application.
* Changed: Staff member can update or delete temporary reservation.

= 1.4.2 =
* Fixed: UserId evised an updated bug.
+ Added: At the screen of "Reservation",select default load tab.


= 1.4.1 =
* Added: New items of the screen of "Menu".

= 1.3.10 =
* Fixed: 

= 1.3.9 =
* Changed: When staff member,show admin menu button at the screen of "Reservation".

= 1.3.8 =
* Added: At the screen of "Reservation Regist",add "status" items.
* Fixed: At the screen of "Reservation Regist",wrong display fields.

= 1.3.7 =
* Added: New screen for smart phone.
* Fixed: At the screen of "Booking",missing the display of "month" scr ....

= 1.3.6 =
* Added: The ability to set "From" and "Return-path" fields of mail header.

= 1.3.5 =
* Fixed: booking call wrong parameters.

= 1.3.4 =
* Changed: Upload Photo directory.
* Changed: When uninsatll mbstring module.

= 1.3.3 =
* Changed: Maitenance staff can't change position.
* Changed: Upload Photo area's css
* Fixed:   After staff postion changed ,the position role is not available.

= 1.3.2 =
* Changed: At the screen of "Staff Setting",staff can change the sequence.
* Changed: At the screen of "Environment Setting",add the option that maintenance staffs is not shop staff member.
* Changed: After install,"plural shops" is default select.

= 1.3.1 =
* Fixed:   htmlspecialchars(javascript)
* Changed: At the screen of "Menu Setting",menu can change the sequence.
* Changed: At the screen of "Reservation",display menu items changed.
* Changed: At the screen of "Performance",display deleted menu and deleted staff member.

= 1.2.2 =
* Fixed: use split function changed explode function.
* Fixed: missing updated photo data.
* Fixed: missing screen when no closed.
* Changed: display "*" on required items.
* Changed: datepicker's default value.

= 1.2.1 =
* Changed: Two or more staff photo setup was enabled.

= 1.1.2 =
* Fixed: At the screen of "Environment Setting",style broken in InternetExplorer.
* Fixed: missing check of "Performance Regist".
* Fixed: missing diplay of "Reservation Regist".

= 1.1.1 =
* Fixed: missing check of "Reservation Regist".
* Fixed: missing display of "Basic Information".
* Changed: At the screen of "Booking",after input password,automaticaly login.

= 1.1.0 =
* Added: At the screen of "Reservation Regist" and "Performance Regist", "ID" of registered newly clients displayed.
* Changed: At the screen of "Booking",maintenance staff display.

= 1.0.0 =
* Added: Administrator can view log.
* Fixed:missing no write of the "Config" log.
* Changed: Default values of "Config".

= 0.4.0 =
* Fixed: At the screen of "Search",selected header line don't filled in.
* Fixed: At the screen of "Search",missing the sequence of Sur name and Given name.
* Fixed: missig the display name of non registerd staff.

= 0.3.0 =
* Fixed: At the screen of "Reservation Detail",staff member  can use the function of search.
* Fixed: Button's display changed "display details" -> "show details".

= 0.2.0 =
* Changed: Staff member can regist without mail-input.
* Changed: If today is holiday ,init display is next day.
* Changed: "Number of the Shops" is "plural shops" as a default.

= 0.1.0 =
* alpha version first release. 


== Upgrade Notice ==


