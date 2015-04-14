<?php
/**
 * Created on Oct 13, 2014
 *
 * @category    GCM Config
 * @package     Admin GCM
 * @author      Bobcares
 *
 */

$path = dirname( dirname( dirname( dirname( __FILE__ ) ) ) );
 
// define the gc displayed per page
define('GC_DISPLAYED_PER_PAGE', 5);

$gcOptionsArray = get_option("gc_options");
$fromEmail = $gcOptionsArray['admin_email'];
$adminEmail = get_option('admin_email');

// GC report mail subject
define('GC_MAIL_SUBJECT', 'Gift Certificate Submission');
define('GC_MAIL_TO', $adminEmail);
define('GC_MAIL_FROM', $fromEmail);
define('GC_MAIL_FROM_NAME', 'Admin');
?>
