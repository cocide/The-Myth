<?php
if (!file_exists("conf.php")) {
	ob_end_clean();
	header("Location: $base_dir/install");
	exit;
}
include("conf.php");
ini_set('display_errors', '0');
$db = mysql_connect(DB_HOST, DB_USER, DB_PASS);

if (!$db) {
	die('Could not connect: ' . mysql_error());
}
mysql_select_db(DATABASE);

require_once("functions.php");

// blank entries we have nothing on
$query = "SELECT * FROM id_asoc WHERE rt_number IS NULL";
$blank_rt = mysql_query($query);
while($row = mysql_fetch_array($blank_rt, MYSQL_ASSOC)) {
	$imdb_number = $row['imdb_number'];
	rt_getData($imdb_number);
}

$query = "SELECT * FROM id_asoc WHERE tmdb_number IS NULL";
$blank_tmdb = mysql_query($query);
while($row = mysql_fetch_array($blank_tmdb, MYSQL_ASSOC)) {
	$imdb_number = $row['imdb_number'];
	$imdbLookupURL="http://api.themoviedb.org/2.1/Movie.imdbLookup/en/json/";
	$tmdb_data = json_response($imdbLookupURL.TMDb_API."/tt".str_pad($imdb_number, 7, "0", STR_PAD_LEFT));

	if (isset($tmdb_data[0]['id'])) {
		$tmdb_number = $tmdb_data[0]['id'];
		# now that we have all the data update the table
		mysql_query ("UPDATE id_asoc SET tmdb_number=".mysql_real_escape_string($tmdb_number)." WHERE imdb_number=".$imdb_number);
		tmdb_getData($tmdb_number, $imdb_number);
	}

}

// out of date entries
$query = "SELECT * FROM info_rt, id_asoc WHERE info_rt.imdb_number = id_asoc.imdb_number AND info_rt.date_updated < DATE(DATE_SUB(NOW(),INTERVAL 15 DAY)) ORDER BY date_updated ASC LIMIT 150";
$blank_rt = mysql_query($query);
while($row = mysql_fetch_array($blank_rt, MYSQL_ASSOC)) {
	rt_getData($row['imdb_number']);
}

$query = "SELECT * FROM info_tmdb, id_asoc WHERE info_tmdb.imdb_number = id_asoc.imdb_number AND info_tmdb.date_updated < DATE(DATE_SUB(NOW(),INTERVAL 15 DAY)) ORDER BY date_updated ASC LIMIT 150";
$blank_tmdb = mysql_query($query);
while($row = mysql_fetch_array($blank_tmdb, MYSQL_ASSOC)) {
	tmdb_getData($row['tmdb_number'], $row['imdb_number']);
}
echo "done";
?>