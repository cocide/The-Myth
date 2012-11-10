<?php
require_once("functions.php");
ob_start();
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<script type="text/javascript" src="js/jquery.js"></script> 
	<script type="text/javascript" src="js/jquery.tablesorter.js"></script>
	<script type="text/javascript">
		$(function() {
			$("#Movies").tablesorter();
	});	
	</script>	
	<script type="text/javascript">
		$(document).ready(function() {
			$("tr").children("td.studio, td.cost, td.producer, td.consensus, td.files, td.cast, td.overview").css({"display" : "block"});
			$("tr").children("td.studio, td.cost, td.producer, td.consensus, td.files, td.cast, td.overview").hide();
			
			$("td.toggle").click(function () { 
				$(this).parent("tr").children("td.detail").toggle();
			});
		});
	</script>
	<link rel="stylesheet" type="text/css" href="style.css" />
	<title>The Myth</title>
</head>
<body>';

if (!file_exists("conf.php")) {
	ob_end_clean();
	header("Location: $base_dir/install");
	exit;
}
ob_end_flush();
include("conf.php");
ini_set('display_errors', '0');
$db = mysql_connect(DB_HOST, DB_USER, DB_PASS);


if (!$db) {
	die('Could not connect: ' . mysql_error());
}
mysql_select_db(DATABASE);


$red = "9D1E15";
$green = "61CE3C";
$ColorSteps = "100";

#make the color gradient
$FromRGB['r'] = hexdec(substr($red, 0, 2));
$FromRGB['g'] = hexdec(substr($red, 2, 2));
$FromRGB['b'] = hexdec(substr($red, 4, 2));

$ToRGB['r'] = hexdec(substr($green, 0, 2));
$ToRGB['g'] = hexdec(substr($green, 2, 2));
$ToRGB['b'] = hexdec(substr($green, 4, 2));

$StepRGB['r'] = ($FromRGB['r'] - $ToRGB['r']) / ($ColorSteps);
$StepRGB['g'] = ($FromRGB['g'] - $ToRGB['g']) / ($ColorSteps);
$StepRGB['b'] = ($FromRGB['b'] - $ToRGB['b']) / ($ColorSteps);

$GradientColors['color'] = array();

for($i = 0; $i <= $ColorSteps; $i++) {
	$RGB['r'] = floor($FromRGB['r'] - ($StepRGB['r'] * $i));
	$RGB['g'] = floor($FromRGB['g'] - ($StepRGB['g'] * $i));
	$RGB['b'] = floor($FromRGB['b'] - ($StepRGB['b'] * $i));
	
	$HexRGB['color']['r'] = sprintf('%02x', ($RGB['r']));
	$HexRGB['color']['g'] = sprintf('%02x', ($RGB['g']));
	$HexRGB['color']['b'] = sprintf('%02x', ($RGB['b']));
	
	$GradientColors['color'][] = implode(NULL, $HexRGB['color']);
}



$dark = "444444";
$light = "999999";

#make the color gradient
$FromRGB['r'] = hexdec(substr($dark, 0, 2));
$FromRGB['g'] = hexdec(substr($dark, 2, 2));
$FromRGB['b'] = hexdec(substr($dark, 4, 2));

$ToRGB['r'] = hexdec(substr($light, 0, 2));
$ToRGB['g'] = hexdec(substr($light, 2, 2));
$ToRGB['b'] = hexdec(substr($light, 4, 2));

$StepRGB['r'] = ($FromRGB['r'] - $ToRGB['r']) / ($ColorSteps);
$StepRGB['g'] = ($FromRGB['g'] - $ToRGB['g']) / ($ColorSteps);
$StepRGB['b'] = ($FromRGB['b'] - $ToRGB['b']) / ($ColorSteps);

$GradientColors['mono'] = array();

for($i = 0; $i <= $ColorSteps; $i++) {
	$RGB['r'] = floor($FromRGB['r'] - ($StepRGB['r'] * $i));
	$RGB['g'] = floor($FromRGB['g'] - ($StepRGB['g'] * $i));
	$RGB['b'] = floor($FromRGB['b'] - ($StepRGB['b'] * $i));
	
	$HexRGB['mono']['r'] = sprintf('%02x', ($RGB['r']));
	$HexRGB['mono']['g'] = sprintf('%02x', ($RGB['g']));
	$HexRGB['mono']['b'] = sprintf('%02x', ($RGB['b']));
	
	$GradientColors['mono'][] = implode(NULL, $HexRGB['mono']);
}

$base_filter = return_filter("SELECT DISTINCT(imdb_number) FROM files");
$filter = $base_filter;

$time['min'] = 999;
$time['max'] = 0;

$year['min'] = 9999;
$year['max'] = 0;

$rating['critics']['min'] = 100;
$rating['critics']['max'] = 0;
$rating['audience']['min'] = 100;
$rating['audience']['max'] = 0;

// limit results based on our form
if (count($_POST) > 0) {
	if (isset($_POST['reset'])) {
		$_POST = array();
	}
	if (!empty($_POST['res'])) {
		switch ($_POST['res']) {
			case '1080':
				$filter = return_filter("SELECT DISTINCT(imdb_number) FROM files WHERE resolution LIKE '1080%' AND ".$filter);
				break;

			case '720':
				$filter = return_filter("SELECT DISTINCT(imdb_number) FROM files WHERE resolution LIKE '720%' AND ".$filter);
				break;

			case 'SD':
				$filter = return_filter("SELECT DISTINCT(imdb_number) FROM files WHERE resolution LIKE 'SD' AND ".$filter);
				break;

			case 'CAM':
				$filter = return_filter("SELECT DISTINCT(imdb_number) FROM files WHERE resolution LIKE 'CAM' AND ".$filter);
				break;
			
			default:
				$filter = return_filter("SELECT DISTINCT(imdb_number) FROM files WHERE resolution IS NULL AND ".$filter);
				break;
		}
	}

	if (!empty($_POST['genre'])) {
		$filter = return_filter("SELECT DISTINCT(imdb_number) FROM genre WHERE name='".mysql_real_escape_string(htmlspecialchars_decode($_POST['genre']))."' AND ".$filter);
	}

	if (!empty($_POST['celeb'])) {
		$filter = return_filter("SELECT DISTINCT(imdb_number) FROM cast WHERE rt_celeb_number=".mysql_real_escape_string(htmlspecialchars_decode($_POST['celeb']))." AND ".$filter);
	}

	if (!empty($_POST['director'])) {
		$filter = return_filter("SELECT DISTINCT(imdb_number) FROM director WHERE name='".mysql_real_escape_string(htmlspecialchars_decode(preg_replace('/([A-Za-z\-\']+), (.*)/', '\2 \1', $_POST['director'])))."' AND ".$filter);
	}

	if (!empty($_POST['producer'])) {
		$filter = return_filter("SELECT DISTINCT(imdb_number) FROM producer WHERE name='".mysql_real_escape_string(htmlspecialchars_decode(preg_replace('/([A-Za-z\-\']+), (.*)/', '\2 \1', $_POST['producer'])))."' AND ".$filter);
	}

	if (!empty($_POST['studio'])) {
		if ($_POST['studio'] == "Other") {
			$filter = return_filter("SELECT DISTINCT(imdb_number) FROM info_rt WHERE studio='' AND ".$filter);
		} else {
			$filter = return_filter("SELECT DISTINCT(imdb_number) FROM info_rt WHERE studio='".mysql_real_escape_string(htmlspecialchars_decode($_POST['studio']))."' AND ".$filter);
		}
	}

	if (!empty($_POST['country'])) {
		$filter = return_filter("SELECT DISTINCT(imdb_number) FROM country WHERE name='".mysql_real_escape_string(htmlspecialchars_decode($_POST['country']))."' AND ".$filter);
	}

	if (!empty($_POST['Tmin'])) {
		$filter = return_filter("SELECT DISTINCT(imdb_number) FROM info_rt WHERE runtime>=".mysql_real_escape_string(htmlspecialchars_decode($_POST['Tmin']))." AND ".$filter);
	}
	if (!empty($_POST['Tmax'])) {
		$filter = return_filter("SELECT DISTINCT(imdb_number) FROM info_rt WHERE runtime<=".mysql_real_escape_string(htmlspecialchars_decode($_POST['Tmax']))." AND ".$filter);
	}

	if (!empty($_POST['Ymin'])) {
		$filter = return_filter("SELECT DISTINCT(imdb_number) FROM info_rt WHERE year>=".mysql_real_escape_string(htmlspecialchars_decode($_POST['Ymin']))." AND ".$filter);
	}
	if (!empty($_POST['Ymax'])) {
		$filter = return_filter("SELECT DISTINCT(imdb_number) FROM info_rt WHERE year<=".mysql_real_escape_string(htmlspecialchars_decode($_POST['Ymax']))." AND ".$filter);
	}

	if (!empty($_POST['Cmin'])) {
		$filter = return_filter("SELECT DISTINCT(imdb_number) FROM info_rt WHERE rating_critics>=".mysql_real_escape_string(htmlspecialchars_decode($_POST['Cmin']))." AND ".$filter);
	}
	if (!empty($_POST['Cmax'])) {
		$filter = return_filter("SELECT DISTINCT(imdb_number) FROM info_rt WHERE rating_critics<=".mysql_real_escape_string(htmlspecialchars_decode($_POST['Cmax']))." AND ".$filter);
	}
	if (!empty($_POST['Amin'])) {
		$filter = return_filter("SELECT DISTINCT(imdb_number) FROM info_rt WHERE rating_audience>=".mysql_real_escape_string(htmlspecialchars_decode($_POST['Amin']))." AND ".$filter);
	}
	if (!empty($_POST['Amax'])) {
		$filter = return_filter("SELECT DISTINCT(imdb_number) FROM info_rt WHERE rating_audience<=".mysql_real_escape_string(htmlspecialchars_decode($_POST['Amax']))." AND ".$filter);
	}
}

$query = "SELECT * FROM files, id_asoc WHERE id_asoc.imdb_number = files.imdb_number AND ".str_replace('imdb_number', 'files.imdb_number', $filter);
$files = mysql_query($query);
while ($row = mysql_fetch_array($files, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['files'][] = $row;

	if (substr($row['resolution'], 0, 4) == "1080") {
		$name = "1080";
	} elseif (substr($row['resolution'], 0, 3) == "720") {
		$name = "720";
	} elseif ($row['resolution'] == "SD") {
		$name = "SD";
	} elseif ($row['resolution'] == "CAM") {
		$name = "CAM";
	} else {
		$name = "Other";
	}
	if (isset($resolutions[$name])) {
		$resolutions[$name]++;
	} else {
		$resolutions[$name] = 1;
	}
	if ($name != "Other") {
		if (!isset($movie_data[$row['imdb_number']]['maxres'])) {
			$movie_data[$row['imdb_number']]['maxres'] = $name;
		} elseif ($movie_data[$row['imdb_number']]['maxres'] == "CAM" && ($name == "1080" || $name == "720" || $name == "SD")) {
			//if were set to CAM and we have better
			$movie_data[$row['imdb_number']]['maxres'] = $name;
		} elseif ($movie_data[$row['imdb_number']]['maxres'] == "SD" && ($name == "1080" || $name == "720")) {
			//if were set to SD and we have better
			$movie_data[$row['imdb_number']]['maxres'] = $name;
		} elseif ($movie_data[$row['imdb_number']]['maxres'] == "720" && $name == "1080") {
			//if were set to 720 and we have 1080
			$movie_data[$row['imdb_number']]['maxres'] = $name;
		}
	}
}

$query = "SELECT * FROM info_rt WHERE ".$filter;
$info_rt = mysql_query($query);
while ($row = mysql_fetch_array($info_rt, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['info_rt'] = $row;
	$name = $row['studio'];
	if (empty($name)) {
		$name = "Other";
	}
	if (isset($studios[$name])) {
		$studios[$name]++;
	} else {
		$studios[$name] = 1;
	}
	if ($row['runtime'] > $time['max']) {
		$time['max'] = $row['runtime'];
	}
	if ($row['runtime'] < $time['min']) {
		$time['min'] = $row['runtime'];
	}

	if ($row['year'] > $year['max']) {
		$year['max'] = $row['year'];
	}
	if ($row['year'] > 0 && $row['year'] < $year['min']) {
		$year['min'] = $row['year'];
	}

	if ($row['rating_critics'] > $rating['critics']['max']) {
		$rating['critics']['max'] = $row['rating_critics'];
	}
	if ($row['rating_critics'] >= 0 && $row['rating_critics'] < $rating['critics']['min']) {
		$rating['critics']['min'] = $row['rating_critics'];
	}
	if ($row['rating_audience'] > $rating['audience']['max']) {
		$rating['audience']['max'] = $row['rating_audience'];
	}
	if ($row['rating_audience'] >= 0 && $row['rating_audience'] < $rating['audience']['min']) {
		$rating['audience']['min'] = $row['rating_audience'];
	}
}

$query = "SELECT * FROM info_tmdb WHERE ".$filter;
$info_tmdb = mysql_query($query);
while ($row = mysql_fetch_array($info_tmdb, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['info_tmdb'] = $row;
}

$query = "SELECT * FROM cast WHERE ".$filter;
$cast = mysql_query($query);
while ($row = mysql_fetch_array($cast, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['cast'][] = $row;
	$name = preg_replace('/(.*) ([A-Za-z\-\']+)/', '\2, \1', $row['name'])."_".$row['rt_celeb_number'];
	if (isset($celebs[$name])) {
		$celebs[$name]++;
	} else {
		$celebs[$name] = 1;
	}
}

$query = "SELECT * FROM director WHERE ".$filter;
$director = mysql_query($query);
while ($row = mysql_fetch_array($director, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['director'][] = $row;
	$name = preg_replace('/(.*) ([A-Za-z\-\']+)/', '\2, \1', $row['name']);
	if (isset($directors[$name])) {
		$directors[$name]++;
	} else {
		$directors[$name] = 1;
	}
}

$query = "SELECT * FROM genre WHERE ".$filter;
$genre = mysql_query($query);
while ($row = mysql_fetch_array($genre, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['genre'][] = $row;
	$name = $row['name'];
	if (isset($genres[$name])) {
		$genres[$name]++;
	} else {
		$genres[$name] = 1;
	}
}

$query = "SELECT * FROM producer WHERE ".$filter;
$producer = mysql_query($query);
while ($row = mysql_fetch_array($producer, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['producer'][] = $row;
	$name = preg_replace('/(.*) ([A-Za-z\-\']+)/', '\2, \1', $row['name']);
	if (isset($producers[$name])) {
		$producers[$name]++;
	} else {
		$producers[$name] = 1;
	}
}

$query = "SELECT * FROM country WHERE ".$filter;
$country = mysql_query($query);
while ($row = mysql_fetch_array($country, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['country'][] = $row;
	$name = $row['name'];
	if (isset($countries[$name])) {
		$countries[$name]++;
	} else {
		$countries[$name] = 1;
	}
}

mysql_close($db);
echo "<form action=\"$base_dir\" method=\"post\">";


echo "Quality: <select name=\"res\">";
echo "<option label=\"Any\"></option>";
ksort($resolutions, SORT_STRING);
foreach ($resolutions as $resolution => $count) {
	echo "<option label=\"".htmlspecialchars("$resolution ($count)")."\"";
	if (htmlspecialchars_decode($_POST['res']) == $resolution) {
		echo " selected=\"selected\"";
	}
	echo ">".htmlspecialchars("$resolution")."</option>";
}
echo "</select>";


echo "Genre: <select name=\"genre\">";
echo "<option label=\"Any\"></option>";
ksort($genres, SORT_STRING);
foreach ($genres as $genre => $count) {
	echo "<option label=\"".htmlspecialchars("$genre ($count)")."\"";
	if (htmlspecialchars_decode($_POST['genre']) == $genre) {
		echo " selected=\"selected\"";
	}
	echo ">".htmlspecialchars("$genre")."</option>";
}
echo "</select>";


echo "Cast: <select name=\"celeb\">";
echo "<option label=\"Any\"></option>";
ksort($celebs, SORT_STRING);
foreach ($celebs as $person => $count) {
	$name = preg_replace('/(.*)_([0-9]+)/', '\1', $person);
	$rt_celeb_number = preg_replace('/(.*)_([0-9]+)/', '\2', $person);
	echo "<option label=\"".htmlspecialchars("$name ($count)")."\"";
	if (htmlspecialchars_decode($_POST['celeb']) == $rt_celeb_number) {
		echo " selected=\"selected\"";
	}
	echo ">".htmlspecialchars("$rt_celeb_number")."</option>";
}
echo "</select>";
echo "<br />";


echo "Director: <select name=\"director\">";
echo "<option label=\"Any\"></option>";
ksort($directors, SORT_STRING);
foreach ($directors as $person => $count) {
	echo "<option label=\"".htmlspecialchars("$person ($count)")."\"";
	if (htmlspecialchars_decode($_POST['director']) == $person) {
		echo " selected=\"selected\"";
	}
	echo ">".htmlspecialchars("$person")."</option>";
}
echo "</select>";


echo "Producer: <select name=\"producer\">";
echo "<option label=\"Any\"></option>";
ksort($producers, SORT_STRING);
foreach ($producers as $person => $count) {
	echo "<option label=\"".htmlspecialchars("$person ($count)")."\"";
	if (htmlspecialchars_decode($_POST['producer']) == $person) {
		echo " selected=\"selected\"";
	}
	echo ">".htmlspecialchars("$person")."</option>";
}
echo "</select>";
echo "<br />";


echo "Studio: <select name=\"studio\">";
echo "<option label=\"Any\"></option>";
ksort($studios, SORT_STRING);
foreach ($studios as $studio => $count) {
	echo "<option label=\"".htmlspecialchars("$studio ($count)")."\"";
	if (htmlspecialchars_decode($_POST['studio']) == $studio) {
		echo " selected=\"selected\"";
	}
	echo ">".htmlspecialchars("$studio")."</option>";
}
echo "</select>";


echo "Country: <select name=\"country\">";
echo "<option label=\"Any\"></option>";
ksort($countries, SORT_STRING);
foreach ($countries as $country => $count) {
	echo "<option label=\"".htmlspecialchars("$country ($count)")."\"";
	if (htmlspecialchars_decode($_POST['country']) == $country) {
		echo " selected=\"selected\"";
	}
	echo ">".htmlspecialchars("$country")."</option>";
}
echo "</select>";
echo "<br />";


echo "Runtime: ";
echo "<input type=\"text\" name=\"Tmin\" maxlength=\"3\" size=\"3\" value=\"".$time['min']."\">";
echo " - ";
echo "<input type=\"text\" name=\"Tmax\" maxlength=\"3\" size=\"3\" value=\"".$time['max']."\">";
echo "Year: ";
echo "<input type=\"text\" name=\"Ymin\" maxlength=\"4\" size=\"3\" value=\"".$year['min']."\">";
echo " - ";
echo "<input type=\"text\" name=\"Ymax\" maxlength=\"4\" size=\"3\" value=\"".$year['max']."\">";

echo "Critics: ";
echo "<input type=\"text\" name=\"Cmin\" maxlength=\"4\" size=\"3\" value=\"".$rating['critics']['min']."\">";
echo " - ";
echo "<input type=\"text\" name=\"Cmax\" maxlength=\"4\" size=\"3\" value=\"".$rating['critics']['max']."\">";
echo "Audience: ";
echo "<input type=\"text\" name=\"Amin\" maxlength=\"4\" size=\"3\" value=\"".$rating['audience']['min']."\">";
echo " - ";
echo "<input type=\"text\" name=\"Amax\" maxlength=\"4\" size=\"3\" value=\"".$rating['audience']['max']."\">";
echo "<br />";


echo "<input type=\"submit\" value=\"Filter\" />";
if ($filter != $base_filter) {
	echo "<input type=\"submit\" name=\"reset\" value=\"Clear\" />";
}
echo "</form>";

#make table headers
echo "\n\t<table id=\"Movies\" border=\"2\">\n";
echo "\t<thead>\n";
echo "\t<tr>\n";
echo "\t\t<th class=\"year\">Year</th>\n";
echo "\t\t<th class=\"popularity\">Critics</th>\n";
echo "\t\t<th class=\"popularity\">Audience</th>\n";
echo "\t\t<th class=\"title\">Title</th>\n";
echo "\t\t<th class=\"rating\">Rating</th>\n";
echo "\t\t<th class=\"resolution\">Quality</th>\n";
echo "\t\t<th class=\"genre\">Genre</th>\n";
echo "\t\t<th class=\"director\">Director</th>\n";
echo "\t\t<th class=\"detail studio\">Studio</th>\n";
echo "\t\t<th class=\"detail producer\">Producer</th>\n";
echo "\t\t<th class=\"detail consensus\">Consensus</th>\n";
echo "\t\t<th class=\"detail time\">Runtime</th>\n";
echo "\t\t<th class=\"detail imdb\">IMDB Number</th>\n";
echo "\t\t<th class=\"detail tmdb\">RT Number</th>\n";
echo "\t\t<th class=\"detail tmdb\">TMDB Number</th>\n";
echo "\t\t<th class=\"detail tagline\">Tagline</th>\n";
echo "\t\t<th class=\"detail overview\">Overview</th>\n";
echo "\t\t<th class=\"detail cast\">Cast</th>\n";
echo "\t\t<th class=\"detail files\">Files</th>\n";
echo "\t</tr>\n";
echo "\t</thead>\n";
echo "\t<tbody>\n";

foreach ($movie_data as $imdb_number => $data) {
	if (isset($data['info_rt']) && isset($data['info_tmdb']) && count($data['files']) > 0) {
		echo "\t<tr>\n";
		echo "\t\t<td class=\"year toggle\">";
		if ($data['info_rt']['year'] > 0) {
			echo $data['info_rt']['year'];
		} else {
			echo "<span class=\"missing\">&mdash;&mdash;</span>";
		}
		echo "</td>\n";
		echo "\t\t<td class=\"popularity toggle\">";
		if ($data['info_rt']['rating_critics'] > 0) {
			echo "<span style=\"color:#".$GradientColors['color'][$data['info_rt']['rating_critics']]."\">".$data['info_rt']['rating_critics']."</span>";
		} else {
			echo "<span class=\"missing\">&mdash;</span>";
		}
		echo "</td>\n";
		echo "\t\t<td class=\"popularity toggle\">";
		if ($data['info_rt']['rating_audience'] > 0) {
			echo "<span style=\"color:#".$GradientColors['mono'][$data['info_rt']['rating_audience']]."\">".$data['info_rt']['rating_audience']."</span>";
		} else {
			echo "<span class=\"missing\">&mdash;</span>";
		}
		echo "</td>\n";
		echo "\t\t<td class=\"title toggle\">";
		if (isset($data['info_rt']['title'])) {
			echo htmlspecialchars(preg_replace('/(.*)( 3D)/', '\1', $data['info_rt']['title']));
		}
		echo "</td>\n";
		echo "\t\t<td class=\"rating toggle\">";
		if (!is_null($data['info_rt']['mpaa']) && $data['info_rt']['mpaa'] != "Unrated") {
			echo "<span class=\"icon\">".$data['info_rt']['mpaa']."</span>";
		}
		echo "</td>\n";
		echo "\t\t<td class=\"resolution toggle\">";
		if (isset($data['maxres'])) {
			echo "<span class=\"icon\">".$data['maxres']."</span>";
		}
		echo "</td>\n";
		echo "\t\t<td class=\"genre toggle\">";
		if (isset($data['genre']) && count($data['genre']) > 0) {
			$previous = false;
			foreach ($data['genre'] as $genre) {
				if ($previous) {
					echo ", ";
				} else {
					$previous = true;
				}
				echo htmlspecialchars($genre['name']);
			}
		}
		echo "</td>\n";
		echo "\t\t<td class=\"director toggle\">";
		if (isset($data['director']) && count($data['director']) > 0) {
			$previous = false;
			foreach ($data['director'] as $director) {
				if ($previous) {
					echo ", ";
				} else {
					$previous = true;
				}
				echo htmlspecialchars($director['name']);
			}
		}
		echo "</td>\n";
		echo "\t\t<td class=\"detail studio";
		if (empty($data['info_rt']['studio'])) {
			echo " shrink";
		}
		echo "\">";
		if (!empty($data['info_rt']['studio'])) {
			echo "Studio: ".htmlspecialchars($data['info_rt']['studio']);
		}
		echo "</td>\n";
		echo "\t\t<td class=\"detail producer";
		if (!isset($data['producer'])) {
			echo " shrink";
		}
		echo "\">";
		if (isset($data['producer']) && count($data['producer']) > 0 ) {
			echo "Producer";
			if (count($data['producer']) > 1) {
				echo "s";
			}
			echo ": ";
			$previous = false;
			foreach ($data['producer'] as $producer) {
				if ($previous) {
					echo ", ";
				} else {
					$previous = true;
				}
				echo htmlspecialchars($producer['name']);
			}
		}
		echo "</td>\n";
		echo "\t\t<td class=\"detail consensus";
		if (empty($data['info_rt']['consensus'])) {
			echo " shrink";
		}
		echo "\">";
		if (isset($data['info_rt']['consensus'])) {
			echo htmlspecialchars($data['info_rt']['consensus']);
		}
		echo "</td>\n";
		echo "\t\t<td class=\"detail time\">".$data['info_rt']['runtime']." min</td>\n";
		echo "\t\t<td class=\"detail imdb\"><a target=\"_blank\" href=\"http://www.imdb.com/title/tt".str_pad($data['files'][0]['imdb_number'], 7, "0", STR_PAD_LEFT)."/\">IMDb</a></td>\n";
		echo "\t\t<td class=\"detail rt\"><a target=\"_blank\" href=\"".$data['info_rt']['rt_link']."\">RT</a></td>\n";
		echo "\t\t<td class=\"detail tmdb\"><a target=\"_blank\" href=\"http://www.themoviedb.org/movie/".$data['files'][0]['tmdb_number']."\">TMDb</a></td>\n";
		echo "\t\t<td class=\"detail cost";
		if (empty($data['info_tmdb']['budget']) && empty($data['info_tmdb']['revenue'])) {
			echo " shrink";
		}
		echo "\">";
		if (!empty($data['info_tmdb']['budget'])) {
			echo "Budget: ".money_format("%.0n", $data['info_tmdb']['budget'])."<br />";
		}
		if (!empty($data['info_tmdb']['revenue'])) {
			echo "Box: ".money_format("%.0n", $data['info_tmdb']['revenue']);
		}
		echo "</td>\n";
		echo "\t\t<td class=\"detail files\"><ul>";
		foreach ($data['files'] as $file) {
			echo "<li>";
			if (!empty($file['resolution'])) {
				echo "<span class=\"icon\">".$file['resolution']."</span>";
			}
			echo htmlspecialchars($file['filename'])."</li>";
		}
		echo "</ul></td>\n";
		echo "\t\t<td class=\"detail tagline";
		if (empty($data['info_tmdb']['tagline'])) {
			echo " shrink";
		}
		echo "\">";
		if (isset($data['info_tmdb']['tagline'])) {
			echo htmlspecialchars($data['info_tmdb']['tagline']);
		}
		echo "</td>\n";
		echo "\t\t<td class=\"detail overview";
		if (empty($data['info_tmdb']['overview'])) {
			echo " shrink";
		}
		echo "\">";
		if (isset($data['info_tmdb']['overview'])) {
			echo htmlspecialchars($data['info_tmdb']['overview']);
		}
		echo "</td>\n";
		echo "\t\t<td class=\"detail cast";
		if (!isset($data['cast'])) {
			echo " shrink";
		}
		echo "\">";
		if (isset($data['cast']) && count($data['cast']) > 0) {
			echo "<ul>";
			foreach ($data['cast'] as $cast) {
				echo "<li><a target=\"_blank\" href=\"http://www.rottentomatoes.com/celebrity/".$cast['rt_celeb_number']."/\">".htmlspecialchars($cast['name'])." (".htmlspecialchars($cast['role']).")</a></li>";
			}
			echo "</ul>";
		}
		echo "</td>\n";
		echo "\t\t<td class=\"detail country";
		if (!isset($data['country'])) {
			echo " shrink";
		}
		echo "\">";
		if (isset($data['country']) && count($data['country']) > 0) {
			echo "<ul>";
			foreach ($data['country'] as $country) {
				echo "<li>".htmlspecialchars($country['name'])."</li>";
			}
			echo "</ul>";
		}
		echo "</td>\n";
		echo "\t</tr>\n";
	}

}
echo "</tbody>\n";
echo "</table>\n";
	
?>
</body>
</html>
