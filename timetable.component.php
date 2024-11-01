<?php

$Ausgabe.="<p class='filter_capacity'><input type='checkbox' onclick='if(this.checked) $(\".anzeige.ausgebucht\").addClass(\"hide\"); else  $(\".anzeige.ausgebucht\").removeClass(\"hide\");' id='cb_ausgebucht'> {$dialog["only_avail"]}</p>";

$arraySignets = array();
if(!empty($json["signets"])){
	foreach($json["signets"] as $signet){
		$arraySignets[$signet["ID"]]=$signet;
	}
}

if(!empty($arraySignets)){
	$Ausgabe.="<p class='filter_signets'>";
	$Ausgabe.="<input type='button' class='button_filter' onclick='jQuery(\"#cb_ausgebucht\").prop(\"checked\",false); jQuery(\".anzeige\").css(\"display\",\"\"); ' value='{$dialog["all_themes"]}' />";
	foreach($arraySignets as $signet){
		$Ausgabe.="<input type='button' class='button_filter' onclick='jQuery(\"#cb_ausgebucht\").prop(\"checked\",false); jQuery(\".anzeige\").css(\"display\",\"\"); jQuery(\".anzeige\").not(\".signet_{$signet["ID"]}\").css(\"display\",\"none\"); ' value='{$signet["bezeichnung"]}' />";
	}
	$Ausgabe.="</p>";
}

$monate = array();
foreach($json["verkehrstage"] as $row)
{
	if($row["datum"]>strtotime("-1 year")){
		$d = date("Y-m-01",$row["datum"]);
		if(array_key_exists($d, $monate)==false){
			$monate[$d] = 0;
		}
		$monate[$d]++;
	}
}

if(count($monate)>1){
	$Ausgabe.="<div class='wp-block-columns block-months'>";
	$Ausgabe.='<a class="wp-block-button-month" href="#" onclick="$(\'.block-timetable\').removeClass(\'hide\'); return false;">'.$dialog["alles_anzeigen"].'</a>';

	foreach($monate as $month_str => $anzahl)
	{
		$monat ="";
		$month = strtotime($month_str);
		if(is_numeric($month)==false){
			continue;
		}

		$m = date("m",$month);

		if($m=="01"){
			$monat =$dialog["jan"];
		}
		elseif($m=="02"){
			$monat =$dialog["feb"];
		}
		elseif($m=="03"){
			$monat =$dialog["mar"];
		}
		elseif($m=="04"){
			$monat =$dialog["apr"];
		}
		elseif($m=="05"){
			$monat =$dialog["may"];
		}
		elseif($m=="06"){
			$monat =$dialog["jun"];
		}
		elseif($m=="07"){
			$monat =$dialog["jul"];
		}
		elseif($m=="08"){
			$monat =$dialog["aug"];
		}
		elseif($m=="09"){
			$monat =$dialog["sep"];
		}
		elseif($m=="10"){
			$monat =$dialog["oct"];
		}
		elseif($m=="11"){
			$monat =$dialog["nov"];
		}
		elseif($m=="12"){
			$monat =$dialog["dec"];
		}

		$Ausgabe.='<a class="wp-block-button-month" href="#" onclick="$(\'.block-timetable\').addClass(\'hide\'); $(\'.month_'.$month_str.'.anzeige\').removeClass(\'hide\'); return false;">'.$monat.'</a>';
	}
	$Ausgabe.="</div>";
}

foreach($json["verkehrstage"] as $row)
{
	$month = date("Y-m-01",$row["datum"]);
	$id="";
	if(isset($monate[$month])){
		$id="month_$month";
		unset($monate[$month]);
	}

	$Ausgabe.="<div class='wp-block-columns block-timetable month_$month anzeige ".($row["ausgebucht"]?"ausgebucht":"frei");

	if(!empty($row["signets"])){
		foreach($row["signets"] as $signet_id){
			$Ausgabe.=" signet_{$signet_id}";
		}
	}

	$Ausgabe.="' id='$id'>";

	$target="";
	if($a["newwindow"]=="true" && !isset($a["target"])){
		$target.=" target=_blank ";
	}

	$buchungslink="";
	//Lokales Buchungsziel
	if(isset($a["target"]) && !$row["ausgebucht"] && !empty($a["target"])){
		$buchungslink.=$a["target"]."?relation=".$row["relation_code"]."&date=".date("Y-m-d",$row["datum_abfahrt"]);
		$use_target=true;
	}
	elseif(!$row["ausgebucht"]){
		$buchungslink.=$row["buchungslink_direkt"];
		$use_target=false;
	}

	$Ausgabe.="<div class='wp-block-column'>";
	$Ausgabe.="<div class='datum'>".$row["datum_format"];
	if($row["nur_hinfahrt"]=="1" && $row["ankunft"]>0){
		$Ausgabe.=" - ".travelmanager_zeitanzeige($row["ankunft"])." {$dialog["uhr"]}";
	}
	$Ausgabe.="</div>";

	if($row["ausgebucht"]){
		$Ausgabe.="<div class='linie_bezeichnung ausgebucht'> - {$dialog["ausgebucht"]} - {$row["linie_bezeichnung"]}</div>";
	}
	else{
		$Ausgabe.="<div class='linie_bezeichnung'><a href='".$buchungslink."' {$target}>{$row["linie_bezeichnung"]}</a></div>";
	}

	$Ausgabe.="<div class='abfahrt_station'>".$row["abfahrthafen"]."</div>";

	$infotext = trim($row["infotext"]." ".$row["infotext2"]);

	$length=300;
	$infotext_raw = strip_tags($infotext);
	if(!$use_target && strlen($infotext_raw)>$length){
		$infotext_raw = mb_substr($infotext_raw, 0,$length);

		$text_infox = $infotext;
		if($row["ausgebucht"]){
			$text_infox.='<br>'.$dialog["ausgebucht"];
			$vButton = "";
		}
		else{
			$vButton = ", this.href";
		}

		$Ausgabe.="<div class='infotext'>".$infotext_raw."...</div>";
		$Ausgabe.="<div class='weiterlesen_link'><a onclick='showDialogTimetableList(decodeURIComponent(\"".rawurlencode($row["linie_bezeichnung"])."\"), decodeURIComponent(\"".rawurlencode($text_infox)."\"){$vButton}); return false;' href='".$buchungslink."'>{$dialog["read_more"]} &gt;&gt;</a></div>";
	}
	else{
		$Ausgabe.="<div class='infotext'>".$infotext_raw."</div>";
		if(!$row["ausgebucht"]){
			$Ausgabe.='<div class="weiterlesen_link"><a href="'.$buchungslink.'" '.$target.'>'.($use_target? $dialog["jetzt_buchen_mehr_infos"] : $dialog["jetzt_buchen"]).' &gt;&gt;</a></div>';
		}
	}

	$Ausgabe.="</div>";

	//Bild der Fahrt
	$Ausgabe.="<div class='wp-block-column text-center'>";

	if(!$row["ausgebucht"])
	{
		$Ausgabe.="<a href='".$buchungslink."' ".$target.">";
	}

	if(!empty($row["files"][0]) && strpos($row["files"][0],".pdf")===false)
	{
		$url = "https://".$a["account"]."/functions/drawImage.php?action=resize&img=" . rawurlencode($row["files"][0]) . "&quality=80&w=350";
	}
	else
	{
		$url = "https://".$a['account']."/images/fontawesome/exclamation-circle.svg";
	}
	$Ausgabe.="<img src='{$url}' class='referenzlogo' alt='{$row["linie_bezeichnung"]}'/>";

	if(!$row["ausgebucht"]){
		$Ausgabe.="</a>";
	}

	$Ausgabe.="</div>";
	$Ausgabe.="<hr class='divider'>";
	$Ausgabe.="</div>";
}