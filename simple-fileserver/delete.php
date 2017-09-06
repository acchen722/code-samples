<html>
<head>
  <?php
    $filename = $_POST["to_delete"];
    $browser_folder = $_GET["path"];

    $pwd = realpath("./files");
    $src_folder = realpath($browser_folder);
    $src_file = "${src_folder}/${filename}";
    if ($src_folder === "${pwd}/recycle_bin"){
      unlink($src_file);
    } else {
      $dest_folder = "${pwd}/recycle_bin";
      $new_file = "${dest_folder}/${filename}";
      while (file_exists($new_file)){
        $new_file .= "#";
      }
      rename($src_file, $new_file);
    }
  ?>
</head>
<body>
  <?php
    $browserpath = substr($browser_folder, strlen($pwd));
    printf("<script>window.location.replace(\"index.php?path=%s\");</script>", $browserpath);
  ?>
</body>
</html>
