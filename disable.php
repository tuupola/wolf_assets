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
 *   http://www.appelsiini.net/projects/funky_cache
 *
 */

$pdo = Record::getConnection();
$table = TABLE_PREFIX . "setting";

$pdo->exec("DELETE FROM $table 
            WHERE name='assets_folder_list' 
            LIMIT 1");
