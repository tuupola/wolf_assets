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

Plugin::setInfos(array(
    'id'          => 'assets',
    'title'       => __('Asset Manager'),
    'description' => __('Mephisto style asset management.'),
    'version'     => '0.5.0-dev3',
    'license'     => 'MIT',
    'author'      => 'Mika Tuupola',
    'update_url'  => 'http://www.appelsiini.net/download/wolf-plugins.xml',
    'website'     => 'http://www.appelsiini.net/projects/frog_assets',
    'require_wolf_version' => '0.6.0',
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
    return str_replace(realpath(CMS_ROOT), '', realpath(CMS_ROOT));
}
