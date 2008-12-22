<?php

/*
 * Assets - Frog CMS Mephisto style asset management plugin
 *
 * Copyright (c) 2008 Mika Tuupola
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *   http://www.appelsiini.net/
 *
 */
 
class AssetsController extends PluginController
{
    function __construct() {
        AuthUser::load();
        if (!(AuthUser::isLoggedIn())) {
            redirect(get_url('login'));            
        }
        
        $_SESSION['asset_folder'] = isset($_SESSION['asset_folder']) ?
                                          $_SESSION['asset_folder']  : 'assets';

        $this->setLayout('backend');
        if (version_compare(FROG_VERSION, '0.9.5', '<')) {
            $this->assignToLayout('sidebar', new View('../../../plugins/assets/views/sidebar'));            
        } else {
            $this->assignToLayout('sidebar', new View('../../plugins/assets/views/sidebar'));            
        }
     
    }

    function index() {
        $this->display('assets/views/index', array(
            'image_array' => assets_latest(0, $_SESSION['asset_folder']),
            'assets_folder_list' => unserialize(Setting::get('assets_folder_list'))
        ));
    }
    
    function settings() {
        $this->display('assets/views/settings', array(
			'assets_folder_list' => unserialize(Setting::get('assets_folder_list'))
		));	
    }

    function pulldown() {
        $this->setLayout(null);
        $this->display('assets/views/pulldown', array(
			'assets_folder_list' => unserialize(Setting::get('assets_folder_list'))
		));	
    }

    function folder($command, $id) {
        
        $assets_folder_list = unserialize(Setting::get('assets_folder_list'));
        
        $pdo   = Record::getConnection();
		$table = TABLE_PREFIX . 'setting';

        switch ($command) {
        case "delete":
            unset($assets_folder_list[$id]);
            $assets_folder_list = serialize($assets_folder_list);
    		
            $sql   = "UPDATE $table 
                      SET value ='$assets_folder_list' 
                      WHERE name = 'assets_folder_list'"; 

            $statement = $pdo->prepare($sql);
            $success   = $statement->execute() !== false;

            if ($success){
                Flash::set('success', __('Folder was removed from list.'));
            } else {
                Flash::set('error', 'An error has occured.');
            }
            break;          
        default:
            break;
        }
        
        redirect(get_url('plugin/assets/settings'));
    }
    
    function save() {
		error_reporting(E_ALL);
    
        /* Setting::saveFromData() does not handle any errors so lets save manually. */

        $pdo   = Record::getConnection();
		$table = TABLE_PREFIX . 'setting';

		$assets_folder_list = serialize($_POST['assets_folder_list']);
        $sql   = "UPDATE $table 
                  SET value ='$assets_folder_list' 
                  WHERE name = 'assets_folder_list'"; 

        $statement = $pdo->prepare($sql);
        $success   = $statement->execute() !== false;

        if ($success){
            Flash::set('success', __('The settings have been updated.'));
        } else {
            Flash::set('error', 'An error has occured.');
        }

        redirect(get_url('plugin/assets/settings'));   
	}
    
    function upload() {
        
        $error_message[0] = "Unknown problem with your upload.";
        $error_message[1] = "Uploaded file too large (load_max_filesize).";
        $error_message[2] = "Uploaded file too large (MAX_FILE_SIZE).";
        $error_message[3] = "File was only partially uploaded.";
        $error_message[4] = "Choose a file to upload.";
        
        /* Use later for remembering the pulldown value. */
        $_SESSION['asset_folder'] = $_POST['asset_folder'];
        
        $upload_dir  = $_SERVER['DOCUMENT_ROOT'] . '/' . $_POST['asset_folder'] . '/';
        $upload_dir  = str_replace('//', '/', $upload_dir);
        $upload_file = $upload_dir . basename($_FILES['user_file']['name']);
        
        if (is_uploaded_file($_FILES['user_file']['tmp_name'])) {
            if (move_uploaded_file($_FILES['user_file']['tmp_name'], $upload_file)) {
                Flash::set('success', 'File ' . basename($_FILES['user_file']['name']) . ' uploaded.');
            } else {
                Flash::set('error', $error_message[$_FILES['user_file']['error']]);
            }     
        } else {
            Flash::set('error', $error_message[$_FILES['user_file']['error']]);
        }
        
        redirect(get_url('plugin/assets'));         
    }
    
    function latest($limit=0, $folder) {
        $folder = str_replace(':', '/', $folder);
        if ('AJAX' == get_request_method()) {
            $this->setLayout(null);
        } 

        $this->display('assets/views/latest', array(
            'image_array' => assets_latest($limit, $folder)
        ));
    }
   
}

function assets_latest($limit = 0, $folder='assets') {
    
    if ('all' == $folder) {
        $folder_list = unserialize(Setting::get('assets_folder_list'));
    } else {
        $folder_list = array($folder);
    }

    $file_array  = array();

    foreach ($folder_list as $folder) {
        $asset_folder = $_SERVER['DOCUMENT_ROOT'] . '/' .  $folder . '/';
        $asset_folder = str_replace('//', '/', $asset_folder);
        $file_array = array_merge($file_array, glob($asset_folder . '*.*'));
    }

    /* Sort by modification time. */
    $sorted_array = array();
    foreach ($file_array as $file) {
        $sorted_array[$file] = filemtime($file);
    }
    arsort($sorted_array);

    $image_array = array();
    
    foreach (array_keys($sorted_array) as $file) {
        /* Ignore directories. */
        /* TODO: Make this recursive. */
        if (is_dir($file)) {
            continue;
        }
        /* Do not include thumbnails. */
        if (!strpos($file, '.64c.')) {
            $path_parts = pathinfo($file);
            $folder     = str_replace($_SERVER['DOCUMENT_ROOT'], '', $path_parts['dirname']) . '/';
            $original   = $folder . $path_parts['basename'];
            $thumbnail  = $folder . $path_parts['filename'] . '.64c.' . $path_parts['extension'];            
            $image_array[$original] = $thumbnail;                
        }
        /* Show maximum limit thumbnails. */
        if ($limit && $limit == count($image_array)) {
            break;
        }
    }
    
    return $image_array;
}