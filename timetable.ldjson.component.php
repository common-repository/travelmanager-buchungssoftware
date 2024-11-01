<?php
/** @var $json array */
$structured_data = [];

foreach($json["verkehrstage"] as $row){

	if(!empty($row["files"][0])){
		$image = $row["files"][0];
	}
	else{
		// URL des Plugins
		$plugin_url = plugins_url('', __FILE__);
		$image = $plugin_url . '/css/placeholder.png';
	}



	//Organization Info
	$orga = [
		"@type"=> "Organization",
		"name"=> get_option('blogname'),
		"url"=> get_site_url()
	];

	if(isset($json["google_business_profile"]) && !empty($json["google_business_profile"])){
		$orga["sameAs"]=$json["google_business_profile"];
	}


	$event = [
		"@context" => "https://schema.org",
		"@type" => "Event",
		"name" => $row["linie_bezeichnung"],
		"startDate" => date('c', $row["datum_abfahrt"]),
		//"endDate" => date('c',$row["datum_ankunft"]),
		"eventAttendanceMode" => "https://schema.org/OfflineEventAttendanceMode",
		"eventStatus" => "https://schema.org/EventScheduled",
		"location" => [
			"@type" => "Place",
			"name" => $row["abfahrthafen"],
			/*
			"address" => [
				"@type" => "PostalAddress",
				"streetAddress" => get_post_meta($post->ID, 'event_street_address', true),
				"addressLocality" => get_post_meta($post->ID, 'event_city', true),
				"postalCode" => get_post_meta($post->ID, 'event_postal_code', true),
				"addressCountry" => get_post_meta($post->ID, 'event_country', true)
			]*/
			"geo"=> [
				"@type"=> "GeoCoordinates",
		        "latitude"=> $row["abfahrthafen_lat"],
		        "longitude"=> $row["abfahrthafen_lon"],
			]
		],
		"image" => esc_url($image),
		"description" => strip_tags( html_entity_decode( $row["infotext"])),
		"offers" => [
			"@type" => "Offer",
			"url" => $row["buchungslink_direkt"],
			"availability" => "https://schema.org/InStock"
		],
		"organizer" => $orga
	];
	$structured_data[] = $event;
}

if (!empty($structured_data)) {
	$Ausgabe= '<script type="application/ld+json">' . json_encode($structured_data) . '</script>';
}