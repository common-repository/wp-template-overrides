<?php
/**
 * Form file for a new WP Template Override
 *
 * @TODO
 */
?>
<br />
<div class="error">
    <p>
        <strong>
            <?php _e( 'WARNING - If you make an error in your PHP Syntax, it will cause a blank white page or a displayed error on that page on your website. You will always be able to edit the file here.', 'wp-template-overrides' ); ?>
        </strong>
    </p>
</div>


<form action="" method="post">



    <p>
        <label for="override_name"><?php _e( 'Override Name', 'wp-template-overrides' ); ?></label>
        <input type="text" name="override_name" placeholder="Override Name" />
    </p>

    <p>
        <?php //

        $themes = wp_get_themes( array( 'errors' => null ) );

        $current_theme = wp_get_theme()->get_stylesheet();
        ?>
        <label for="override_theme"><?php _e( 'Theme To Override', 'wp-template-overrides' ); ?></label>
        <select data-nonce="<?php echo wp_create_nonce( 'get_templates_for_theme' ); ?>" name="override_theme">
            <option value=""><?php _e( 'Please Select..', 'wp-template-overrides' ); ?></option>
            <?php
            foreach ( $themes as $a_stylesheet => $a_theme ) {
                echo "\n\t" . '<option value="' . esc_attr( $a_stylesheet ) . '">' . $a_theme->display('Name') . '</option>';
            }
            ?>
        </select>
    </p>

    <?php /*
    <p>
        <label for="override_type"><?php _e( 'Override Type', 'wp-template-overrides' ); ?></label>
        <select name="override_type">
            <option value="existing-file">Existing File</option>
        </select>
    </p> */ ?>

    <p class="override-type-existing">
        <label for="file_to_override">Please Select The File To Override</label>
        <select name="file_to_override" data-nonce="<?php echo wp_create_nonce( 'get_file_content' ); ?>"></select>
    </p>


    <style>
        #editor{
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
        }
    </style>

    <div id="editor_wrapper" style="position:relative; width:100%;">
        <div id="editor"></div>
    </div>

    <input type="hidden" name="save_override_nonce" id="save_override_nonce" value="<?php echo wp_create_nonce( 'save_override' ); ?>" />
    <?php submit_button( 'Save Override', 'primary', 'save_override' ); ?>

</form>