<?php
require_once("functions.php");
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
	header("Location: $base_dir/install");
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
				$query = "SELECT * FROM id_asoc WHERE imdb_number='".mysql_real_escape_string($imdb_number)."'";
				$result = mysql_query($query);
				# and only if its not in the
				if (mysql_num_rows($result) == 0) {
					$query = "INSERT INTO id_asoc SET imdb_number=".mysql_real_escape_string($imdb_number);
					mysql_query($query);
				}
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
							preg_match('/[^\[]*\[[0-9]{4}-[0-9]{7}.*((720[ip]*)|(1080[ip]*)|(D[vV]D)|(R[56]))/', $fileName, $resolution);
							if ($resolution[1] == "DvD" || $resolution[1] == "DVD") {
								$resolution[1] = "SD";
							} elseif ($resolution[1] == "R5" || $resolution[1] == "R6") {
								$resolution[1] = "CAM";
							} 
							if (count($resolution) > 1) {
								$query = "UPDATE files SET resolution='".$resolution[1]."' WHERE filename='".mysql_real_escape_string($fileName)."'";
								mysql_query($query);
							}
						}
					}
				}
			}
		}
	}
}
if (isset($_FILES['file'])) {
	$query = "SELECT * FROM files, id_asoc WHERE files.imdb_number = id_asoc.imdb_number AND id_asoc.rt_number IS NULL";
	$blank_rt = mysql_query($query);
	while($row = mysql_fetch_array($blank_rt, MYSQL_ASSOC)) {
		rt_getData($row['imdb_number']);
	}

	$query = "SELECT * FROM files, id_asoc WHERE files.imdb_number = id_asoc.imdb_number AND id_asoc.tmdb_number IS NULL";
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
	ob_end_clean();
	header("Location: $base_dir");
	exit;
}

mysql_close($db);
ob_end_flush();
?>
</body>
</html>
