<?php
/**
 * Admin Page for Wp Template Override
 */

$curpage = ( isset( $_GET['subpage'] ) ) ? $_GET['subpage'] : 'list-overrides' ;

?>
<div class="wrap">

    <?php if( isset( $_GET['edit_override'] ) ): ?>

        <?php include WPTO_PLUGIN_PATH . 'inc/admin_parts/edit-override.php'; ?>

    <?php else: ?>

        <h1><?php _e( 'WP Template Overrides', 'wp-template-overrides' ); ?></h1>

        <h2 class="nav-tab-wrapper">
            <?php
            $tabs = array(
                'list-overrides' => __( 'List Overrides', 'wp-template-overrides' ),
                'new-override'   => __( 'New Override', 'wp-template-overrides' )
            );

            foreach( $tabs as $tab => $text ){
                $class = ( $curpage === $tab ) ? 'nav-tab nav-tab-active' : 'nav-tab' ;
                echo '<a href="' . esc_attr( add_query_arg( 'subpage', $tab ) ) . '" class="' . esc_attr( $class ) . '">' . esc_html( $text ) . '</a>';
            }
            ?>
        </h2>
        <?php

        switch( $curpage ):

            case 'list-overrides':
                include WPTO_PLUGIN_PATH . 'inc/admin_parts/list-overrides.php';
                break;

            case 'new-override':
                include WPTO_PLUGIN_PATH . 'inc/admin_parts/new-override.php';
                break;

        endswitch;

    endif; ?>



</div>