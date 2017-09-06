<html>
<head>
  <?php
    $fldrname = $_POST["new_folder"];
    $browser_folder = $_GET["path"];

    $pwd = realpath("./files");
    $src_folder = realpath($browser_folder);
    $new_folder_path = sprintf("%s/%s", $src_folder, $fldrname);
    if ($src_folder !== realpath("files/recycle_bin")){
      mkdir($new_folder_path);
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
