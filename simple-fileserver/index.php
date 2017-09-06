<html>
<head>
<style>
  .invisible{
    display: none;
  }
  /*
  Fallback text if fontawesome not available
  https://stackoverflow.com/questions/23653708/fallback-from-fontawesome-if-font-download-blocked
  */
  [class*="fa-"]:before
  {
      content:"▼";
  }
</style>
<link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">
<script>
  function pressDelete(elem){
    elem.querySelector("input[type=submit]").click();
  }
</script>
<!--
<?php
	function human_filesize($bytes, $decimals=2){
		$size = ["B", "kB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];
		$factor = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$decimals}f %s", $bytes / pow(1024, $factor), @$size[$factor]);
  }

  file_put_contents("access.log", date("Y-m-d h:i:sa") . ": " . $_SERVER['REMOTE_ADDR'] . " " . $_GET["path"] . PHP_EOL, FILE_APPEND);

  // Pseudo MVC (for practice), separating out display and data logic
  // Would be better if separated out filesystem functions as the Model, but accesses are trivial enough to have native functions
  $view = [];
	$pwd = realpath("./files");
	$path = $pwd . htmlspecialchars($_GET["path"]);
  if ($pwd != substr(realpath($path), 0, strlen($pwd))){
    $view["folder_constraint"] = "<script>window.location.replace(\"index.php\");</script>";
  }
  $view["path"] = $path;

  $contents = scandir($path);
  foreach ($contents as $name){
    $newpath = realpath("${path}/${name}");
    $fspath = substr($newpath, strlen($pwd));
    $fileinfo = stat($newpath);
    $modtime = date("Y-m-d H:i:s", $fileinfo["mtime"]);
    if ($name === "."){
      continue;
    } else if (is_dir($newpath)){
      $view["dirs"][] = ["fspath" => $fspath, "name" => $name,
        "modtime" => $modtime];
    } else {
      $view["files"][] = ["fspath" => $fspath, "name" => $name,
        "size" => human_filesize($fileinfo["size"]), "modtime" => $modtime];
    }
  }
?>
-->
</head>
<body>
  <?php printf("<h1>%s</h1>", $view["path"]); ?>
  <h3>Upload File :</h3>
  <?php printf("<form action=\"upload.php?path=%s\" method=\"POST\" enctype=\"multipart/form-data\">", $view["path"]); ?>
    <input type="file" name="upload" />
    <input type="submit" />
  </form>
  <h3>Create Folder :</h3>
  <?php printf("<form action=\"mkdir.php?path=%s\" method=\"POST\" enctype=\"multipart/form-data\">", $view["path"]); ?>
    <input type="text" name="new_folder" />
    <input type="submit" />
  </form>
  <hr>
  <table>
    <tr><th>Name</th><th>Size</th><th>Last Modification</th><th>Delete</th></tr>
    <?php
      foreach ($view["dirs"] as $dir){
        if ($dir["name"] == ".."){
          $dir["name"] = "&lt;parent&gt;";
        }
    ?>
    <tr>
      <?php printf("<td><a href=\"./index.php?path=%s\">%s</a></td>", $dir["fspath"], $dir["name"]); ?>
      <td>&lt;dir&gt;</td>
      <?php printf("<td>%s</td>", $dir["modtime"]); ?>
      <td>--</td>
    </tr>
    <?php
      }
      foreach ($view["files"] as $file){
    ?>
    <tr>
      <td>
        <?php printf("<a href=\"files%s\"><i class=\"fa fa-download\" aria-hidden=\"true\"></i></a>&nbsp;&nbsp;", $file["fspath"]); ?>
        <?php printf("<a href=\"player.php?src=files%s\">%s</a>", $file["fspath"], $file["name"]); ?>
      </td>
      <?php printf("<td>%s</td>", $file["size"]); ?>
      <?php printf("<td>%s</td>", $file["modtime"]); ?>
      <td>
        <a href=\"javascript:void(0);\" onclick=\"pressDelete(this);\">[X]
          <?php printf("<form class=\"invisible\" action=\"delete.php?path=%s\" method=\"POST\" enctype=\"multipart/form-data\">", $view["path"]); ?>
            <?php printf("<input type=\"hidden\" name=\"to_delete\" value=\"%s\" />", $file["name"]); ?>
            <input type=\"submit\" value=\"[X]\" />
          </form>
        </a>
      </td>
    </tr>
    <?php
      }
    ?>
  </table>
</body>
</html>
