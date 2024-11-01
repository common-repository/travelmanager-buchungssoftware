=== Travelmanager booking software ===
Contributors: curato
Donate link: https://travelmanager.de/
Tags: travel manager reservation system booking software booking timetable booking system
Tested up to: 6.6
Requires at least: 6.0
Requires PHP: 8.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This is the README file for the Travelmanager WordPress plugin. For information in German, please see the [German version of the README](README.md).

[Go to the German README](README.md)

Loads content such as the next departures and a booking calendar from your Travelmanager account and displays it directly in Wordpress.

== Description ==

The [Travelmanager booking software](https://ferry-software.com) is the reservation system for the mapping of trips, ferry connections, tours and events in a web-based software. The Travelmanager plugin contains various views such as the timetable (next departures) and a booking calendar.

**Event and theme trips**
In this example we display the next trips of the lines with the ID 100 and 101 from station with the ID 123, we search in the period from today to 3 months
<blockquote><pre>[travelmanager call="timetable" station_id="123" line_id="100,101" account="youraccount.example.com" stop="3 months"]</pre></blockquote>

**The next journeys**
The next departures from a station from today until 3 months from now - In this example, the next departures from the station with ID 123 are displayed
<blockquote><pre>[travelmanager call="timetable" station_id="123" account="youraccount.example.com" stop="3 months"]</pre></blockquote>

Further parameters:
* exact - if this is set to "true", only one relation is displayed in the booking mask - interesting for round trips, for example
* max - number of search results for a query. Keep the value as low as possible, the maximum possible value is 350.
* lang - Specifies the language - de for German and en for English

**Note / Alert**
Alerts stored in the Travelmanager backend, e.g. information about travel delays, can be easily retrieved with this shortcode:
<blockquote><pre>[travelmanager call="alert" account="youraccount.example.com"]</pre></blockquote>

**Simple calendar**
Calendar view of the next tours
To display the calendar for the future, use the "start" parameter to control the desired display, e.g. calculate from the current date:
* this month
* next month
* +3 months
* +4 months

or you enter the exact start date:
* 21.12.2021
* 31.12.2022

or the next months of the year
* january
* february
* march
* april
* may
* june
* july
* august
* september
* october
* november
* december

If the link from an available trip date should open in a new window, set the parameter newwindow="true" when calling the link

*example shortcode
<blockquote><pre>[travelmanager call="calendar" start="this month" station_id="123" line_id="100,101" newwindow="false" account="youraccount.example.com"]</pre></blockquote>

**Calendar of events**
Calendar showing events for specific categories.
<blockquote><pre>[travelmanager call="eventcalendar" station_id="1" account="youraccount.example.com" start="" line_typ_id="1234" newwindow="true"]</pre></blockquote>

**Relations and tours in tabs**
Choose from your line categories and then directly select the journey with the interactive journey finder.
<blockquote><pre>[travelmanager call="find" account="youraccount.example.com"]</pre></blockquote>

**Interactive search with date selection**
Select the date and category to then display the next trips from a specific station directly in the content of the website.
<blockquote><pre>[travelmanager call="list" station_id="1" account="youraccount.example.com"]</pre></blockquote>

**Interactive search with month selection**
Select the month and category to display the next trips from a specific station directly in the content of the website.
<blockquote><pre>[travelmanager call="listcategories" station_id="1" account="youraccount.example.com"]</pre></blockquote>

**Embedded online booking**
Include the booking widget with this shortcode. The product_id parameter contains the external product ID of a tour. An overview of the tour is displayed with the option to directly select a trip and book it within the page.
<blockquote><pre>[travelmanager call="eventinfo" product_id="example-1-2-1-2" account="example.travelmanager.software"]</pre></blockquote>

**Onlineshop**
For the sale of pure articles such as vouchers, multi-ride tickets, etc., directly in the content of your page. The category_id parameter corresponds to the ID of the item category from the backend. When you call up the page with the parameter &view=csv, you will receive all articles in CSV format, which you can integrate into Google Shopping, for example.
<blockquote><pre>[travelmanager call="shop" account="youraccount.example.com" category_id="1,2"]</pre></blockquote>

**Shopping cart icon**
Display the contents of the shopping cart directly on the website.
<blockquote><pre>[travelmanager call="basket" account="youraccount.example.com"]</pre></blockquote>

**Sitemap**
Preview of all published tours in the content
<blockquote><pre>[travelmanager call="sitemap" account="youraccount.example.com"]</pre></blockquote>

List of all published tours for Google Sitemap
?view=sitemap

== Frequently asked questions ==

- I don't have a Travelmanager account yet, where can I create one?
  Visit [this page](https://travelmanager.de/kontakt/) and send a message to create an account.

- **I don't have a Tickyt account yet, where can I create one?
  Visit [this page](https://tickyt.de/kontakt/) and send a message to create an account.

- **I don't have a Gastrozack account yet, where can I create one?
  Visit [this page](https://gastrozack.app/mehr-infos/) and send a message to create an account.

- **I need support for the plugin, who can I contact?
  Our customer service will be happy to help you. Contact us at [service@travelmanager.de](mailto:service@travelmanager.de).

- **I need support for the plugin, who can I contact? =

Simply send us an e-mail to service@travelmanager.de - we look forward to hearing from you!

- **I have a feature request =

Just send us an e-mail to service@travelmanager.de - we look forward to your feedback!

== Installation ==

1. download and activate the plugin

2. activate from dashboard

3. there are no settings

4. insert the shortcode to your post,page or widget

== Screenshots ==

1. example view from the Travelmanager online demo, view with booking button of the next event trips
2. calendar view of the next tours
3. the next departures from a station

== About us ==

All information about the Travelmanager booking software can be found at [Travelmanager Buchungsssoftware](https://travelmanager.de "Travelmanager Buchungssoftware"), [Tickyt Buchungsssoftware](https://tickyt.de "Tickyt Buchungssoftware"), [Gastrozack Gastrokasse](https://gastrozack.app "Gastrozack Gastrokasse") and at [PHCOM](https://www.phcom.de/ "PHCOM Informatik").

== Markdown ==
[Travelmanager booking software](https://travelmanager.de)
[Ferry Software](https://ferry-software.com)
[Tickyt booking software](https://tickyt.de)
[Gastrozack POS software](https://gastrozack.app)
[PHCOM Software](https://www.phcom.de)