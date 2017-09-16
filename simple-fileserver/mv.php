<html>
<head>
  <!--
  <?php
    $src_item = $_POST["src"];
    $browser_folder = $_GET["path"];
    $dst_name = $_POST["new_name"];

    $pwd = realpath("./files");
    $src_folder = realpath($browser_folder);
    $new_item = sprintf("%s/%s", $src_folder, $dst_name);

//    printf("%s -> %s", $src_item, $new_item);
    rename($src_item, $new_item);
  ?>
  -->
</head>
<body>
  <?php
    $browserpath = substr($browser_folder, strlen($pwd));
    printf("<script>window.location.replace(\"index.php?path=%s\");</script>", $browserpath);
  ?>
</body>
</html>
