<?php
namespace Ilovepdf;
require_once  './ilovepdf-php/init.php';
$n = 0;

$usrip = $_SERVER["REMOTE_ADDR"];
$pth = "c:/sites/pdfuploads/".str_replace(":", "-", $usrip)."/";

is_dir($pth)?  : mkdir($pth, 0777, true);

// Generate array of uploaded files and file info

$files = [];
function generate($parent, $file_nos) {
  global $files;
  $arr = [];
  $keys = array_keys($parent);
  for($i=0; $i<$file_nos; $i++) {
    foreach($keys as $key) {
      $arr[$key] = $parent[$key][$i];
    }
    array_push($files, $arr);
  }
  return $files;
}
$files = generate($_FILES["pdf"], sizeof($_FILES["pdf"]["name"]));
// print_r($files);

// Get destination file path (path to save file) and save uploaded file

$dest_file;
$temp_file;
$save_path_array = [];
foreach($files as $file) {
  if (file_exists($pth.basename($file["name"]))) {
    // If file exists add random number to the begining of file name before save
    $filename = basename($file['name']);
    $num = rand(1001, 9999);
    $dest_file = $pth.$num.$filename;
    array_push($save_path_array, $dest_file);

  }
  else {
    // If file does not exist save with original filename
    $dest_file = $pth.basename($file['name']);

  }
  $temp_file = $file['tmp_name'];
  try {
    move_uploaded_file($temp_file, $dest_file);
    // print_r("File uploaded successfully");
  }
  catch(Execption $e) {
    echo($e->getMessage());
  }
}

$dload_pth = "./processed_files/".str_replace(":", "-", $usrip)."/compressed/";
$file_n = "";
if (!file_exists($dload_pth)) mkdir($dload_pth, 0777, true);
$ilovepdf=new Ilovepdf('project_public_ee0f6b6af466b23bb3bade9c886411ab_jrhdt0cdbed1734a9e1501d1b6a0d10c5cc8b','secret_key_059af9b213174da160fbf7e8290ec261_9IRV72abd3ed98adc2a72a673b7ee0f5060ac');
$ilovepdf->verifySsl(false);
$myTask = $ilovepdf->newTask('compress');
if(sizeof($save_path_array) > 1) {
  $file_n = "merged_zip_".date("d-m-y h m sa").".zip";
  $myTask->setPackagedFilename($file_n);
}
else if ($num) {
  $file_n = $num.$filename;
}
else {
  $file_n = $filename;
}
foreach($save_path_array as $file_path) {
  $file1=$myTask->addFile($file_path);
}
$myTask->execute();
$x = $myTask->download($dload_pth);


$file_pth = $dload_pth.$file_n;
$file_nm = basename($file_pth);

// echo("<a href='$dload_pth' target='_blank'>Browse Folder</a>");
echo("<a href='$file_pth' download>".$file_nm."</a>");
?>
