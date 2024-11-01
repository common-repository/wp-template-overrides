/**
 * Initialisation Js File For WP Template Overrides
 */

/**
 * Initialise ACE Editor
 */
if( document.getElementById("editor") ){
    var editor = ace.edit("editor");
    editor.setTheme("ace/theme/dawn");
    editor.getSession().setMode("ace/mode/php");
}

/**
 * Admin Functionality.
 */
jQuery( document ).ready( function($){

    $( '#save_override').hide();

    var AJAXURL = '/wp-admin/admin-ajax.php';

    $( 'select[name=override_theme]').on( 'change', function(){

        var theme = $( this ).val();
        var nonce = $( this ).data( 'nonce' );

        editor.setReadOnly(false);
        editor.setValue( 'Loading Files.....' );
        editor.setReadOnly(true);

        $.post( AJAXURL, {
            action : 'wpto_get_templates_for_theme',
            theme : theme,
            nonce : nonce
        }, function( resp ){



            if( typeof resp === 'object' ){

                editor.setReadOnly(false);
                editor.setValue( 'Please Select A File...' );
                editor.setReadOnly(true);

                var select = $( 'select[name=file_to_override]' );
                $( select).empty();

                $( select ).append( '<option value="">Please Select...</option>' );

                for( name in resp ){
                    $( select).append( '<option value="' + resp[name] + '">' + name + '</option>' );
                }

            }

        });

    });

    $( 'select[name=file_to_override]' ).on( 'change', function(){

        var file  = $( this ).val();
        var nonce = $( this ).data( 'nonce' );

        editor.setReadOnly(false);
        editor.setValue( 'Loading.....' );
        editor.setReadOnly(true);

        $.post( AJAXURL, {
            action : 'wpto_get_file_contents',
            file   : file,
            nonce  : nonce
        }, function( resp ){

            editor.setValue(resp);
            editor.gotoLine(1);
            editor.setReadOnly(false);

            $( '#save_override').show();

        });

    });

    var test = 'test';

    $( '#save_override' ).on( 'click', function( e ){
        e.preventDefault();

        var name = $( 'input[name=override_name]').val();
        var theme = $( 'select[name=override_theme]').val();
        var file = $( 'select[name=file_to_override').val();
        var content = editor.getValue();
        var nonce = $( '#save_override_nonce').val();

        $.post( AJAXURL, {
            action  : 'wpto_save_override',
            name    : name,
            theme   : theme,
            file    : file,
            content : content,
            nonce   : nonce
        }, function( resp ){

            alert( resp );

            if( resp === 'The override has been saved.' ){
                location.reload();
            }

        });

    });

    $( '#update_override' ).on( 'click', function( e ){
        e.preventDefault();

        var nonce = $( '#update_override_nonce').val();
        var content = editor.getValue();
        var key = $( '#update_override_key').val();

        $.post( AJAXURL, {
            action  : 'wpto_update_override',
            key     : key,
            content : content,
            nonce   : nonce
        }, function( resp ){

            alert( resp );

            location.reload();

        });

    })


    /**
     * Auto Adjust The Editor Size.
     */
    var heightUpdateFunction = function() {

        // http://stackoverflow.com/questions/11584061/
        var newHeight =
            editor.getSession().getScreenLength()
                * editor.renderer.lineHeight
                + editor.renderer.scrollBar.getWidth();

        $('#editor_wrapper').height(newHeight.toString() + "px");
        $('#editor').height(newHeight.toString() + "px");
        $('#editor-section').height(newHeight.toString() + "px");

        // This call is required for the editor to fix all of
        // its inner structure for adapting to a change in size
        editor.resize();
    };

    // Set initial size to match initial content
    if( document.getElementById("editor") ){
        heightUpdateFunction();

        // Whenever a change happens inside the ACE editor, update
        // the size again
        editor.getSession().on('change', heightUpdateFunction);
    }

});