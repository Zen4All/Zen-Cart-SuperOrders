<?php
/**
 *
 *  SUPER ORDERS v3.0
 *
 *  Based on Super Order 2.0
 *  By Frank Koehl - PM: BlindSide (original author)
 *
 *  Super Orders Updated by:
 *  ~ JT of GTICustom
 *  ~ C Jones Over the Hill Web Consulting (http://overthehillweb.com)
 *  ~ Loose Chicken Software Development, david@loosechicken.com
 *
 *  Powered by Zen-Cart (www.zen-cart.com)
 *  Portions Copyright (c) 2005 The Zen-Cart Team
 *
 *  Released under the GNU General Public License
 *  available at www.zen-cart.com/license/2_0.txt
 *  or see "license.txt" in the downloaded zip
 *
 *  DESCRIPTION:   Generates an order shipping label
 *
 * $Id: super_batch_forms.php v 2010-10-24 $
 */
require_once 'includes/application_top.php';
require_once DIR_WS_CLASSES . 'currencies.php';
$currencies = new currencies();

// AJB 2012-05-31 - start (1)
// if(isset($_GET['oID']))
// $oID = zen_db_prepare_input($_GET['oID']);

if (isset($_GET['oID'])) {
  $oID = zen_db_prepare_input($_GET['oID']);
  $batched = false;
  $batch_item = 0;
} else {
  $batched = true;
}

require_once(DIR_WS_CLASSES . 'order.php');
$order = new order($oID);
?>

<?php
if ($batched == false) {
  $page_title = HEADER_SHIPPINGLABEL . (int)$oID;
} else {
  $page_title = HEADER_SHIPPINGLABELS;
}
?>

<?php if (($batched == false) or ( $batched == true and $batch_item == 1)) { ?>
  <!doctype html>
  <html <?php echo HTML_PARAMS; ?>>
    <head>
      <meta charset="<?php echo CHARSET; ?>">
      <title><?php echo $page_title; ?></title>
      <link rel="stylesheet" href="includes/stylesheet.css">
      <style>
        @media screen {
            div.form-separator {border-style:none none solid none;  border-bottom:thick dotted #000000;;}
        }
        @media print {
            div.form-separator {display: none;}
        }
      </style>
    </head>

    <body>

    <?php } ?>

    <?php if (($batched == true) && ( $batch_item > 1) && ( ($batch_item % $forms_per_page) == 0)) { ?>
      <div style="page-break-before:always"><span style="display: none;">&nbsp;</span></div>
      <br>
    <?php } ?>

    <div class="form-separator"></div>

    <!-- body_text //-->
    <div>
      <table border="0" width="100%" cellspacing="4" cellpadding="4">
        <tr>
          <td>
            <table border="0" width="100%" cellspacing="4" cellpadding="4">
              <tr>
                <td class="main"><?php echo nl2br(STORE_NAME_ADDRESS); ?></td>
              </tr>
            </table>
            <table border="0" width="100%" cellspacing="2" cellpadding="2">
              <tr>
                <td valign="top">
                  <table width="100%" border="0" cellspacing="2" cellpadding="2">
                    <tr>
                      <td width="20%"><?php echo $oID; ?></td>
                      <td class="pageHeading"><?php echo zen_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br>'); ?></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </div>

    <?php if (($batched == false) || ( ($batched == true) && ( $batch_item == $number_of_orders))) { ?>
    </body>
  </html>
<?php
}