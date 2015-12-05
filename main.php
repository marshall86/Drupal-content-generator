<?php
 /**
  * Created by PhpStorm.
  * User: marshall
  * Date: 2012
  */

  //include the main script for the Drupal content generation
  include("contentGenerator.php");
  $directory = "temp_img/"; //substitute this folder name with yours
  copyFrom($directory);

  /**
   * This function reads the content of a folder then the redExifJpg function or the extractZip function
   *
   * @param $directory
   *
   */

  function copyFrom($directory) {

	$status = "";

	//check if the gave directory is a real one
	if (is_dir($directory) && $directory_handle = opendir($directory)) {
		while (($file = readdir($directory_handle)) !== false) {
			//check if $file it's not a directory itself or it's not a "." or ".." path
			if((!is_dir($file)) && ($file != ".") && ($file != "..")) {
				//getting the file path info
				$path_info = pathinfo($file);
				//Check the file extension
				if($path_info['extension'] == "jpg") {
				   readJPGExifData($file, $directory);
				}elseif($path_info['extension'] == "zip") {
				   extractZip($file, $directory);
				}else {
				   $status = "No jpg images present in the remote folder";
				}
			}
        }

		closedir($directory_handle);
	}

	echo $status;

  }

 /**
  * This function extracts all the images present in a Zip archive
  * it moves them to the choosen remote folder
  * it removes the archive to prevent any kind of confusion
  *
  * @param $arch
  * @param $directory
  *
  */
  function extractZip($arch, $directory) {

	$arc = $directory . $arch;
	$zipArch = new ZipArchive();

	if($zipArch->open($arc)) {
		$zipArch->extractTo($directory);
	}else {
		@exit("It's impossible to open the file: " . $arch."\n");
	}

    $zipArch->close();

	//removing the Zip archive to prevent any kind of confusion next time
    @unlink($_SERVER["DOCUMENT_ROOT"] . $directory . $arch);

    copyFrom($directory);

  }

  /**
   * This function extracts the EXIF data from a JPG image and convert then convert the GPS coordinates in degree
   *
   * @param $img
   * @param $directory
   *
   */

  function readJPGExifData($img, $directory) {

	$imgDir = $directory.$img;
	//reading exif data
	$exif    = exif_read_data($imgDir, 0, true);
	//preparing the GPS coordinates
	$gpsLat  = $exif["GPS"]["GPSLatitude"];
	$degLat  = $gpsLat[0];
	$minLat  = $gpsLat[1];
	$secLat  = $gpsLat[2];

	$gpsLng  = $exif["GPS"]["GPSLongitude"];
	$degLon  = $gpsLng[0];
	$minLon  = $gpsLng[1];
	$secLon  = $gpsLng[2];

	//converting GPS lat data to degree
	$secLat  = $secLat[0].$secLat[1] . "," . $secLat[2].$secLat[3].$secLat[4];
	$minLat2 = $minLat/60;
	$secLat2 = $secLat*(1/3600);
	$lat     = $degLat+$minLat2+$secLat2; //Lat coordinate - degree

	//converting GPS long data to degree
	$secLon  = $secLon[0].$secLon[1] . "," . $secLon[2].$secLon[3].$secLon[4];
	$minLon2 = $minLon/60;
	$secLon2 = $secLon * (1/3600);
	$lng     = $degLon+$minLon2+$secLon2; //Long coordinate - degree

	//creating Drupal content with the image lat, lng data
	createContentFromImage($lat, $lng, $img);

	//removing the image to prevent any kind of confusion next time
	@unlink($_SERVER["DOCUMENT_ROOT"] . $imgDir);

  }

?>