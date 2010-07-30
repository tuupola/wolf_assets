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
 
jQuery(function($) {

    /* FIX IE of not allowing dropping links into textarea. */
    if ($.browser.msie) {
        document.ondragstart = function () { 
            window.event.dataTransfer.effectAllowed = "copyLink"; 
        };    
    }
            
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
    console.log( $("#assets_list a"));
    $("#assets_list a").draggable({
        revert: 'invalid',
        helper: 'clone',
        cursorAt: { top: 32, right: 32 }
    });
    $("#trash_can").droppable({
        tolerance: 'touch',
        drop: function(event, ui) {
		    var url = frog_root + '/admin/?/plugin/assets/file/delete' + $(ui.draggable.context).attr('href');
		    $(ui.draggable.context).hide();
		    $.getScript(url);
		    $(this).attr('src', '/wolf/plugins/assets/images/trash.png');
        }, 
        over: function(event, ui) {
            $(this).attr('src', '/wolf/plugins/assets/images/trash_full.png');
        },
        out: function(event, ui) {
            $(this).attr('src', '/wolf/plugins/assets/images/trash.png');
		}
	});
	    
    /* Run only when editing a page. */
    if ($('.page textarea').size()) {
        $('#pages')
            .prepend('<div id="assets_sidebar">'
                     + '<div id="assets_folder"><img src="/wolf/plugins/assets/images/indicator.gif" /></div>'
                     + '<div id="assets_page"><img src="/wolf/plugins/assets/images/indicator.gif" /></div>'
                     + '</div>');
                     
        $('#assets_page').load(frog_root + '/admin/?/plugin/assets/latest/0');

        $('#assets_folder')
            .load(frog_root + '/admin/?/plugin/assets/pulldown', function() {
                $('select', this).bind('change', function() {
                    var folder = $(this).val().replace(/\//g, ':');
                    $("#assets_page").load(frog_root + '/admin/?/plugin/assets/latest/0/' + folder);
                });
            })
    }

    /* Just a shortcut to also reposition when clicking a tab. */
    $('#tabs-meta > a.tab').bind('click', function() {
        $(window).trigger('resize');
    });

});
