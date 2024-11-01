<?php
/*
Plugin Name: Travelmanager und Tickyt Buchungssoftware
Plugin URI: https://travelmanager.de/funktionen/wordpress-plugin/
Description: Inhalte aus der Travelmanager, Tickyt Buchungssoftware und Gastrozack Gastrokasse direkt in WordPress einbinden
Version: 21.77
Author: Philipp Stäbler
Text Domain: travelmanager
License: GPLv2
Released under the GNU General Public License (GPL)
https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
*/

include( plugin_dir_path( __FILE__ ) . 'ajax/target.php');
require_once plugin_dir_path( __FILE__ ) . 'libs/functions.php';

if( !function_exists('get_plugin_data') ){
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

$locale = get_locale();
if ( $locale == 'de_DE' OR $locale == 'de' OR $locale == 'de_AT') {
	require_once plugin_dir_path( __FILE__ ) ."libs/dialoge_de.php";
} else {
	require_once plugin_dir_path( __FILE__ ) ."libs/dialoge_en.php";
}

/* Cache deaktivieren */
if (function_exists('wp_cache_disable')) {
	wp_cache_disable();
}

function travelmanager ($atts )
{
	global $dialog, $locale;
	$regional = $locale=="de_DE"?"de":"en";
    $plugin_data = get_plugin_data( __FILE__ );
    $plugin_version = $plugin_data['Version'];

    setlocale(LC_TIME, "de_DE");

    $Ausgabe="";
    $a = shortcode_atts( array(
        'account' => '',
        'linie_id' => '',
        'newwindow' => '',
        'start' => '',
        'lang' => '',
        'view' => '',
        'godirect' => '',
        'target' => '',
        "exact" => "",
        "vermittler_id" => "",
        "mandant_id" => "",
        "product_id" => "",
        "linie_typ_id" => "",
        'stop' => '',
        'max' => '',
        'station_id' => '',
        'show_station' => '',
        'show_category' => '',
        'category_id' => '',
        'signets' => '',
        'current_date' => '',
        'ressource_id' => '',
        'call' => ''
    ), $atts );

    //Sanitize
    if(empty($a['vermittler_id'])){
        $a['vermittler_id'] = "";
    }else{
        $a['vermittler_id'] = (integer)$a['vermittler_id'];
    }

    $a['max'] = (integer)$a['max'];

    //Standard-Call
    if(empty($a['call']))
    {
        $a['call']="timetable";
    }

    if(empty($a['account']))
    {
        return "Please define your account";
    }

    $start = $a['start'];
    $stop = $a['stop'];
    $max = $a["max"];
    $cacheMinutes=30;

	$url="";
	if($a["call"]=="demo"){
		$url = "https://".$a['account']."/xwordpress_endpoint.php";
	}
	elseif($a["call"]=="alert")
    {
        $url = "https://".$a['account']."/api.php?from=wordpress&call=alert&json=true";
	    $cacheMinutes=5;
    }
	elseif($a["call"]=="basket" && (!empty(get_transient( "basket_".tm_get_session_id())) OR isset($_GET["insertIntoBasket"]) OR isset($_GET["insertIntoBasketEV"])))
	{
		$url = "https://".$a['account']."/xwordpress_endpoint.php?call=warenkorb&session_id=".tm_get_session_id()."&rand=".rand(0,100);
		$cacheMinutes=-1;
	}
	elseif($a["call"]=="basket")
	{
		$url = "";
	}
	elseif($a["call"]=="eventcard")
	{
		$url = "https://".$a['account']."/xwordpress_endpoint.php?call=eventcard&product=".$a["product_id"]."&rand=".rand(0,100);
	}
	elseif(isset($_GET["insertIntoBasket"]) && $_GET["insertIntoBasket"]=="true" AND !empty($_GET["relation"]) && $a["call"]=="eventinfo")
	{
		$url = "";
	}
	elseif($a["call"]=="eventinfo" OR !empty($_GET["relation"]))
	{
		if(!empty($_GET["relation"])){
			$a["call"]="eventinfo";
			$a["product_id"]=$_GET["relation"];
		}
		$url = "https://".$a['account']."/xwordpress_endpoint.php?call=eventinfo&product=".$a["product_id"];
	}
	elseif($a["call"]=="shop")
	{
		$url = "https://".$a['account']."/xwordpress_endpoint.php?call=shop&category_id=".$a["category_id"];
	}
	elseif($a["call"]=="artikel")
	{
		$url = "https://".$a['account']."/xwordpress_endpoint.php?call=artikelinfo&product_id=".$a["product_id"];
	}
	elseif($a["call"]=="sitemap")
	{
		$linie_typ_id="";
		$linien_ids="";
		if(isset($a["linie_typ_id"]) AND !empty($a["linie_typ_id"])){
			$linie_typ_id=$a["linie_typ_id"];
		}
		if(isset($_GET["linien_ids"]) AND !empty($a["linien_ids"])){
			$linien_ids=$a["linien_ids"];
		}

		$url = "https://".$a['account']."/xwordpress_endpoint.php?call=sitemap&linie_typ_id=".$linie_typ_id."&linien_ids=".$linien_ids;
	}
	elseif($a["call"]=="fahrtfinder2" OR $a["call"]=="fahrtfinder2adler")
	{
		$linie_typ_id="";
		if(isset($a["linie_typ_id"]) AND !empty($a["linie_typ_id"])){
			$linie_typ_id=$a["linie_typ_id"];
		}
		$url = "https://".$a['account']."/xwordpress_endpoint.php?call=fahrtfinder2&linie_typ_id=".$linie_typ_id;
	}
	//Eventliste
	elseif($a['call']=="timetable" OR $a['call']=="timetablev2"){
		if(empty($start))
		{
			$start = strtotime("today 00:00");
		}
		elseif(travelmanager_ConvertDeutschesDatumIntoTS($start)>strtotime("-1 month")){
			$start = travelmanager_ConvertDeutschesDatumIntoTS($start);
		}
		elseif(!empty($start)){
			$start = strtotime($start);
		}

		$station_id=0;
		if(isset($a['station_id'])){
			$station_id = $a['station_id'];
		}

		if($start<strtotime("-1 month")){
			$start = strtotime("today 00:00");
		}

		//Stopdatum ist angegeben als String
		if(!empty($stop) AND is_string($stop))
		{
			$stop = strtotime("+".$stop,$start);
			if($stop<$start)
			{
				$stop = strtotime("+1 month",$start);
			}
		}
		else
		{
			$stop = strtotime("+1 month",$start);
		}
		if(empty($max) OR $max>500 OR is_numeric($max)==false)
		{
			$max=250;
		}


		$url = "https://".$a['account']."/xwordpress_endpoint.php?call=timetablev3&station_id=".$station_id."&max=".$max."&datum=".$start."&datum_stop=".$stop;

		if(!empty($a["mandant_id"])){
			$url.="&mandant_id=".(int)$a["mandant_id"];
		}

		if(!empty($a['linie_typ_id'])){
			$url.="&linie_typ_id=".$a['linie_typ_id'];
		}
		elseif(!empty($a['linie_id'])){
			$url.="&linien_ids=".$a['linie_id'];
		}

		if(!empty($a["ressource_id"]) && (int)$a["ressource_id"]>0){
			$url.="&ressource_id=".(int)$a['ressource_id'];
		}
	}
    elseif($a["call"]=="contingent"){
        $url = "https://".$a['account']."/xwordpress_endpoint.php?call=contingent&product_id={$a["product_id"]}&start={$a['start']}&stop={$a['stop']}&max={$a['max']}";
    }
	//Eventkalender - Rückgabe als HTML
    elseif($a['call']=="calendar")
    {
        if(empty($a['linie_id']) AND empty($a['linie_typ_id']))
        {
            return "Please define Parameter linie_id";
        }

        if(empty($start))
        {
            $start = "this month";
        }

        $url = "https://".$a['account']."/timetable.php?aktion=verkehrstage&hafen_id={$a['station_id']}&start_datum=".urlencode($start)."&linien_ids={$a['linie_id']}";

	    if(!empty($a['linie_typ_id'])){
		    $url.="&linie_typ_id=".$a['linie_typ_id'];
	    }

        if($a["newwindow"]=="true")
        {
            $url.="&newwindow=true";
        }
    }
    elseif($a['call']=="eventcalendar")
    {
        if(empty($a['linie_typ_id']) AND empty($a['station_id']))
        {
            return "Please define Parameter linie_typ_id OR station_id";
        }

        //Immer mit dem aktuellen Monat starten
        if(!empty($a['start']) && strtotime($a['start'])>strtotime("-1 month")){
            $start = $a['start'];
        }
        else{
            $start = "this month";
        }

		if(isset($_GET["station_id"])==false && !isset( $a['station_id'])){
			$station_id = 0;
		}
        elseif(isset($_GET["station_id"]) && (int)$_GET["station_id"]>0){
            $station_id = (int)$_GET["station_id"];
        }
        else{
            $station_id = $a['station_id'];
        }

        if(!empty($_GET["linie_typ_id"])){
            $linie_typ_id = $_GET["linie_typ_id"];
        }
        else{
            $linie_typ_id = $a['linie_typ_id'];
        }

        $godirect="false";
        if(isset($_GET["godirect"]) && $_GET["godirect"]=="true"){
            $godirect="true";
        }

        $url = "https://".$a['account']."/timetable.php?aktion=eventcalendar&return=json&linie_typ_id={$linie_typ_id}&station_id={$station_id}&datum=".urlencode($start)."&godirect=".$godirect;

        if($a["newwindow"]=="true")
        {
            $url.="&newwindow=true";
        }
    }
    elseif($a['call']=="find"){
        $url = "https://".$a['account']."/timetable.php?aktion=fahrtsuche&modus=json";
        $cacheMinutes = 60;
    }
    elseif($a['call']=="listcategories"){
        $url = "https://".$a['account']."/timetable.php?aktion=listcategories&station_id=".$a["station_id"]."&linie_typ_id={$a['linie_typ_id']}";
        $cacheMinutes = 180;
    }
    elseif($a['call']=="list"){
        $url = "https://".$a['account']."/timetable.php?aktion=fahrtsuche&modus=json&station_id=".$a["station_id"];
        $cacheMinutes = 180;
    }
    else
    {
        return "Invalid call parameter";
    }

	//Wenn es eine URL Gibt - Daten holen
	if(!empty($url)){
		$url.="&from=wordpress";
		$url.="&version=".$plugin_version;
		$url.="&vermittler_id=".$a['vermittler_id'];

		//Sprache
		if(!empty($a["lang"])){
			$url.="&lang=".$a["lang"];
		}
		elseif (get_locale() == 'de_DE') {
			$url.="&lang=de";
		}
		else{
			$url.="&lang=en";
		}

		//URL speichern
		$keyHash = travelmanager_save_hash($url);
		$result = travelmanager_request_content($url,$cacheMinutes);
		if(array_key_exists("fehler",$result) && $result["fehler"]==true){
			return $result["msg"];
		}
		$json_data = $result["msg"];
	}
	else{
		$json_data = json_encode( []);
	}


    //Daten ausgeben - HTML
	if($a['call']=="demo")
	{
		$Ausgabe.='
		<div class="container">
			<div class="row">
			    <div class="col-6">Inhalt 1</div>
			    <div class="col-6">Inhalt 2</div>
			</div>
		</div>
		';
	}
	//Erfolgsmeldung nach Hinzufügen eines Einzelverkauf in den Warenkorb
	elseif(isset($_GET["insertIntoBasketEV"]) && $_GET["insertIntoBasketEV"]=="true" && $a["call"]=="shop")
	{
		$shopDomain = $_GET["return"];
		$url_add_booking = $_SERVER["REDIRECT_URL"];
		$Ausgabe.='
		<div class="success_message">
			'.$dialog["basket_item_success"].'
			<div class="wp-block-columns">
			    <div class="wp-block-column">
			    	<a href="'.$shopDomain.'basket" class="buttonShop">'.$dialog["pay_now"].'</a>
				</div>
			    <div class="wp-block-column">
			    	<a href="'.$url_add_booking.'" class="buttonShop">'.$dialog["weiterer_artikel"].'</a>
				</div>
			</div>
		</div>
		';
	}
	//Erfolgsmeldung nach Hinzufügen in den Warenkorb
	elseif(isset($_GET["insertIntoBasket"]) && $_GET["insertIntoBasket"]=="true" AND !empty($_GET["relation"]) && $a["call"]=="eventinfo")
	{
		if(!isset($_GET["return"])){
			$shopDomain = "https://".$a['account']."/";
		}
		else{
			$shopDomain = $_GET["return"];
		}
		$url_add_booking = "?relation=".$_GET["relation"];
		$Ausgabe.='
		<div class="success_message">
			'.$dialog["basket_success"].'
			<div class="wp-block-columns">
			    <div class="wp-block-column">
			    	<a href="'.$shopDomain.'basket">'.$dialog["pay_now"].'</a>
				</div>
			    <div class="wp-block-column">
			    	<a href="'.$url_add_booking.'">'.$dialog["weitere_buchung"].'</a>
				</div>
			</div>
		</div>
		';
	}
	//Kalender - wird als HTML zurück gegeben
	elseif($a['call']=="calendar")
    {
        $Ausgabe.=$json_data;
    }
	//Daten als JSON parsen
    elseif(travelmanager_isJson($json_data))
    {
        $json = json_decode($json_data,true);

        //Kontingent-Ausgabe in Liste oder als Kalender
        if($a['call']=="contingent"){

            if($json["success"]==false){
                $Ausgabe=$json["meldung"];
            }
            elseif($a["view"]=="calendar")
            {
                $objektID = uniqid("cal");

                $Ausgabe = "<div class='calendarcontingent' hash='{$keyHash}'></div>";

                //Array anpassen
                $dataCal = array();
                $beschreibung ="";
                if(!empty($json)){
                    foreach($json["data"] as $row){
                        if(empty($beschreibung)){
                            $beschreibung=$row["tour"]." ".$row["start_station"];
                            if($row["start_station"]!=$row["stop_station"]){
                                $beschreibung.=" - ".$row["stop_station"];
                            }
                        }
                        $datum = strtotime($row["datum"]);
                        if(empty($datum) OR !is_numeric($datum)){
                            $datum = strtotime("now");
                        }
	                    $key = date("Y-m-d", (int)$datum);
	                    $bez = explode(' ', $row["kontingent"][0]["preistyp"], 3);
                        $bezeichnung = str_replace("Euro", "€", $bez[0]."". $bez[1]);
                        $dataCal[$key]=array("free"=>$row["kontingent"][0]["free"],"url"=>$row["url"],"bezeichnung"=>$bezeichnung);
                    }
                }

                $newWindow='false';
                if($a["newwindow"]=="true")
                {
                    $newWindow='true';
                }

                //Startdatum definieren
                if(empty($start))
                {
                    $start = strtotime("this month");
                }
                elseif(travelmanager_ConvertDeutschesDatumIntoTS($start)>strtotime("-1 month")){
                    $start = travelmanager_ConvertDeutschesDatumIntoTS($start);
                }

                if(empty($start) OR !is_numeric($start))
                {
                    $start = strtotime("now");
                }


                $Ausgabe.="
                <div class='beschreibung'>{$beschreibung}</div>
                <div id='{$objektID}' data='".rawurlencode(json_encode($dataCal))."' newwindow='{$newWindow}'></div>

                <script>
                jQuery(function() {
                    var dateToday = new Date('".date("Y-m-d", (int)$start)."');
                    jQuery.datepicker.setDefaults(calendarLocale);
                    var days = JSON.parse(decodeURIComponent(jQuery('#{$objektID}').attr('data')));
                    
                    jQuery('#{$objektID}').datepicker({
                        numberOfMonths: 1,
                        regional:'{$regional}',
                        dateFormat : 'yy-mm-dd',
                        minDate: dateToday, 
                        maxDate: 360,
                        onChangeMonthYear: function(year,month){
                            if(parseInt(month)<10){
                                month='0'+month;
                            }
                            try{
                                clearTimeout(myTimeout);
                            }catch (err) {}

                            myTimeout = setTimeout(function(){
                            
	                            var data = {};
	                            data['datum'] = year+'-'+month+'-01';
	                            data['action'] = 'travelmanager_eventinfo_rueckfahrt';
	                            data['hash']='{$keyHash}';
	                    
	                            jQuery.blockUI({ message: '{$dialog["inhalt_laden"]}...'});
	                            jQuery.post(ajaxurl, data, function(response) { 
	                                jQuery.unblockUI();
	                                console.log(response);
	                                days = response;
	                                jQuery('#{$objektID}').datepicker('refresh');
	                            } );
                                
                            },500);
                        },


                        //Tag ist ausgewaehlt
                        onSelect: function (day) {
                            if (days[day]!==undefined) {
                                if(jQuery('#{$objektID}').attr('newwindow')){
                                    window.open(days[day]['url']);
                                }
                                else{
                                    window.location.href=days[day]['url'];
                                }
                            }
                        },           
                        beforeShowDay: function (day) {
                            var dayFormat = jQuery.datepicker.formatDate('yy-mm-dd', day);
                            var tag = parseInt(jQuery.datepicker.formatDate('dd', day));
                             
                            if (days[dayFormat]===undefined || parseInt(days[dayFormat]['free'])<1) {
                                return [false,'notavailable contingentcell',''];
                            } else {
                                var title=days[dayFormat]['bezeichnung'];
                                setTimeout(function(){
                                    jQuery('#{$objektID} .available a[data-date=\"'+tag+'\"]').html(tag+'<sub>'+title+'</sub>');
                                },200);
                                return [true,'available contingentcell',title+' {$dialog["verfuegbar"]}'];
                                
                            } 
                        }
                    })
                    
                });
                </script> 
                ";


            }
            else{
                $Ausgabe = "<div class='listcontingent' hash='{$keyHash}'>";
                foreach($json["data"] as $idx=>$row){
                    $datum = date("d.m.Y",strtotime($row["datum"]));

                    if($idx==0){

                        $Ausgabe .= "<div class='linie_bezeichnung'>{$row["tour"]}: {$row["start_station"]} -> {$row["stop_station"]}</div>";
                        $Ausgabe .="
                            <div class='wp-block-columns headline'>
                                <div class='wp-block-column'>
                                    <div class='datum'>{$dialog["date"]}</div>
                                </div>
                                <div class='wp-block-column'>
                                    <div class='preistyp'>{$dialog["ticket"]}</div>
                                </div>
                                <div class='wp-block-column'>
                                    <div class='anzahl'>{$dialog["anzahl"]}</div>
                                </div>
                                <div class='wp-block-column'></div>
                            </div>
                            ";
                    }

                    foreach($row["kontingent"] as $pt){
                        $preistyp = $pt["preistyp"];
                        $Ausgabe.="
                            <div class='wp-block-columns content'>
                                <div class='wp-block-column'>
                                    <div class='datum'>{$datum} {$row["uhrzeit"]} Uhr</div>
                                </div>
                                <div class='wp-block-column'>
                                    <div class='preistyp'>{$preistyp}</div>
                                </div>
                                <div class='wp-block-column'>
                                    <div class='anzahl'>{$pt["free"]}</div>
                                </div>
                                <div class='wp-block-column'>
                                    <a href='{$row["url"]}'>{$dialog["jetzt_buchen"]}</a>
                                </div>
                            </div>
                            ";
                    }

                }

                $Ausgabe .= "</div>";
            }
        }
		elseif($a["call"]=="artikel"){
			$artikel = $json["artikel"];

			//HTML Ausgabe
			$Ausgabe = "<div class='artikel_container'>";
			$Ausgabe.="<h1 class='bezeichnung'>".$artikel["bezeichnung"]."</h1>";

			if(count($artikel["files"])>0){
				$Ausgabe.= travelmanager_glightbox_galerie($artikel["files"]);
				$Ausgabe.="<div class='hinweis_bilder'><sub>{$dialog["klicke_gross"]}</sub></div>";
			}
			if(!empty($artikel["beschreibung"])){
				$Ausgabe.="<div class='beschreibung'>".$artikel["beschreibung"]."</div>";
			}
			if(!empty($artikel["betrag"])){
				$Ausgabe.="<div class='artikel_betrag'>".$artikel["betrag"]."</div>";
			}

			$Ausgabe .= "<div class='kaufen_div'><a href='".$artikel["url"]."'>{$dialog["klicke_shop"]}</a></div>";
			$Ausgabe.="</div>";
		}
        elseif($a["call"]=="basket"){
	        $session_id = tm_get_session_id();
	        $key = "basket_" . $session_id;

	        // Warenkorb-Cache leeren (optional, falls benötigt)
	        delete_transient($key);

	        $basket = '<svg class="shopping-cart" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M253.3 35.1c6.1-11.8 1.5-26.3-10.2-32.4s-26.3-1.5-32.4 10.2L117.6 192 32 192c-17.7 0-32 14.3-32 32s14.3 32 32 32L83.9 463.5C91 492 116.6 512 146 512L430 512c29.4 0 55-20 62.1-48.5L544 256c17.7 0 32-14.3 32-32s-14.3-32-32-32l-85.6 0L365.3 12.9C359.2 1.2 344.7-3.4 332.9 2.7s-16.3 20.6-10.2 32.4L404.3 192l-232.6 0L253.3 35.1zM192 304l0 96c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-96c0-8.8 7.2-16 16-16s16 7.2 16 16zm96-16c8.8 0 16 7.2 16 16l0 96c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-96c0-8.8 7.2-16 16-16zm128 16l0 96c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-96c0-8.8 7.2-16 16-16s16 7.2 16 16z"/></svg>';

	        // Darstellung des Warenkorbsymbols (unterschiedlich bei leerem und gefülltem Warenkorb)
	        $Ausgabe = '<div id="travelmanager-cart-icon" class="travelmanager-basket ';


	        if (empty($json)) {
		        // Warenkorb ist leer
		        $Ausgabe .= '" session="' . $session_id . '">';
		        $Ausgabe .= $basket;
		        $Ausgabe .= '<span id="travelmanager-cart-status"></span>';
	        } else {
		        // Warenkorb enthält Artikel
		        $Ausgabe .= 'items-present" session="' . $session_id . '" onclick="window.location.href=\'' . $json["basket_url"] . '\';">';
		        $Ausgabe .= $basket;
		        $Ausgabe .= '<span id="travelmanager-cart-status">';
		        $Ausgabe .= '<a href="' . $json["basket_url"] . '">' . $json["anzahl"] . '</a>';
		        $Ausgabe .= '</span>';

		        // Warenkorb-Daten speichern, falls Artikel vorhanden sind
		        set_transient($key, rawurlencode($json_data), 60);
	        }

	        // Abschluss der Warenkorb-Ausgabe
	        $Ausgabe .= '</div>';

        }
        elseif($a["call"]=="eventcard"){
	        include("eventcard.component.php");
        }
		elseif($a["call"]=="eventinfo"){
			include("buchung.component.php");
		}
        elseif($a["call"]=="shop"){
	        include_once("shop.component.php");
        }
        elseif($a["call"]=="sitemap"){
	        include_once("sitemap.component.php");
        }
        elseif($a["call"]=="fahrtfinder2" OR $a["call"]=="fahrtfinder2adler"){
	        include("fahrtfinder2.component.php");
        }
        elseif($a['call']=="listcategories"){

            if(empty($a["station_id"])){
                $Ausgabe="Please define parameter station_id";
            }
            else{

                $linie_typ_id = "";
                foreach($json["kategorien"] as $row){
                    if(!empty($linie_typ_id)){
                        $linie_typ_id.=",";
                    }
                    $linie_typ_id.=$row["linie_typ_id"];
                }


                $Ausgabe.="
            <div class='listcategories' hash='{$keyHash}'>
            <div class='wp-block-columns'>
            <div class='wp-block-column'>
                <div class='caption'>Auswahl</div>
                <select name='linientyp_id' class='linientyp_id' onchange='loadListCategorie(this)'>
    ";

                $Ausgabe.="<option value='$linie_typ_id' selected> - - {$dialog["alles_selected"]} - -</option>";
                foreach($json["kategorien"] as $row){
                    $Ausgabe.="<option value='{$row["linie_typ_id"]}'>".$row["bezeichnung"]."</option>";
                }

                $Ausgabe.="
                </select>
            </div>
            <div class='wp-block-column'>
                <div class='caption'>Monat</div>
                <select class='monat' onchange='loadListCategorie(this)'>";

                $ts = strtotime("now");
                for($i=$ts;$i<strtotime("+1 year",$ts);$i = strtotime("+1 month",$i)){
                    $Ausgabe.="<option value='$i'>".strftime("%m - %Y",$i)."</option>";
                }
                $Ausgabe.="
                </select>
            </div>
            </div>
            ";


                $Ausgabe.="<div class='fahrplan'></div>
            </div>";
            }
        }
        elseif($a['call']=="eventcalendar")
        {

            $Ausgabe.="<div class='eventycalendar' hash='{$keyHash}'>
            <div class='navigation wp-block-columns'>";

            if(empty($start)){
                $start = "first day of this month";
            }
            $Ausgabe.="
                <div class='wp-block-column'>
                    <div class='caption'>{$dialog["select_month"]}</div>
                    <select class='zeitraum' onchange='TMchangeMonth(this);'>";

            $c=0;
            $datum = strtotime(strftime("%Y-%m-01",strtotime($start)));
            $jahr = strftime("%Y",$datum);
            $start_monat = strftime("%m",$datum);
            for($i=0;$i<12;$i++){

                $use_monat = $start_monat+$i;
                $use_jahr = $jahr;
                if($use_monat>24){
                    $use_monat-=24;
                    $use_jahr++;
                }
                elseif($use_monat>12){
                    $use_monat-=12;
                    $use_jahr++;
                }
                $datum = strtotime("$use_jahr-$use_monat-01");

                $Ausgabe.="<option value='$c' ".($c==0?"selected":"").">".strftime("%m - %Y",$datum)."</option>";
                $c++;
            }
            $Ausgabe.="
                    </select>
                </div>";

            if($a["show_station"]=="true"){
                $Ausgabe.="
                <div class='wp-block-column'>
                    <div class='caption'>{$dialog["station"]}</div>
                    <select class='station' onchange='TMchangeMonth(this);'>";
                foreach($json["station"] as $row){
                    $Ausgabe.="<option value='{$row["ID"]}' ".($row["ID"]==$station_id?"selected":"").">".$row["bezeichnung"]."</option>";
                }

                $Ausgabe.="
                    </select>
                </div>";
            }


            if($a["show_category"]=="true"){
                $Ausgabe.="
                <div class='wp-block-column'>
                    <div class='caption'>{$dialog["category"]}</div>
                    <select class='category' onchange='TMchangeMonth(this);'>";
                foreach($json["kategorien"] as $row){
                    $Ausgabe.="<option value='{$row["ID"]}' ".($row["ID"]==$linie_typ_id?"selected":"").">".$row["bezeichnung"]."</option>";
                }

                $Ausgabe.="
                    </select>
                </div>";
            }

            $Ausgabe.=" </div>";

            $Kalender = $json["ausgabe"];

            $Ausgabe.="<div class='calendercontainer'>".$Kalender."</div>";
            $Ausgabe.="</div>";
        }
        elseif($a['call']=="list"){

            if(isset($_GET["start"])){
                $datum_von = $_GET["start"];
            }
            else{
                $datum_von = strftime("%d.%m.%Y");
            }

	        $linien_typ_id=0;
			if(isset($_GET["linientyp_id"]))
			{
				$linien_typ_id = (int)$_GET["linientyp_id"];
			}

            $Ausgabe.="
                <div class='listcontainer'>
                <form method='GET'>
                <div class='wp-block-columns'>";
            $Ausgabe.="
                <div class='wp-block-column'>
                    <input type='text' value='$datum_von' name='start' id='start' class='datepicker' autocomplete='off' readonly/>
                </div>";

            $arrayDuration = array("1 day"=>"1 {$dialog["tag"]}","3 days"=>"3 {$dialog["tage"]}","1 week"=>"1 {$dialog["week"]}","1 month"=>"1 {$dialog["month"]}");

            $Ausgabe.="
                <div class='wp-block-column' style='display:none'>
                    <select name='zeitraum'>";

            foreach($arrayDuration as $idx=>$duration){
                $Ausgabe.="<option value='$idx' ".($idx=="1 day"?"selected":"").">$duration</option>";
            }
            $Ausgabe.="
                    </select>
                </div>";
            $Ausgabe.="
                <div class='wp-block-column'>
                <select name='linientyp_id' id='linientyp_id'>
    ";
            foreach($json as $row){
                $Ausgabe.="<option value='{$row["linie_typ_id"]}' ".($linien_typ_id==$row["linie_typ_id"]?"selected":"").">".$row["bezeichnung"]."</option>";
            }

            $Ausgabe.="
                </select>
                </div>";
           $Ausgabe.="<div class='wp-block-column'><button hash='{$keyHash}' onclick=\"if(jQuery('#start').val()=='') { alert('{$dialog["please_define_zeitraum"]}'); return false; } TMFindenInteraktiv(this); return false;\">{$dialog["finden"]}</button></div>";
           $Ausgabe.="</div>";
           $Ausgabe.="</form>";
            $Ausgabe.="<div class='suchergebnis'></div></div>";

            $Ausgabe.="

                <script>
                jQuery(function() {
                    var dateToday = new Date();
                    jQuery.datepicker.setDefaults(calendarLocale);
                    jQuery('.datepicker').datepicker({
                        numberOfMonths: 1,
                        regional:'{$regional}',
                        dateFormat : 'dd.mm.yy',
                        minDate: dateToday, 
                        maxDate: 365*2
                    })
                    
                });
                </script> 
                ";
        }
        elseif($a['call']=="find"){

            if(empty($json)){
                $Ausgabe=$dialog["no_result_linie"];
            }
            else{

	            $linien_typ_id=0;
	            if(isset($_GET["linientyp_id"])){
		            $linien_typ_id = (int)$_GET["linientyp_id"];
	            }
                $Ausgabe.="
                <div class='containerfind'>
                <form method='GET'>
                <input type='hidden' name='aktion' value='finden'/>
                <div class='wp-block-columns'>";
                $Ausgabe.="
                <div class='wp-block-column'>
                <select name='linie_typ_id'>
    ";
                foreach($json as $row){
                    $Ausgabe.="<option value='{$row["linie_typ_id"]}' ".($linien_typ_id==$row["linie_typ_id"]?"selected":"").">".$row["bezeichnung"]."</option>";
                }

                $Ausgabe.="
                </select>
                </div>";
                $Ausgabe.="<div class='wp-block-column'><input type='button' class='button' value='{$dialog["finden"]}' onclick='TMFindenTabs(this)' hash='{$keyHash}'/></div>";
                $Ausgabe.="</div>
                </form>";

                $Ausgabe.="<div class='findencontainer'></div>
                </div>";
            }
        }
        elseif($a["call"]=="alert")
        {
			$classAdd = "noalert";
			if(!empty($json["alert"]) AND strlen($json["alert"])>10){
				$classAdd = "hasalert";
			}
            $Ausgabe.="<div class='notice notice-warning is-dismissible {$classAdd}'>";
			if($a["view"]!="none"){
				$Ausgabe.=$json["alert"];
			}
	        $Ausgabe.="</div>";
        }
        elseif($a['call']=="timetable" OR $a['call']=="timetablev2"){
	        include "timetable.ldjson.component.php";
			if($a["view"]!="ldjson"){
				include("timetable.component.php");
			}
        }
        elseif(!empty($json) AND is_array($json))
        {
			$Ausgabe.="Invalid call";
        }
        else
        {
	        $satz = sprintf(
		        $dialog["no_fahrt"],
		        date("d.m.Y", $start),
		        date("d.m.Y", $stop)
	        );
	        $Ausgabe.="<div class='notice notice-warning is-dismissible'><p>{$satz}.</p></div>";
        }
    }
    else
    {
        $Ausgabe.=$dialog["no_result_was_found"];
    }

	$Ausgabe.="<input type='hidden' id='tm_dialoge' value='".rawurlencode( json_encode( $dialog))."'/>";

	return "<div class='tm_plugin' lang='$locale'>".$Ausgabe."</div>";
}

add_action('wp_enqueue_scripts', 'travelmanager_enqueue_jquery',1);  // Priorität auf 1 setzen
add_action('wp_enqueue_scripts', 'travelmanager_enqueue_styles', 100);
add_action('wp_enqueue_scripts', 'travelmanager_enqueue_glightbox', 100);
add_action('wp_enqueue_scripts', 'travelmanager_enqueue_flexslider', 100);
add_action('init', 'tm_get_session_id');

/*Ajax Calls */
add_action( 'wp_ajax_travelmanager_eventcalendar', 'travelmanager_eventcalendar' );
add_action( 'wp_ajax_nopriv_travelmanager_eventcalendar', 'travelmanager_eventcalendar' );
add_action( 'wp_ajax_travelmanager_list', 'travelmanager_list' );
add_action( 'wp_ajax_nopriv_travelmanager_list', 'travelmanager_list' );
add_action( 'wp_ajax_travelmanager_eventinfo_hinfahrt', 'travelmanager_eventinfo_hinfahrt' );
add_action( 'wp_ajax_nopriv_travelmanager_eventinfo_hinfahrt', 'travelmanager_eventinfo_hinfahrt' );
add_action( 'wp_ajax_travelmanager_eventinfo_rueckfahrt', 'travelmanager_eventinfo_rueckfahrt' );
add_action( 'wp_ajax_nopriv_travelmanager_eventinfo_rueckfahrt', 'travelmanager_eventinfo_rueckfahrt' );
add_action( 'wp_ajax_travelmanager_contingent', 'travelmanager_contingent' );
add_action( 'wp_ajax_nopriv_travelmanager_contingent', 'travelmanager_contingent' );
add_action( 'wp_ajax_travelmanager_listcategories', 'travelmanager_listcategories' );
add_action( 'wp_ajax_nopriv_travelmanager_listcategories', 'travelmanager_listcategories' );
add_action( 'wp_ajax_travelmanager_tabs', 'travelmanager_tabs' );
add_action( 'wp_ajax_nopriv_travelmanager_tabs', 'travelmanager_tabs' );
add_action( 'wp_ajax_travelmanager_fahrtfinderv2_get_relations', 'travelmanager_fahrtfinderv2_get_relations' );
add_action( 'wp_ajax_nopriv_travelmanager_fahrtfinderv2_get_relations', 'travelmanager_fahrtfinderv2_get_relations' );

add_action('wp_head', 'travelmanager_plugin_calendar_locale');
add_action('wp_head', 'travelmanager_plugin_ajaxurl');

add_shortcode( 'travelmanager', 'travelmanager' );
add_shortcode( 'tickyt', 'travelmanager' );
add_shortcode( 'popup', 'travelmanager' );

/*Cache wieder aktivieren */
if (function_exists('wp_cache_enable')) {
	wp_cache_enable();
}