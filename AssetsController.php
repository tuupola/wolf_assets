<?php

/*
 * Assets - Frog CMS Mephisto style asset management plugin
 *
 * Copyright (c) 2008-2009 Mika Tuupola
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *   http://www.appelsiini.net/projects/frog_assets
 *
 */
 
class AssetsController extends PluginController
{
    function __construct() {
        AuthUser::load();
        if (!(AuthUser::isLoggedIn())) {
            redirect(get_url('login'));            
        }

        $_SESSION['assets_folder'] = isset($_SESSION['assets_folder']) ?
                                           $_SESSION['assets_folder'] : assets_default_folder();

        $this->setLayout('backend');
        if (version_compare(FROG_VERSION, '0.9.4', '<=')) {
            $this->assignToLayout('sidebar', new View('../../../plugins/assets/views/sidebar'));            
        } else {
            $this->assignToLayout('sidebar', new View('../../plugins/assets/views/sidebar'));            
        }
     
    }

    function index() {
        $this->display('assets/views/index', array(
            'image_array' => assets_latest(0, $_SESSION['assets_folder']),
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
            $deleted = $assets_folder_list[$id];
            unset($assets_folder_list[$id]);
            $assets_folder_list = serialize($assets_folder_list);
    		
            $query = "UPDATE $table 
                      SET value = '$assets_folder_list' 
                      WHERE name = 'assets_folder_list'"; 
                      
            if ($pdo->exec($query)) {
                Flash::set('success', __('Folder :deleted was removed from list. Delete it manually from server.', 
                                          array(':deleted' => $deleted)));
            } else {
                Flash::set('error', 'An error has occured.');
            }
            break;          
        default:
            Flash::set('error', 'Hey! What are you doing?');
            break;
        }
        
        redirect(get_url('plugin/assets/settings'));
    }
    
    function file() {
        $args    = func_get_args();
        $command = array_shift($args);
        $asset   = $_SERVER['DOCUMENT_ROOT'] . '/' . implode('/', $args);
        $asset   = urldecode($asset);
        switch ($command) {
        case "delete":
            if (@unlink($asset)) {
                print "jQuery('#success').remove();";
                print "jQuery('#error').remove();";
                print "jQuery('#content').prepend('<div id=\"success\">Asset deleted.</div>');";
                print "jQuery('#success').hide().fadeIn('slow');";
            } else {
                print "jQuery('#success').remove();";
                print "jQuery('#error').remove();";
                print "jQuery('#content').prepend('<div id=\"error\">Could not delete asset.</div>');";
                print "jQuery('#error').hide().fadeIn('slow');";
            }
            break;          
        default:
            Flash::set('error', 'Hey! What are you doing?');
            break;
        }
        
        //redirect(get_url('plugin/assets/settings'));
    }
    
    function save() {
		error_reporting(E_ALL);
    
        /* Setting::saveFromData() does not handle any errors so lets save manually. */

        $pdo   = Record::getConnection();
		$table = TABLE_PREFIX . 'setting';

		$assets_folder_list = serialize($_POST['assets_folder_list']);
        $query = "UPDATE $table 
                  SET value ='$assets_folder_list' 
                  WHERE name = 'assets_folder_list'"; 
 
        $folder_created = false;
        foreach ($_POST['assets_folder_list'] as $folder) {
            $check_folder = $_SERVER['DOCUMENT_ROOT'] . '/' . $folder;
            if (! file_exists($check_folder)) {
                if (@mkdir($check_folder)) {
                    $folder_created = true;
                } else {
                    Flash::set('error', __('NOTE! You must create folder :folder manually.', 
                                            array(':folder' => $check_folder)));                    
                };
            }
        }
              
        if (false === $pdo->exec($query)) {
            Flash::set('error', __('An error has occured.'));
        } else {
            if ($folder_created) {
                Flash::set('success', __('Folder has been created and settings have been updated'));                
            } else {
                Flash::set('success', __('The settings have been updated.'));                
            }
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
        $_SESSION['assets_folder'] = $_POST['assets_folder'];
        
        $upload_dir  = $_SERVER['DOCUMENT_ROOT'] . '/' . $_POST['assets_folder'] . '/';
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
    
    function latest($limit=0, $folder=null) {
        $folder = str_replace(':', '/', $folder);
        if (trim($folder)) {
            $_SESSION['assets_folder'] = $folder;            
        } else {
            $folder = $_SESSION['assets_folder'];
        }

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
        $assets_folder = $_SERVER['DOCUMENT_ROOT'] . '/' .  $folder . '/';
        $assets_folder = str_replace('//', '/', $assets_folder);
        $file_array = array_merge($file_array, glob($assets_folder . '*.*'));
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
        if (is_dir($file)) {
            continue;
        }
        /* Do not include thumbnails. */
        if (!strpos($file, '.64c.')) {
            $path_parts = pathinfo($file);
            /* Support for PHP older than 5.2.0 */
            if (empty($path_parts['filename'])) {
                $path_parts['filename'] = basename($file, '.' . $path_parts['extension']);                                
            }

            $folder   = '/' . str_replace($_SERVER['DOCUMENT_ROOT'], '', $path_parts['dirname']) . '/';
            $folder   = str_replace('//', '/', $folder);
            $original = $folder . $path_parts['basename'];
            
            if (assets_is_image($path_parts['extension'])) {
                $thumbnail  = $folder . $path_parts['filename'] . '.64c.' . $path_parts['extension'];                
            } else {
                $thumbnail = assets_get_icon($path_parts['extension']);
            }
            $image_array[$original] = $thumbnail;                
        }
        /* Show maximum limit thumbnails. */
        if ($limit && $limit == count($image_array)) {
            break;
        }
    }
    
    return $image_array;
}

function assets_is_image($extension) {
    $images = array('jpg', 'jpeg', 'gif', 'png', 'JPG', 'JPEG', 'GIF', 'PNG');
    return in_array($extension, $images);
}

function assets_get_icon($extension) {
    switch (strtolower($extension)) {
        case 'pdf':
            $retval = 'images/pdf.png';
            break;
        case 'mpg':
        case 'mov':
        case 'avi':
        case 'swf':
        case 'flv':
            $retval = 'images/video.png';
            break;
        case 'mp2':
        case 'mp3':
        case 'mpga':
        case 'wav':
            $retval = 'images/audio.png';
            break;    
        default:
            $retval = 'images/doc.png';
            break;
    }
    return '../frog/plugins/assets/' . $retval;
}

function assets_default_folder() {
    $assets_folder_list = unserialize(Setting::get('assets_folder_list'));
    return $assets_folder_list[0];
}
