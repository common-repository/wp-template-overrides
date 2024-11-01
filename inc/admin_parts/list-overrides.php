<?php
/**
 * List Overrides Page For Wp Template Overrides
 */
require_once WPTO_PLUGIN_PATH . 'inc/class-wpto-listtable.php';

$wpto_listtable = new WPTO_List_Table();
$wpto_listtable->prepare_items();

$wpto_listtable->display();