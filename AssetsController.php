<?php

/*
 * Assets - Wolf CMS Mephisto style asset management plugin
 *
 * Copyright (c) 2008-2010 Mika Tuupola
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *   http://www.appelsiini.net/projects/frog_assets
 *
 */
 
if (!defined('DASHBOARD_LOG_EMERG'))   define('DASHBOARD_LOG_EMERG',    0);
if (!defined('DASHBOARD_LOG_ALERT'))   define('DASHBOARD_LOG_ALERT',    1);
if (!defined('DASHBOARD_LOG_CRIT'))    define('DASHBOARD_LOG_CRIT',     2);
if (!defined('DASHBOARD_LOG_ERR'))     define('DASHBOARD_LOG_ERR',      3);
if (!defined('DASHBOARD_LOG_WARNING')) define('DASHBOARD_LOG_WARNING',  4);
if (!defined('DASHBOARD_LOG_NOTICE'))  define('DASHBOARD_LOG_NOTICE',   5);
if (!defined('DASHBOARD_LOG_INFO'))    define('DASHBOARD_LOG_INFO',     6);
if (!defined('DASHBOARD_LOG_DEBUG'))   define('DASHBOARD_LOG_DEBUG',    7); 
 
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
        $this->assignToLayout('sidebar', new View('../../plugins/assets/views/sidebar'));            
     
    }

    function index() {
        assets_check_gd_support(); 
        $this->display('assets/views/index', array(
            'image_array' => assets_latest(0, 'thumbnail', $_SESSION['assets_folder']),
            'assets_folder_list' => unserialize(Setting::get('assets_folder_list'))
        ));
    }
    
    function settings() {
        assets_check_gd_support(); 
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
                $message = sprintf('Asset manager settings were updated by :username.');
                Observer::notify('log_event', $message, 'assets');                              
            } else {
                Flash::set('error', 'An error has occured.');
                $message = sprintf('Updating asset manager settings by :username failed.');
                Observer::notify('log_event', $message, 'assets', DASHBOARD_LOG_CRIT);
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
        $asset   = realpath(CMS_ROOT) . '/' . implode('/', $args);
        $asset   = urldecode($asset);
        $info    = pathinfo($asset);
        switch ($command) {
        case "delete":
            if (@unlink($asset)) {
                print "jQuery('#success').remove();";
                print "jQuery('#error').remove();";
                print "jQuery('#content').prepend('<div id=\"success\">File deleted.</div>');";
                print "jQuery('#success').hide().fadeIn('slow');";
                $message = sprintf('File %s was deleted by :username.',
                                   basename($asset));
                Observer::notify('log_event', $message, 'assets');
                /* Attempt to remove all generated thumbnails. */
                foreach (glob($info['dirname'] . '/'. $info['filename'] . '*') as $thumbnail) {
                    if (assets_is_thumbnail($thumbnail)) {
                        @unlink($thumbnail);
                    } 
                }
            } else {
                print "jQuery('#success').remove();";
                print "jQuery('#error').remove();";
                print "jQuery('#content').prepend('<div id=\"error\">Could not delete file.</div>');";
                print "jQuery('#error').hide().fadeIn('slow');";
                $message = sprintf('Deleting file %s by :username failed. %s',
                                   basename($asset),
                                   $error_message[$_FILES['user_file']['error']]);
                Observer::notify('log_event', $message, 'assets', DASHBOARD_LOG_ERR);             
            }
            break;          
        default:
            Flash::set('error', 'Hey! What are you doing?');
            break;
        }
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
            $check_folder = CMS_ROOT . '/' . $folder;
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
            $message = sprintf('Updating asset manager settings by :username failed.');
            Observer::notify('log_event', $message, 'assets', DASHBOARD_LOG_CRIT);
        } else {
            if ($folder_created) {
                Flash::set('success', __('Folder has been created and settings have been updated'));                
            } else {
                Flash::set('success', __('The settings have been updated.'));                
            }
            $message = sprintf('Asset manager settings were updated by :username.');
            Observer::notify('log_event', $message, 'assets');
        }

        redirect(get_url('plugin/assets/settings'));   
	}
    
    function upload() {
        
        $error_message[0] = "Unknown problem with upload.";
        $error_message[1] = "Uploaded file too large (load_max_filesize).";
        $error_message[2] = "Uploaded file too large (MAX_FILE_SIZE).";
        $error_message[3] = "File was only partially uploaded.";
        $error_message[4] = "Choose a file to upload.";
        
        /* Use later for remembering the pulldown value. */
        $_SESSION['assets_folder'] = $_POST['assets_folder'];
        
        $upload_dir  = CMS_ROOT . '/' . $_POST['assets_folder'] . '/';
        $upload_dir  = str_replace('//', '/', $upload_dir);
        $upload_file = $upload_dir . basename($_FILES['user_file']['name']);
        
        if (is_uploaded_file($_FILES['user_file']['tmp_name'])) {
            if (move_uploaded_file($_FILES['user_file']['tmp_name'], $upload_file)) {

                Flash::set('success', 'File ' . basename($_FILES['user_file']['name']) . ' uploaded.');

                $message = sprintf('File %s was uploaded by :username.',
                                   basename($_FILES['user_file']['name']));
                Observer::notify('log_event', $message, 'assets');

            } else {
                Flash::set('error', $error_message[$_FILES['user_file']['error']]);
                $message = sprintf('Uploading file %s by :username failed. %s',
                                   basename($asset),
                                   $error_message[$_FILES['user_file']['error']]);
                Observer::notify('log_event', $message, 'assets', DASHBOARD_LOG_ERR);
            }     
        } else {
            Flash::set('error', $error_message[$_FILES['user_file']['error']]);
            $message = sprintf('Uploading file %s by :username failed. %s',
                               basename($asset),
                               $error_message[$_FILES['user_file']['error']]);
            Observer::notify('log_event', $message, 'assets', DASHBOARD_LOG_ERR);
        }
        
        redirect(get_url('plugin/assets'));         
    }
    
    function latest($limit=0, $image_size='thumbnail', $folder=null) {
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
            'image_array' => assets_latest($limit, $image_size, $folder)
        ));
    }
   
}

function assets_latest($limit = 0, $image_size = 'thumbnail', $folder = 'assets') {
    
    if ('all' == $folder) {
        $folder_list = unserialize(Setting::get('assets_folder_list'));
    } else {
        $folder_list = array($folder);
    }

    $file_array  = array();

    foreach ($folder_list as $folder) {
        $assets_folder = CMS_ROOT . '/' .  $folder . '/';
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
        if (!assets_is_thumbnail($file)) {
            $path_parts = pathinfo($file);
            /* Support for PHP older than 5.2.0 */
            if (empty($path_parts['filename'])) {
                $path_parts['filename'] = basename($file, '.' . $path_parts['extension']);                                
            }

            $folder   = '/' . str_replace(realpath(CMS_ROOT), '', $path_parts['dirname']) . '/';
            $folder   = str_replace('//', '/', $folder);
            $original = $folder . $path_parts['basename'];
            
            if (assets_is_image($path_parts['extension'])) {
                /* Fix for TinyMCE and others who cannot handle thumbnails. */
                if ('original' == $image_size) {
                    $thumbnail  = $folder . $path_parts['filename'] . '.' . $path_parts['extension'];                    
                } else {
                    $thumbnail  = $folder . $path_parts['filename'] . '.64c.' . $path_parts['extension'];                    
                }
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

function assets_is_thumbnail($file) {  
    switch (true) {
        case preg_match('#(.+)\.([0-9]+)x?(c?)\.([a-z]+)$#i', $file);
        case preg_match('#(.+)\.x([0-9]+)(c?)\.([a-z]+)$#i', $file);
        case preg_match('#(.+)\.([0-9]+)x([0-9]+)(c?)\.([a-z]+)$#i', $file);
            $retval = true;
            break;
        default;
            $retval = false;
        break;
    }   
    return $retval;
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
    return '/wolf/plugins/assets/' . $retval;
}

function assets_default_folder() {
    $assets_folder_list = unserialize(Setting::get('assets_folder_list'));
    return $assets_folder_list[0];
}

function assets_check_gd_support() {
    $provided = gd_info();
    $needed   = array('GIF Read Support', 'JPG Support', 'PNG Support');
    if(isset($provided['JPEG Support'])) {
        $needed = array('GIF Read Support', 'JPEG Support', 'PNG Support');
    }

    foreach ($needed as $item) {
        if (!$provided[$item]) {
            Flash::set('error', __('Your system does not have :item. Assets manager will not work properly.', 
                             array(':item' => $item)));
            Flash::init();               
        }
    }
}
