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
 
jQuery(function($) {
        
    /* If you are running 0.9.4 or older and you installed Frog */
    /* somewhere else than document root uncomment and edit line below by hand. */
    /* var frog_root = ''; */

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
    $("select[name='assets_folder']").bind('change', function() {
        var folder = $(this).val().replace(/\//g, ':');
        $("#assets_list").load(frog_root + '/admin/?/plugin/assets/latest/0/' + folder, null, function() {
            /* Make assets draggable in assets tab. */
            $("#assets_list a").draggable({
                revert: 'invalid'
            });
        });
    });
    
    /* Make assets draggable in assets tab. */
    $("#assets_list a").draggable({
        revert: 'invalid'
    });
    $("#trash_can").droppable({
        tolerance: 'touch',
        drop: function(event, ui) {
		    var url = frog_root + '/admin/?/plugin/assets/file/delete' + $(ui.draggable.context).attr('href');
		    $(ui.draggable.context).hide();
		    $.getScript(url);
		    $(this).attr('src', '../frog/plugins/assets/images/trash.png')
        }, 
        over: function(event, ui) {
            $(this).attr('src', '../frog/plugins/assets/images/trash_full.png');
        },
        out: function(event, ui) {
            $(this).attr('src', '../frog/plugins/assets/images/trash.png');
		}
	});
	    
    /* Run only when editing a page. */
    if ($('.page textarea').size()) {
        $('#pages')
            .prepend('<div id="assets_page"><img src="../frog/plugins/assets/images/indicator.gif" /></div>')
            .prepend('<div id="assets_folder"><img src="../frog/plugins/assets/images/indicator.gif" /></div>');

        var left = $('.page textarea:visible').offset().left + $('.page textarea:visible').outerWidth() + 5;
        var top  = $('.page textarea:visible').offset().top - 1 ;

        $('#assets_page')
            .load(frog_root + '/admin/?/plugin/assets/latest/8')
            .css('top', top)
            .css('left', left);

        var left_2 = $('.page textarea:visible').offset().left + $('.page textarea:visible').outerWidth() + 5;
        var top_2  = $('.part > p > select:visible').offset().top;

        $('#assets_folder')
            .load(frog_root + '/admin/?/plugin/assets/pulldown', function() {
                $('select', this).bind('change', function() {
                    var folder = $(this).val().replace(/\//g, ':');
                    $("#assets_page").load(frog_root + '/admin/?/plugin/assets/latest/8/' + folder);
                });
            })
            .css('top', top_2)
            .css('left', left_2);
    }
    
    /* Reposition assets when resizing a window. */
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
    });
    
    /* Just a shortcut to also reposition when clicking a tab. */
    $('#tabs-meta > a.tab').bind('click', function() {
        $(window).trigger('resize');
    });

});