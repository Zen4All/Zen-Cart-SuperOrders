<?php

/**
 * @package admin
 *
 *  Based on Super Order 2.0
 *  By Frank Koehl - PM: BlindSide (original author)
 *
 *  Super Orders Updated by:
 *  ~ JT of GTICustom
 *  ~ C Jones Over the Hill Web Consulting (http://overthehillweb.com)
 *  ~ Loose Chicken Software Development, david@loosechicken.com
 *
 *
 *  DESCRIPTION: Replaces admin/invoice.php, adds amount paid &
 *  balance due values based on super_order class calculations.  Also
 *  includes the option to display a tax exemption number, configurable
 *  from the admin.
 *
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: invoice.php 19136 2011-07-18 16:56:18Z wilt $
 */
require 'includes/application_top.php';
require DIR_WS_CLASSES . 'order.php';
require DIR_WS_CLASSES . 'currencies.php';
require_once DIR_WS_LANGUAGES . $_SESSION['language'] . '/orders.php';
require_once(DIR_WS_LANGUAGES . $_SESSION['language'] . '/invoice.php');
require_once(DIR_WS_LANGUAGES . $_SESSION['language'] . '/packingslip.php');
require_once(DIR_WS_LANGUAGES . $_SESSION['language'] . '/super_shipping_label.php');
require_once(DIR_WS_CLASSES . 'super_order.php');
$selected_oids = zen_db_prepare_input($_POST['batch_order_numbers']);
$target_file = zen_db_prepare_input($_POST['target_file']);
$_num_copies = zen_db_prepare_input($_POST['num_copies']);
unset($batch_order_numbers);
foreach ($selected_oids as $order_number => $print_order) {
  $batch_order_numbers[] = $order_number;
}
// begin error handling
if (!(is_array($batch_order_numbers))) {
  exit(ERROR_NO_ORDERS);
}

if (!(is_file($target_file))) {
  exit(ERROR_NO_FILE);
}
// end error handling

sort($batch_order_numbers);
$number_of_orders = sizeof($batch_order_numbers);
$total_rows = $number_of_orders * $_num_copies;

// AJB 2012-05-31 - start
switch ($target_file) {
  case FILENAME_SUPER_INVOICE . '.php':
    $forms_per_page = 1;
    break;
  case FILENAME_SUPER_PACKINGSLIP . '.php':
    $forms_per_page = 1;
    break;
  case FILENAME_SUPER_SHIPPING_LABEL . '.php':
    $forms_per_page = 4;
    break;
}

$batch_item = 0;
// AJB 2012-05-31 - end

foreach ($batch_order_numbers as $_order_number) {

  // AJB 2012-05-31 - start
  $batch_item = $batch_item + 1;
  // AJB 2012-05-31 - end

  $oID = $_order_number;
  require $target_file;
  if ($_num_copies > 1) {

    for ($_icpy = 1; $_icpy < $_num_copies; $_icpy++) {
      $oID = $_order_number;
      require $target_file;
    }
  }
}

require_once(DIR_WS_INCLUDES . 'application_bottom.php');
