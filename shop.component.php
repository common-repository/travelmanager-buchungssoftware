<?php

$Ausgabe = "";
if(empty($json)){
	$Ausgabe.="Es wurden keine Produkte gefunden";
}
//CSV-Ansicht (Für Google Shopping)
elseif(isset($_GET["view"]) && $_GET["view"]=="csv"){
	//cho "<pre>"; print_r($_SERVER); echo "</pre>"; exit;
	$products=[];
	foreach($json["items"] as $kategorie_id => $artikels){
		foreach($artikels["items"] as $artikel){
			if(isset($a["target"]) && !empty($a["target"])){
				$url = "https://".$a["target"]."?item={$artikel["artikel_id"]}";
			}
			else{
				$url = "https://".$_SERVER["SERVER_NAME"].$_SERVER["REDIRECT_URL"]."?item={$artikel["artikel_id"]}";
			}
			$formatted_price = number_format($artikel["price"], 2) . " " . $json["waehrung"];

			$products[]=[
				'id' => $artikel["artikel_id"],
				'name' => strip_tags($artikel["title"]),
				'link' => $url,
				'mobile_link' => $url,
				'image_link' => tm_cache_image_locally($artikel["files"][0]["full"]),
				'description' => strip_tags($artikel["description"]),
				'brand' => $json["mandant"],
				'currency' => $json["waehrung"],
				'identifier_exists' => 'no',
				'price' => $formatted_price,
				'availability' => 'in_stock'
			];
		}
	}
	displayCSVInBrowser( $products);
	exit;
}
//Detailansicht
elseif(isset($_GET["item"]) AND !empty($_GET["item"])){
	$artikel_id = (int)$_GET["item"];
	foreach($json["items"] as $kategorie_id => $artikels){
		foreach($artikels["items"] as $artikel){
			if($artikel_id == $artikel["artikel_id"]){
				$Ausgabe.=renderArtikelItem($artikel,$json["frontend"],$json["waehrung"]);
			}
		}
	}
}
//Übersicht
else{
	$Ausgabe.="<div class='article-container'>";
	foreach($json["items"] as $kategorie_id => $artikels){
		foreach($artikels["items"] as $artikel){
			$Ausgabe.=renderArtikelItem($artikel);
		}
	}
	$Ausgabe.="</div>";
}

function renderArtikelItem($artikel,$shop_url="",$waehrung=""){
	global $dialog;

	$inDetailSeite=isset($_GET["item"]) && !empty($_GET["item"]);

	$Ausgabe="<div class='tm_index_item'>";

	if(!$inDetailSeite){
		$Ausgabe.="<a href='?item={$artikel["artikel_id"]}'>";
		$class = "tm_index_headline";
	}
	else{
		$class = "tm_item_headline";
		$Ausgabe.="<a href='{$_SERVER["REDIRECT_URL"]}'>&lt; zurück</a>";
	}
	$Ausgabe.="<h2 class='{$class}'>".$artikel["title"]."</h2>";

	//Beschreibungstext
	$description=$artikel["description"];
	$class="";
	$classImg="";
	if(!$inDetailSeite){
		$description = strip_tags($description);
		$class="tm_index_infotext";
		$classImg="tm_index_images";
	}
	$Ausgabe.="<div class='{$class}'>".$description."</div>";

	if(!$inDetailSeite){
		$Ausgabe.="</a>";
	}

	if(!empty($artikel["files"])){
		$Ausgabe.="<div class='{$classImg}'>".travelmanager_glightbox_galerie($artikel["files"])."</div>";
	}

	if ($inDetailSeite) {
		$Ausgabe .= "<div class='tm_index_cart'>";
		$Ausgabe .= "<form method='post' action='{$shop_url}basket'>";
		$Ausgabe .= "<input type='hidden' name='aktion' value='insertIntoWarenkorb'>";
		$Ausgabe .= "<input type='hidden' name='artikel_id' value='{$artikel["artikel_id"]}'>";
		$Ausgabe .= "<input type='hidden' name='warenkorb_foreign_id' value='".tm_get_session_id()."'>";
		$Ausgabe .= "<input type='hidden' name='goto' value='https://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]."'>";

		// Wenn der Artikel ein Gutschein oder Guthaben ist
		if ($artikel["ist_gutschein"] || $artikel["ist_guthaben"] ) {
			//Betragseingabe
			$defaultValue = 25;
			$optionValues = [5, 10, 15, 20, 25, 50,100];  // Definiere die Werte für die Optionen
			$Ausgabe .= "<label for='value_{$artikel["artikel_id"]}'>{$dialog["gsbetrag"]} {$waehrung}: </label>";
			$Ausgabe .= '<select name="guthaben" class="quantity_select" id="value_{$artikel["artikel_id"]}">';
			foreach ($optionValues as $value) {
				$Ausgabe .= "<option value=\"$value\" ".($defaultValue==$value?"selected":"").">$value</option>";
			}
			$Ausgabe .= '</select>';
		}
		else{
			// Display the price
			$price = number_format($artikel["price"], 2, ',', '.'); // Assuming the price is stored in $artikel['price']
			$Ausgabe .= "<div class='price_display'>{$dialog["price"]}: {$price} {$waehrung}</div>";
		}

		// Dropdown zur Auswahl der Artikelanzahl (min bis max)
		if($artikel["maximalauswahl"]>1){
			$Ausgabe .= "<label for='quantity_{$artikel["artikel_id"]}'>{$dialog["anzahl"]}: </label>";
			$Ausgabe .= "<select id='quantity_{$artikel["artikel_id"]}' name='anzahl' class='quantity_select'>";
			for ($i = 1; $i <= $artikel["maximalauswahl"]; $i++) {
				$Ausgabe .= "<option value='{$i}'>{$i}</option>";
			}
			$Ausgabe .= "</select>";
		}
		//Fixe Anzahl
		else{
			$Ausgabe .= "<input type='hidden' name='anzahl' value='1'>";
		}

		// Add the "In den Warenkorb" button
		$Ausgabe .= "<button type='submit' class='buttonShop'>{$dialog["in_warenkorb"] }</button>";
		$Ausgabe .= "</form>";
		$Ausgabe .= "</div>";
	}
	else{
		$Ausgabe.="<div class='tm_index_link text-right'><a href='?item={$artikel["artikel_id"]}'>{$dialog["read_more"]}...</a></div>";
	}

	$Ausgabe.="</div>";
	return $Ausgabe;
}

function displayCSVInBrowser($data) {
	// Setze den Content-Type auf text/plain, um die CSV im Browser anzuzeigen
	header('Content-Type: text/plain');

	// Öffne den Output-Stream
	$output = fopen('php://output', 'w');

	// Schreibe die CSV-Header (erste Zeile)
	if (!empty($data)) {
		fputcsv($output, array_keys($data[0]));
	}

	// Schreibe die CSV-Daten
	foreach ($data as $row) {
		fputcsv($output, $row);
	}

	// Schließe den Output-Stream
	fclose($output);
}

