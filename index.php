<?php
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
	header("Location: install");
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

for($i = 0; $i <= $ColorSteps; $i++)
{
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

for($i = 0; $i <= $ColorSteps; $i++)
{
	$RGB['r'] = floor($FromRGB['r'] - ($StepRGB['r'] * $i));
	$RGB['g'] = floor($FromRGB['g'] - ($StepRGB['g'] * $i));
	$RGB['b'] = floor($FromRGB['b'] - ($StepRGB['b'] * $i));
	
	$HexRGB['mono']['r'] = sprintf('%02x', ($RGB['r']));
	$HexRGB['mono']['g'] = sprintf('%02x', ($RGB['g']));
	$HexRGB['mono']['b'] = sprintf('%02x', ($RGB['b']));
	
	$GradientColors['mono'][] = implode(NULL, $HexRGB['mono']);
}

$query = "SELECT * FROM files";
$files = mysql_query($query);
while ($row = mysql_fetch_array($files, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['files'][] = $row;
}

$query = "SELECT * FROM info_rt";
$info_rt = mysql_query($query);
while ($row = mysql_fetch_array($info_rt, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['info_rt'] = $row;
}

$query = "SELECT * FROM info_tmdb";
$info_tmdb = mysql_query($query);
while ($row = mysql_fetch_array($info_tmdb, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['info_tmdb'] = $row;
}

$query = "SELECT * FROM cast";
$cast = mysql_query($query);
while ($row = mysql_fetch_array($cast, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['cast'][] = $row;
}

$query = "SELECT * FROM director";
$director = mysql_query($query);
while ($row = mysql_fetch_array($director, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['director'][] = $row;
}

$query = "SELECT * FROM genre";
$genre = mysql_query($query);
while ($row = mysql_fetch_array($genre, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['genre'][] = $row;
}

$query = "SELECT * FROM producer";
$producer = mysql_query($query);
while ($row = mysql_fetch_array($producer, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['producer'][] = $row;
}

$query = "SELECT * FROM country";
$country = mysql_query($query);
while ($row = mysql_fetch_array($country, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['country'][] = $row;
}

mysql_close($db);

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
	if (isset($data['info_rt'])) {

		

			echo "\t<tr>\n";
			echo "\t\t<td class=\"year toggle\">";
			if (substr($data['info_rt']['release_theater'], 0, 4) > 0) {
				echo substr($data['info_rt']['release_theater'], 0, 4);
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
				echo htmlspecialchars($data['info_rt']['title']);
			}
			echo "</td>\n";
			echo "\t\t<td class=\"rating toggle\">";
			if (!is_null($data['info_rt']['mpaa']) && $data['info_rt']['mpaa'] != "Unrated") {
				echo "<span class=\"icon\">".$data['info_rt']['mpaa']."</span>";
			}
			echo "</td>\n";
			echo "\t\t<td class=\"resolution toggle\">";
			$res = 0;
			foreach ($data['files'] as $file) {
				if ($res < 3 && substr($file['resolution'], 0, 4) == "1080") {
					$res = 3;
				} elseif ($res < 2 && substr($file['resolution'], 0, 3) == "720") {
					$res = 2;
				} elseif ($res < 1 && $file['resolution'] == "SD") {
					$res = 1;
				}
			} if ($res > 0) {
				echo "<span class=\"icon\">";
				switch ($res) {
					case 3:
						echo "1080";
						break;
					
					case 2:
						echo "720";
						break;
					
					case 1:
						echo "SD";
						break;
				}
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
