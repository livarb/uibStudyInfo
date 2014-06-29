<?php
// (C) Livar Bergheim - livar.bergheim@gmail.com
// june 2014

$time_start = microtime(true); // start measuring run-time of script

header('Content-Type: text/html; charset=utf-8'); // utf8 for norwegian characters outputted correctly in runtime output

// DATABASE-CREDENTIALS
// see sample-connect.php
include('connect.php');

// SETTINGS
// $settings["apikey"] = ""; // or place it in connect.php
$settings["table"] = "uib_studyprogrammes";
$settings["doSQL"] = false; // turn on/off performing SQL-queries. For testing
$settings["limit"] = 500; // set low, e.g. 5, for testing purposes. 195 study programmes at UiB pr 2014-06-10

// DB TILKOBLING - type MySQLI
$mysqli = new mysqli($hostname, $brukar, $passord, $database) or die("MySQLI fungerar ikkje...");
$mysqli->set_charset("utf8");

// CLEAR TABLE
if ($settings["doSQL"])
$mysqli->query("DELETE FROM " . $settings["table"]);

// Inserts a study programme into DB
function insertStudyprogrammeToDB($studyprogramme, $settings, $mysqli) {
	if ($settings["doSQL"]) {
		$stmt = $mysqli->prepare("INSERT INTO " . $settings["table"] . " (id, category, nuskode, studiepoeng, title_no, title_en) VALUES(?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("ssssss",
			$studyprogramme["id"],
			$studyprogramme["category"],
			$studyprogramme["nuskode"],
			$studyprogramme["studiepoeng"],
			$studyprogramme["title_no"],
			$studyprogramme["title_en"]);
		$stmt->execute();
	}
}

function updateStudyprogramme($id, $field, $data, $settings, $mysqli) {
	if ($settings["doSQL"]) {
		$stmt = $mysqli->prepare("UPDATE " . $settings["table"] . " SET $field = ? WHERE id = ?");
		$stmt->bind_param("ss",
			$data,
			$id);
		$stmt->execute();
	}
}

// http://es1.php.net/manual/en/function.strip-tags.php#110280
// better than strip_tags() because "<p>Mål</p>Dette er" --> "Mål Dette er"
// would be "MålDette er" with strip_tags
function rip_tags($string) {    
    // ----- remove HTML TAGs ----- 
    $string = preg_replace ('/<[^>]*>/', ' ', $string); 
    
    // ----- remove control characters ----- 
    $string = str_replace("\r", '', $string);    // --- replace with empty space
    $string = str_replace("\n", ' ', $string);   // --- replace with space
    $string = str_replace("\t", ' ', $string);   // --- replace with space
    
    // ----- remove multiple spaces ----- 
    $string = trim(preg_replace('/ {2,}/', ' ', $string));
    
    return $string; 
}

$nus_codes_raw = file_get_contents("https://fs-pres.data.uib.no/" . $settings["apikey"] . "/fag/info.json");
$nus_codes_data = json_decode($nus_codes_raw, true);

if (count($nus_codes_data) < 5) {
	die("Didn't get any NUS codes from UiB..\nOpen data service down at the moment?");
}

// COUNTERS
$max["num_studieprogram"] = 0;
$max["sum_studiepoeng"] = 0;

foreach ($nus_codes_data as $nus_code => $nus_text) {
	print ("\n=== " . $nus_code . "  " . $nus_text["nn"] . "\n");
	
	$studyprogrammesByNUS_raw = file_get_contents("https://fs-pres.data.uib.no/" . $settings["apikey"] . "/fag/" . $nus_code . "/info.json");
	$studyprogrammesByNUS_data = json_decode($studyprogrammesByNUS_raw, true);
	
	if (isset($studyprogrammesByNUS_data["studieprogrammer"])) {
		foreach ($studyprogrammesByNUS_data["studieprogrammer"] as $studyprogramme) {
		
			insertStudyprogrammeToDB($studyprogramme, $settings, $mysqli);
			
			$max["category"] = max($max["category"], strlen($studyprogramme["category"]));
			$max["id"] = max($max["id"], strlen($studyprogramme["id"]));
			$max["nuskode"] = max($max["nuskode"], strlen($studyprogramme["nuskode"]));
			$max["title_no"] = max($max["title_no"], strlen($studyprogramme["title_no"]));
			$max["title_en"] = max($max["title_en"], strlen($studyprogramme["title_en"]));
			$max["num_studieprogram"]++;
			$max["sum_studiepoeng"] += $studyprogramme["studiepoeng"];
		
			// FETCH DETAILS
			$studyprogrammeTexts_raw = file_get_contents("https://fs-pres.data.uib.no/" . $settings["apikey"] . "/studieprogram/" . $studyprogramme["id"] . "/info.json?full");
			
			if (strlen($studyprogrammeTexts_raw) < 20) {
				print ("No text for study programme!\n" . $studyprogrammeTexts_raw . "\n\n");
			} else { // process texts
				$studyprogrammeTexts_data = json_decode($studyprogrammeTexts_raw, true);
				
				foreach($studyprogrammeTexts_data["info"] as $textKey => $textValue) {
					switch($textKey) {
						case "B_RE_INTRO":
							$text = rip_tags($textValue);
							$max["b_re_intro"] = max($max["b_re_intro"], strlen($text));
							if (strlen($text) > 0)
							updateStudyprogramme($studyprogramme["id"], "b_re_intro", $text, $settings, $mysqli);
							break;
						case "B_RE_KRAV":
							$text = rip_tags($textValue);
							$max["b_re_krav"] = max($max["b_re_krav"], strlen($text));
							if (strlen($text) > 0)
							updateStudyprogramme($studyprogramme["id"], "b_re_krav", $text, $settings, $mysqli);							
							break;
						case "B_RE_PLASS":
							$text = rip_tags($textValue);
							$max["b_re_plass"] = max($max["b_re_plass"], strlen($text));
							if (strlen($text) > 0)
							updateStudyprogramme($studyprogramme["id"], "b_re_plass", $text, $settings, $mysqli);								
							break;							
						case "B_RE_UTVEK":
							$text = rip_tags($textValue);
							$max["b_re_utvek"] = max($max["b_re_utvek"], strlen($text));
							if (strlen($text) > 0)
							updateStudyprogramme($studyprogramme["id"], "b_re_utvek", $text, $settings, $mysqli);								
							break;
						case "B_RE_POENG":
							$text = rip_tags($textValue);
							$max["b_re_poeng"] = max($max["b_re_poeng"], strlen($text));
							if (strlen($text) > 0)
							updateStudyprogramme($studyprogramme["id"], "b_re_poeng", $text, $settings, $mysqli);	
							break;
						case "B_RE_VISST":
							$text = rip_tags($textValue);
							if (strlen($text) > 0) {
								$max["b_re_visst"] = max($max["b_re_visst"], strlen($text));
								$text = trim(str_replace("&nbsp;", '', $text));
								updateStudyprogramme($studyprogramme["id"], "b_re_visst", $text, $settings, $mysqli);
							}				
							break;
						case "B_RE_YRKES":
							$text = rip_tags($textValue);
							$max["b_re_yrkes"] = max($max["b_re_yrkes"], strlen($text));
							if (strlen($text) > 0)
							updateStudyprogramme($studyprogramme["id"], "b_re_yrkes", $text, $settings, $mysqli);								
							break;
						case "SP_ARBLREL":
							$text = rip_tags($textValue);
							$max["sp_arblrel"] = max($max["sp_arblrel"], strlen($text));
							if (strlen($text) > 0)
							updateStudyprogramme($studyprogramme["id"], "sp_arblrel", $text, $settings, $mysqli);								
							break;
						case "SP_INNHOLD":
							$text = rip_tags($textValue);
							$max["sp_innhold"] = max($max["sp_innhold"], strlen($text));
							if (strlen($text) > 0)
							updateStudyprogramme($studyprogramme["id"], "sp_innhold", $text, $settings, $mysqli);								
							break;	
						case "SP_UTBYTTE":
							$text = rip_tags($textValue);
							$max["sp_utbytte"] = max($max["sp_utbytte"], strlen($text));
							if (strlen($text) > 0)
							updateStudyprogramme($studyprogramme["id"], "sp_utbytte", $text, $settings, $mysqli);								
							break;			
						case "SP_OBLIGAT":
							$text = rip_tags($textValue);
							$max["sp_obligat"] = max($max["sp_obligat"], strlen($text));
							if (strlen($text) > 0) {
								updateStudyprogramme($studyprogramme["id"], "sp_obligat", $text, $settings, $mysqli);								
								
								// NB. Will not match subjects with ids like PRAKTINF, subjects with '/' in names etc.
								$matches = array();
								preg_match_all("/\b[A-Z]{2,4}[0-9]{3}/", $text, $matches);
								$emner = implode(", ", $matches[0]);
								
								updateStudyprogramme($studyprogramme["id"], "emner", $emner, $settings, $mysqli);	
							}
							break;								
					}
				}
			}
			
			// limit # studyprogrammes fetched - useful for testing
			if ($max["num_studieprogram"] >= $settings["limit"]) {
				print "\n\nLimit(" . $settings["limit"] . ") reached. Aborting.\n\n";
				break(2);
			}
			
			flush(); // flush output (to receiver/browser)
			
			// give UiB's API a break between study programmes
			// sleep(1); // 1 second break
			time_nanosleep(0, 200000000); // 0,2 second break
			
			set_time_limit(10); // so the PHP-script doesn't time out			
		} // foreach studyprogramme
	} else {
		print "No study programmes under this NUS-code.\n";
	} // if-else nus-code has studyprogrammes
} // foreach nus-code

// LUKK DB-TILKOBLING
$mysqli->close();

print ("\n\n");
print_r($max);

/*
output of array $max :
Array
(
    [num_studieprogram] => 195
    [sum_studiepoeng] => 28650
    [category] => 31
    [id] => 11
    [nuskode] => 6
    [title_no] => 81
    [title_en] => 84
    [b_re_intro] => 1426
    [b_re_krav] => 458
    [b_re_plass] => 51
    [b_re_poeng] => 221
    [b_re_utvek] => 759
    [b_re_visst] => 335
    [b_re_yrkes] => 1186
    [sp_arblrel] => 1799
    [sp_innhold] => 5825
    [sp_utbytte] => 5148
)
*/

$time_end = microtime(true);
$time = number_format($time_end - $time_start, 4);
echo "\nKøyring av dette scriptet tok $time sekund\n";

?>