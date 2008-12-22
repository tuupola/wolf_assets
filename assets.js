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
 
jQuery(function($) {
    
    /* Settings tab stuff. */  
    $("img.assets-folder-add").bind('click', function() {
        $(this)
            .parent()
            .parent()
            .clone(true)
            .appendTo("td.assets-folder");

        $("td.assets-folder input:last").val("");

        return false;
    });
    
    /* When is Assets tab reload assets list according to pulldown. */
    $("select[name='asset_folder']").bind('change', function() {
        var folder = $(this).val().replace(/\//, ':');
        $("#assets_list").load('/admin/?/plugin/assets/latest/0/' + folder);
    });
    
    /* Run only when editing a page. */
    if ($('#page-1 textarea').size()) {
        $('#pages')
            .prepend('<div id="assets_page"><img src="../frog/plugins/assets/images/indicator.gif" /></div>')
            .prepend('<div id="assets_folder"><img src="../frog/plugins/assets/images/indicator.gif" /></div>')
            ;

        var left = $('#page-1 textarea').offset().left + jQuery('#page-1 textarea').outerWidth() + 5;
        var top  = $('#page-1 textarea').offset().top - 1 ;

        $('#assets_page')
            .load('/admin/?/plugin/assets/latest/8/all')
            .css('top', top)
            .css('left', left);

        var left_2 = $('#page-1 textarea').offset().left + jQuery('#page-1 textarea').outerWidth() + 5;
        var top_2  = $('#part-1 > p > select').offset().top;

        $('#assets_folder')
            .load('/admin/?/plugin/assets/pulldown', function() {
                $('select', this).bind('change', function() {
                    var folder = $(this).val().replace(/\//, ':');
                    $("#assets_page").load('/admin/?/plugin/assets/latest/8/' + folder);
                });
            })
            .css('top', top_2)
            .css('left', left_2);
    }
    
    $(window).bind('resize', function() {
        var left = $('#page-1 textarea').offset().left + $('#page-1 textarea').outerWidth() + 5;
        var top  = $('#page-1 textarea').offset().top - 1 ;
        $('#assets_page')
            .css('top', top)
            .css('left', left);     

        var left_2 = $('#page-1 textarea').offset().left + jQuery('#page-1 textarea').outerWidth() + 5;
        var top_2  = $('#part-1 > p > select').offset().top;         
        $('#assets_folder')
            .css('top', top_2)
            .css('left', left_2);     
    })
    
});