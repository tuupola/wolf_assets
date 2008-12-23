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
 *   http://www.appelsiini.net/projects/frog_assets
 *
 */


$pdo     = Record::getConnection();
$table   = TABLE_PREFIX . "setting";
$default = serialize(array('assets'));

$pdo->exec("INSERT INTO $table (name, value) 
            VALUES ('assets_folder_list', '$default')");

            
