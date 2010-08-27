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
    
    /* Add James Padolsey's regexp selector (http://shrt.st/mma). */
    $.expr[':'].regex = function(elem, index, match) {
        var matchParams = match[3].split(','),
            validLabels = /^(data|css):/,
            attr = {
                method: matchParams[0].match(validLabels) ? 
                            matchParams[0].split(':')[0] : 'attr',
                property: matchParams.shift().replace(validLabels,'')
            },
            regexFlags = 'ig',
            regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g,''), regexFlags);
        return regex.test(jQuery(elem)[attr.method](attr.property));
    };
    
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
        $("#assets_list").load(frog_root + '/admin/?/plugin/assets/latest/0/thumbnail/' + folder, null, function() {
            /* Make assets draggable in assets tab. */
            $("#assets_list a").draggable({
                revert: 'invalid'
            });
        });
    });
    
    /* Make assets draggable in assets tab. */
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
        
        var image_size = 'thumbnail';
        if ('tinymce' == $(':regex(id,^part_[0-9]*_filter):visible').val()) {
            image_size = 'original';
        }

        /* TinyMCE should fets original pictures for drag and drop to work. */
        $(':regex(id,^part_[0-9]*_filter)').bind('change', function() {
            if ('tinymce' == $(':regex(id,^part_[0-9]*_filter):visible').val()) {
                image_size = 'original';
            } else {
                image_size = 'thumbnail';
            }
            //console.log(image_size);
        });
        
        /* Read new filter value when page part tab is clicked. */
        $('#tab-control a.tab').bind('click', function() {
            $(':regex(id,^part_[0-9]*_filter)').trigger('change');
        });
        
        $('#pages')
            .prepend('<div id="assets_sidebar">'
                     + '<div id="assets_folder"><img src="/wolf/plugins/assets/images/indicator.gif" /></div>'
                     + '<div id="assets_page"><img src="/wolf/plugins/assets/images/indicator.gif" /></div>'
                     + '</div>');
                     
        $('#assets_page').load(frog_root + '/admin/?/plugin/assets/latest/0/' + image_size);

        $('#assets_folder')
            .load(frog_root + '/admin/?/plugin/assets/pulldown', function() {
                $('select', this).bind('change', function() {
                    var folder = $(this).val().replace(/\//g, ':');
                    $("#assets_page").load(frog_root + '/admin/?/plugin/assets/latest/0/' + image_size + '/' + folder);
                });
            });
    }

});
