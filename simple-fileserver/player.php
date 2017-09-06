<!DOCTYPE html>
<html lang="en">
<head>
	<!-- For current functions, not necessary, but included for future extensibility -->
    <link href="videojs/video-js.css" rel="stylesheet">
    <script src="videojs/video.js"></script>
<?php
	$view = [];
	
	$video_file = $_GET["src"];
	$video_name = basename($video_file);
  $current_folder = dirname($video_file);
  // VIEW: Current folder
  $view["fspath"] = $current_folder;
	$tracks_file = $current_folder . "/tracks.json";
	// VIEW: Video filepath
	$view["source"] = $video_file;
	
	// VIEW: Page title
	$view["title"] = $video_name;
	
	if (file_exists(realpath($tracks_file))){
		$tracks = json_decode(file_get_contents(realpath($tracks_file)), true);
		if (array_key_exists($video_name, $tracks)){
			// VIEW: Caption tracks
			$view["tracks"] = [];
			if (array_key_exists("subtitles", $tracks[$video_name])){
				foreach ($tracks[$video_name]["subtitles"] as $sub_lang => $sub_data){
					$sub_file = $sub_data["file"];
          $sub_label = $sub_data["label"];
          $view["tracks"][] = ["name" => $sub_file, "lang" => $sub_lang, "label" => $sub_label];
//					$view["tracks"][] = "<track kind='captions' src=\"${current_folder}/${sub_file}\" srclang=\"${sub_lang}\" label=\"${sub_label}\" />";
				}
			}
			if (array_key_exists("audio", $tracks[$video_name])){
			}
		}
	}
?>
<?php printf("<title>%s</title>", $view["title"]); ?>

</head>
<body>

  <video id="main_video" class="video-js vjs-default-skin vjs-big-play-centered"
  controls preload="metadata" width="640" height="264" data-setup="{}">
  <?php printf("<source src=\"%s\" type=\"video/webm\" >", $view["source"]); ?>
  <?php foreach ($view["tracks"] as $track) { ?>
  <?php
    printf("<track kind=\"captions\" src=\"%s/%s\" srclang=\"%s\" label=\"%s\" />",
      $view["fspath"], $track["name"], $track["lang"], $track["label"]);
  ?>
	<?php } ?>
  </video>

</body>

</html>
