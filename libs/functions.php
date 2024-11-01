<?php

function travelmanager_ConvertDeutschesDatumIntoTS($vDatum, $vUhrzeit="00:00:00")
{
	//Uhrzeit anh&auml;ngen, z.B. $datum="20.12.2002 13:43:12";
	$datum1=$vDatum." ".$vUhrzeit;
	// macht aus deutschem englisches datum
	$datumE=preg_replace("#([0-9]{2})\.([0-9]{2})\.([0-9]{4}) (.*)#","\\3-\\2-\\1 \\4",$datum1);
	// umwandeln in timestamp
	$ts = strtotime("$datumE");
	return $ts;
}

function travelmanager_isJson($string) {
	json_decode($string);
	return (json_last_error() == JSON_ERROR_NONE);
}


function travelmanager_save_hash($url){

	$key =  "tm_url_".md5($url);
	update_option($key, $url,false);
	return $key;
}

function travelmanager_get_url($hash){

	return get_option($hash);
}

function travelmanager_glightbox_galerie($bilder) {
	if (empty($bilder)) return;
	$Ausgabe="";
	$Ausgabe.= '<div class="glightbox-galerie">';
	$unique_id = uniqid("meinegalerie");
	foreach ($bilder as $idx=>$bild) {
		$src_full = tm_cache_image_locally( $bild['full']);
		$src_thumb = tm_cache_image_locally( $bild['thumbnail']);
		$Ausgabe.= '<a href="' . esc_url($src_full) . '" class="glightbox" data-gallery="'.$unique_id.'" '.($idx>0?"style='display:none;'":"").'>';
		$Ausgabe.= '<img src="' . esc_url($src_thumb) . '" alt="' . @esc_attr($bild['alt']) . '">';
		$Ausgabe.= '</a>';
	}
	$Ausgabe.= '</div>';
	return $Ausgabe;
}
function travelmanager_flexslider_galerie($bilder) {
	if (empty($bilder)) return "";

	$Ausgabe ="";
	$Ausgabe.= '
	<div class="flexslider">
  	<ul class="slides">';

	foreach ($bilder as $bild) {
		$Ausgabe.= '<li><img src="' . esc_url($bild['full']) . '" /></li>';
	}

	$Ausgabe.= '
	</ul>
  	</div>';
	return $Ausgabe;
}

function travelmanager_request_content($url,$cacheMinutes){

	$return = array();
	$key = md5($url);
	$cache_duration=$cacheMinutes*60;

	//5 Sekunden Mindescachedauer
	if(empty($cache_duration) OR $cache_duration<1){
		$cache_duration = 5;
	}

	//Pruefen ob Daten im Cache sind
	$json_data = rawurldecode(get_transient( $key ));

	//Prüfung ob Daten neu geholt werden
	if ($json_data===false OR empty($json_data) OR $cacheMinutes < 0) {
		$args = array(
			'timeout'     => 15,
			'sslverify' => false
		);
		//Daten neu holen
		$response = wp_remote_get( $url,$args);
		$json_data     = wp_remote_retrieve_body( $response );

		//Es kam kein Fehler zurück - Antwort im Cache speichern
		if(strpos($json_data,'"error"')===false){
			delete_transient($key);
			$v1=set_transient( $key, rawurlencode($json_data), $cache_duration);
			if($v1===false){
				$return["fehler"]=true;
				$return["msg"]="Invalid Wordpress Cache configuration";
				return $return;
			}
		}
	}

	//Es kam ein Fehler zur�ck
	if(strpos($json_data,'"error"')!==false){
		$json = json_decode($json_data,true);

		//Zu hohe Auslastung - Button einstellen
		if(isset($json["auslastungfehler"]) && $json["auslastungfehler"]===true){
			$Ausgabe = "<button onclick=\"window.location.reload()\" meldung='{$json["error"]}'>Fahrplan laden</button>";
		}
		else{
			$Ausgabe = $json["error"];
		}
		delete_transient($key);

		$return["fehler"]=true;
		$return["msg"]=$Ausgabe;
		return $return;
	}
	$return["msg"]=$json_data;
	return $return;
}

function travelmanager_zeitanzeige($ankunft,$suffix="")
{
	if(strpos($ankunft,":")!==false)
	{
		return $ankunft;
	}
	if ($ankunft>0)
	{
		if(strlen($ankunft)==1)
		{
			$ankunft="000".$ankunft;
		}
		elseif(strlen($ankunft)==2)
		{
			$ankunft="00".$ankunft;
		}
		elseif(strlen($ankunft)==3)
		{
			$ankunft="0".$ankunft;
		}

		if ($ankunft==0)
		{
			$ankunft = "00:00";
		}
		$ankunft = substr($ankunft, 0,2).":".substr($ankunft, 2,2).$suffix;
	}
	else
	{
		$ankunft="";
	}
	return $ankunft;
}

function travelmanager_enqueue_styles() {
	// Vollst�ndiger Pfad zum Hauptverzeichnis des Plugins
	$plugin_directory = plugin_dir_url( dirname( __FILE__ ) );
	wp_register_style( 'travelmanagerstyle',$plugin_directory. 'css/travelmanagerstyle.css');
	wp_enqueue_style( 'travelmanagerstyle');
}

function travelmanager_enqueue_glightbox() {

	$plugin_directory = plugin_dir_url( dirname( __FILE__ ) );

	wp_enqueue_style('glightbox-css', $plugin_directory . 'libs/glightbox/glightbox.min.css');
	wp_enqueue_script('glightbox-js', $plugin_directory . 'libs/glightbox/glightbox.min.js', array('jquery'), '1.0.0', true);
	wp_enqueue_script('bildergallery-js', $plugin_directory . 'libs/glightbox/shared.js', array('glightbox-js','jquery'), false, true);
}
function travelmanager_enqueue_flexslider() {

	$plugin_directory = plugin_dir_url( dirname( __FILE__ ) );

	wp_enqueue_style('flexslider-css', $plugin_directory . 'libs/flexslider/flexslider.css');
	wp_enqueue_script('flexslider-js', $plugin_directory . 'libs/flexslider/jquery.flexslider-min.js', array('jquery'), '1.0.0', true);
	wp_enqueue_script('flexslider-shared-js', $plugin_directory . 'libs/flexslider/shared.js', array('flexslider-js','jquery'), false, true);
}
function travelmanager_enqueue_jquery(): void {

	$plugin_directory = plugin_dir_url( dirname( __FILE__ ) );

	if (!wp_script_is('jquery', 'enqueued')) {
		wp_enqueue_script('jquery');
	}

	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'jquery-ui-tabs' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_register_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.0/themes/smoothness/jquery-ui.min.css' );

	wp_enqueue_style( 'jquery-ui' );
	wp_enqueue_script( 'ajax-script', $plugin_directory. 'js/functions.js');
	wp_enqueue_script( 'jquery-block-ui',  $plugin_directory.'js/jquery.blockUI.js');
	wp_enqueue_script( 'buchung_functions', $plugin_directory. 'js/buchung.js');
	wp_enqueue_script( 'fahrtfinderv2_functions', $plugin_directory. 'js/fahrtfinderv2.js');
}


function travelmanager_plugin_ajaxurl() {

	echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
}

function travelmanager_plugin_calendar_locale(){
	echo '<script type="text/javascript">';

	if(get_locale()=="de_DE"){
		echo "          
	        var calendarLocale = {
	            closeText: 'schließen',
	            prevText: '&#x3c;zurück',
	            nextText: 'Vor&#x3e;',
	            currentText: 'heute',
	            monthNames: ['Januar','Februar','März','April','Mai','Juni', 'Juli','August','September','Oktober','November','Dezember'],
	            monthNamesShort: ['Jan','Feb','Mär','Apr','Mai','Jun', 'Jul','Aug','Sep','Okt','Nov','Dez'],
	            dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
	            dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],
	            dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'],
	            weekHeader: 'Wo',
	            dateFormat: 'yy-mm-dd',
	            firstDay: 1,
	            isRTL: false,
	            showMonthAfterYear: false,
	            yearSuffix: ''
	        };
	    ";
	}
	else{
		echo '
			var calendarLocale = {
				closeText: "Done",
				prevText: "Prev",
				nextText: "Next",
				currentText: "Today",
				monthNames: [ "January", "February", "March", "April", "May", "June",
				"July", "August", "September", "October", "November", "December" ],
				monthNamesShort: [ "Jan", "Feb", "Mar", "Apr", "May", "Jun",
				"Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ],
				dayNames: [ "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday" ],
				dayNamesShort: [ "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat" ],
				dayNamesMin: [ "Su", "Mo", "Tu", "We", "Th", "Fr", "Sa" ],
				weekHeader: "Wk",
				dateFormat: "yy-mm-dd",
				firstDay: 1,
				isRTL: false,
				showMonthAfterYear: false,
				yearSuffix: "" 
            };';
	}
	echo '

	    </script>';

}

function tm_get_session_id() {
	if (!session_id()) {
		session_start();
	}
	return session_id();
}

function tm_cache_image_locally($image_url) {
	// Verzeichnis für den Bildcache festlegen
	$upload_dir = wp_upload_dir(); // Standard-Upload-Verzeichnis von WordPress
	$cache_dir = $upload_dir['basedir'] . '/image_cache/'; // Ordner für den Cache

	// Erstellen des Cache-Verzeichnisses, falls es noch nicht existiert
	if (!file_exists($cache_dir)) {
		mkdir($cache_dir, 0755, true);
	}

	if(is_writable( $cache_dir)==false){
		return $image_url;
	}

	// Hash der URL erzeugen, um einen eindeutigen Dateinamen zu erstellen
	$image_hash = md5($image_url);

	// Bildtyp (Dateiendung) aus der URL extrahieren
	$image_info = pathinfo($image_url);
	$image_extension = isset($image_info['extension']) ? $image_info['extension'] : 'jpg'; // Standard 'jpg' falls nicht vorhanden
	$image_extension = preg_replace('/[?&].*/', '', $image_extension);

	// Lokaler Dateiname (hash + korrekte Dateiendung)
	$image_filename = $image_hash . '.' . $image_extension;

	// Lokaler Pfad des gecachten Bildes
	$cached_image_path = $cache_dir . $image_filename;

	// Cache-Zeit: 1 Tag (24 Stunden)
	$cache_lifetime = 86400; // 24 Stunden in Sekunden

	// Überprüfen, ob das Bild bereits lokal gecacht ist und ob es älter als 1 Tag ist
	if (!file_exists($cached_image_path) || (time() - filemtime($cached_image_path)) > $cache_lifetime) {
		// Bild vom Remote-Server herunterladen und im Cache speichern
		$image_data = wp_remote_get($image_url);

		if (is_wp_error($image_data)) {
			return $image_url; // Bei Fehler: Original-URL zurückgeben
		}

		// Bilddaten speichern
		file_put_contents($cached_image_path, wp_remote_retrieve_body($image_data));
	}

	// URL zum lokal gecachten Bild zurückgeben
	$cached_image_url = $upload_dir['baseurl'] . '/image_cache/' . $image_filename;

	return $cached_image_url;
}

