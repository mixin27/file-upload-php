<?php

// Credit to: https://www.php.net/manual/en/features.file-upload.php
try {

  // Undefined | Multiple Files | $_FILES Corruption Attack
  // If this request falls under any of them, treat it invalid.
  if (
    !isset($_FILES['upfile']['error']) ||
    is_array($_FILES['upfile']['error'])
  ) {
    throw new RuntimeException('Invalid parameters.');
  }

  // Check $_FILES['upfile']['error'] value.
  switch ($_FILES['upfile']['error']) {
    case UPLOAD_ERR_OK:
      break;
    case UPLOAD_ERR_NO_FILE:
      throw new RuntimeException('No file sent.');
    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
      throw new RuntimeException('Exceeded filesize limit.');
    default:
      throw new RuntimeException('Unknown errors.');
  }

  // You should also check filesize here.
  if ($_FILES['upfile']['size'] > 4000000) {
    throw new RuntimeException('Exceeded filesize limit.');
  }

  // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
  // Check MIME Type by yourself.
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  if (false === $ext = array_search(
    $finfo->file($_FILES['upfile']['tmp_name']),
    array(
      'jpg' => 'image/jpeg',
      'png' => 'image/png',
      'gif' => 'image/gif',
    ),
    true
  )) {
    throw new RuntimeException('Invalid file format.');
  }

  $uploadPath = './uploads/';

  // check whether the user defined folder exist
  // If not, create a directory
  $userDefinedFolder = $_POST['upfolder'];
  if (!empty($userDefinedFolder)) {
    $dest_path = sprintf($uploadPath . '%s', $userDefinedFolder);
    if (!file_exists($dest_path)) {
      if (!mkdir($dest_path, 0777, true)) {
        throw new RuntimeException('Cannot create a directory');
      }
    }
  } else {
    // you can remove this throw exception,
    // if you want to upload file to default uploads folder.
    throw new RuntimeException('Folder name cannot be empty');
  }

  // You should name it uniquely.
  // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
  // On this example, obtain safe unique name from its binary data.
  if (!move_uploaded_file(
    $_FILES['upfile']['tmp_name'],
    sprintf(
      $uploadPath . '%s/%s.%s',
      $userDefinedFolder,
      sha1_file($_FILES['upfile']['tmp_name']),
      $ext
    )
  )) {
    throw new RuntimeException('Failed to move uploaded file.');
  }

  echo 'File is uploaded successfully.';
} catch (RuntimeException $e) {

  echo $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>File Upload</title>
</head>

<body>

  <form action="index.php" method="POST" enctype="multipart/form-data">
    <label for="folderName">Folder Name:</label>
    <input type="text" name="upfolder" id="folderName">
    <br />
    <input type="file" name="upfile" id="userImage">
    <br />
    <input type="submit" value="Start Upload" name="btnUpload">
  </form>

</body>

</html>