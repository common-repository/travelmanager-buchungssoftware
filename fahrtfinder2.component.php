<?php
$Ausgabe = "";
if(!empty($json["stationen"])){

	$target="";
	if(isset($a["target"]) && !empty($a["target"])){
		$target = $a["target"];
	}

	$targetURL="";
	if(isset($a["godirect"]) && !empty($a["godirect"])){
		$targetURL = $a["godirect"];
	}

	$station_id=0;
	if(isset($_GET["station_id"]) && (int)$_GET["station_id"]>0 AND empty($a["signets"])){
		$station_id = (int)$_GET["station_id"];
	}
	elseif(isset($a["station_id"]) && (int)$a["station_id"]>0 AND empty($a["signets"])){
		$station_id = (int)$a["station_id"];
	}

	$stop_station_id=0;
	if(isset($_GET["stop_station_id"]) && (int)$_GET["stop_station_id"]>0 AND empty($a["signets"])){
		$stop_station_id = (int)$_GET["stop_station_id"];
	}

	$linien_ids="";
	if(isset($_GET["linien_ids"]) && !empty($_GET["linien_ids"]) AND empty($a["signets"])){
		$linien_ids = $_GET["linien_ids"];
	}

	$linie_typ_id = 0;
	if(isset($a["linie_typ_id"]) && (int)$a["linie_typ_id"]>0){
		$linie_typ_id = (int)$a["linie_typ_id"];
	}
	elseif(isset($_GET["category_id"]) && (int)$_GET["category_id"]>0){
		$linie_typ_id = (int)$_GET["category_id"];
	}

	$hide_station=false;
	if(isset($a["show_station"]) && ($a["show_station"]=="false" OR $a["show_station"]===false)){
		$hide_station = true;
	}

	$ref="";
	if(isset($_GET["ref"])){
		$ref=$_GET["ref"];
	}

	//Stationen
	$stationen_options=[];
	foreach($json["stationen"] as $row){
		$sel="";
		if($station_id==$row["station_id"]){
			$sel = "selected";
		}
		$stationen_options[]="<option value='".$row["station_id"]."' zielstationen='".rawurlencode( json_encode( $row["zielstationen"]))."' ".$sel.">".$row["bezeichnung"]."</option>";
	}
	//Signets
	$signets="";
	if(!empty($a["signets"])){
		$signets = $a["signets"];
	}

	$keyHash2 = uniqid("tm");

	//Suchfunktion ausblenden wenn Signets angegeben wurrden
	if(!empty($signets)){
		$Ausgabe.='<div class="fahrtfinder2_container hide signet" hash="'.$keyHash.'" hash2="'.$keyHash2.'" targetobj="'.$target.'" targeturl="'.$targetURL.'" linie_typ_id="'.$linie_typ_id.'" linien_ids="'.$linien_ids.'" signets="'.$signets.'" max="'.$a["max"].'" ref="'.$ref.'"></div>';
	}
	elseif($a["call"]=="fahrtfinder2adler"){

		$Ausgabe.='<div class="fahrtfinder2_container" hash="'.$keyHash.'" hash2="'.$keyHash2.'" targetobj="'.$target.'" targeturl="'.$targetURL.'" linie_typ_id="'.$linie_typ_id.'" linien_ids="'.$linien_ids.'" ref="'.$ref.'">
		<div class="qfinder">
			<div class="startstation">
			<div class="input-group">
	
			  <label class="input-group-text">
				  <img src="/wp-content/uploads/symbole/starthafen.svg" width="30px"/>
			  </label>
	
			  <select class="form-select" name="station_id" id="station_id" onchange="$(this).closest(\'.fahrtfinder2_container\').attr(\'linien_ids\',\'\'); TMfahrtfinder2_set_zielstation(this,'.$stop_station_id.')" zielstation_id="'.$stop_station_id.'"><option value="0">'.$dialog["alle_selected"].'</option>'.implode( " ", $stationen_options).'</select>
	
			</div>
		  </div>
	
			<div class="stopstation">
			<div class="input-group">
	
			  <label class="input-group-text">
				  <img src="/wp-content/uploads/symbole/zielhafen.svg" width="30px"/>
			  </label>
	
			  <select class="form-select" name="stop_station_id" id="stop_station_id" class="hide"></select>
	
			</div>
		  </div>
	
			<div class="datum">
	
			<div class="input-group">
	
				<label class="input-group-text">
					<img src="/wp-content/uploads/symbole/datum.svg" width="30px"/>
				</label>
	
				<input type="date" class="form-control" id="datum_abfahrt" name="datum_abfahrt" min="2024-03-12" value="">
	
			</div>
	
			<div class="form-check">
			  <input class="form-check-input" type="checkbox" value="1" name="3days">
			  <label class="form-check-label">
				+- 3 Tage
			  </label>
			</div>
		</div>
	
			<div class="finden"><input type="button" value="Finden" id="button_finden" onclick="TMfahrtfinder2_finden(this);" class="button"></div>
		</div>
		</div>';
	}
	else{
		$Ausgabe.="<div class='fahrtfinder2_container' hash='{$keyHash}' hash2='{$keyHash2}' targetobj='{$target}' targeturl='{$targetURL}' linie_typ_id='$linie_typ_id' linien_ids='$linien_ids' ref='".$ref."'>";
		$Ausgabe.="<div class='column startstation'>";
		$Ausgabe.="<select name='station_id' id='station_id' onchange='TMfahrtfinder2_set_zielstation(this,$stop_station_id)' zielstation_id='$stop_station_id'>";
		$Ausgabe.="<option value='0' zielstationen='".rawurlencode( json_encode([]))."'> - - ".$dialog["bitte_auswaehlen"]." - -</option>".implode( " ", $stationen_options)."</select>";
		$Ausgabe.="</div>";
		if(!$hide_station) {
			$Ausgabe.="<div class='column stopstation'>";
			$Ausgabe.="<select name='stop_station_id' id='stop_station_id' class='hide'></select>";
			$Ausgabe.="</div>";
		}

		$date="";
		if($a["current_date"]==="true" && date("H")>12){
			$date = date("Y-m-d",strtotime( "+1 day"));
		}
		elseif($a["current_date"]==="true"){
			$date = date("Y-m-d");
		}

		$Ausgabe.="<div class='column datumauswahl'>";
		$Ausgabe.="<input type='date' id='datum_abfahrt' name='datum_abfahrt'  min='".date("Y-m-d")."' value='".$date."'/>";
		$Ausgabe.="<label><input type='checkbox' value='1' name='3days'>+- ".$dialog["3_tage"]."</label>";
		$Ausgabe.="</div>";
		$Ausgabe.="<div class='column finden'>";
		$Ausgabe.="<input type='button' value='".$dialog["finden"]."' id='button_finden' onclick='TMfahrtfinder2_finden(this);' class='button'>";
		$Ausgabe.="</div>";
		$Ausgabe.="</div>";
	}

	if(empty($target)){
		$Ausgabe.="<div class='fahrtfinder2_result_container' hash='{$keyHash}' hash2='$keyHash2'></div>";
	}
}
else{
	$Ausgabe.= "Invalid shortcode configuration";
}