<?php
// (C) Livar Bergheim - livar.bergheim@gmail.com
// june 2014

// NOTE:
// see fetchStudieprogram.php, which is almost similar, for better comments and explanation of code.

$time_start = microtime(true);

// header("Content-type: text/javascript"); // to make javascripts/browsers happy
header('Content-Type: text/html; charset=utf-8');

// DATABASE-CREDENTIALS
include('connect.php');

// SETTINGS
// $settings["apikey"] = "";
$settings["table"] = "uib_subjects";
$settings["doSQL"] = false; // turn on/off performing SQL-queries
$settings["limit"] = 5; // set low, e.g. 5, for testing purposes. ca. 2100 subjects at UiB pr 2014-06-10

// DB TILKOBLING - MySQLI
$mysqli = new mysqli($hostname, $brukar, $passord, $database) or die("MySQLI fungerar ikkje...");
$mysqli->set_charset("utf8");

// CLEAR TABLE
if ($settings["doSQL"])
$mysqli->query("DELETE FROM " . $settings["table"]);

// Inserts a subject into DB
function insertSubjectToDB($subject, $settings, $mysqli) {
	if ($settings["doSQL"]) {
		$stmt = $mysqli->prepare("INSERT INTO " . $settings["table"] . " (id, studiepoeng, title_en, title_no) VALUES(?, ?, ?, ?)");
		$stmt->bind_param("ssss",
			$subject["id"],
			$subject["studiepoeng"],
			$subject["title_en"],
			$subject["title_no"]);
		$stmt->execute();
	}
}

function updateSubject($id, $field, $data, $settings, $mysqli) {
	if ($settings["doSQL"]) {
		$stmt = $mysqli->prepare("UPDATE " . $settings["table"] . " SET $field = ? WHERE id = ?");
		$stmt->bind_param("ss",
			$data,
			$id);
		$stmt->execute();
	}
}

// http://es1.php.net/manual/en/function.strip-tags.php#110280
// better than strip_tags() because "<p>M�l</p>Dette er" --> "M�l Dette er"
// would be "M�lDette er" with strip_tags
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

$subjects_counter = 0;
$study_points_counter = 0;
$max["eb_innhold"] = 0;
$max["eb_utbytte"] = 0;
$max["eb_undsem"] = 0;
$max["eb_krav"] = 0;
$max["eb_anbkrav"] = 0;
$max["eb_fagovl"] = 0;

if (count($nus_codes_data) < 5) {
	die("Didn't get any NUS codes from UiB..\nOpen data service down at the moment?");
}

foreach ($nus_codes_data as $nus_code => $nus_text) {
	print ("\n=== " . $nus_code . "  " . $nus_text["nn"] . "\n");
	
	$subjectsByNUS_raw = file_get_contents("https://fs-pres.data.uib.no/" . $settings["apikey"] . "/fag/" . $nus_code . "/info.json");
	$subjectsByNUS_data = json_decode($subjectsByNUS_raw, true);
	// print_r($subjectsByNUS_data);
	
	if (isset($subjectsByNUS_data["emner"])) {
		foreach ($subjectsByNUS_data["emner"] as $subject) {
			if ($subject["category"] == "emne") {
				print ($subject["id"] . "  " . $subject["title_no"] . "\n");
							
				$max["title_no"] = max($max["title_no"], strlen($subject["title_no"]));
				$max["title_en"] = max($max["title_en"], strlen($subject["title_en"]));
				$max["id"] = max($max["id"], strlen($subject["id"]));
				
				insertSubjectToDB($subject, $settings, $mysqli);
				
				// Note str_replace because "/" in subject code is not encoded as %2F, but replaced with underscore in the query URL
				$subjectTexts_raw = file_get_contents("https://fs-pres.data.uib.no/" . $settings["apikey"] . "/nn/emne/" . str_replace('/', '_', $subject["id"]) . "/render.json");
				
				if (strlen($subjectTexts_raw) < 20) { // check if subject has text??
					print ("No text for subject!\n" . $subjectTexts_raw . "\n\n");
					// die("No texts for subject??\n");
				} else {				
				
				$subjectTexts_data = json_decode($subjectTexts_raw, true);
				// print_r($subjectTexts_data);
				// print (count($subjectTexts_data[0]["#items"]) . "\n");
				foreach ($subjectTexts_data[0]["#items"] as $subjectTextItem) {
					switch($subjectTextItem["#type"]) {
						case "EB_INNHOLD":
							$text = rip_tags($subjectTextItem["#text"]);
							$max["eb_innhold"] = max($max["eb_innhold"], strlen($text));
							if (strlen($text) > 0)
							updateSubject($subject["id"], "eb_innhold", $text, $settings, $mysqli);
							break;
						case "EB_UTBYTTE":
							$text = rip_tags($subjectTextItem["#text"]);
							$max["eb_utbytte"] = max($max["eb_utbytte"], strlen($text));
							if (strlen($text) > 0)
							updateSubject($subject["id"], "eb_utbytte", $text, $settings, $mysqli);
							break;
						case "EB_UNDSEM":
							$text = rip_tags($subjectTextItem["#text"]);
							$max["eb_undsem"] = max($max["eb_undsem"], strlen($text));
							if (strlen($text) > 0)
							updateSubject($subject["id"], "eb_undsem", $text, $settings, $mysqli);
							break;
						case "EB_KRAV":
							$text = rip_tags($subjectTextItem["#text"]);
							$max["eb_krav"] = max($max["eb_krav"], strlen($text));
							if (strlen($text) > 0)
							updateSubject($subject["id"], "eb_krav", $text, $settings, $mysqli);
							break;
						case "EB_ANBKRAV":
							$text = rip_tags($subjectTextItem["#text"]);
							$max["eb_anbkrav"] = max($max["eb_anbkrav"], strlen($text));
							if (strlen($text) > 0)
							updateSubject($subject["id"], "eb_anbkrav", $text, $settings, $mysqli);
							break;							
						case "EB_FAGOVL":
							$text = rip_tags($subjectTextItem["#text"]);
							$max["eb_fagovl"] = max($max["eb_fagovl"], strlen($text));
							if (strlen($text) > 0)
							updateSubject($subject["id"], "eb_fagovl", $text, $settings, $mysqli);
							break;							
					}
				}
				
				// print ("\n\n");
				}
				
				$study_points_counter += $subject["studiepoeng"];
				$subjects_counter++;
							
				// limit # subjects fetched - useful for testing
				if ($subjects_counter >= $settings["limit"]) {
					print "\n\nLimit(" . $settings["limit"] . ") reached. Aborting.\n\n";
					break(2);
				}
				
				flush(); // flush output (to receiver/browser)
				sleep(1); // give UiB's API a 1 second break between subjects
				set_time_limit(10); // so the PHP-script doesn't time out
			}
		}
	} else {
		print "No subjects under this NUS-code.\n";
	}
	// flush(); // flushes output buffer
}

echo "\nNumber of subjects at UiB: " . $subjects_counter . "\n";
echo "Study points: " . $study_points_counter . "\n\n";

// LUKK DB
$mysqli->close();

print_r($max);

$time_end = microtime(true);
$time = number_format($time_end - $time_start, 4);
echo "\nK�yring av dette scriptet tok $time seconds\n";

?>