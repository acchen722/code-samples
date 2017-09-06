<html>
<head>
  <?php

    $filename = $_FILES["upload"]["name"];
    $dest_folder = $_GET["path"];
    $pwd = realpath("./files");
    
    if ($pwd != substr(realpath($dest_folder), 0, strlen($pwd))){
      $dest_folder = $pwd;
    }
    $new_file = sprintf("%s/%s", realpath($dest_folder), $filename);
    while (file_exists($new_file)){
      $new_file .= "#";
    }
    
    
    move_uploaded_file($_FILES["upload"]["tmp_name"], $new_file);

  ?>
</head>
<body>
  <?php
    //    printf("<p>Uploaded '%s':<br> %s -> %s</p>", $filename, $_FILES["upload"]["tmp_name"], $new_file);
    $browserpath = substr($dest_folder, strlen($pwd));
    printf("<script>window.location.replace(\"index.php?path=%s\");</script>", $browserpath);
  ?>
</body>
</html>
