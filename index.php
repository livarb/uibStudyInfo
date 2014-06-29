<?php
header('Content-Type: text/html; charset=utf-8');

require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim(array(
    'debug' => true
));

// MySQL instillingar
// DATABASE-CREDENTIALS
// see sample-connect.php
include('connect.php');

$connection = @mysql_connect($hostname, $brukar, $passord)
 or die("Unable to connect to SQL server");
@mysql_select_db($database)
 or die("Kunne ikkje velje database($database)");
mysql_set_charset('utf8', $connection); // json_encode() tar kun UTF8. Viss ikkje blir det null.


// === ROUTES ===

// 71 av 195 studieprogram har ein slik "visste du?"-snutt pr. juni 2014
$app->get('/visste_du/', function () use ($app, $settings) {
	$time_start = microtime(true);
	$result = mysql_query(
		"SELECT 
			b_re_visst,
			title_no,
			id,
			CONCAT('http://www.uib.no/studieprogram/', id) as url
		FROM uib_studyprogrammes 
		WHERE
			b_re_visst is not null
		ORDER BY RAND()
		LIMIT 1
		");
	
	while(($resultArray[] = mysql_fetch_assoc($result)) || array_pop($resultArray));
	
	$time_end = microtime(true);
	$time = number_format($time_end - $time_start, 4);
	$app->response()->header('Query-time', $time);
	
	echo jsonpWrap(json_encode($resultArray));
});

$app->get('/studieprogram/:query', function ($query) use ($app, $settings) {
	$time_start = microtime(true);
	$query_string = mysql_real_escape_string($query);
	$result = mysql_query(
		"SELECT * FROM uib_studyprogrammes 
			WHERE
				id LIKE '%$query_string%' OR
				title_no LIKE '%$query_string%' OR
				sp_innhold LIKE '%$query_string%' OR
				b_re_intro LIKE '%$query_string%'
			ORDER BY id ASC
			LIMIT 0,20
		");
	
	while(($resultArray[] = mysql_fetch_assoc($result)) || array_pop($resultArray));
	
	$time_end = microtime(true);
	$time = number_format($time_end - $time_start, 4);
	$app->response()->header('Query-time', $time);
	
	echo jsonpWrap(json_encode($resultArray));
});

$app->get('/studieprogram/', function () use ($app, $settings) {
	$result = mysql_query(
		"SELECT * FROM uib_studyprogrammes ORDER BY id");
	
	while(($resultArray[] = mysql_fetch_assoc($result)) || array_pop($resultArray));
	echo jsonpWrap(json_encode($resultArray));
});

$app->get('/studiepoengdistribusjon/:stp', function ($stp) use ($app, $settings) {
	$time_start = microtime(true);
	$query_string = mysql_real_escape_string($stp);
	$result = mysql_query(
		"SELECT * FROM uib_subjects 
			WHERE 
				studiepoeng = $stp
		");
	
	while(($resultArray[] = mysql_fetch_assoc($result)) || array_pop($resultArray));

	$data = jsonpWrap(json_encode($resultArray));
	
	$time_end = microtime(true);
	$time = number_format($time_end - $time_start, 4);
	$app->response()->header('Query-time', $time);
	
	echo $data;
});

$app->get('/studiepoengdistribusjon/', function () use ($app, $settings) {
	$result = mysql_query(
		"SELECT 
			studiepoeng,
			COUNT(*) as antal			
		FROM uib_subjects
		GROUP BY (studiepoeng)");
	
	while(($resultArray[] = mysql_fetch_assoc($result)) || array_pop($resultArray));
	echo jsonpWrap(json_encode($resultArray));
});

$app->get('/emnermedtekst2/:query', function ($query) use ($app, $settings) {
	$time_start = microtime(true);
	$query_string = mysql_real_escape_string($query);
	$result = mysql_query(
		"SELECT * FROM uib_subjects 
			WHERE 
				title_no LIKE '%query_string%' OR
				MATCH(eb_innhold, eb_utbytte) AGAINST('$query_string')
		");
	
	while(($resultArray[] = mysql_fetch_assoc($result)) || array_pop($resultArray));
	
	$time_end = microtime(true);
	$time = number_format($time_end - $time_start, 4);
	$app->response()->header('Query-time', $time);
	
	echo jsonpWrap(json_encode($resultArray));
});

$app->get('/emnermedtekst/:query', function ($query) use ($app, $settings) {
	$time_start = microtime(true);
	$query_string = mysql_real_escape_string($query);
	$result = mysql_query(
		"SELECT * FROM uib_subjects 
			WHERE
				id LIKE '%$query_string%' OR
				title_no LIKE '%$query_string%' OR
				eb_innhold LIKE '%$query_string%' OR
				eb_utbytte LIKE '%$query_string%'
			ORDER BY id ASC
			LIMIT 0,20
		");
	
	while(($resultArray[] = mysql_fetch_assoc($result)) || array_pop($resultArray));
	
	$time_end = microtime(true);
	$time = number_format($time_end - $time_start, 4);
	$app->response()->header('Query-time', $time);
	
	echo jsonpWrap(json_encode($resultArray));
});

$app->get('/emnermedtekst/', function () use ($app, $settings) {
	$result = mysql_query(
		"SELECT * FROM uib_subjects ORDER BY id");
	
	while(($resultArray[] = mysql_fetch_assoc($result)) || array_pop($resultArray));
	echo jsonpWrap(json_encode($resultArray));
});

// === YMSE STØTTEFUNKSJONAR OG ANNA ===

$app->get('/hello/:name', function ($name) {
    echo "Hello, $name";
});

function jsonpWrap($jsonp) {
  // $app = Slim::getInstance();
  $app = \Slim\Slim::getInstance();

  if (($jsonCallback = $app->request()->get('jsoncallback')) !== null) {
    $jsonp = sprintf("%s(%s);", $jsonCallback, $jsonp);
    $app->response()->header('Content-type', 'application/javascript');
  } else {
	$app->response()->header('Content-type', 'application/json');
  }
  return $jsonp;
}

function createResult($action, $success = true, $id = 0) {
    return json_encode([
        'action' => $action,
        'success' => $success,
        'id' => intval($id),
    ]);
}

$app->run();
?>