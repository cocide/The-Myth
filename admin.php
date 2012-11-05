<?php
ob_start();
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="style.css" />
	<title>The Myth - Admin</title>
</head>
<body>';

if (!file_exists("conf.php")) {
	ob_end_clean();
	header("Location: install");
	exit;
}
include("conf.php");
ini_set('display_errors', '0');


if (!isset($_POST['PASS']) || $_POST['PASS'] != PASS) {
	echo '<form method="post">
	Admin Password: <input type="password" name="PASS"><br>
	<input type="submit" value="Login">
	</form>';
} else {
	$db = mysql_connect(DB_HOST, DB_USER, DB_PASS);
	if (!$db) {
		die('Could not connect: ' . mysql_error());
	}
	mysql_select_db(DATABASE);

	if (!isset($_FILES["file"])) {
		echo '<form method="post" enctype="multipart/form-data">
			<label for="file">Filename:</label>
			<input type="file" name="file" id="file" /><br />
			<input type="radio" name="wipe" value="no" checked> Add to list<br>
			<input type="radio" name="wipe" value="yes"> Wipe all data before upload<br>
			<input type="hidden" name="PASS" value="'.$_POST['PASS'].'" />
			<br />
			<input type="submit" name="submit" value="Upload" />
			</form>
			NOTE: The file must end in .ls or .txt';
	} elseif ($_FILES["file"]["error"] > 0) {
		echo "Error: " . $_FILES["file"]["error"] . "<br />";
	} else {
		$extension = end(explode(".", $_FILES["file"]["name"]));
		if ($extension != "ls" && $extension != "txt") {
			die ("Extension must be .ls or .txt");
		}
		if ($_POST['wipe'] == "yes") {
			mysql_query("DELETE FROM files");
		}
		# grab the uploaded file and add it to the DB
		$file_list = file($_FILES["file"]["tmp_name"]);
		foreach ($file_list as $count => $fileName) {
			$fileName = preg_replace('/[\x00-\x1F]/', '', $fileName);
			# only if its not a torrent file
			if (end(explode(".", $fileName)) != "torrent" && end(explode(".", $fileName)) != "idx") {
				$imdb_number = substr(preg_replace("/[^\[]*\[[0-9]{4}-([0-9]{7}).*/", "$1", $fileName), 0, 7);
				$query = "SELECT * FROM files WHERE filename='".mysql_real_escape_string($fileName)."'";
				$result = mysql_query($query);
				# and only if its not in the
				if (mysql_num_rows($result) == 0) {
					$query = "INSERT INTO files SET filename='".mysql_real_escape_string($fileName)."', imdb_number=".mysql_real_escape_string($imdb_number);
					if (mysql_query ($query)) {
						if (end(explode(".", $fileName)) == "srt" || end(explode(".", $fileName)) == "sub") {
							# if it inserted well and its a sub denote that
							$query = "UPDATE files SET subtitle=1 WHERE filename='".mysql_real_escape_string($fileName)."'";
							mysql_query($query);
						} else {
							# find and add the resolution of the file
							$resolution = array();
							preg_match('/^[^\]]+\][a-zA-Z]*\.*(([0-9]{3,4}[ip]+)|(DvD))/', $fileName, $resolution);
							if ($resolution[1] == "DvD") {
								$resolution[1] = "SD";
							}
							if (count($resolution) > 1) {
								$query = "UPDATE files SET resolution='".$resolution[1]."' WHERE filename='".mysql_real_escape_string($fileName)."'";
							}
							mysql_query($query);
						}
					}
				}
			}
		}
	}
}
if (isset($_FILES['file'])) {
	$query = "SELECT * FROM files WHERE rt_number IS NULL";
	$blank_rt = mysql_query($query);
	while($row = mysql_fetch_array($blank_rt, MYSQL_ASSOC)) {
		$imdb_number = $row['imdb_number'];
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
					"release_theater='".mysql_real_escape_string($info[$imdb_number]['release_dates']['theater'])."', ".
					"release_dvd='".mysql_real_escape_string($info[$imdb_number]['release_dates']['dvd'])."', ".
					"consensus='".mysql_real_escape_string($info[$imdb_number]['critics_consensus'])."', ".
					"rating_critics=".mysql_real_escape_string($info[$imdb_number]['ratings']['critics_score']).", ".
					"rating_audience=".mysql_real_escape_string($info[$imdb_number]['ratings']['audience_score']).", ".
					"studio='".mysql_real_escape_string($info[$imdb_number]['studio'])."', ".
					"rt_link='".mysql_real_escape_string($info[$imdb_number]['links']['alternate'])."'");
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
			mysql_query ("UPDATE files SET rt_number=".mysql_real_escape_string($info[$imdb_number]['id'])." WHERE imdb_number=".$imdb_number);

			usleep(150000); // sleep .15 seconds to not go over our 10 requests per second limit
		}
	}

	$query = "SELECT * FROM files WHERE tmdb_number IS NULL";
	$blank_tmdb = mysql_query($query);
	while($row = mysql_fetch_array($blank_tmdb, MYSQL_ASSOC)) {
		$imdb_number = $row['imdb_number'];

		$imdbLookupURL="http://api.themoviedb.org/2.1/Movie.imdbLookup/en/json/";
		$infoURL="http://api.themoviedb.org/2.1/Movie.getInfo/en/json/";
		$tmdb_data = json_response($imdbLookupURL.TMDb_API."/tt".str_pad($imdb_number, 7, "0", STR_PAD_LEFT));


		if (isset($tmdb_data[0]['id'])) {				
			$tmdb_number = $tmdb_data[0]['id'];
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
						"revenue=".mysql_real_escape_string($tmdb_info[0]['revenue']));
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


				# now that we have all the data update the table
				mysql_query ("UPDATE files SET tmdb_number=".mysql_real_escape_string($tmdb_number)." WHERE imdb_number=".$imdb_number);
			}
		}
	}
	ob_end_clean();
	header("Location: index.php");
	exit;
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
mysql_close($db);
ob_end_flush();
?>
</body>
</html>
