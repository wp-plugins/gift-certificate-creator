<?php
/**
 * Created on Oct 13, 2014
 *
 * @category    GCM
 * @package     Admin GCM
 * @author      Bobcares
 *
 */

/**
 * The class contains all functions used for gc management
 *
 * @category	GCM
 * @package   	GCM Functions
 * @author      Bobcares
 * @license
 * @link
 *
 */
class GCM {

    /**
     * function to get all gc lists or count of list
     * @param Array $args    The arguments required to select gc list
     * @return Array/count    The gc list or count of gclist rows
     */
    function getAllGCList($args = array()) {
        global $wpdb;        
        $number = absint($args['number']);
        $offset = absint($args['offset']);        
        
        // if number is not empty
        if (!empty($number) && empty($args['count']) ) {
            $limits = !empty($offset) ? "LIMIT $offset, $number" : "LIMIT $number";
        } else {
            $limits = '';
        }
        
        // fields of sql
        $fields = !empty($args['count']) ? 'COUNT(*) count' : '*';
        
        // whree consition of sql
        $where = "1=1";
        $where .= !empty($args['search']) ? " AND (user_name like '%".addslashes($args['search'])."%' or receip_name like '%".addslashes($args['search'])."%')" : "";
        
        // order parameters
        $order = ('DESC' == strtoupper($args['order']) ) ? 'ASC' : 'DESC';
        $orderby = !empty($args['orderby']) ? $args['orderby'] : 'user_name';
        
        // create sql
        $dbTable = $wpdb->prefix . "gift_certificates";
        $sql = "SELECT $fields FROM $dbTable WHERE $where $groupby ORDER BY $orderby $order $limits";
        
        // check whether count or list needed
        if (!empty($args['count'])) {
            $countInfo = $wpdb->get_row($sql, OBJECT);
            return  $countInfo->count;
        } else {
            $gcList = $wpdb->get_results($sql, OBJECT );
            return $gcList;
        }
        
    }
    
    /**
     * function to create new gift certificate
     * @param $gcInfo     The array contains gift certificate info
     */
    function createNewGCM($gcInfo) {
        global $wpdb;
        $sql = "INSERT INTO $wpdb->prefix" . "gift_certificates(cert_amount, user_name, receip_name, cc_number, cc_exp, cc_sec_code, receip_address) 
        values('{$gcInfo['cert_amount']}', '{$gcInfo['user_name']}', '{$gcInfo['receip_name']}', 
        '{$gcInfo['cc_number']}', '{$gcInfo['cc_exp_month']}-{$gcInfo['cc_exp_year']}', '{$gcInfo['cc_sec_code']}', '{$gcInfo['receip_address']}')";
        $wpdb->query($sql);
    }
    
    /**
     * function to send gc reports to users
     * @param Array $toEmail         The to address
     * @param String $fromEmail      The from address
     * @param String $subject        The subject of mail
     * @param String $gcInfo        The gclist items and details
     */
    function sendGCMReportEmail($toEmail, $fromEmail, $subject, $gcInfo) {
        
        $headers[] = "From: Admin <$fromEmail>";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        ob_start();
        ?>
        Hi Admin,
        <br>
        <br>
        Please check submitted gift certification details below
        <br>
        <br>
        <style>
        table {
            border-collapse: collapse;
            font-size: 12px;
            width: 60%;
        }
        table, td, th {
            border: 1px solid  #E1E1E1;
             color: #555555;
        }
        th, td {
            padding: 5px 10px;
        }
        </style>
        <table class="gc_report">
            <tr style="background-color: ">
                <th>#</th>
                <th>Name</th>
                <th>Receipient Name</th>
                <th>Amount</th>
            </tr>
            <tr style='<?php echo $style; ?>'>
                <td align="center">1</td>
                <td><?php echo $gcInfo['user_name']; ?></td>
                <td align="center"><?php echo $gcInfo['receip_name']; ?></td>
                <td align="center"><?php echo $gcInfo['cert_amount']; ?></td>
            </tr>
        </table>
        <?php
        $message = ob_get_contents();
        ob_end_clean();
        wp_mail($toEmail, $subject, $message, $headers);
    }
    
}
