<?php
if (substr($_SERVER['REQUEST_URI'], -1) != "/") {
	$base_dir = dirname($_SERVER['REQUEST_URI']);
} else {
	$base_dir = $_SERVER['REQUEST_URI'];
}

function return_filter($query) {
	$new_filter = mysql_query($query);
	while ($row = mysql_fetch_array($new_filter, MYSQL_ASSOC)) {
		$new_numbers[] = $row['imdb_number'];
	}
	return "(imdb_number=".implode(" OR imdb_number=", $new_numbers).")";
}


function json_response($url) {
	$curl_handle=curl_init();
	curl_setopt($curl_handle, CURLOPT_URL, $url);
	curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl_handle, CURLOPT_HEADER, 1);
	$response = curl_exec($curl_handle);
	curl_close($curl_handle);
	list($header, $json) = explode("\r\n\r\n", $response, 2);
	$data = json_decode($json, TRUE);
	$header_array= explode("\r\n", $header);
	foreach ($header_array as $header_element) {
		$header_clean = array();
		$header_clean = explode(": ", $header_element);
		if (count($header_clean) == 2) {
			$data['header'][$header_clean[0]] = $header_clean[1];
		}
	}
	return $data;
}


function rt_getData($imdb_number) {
	$info[$imdb_number] = json_response("http://api.rottentomatoes.com/api/public/v1.0/movie_alias.json?type=imdb&id=".str_pad($imdb_number, 7, "0", STR_PAD_LEFT)."&apikey=".RT_API);
	if (isset($info[$imdb_number]['title'])) {

		# if that RT number is not in the DB add it
		$query = "SELECT * FROM info_rt WHERE imdb_number=".$imdb_number;
		$result = mysql_query($query);
		if (mysql_num_rows($result) == 0) {
			mysql_query ("INSERT INTO info_rt SET ".
				"imdb_number=".$imdb_number.", ".
				"title='".mysql_real_escape_string($info[$imdb_number]['title'])."', ".
				"mpaa='".mysql_real_escape_string($info[$imdb_number]['mpaa_rating'])."', ".
				"runtime=".mysql_real_escape_string($info[$imdb_number]['runtime']).", ".
				"year=".mysql_real_escape_string($info[$imdb_number]['year']).", ".
				"release_theater='".mysql_real_escape_string($info[$imdb_number]['release_dates']['theater'])."', ".
				"release_dvd='".mysql_real_escape_string($info[$imdb_number]['release_dates']['dvd'])."', ".
				"consensus='".mysql_real_escape_string($info[$imdb_number]['critics_consensus'])."', ".
				"rating_critics=".mysql_real_escape_string($info[$imdb_number]['ratings']['critics_score']).", ".
				"rating_audience=".mysql_real_escape_string($info[$imdb_number]['ratings']['audience_score']).", ".
				"studio='".mysql_real_escape_string($info[$imdb_number]['studio'])."', ".
				"rt_link='".mysql_real_escape_string($info[$imdb_number]['links']['alternate'])."', ".
				"date_updated=NOW()");
		} else {
			$query = "SELECT * FROM info_rt WHERE imdb_number=".$imdb_number." AND date_updated < DATE(DATE_SUB(NOW(),INTERVAL 15 DAY))";
			$result = mysql_query($query);
			if (mysql_num_rows($result) != 0) {
				mysql_query ("UPDATE info_rt SET ".
					"title='".mysql_real_escape_string($info[$imdb_number]['title'])."', ".
					"mpaa='".mysql_real_escape_string($info[$imdb_number]['mpaa_rating'])."', ".
					"runtime=".mysql_real_escape_string($info[$imdb_number]['runtime']).", ".
					"year=".mysql_real_escape_string($info[$imdb_number]['year']).", ".
					"release_theater='".mysql_real_escape_string($info[$imdb_number]['release_dates']['theater'])."', ".
					"release_dvd='".mysql_real_escape_string($info[$imdb_number]['release_dates']['dvd'])."', ".
					"consensus='".mysql_real_escape_string($info[$imdb_number]['critics_consensus'])."', ".
					"rating_critics=".mysql_real_escape_string($info[$imdb_number]['ratings']['critics_score']).", ".
					"rating_audience=".mysql_real_escape_string($info[$imdb_number]['ratings']['audience_score']).", ".
					"studio='".mysql_real_escape_string($info[$imdb_number]['studio'])."', ".
					"rt_link='".mysql_real_escape_string($info[$imdb_number]['links']['alternate'])."', ".
					"date_updated=NOW() ".
					"WHERE imdb_number=".$imdb_number);
			}
		}

		# if we do not have any genre for that RT number add it
		foreach ($info[$imdb_number]['genres'] as $genre) {
			$query = "SELECT * FROM genre WHERE imdb_number=".$imdb_number." AND name='".mysql_real_escape_string($genre)."'";
			$result = mysql_query($query);
			if (mysql_num_rows($result) == 0) {
				mysql_query ("INSERT INTO genre SET imdb_number=".$imdb_number.", name='".mysql_real_escape_string($genre)."'");
			}
		}


		# if we do not have any director for that RT number add it
		foreach ($info[$imdb_number]['abridged_directors'] as $director) {
			$query = "SELECT * FROM director WHERE imdb_number=".$imdb_number." AND name='".mysql_real_escape_string($director['name'])."'";
			$result = mysql_query($query);
			if (mysql_num_rows($result) == 0) {
				mysql_query ("INSERT INTO director SET imdb_number=".$imdb_number.", name='".mysql_real_escape_string($director['name'])."'");
			}
		}

		# if we do not have any cast for that RT number add it
		foreach ($info[$imdb_number]['abridged_cast'] as $person) {
			foreach ($person['characters'] as $character) {
				$query = "SELECT * FROM cast WHERE imdb_number=".$imdb_number." AND rt_celeb_number='".mysql_real_escape_string($person['id'])."' AND role='".mysql_real_escape_string($character)."'";
				$result = mysql_query($query);
				if (mysql_num_rows($result) == 0) {
					mysql_query ("INSERT INTO cast SET imdb_number=".$imdb_number.", name='".mysql_real_escape_string($person['name'])."', role='".mysql_real_escape_string($character)."', rt_celeb_number=".mysql_real_escape_string($person['id']));
				}
			}
		}

		# now that we have all the data update the table
		mysql_query ("UPDATE id_asoc SET rt_number=".mysql_real_escape_string($info[$imdb_number]['id'])." WHERE imdb_number=".$imdb_number);

		usleep(100000); // sleep .1 seconds to not go over our 10 requests per second limit
	}
}


function tmdb_getData($tmdb_number, $imdb_number) {
	$infoURL="http://api.themoviedb.org/2.1/Movie.getInfo/en/json/";
	$tmdb_info = json_response($infoURL.TMDb_API."/".$tmdb_number);

	if (isset($tmdb_info[0])) {

		# if that TMDb number is not in the DB add it
		$query = "SELECT * FROM info_tmdb WHERE imdb_number=".$imdb_number;
		$result = mysql_query($query);
		if (mysql_num_rows($result) == 0) {
			mysql_query ("INSERT INTO info_tmdb SET ".
				"imdb_number=".$imdb_number.", ".
				"tagline='".mysql_real_escape_string($tmdb_info[0]['tagline'])."', ".
				"overview='".mysql_real_escape_string($tmdb_info[0]['overview'])."', ".
				"budget=".mysql_real_escape_string($tmdb_info[0]['budget']).", ".
				"revenue=".mysql_real_escape_string($tmdb_info[0]['revenue']).", ".
				"date_updated=NOW()");
		} else {
			$query = "SELECT * FROM info_tmdb WHERE imdb_number=".$imdb_number." AND date_updated < DATE(DATE_SUB(NOW(),INTERVAL 15 DAY))";
			$result = mysql_query($query);
			if (mysql_num_rows($result) != 0) {
				mysql_query ("UPDATE info_tmdb SET ".
					"tagline='".mysql_real_escape_string($tmdb_info[0]['tagline'])."', ".
					"overview='".mysql_real_escape_string($tmdb_info[0]['overview'])."', ".
					"budget=".mysql_real_escape_string($tmdb_info[0]['budget']).", ".
					"revenue=".mysql_real_escape_string($tmdb_info[0]['revenue']).", ".
					"date_updated=NOW() ".
					"WHERE imdb_number=".$imdb_number);
			}
		}

		# if we do not have any countries for that TMDb number add it
		foreach ($tmdb_info[0]['countries'] as $country) {
			$query = "SELECT * FROM country WHERE imdb_number=".$imdb_number." AND name='".mysql_real_escape_string($country['name'])."'";
			$result = mysql_query($query);
			if (mysql_num_rows($result) == 0) {
				mysql_query ("INSERT INTO country SET imdb_number=".$imdb_number.", name='".mysql_real_escape_string($country['name'])."'");
			}
		}

		# if we do not have any producers for that TMDb number add it
		foreach ($tmdb_info[0]['cast'] as $person) {
			if ($person['job'] == "Producer") {
				$query = "SELECT * FROM producer WHERE imdb_number=".$imdb_number." AND name='".mysql_real_escape_string($person['name'])."'";
				$result = mysql_query($query);
				if (mysql_num_rows($result) == 0) {
					mysql_query ("INSERT INTO producer SET imdb_number=".$imdb_number.", name='".mysql_real_escape_string($person['name'])."'");
				}
			}
		}
	}
}
?>