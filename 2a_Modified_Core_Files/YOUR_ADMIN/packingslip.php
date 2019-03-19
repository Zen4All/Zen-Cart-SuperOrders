<?php
/**
 * @package admin
 * @copyright Copyright 2003-2010 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
//////////////////////////////////////////////////////////////////////////
//  Based on Super Order 2.0                                        	//
//  By Frank Koehl - PM: BlindSide (original author)                	//
//                                                                  	//
//  Super Orders Updated by:						//
//  ~ JT of GTICustom							//
//  ~ C Jones Over the Hill Web Consulting (http://overthehillweb.com)	//
//  ~ Loose Chicken Software Development, david@loosechicken.com	//
//////////////////////////////////////////////////////////////////////////
//  DESCRIPTION: Replaces admin/packingslip.php adding the following 	//
//  features:								//
//  ~ Ability to display a special "split" packingslip when an order	//
//    has been split. (Split orders feature is accessible through the	//
//    order details page)					        //
//  ~ Admin configurable product image display				//
//  ~ Properly aligned address info.					//
//////////////////////////////////////////////////////////////////////////
 * @version $Id: packingslip.php 15788 2010-04-02 10:44:40Z drbyte $
*/

  require_once('includes/application_top.php');
  require_once(DIR_WS_CLASSES . 'order.php');
  require_once(DIR_WS_CLASSES . 'super_order.php');
  require_once(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

global $db;

// AJB 2012-05-31 - start (1)

// if(isset($_GET['oID']))

// $oID = zen_db_prepare_input($_GET['oID']);

if(isset($_GET['oID'])) {
	$oID = zen_db_prepare_input($_GET['oID']);
	$batched = false;
	$batch_item = 0;
} else {
	$batched = true;
}

// AJB 2012-05-31 - end (1)
  $orders = $db->Execute("select orders_id
                          from " . TABLE_ORDERS . "
                          where orders_id = '" . (int)$oID . "'");

 //include(DIR_WS_CLASSES . 'order.php');

  $so = new super_order($oID);
  $order = new order($oID);

  // prepare order-status pulldown list
  $orders_statuses = array();
  $orders_status_array = array();

// SUPER_CODE_START
  $reverse_split = ( ($_GET['reverse_count'] % 2) ? 'odd' : 'even' );
  $_GET['reverse_count']++;
  $split = $_GET['split'];

// Add product images based on admin configuration settings
	$display_images = (PACKINGSLIP_IMAGES == 'Yes' ? True : False);

// Find any comments entered at checkout and display on packingslip if they exist
  $orders_status = $db->Execute("select orders_status_id, orders_status_name
                                 from " . TABLE_ORDERS_STATUS . "
                                 where language_id = '" . (int)$_SESSION['languages_id'] . "'");
  while (!$orders_status->EOF) {
    $orders_statuses[] = array('id' => $orders_status->fields['orders_status_id'],
                               'text' => $orders_status->fields['orders_status_name'] . ' [' . $orders_status->fields['orders_status_id'] . ']');
    $orders_status_array[$orders_status->fields['orders_status_id']] = $orders_status->fields['orders_status_name'];
    $orders_status->MoveNext();
  }
// SUPER_CODE_END

?>

<?php // AJB 2012-05-31 - start (2) // ?>

<?php if ($batched == false) {
	$page_title = HEADER_PACKINGSLIP . (int)$oID;
} else {
	$page_title = HEADER_PACKINGSLIPS;
}
?>

<?php if (($batched == false) or ($batched == true and $batch_item == 1)) { ?>

<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo $page_title; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<style>
@media screen {
div.form-separator {border-style:none none solid none;	border-bottom:thick dotted #000000;;}
}

@media print {
div.form-separator {display: none;}
}
</style>
<script language="javascript" src="includes/menu.js"></script>
</head>

<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">

<?php } ?>

<?php // AJB 2012-05-31 - end (2) // ?>

<?php // AJB 2012-05-31 - start (3) // ?>

<?php if (($batched == true) and ($batch_item > 1) and (($batch_item % $forms_per_page) == 0)) { ?>
<div style="page-break-before:always"><span style="display: none;">&nbsp;</span></div>
<br />
<?php } ?>

<div class="form-separator"></div>
<?php // AJB 2012-05-31 - end (3) // ?>

<!-- body_text //-->
<div>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td colspan="6">
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
      		<tr>
      			  <td class="pageHeading"><?php echo nl2br(STORE_NAME_ADDRESS); ?></td>
     			   <td class="pageHeading" align="right"><a href="<?php echo FILENAME_PACKINGSLIP . '?' . zen_get_all_get_params(); ?>"><?php echo zen_image(DIR_WS_IMAGES . HEADER_LOGO_IMAGE, HEADER_ALT_TEXT)?></a><br><?php echo TEXT_PACKING_SLIP; ?></td>
      		</tr>
    	</table>
	</td>
  </tr>
 <tr>
 	<td colspan="6">
		<?php echo zen_draw_separator(); ?>
	</td>
 </tr>
  <tr>
    <td>
		<table border="0" cellpadding="2" cellspacing="0" width="100%">
    	  <tr>
      	 	</tr>
<?php
      $order_check = $db->Execute("SELECT cc_cvv, customers_name, customers_company, customers_street_address,
                                    customers_suburb, customers_city, customers_postcode,
                                    customers_state, customers_country, customers_telephone,
                                    customers_email_address, customers_address_format_id, delivery_name,
                                    delivery_company, delivery_street_address, delivery_suburb,
                                    delivery_city, delivery_postcode, delivery_state, delivery_country,
                                    delivery_address_format_id, billing_name, billing_company,
                                    billing_street_address, billing_suburb, billing_city, billing_postcode,
                                    billing_state, billing_country, billing_address_format_id,
                                    payment_method, cc_type, cc_owner, cc_number, cc_expires, currency,
                                    currency_value, date_purchased, orders_status, last_modified" . $additional_columns . "
                                    FROM " . TABLE_ORDERS . "
                                    WHERE orders_id = '" . (int)$oID . "'");
  $show_customer = 'false';

  if ($order_check->fields['billing_name'] != $order_check->fields['delivery_name']) {
    $show_customer = 'true';
  }

  if ($order_check->fields['billing_street_address'] != $order_check->fields['delivery_street_address']) {
    $show_customer = 'true';
  }

  if ($show_customer == 'true') {
?>
      <tr>
	  	<td colspan="6">
       	<table cellpadding="2" cellspacing="2" border="0">
			<tr>
				 <td><?php echo zen_draw_separator('pixel_trans.gif', '25', '1'); ?></td>
				<td valign="top"><table border="0" cellpadding="2" cellspacing="0" width="100%">
				  <tr>
					<td class="main"><strong><?php echo ENTRY_SHIP_TO; ?></strong></td>
				  </tr>
				  <?php }?>
				  <tr>
					<td class="main"><?php echo zen_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br>'); ?></td>
				  </tr>
				  <tr>
					<td><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
				  </tr>
				  <tr>
					<td class="main"><?php echo $order->customer['telephone']; ?></td>
				  </tr>
				  <tr>
					<td class="main"><?php echo '<a href="mailto:' . $order->customer['email_address'] . '">' . $order->customer['email_address'] . '</a>'; ?></td>
				  </tr>
				</table></td>
				<td><?php echo zen_draw_separator('pixel_trans.gif', '175', '1'); ?></td>
				<td align="right" valign="top"><table border="0" cellpadding="2" cellspacing="0" width="100%">
				  <tr>
					<td class="main"><strong><?php echo ENTRY_SOLD_TO; ?></strong></td>
				  </tr>
				  <tr>
					<td class="main"><?php echo zen_address_format($order->customer['format_id'], $order->billing, 1, '', '<br>'); ?></td>
				  </tr>
				</table></td>
			</tr>
		</table>
		</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
  </tr>

<?php
  // Trim shipping details
  for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
	if ($order->totals[$i]['class'] == 'ot_shipping') {
	  $shipping_method = $order->totals[$i]['title'];
	  break;
	}
  }
?>
 <tr>
    <td><table border="0" cellpadding="2" cellspacing="0">
      <tr>
        <td class="main"><strong><?php echo ENTRY_ORDER_ID . $oID; ?></strong></td>
      </tr>
      <tr>
        <td class="main"><strong><?php echo ENTRY_DATE_PURCHASED; ?></strong></td>
        <td class="main"><?php echo zen_date_long($order->info['date_purchased']); ?></td>
      </tr>
      <tr>
        <td class="main"><strong><?php echo ENTRY_PAYMENT_METHOD; ?></strong></td>
        <td class="main"><?php echo $order->info['payment_method']; ?></td>
      </tr>
      <tr>
        <td class="main"><strong><?php echo ENTRY_SHIPPING_METHOD; ?></strong></td>
        <td class="main"><?php echo $shipping_method; ?></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
  </tr>
  <tr>
    <td><table border="0" cellpadding="2" cellspacing="0" width="100%">
      <tr class="dataTableHeadingRow">
<?php

//  SUPER_CODE_START
      if ($display_images) { ?>
        <td class="dataTableHeadingContent" width="10%"><?php echo TABLE_HEADING_IMAGE; ?></td>
        <td class="dataTableHeadingContent" width="10%"><?php echo TABLE_HEADING_QTY; ?></td>
        <td class="dataTableHeadingContent" width="40%"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
      <?php } else { ?>
        <td class="dataTableHeadingContent" width="10%"><?php echo TABLE_HEADING_QTY; ?></td>
        <td class="dataTableHeadingContent" width="50%"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
      <?php } ?>
        <td class="dataTableHeadingContent" width="40%"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
      </tr>

<?php
      for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
        echo '      <tr class="dataTableRow">' . "\n";
        if ($display_images && isset($order->products[$i]['id']) ) {
          $products = $db->Execute("SELECT products_image
                                    FROM " . TABLE_PRODUCTS . "
                                    WHERE products_id ='" . $order->products[$i]['id'] . "'");
          echo '        <td class="dataTableContent" valign="middle">' . zen_image(DIR_WS_CATALOG . DIR_WS_IMAGES . $products->fields['products_image'] , $order->products[$i]['name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a></td>';
        }
        echo '        <td class="dataTableContent" valign="middle">';
		echo zen_image(DIR_WS_ICONS . 'tick.gif', ICON_TICK);
        echo $order->products[$i]['qty'] . '&nbsp;</td>' . "\n" .
             '        <td class="dataTableContent" valign="middle">' . $order->products[$i]['name'];

        if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) {
          for ($j=0, $k=sizeof($order->products[$i]['attributes']); $j<$k; $j++) {
            echo '<br><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . nl2br(zen_output_string_protected($order->products[$i]['attributes'][$j]['value']));
            echo '</i></small></nobr>';
          }
        }
        echo '        </td>' . "\n" .
             '        <td class="dataTableContent" valign="middle">' . $order->products[$i]['model'] . '</td>' . "\n" .
             '      </tr>' . "\n";
      }
?>

<?php
 $parent_child= $db->Execute("select split_from_order, is_parent
                                     	 from " . TABLE_ORDERS . "
                                      where orders_id = '" . $oID . "'");
if($parent_child->fields['split_from_order']):

$so = new super_order($parent_child->fields['split_from_order']);
  $order = new order($parent_child->fields['split_from_order']);
?>

<?php
      for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
        echo '      <tr class="dataTableRow">' . "\n";
        if ($display_images && isset($order->products[$i]['id']) ) {
          $products = $db->Execute("SELECT products_image
                                    FROM " . TABLE_PRODUCTS . "
                                    WHERE products_id ='" . $order->products[$i]['id'] . "'");
          echo '        <td class="dataTableContent" valign="middle">' . zen_image(DIR_WS_CATALOG . DIR_WS_IMAGES . $products->fields['products_image'] , $order->products[$i]['name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a>&nbsp;</td>';
        }
        echo '        <td class="dataTableContent" valign="middle">';
		echo zen_image(DIR_WS_ICONS . 'cross.gif', ICON_CROSS);
        echo $order->products[$i]['qty'] . '&nbsp;</td>' . "\n" .
             '        <td class="dataTableContent" valign="middle">' . $order->products[$i]['name'];
        if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) {
          for ($j=0, $k=sizeof($order->products[$i]['attributes']); $j<$k; $j++) {
            echo '<br><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'];
            echo '</i></small></nobr>';
          }
        }
        echo '        </td>' . "\n" .
             '        <td class="dataTableContent" valign="middle">' . $order->products[$i]['model'] . '</td>' . "\n" .
             '      </tr>' . "\n";
      }
?>

<?php
endif;
?>
    </table></td>
  </tr>

<?php if (ORDER_COMMENTS_PACKING_SLIP > 0) { ?>
      <tr>
        <td class="main"><table border="0" cellspacing="0" cellpadding="5">
          <tr>
            <td class="smallText" align="left"><strong><?php echo TABLE_HEADING_DATE_ADDED; ?></strong></td>
            <td class="smallText" align="left"><strong><?php echo TABLE_HEADING_STATUS; ?></strong></td>
            <td class="smallText" align="left"><strong><?php echo TABLE_HEADING_COMMENTS; ?></strong></td>
          </tr>

<?php
    $orders_history = $db->Execute("select orders_status_id, date_added, customer_notified, comments
                                    from " . TABLE_ORDERS_STATUS_HISTORY . "
                                    where orders_id = '" . zen_db_input($oID) . "' and customer_notified >= 0
                                    order by date_added");
    if ($orders_history->RecordCount() > 0) {
      $count_comments=0;
      while (!$orders_history->EOF) {
        $count_comments++;
        echo '          <tr>' . "\n" .
             '            <td class="smallText" valign="top" align="left">' . zen_datetime_short($orders_history->fields['date_added']) . '</td>' . "\n";
        echo '            <td class="smallText" valign="top" align="left">' . $orders_status_array[$orders_history->fields['orders_status_id']] . '</td>' . "\n";
        echo '            <td class="smallText" valign="top" align="left">' . ($orders_history->fields['comments'] == '' ? TEXT_NONE : nl2br(zen_db_output(stripslashes($orders_history->fields['comments'])))) . '&nbsp;</td>' . "\n" .
             '          </tr>' . "\n";
        $orders_history->MoveNext();

        if (ORDER_COMMENTS_PACKING_SLIP == 1 && $count_comments >= 1) {
     //    break;
        }
      }
    } else {
        echo '          <tr>' . "\n" .
             '            <td class="smallText" colspan="5">' . TEXT_NO_ORDER_HISTORY . '</td>' . "\n" .
             '          </tr>' . "\n";
    }
?>
        </table></td>
      </tr>

<?php } // order comments ?>

<?php if ($_GET['split']) { ?>
  <tr>
    <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
  </tr>
  <tr>
    <td align="right"><table border="0" cellpadding="2" cellspacing="0">
      <tr>
        <td class="smallText"><?php echo zen_image(DIR_WS_ICONS . 'tick.gif', ICON_TICK); ?></td>
        <td class="smallText"><?php echo ENTRY_PRODUCTS_INCL; ?></td>
      </tr>
      <tr>
        <td class="smallText"><?php echo zen_image(DIR_WS_ICONS . 'cross.gif', ICON_CROSS); ?></td>
        <td class="smallText"><?php echo ENTRY_PRODUCTS_EXCL; ?></td>
      </tr>
    </table></td>
  </tr>

<?php } ?>
</table>
</div>

<!-- body_text_eof //-->
<br>

<?php // AJB 2012-05-31 - start (4) // ?>

<?php if (($batched == false) or (($batched == true) and ($batch_item == $number_of_orders))) { ?>

</body>
</html>

<?php } ?>

<?php // AJB 2012-05-31 - end (4) // ?>

<?php require_once(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
