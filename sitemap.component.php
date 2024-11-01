<?php
$Ausgabe = "";

//Sitemap XML
if(!empty($json["index"]) AND isset($_GET["view"]) AND $_GET["view"]=="sitemap"){
	// Header setzen, um den Content-Type auf XML zu setzen
	header('Content-Type: application/xml; charset=utf-8');

	// Einfaches Array von URLs
	$urls = [];
	foreach($json["index"] as $linie){
		foreach($linie["relation"] as $relation){
			$current_page_url = home_url( $_SERVER['REQUEST_URI'] );
			$parsed_url = parse_url($current_page_url);
			$base_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $parsed_url['path'];
			$url = $base_url."?relation=".$relation["external_product_key"]."#booknow";
			$urls[]= $url;
		}
	}

	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

	foreach ($urls as $url) {
		echo '<url>';
		echo '<loc>' . esc_url($url) . '</loc>';
		echo '<changefreq>daily</changefreq>';
		echo '<priority>1.0</priority>';
		echo '</url>';
	}

	echo '</urlset>';

	exit;
}
//Ausgabe im normalen Content
elseif(!empty($json["index"])){

	$Ausgabe.="<div class='tm_index_wrapper'>";
	foreach($json["index"] as $linie){
		$Ausgabe.="<div class='tm_index_container'>";
		$Ausgabe.="<h2 class='tm_index_headline'>".$linie["bezeichnung"]."</h2>";
		$Ausgabe.="<div class='tm_index_infotext'>".$linie["infotext"]."</div>";

		if(!empty($linie["files"])){
			$Ausgabe.="<div class='tm_index_images'>".travelmanager_glightbox_galerie($linie["files"])."</div>";
		}


		//Infotexte
		/*
		for($i=1;$i<=3;$i++){
			if(!empty($linie["infotext".$i])){
				if(!empty($linie["infotext{$i}_caption"])){
					$Ausgabe.="<h3 class='tm_index_caption{$i}'>".$linie["infotext{$i}_caption"]."</h3>";
				}
				$Ausgabe.="<div class='tm_index_content{$i}'>".$linie["infotext{$i}"]."</div>";
			}
		}*/
		//Relationen
		$Ausgabe.="<div class='tm_relationen'>";
		$Ausgabe.="<b class='tm_call_to_action'>".$dialog["more_info"]."</b>";
		$Ausgabe.="<ul>";
		foreach($linie["relation"] as $relation){
			if($relation["station_start"] == $relation["station_stop"] OR $linie["nur_hinfahrt"]=="1"){
				$linkcontent = $relation["station_start"];
			}
			else{
				$linkcontent = $relation["station_start"]." - ".$relation["station_stop"];
			}
			$Ausgabe.="<li><a href='?relation=".$relation["external_product_key"]."#booknow'>".$linkcontent."</a></li>";
		}
		$Ausgabe.="</ul>";
		$Ausgabe.="</div>";
		$Ausgabe.="</div>";
	}
	$Ausgabe.="</div>";
}
else{
	$Ausgabe.= "Invalid shortcode configuration";
}
