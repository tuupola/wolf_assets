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
    'title'       => 'Asset Manager',
    'description' => 'Mephisto style asset management.',
    'version'     => '0.4.8-dev',
    'license'     => 'MIT',
    'author'      => 'Mika Tuupola',
    'require_frog_version' => '0.9.4',
    'update_url'  => 'http://www.appelsiini.net/download/frog-plugins.xml',
    'website'     => 'http://www.appelsiini.net/projects/frog_assets'
));

/* Stuff for backend. */
if (strpos($_SERVER['PHP_SELF'], ADMIN_DIR . '/index.php')) {
    Plugin::addController('assets', 'Assets');
    Observer::observe('view_backend_list_plugin', 'assets_inject_javascript');  
}

function assets_inject_javascript($plugin_name, $plugin) {
    if ('assets' == $plugin_name) {
        print '<script type="text/javascript" charset="utf-8">';
        printf("var frog_root = '%s';", assets_frog_root());
        print '</script>';        
    }
}

function assets_frog_root() {
    return str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', realpath(FROG_ROOT));
}
