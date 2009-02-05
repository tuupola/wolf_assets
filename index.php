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

Plugin::setInfos(array(
    'id'          => 'assets',
    'title'       => 'Assets',
    'description' => 'Mephisto style asset management.',
    'version'     => '0.3.4',
    'license'     => 'MIT',
    'author'      => 'Mika Tuupola',
    'require_frog_version' => '0.9.3',
    'update_url'  => 'http://www.appelsiini.net/download/frog-plugins.xml',
    'website'     => 'http://www.appelsiini.net/projects/frog_assets'
));

/* Stuff for backend. */
if ('/admin/index.php' == $_SERVER['PHP_SELF']) {
    Plugin::addController('assets', 'Assets');
} 

