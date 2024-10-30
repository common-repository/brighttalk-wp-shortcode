<?php
/**
 * @wordpress-plugin
 * Plugin Name: BrightTALK WordPress Shortcode
 * Plugin URI: https://github.com/BrightTALK/brighttalk-wp-shortcode/
 * Bitbucket Plugin URI: https://bitbucket.org/brighttalklabs/brighttalk-wp-shortcode
 * Bitbucket Branch: master
 * Description: Add the BrightTALK media player shortcode to to simplify embedding BrightTALK content into your site.
 * Version: 2.4.0
 * Author: BrightTALK
 * Author URI: https://developer.brighttalk.com/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: brighttalk-wp-shortcode
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

function brighttalk_wp_shortcode($atts, $content=null){

  // Currently auto height only works for standalone

  $brighttalk_shortcode_atts = shortcode_atts( array(
    'channelid' => '1166',
    'commid' => '0',
    'displaymode' => 'channellist',
    'height' => '',
    'track' => 'BrightTALK WP Shortcode'
  ), $atts );

  // Validate display options
  switch ($brighttalk_shortcode_atts['displaymode']) {
    case 'standalone':
      $brighttalk_shortcode_atts['height'] = 'auto';
      break;
    case 'channellist':
      $brighttalk_shortcode_atts['height'] = '2000px';
      break;
    default:
      $brighttalk_shortcode_atts['displaymode'] = 'channellist';
      $brighttalk_shortcode_atts['height'] = '2000px';
      break;
  }

  $track = htmlspecialchars($brighttalk_shortcode_atts['track'], ENT_XML1 | ENT_QUOTES, 'UTF-8');

  // Get the host.
  $host = get_option( 'BRIGHTTALK_API_HOSTNAME' );
  // Check if the host isn't set.
  if ( false == $host ) {
    // Set the default host.
    $host = 'www.brighttalk.com';
  }
	// Get environment.
	if ( 'www.int01.brighttalk.net' === $host ) {
		$environment = 'int01';
	} elseif ( 'www.test01.brighttalk.net' === $host ) {
		$environment = 'test01';
	} elseif ( 'www.stage.brighttalk.net' === $host ) {
		$environment = 'stage';
	} else {
		// Default.
		$environment = 'prod';
	}
  // Take care - this is VeRy case sensitive
  $embed = '<script src="https://' . $host . '/clients/js/player-embed/player-embed.js" class="jsBrightTALKEmbed">{"channelId" : %d, "commid" : %d, "height" : "%s", "width" : "100%%", "displayMode" : "%s", "track" : "%s", "environment" : "%s" }</script>';

  $op = sprintf($embed, $brighttalk_shortcode_atts['channelid'], $brighttalk_shortcode_atts['commid'], $brighttalk_shortcode_atts['height'], $brighttalk_shortcode_atts['displaymode'], $track, $environment );

  return $op;
}

function brighttalk_wp_time($atts, $content=null){

  // Parse args
  $time_atts = shortcode_atts( array(
    'epoch' => 0,
    'format' => 'F j, Y, g:ia T'
  ), $atts );

  if ($time_atts['epoch'] == 0) {
     return "No epoch set";
  }

	// Check if a cookie is set
	if ( ! isset( $_COOKIE['BTSESSION'] ) ) {
		// Get environment data
		$country = getenv( 'HTTP_GEOIP_COUNTRY_CODE' );
		$region = getenv( 'HTTP_GEOIP_REGION' );
		$tz = brighttalk_region_tz_lookup(
			$country,
			$region
		);
		if ( false === $tz ) {
			// Set a default
			$tz = 'UTC';
		}
	} else {
		// Split the string
		$parts = explode( ':', $_COOKIE['BTSESSION'] );
		// Check if a time zone is available
		if ( empty( $parts[4] ) ) {
			// Get environment data
			$country = getenv( 'HTTP_GEOIP_COUNTRY_CODE' );
			$region = getenv( 'HTTP_GEOIP_REGION' );
			$tz = brighttalk_region_tz_lookup(
				$country,
				$region
			);
			if ( false === $tz ) {
				// Set a default
				$tz = 'UTC';
			}
		} else {
			// Set time zone
			$tz = $parts[4];
		}
	}

  // Convert date
  $epoch = $time_atts['epoch'];
  $datetime = new DateTime("@$epoch");
  $user_timezone = new DateTimeZone($tz);
  $datetime->setTimezone($user_timezone);
  $op = $datetime->format($time_atts['format']);

  return $op;
}


add_shortcode('BrightTALK', 'brighttalk_wp_shortcode');
add_shortcode('brighttalk', 'brighttalk_wp_shortcode');
add_shortcode('brighttalk-time', 'brighttalk_wp_time');


// Will only work for sites hosted on brighttalk.com
function brighttalk_getTZFromBTSession($session) {
  $session_decode = urldecode($session);
  $args = explode(':',$session_decode);
  return $args[4];
}

function activate_brighttalk_wp_shortcode() {
  $url = "https://docs.google.com/forms/d/e/1FAIpQLScWiqcp55gZbaVhGpWsNRbHi4xAkZ4edvENPGaTRvoyo-ymtQ/formResponse";

  $response = wp_remote_post( $url, array(
    'method' => 'POST',
    'timeout' => 5,
    'redirection' => 5,
    'httpversion' => '1.0',
    'blocking' => true,
    'headers' => array(),
    'body' => array( 'entry.470806162' => 'Activated', 'entry.631341731' => 'brighttalk-wp-shortcode', 'entry.678235294' => site_url(), 'entry.1909258079' => get_bloginfo('version'), 'submit' => 'Submit' ),
    'cookies' => array()
    )
  );
}


function deactivate_brighttalk_wp_shortcode() {
  $url = "https://docs.google.com/forms/d/e/1FAIpQLScWiqcp55gZbaVhGpWsNRbHi4xAkZ4edvENPGaTRvoyo-ymtQ/formResponse";

  $response = wp_remote_post( $url, array(
    'method' => 'POST',
    'timeout' => 5,
    'redirection' => 5,
    'httpversion' => '1.0',
    'blocking' => true,
    'headers' => array(),
    'body' => array( 'entry.470806162' => 'Deactivated', 'entry.631341731' => 'brighttalk-wp-shortcode', 'entry.678235294' => site_url(), 'entry.1909258079' => get_bloginfo('version'), 'submit' => 'Submit' ),
    'cookies' => array()
    )
  );

}


register_activation_hook( __FILE__, 'activate_brighttalk_wp_shortcode' );
register_deactivation_hook( __FILE__, 'deactivate_brighttalk_wp_shortcode' );

function run_brighttalk_wp_shortcode() {
}

run_brighttalk_wp_shortcode();

// Add BrightTALK TZ Helper function
function brighttalk_region_tz_lookup($country, $region) {
    $timezone = false;
    switch ($country) {
        case "AD":
            $timezone = "Europe/Andorra";
            break;
        case "AE":
            $timezone = "Asia/Dubai";
            break;
        case "AF":
            $timezone = "Asia/Kabul";
            break;
        case "AG":
            $timezone = "America/Antigua";
            break;
        case "AI":
            $timezone = "America/Anguilla";
            break;
        case "AL":
            $timezone = "Europe/Tirane";
            break;
        case "AM":
            $timezone = "Asia/Yerevan";
            break;
        case "AN":
            $timezone = "America/Curacao";
            break;
        case "AO":
            $timezone = "Africa/Luanda";
            break;
        case "AQ":
            $timezone = "Antarctica/South_Pole";
            break;
        case "AR":
            $timezone = "America/Argentina/Buenos_Aires";
            break;
        case "AS":
            $timezone = "Pacific/Pago_Pago";
            break;
        case "AT":
            $timezone = "Europe/Vienna";
            break;
        case "AU":
            $timezone = "Australia/Sydney";
            break;
        case "AW":
            $timezone = "America/Aruba";
            break;
        case "AX":
            $timezone = "Europe/Mariehamn";
            break;
        case "AZ":
            $timezone = "Asia/Baku";
            break;
        case "BA":
            $timezone = "Europe/Sarajevo";
            break;
        case "BB":
            $timezone = "America/Barbados";
            break;
        case "BD":
            $timezone = "Asia/Dhaka";
            break;
        case "BE":
            $timezone = "Europe/Brussels";
            break;
        case "BF":
            $timezone = "Africa/Ouagadougou";
            break;
        case "BG":
            $timezone = "Europe/Sofia";
            break;
        case "BH":
            $timezone = "Asia/Bahrain";
            break;
        case "BI":
            $timezone = "Africa/Bujumbura";
            break;
        case "BJ":
            $timezone = "Africa/Porto-Novo";
            break;
        case "BL":
            $timezone = "America/St_Barthelemy";
            break;
        case "BM":
            $timezone = "Atlantic/Bermuda";
            break;
        case "BN":
            $timezone = "Asia/Brunei";
            break;
        case "BO":
            $timezone = "America/La_Paz";
            break;
        case "BQ":
            $timezone = "America/Curacao";
            break;
        case "BR":
            $timezone = "America/Sao_Paulo";
            break;
        case "BS":
            $timezone = "America/Nassau";
            break;
        case "BT":
            $timezone = "Asia/Thimphu";
            break;
        case "BV":
            $timezone = "Antarctica/Syowa";
            break;
        case "BW":
            $timezone = "Africa/Gaborone";
            break;
        case "BY":
            $timezone = "Europe/Minsk";
            break;
        case "BZ":
            $timezone = "America/Belize";
            break;
        case "CA":
            switch ($region) {
                case "AB":
                    $timezone = "America/Edmonton";
                    break;
                case "BC":
                    $timezone = "America/Vancouver";
                    break;
                case "MB":
                    $timezone = "America/Winnipeg";
                    break;
                case "NB":
                    $timezone = "America/Halifax";
                    break;
                case "NL":
                    $timezone = "America/St_Johns";
                    break;
                case "NS":
                    $timezone = "America/Halifax";
                    break;
                case "NT":
                    $timezone = "America/Yellowknife";
                    break;
                case "NU":
                    $timezone = "America/Rankin_Inlet";
                    break;
                case "ON":
                    $timezone = "America/Toronto";
                    break;
                case "PE":
                    $timezone = "America/Halifax";
                    break;
                case "QC":
                    $timezone = "America/Montreal";
                    break;
                case "SK":
                    $timezone = "America/Regina";
                    break;
                case "YT":
                    $timezone = "America/Whitehorse";
                    break;
        }
        break;
        case "CC":
            $timezone = "Indian/Cocos";
            break;
        case "CD":
            $timezone = "Africa/Kinshasa";
            break;
        case "CF":
            $timezone = "Africa/Bangui";
            break;
        case "CG":
            $timezone = "Africa/Brazzaville";
            break;
        case "CH":
            $timezone = "Europe/Zurich";
            break;
        case "CI":
            $timezone = "Africa/Abidjan";
            break;
        case "CK":
            $timezone = "Pacific/Rarotonga";
            break;
        case "CL":
            $timezone = "America/Santiago";
            break;
        case "CM":
            $timezone = "Africa/Lagos";
            break;
        case "CN":
            $timezone = "Asia/Shanghai";
            break;
        case "CO":
            $timezone = "America/Bogota";
            break;
        case "CR":
            $timezone = "America/Costa_Rica";
            break;
        case "CU":
            $timezone = "America/Havana";
            break;
        case "CV":
            $timezone = "Atlantic/Cape_Verde";
            break;
        case "CW":
            $timezone = "America/Curacao";
            break;
        case "CX":
            $timezone = "Indian/Christmas";
            break;
        case "CY":
            $timezone = "Asia/Nicosia";
            break;
        case "CZ":
            $timezone = "Europe/Prague";
            break;
        case "DE":
            $timezone = "Europe/Berlin";
            break;
        case "DJ":
            $timezone = "Africa/Djibouti";
            break;
        case "DK":
            $timezone = "Europe/Copenhagen";
            break;
        case "DM":
            $timezone = "America/Dominica";
            break;
        case "DO":
            $timezone = "America/Santo_Domingo";
            break;
        case "DZ":
            $timezone = "Africa/Algiers";
            break;
        case "EC":
            $timezone = "America/Guayaquil";
            break;
        case "EE":
            $timezone = "Europe/Tallinn";
            break;
        case "EG":
            $timezone = "Africa/Cairo";
            break;
        case "EH":
            $timezone = "Africa/El_Aaiun";
            break;
        case "ER":
            $timezone = "Africa/Asmara";
            break;
        case "ES":
            $timezone = "Europe/Madrid";
            break;
        case "ET":
            $timezone = "Africa/Addis_Ababa";
            break;
        case "FI":
            $timezone = "Europe/Helsinki";
            break;
        case "FJ":
            $timezone = "Pacific/Fiji";
            break;
        case "FK":
            $timezone = "Atlantic/Stanley";
            break;
        case "FM":
            $timezone = "Pacific/Pohnpei";
            break;
        case "FO":
            $timezone = "Atlantic/Faroe";
            break;
        case "FR":
            $timezone = "Europe/Paris";
            break;
        case "FX":
            $timezone = "Europe/Paris";
            break;
        case "GA":
            $timezone = "Africa/Libreville";
            break;
        case "GB":
            $timezone = "Europe/London";
            break;
        case "GD":
            $timezone = "America/Grenada";
            break;
        case "GE":
            $timezone = "Asia/Tbilisi";
            break;
        case "GF":
            $timezone = "America/Cayenne";
            break;
        case "GG":
            $timezone = "Europe/Guernsey";
            break;
        case "GH":
            $timezone = "Africa/Accra";
            break;
        case "GI":
            $timezone = "Europe/Gibraltar";
            break;
        case "GL":
            $timezone = "America/Godthab";
            break;
        case "GM":
            $timezone = "Africa/Banjul";
            break;
        case "GN":
            $timezone = "Africa/Conakry";
            break;
        case "GP":
            $timezone = "America/Guadeloupe";
            break;
        case "GQ":
            $timezone = "Africa/Malabo";
            break;
        case "GR":
            $timezone = "Europe/Athens";
            break;
        case "GS":
            $timezone = "Atlantic/South_Georgia";
            break;
        case "GT":
            $timezone = "America/Guatemala";
            break;
        case "GU":
            $timezone = "Pacific/Guam";
            break;
        case "GW":
            $timezone = "Africa/Bissau";
            break;
        case "GY":
            $timezone = "America/Guyana";
            break;
        case "HK":
            $timezone = "Asia/Hong_Kong";
            break;
        case "HN":
            $timezone = "America/Tegucigalpa";
            break;
        case "HR":
            $timezone = "Europe/Zagreb";
            break;
        case "HT":
            $timezone = "America/Port-au-Prince";
            break;
        case "HU":
            $timezone = "Europe/Budapest";
            break;
        case "ID":
            $timezone = "Asia/Jakarta";
            break;
        case "IE":
            $timezone = "Europe/Dublin";
            break;
        case "IL":
            $timezone = "Asia/Jerusalem";
            break;
        case "IM":
            $timezone = "Europe/Isle_of_Man";
            break;
        case "IN":
            $timezone = "Asia/Kolkata";
            break;
        case "IO":
            $timezone = "Indian/Chagos";
            break;
        case "IQ":
            $timezone = "Asia/Baghdad";
            break;
        case "IR":
            $timezone = "Asia/Tehran";
            break;
        case "IS":
            $timezone = "Atlantic/Reykjavik";
            break;
        case "IT":
            $timezone = "Europe/Rome";
            break;
        case "JE":
            $timezone = "Europe/Jersey";
            break;
        case "JM":
            $timezone = "America/Jamaica";
            break;
        case "JO":
            $timezone = "Asia/Amman";
            break;
        case "JP":
            $timezone = "Asia/Tokyo";
            break;
        case "KE":
            $timezone = "Africa/Nairobi";
            break;
        case "KG":
            $timezone = "Asia/Bishkek";
            break;
        case "KH":
            $timezone = "Asia/Phnom_Penh";
            break;
        case "KI":
            $timezone = "Pacific/Tarawa";
            break;
        case "KM":
            $timezone = "Indian/Comoro";
            break;
        case "KN":
            $timezone = "America/St_Kitts";
            break;
        case "KP":
            $timezone = "Asia/Pyongyang";
            break;
        case "KR":
            $timezone = "Asia/Seoul";
            break;
        case "KW":
            $timezone = "Asia/Kuwait";
            break;
        case "KY":
            $timezone = "America/Cayman";
            break;
        case "KZ":
            $timezone = "Asia/Almaty";
            break;
        case "LA":
            $timezone = "Asia/Vientiane";
            break;
        case "LB":
            $timezone = "Asia/Beirut";
            break;
        case "LC":
            $timezone = "America/St_Lucia";
            break;
        case "LI":
            $timezone = "Europe/Vaduz";
            break;
        case "LK":
            $timezone = "Asia/Colombo";
            break;
        case "LR":
            $timezone = "Africa/Monrovia";
            break;
        case "LS":
            $timezone = "Africa/Maseru";
            break;
        case "LT":
            $timezone = "Europe/Vilnius";
            break;
        case "LU":
            $timezone = "Europe/Luxembourg";
            break;
        case "LV":
            $timezone = "Europe/Riga";
            break;
        case "LY":
            $timezone = "Africa/Tripoli";
            break;
        case "MA":
            $timezone = "Africa/Casablanca";
            break;
        case "MC":
            $timezone = "Europe/Monaco";
            break;
        case "MD":
            $timezone = "Europe/Chisinau";
            break;
        case "ME":
            $timezone = "Europe/Podgorica";
            break;
        case "MF":
            $timezone = "America/Marigot";
            break;
        case "MG":
            $timezone = "Indian/Antananarivo";
            break;
        case "MH":
            $timezone = "Pacific/Kwajalein";
            break;
        case "MK":
            $timezone = "Europe/Skopje";
            break;
        case "ML":
            $timezone = "Africa/Bamako";
            break;
        case "MM":
            $timezone = "Asia/Rangoon";
            break;
        case "MN":
            $timezone = "Asia/Ulaanbaatar";
            break;
        case "MO":
            $timezone = "Asia/Macau";
            break;
        case "MP":
            $timezone = "Pacific/Saipan";
            break;
        case "MQ":
            $timezone = "America/Martinique";
            break;
        case "MR":
            $timezone = "Africa/Nouakchott";
            break;
        case "MS":
            $timezone = "America/Montserrat";
            break;
        case "MT":
            $timezone = "Europe/Malta";
            break;
        case "MU":
            $timezone = "Indian/Mauritius";
            break;
        case "MV":
            $timezone = "Indian/Maldives";
            break;
        case "MW":
            $timezone = "Africa/Blantyre";
            break;
        case "MX":
            $timezone = "America/Mexico_City";
            break;
        case "MY":
            $timezone = "Asia/Kuala_Lumpur";
            break;
        case "MZ":
            $timezone = "Africa/Maputo";
            break;
        case "NA":
            $timezone = "Africa/Windhoek";
            break;
        case "NC":
            $timezone = "Pacific/Noumea";
            break;
        case "NE":
            $timezone = "Africa/Niamey";
            break;
        case "NF":
            $timezone = "Pacific/Norfolk";
            break;
        case "NG":
            $timezone = "Africa/Lagos";
            break;
        case "NI":
            $timezone = "America/Managua";
            break;
        case "NL":
            $timezone = "Europe/Amsterdam";
            break;
        case "NO":
            $timezone = "Europe/Oslo";
            break;
        case "NP":
            $timezone = "Asia/Kathmandu";
            break;
        case "NR":
            $timezone = "Pacific/Nauru";
            break;
        case "NU":
            $timezone = "Pacific/Niue";
            break;
        case "NZ":
            $timezone = "Pacific/Auckland";
            break;
        case "OM":
            $timezone = "Asia/Muscat";
            break;
        case "PA":
            $timezone = "America/Panama";
            break;
        case "PE":
            $timezone = "America/Lima";
            break;
        case "PF":
            $timezone = "Pacific/Marquesas";
            break;
        case "PG":
            $timezone = "Pacific/Port_Moresby";
            break;
        case "PH":
            $timezone = "Asia/Manila";
            break;
        case "PK":
            $timezone = "Asia/Karachi";
            break;
        case "PL":
            $timezone = "Europe/Warsaw";
            break;
        case "PM":
            $timezone = "America/Miquelon";
            break;
        case "PN":
            $timezone = "Pacific/Pitcairn";
            break;
        case "PR":
            $timezone = "America/Puerto_Rico";
            break;
        case "PS":
            $timezone = "Asia/Gaza";
            break;
        case "PT":
            $timezone = "Europe/Lisbon";
            break;
        case "PW":
            $timezone = "Pacific/Palau";
            break;
        case "PY":
            $timezone = "America/Asuncion";
            break;
        case "QA":
            $timezone = "Asia/Qatar";
            break;
        case "RE":
            $timezone = "Indian/Reunion";
            break;
        case "RO":
            $timezone = "Europe/Bucharest";
            break;
        case "RS":
            $timezone = "Europe/Belgrade";
            break;
        case "RU":
            $timezone = "Europe/Moscow";
            break;
        case "RW":
            $timezone = "Africa/Kigali";
            break;
        case "SA":
            $timezone = "Asia/Riyadh";
            break;
        case "SB":
            $timezone = "Pacific/Guadalcanal";
            break;
        case "SC":
            $timezone = "Indian/Mahe";
            break;
        case "SD":
            $timezone = "Africa/Khartoum";
            break;
        case "SE":
            $timezone = "Europe/Stockholm";
            break;
        case "SG":
            $timezone = "Asia/Singapore";
            break;
        case "SH":
            $timezone = "Atlantic/St_Helena";
            break;
        case "SI":
            $timezone = "Europe/Ljubljana";
            break;
        case "SJ":
            $timezone = "Arctic/Longyearbyen";
            break;
        case "SK":
            $timezone = "Europe/Bratislava";
            break;
        case "SL":
            $timezone = "Africa/Freetown";
            break;
        case "SM":
            $timezone = "Europe/San_Marino";
            break;
        case "SN":
            $timezone = "Africa/Dakar";
            break;
        case "SO":
            $timezone = "Africa/Mogadishu";
            break;
        case "SR":
            $timezone = "America/Paramaribo";
            break;
        case "SS":
            $timezone = "Africa/Juba";
            break;
        case "ST":
            $timezone = "Africa/Sao_Tome";
            break;
        case "SV":
            $timezone = "America/El_Salvador";
            break;
        case "SX":
            $timezone = "America/Curacao";
            break;
        case "SY":
            $timezone = "Asia/Damascus";
            break;
        case "SZ":
            $timezone = "Africa/Mbabane";
            break;
        case "TC":
            $timezone = "America/Grand_Turk";
            break;
        case "TD":
            $timezone = "Africa/Ndjamena";
            break;
        case "TF":
            $timezone = "Indian/Kerguelen";
            break;
        case "TG":
            $timezone = "Africa/Lome";
            break;
        case "TH":
            $timezone = "Asia/Bangkok";
            break;
        case "TJ":
            $timezone = "Asia/Dushanbe";
            break;
        case "TK":
            $timezone = "Pacific/Fakaofo";
            break;
        case "TL":
            $timezone = "Asia/Dili";
            break;
        case "TM":
            $timezone = "Asia/Ashgabat";
            break;
        case "TN":
            $timezone = "Africa/Tunis";
            break;
        case "TO":
            $timezone = "Pacific/Tongatapu";
            break;
        case "TR":
            $timezone = "Asia/Istanbul";
            break;
        case "TT":
            $timezone = "America/Port_of_Spain";
            break;
        case "TV":
            $timezone = "Pacific/Funafuti";
            break;
        case "TW":
            $timezone = "Asia/Taipei";
            break;
        case "TZ":
            $timezone = "Africa/Dar_es_Salaam";
            break;
        case "UA":
            $timezone = "Europe/Kiev";
            break;
        case "UG":
            $timezone = "Africa/Kampala";
            break;
        case "UM":
            $timezone = "Pacific/Wake";
            break;
        case "US":
            switch ($region) {
                case "AK":
                    $timezone = "America/Anchorage";
                    break;
                case "AL":
                    $timezone = "America/Chicago";
                    break;
                case "AR":
                    $timezone = "America/Chicago";
                    break;
                case "AZ":
                    $timezone = "America/Phoenix";
                    break;
                case "CA":
                    $timezone = "America/Los_Angeles";
                    break;
                case "CO":
                    $timezone = "America/Denver";
                    break;
                case "CT":
                    $timezone = "America/New_York";
                    break;
                case "DC":
                    $timezone = "America/New_York";
                    break;
                case "DE":
                    $timezone = "America/New_York";
                    break;
                case "FL":
                    $timezone = "America/New_York";
                    break;
                case "GA":
                    $timezone = "America/New_York";
                    break;
                case "HI":
                    $timezone = "Pacific/Honolulu";
                    break;
                case "IA":
                    $timezone = "America/Chicago";
                    break;
                case "ID":
                    $timezone = "America/Denver";
                    break;
                case "IL":
                    $timezone = "America/Chicago";
                    break;
                case "IN":
                    $timezone = "America/Indiana/Indianapolis";
                    break;
                case "KS":
                    $timezone = "America/Chicago";
                    break;
                case "KY":
                    $timezone = "America/New_York";
                    break;
                case "LA":
                    $timezone = "America/Chicago";
                    break;
                case "MA":
                    $timezone = "America/New_York";
                    break;
                case "MD":
                    $timezone = "America/New_York";
                    break;
                case "ME":
                    $timezone = "America/New_York";
                    break;
                case "MI":
                    $timezone = "America/New_York";
                    break;
                case "MN":
                    $timezone = "America/Chicago";
                    break;
                case "MO":
                    $timezone = "America/Chicago";
                    break;
                case "MS":
                    $timezone = "America/Chicago";
                    break;
                case "MT":
                    $timezone = "America/Denver";
                    break;
                case "NC":
                    $timezone = "America/New_York";
                    break;
                case "ND":
                    $timezone = "America/Chicago";
                    break;
                case "NE":
                    $timezone = "America/Chicago";
                    break;
                case "NH":
                    $timezone = "America/New_York";
                    break;
                case "NJ":
                    $timezone = "America/New_York";
                    break;
                case "NM":
                    $timezone = "America/Denver";
                    break;
                case "NV":
                    $timezone = "America/Los_Angeles";
                    break;
                case "NY":
                    $timezone = "America/New_York";
                    break;
                case "OH":
                    $timezone = "America/New_York";
                    break;
                case "OK":
                    $timezone = "America/Chicago";
                    break;
                case "OR":
                    $timezone = "America/Los_Angeles";
                    break;
                case "PA":
                    $timezone = "America/New_York";
                    break;
                case "RI":
                    $timezone = "America/New_York";
                    break;
                case "SC":
                    $timezone = "America/New_York";
                    break;
                case "SD":
                    $timezone = "America/Chicago";
                    break;
                case "TN":
                    $timezone = "America/Chicago";
                    break;
                case "TX":
                    $timezone = "America/Chicago";
                    break;
                case "UT":
                    $timezone = "America/Denver";
                    break;
                case "VA":
                    $timezone = "America/New_York";
                    break;
                case "VT":
                    $timezone = "America/New_York";
                    break;
                case "WA":
                    $timezone = "America/Los_Angeles";
                    break;
                case "WI":
                    $timezone = "America/Chicago";
                    break;
                case "WV":
                    $timezone = "America/New_York";
                    break;
                case "WY":
                    $timezone = "America/Denver";
                    break;
        }
        break;
        case "UY":
            $timezone = "America/Montevideo";
            break;
        case "UZ":
            $timezone = "Asia/Tashkent";
            break;
        case "VA":
            $timezone = "Europe/Vatican";
            break;
        case "VC":
            $timezone = "America/St_Vincent";
            break;
        case "VE":
            $timezone = "America/Caracas";
            break;
        case "VG":
            $timezone = "America/Tortola";
            break;
        case "VI":
            $timezone = "America/St_Thomas";
            break;
        case "VN":
            $timezone = "Asia/Phnom_Penh";
            break;
        case "VU":
            $timezone = "Pacific/Efate";
            break;
        case "WF":
            $timezone = "Pacific/Wallis";
            break;
        case "WS":
            $timezone = "Pacific/Pago_Pago";
            break;
        case "YE":
            $timezone = "Asia/Aden";
            break;
        case "YT":
            $timezone = "Indian/Mayotte";
            break;
        case "YU":
            $timezone = "Europe/Belgrade";
            break;
        case "ZA":
            $timezone = "Africa/Johannesburg";
            break;
        case "ZM":
            $timezone = "Africa/Lusaka";
            break;
        case "ZW":
            $timezone = "Africa/Harare";
            break;
    }
    return $timezone;
}
