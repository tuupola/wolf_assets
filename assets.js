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
 
 jQuery(document).ready(function() {
    
    /* Run only when editing a page. */
    if (jQuery('#page-1 textarea').size()) {
        jQuery('#pages')
            .prepend('<div id="assets"><img src="../frog/plugins/assets/images/indicator.gif" /></div>');

        var left = jQuery('#page-1 textarea').offset().left + jQuery('#page-1 textarea').outerWidth() + 5;
        var top  = jQuery('#page-1 textarea').offset().top - 1 ;

        jQuery('#assets')
            .load('/admin/?/plugin/assets/latest')
            .css('top', top)
            .css('left', left);      
    }
    
});