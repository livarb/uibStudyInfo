<?php
// (C) Livar Bergheim - livar.bergheim@gmail.com
// june 2014 - february 2015

// To run this script - set value of $settings["apikey"] (see line below)

$time_start = microtime(true);

// header("Content-type: text/javascript"); // to make javascripts/browsers happy
header('Content-Type: text/html; charset=utf-8');

// DATABASE-CREDENTIALS
include('connect.php');

// SETTINGS
// $settings["apikey"] = "";
$settings["semester"] = "2015v";
$settings["limit"] = 3000; // set low, e.g. 5, for testing purposes. ca. 2100 subjects at UiB pr 2014-06-10

// Start with fetching top-level NUS-codes
$nus_codes_raw = file_get_contents("https://fs-pres.data.uib.no/" . $settings["apikey"] . "/fag/info.json");
$nus_codes_data = json_decode($nus_codes_raw, true);

$subjects_counter = 0;
$study_points_counter = 0;
$max["title_no"] = 0;
$max["title_en"] = 0;
$max["id"] = 0;
$max["hasSchedule"] = 0;
$max["hasNoSchedule"] = 0;
$max["apiCalls"] = 1; // count number of API-calls

// prints each CSV-line
function printLine($dates_iso, $subject, $period) {
	$period_split = explode("-", str_replace(":", "", $period));
	$delim = "; ";

	if (is_array($dates_iso["row"])) { // if multiple dates
		foreach($dates_iso["row"] as $date) {
			echo (
				$subject["id"] . $delim . 
				$date . $delim . 
				$period_split[0] . $delim . 
				$period_split[1] . $delim . 
				$subject["studiepoeng"] . $delim .
				$subject["category"] . $delim .
				$subject["title_no"] . $delim .
				$subject["title_en"] .
				"\n");
		}
	} else { // only one date in the object
		$date = $dates_iso["row"];
			echo (
				$subject["id"] . $delim . 
				$date . $delim . 
				$period_split[0] . $delim . 
				$period_split[1] . $delim . 
				$subject["studiepoeng"] . $delim .
				$subject["category"] . $delim .
				$subject["title_no"] . $delim .
				$subject["title_en"] .
				"\n");
	}
}

// HEADING
echo "emnekode; dato; starttid; sluttid; emne_studiepoeng; emne_kategori; emne_tittel_no; emne_tittel_en\n";

$descriptionTypes = array(); // for data-debugging - checking unique values for attribute description

if (count($nus_codes_data) < 5) {
	die("Didn't get any NUS codes from UiB..\nOpen data service down at the moment?");
}

foreach ($nus_codes_data as $nus_code => $nus_text) {
	// print ("\n=== " . $nus_code . "  " . $nus_text["nn"] . "\n");
	
	// fetch data for each NUS-code, which includes subjects classified under each NUS-code
	$subjectsByNUS_raw = file_get_contents("https://fs-pres.data.uib.no/" . $settings["apikey"] . "/fag/" . $nus_code . "/info.json");
	$subjectsByNUS_data = json_decode($subjectsByNUS_raw, true);
	$max["apiCalls"]++;
	// print_r($subjectsByNUS_data);
	
	if (isset($subjectsByNUS_data["emner"])) {
		foreach ($subjectsByNUS_data["emner"] as $subject) {
			// if (substr($subject["id"], 0, 3) === "INF" && is_numeric(substr($subject["id"], 3, 1)) ) {
				// print ($subject["id"] . "  " . $subject["title_no"] . "\n");

				// print_r($subject);
							
				$max["title_no"] = max($max["title_no"], strlen($subject["title_no"]));
				$max["title_en"] = max($max["title_en"], strlen($subject["title_en"]));
				$max["id"] = max($max["id"], strlen($subject["id"]));
				
				// Note str_replace because "/" in subject code is not encoded as %2F, but replaced with underscore in the query URL
				// NB. This does not work on the dataset used here, timeplanliste
				$subjectSchedule_raw = file_get_contents("https://timeplan.data.uib.no/" . $settings["apikey"] . "/JSON/timeplanliste/" . $settings["semester"] . "/" . str_replace('/', '_', $subject["id"]) );
				$subjectSchedule_data = json_decode($subjectSchedule_raw, true);
				$max["apiCalls"]++;

				// echo "  ";
				if (isset($subjectSchedule_data["row"])) {
					// echo "has schedule\n";
					// print_r($subjectSchedule_data);

					if (!isset($subjectSchedule_data["row"]["name"])) { // several entries
						foreach($subjectSchedule_data["row"] as $scheduleEntry) {
							if ($scheduleEntry["orig_uaktivitet"] == 1 || stripos($scheduleEntry["description"], "forelesning") !== false) {
								$descriptionTypes[] = $scheduleEntry["description"];
								printLine($scheduleEntry["dates_iso"], $subject, $scheduleEntry["period"]);
							}				
						}
					} else { // only one schedule-entry
						if ($subjectSchedule_data["row"]["orig_uaktivitet"] == 1 || stripos($scheduleEntry["description"], "forelesning") !== false) {
							$descriptionTypes[] = $subjectSchedule_data["row"]["description"];
							printLine($subjectSchedule_data["row"]["dates_iso"], $subject, $subjectSchedule_data["row"]["period"]);
						}
					}
					$max["hasSchedule"]++;
				} else {
					// echo "NO schedule\n";
					$max["hasNoSchedule"]++;
				}

				$subjects_counter++;
							
				// limit # subjects fetched - useful for testing
				if ($subjects_counter >= $settings["limit"]) {
					print "\n\nLimit(" . $settings["limit"] . ") reached. Aborting.\n\n";
					break(2);
				}
				
				flush(); // flush output (to receiver/browser)

				// wait to give UiB's API a break between subjects
				// sleep(1); // give UiB's API a 1 second break between subjects
				time_nanosleep(0, 500000000); // 0,5 second break
				// time_nanosleep(0, 050000000); // 0,05 second

				set_time_limit(10); // so the PHP-script doesn't time out
			// } // if - restriction on subjects
		}
	} else {
		// print "No subjects under this NUS-code.\n";
	}
}

echo "\n Description types:\n";
$descriptions = array_unique($descriptionTypes);
print_r($descriptions);

echo "\n\n";

echo "\nNumber of subjects at UiB: " . $subjects_counter . "\n";
echo "Study points: " . $study_points_counter . "\n\n";

print_r($max);

$time_end = microtime(true);
$time = number_format($time_end - $time_start, 4);
echo "\nKøyring av dette scriptet tok $time seconds\n";

?>