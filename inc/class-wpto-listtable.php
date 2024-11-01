<?php
/**
 * @TODO
 */
class WPTO_List_Table extends WP_List_Table {

    /**
     * List Table constructor
     */
    function __construct(){
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'override',
            'plural'    => 'overrides',
            'ajax'      => false
        ) );
    }


    /**
     * Column Defaults.
     */
    function column_default($item, $column_name){
        switch($column_name){
            case 'file':
                return basename( $item['original_file'] );
                break;

            case 'theme':
                return $item['theme'];
                break;
            default:
        }
    }


    /**
     * Column Names
     */
    function column_name( $item ){

        $edit_url = add_query_arg( array(
            'edit_override' => $item['key']
        ));

        $delete_url = add_query_arg( array(
            'delete_template_override' => $item['key'],
            '_nonce' => wp_create_nonce( 'delete_template_override_' . $item['key'] )
        ) );

        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="%s">' . __( 'Edit'  , 'wp-template-overrides'   ) . '</a>', $edit_url ),
            'delete'    => sprintf('<a href="%s">' . __( 'Delete', 'wp-template-overrides'   ) . '</a>', $delete_url ),
        );

        //Return the title contents
        return sprintf('%1$s %2$s',
            /*$1%s*/ $item['name'],
            /*$2%s*/ $this->row_actions($actions)
        );
    }


    /**
     * Returns the columns available
     */
    function get_columns(){
        $columns = array(
            'name'  => 'Name',
            'file'  => 'File',
            'theme' => 'Theme'
        );
        return $columns;
    }

    /**
     * Prepare the items to be displayed
     */
    function prepare_items() {
        global $WP_Template_Overrides;

        $per_page = 20;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $current_page = $this->get_pagenum();

        $data = $WP_Template_Overrides->overrides;

        if( ! $data ){
            return false;
        }


        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');

        $total_items = count($data);

        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);

        $this->items = $data;

        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }

}