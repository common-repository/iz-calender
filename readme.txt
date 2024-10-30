=== IZ-Calender ===

Contributors: Paul Cilliers
Tags: calender, calendar, Calender events, Caledar events, events, IZ, IZ-Calender
Requires at least: 3.0
Tested up to: 3.0
Stable tag: 1.3
License: GPLv2

== Description ==

IZ-Calender is a user-friendly callender view of events also providing a clean admin interface which enables you to add, update, delete and manage events. Add the calender to your theme and view upcoming events.

== Installation ==
1. Unpack the download-package
2. Upload the folder to your wp plugin directory of you wordpress instalation. 
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Admin Menu `IZ-Calender`
5. Add  `<phpcode><?php IZCalendar(); ?></phpcode>` to your posts,pages or text widget/s where you'd like the calender to display
6. Start adding events from the admin interface ( Menu `IZ-Calender` -> `Add new` )

== Screenshots ==
1. Admin interface - listing all events (WordPress 3.0.1)
2. Admin interface - Adding a new event (WordPress 3.0.1)
3. Default User interface (WordPress 3.0.1)
4. Custom styled User interface (WordPress 3.0.1)

== Changelog ==

= 1.1 =
* Spell out that the license is GPLv2
* Bug fix - stylesheet and image paths corrected
* Added div container id ui-izc to function "IZCalendar()" called by user-interface

= 1.3 =
* thanx to a plugin called PHP exec users can now add the callender to any page or sidebar


== Frequently Asked Questions ==



= Why is the user-interface not displaying correctly? =
Your template's style.css might be interfering with the default css of the plugin. To customize the way the plugin is displayed in your theme either create styles overwriting the the styles called from izcalender/css/iz-calender.css or customize izcalender/css/iz-calender.css to suit your theme. 

= How do I make suggestions or report bugs for this plugin? =
Email questions,bugs and suggestions to paul@intisul.co.za 