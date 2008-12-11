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

        $this->setLayout('backend');
        #$this->assignToLayout('sidebar', new View('../../../plugins/dashboard/views/sidebar'));
    }

    function index() {
        $this->display('assets/views/index', array(
            'image_array' => assets_latest()
        ));
    }
    
    function upload() {
        
        $error_message[0] = "Unknown problem with your upload.";
        $error_message[1] = "Uploaded file too large (load_max_filesize).";
        $error_message[2] = "Uploaded file too large (MAX_FILE_SIZE).";
        $error_message[3] = "File was only partially uploaded.";
        $error_message[4] = "Choose a file to upload.";
        
        $upload_dir  = $_SERVER['DOCUMENT_ROOT'] . '/assets/';
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
    
    function latest() {

        $limit = 0;
        if ('AJAX' == get_request_method()) {
            $this->setLayout(null);
            $limit = 8;
        } 

        $this->display('assets/views/latest', array(
            'image_array' => assets_latest($limit)
        ));
    }
   
}

function assets_latest($limit = 0) {

    $image_array  = array();
    $file_array   = glob($_SERVER['DOCUMENT_ROOT'] . '/assets/*.*');

    /* Sort by modification time. */
    $sorted_array = array();
    foreach ($file_array as $file) {
        $sorted_array[$file] = filemtime($file);
    }
    arsort($sorted_array);
    
    foreach (array_keys($sorted_array) as $file) {
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