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

  .item_size{
    text-align: center;
  }
  .item_move{
    text-align: center;
  }
  .item_delete{
    text-align: center;
  }
</style>
<link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">
<script>
  function submitForm(elem){
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
  $view["rawpath"] = $_GET["path"];
  $view["path"] = $path;
  $view["title"] = $path;
  $parent = [];
  $dirs = [];
  $files = [];
  $contents = scandir($path);
  foreach ($contents as $name){
    $newpath = realpath("${path}/${name}");
    $fspath = substr($newpath, strlen($pwd));
    $fileinfo = stat($newpath);
    $modtime = date("Y-m-d H:i:s", $fileinfo["mtime"]);
    if ($name === "."){
      continue;
    } if ($name === ".."){
      $parent = ["type" => "parent", "fspath" => $fspath, "name" => "&lt;parent&gt;",
        "modtime" => $modtime];
    } else if (is_dir($newpath)){
      $dirs[] = ["type" => "dir", "fspath" => $fspath, "name" => $name,
        "modtime" => $modtime];
    } else {
      $files[] = ["type" => "file", "fspath" => $fspath, "name" => $name,
        "size" => human_filesize($fileinfo["size"]), "modtime" => $modtime];
    }
  }
  $view["items"] = array_merge([$parent], $dirs, $files);
  $view["is_moving"] = $_POST["src"] !== null;
  $view["move_src"] = $_POST["src"];
  $view["move_src_name"] = basename($_POST["src"]);
?>
-->
<?php printf("<title>%s</title>", $view["title"]); ?>
</head>
<body>
  <?php printf("<h1>%s</h1>", $view["path"]); ?>
  <?php
    if ($view["is_moving"]){
      printf("<form action=\"mv.php?path=%s\" method=\"POST\" enctype=\"multipart/form-data\">", $view["path"]);
      printf("  <input type=\"hidden\" name=\"src\" value=\"%s\" />", $view["move_src"]);
      printf("  <h2>Moving: %s ", $view["move_src"]);
      printf("  <a href=\"javascript:void(0)\" onclick=\"submitForm(this)\">⇒");
      printf("    <input class=\"invisible\" type=\"submit\" /></a> ");
      printf("  %s/<input type=\"text\" name=\"new_name\" value=\"%s\" /></h2></form>",
        $view["path"], $view["move_src_name"]);
    }
  ?>
  <table id="items_table">
    <tr><th>Name</th><th>Size</th><th>Last Modification</th><th>Move/Rename</th><th>Delete</th></tr>
    <?php
      foreach ($view["items"] as $item){
    ?>
    <tr class="item_row">
      <td class="item_name"><?php // Name
        switch($item["type"]){
          case "parent":
          case "dir":
            if ($view["is_moving"]){
              printf("<a href=\"javascript:void(0);\" onclick=\"submitForm(this);\">%s", $item["name"]);
              printf("  <form class=\"invisible\" action=\"index.php?path=%s\" method=\"POST\" enctype=\"multipart/form-data\">", $item["fspath"]);
              printf("  <input type=\"hidden\" name=\"src\" value=\"%s\" />", $view["move_src"]);
              printf("  <input type=\"submit\" value=\"[>]\" /></form></a>");
            } else {
              printf("<a href=\"./index.php?path=%s\">%s</a>",
                $item["fspath"], $item["name"]);
            }
            break;
          case "file":
            printf("<a href=\"files%s\"><i class=\"fa fa-download\""
              . " aria-hidden=\"true\"></i></a>&nbsp;&nbsp;", $item["fspath"]);
            printf("<a href=\"player.php?src=files%s\">%s</a>",
              $item["fspath"], $item["name"]);
            break;
        }
      ?></td>
      <td class="item_size"><?php // Size
        switch($item["type"]){
          case "parent":
          case "dir":
            printf("&lt;dir&gt;");
            break;
          case "file":
            printf($item["size"]);
            break;
        }
      ?></td>
      <td class="item_modtime"><?php // Last Modification
        printf($item["modtime"]);
      ?></td>
      <td class="item_move"><?php // Move
        switch($item["type"]){
          case "parent":
            printf("--");
            break;
          case "dir":
          case "file":
      ?>
        <a href="javascript:void(0);" onclick="submitForm(this);">[>]
          <?php printf("<form class=\"invisible\" action=\"index.php?path=%s\" method=\"POST\" enctype=\"multipart/form-data\">", $view["rawpath"]); ?>
            <?php printf("<input type=\"hidden\" name=\"src\" value=\"%s/%s\" />", $view["path"], $item["name"]); ?>
            <input type="submit" value="[>]" />
          </form>
        </a>
      <?php
            break;
        }
      ?></td>
      <td class="item_delete"><?php // Delete
        switch($item["type"]){
          case "parent":
          case "dir":
            printf("--");
            break;
          case "file":
      ?>
        <a href="javascript:void(0);" onclick="submitForm(this);">[X]
          <?php printf("<form class=\"invisible\" action=\"delete.php?path=%s\" method=\"POST\" enctype=\"multipart/form-data\">", $view["path"]); ?>
            <?php printf("<input type=\"hidden\" name=\"to_delete\" value=\"%s\" />", $item["name"]); ?>
            <input type="submit" value="[X]" />
          </form>
        </a>
      <?php
            break;
        }
      ?></td>
    </tr>
    <?php
      }
    ?>
  </table>
  <hr>
  <h3>Upload File :</h3>
  <?php printf("<form autocomplete=\"off\" action=\"upload.php?path=%s\" method=\"POST\" enctype=\"multipart/form-data\">", $view["path"]); ?>
    <input type="file" name="upload" />
    <input type="submit" />
  </form>
  <h3>Create Folder :</h3>
  <?php printf("<form autocomplete=\"off\" action=\"mkdir.php?path=%s\" method=\"POST\" enctype=\"multipart/form-data\">", $view["path"]); ?>
    <input type="text" name="new_folder" />
    <input type="submit" />
  </form>
</body>
</html>
