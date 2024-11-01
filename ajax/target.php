<?php

$locale = get_locale();
if ( $locale == 'de_DE') {
	require_once plugin_dir_path( __FILE__ ) ."../libs/dialoge_de.php";
} else {
	require_once plugin_dir_path( __FILE__ ) ."../libs/dialoge_en.php";
}


function travelmanager_eventcalendar() {
    $url = travelmanager_get_url($_POST["hash"]);
    $url.="&extra_months=".$_POST["extra_months"];
    $url.="&use_station_id=".$_POST["station_id"];
    $url.="&use_category_id=".$_POST["category_id"];

    $result = travelmanager_request_content($url, 15);
    $json = json_decode($result["msg"],true);

    $array = array();
    $array["html"]=$json["ausgabe"];

    header("Content-type:application/json; charset=utf-8");
    echo json_encode($array);
    wp_die(); // this is required to terminate immediately and return a proper response
}

function travelmanager_contingent(){

    $url = travelmanager_get_url($_POST["hash"]);
    $url.="&newstart=".$_POST["datum"];
    $result = travelmanager_request_content($url, 15);
    $data = json_decode($result["msg"],true);
    $dataCal = array();
    if(!empty($data)){
        foreach($data["data"] as $row){
            $key = strftime("%Y-%m-%d",strtotime($row["datum"]));
            $bez = explode(' ', $row["kontingent"][0]["preistyp"], 3);
            $bezeichnung = str_replace("Euro", "€", $bez[0].$bez[1]);
            $dataCal[$key]=array("free"=>$row["kontingent"][0]["free"],"url"=>$row["url"],"bezeichnung"=>$bezeichnung);
        }
    }

    header("Content-type:application/json; charset=utf-8");
    echo json_encode($dataCal);
    wp_die(); // this is required to terminate immediately and return a proper response
}

function travelmanager_eventinfo_hinfahrt(){
	$url = travelmanager_get_url($_POST["hash"]);
	$url.="&datum=".$_POST["datum"];
	$url.="&rand=".rand(0,1000);
	$url.="&linie_id=".(int)$_POST["linie_id"];
	$url.="&station_start_id=".(int)$_POST["station_start_id"];
	$url.="&station_stop_id=".(int)$_POST["station_stop_id"];
	$url.="&aktion2=get_hinfahrt_zeiten";
	$result = travelmanager_request_content($url, 1);
	header("Content-type:application/json; charset=utf-8");
	echo $result["msg"];
	wp_die();
}
function travelmanager_eventinfo_rueckfahrt(){
	$url = travelmanager_get_url($_POST["hash"]);
	$url.="&newstart=".$_POST["datum"];
	$url.="&datum_hinfahrt=".$_POST["datum_hinfahrt"];
	$url.="&rand=".rand(0,1000);
	$url.="&hinfahrt_linie_id=".(int)$_POST["hinfahrt_linie_id"];
	$url.="&station_start_id=".$_POST["station_start_id"];
	$url.="&station_stop_id=".$_POST["station_stop_id"];
	$url.="&ankunft=".$_POST["ankunft"];
	$result = travelmanager_request_content($url, 1);
	header("Content-type:application/json; charset=utf-8");
	echo $result["msg"];
	wp_die(); // this is required to terminate immediately and return a proper response
}

function travelmanager_listcategories(){
	global $dialog, $locale;
    $url = travelmanager_get_url($_POST["hash"]);
    $url.="&only_linie_typ_id=".$_POST["linie_typ_id"]."&fahrplan=true"."&start=".(int)$_POST["ts"];
    $result = travelmanager_request_content($url, 15);

    $json = json_decode($result["msg"],true);
    //Liste darstellen
    $Ausgabe="";
    if(empty($json["fahrplan"])){
        $Ausgabe.="<div class='fehler'>".$json["meldung"]."</div>";
    }
    else{
        $Ausgabe.="<div class='alternierend'>";
        foreach($json["fahrplan"] as $row){
            $Ausgabe.="<div class='wp-block-columns'>";

            $Ausgabe.="<div class='wp-block-column'>".strftime("%d.%m.%Y",$row["abfahrt_ts"])." ".$row["abfahrt_uhrzeit"]."</div>";
            $Ausgabe.="<div class='wp-block-column'>".$row["linie_bezeichnung_internet"]."</div>";
            $Ausgabe.="<div class='wp-block-column'>".$row["infotext_api2"]."</div>";

            //Kapazität
            $Ausgabe.="<div class='wp-block-column'>";
            if($row["nur_hinfahrt"]=="1" AND $row["keine_kapazitaet"]=="0"){

                if($row["frei"]>10){
                    $Ausgabe.=$dialog["verfuegbar"];
                }
                elseif($row["frei"]>0){
                    $Ausgabe.=$dialog["wenige_plaetze"];
                }
                else{
                    $Ausgabe.=$dialog["ausgebucht"];
                }
            }
            $Ausgabe.="</div>";

            if(!empty($row["infotext_api"]) && strlen($row["infotext_api"])>10){
                $infotext = $row["infotext_api"];
                $Ausgabe.="<div class='wp-block-column'><a href='{$row["url"]}' target='_blank' linie='".rawurlencode($row["linie_bezeichnung_internet"])."' infotext='".rawurlencode($infotext)."' onclick='showDialogListCategorie(this); return false;'>{$dialog["more_info"]}</a></div>";
            }
            else{
                $Ausgabe.="<div class='wp-block-column'><a href='{$row["url"]}' target='_blank'>{$dialog["jetzt_buchen"]}</a></div>";
            }

            $Ausgabe.="</div>";
        }
        $Ausgabe.="</div>";
    }
    $array = array();
    $array["html"]=$Ausgabe;
    header("Content-type:application/json; charset=utf-8");
    echo json_encode($array);
    wp_die(); // this is required to terminate immediately and return a proper response

}

function travelmanager_fahrtfinderv2_get_relations(){

	$url = travelmanager_get_url($_POST["hash"]);
	$url.="&station_id=".$_POST["station_id"];
	$url.="&zielstation_id=".$_POST["station_stop_id"];
	if(isset($_POST["signets"])){
		$url.="&signets=".$_POST["signets"];
	}
	$url.="&datum=".$_POST["datum"];
	if(isset($_POST["max"])){
		$url.="&max=".(int)$_POST["max"];
	}
	$url.="&linie_typ_id=".(int)$_POST["linie_typ_id"];
	$url.="&linien_ids=".$_POST["linien_ids"];
	$url.="&3days=".$_POST["3days"];
	$url.="&do=getRelations";
	$result = travelmanager_request_content($url, $_POST["signets"]==""?1:15);

	$json = json_decode($result["msg"],true);
	header("Content-type:application/json; charset=utf-8");
	if(!empty($json["relations"])){
		echo json_encode(array("result"=>$json["relations"],"categories"=>$json["categories"]));
	}
	else{
		echo json_encode(array("result"=>array(),"categories"=>[]));
	}
	wp_die(); // this is required to terminate immediately and return a proper response
}

function travelmanager_tabs() {
	global $dialog;
    $url = travelmanager_get_url($_POST["hash"]);
    $url.="&linie_typ_id=".$_POST["linie_typ_id"];
    $result = travelmanager_request_content($url, 15);
    $json = json_decode($result["msg"],true);

    $Ausgabe = "";
    foreach($json as $row){

        //Darstellung einzelne Linien
        if($row["linie_typ_id"]==$_POST["linie_typ_id"]){
            $Ausgabe.="
                            <div class='jqueryuitabs'>
                            <ul>";
            foreach($row["linien"] as $linie){
                $Ausgabe.="<li><a href='#tabs-linie-".$linie["ID"]."'>".$linie["bezeichnung"]."</a></li>";
            }
            $Ausgabe.="
                            </ul>";

            foreach($row["linien"] as $linie){
                $Ausgabe.="<div id='tabs-linie-".$linie["ID"]."'>";
                $Ausgabe.="<div class='wp-block-columns'>";

                $Ausgabe.="<div class='wp-block-column columnimage'>";
                if(!empty($linie["files"])){
                    $Ausgabe.="<img src='{$linie["files"][0]}' alt='{$linie["bezeichnung"]}' class='linievorschaubild'/>";
                }
                else
                {
                    $Ausgabe.="<img src='https://via.placeholder.com/640x480.png?text=Kein Bild vorhanden' alt='{$linie["bezeichnung"]}' class='linievorschaubild'/>";
                }
                $Ausgabe.="</div>";

                if(empty($linie["infotext"])){
                    $infotext = "<p>".$dialog["no_infotext"]."</p>";
                }
                else{
                    $infotext = $linie["infotext"];
                }


                $Ausgabe.="
                                <div class='wp-block-column'>
                                    ".$infotext."
                                    <p><a class='button' href='{$linie["url"]}'>{$dialog["open_calendar"]}</a></p>
                                </div>";

                $Ausgabe.="</div>";
                $Ausgabe.="</div>";
            }

            $Ausgabe.="</div>";
        }
    }

    $array = array();
    $array["html"]=$Ausgabe;
    header("Content-type:application/json; charset=utf-8");
    echo json_encode($array);
    wp_die();
}

function travelmanager_list() {
	global $dialog, $locale;
    $url = travelmanager_get_url($_POST["hash"]);
    $url.="&".$_POST["data"];
    $result = travelmanager_request_content($url, 15);

    $variables= array();
    parse_str($_POST["data"], $variables);

    if($result["error"]){
        $Ausgabe = $result["msg"];
    }
    else{

        $Ausgabe="";
        $json = json_decode($result["msg"],true);

        foreach($json as $row){
            if($variables["linientyp_id"] == $row["linie_typ_id"]){

                $Ausgabe.="<hr class='trennung'>";

                //Fahrtenergebnisse
                if(empty($row["fahrplan"])){
                    $Ausgabe.="<div class='callout alert'><p>{$dialog["no_result_was_found"]}</p></div>";
                }
                else{

                    $c=1;
                    foreach($row["fahrplan"] as $fahrplan){
                        $Ausgabe.="<div class='wp-block-columns ".($c%2==0?"even":"odd")."'>";
                        $Ausgabe.="
                                <div class='wp-block-column'>
                                    ".strftime("%d.%m.%Y",$fahrplan["abfahrt_ts"])." ".$fahrplan["abfahrt_uhrzeit"]."
                                </div>
                                <div class='wp-block-column'>
                                    {$fahrplan["linie_bezeichnung_internet"]}
                                </div>
                                <div class='wp-block-column'>
                                    <a class='button' href='{$fahrplan["url"]}'>{$dialog["jetzt_buchen"]}</a>
                                </div>";
                        $Ausgabe.="</div>";

                        $c++;
                    }
                }
            }
        }
    }



    $array = array();
    $array["html"]=$Ausgabe;
    //$array["url"]=$url;
    header("Content-type:application/json; charset=utf-8");
    echo json_encode($array);
    wp_die(); // this is required to terminate immediately and return a proper response
}


