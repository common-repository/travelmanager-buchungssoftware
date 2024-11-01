=== Travelmanager Buchungssoftware ===
Contributors: curato
Donate link: https://travelmanager.de/
Tags: Travelmanager Reservierungssystem Buchungssoftware booking timetable fahrplan buchungssystem
Tested up to: 6.6
Requires at least: 6.0
Requires PHP: 8.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Dies ist die README-Datei für das Travelmanager WordPress Plugin. Für Informationen in Englisch, sehen Sie bitte die [englische Version der README](README_EN.md).

[Weiter zur englischen README](README_EN.md)

Lädt Inhalte wie die nächsten Abfahrten und ein Buchungskalender aus Ihrem Travelmanager Account und stellt sie direkt in Wordpress dar.

== Description ==

Die [Travelmanager Buchungssoftware](https://travelmanager.de) und [Tickyt Buchungssoftware](https://tickyt.de) sowie die  [Gastrozack Gastrokasse](https://gastrozack.app) ist das Reservierungssystem sowie die Gastrokasse für die Abbildung von Fahrten, Touren und Events in einer webbasierten Software. Das Travelmanager Plugin beinhaltet verschiedene Ansichten wie den Fahrplan (Die nächsten Abfahrten) sowie ein Buchungskalender.

**Event- und Themenfahrten**
In diesem Beispiel geben wir die nächsten Fahrten der Linien mit der ID 100 und 101 ab Station mit der ID 123 aus, wir suchen im Zeitraum von heute bis 3 Monate für die Ressource mit der ID1
<blockquote><pre>[travelmanager call="timetable" station_id="123" linie_id="100,101" account="youraccount.example.com" stop="3 months" ressource_id="1"]</pre></blockquote>

Möchte man nur die Daten strukturiert an Google übergeben, dann ergänzt man den Shortcode mit dem Parameter view="ldjson", also z.B.:
<blockquote><pre>[travelmanager call="timetable" station_id="123" linie_id="100,101" account="youraccount.example.com" stop="3 months" ressource_id="1" view="ldjson"]</pre></blockquote>
Der Shortcode erzeugt dann eine Ausgabe die von Google eingelesen und in den Suchergebnissen entsprechend verarbeitet wird. Testen kannst du die Ausgabe z.B. mit diesem Tool: [https://search.google.com/test/rich-results]

**Die nächsten Fahrten**
Die nächsten Abfahrten ab einer Station von heute bis in 3 Monate - In diesem Beispiel werden die nächsten Abfahrten der Station mit der ID 123 ausgegeben
<blockquote><pre>[travelmanager call="timetable" station_id="123" account="youraccount.example.com" stop="3 months" ressource_id="1"]</pre></blockquote>

Weitere Parameter:
* exact - ist dieser auf "true" gesetzt, wird in der Buchungsmaske nur eine Relation angezeigt - interessant z.B. bei Rundfahrten
* max - Anzahl der Suchergebnisse bei einer einer Abfrage. Halten Sie den Wert möglichst gering, der maximal mögliche Wert beträgt 350.
* lang - Gibt die Sprache an - de für deutsch und en für englisch

**Hinweis / Alert**
Im Travelmanager Backend hinterlegte Hinweise, wie z.B. Informationen über Fahrtverschiebungen, rufen Sie ganz einfach ab mit diesem Shortcode:
<blockquote><pre>[travelmanager call="alert" account="youraccount.example.com"]</pre></blockquote>

**Einfacher Kalender**
Kalenderansicht der nächsten Touren
Um den Kalender für die Zukunft darzustellen, steuert man mit dem Parameter "start" die gewünschte Anzeige, z.b. man berechnet vom jetzigen Datum:
* this month
* next month
* +3 months
* +4 months

oder man gibt den exakten Starttag an:
* 21.12.2021
* 31.12.2022

oder die nächsten Monate des Jahres
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

Soll der Link von einem verfügbaren Fahrtdatum in einem neuen Fenster öffnen, setzen Sie beim Aufruf den Parameter newwindow="true"

*Beispielshortcode*
<blockquote><pre>[travelmanager call="calendar" start="this month" station_id="123" linie_id="100,101" newwindow="false" account="youraccount.example.com"]</pre></blockquote>

**Veranstaltungskalender**
Kalender der Events für bestimmte Kategorien anzeigt.
<blockquote><pre>[travelmanager call="eventcalendar" station_id="1" account="youraccount.example.com" start="" linie_typ_id="1234" newwindow="true"]</pre></blockquote>

**Linien und Touren in Tabs**
Wählen Sie aus aus Ihren Linienkategorien und anschließend direkt die Fahrt mit dem interaktiven Fahrtenfinder.
<blockquote><pre>[travelmanager call="find" account="youraccount.example.com"]</pre></blockquote>

**Interaktive Suche mit Datumsauswahl**
Wählen Sie das Datum und die Kategorie um anschließend die nächsten Fahrten ab einer bestimmten Station direkt im Inhalt der Webseite anzuzeigen.
<blockquote><pre>[travelmanager call="list" station_id="1" account="youraccount.example.com"]</pre></blockquote>

**Interaktive Suche mit Monatsauswahl**
Wählen Sie den Monat und die Kategorie aus um anschließend die nächsten Fahrten ab einer bestimmten Station direkt im Inhalt der Webseite anzuzeigen.
<blockquote><pre>[travelmanager call="listcategories" station_id="1" account="youraccount.example.com"]</pre></blockquote>

**Eingebettete Onlinebuchung**
Binde das Buchungswidget ein mit diesem Shortcode. Der Parameter product_id beinhaltet die External Product ID einer Tour. Es wird eine Übersicht zu der Tour dargestellt mit der Möglichkeit direkt eine Fahrt auszuwählen und diese innerhalb der Seite zu buchen.
<blockquote><pre>[travelmanager call="eventinfo" product_id="example-1-2-1-2" account="example.travelmanager.software"]</pre></blockquote>

**Warenkorbsymbol**
Stelle den Inhalt des Warenkorbs direkt in der Website dar.
<blockquote><pre>[travelmanager call="basket" account="youraccount.example.com"]</pre></blockquote>

**Onlineshop**
Für den Verkauf reiner Artikel wie Gutscheine, Mehrfahrtenkarten usw., direkt im Inhalt deiner Seite. Der Parameter category_id entspricht der ID der Artikel-Kategorie aus dem Backend. Beim Aufruf der Seite mit dem Paramter &view=csv erhältst du alle Artikel im CSV Format, die du z.B. bei Google Shopping einbinden kannst. Das CSV gibst du mit einem Plain Template aus, damit kein Header und Footer sichtbar ist. Für die Ausgabe mit CSV gib noch den Parameter Target für die Produktseiten an.
<blockquote><pre>[travelmanager call="shop" account="youraccount.example.com" category_id="1,2"]</pre></blockquote>

**Fahrtfinder V2**
Mit dem Fahrtfinder kannst du deine Inhalte übersichtlich mit einem Suchfilter, z.B. auf deiner Startseite präsentieren.
<blockquote><pre>[travelmanager call="fahrtfinder2" station_id="2" signets="1" account="example.travelmanager.software"]</pre></blockquote>

**Sitemap**
Darstellung aller veröffentlichten Angebote auf einer Übersichtsseite
<blockquote><pre>[travelmanager call="sitemap" account="youraccount.example.com"]</pre></blockquote>

Darstellung aller veröffentlichten Angebote für Google Sitemap
?view=sitemap

== Häufig gestellte Fragen ==

- **Ich habe noch keinen Travelmanager Account, wo kann ich diesen eröffnen?**
  Besuchen Sie [diese Seite](https://travelmanager.de/kontakt/) und senden Sie eine Nachricht, um ein Konto zu erstellen.

- **Ich habe noch keinen Tickyt Account, wo kann ich diesen eröffnen?**
  Besuchen Sie [diese Seite](https://tickyt.de/kontakt/) und senden Sie eine Nachricht, um ein Konto zu erstellen.

- **Ich habe noch keinen Gastrozack Account, wo kann ich diesen eröffnen?**
  Besuchen Sie [diese Seite](https://gastrozack.app/mehr-infos/) und senden Sie eine Nachricht, um ein Konto zu erstellen.

- **Ich benötige Support für das Plugin, an wen kann ich mich wenden?**
  Unser Kundenservice hilft Ihnen gerne weiter. Kontaktieren Sie uns unter [service@travelmanager.de](mailto:service@travelmanager.de).

- **Ich benötige Support für das Plugin, an wen kann ich mich wenden? =

Schreiben Sie uns einfach eine Mail an service@travelmanager.de - wir freuen uns auf Ihre Nachricht!

- **Ich habe einen Funktionswunsch =

Schreiben Sie uns einfach eine Mail an service@travelmanager.de - wir freuen uns auf Ihr Feedback!

== Installation ==

1. Download and activate the plugin

2. Activate from dashboard

3. There are no settings

4. Insert the shortcode to your post,page or widget

== Screenshots ==

1. Beispielansicht aus der Travelmanager Online-Demo, Ansicht mit Buchungsbutton der nächsten Eventfahrten
2. Kalenderansicht der nächsten Touren
3. Die nächsten Abfahrten ab einer Station

== Über uns ==

Alle Informationen zur Travelmanager Buchungssoftware finden Sie unter [Travelmanager Buchungsssoftware](https://travelmanager.de "Travelmanager Buchungssoftware"), [Tickyt Buchungsssoftware](https://tickyt.de "Tickyt Buchungssoftware"), [Gastrozack Gastrokasse](https://gastrozack.app "Gastrozack Gastrokasse") und unter [PHCOM](https://www.phcom.de/ "PHCOM Informatik").

== Markdown ==
[Travelmanager Buchungssoftware](https://travelmanager.de)
[Ferry Software](https://ferry-software.com)
[Tickyt Buchungssoftware](https://tickyt.de)
[Gastrozack Kassensoftware](https://gastrozack.app)
[PHCOM Software](https://www.phcom.de)