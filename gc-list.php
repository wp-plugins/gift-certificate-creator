<?php
/**
 * Created on Oct 13, 2014
 *
 * @category    GC List
 * @package     Admin GC
 * @author      Bobcares
 *
 */

//include require files
$dir = plugin_dir_path( __FILE__ );
require_once($dir.'classes/gcm-table.class.php');

$gcListTable = new GCListTable();
$pagenum = $gcListTable->get_pagenum();
$doaction = $gcListTable->current_action();
$gcObj =  new GCM();

// prepare items
$gcListTable->prepareItems();
$title = 'Gift Certificates';
?>
<div class="wrap">
	<h2>
		<?php 
		echo $title;
		
		// if search done
		if ( isset($_REQUEST['s']) && $_REQUEST['s'] ) {
	        echo '<span class="subtitle">' . sprintf( __( 'Search results for &#8220;%s&#8221;' ), wp_html_excerpt( esc_html( wp_unslash( $_REQUEST['s'] ) ), 50, '&hellip;' ) ) . '</span>';
        }
        ?>
	</h2>
	<?php
    $gcListTable->views();
    ?>
	<form id="gc-form" action="" method="get">
		<?php $gcListTable->search_box('Search GC', 'gc' ); ?>
		<input type="hidden" name="page" value="gc_list_page" />
		<input type="hidden" name="_total" value="<?php echo esc_attr( $gcListTable->get_pagination_arg('total_items') ); ?>" />
		<input type="hidden" name="_per_page" value="<?php echo esc_attr( $gcListTable->get_pagination_arg('per_page') ); ?>" />
		<input type="hidden" name="_page" value="<?php echo esc_attr( $gcListTable->get_pagination_arg('page') ); ?>" />
		<?php
		
		// if paged is set  
		if ( isset($_REQUEST['paged']) ) {
            ?>
		    <input type="hidden" name="paged" value="<?php echo esc_attr( absint( $_REQUEST['paged'] ) ); ?>" />
		    <?php 
        }
        
        $gcListTable->display(); 
        ?>
		
	</form>
</div>

<div id="ajax-response"></div>
