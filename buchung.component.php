<?php

$ref="";
if(isset($_GET["ref"])){
	$ref=$_GET["ref"];
}

$Ausgabe = "<div class='eventinfo_container' session_id='".tm_get_session_id()."' id='booknow' hafen_start='".rawurlencode( $json["location_start"])."' hafen_stop='".rawurlencode( $json["location_stop"])."' reference='{$ref}'>";
$Ausgabe.="<h1 class='event_title'>".$json["title"]."</h1>";

//Bilder
if(count($json["files"])>0){
	$Ausgabe.="<div class='flexslider_container'>";
	$Ausgabe.= travelmanager_flexslider_galerie($json["files"]);
	$Ausgabe.="</div>";
}

$Ausgabe.="<div class='container_verbindung_info'>";
if($json["location_start"]!=$json["location_stop"]){
	$Ausgabe.="<h2 class='event_station_title'>{$dialog["relation"]}</h2>";
	$Ausgabe.="<div class='event_verbindung'>".$json["location_start"]." - ".$json["location_stop"]."</div>";
}
else{
	$Ausgabe.="<h2 class='event_station_title'>{$dialog["station"]}</h2>";
	$Ausgabe.="<div class='event_verbindung'>".$json["location_start"]."</div>";
}

if(!empty($json["fahrtdauer"])){
	$Ausgabe.="<div class='eventdatum_fahrtdauer'><span>{$dialog["fahrtdauer"]}:</span> {$json["fahrtdauer"]}</div>";
}
if(isset($json["schiff"]) && !empty($json["schiff"])){
	$Ausgabe.="<div class='schiff'>".$json["schiff"]."</div>";
}
$Ausgabe.="</div>";

if(!empty($json["highlights"])){
	$Ausgabe.="<h2 class='highlights'>{$dialog["highlights"]}</h2>".$json["highlights"];
}
if(!empty($json["description"])){
	$Ausgabe.="<h2 class='description'>{$dialog["beschreibung"]}</h2>".$json["description"];
}

if(!empty($json["included"])){
	$Ausgabe.="<h2 class='included'>{$dialog["enthalten"]}</h2>".$json["included"];
}

$days = array();
foreach ($json["days"] as $index => $item) {
	$days[] = $item;
}
$Ausgabe.="<h2 class='headline_buchung_auswahl'>".$dialog["available_headliner"]."</h2>";
$Ausgabe.=$json["api4"];

//Datum hinzufügen für Auswahl
$use_date = "";
if(isset($_GET["date"]) && !empty($_GET["date"])){
	$datum = strtotime( $_GET["date"]);
	if($datum>strtotime( "now")){
		$use_date = $_GET["date"];
	}
}

if($json["one_way"]){
	$Ausgabe.="
	<div class='eventdatum_hinweis'>{$dialog["select_date"]}</div>
	<div class='eventdatum oneway' 
		days='".rawurlencode( json_encode( $days))."' 
		one_way='1' 
		initial_date='".$use_date."'
		url_frontend='".rawurlencode($json["url_frontend"])."' 
		days_return='".rawurlencode( json_encode( []))."'
		linie_id='".rawurlencode($json["linie_id"])."' 
		location_start_id='{$json["location_start_id"]}' 
		location_stop_id='{$json["location_stop_id"]}' 
		keyhash='{$keyHash}' 
		>
	</div>
	<div class='eventdatum_abfahrt'></div>";
}
else{

	$keyHash = travelmanager_save_hash($url);

	$Ausgabe.="
	<div class='column'></div>
	<div class='wp-block-columns'>
		<div class='wp-block-column'>
			<div class='eventdatum' 
				days='".rawurlencode( json_encode( $days))."' 
				one_way='0' 
				initial_date='".$use_date."'
				url_frontend='".rawurlencode($json["url_frontend"])."' 
				only_tagesfahrt='".($json["only_tagesfahrt"]?"1":"0")."' 
				days_return='".rawurlencode( json_encode( $json["return_dates"]))."'
				linie_id='".rawurlencode($json["linie_id"])."' 
				location_start_id='{$json["location_start_id"]}' 
				location_stop_id='{$json["location_stop_id"]}' 
				keyhash='{$keyHash}' 
				>
				<div class='eventdatum_hinweis'>{$dialog["select_date"]}</div>
			</div>
			<div class='eventdatum_abfahrt'></div>
		</div>
		<div class='wp-block-column'>
			<div class='rueckfahrt hide'>
				<div class='rueckfahrt_infotext'>{$dialog["select_date_rf"]}</div>
				<div class='rueckfahrt_datepicker'></div>
			</div>
			<div class='rueckfahrt_abfahrt'></div>
		</div>
	</div>";
}

$Ausgabe.="<div class='booking_window hide'><h3>".$dialog["ticket"]."</h3><iframe src='about:blank' frameborder='0'></iframe><div class='url hide'></div></div>";


if(isset($json["location_coordinates"]) && !empty($json["location_coordinates"])){
	$link = "http://maps.google.com/maps?q=".$json["location_coordinates"];
	$Ausgabe.="<h2 class='anfahrtsbeschreibung'>{$dialog["wegbeschreibung"]}</h2><div class='location_wayfinder'><a href='$link' target='_blank'>".$dialog["wegbeschreibung_link"]."</a></div>";
}

if(!empty($json["rating_code"])){
	$Ausgabe.="<h2 class='bewertung'>{$dialog["bewertung"]}</h2><div code='{$json["rating_code"]}' class='rating_code'></div>";
}

$Ausgabe.="</div>";