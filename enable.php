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

/* Prevent direct access. */
if (!defined("FRAMEWORK_STARTING_MICROTIME")) {
    die("All your base are belong to us!");
}

$pdo     = Record::getConnection();
$table   = TABLE_PREFIX . "setting";
$default = serialize(array('assets'));

$pdo->exec("INSERT INTO $table (name, value) 
            VALUES ('assets_folder_list', '$default')");

            
