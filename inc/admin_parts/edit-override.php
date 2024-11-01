<?php

if( ! current_user_can( 'manage_options' ) ){
    echo __( 'You do not have permission to be here...', 'wp-template-overrides' );
    exit;
}

$key = wp_kses( $_GET['edit_override'], array() );

$delete_url = add_query_arg( array(
    'delete_template_override' => $key,
    '_nonce' => wp_create_nonce( 'delete_template_override_' . $key )
) );

?>

<h1><?php _e( 'Edit Template Override', 'wp-template-overrides' ); ?> - <a href="<?php echo esc_attr( $delete_url ); ?>"><?php _e( 'Delete?', 'wp-template-overrides' ); ?></a></h1>

<h2><a href="<?php echo remove_query_arg( 'edit_override' ) ?>"><?php _e( 'Back to the list', 'wp-template-overrides' ); ?></a></h2>

<div class="error">
    <p>
        <strong>
            <?php _e( 'WARNING - If you make an error in your PHP Syntax, it will cause a blank white page or a displayed error on that page on your website. You will always be able to edit the file here.', 'wp-template-overrides' ); ?>
        </strong>
    </p>
</div>

<?php
$override = isset( $this->overrides[$key] ) ? $this->overrides[$key] : false ;

if( ! $override ){
    echo sprintf( __( 'Override Not Found. <a href="%s">Go Back?</a>' ), remove_query_arg( 'edit_override' ) );
    exit;
}
?>

<h4><?php _e( 'Name:', 'wp-template-overrides' ); ?> <i><?php echo $override['name']; ?></i></h4>

<h4><?php _e( 'Theme:', 'wp-template-overrides' ); ?> <i><?php echo $override['theme']; ?></i></h4>

<h4><?php _e( 'File:', 'wp-template-overrides' ); ?> <i><?php echo basename( $override['file'] ); ?></i></h4>

<form action="" method="post">

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
        <div id="editor"><?php echo htmlspecialchars( file_get_contents( $override['file'] ) ); ?></div>
    </div>

    <input type="hidden" name="update_override_key" id="update_override_key" value="<?php echo esc_attr( $key ); ?>" />
    <input type="hidden" name="update_override_nonce" id="update_override_nonce" value="<?php echo esc_attr( wp_create_nonce( 'update_override' ) ); ?>" />
    <?php submit_button( 'Save Override', 'primary', 'update_override' ); ?>

</form>