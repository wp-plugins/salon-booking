=== Salon Booking ===
Contributors: kuu(Tanaka Hisao)
Donate link: http://salon.mallory.jp/en/
Tags: hair salon,salon,appointment,booking,reservation,dental clinic,hospital,mutilingual
Requires at least: 3.5
Tested up to: 3.5
Stable tag: 1.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Salon Booking enables the reservation to one-on-one business between a client and a staff member.

== Description ==

Salon Booking enables the reservation to one-on-one business between a client and a staff member, 
namely those businesses like hair salon, hospital, dental clinic and so on..

Salon Booking requires neither member registration to make reservations from on the Web sites, 
nor loses prospective clients who hesitate to register personal information. 
To prevent the wrongful registration and reservation, 
the reservation procedure is devised for the clients with no registration at all 
as follows; "tentative reservation", "response to the e-mail address who tentatively reserved", 
and "confirmation by the client on the confirmation screen on the Web sites".
And if a client agrees to register, the reservation is done at once and 
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

== Installation ==

1. Upload `salon-booking` to the `/wp-content/plugins/` directory.
2. `/wp-content/plugins/salon-booking/uploads` directory change permissons(read and write).
3. Activate the plugin through the 'Plugins' menu in WordPress.

= alredey installed (version 1.2.1 or later) =

1. Backup photo-files under `/wp-content/plugins/salon-booking/uploads`
2. Deactivate this plugin.(Input datas are not lost)
3. Upload `salon-booking` to the `/wp-content/plugins/` directory.
4. Activate the plugin through the 'Plugins' menu in WordPress.
5. If photo-files were deleted ,restore the backup photo-files under `/wp-content/plugins/salon-booking/uploads`

= alredey installed (version 1.1.2 or earlier) =

1. Deactivate this plugin.(Input datas are not lost)
2. Remove `salon-booking` to the `/wp-content/plugins/` directory.
3. Upload `salon-booking` to the `/wp-content/plugins/` directory.
4. `/wp-content/plugins/salon-booking/uploads` directory change permissons(read and write).
5. Activate the plugin through the 'Plugins' menu in WordPress.
6. Upload the photo of staff member again.(I had no time to make function that old photos move new photos.Sorry)

== Frequently Asked Questions ==
1. [Docs](http://salon.mallory.jp/en/?page_id=80)

== Screenshots ==

1. Reservation form
2. Reservation form(detail)
3. Admin form(environment)
4. Admin form(staff)
5. Admin form(menu)
6. Admin form(shop)
7. Admin form(customer)
8. Admin form(reservation)
9. Admin form(performance)
10. Admin form(timecard)

== Changelog ==

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


