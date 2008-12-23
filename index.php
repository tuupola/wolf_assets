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

Plugin::setInfos(array(
    'id'          => 'assets',
    'title'       => 'Assets', 
    'description' => 'Mephisto style asset management.', 
    'version'     => '0.3.1', 
    'license'     => 'MIT',
    'require_frog_version' => '0.9.3',
    'update_url'  => 'http://www.appelsiini.net/download/frog-plugins.xml',
    'website'     => 'http://www.appelsiini.net/'
));

/* Stuff for backend. */
if (class_exists('AutoLoader')) {
    Plugin::addController('assets', 'Assets');
} 

