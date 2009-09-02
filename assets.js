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
    
    /* FIX IE of not allowing dropping links into textarea. */
    if ($.browser.msie) {
        document.ondragstart = function () { 
            if (window.event.dataTransfer != undefined) {
                window.event.dataTransfer.effectAllowed = "copyLink";
            } 
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
    $("#assets_list a").draggable({
        revert: 'invalid'
    });
    $("#trash_can").droppable({
        tolerance: 'touch',
        drop: function(event, ui) {
		    var url = frog_root + '/admin/?/plugin/assets/file/delete' + $(ui.draggable.context).attr('href');
		    $(ui.draggable.context).hide();
		    $.getScript(url);
		    $(this).attr('src', '../frog/plugins/assets/images/trash.png');
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
    
    /* Google Gears support. */
    var desktop = google.gears.factory.create('beta.desktop');
    var request = google.gears.factory.create('beta.httprequest');

    /* We cannot use $.bind() since jQuery does not normalize the native events. */
    if ($.browser.mozilla) {
        $('#content-wrapper').get(0).addEventListener('dragdrop', upload, false);
    } else if ($.browser.msie) {
        $('#content-wrapper').get(0).attachEvent('ondrop', upload, false);                
        $('#content-wrapper').get(0).attachEvent('ondragover', function(event) { event.returnValue = false; }, false);                
    } else if ($.browser.safari) {
        $('#content-wrapper').get(0).addEventListener('drop', upload, false);        
        $('#content-wrapper').get(0).addEventListener('dragover', function(event) { event.returnValue = false; }, false);
    }
    
    function upload(event) {
        var data = desktop.getDragData(event, 'application/x-gears-files');
        var file = data.files[0]; /* TODO: Support for multiple files. */
        var boundary = '------multipartformboundary' + '12345';
        var dashdash = '--';
        var crlf     = '\r\n';
        
        var builder = google.gears.factory.create('beta.blobbuilder');

        builder.append(dashdash);
        builder.append(boundary);
        builder.append(crlf);

        builder.append('Content-Disposition: form-data; name="user_file"');
        
        if (file.name) {
          builder.append('; filename="' + file.name + '"');
        }

        builder.append(crlf);
        builder.append('Content-Type: application/octet-stream');
        builder.append(crlf);
        builder.append(crlf);
        builder.append(file.blob);
        builder.append(crlf);
        builder.append(dashdash);
        builder.append(boundary);
        builder.append(dashdash);
        builder.append(crlf);
        
        request.upload.onprogress = function() {
        };
        
        request.onreadystatechange = function() {
            switch(request.readyState) {
                case 4:
                    /* Rebind jQuery UI draggable */
                    $("#assets_list").html(request.responseText);
                    $("#assets_list a").draggable({
                        revert: 'invalid'
                    });
                    break;
            }
        };
        
        request.open("POST", "/admin/plugin/assets/upload?assets_folder=assets&view=latest");
        request.setRequestHeader('content-type', 'multipart/form-data; boundary=' + boundary);
        request.send(builder.getAsBlob());
        
        /* Prevent FireFox opening the dragged file. */
        if ($.browser.mozilla) {
            event.stopPropagation();
        }
    }

});

// Copyright 2007, Google Inc.
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are met:
//
//  1. Redistributions of source code must retain the above copyright notice,
//     this list of conditions and the following disclaimer.
//  2. Redistributions in binary form must reproduce the above copyright notice,
//     this list of conditions and the following disclaimer in the documentation
//     and/or other materials provided with the distribution.
//  3. Neither the name of Google Inc. nor the names of its contributors may be
//     used to endorse or promote products derived from this software without
//     specific prior written permission.
//
// THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR IMPLIED
// WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
// MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO
// EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
// SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
// PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
// OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
// WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
// OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
// ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
//
// Sets up google.gears.*, which is *the only* supported way to access Gears.
//
// Circumvent this file at your own risk!
//
// In the future, Gears may automatically define google.gears.* without this
// file. Gears may use these objects to transparently fix bugs and compatibility
// issues. Applications that use the code below will continue to work seamlessly
// when that happens.

(function() {
  // We are already defined. Hooray!
  if (window.google && google.gears) {
    return;
  }

  var factory = null;

  // Firefox
  if (typeof GearsFactory != 'undefined') {
    factory = new GearsFactory();
  } else {
    // IE
    try {
      factory = new ActiveXObject('Gears.Factory');
      // privateSetGlobalObject is only required and supported on IE Mobile on
      // WinCE.
      if (factory.getBuildInfo().indexOf('ie_mobile') != -1) {
        factory.privateSetGlobalObject(this);
      }
    } catch (e) {
      // Safari
      if ((typeof navigator.mimeTypes != 'undefined')
           && navigator.mimeTypes["application/x-googlegears"]) {
        factory = document.createElement("object");
        factory.style.display = "none";
        factory.width = 0;
        factory.height = 0;
        factory.type = "application/x-googlegears";
        document.documentElement.appendChild(factory);
      }
    }
  }

  // *Do not* define any objects if Gears is not installed. This mimics the
  // behavior of Gears defining the objects in the future.
  if (!factory) {
    return;
  }

  // Now set up the objects, being careful not to overwrite anything.
  //
  // Note: In Internet Explorer for Windows Mobile, you can't add properties to
  // the window object. However, global objects are automatically added as
  // properties of the window object in all browsers.
  if (!window.google) {
    google = {};
  }

  if (!google.gears) {
    google.gears = {factory: factory};
  }
})();
