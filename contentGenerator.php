<?php
  /**
   * Created by PhpStorm.
   * User: marshall
   * Date: 2012
   */

  //Default Druapl config
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
  define("DRUPAL_ROOT", getcwd() );
  $_SERVER['REMOTE_ADDR'] = "localhost"; // Necessary if running from command line
  require_once DRUPAL_ROOT.'/includes/bootstrap.inc';
  drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

  /**
   * Funzione che consente di creare automaticamente un contenuto drupal con le immagini jpg caricate in temp_img
   * e i dati exif ricavati da esse.
   *
   * @param $lat
   * @param $lng
   * @param $fileName
   *
   */
  function createContentFromImage($lat, $lng, $fileName) {

    $node       = new stdClass(); // Create a new node object Or page, or whatever content type you like
    $node->type = "exif_data";   // Set some default values
    node_object_prepare($node);
    $node->language = "en";
    $node->uid      = 1;
	$coords         = (object) array('lat' => $lat, 'lng' => $lng);
    $node->field_posizione['und'][0] = (array) $coords;

	//@ToDo here you have to substitute the path
    $file_path   = "/var/www/.." . $fileName;
    $count_photo = count($file_path);

    for ($i = 0; $i < $count_photo; $i++) {

	    if(getimagesize($file_path)) {

		    $file_gallery = (object) array(
			    'uid'       => 0,
                'uri'       => $file_path,
                'filemime'  => file_get_mimetype($file_path),
                'status'    => 1,
            );

		    //substitute "your_destination_folder" with a folder name
		    try{
			    file_copy($file_gallery, 'public://your_destination_folder');
			    $node->field_image['und'][0] = (array) $file_gallery;
			    echo "File correctly copied";
		    }catch (Exception $e) {
			    echo $e->getMessage();
		    }

        }

    }

	//$node = node_submit($node); // Prepare node for saving
    if($node = node_submit($node)) { // Prepare node for saving
        node_save($node); //Drupal node saving function call
        $status = "Content created correctly"."";
    }else{
        $status= "Something went wrong during the node submitting";
    }

    echo $status;

  } 
?>