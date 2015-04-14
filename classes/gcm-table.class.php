<?php
/**
 * Created on Oct 6, 2014
 *
 * @category    GC Table List
 * @package     GC List
 * @author      Bobcares
 *
 */

/**
 * The class contains all functions used for gc list table management
 *
 * @category    GC Table List
 * @package     GC List
 * @author      Bobcares
 * @license
 * @link
 *
 */
class GCListTable extends WP_List_Table {

    /**
     * checkbox needed for mass actions
     * @var bool
     */
    var $checkbox = false;

    /**
     * constructor of the class
     * @param unknown_type $args
     */
    function __construct( $args = array() ) {
        parent::__construct( array(
            'plural' => 'gcs',
            'singular' => 'gc',
            'ajax' => true,
            'screen' => 1,
        ) );
    }

    /**
     * functions to prepare items required for table display
     */
    function prepareItems() {
        
        global $search;
        $search = ( isset( $_REQUEST['s'] ) ) ? $_REQUEST['s'] : '';
        $orderby = ( isset( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : '';
        $order = ( isset( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : '';
        $gcPerPage = $this->getPerPage();
        $doingAjax = defined( 'DOING_AJAX' ) && DOING_AJAX;

        // per page number
        if ( isset( $_REQUEST['number'] ) ) {
            $number = (int) $_REQUEST['number'];
        } else {
            $number = $gcPerPage + min( 8, $gcPerPage );
        }

        $page = $this->get_pagenum();

        // start number
        if ( isset( $_REQUEST['start'] ) ) {
            $start = $_REQUEST['start'];
        } else {
            $start = ( $page - 1 ) * $gcPerPage;
        }

        // if ajax request
        if ( $doingAjax && isset( $_REQUEST['offset'] ) ) {
            $start += $_REQUEST['offset'];
        }

        $args = array(
            'search' => $search,
            'offset' => $start,
            'number' => $number,
            'orderby' => $orderby,
            'order' => $order,
        );

        // get all gc list according to teh conditions
        $gcObj = new GCM();
        $gcList = $gcObj->getAllGCList($args);        
        $this->items = array_slice( $gcList, 0, $gcPerPage );
        $this->extra_items = array_slice( $gcList, $gcPerPage );
        $totalGC = $gcObj->getAllGCList( array_merge( $args, array('count' => true, 'offset' => 0, 'number' => 0) ) );
        $this->set_pagination_args( array(
            'total_items' => $totalGC,
            'per_page' => $gcPerPage,
        ));
    }

    /**
     * function to get per page count
     * @return mixed Per page count
     */
    function getPerPage() {
        return GC_DISPLAYED_PER_PAGE;
    }

    /**
     * (non-PHPdoc)
     * @see WP_List_Table::no_items()
     */
    function no_items() {
        echo 'No Gift Certificates Found.';
    }

    /**
     * (non-PHPdoc) 
     * @see WP_List_Table::current_action() 
     */
    function current_action() {
        return parent::current_action();
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_columns()
	 */
	function get_columns() {
		$columns = array();
		$columns['user_name'] = 'Name';
		$columns['receip_name'] = 'Gift Name';
		$columns['cert_amount'] = 'Amount';
		/*$columns['cc_number'] = 'CC #';
		$columns['cc_exp'] = 'CC Exp'; */
		$columns['cc_sec_code'] = 'Email';
		$columns['receip_address'] = 'Address';
		return $columns;
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_sortable_columns()
	 */
	function get_sortable_columns() {
		return array(
			'user_name'   => 'user_name',
			'receip_name' => 'receip_name',
            'cert_amount' => 'cert_amount',
		);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::display_tablenav()
	 */
	function display_tablenav( $which ) {
	    ?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">
	        <?php
			$this->extra_tablenav( $which );
			$this->pagination( $which );
	        ?>
			<br class="clear" />
		</div>
	    <?php
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::display()
	 */
	function display() {
		$this->display_tablenav('top' );
		$this->count = 1;
		?>
    	<table class="<?php echo implode( ' ', $this->get_table_classes() ); ?>">
    		<thead>
    			<tr>
    				<?php $this->print_column_headers(); ?>
    			</tr>
    		</thead>
    
    		<tfoot>
    			<tr>
    				<?php $this->print_column_headers( false ); ?>
    			</tr>
    		</tfoot>
    
    		<tbody id="the-comment-list" data-wp-lists="list:comment">
    			<?php $this->display_rows_or_placeholder(); ?>
    		</tbody>
    
    		<tbody id="the-extra-comment-list" data-wp-lists="list:comment" style="display: none;">
    			<?php $this->items = $this->extra_items; $this->display_rows(); ?>
    		</tbody>
    	</table>
    	<?php
	    $this->display_tablenav( 'bottom' );
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::single_row()
	 */
	function single_row($gcRow) {
		echo "<tr id='gc-$gcRow->id'>";
	    $this->single_row_columns($gcRow);
		echo "</tr>\n";
	}

	/**
	 * Function to print user_name column
	 * @param object $gcRow    The gc row object
	 * @return String column value
	 */
	function column_user_name( $gcRow ) {
		return $gcRow->user_name;
	}

	/**
	 * Function to print receip_name column
	 * @param object $gcRow    The gc row object
	 * @return String column value
	 */
	function column_receip_name( $gcRow ) {
		return $gcRow->receip_name;
	}

	/**
	 * Function to print cert amount column
	 * @param object $gcRow    The gc row object
	 * @return String column value
	 */
	function column_cert_amount( $gcRow ) {
		return "$" . $gcRow->cert_amount;
	}

	/**
	 * Function to print cc # column
	 * @param object $gcRow    The gc row object
	 * @return String column value
	 */
	function column_cc_number( $gcRow ) {
		//return "xxxxxxxx" . substr($gcRow->cc_number, -4);
		return $gcRow->cc_number;
	}

	/**
	 * Function to print cc_exp column
	 * @param object $gcRow    The gc row object
	 * @return String column value
	 */
	function column_cc_exp($gcRow ) {
		return $gcRow->cc_exp;
	}

	/**
	 * Function to print cc_sec_code column
	 * @param object $gcRow    The gc row object
	 * @return String column value
	 */
	function column_cc_sec_code( $gcRow ) {
		return $gcRow->cc_sec_code;
	}
	
	/**
	 * Function to print cc_sec_code column
	 * @param object $gcRow    The gc row object
	 * @return String column value
	 */
	function column_receip_address( $gcRow ) {
		return $gcRow->receip_address;
	}
}
?>
