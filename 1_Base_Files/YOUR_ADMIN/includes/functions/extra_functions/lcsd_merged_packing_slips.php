<?php

/**
 *
 *  SUPER ORDERS v4.0.5
 *
 *  Based on Super Order 2.0
 *  By Frank Koehl - PM: BlindSide (original author)
 *
 *  Super Orders Updated by:
 *  ~ JT of GTICustom
 *  ~ C Jones Over the Hill Web Consulting (http://overthehillweb.com)
 *  ~ Loose Chicken Software Development, david@loosechicken.com
 *  ~ Zen4All, http://zen4all.nl
 *
 *  Powered by Zen-Cart (www.zen-cart.com)
 *  Portions Copyright (c) 2005 The Zen-Cart Team
 *
 *  Released under the GNU General Public License
 *  available at www.zen-cart.com/license/2_0.txt
 * or see "license.txt" in the downloaded zip
 *
 *  DESCRIPTION:   PDF version of super_packingslip.php,
 *  Features include:
 *  ~ Pricing information which supports tax included pricing options
 *    identical to super_invoices.php
 *  ~ Admin configurable options to use with forms that have a peel
 *    off mailing label in place of the default "Packing Slip" text
 *
 * $Id: lcsd_merged_packing_slips.php v 2015-12-30 $
 */

$display_tax = (TAX_ID_NUMBER == '' ? true : false);

function lcsd_merged_packingslips_master_list($selected_oids, $merge_selected_oids)
{

  if ($selected_oids == '' || $selected_oids == null) {
    return;
  }

  global $db;

  $orderwhere = '';
  foreach ($selected_oids as $order_number => $print_order) {
    if ($orderwhere != '') {
      $orderwhere .= ',';
    }
    $orderwhere .= $order_number;
  }
  if ($orderwhere != '') {
    $orderwhere = ' WHERE orders_id IN (' . $orderwhere . ') ';
  }

  $pdf = new PDF('P', 'pt', 'letter');
  $pdf->AddPage();

  if ($merge_selected_oids) {

    $customers = $db->Execute("SELECT MIN(o.orders_id), o.customers_id, o.delivery_name, o.delivery_street_address, o.delivery_postcode
                               FROM " . TABLE_ORDERS . " o
                               LEFT JOIN " . TABLE_CUSTOMERS . " AS c ON c.customers_id = o.customers_id
                               " . $orderwhere . "
                               GROUP BY o.customers_id, o.delivery_name, o.delivery_street_address, o.delivery_postcode
                               ORDER BY MIN(o.orders_id) ASC");

    foreach ($customers as $customer) {
      $orders = $db->Execute("SELECT o.orders_id, o.customers_id
                              FROM " . TABLE_ORDERS . " o
                              LEFT JOIN " . TABLE_CUSTOMERS . " c ON c.customers_id = o.customers_id
                              " . $orderwhere . "
                              AND o.customers_id = " . (int)$customer['customers_id'] . "
                              AND o.delivery_name = '" . zen_db_input($customer['delivery_name']) . "'
                              AND o.delivery_street_address = '" . zen_db_input($customer['delivery_street_address']) . "'
                              AND o.delivery_postcode = '" . zen_db_input($customer['delivery_postcode']) . "'
                              ORDER BY o.orders_id ASC");

      $orderArray = array();
      foreach ($orders as $item) {
        $OrderID = $item['orders_id'];
        $order = new order($OrderID);
        /* order object doesn't include orderid.  added id to info so we can retrieve it */
        $order->info['id'] = $OrderID;
        $orderArray[] = $order;
      }
      draw_packing_slip_master_list_customer($orderArray, $pdf);
    }
  } else {
    $totalListQty = 0;
    $orders = $db->Execute("SELECT o.orders_id, o.customers_id
                            FROM " . TABLE_ORDERS . " o
                            LEFT JOIN " . TABLE_CUSTOMERS . " c ON c.customers_id = o.customers_id
                            " . $orderwhere . "
                            ORDER BY o.orders_id ASC ");

    foreach ($orders as $item) {
      $OrderID = $item['orders_id'];
      $order = new order($OrderID);
      /* order object doesn't include orderid.  added id to info so we can retrieve it */
      $order->info['id'] = $OrderID;

      draw_packing_slip_master_list_customer(array($order), $pdf);
    }
    //at end of loop, make a double line and finish it off.
    $pdf->Line(18, $pdf->GetY(), 594, $pdf->GetY());
    $pdf->Line(18, $pdf->GetY(), 594, $pdf->GetY());
    $qtyMsg = ('Total items: ' . $totalListQty);
    $pdf->Cell(5, 12, $qtyMsg);
  }

  $pdf->Output();
}

function draw_packing_slip_master_list_customer($orderArray, &$pdf)
{
  $order = $orderArray[0];

  $customerName = $order->delivery['name'];
  if ($order->delivery['street_address'] == '') { /* this is to match the merged packingslips record */
    $customerName = $order->customer['name'];
  }

  $totalQty = 0;
  foreach ($orderArray as $order) {
    for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
      $totalQty = $totalQty + $order->products[$i]['qty'];
    }
  }
  if ($totalQty == 1) {
    $headingLine = $customerName . ' (' . $totalQty . ' piece)';
  } else {
    $headingLine = $customerName . ' (' . $totalQty . ' pieces)';
  }

  $shipTo = zen_address_format($order->delivery['format_id'], $order->delivery, 0, '', "\n");
  if ($order->delivery['street_address'] == '') {
    $shipTo = zen_address_format($order->customer['format_id'], $order->customer, 0, '', "\n");
  }
  $orderRightBox = $shipTo;
  $pdf->SetFontSize('6');
  $pdf->Cell('580', '10', $orderRightBox, '', '', 'R');
  $pdf->Line(18, $pdf->GetY(), 594, $pdf->GetY());
  $pdf->Ln(2);
  $pdf->SetFontSize('10');
  $pdf->Cell(10, 10, '', 'LRTB');
  $pdf->Cell(200, 12, $headingLine, '', '0');
  $pdf->Ln(10);
  $pdf->MultiCell(566, 2, '');

  foreach ($orderArray as $order) {
    $orderText = '';
    for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
      if ($orderText != '') {
        $orderText = '';
      }
      $pdf->Ln(4);
      $pdf->Cell(27, 10, '');
      $pdf->Cell(8, 8, '', 'LRTB');
      $pdf->Cell(5, 0, '');
      $pdf->SetFontSize('6');
      $dateId = zen_date_short($order->info['date_purchased']) . '        #' . $order->info[id];

      $orderText .= ' ' . $order->products[$i]['qty'] . 'x ' . $order->products[$i]['name'] . '/' . $order->products[$i]['model'];
      $pdf->Cell(390, 10, $orderText, 0, 0);

      if ($previousOrderId == $order->info[id]) {
        $pdf->Ln(12);
      } else {
        $pdf->SetFontSize(10);
        $pdf->Cell(140, 10, $dateId, 0, 1, 'R');
        $pdf->SetFontSize(6);
      }

      if (sizeof($order->products[$i]['attributes']) > 0) {
        $attribCount = (sizeof($order->products[$i]['attributes']));

        $aC = 0;
        while ($aC < $attribCount) {
          $attribText = $order->products[$i]['attributes'][$aC]['option'] . ': ' . $order->products[$i]['attributes'][$aC]['value'];
          $pdf->Cell(40, 10, '');
          $pdf->Cell(530, 10, $attribText, 0, 1);
          $aC++;
        }
      }

      $previousOrderId = $order->info[id];
      $attribText = '';
    }
    $pdf->Ln(8);
  }
}

function lcsd_merged_packingslips($selected_oids, $merge_selected_oids)
{
  global $db;
  if ($selected_oids == '' || $selected_oids == null) {
    return;
  }

  require_once(DIR_WS_CLASSES . 'order.php');

  $orderwhere = '';

  foreach ($selected_oids as $order_number => $print_order) {
    if ($orderwhere != '') {
      $orderwhere .= ',';
    }
    $orderwhere .= $order_number;
  }

  if ($orderwhere != '') {
    $orderwhere = ' WHERE orders_id IN (' . $orderwhere . ') ';
  }

  $pdf = new PDF('P', 'pt', 'letter');

  // $pdf->AddPage('');

  if ($merge_selected_oids) {
    $customers = $db->Execute("SELECT MIN(o.orders_id), o.customers_id, o.delivery_name, o.delivery_street_address, o.delivery_postcode
                               FROM " . TABLE_ORDERS . " o
                               LEFT JOIN " . TABLE_CUSTOMERS . " c ON c.customers_id = o.customers_id
                               " . $orderwhere . "
                               GROUP BY o.customers_id, o.delivery_name, o.delivery_street_address, o.delivery_postcode
                               ORDER BY MIN(o.orders_id) ASC");

    while (!$customers->EOF) {
      $orders = $db->Execute("SELECT o.orders_id, o.customers_id
                              FROM " . TABLE_ORDERS . " AS o
                              JOIN " . TABLE_CUSTOMERS . " AS c ON c.customers_id = o.customers_id
                              " . $orderwhere . "
                              AND o.customers_id = " . (int)$customers->fields['customers_id'] . "
                              AND o.delivery_name = '" . zen_db_input($customers->fields['delivery_name']) . "'
                              AND o.delivery_street_address = '" . zen_db_input($customers->fields['delivery_street_address']) . "'
                              AND o.delivery_postcode = '" . zen_db_input($customers->fields['delivery_postcode']) . "'
                              ORDER BY o.orders_id ASC");
      $OrderID = $orders->fields['orders_id'];
      $order = new order($OrderID);
      /* order object doesn't include orderid.  added id to info so we can retrieve it */
      $order->info['id'] = $OrderID;
      $pdf->SetHeaderVars($order, $merge_selected_oids);
      $pdf->AddPage('');
      draw_packing_slip_customer($order, $pdf);
      draw_packing_slip_order($order, $pdf);
      $orders->MoveNext();

      while (!$orders->EOF) {
        $OrderID = $orders->fields['orders_id'];
        $order = new order($OrderID);
        /* order object doesn't include orderid.  added id to info so we can retrieve it */
        $order->info['id'] = $OrderID;
        $pdf->Ln(12);
        $pdf->SetHeaderVars($order, $merge_selected_oids);
        $pdf->AddPage('');
        draw_packing_slip_customer($order, $pdf);
        draw_packing_slip_order($order, $pdf);
        $orders->MoveNext();
      }

      $customers->MoveNext();
    }
  } else {

    $orders = $db->Execute("SELECT o.orders_id, o.customers_id
                            FROM " . TABLE_ORDERS . " o
                            LEFT JOIN " . TABLE_CUSTOMERS . " c ON c.customers_id = o.customers_id
                            " . $orderwhere . "
                            ORDER BY o.orders_id ASC");

    while (!$orders->EOF) {
      $OrderID = $orders->fields['orders_id'];

      $order = new order($OrderID);
      /* order object doesn't include orderid.  added id to info so we can retrieve it */
      $order->info['id'] = $OrderID;
      $pdf->SetHeaderVars($order, $merge_selected_oids);

      $pdf->AddPage('');
      draw_packing_slip_customer($order, $pdf);
      draw_packing_slip_order($order, $pdf);

      $orders->MoveNext();
    }
  }

  $pdf->Output();
}

function draw_packing_slip_customer($order, &$pdf)
{
  $billTo = zen_address_format($order->customer['format_id'], $order->billing, 0, '', "\n");
  if ($order->billing['street_address'] == '') {
    $billTo = zen_address_format($order->customer['format_id'], $order->customer, 0, '', "\n");
  }

  $shipTo = zen_address_format($order->delivery['format_id'], $order->delivery, 0, '', "\n");
  if ($order->delivery['street_address'] == '') {
    $shipTo = zen_address_format($order->customer['format_id'], $order->customer, 0, '', "\n");
  }

  $customerPhone = $order->customer['telephone'];
  $customerEmail = $order->customer['email_address'];

  $pdf->SetFont('Arial', '', 10);
  $billToY = $pdf->GetY();
  $pdf->MultiCell(220, 13, "Bill To: \n" . $billTo);
  $pdf->Ln(2);
  $pdf->MultiCell(220, 13, 'Phone: ' . $customerPhone);
  $pdf->MultiCell(220, 13, 'Email: ' . $customerEmail);
  $pdf->Ln(12);
  $EndY = $pdf->GetY();
  $pdf->SetXY(306, $billToY);
  $pdf->MultiCell(220, 13, "Ship To: \n" . $shipTo);
  $pdf->SetXY(18, $EndY);
}

function draw_packing_slip_order($order, &$pdf)
{
  global $db;

  require_once(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  // prepare order-status pulldown list
  $orders_statuses = array();
  $orders_status_array = array();
  $orders_status = $db->Execute("select orders_status_id, orders_status_name
                                 from " . TABLE_ORDERS_STATUS . "
                                 where language_id = '" . (int)$_SESSION['languages_id'] . "'");
  while (!$orders_status->EOF) {
    $orders_statuses[] = array('id' => $orders_status->fields['orders_status_id'],
      'text' => $orders_status->fields['orders_status_name'] . ' [' . $orders_status->fields['orders_status_id'] . ']');
    $orders_status_array[$orders_status->fields['orders_status_id']] = $orders_status->fields['orders_status_name'];
    $orders_status->MoveNext();
  }

  /* display order information header */
  $pdf->SetFont('Arial', 'B', 6);
  /* order object doesn't include orderid.  added id to info so we can retrieve it here */
  $pdf->Cell(120, 14, ENTRY_ORDER_ID . $order->info['id']);
  $pdf->Cell(236, 14, ENTRY_DATE_PURCHASED . ' ' . zen_date_long($order->info['date_purchased']));
  $pdf->MultiCell(220, 14, ENTRY_PAYMENT_METHOD . ' ' . $order->info['payment_method']);
  $pdf->SetFont('Arial', '', 10);

  if (TAX_ID_NUMBER != '') {
    $pdf->SetFillColor(180, 180, 180);
    $pdf->Cell(30, 14, TABLE_HEADING_QTY, 1, 0, '', 1);
    $pdf->Cell(160, 14, TABLE_HEADING_PRODUCTS, 1, 0, '', 1);
    $pdf->Cell(160, 14, TABLE_HEADING_PRODUCTS_MODEL, 1, 0, '', 1);
    $pdf->Cell(60, 14, TABLE_HEADING_TAX, 1, 0, '', 1);
    $pdf->Cell(78, 14, TABLE_HEADING_PRICE_NO_TAX, 1, 0, 'R', 1);
    $pdf->MultiCell(78, 14, TABLE_HEADING_TOTAL_NO_TAX, 1, 'R', 1);
  } else {
    $pdf->SetFillColor(180, 180, 180);
    $pdf->Cell(25, 14, TABLE_HEADING_QTY, 1, 0, '', 1);
    $pdf->Cell(120, 14, TABLE_HEADING_PRODUCTS, 1, 0, '', 1);
    $pdf->Cell(120, 14, TABLE_HEADING_PRODUCTS_MODEL, 1, 0, '', 1);
    $pdf->Cell(40, 14, TABLE_HEADING_TAX, 1, 0, 'R', 1);
    $pdf->Cell(68, 14, TABLE_HEADING_PRICE_EXCLUDING_TAX, 1, 0, 'R', 1);
    $pdf->Cell(68, 14, TABLE_HEADING_PRICE_INCLUDING_TAX, 1, 0, 'R', 1);
    $pdf->Cell(68, 14, TABLE_HEADING_TOTAL_EXCLUDING_TAX, 1, 0, 'R', 1);
    $pdf->MultiCell(68, 14, TABLE_HEADING_TOTAL_INCLUDING_TAX, 1, 'R', 1);
  }
  $pdf->SetFillColor(256, 256, 256);

  if (TAX_ID_NUMBER != '') {
    /* draw order items in table when tax ID number is NOT null */
    for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
      /* BEGIN update (Loose Chicken Software Development, david@loosechicken.com 01-03-2011) */
      /* break attributes onto new lines */
      $prod_name = $order->products[$i]['name'];
      $prod_attrs = array();
      if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) {
        for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j++) {
          $prod_attrs[] = $order->products[$i]['attributes'][$j]['option'] . ': ' . zen_output_string_protected($order->products[$i]['attributes'][$j]['value']);
        }
      }
      /* BEGIN add (Loose Chicken Software Development, david@loosechicken.com 01-07-2011) */

      /* Find height of model and Product names */
      $h_model = $pdf->MultiCellHeight(160, 14, $order->products[$i]['model'], 'LRT', 'L', 1);
      $h = $h_model;

      $h_product_name = $pdf->MultiCellHeight(160, 14, $prod_name, 'LRT', 'L', 1);
      $h = $h_product_name > $h ? $h_product_name : $h;

      /* If cells would be too tall, force a page break */
      if ($pdf->y + $h > $pdf->PageBreakTrigger) {
        $pdf->AddPage();
      }
      /* Get current y, to use with $h_product_name later when placing attributes */
      $y_top = $pdf->getY();

      /* END add (Loose Chicken Software Development 01-07-2011) */

      $pdf->Cell(30, 14, $order->products[$i]['qty'], 'LRT', 0, '', 1);

      /* BEGIN update (Loose Chicken Software Development, david@loosechicken.com 01-07-2011) */
      /*
       * Changed Cell to MultiCell for the test wrapping.
       * Because MultiCell doesn't leave the cursor in the right place:
       * Retrived cursor before drawing cell, and placed it afterward base on origal location and cell width
       */
      $y = $pdf->getY();
      $x = $pdf->getX();
      $pdf->MultiCell(160, 14, $prod_name, 'LRT', 'L', 1);
      $max_y = $pdf->getY();
      $pdf->setXY($x + 160, $y);

      $y = $pdf->getY();
      $x = $pdf->getX();
      $pdf->MultiCell(160, 14, $order->products[$i]['model'], 'LRT', 'L', 1);
      $max_y = $pdf->getY() > $max_y ? $pdf->getY() : $max_y;
      $pdf->setXY($x + 160, $y);
      /* END update (Loose Chicken Software Development, david@loosechicken.com 01-07-2011) */

      $pdf->Cell(60, 14, ENTRY_NO_TAX, 'LRT', 0, '', 1);
      $pdf->Cell(78, 14, $currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']), 'LRT', 0, 'R', 1);
      $pdf->MultiCell(78, 14, $currencies->format($order->products[$i]['final_price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']), 'LRT', 'R', 1);

      /* BEGIN add (Loose Chicken Software Development, david@loosechicken.com 01-07-2011) */
      /*
       * Draw cell borders in case model or product name wrapped and thus would leave blank
       * spaces on the other cells. (This must be transparent so the cells are not overritten)
       */
      $x = $pdf->GetX();
      $y = $pdf->GetY();
      if ($max_y > $y) {
        $h = $max_y - $y;

        $pdf->Cell(30, $h, '', 'LR', 0, '', 0);
        $pdf->Cell(160, $h, '', 'LR', 0, '', 0);
        $pdf->Cell(160, $h, '', 'LR', 0, '', 0);
        $pdf->Cell(60, $h, '', 'LR', 0, '', 0);
        $pdf->Cell(78, $h, '', 'LR', 0, '', 0);
        $pdf->MultiCell(78, $h, '', 'LR', 'J', 0);

        $pdf->setXY($x, $y);
      }
      $max_y_page = $pdf->PageNo();

      /* Place attributes below product_name */
      $pdf->setXY($x, $y_top + $h_product_name);
      /* END add (Loose Chicken Software Development, david@loosechicken.com 01-07-2011) */

      $pdf->SetFont('Arial', '', 8);
      $pdf->SetTextColor(40, 40, 40);
      foreach ($prod_attrs as $prod_attr) {
        $pdf->Cell(30, 10, '', 'LR', 0, '', 1);
        $pdf->Cell(160, 10, '  - ' . $prod_attr, 'LR', 0, '', 1);
        $pdf->Cell(160, 10, '', 'LR', 0, '', 1);
        $pdf->Cell(60, 10, '', 'LR', 0, '', 1);
        $pdf->Cell(78, 10, '', 'LR', 0, '', 1);
        $pdf->MultiCell(78, 10, '', 'LR', 'J', 1);
      }
      $x = $pdf->GetX();
      $y = $pdf->GetY();

      /* BEGIN add (Loose Chicken Software Development, david@loosechicken.com 01-07-2011) */
      /*
       * If after drawing the attributes the cursor is not as low as after drawing
       * the model and product name lower the cursor for the drawing of the bottom line
       */
      if (($max_y > $y) && ($max_y_page == $pdf->PageNo())) {
        $y = $max_y;
      }
      /* END add (Loose Chicken Software Development, david@loosechicken.com 01-07-2011) */

      $pdf->Line($x, $y, ($x + 30 + 160 + 160 + 60 + 78 + 78), $y);
      $pdf->SetTextColor(0, 0, 0);
      $pdf->SetFont('Arial', '', 10);
      /* END modification (Loose Chicken Software Development 01-03-2011) */
    }
  } else {
    /* draw order items in table  when tax ID number IS null */
    for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
      /* BEGIN update (Loose Chicken Software Development, david@loosechicken.com 01-03-2011) */
      /* break attributes onto new lines */
      $prod_name = $order->products[$i]['name'];
      $prod_attrs = array();
      if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) {
        for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j++) {
          $prod_attrs[] = $order->products[$i]['attributes'][$j]['option'] . ': ' . zen_output_string_protected($order->products[$i]['attributes'][$j]['value']);
        }
      }
      /* BEGIN add (Loose Chicken Software Development, david@loosechicken.com 01-07-2011) */

      /* Find height of model and Product names */
      $h_model = $pdf->MultiCellHeight(120, 14, $order->products[$i]['model'], 'LRT', 'L', 1);
      $h = $h_model;

      $h_product_name = $pdf->MultiCellHeight(120, 14, $prod_name, 'LRT', 'L', 1);
      $h = $h_product_name > $h ? $h_product_name : $h;

      /* If cells would be too tall, force a page break */
      if ($pdf->y + $h > $pdf->PageBreakTrigger) {
        $pdf->AddPage();
      }
      /* Get current y, to use with $h_product_name later when placing attributes */
      $y_top = $pdf->getY();

      /* END add (Loose Chicken Software Development 01-07-2011) */

      $pdf->Cell(25, 14, $order->products[$i]['qty'], 'LRT', 0, '', 1);

      /* BEGIN update (Loose Chicken Software Development, david@loosechicken.com 01-07-2011) */
      /*
       * Changed Cell to MultiCell for the test wrapping.
       * Because MultiCell doesn't leave the cursor in the right place:
       * Retrived cursor before drawing cell, and placed it afterward base on origal location and cell width
       */
      $y = $pdf->getY();
      $x = $pdf->getX();
      $pdf->MultiCell(120, 14, $prod_name, 'LRT', 'L', 1);
      $max_y = $pdf->getY();
      $pdf->setXY($x + 120, $y);

      $y = $pdf->getY();
      $x = $pdf->getX();
      $pdf->MultiCell(120, 14, $order->products[$i]['model'], 'LRT', 'L', 1);
      $max_y = $pdf->getY() > $max_y ? $pdf->getY() : $max_y;
      $pdf->setXY($x + 120, $y);
      /* END update (Loose Chicken Software Development, david@loosechicken.com 01-07-2011) */

      /* $pdf->Cell(40, 14, zen_display_tax_value($order->products[$i]['tax']) . '%', 'LRT', 0, 'R', 1);
        $pdf->Cell(68, 14, $currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']), 'LRT', 0, 'R', 1);
        $pdf->Cell(68, 14, $currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']), 'LRT', 0, 'R', 1);
        $pdf->Cell(68, 14, $currencies->format($order->products[$i]['final_price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']), 'LRT', 0, 'R', 1);
        $pdf->MultiCell(68, 14, $currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']), 'LRT', 'R', 1);
       */


      $pdf->Cell(40, 14, html_entity_decode(zen_display_tax_value($order->products[$i]['tax']) . '%', ENT_QUOTES, "ISO-8859-15"), 'LRT', 0, 'R', 1);

      if ($order->info['currency'] == 'EUR') {
        $f1 = str_replace('&euro;', '€', $currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']));
        $pdf->Cell(68, 14, html_entity_decode($f1), 'LRT', 'R', 1);
      } else {
        $pdf->Cell(68, 14, html_entity_decode($currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']), ENT_QUOTES, "ISO-8859-15"), 'LRT', 0, 'R', 1);
      }

      if ($order->info['currency'] == 'EUR') {
        $f2 = str_replace('&euro;', '€', $currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']));
        $pdf->Cell(68, 14, html_entity_decode($f2), 'LRT', 'R', 1);
      } else {
        $pdf->Cell(68, 14, html_entity_decode($currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']), ENT_QUOTES, "ISO-8859-15"), 'LRT', 0, 'R', 1);
      }

      if ($order->info['currency'] == 'EUR') {
        $f3 = str_replace('&euro;', '€', $currencies->format($order->products[$i]['final_price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']));
        $pdf->Cell(68, 14, html_entity_decode($f3), 'LRT', 'R', 1);
      } else {
        $pdf->Cell(68, 14, html_entity_decode($currencies->format($order->products[$i]['final_price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']), ENT_QUOTES, "ISO-8859-15"), 'LRT', 0, 'R', 1);
      }

      if ($order->info['currency'] == 'EUR') {


        $f = str_replace('&euro;', '€', $currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']));
        $pdf->MultiCell(68, 14, html_entity_decode($f), 'LRT', 'R', 1);
      } else {
        $pdf->MultiCell(68, 14, html_entity_decode($currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']), ENT_QUOTES, "ISO-8859-15"), 'LRT', 'R', 1);
      }
      /* BEGIN add (Loose Chicken Software Development, david@loosechicken.com 01-07-2011) */
      /*
       * Draw cell borders in case model or product name wrapped and thus would leave blank
       * spaces on the other cells. (This must be transparent so the cells are not overritten)
       */
      $x = $pdf->GetX();
      $y = $pdf->GetY();
      if ($max_y > $y) {
        $h = $max_y - $y;

        $pdf->Cell(25, $h, '', 'LR', 0, '', 0);
        $pdf->Cell(120, $h, '', 'LR', 0, '', 0);
        $pdf->Cell(120, $h, '', 'LR', 0, '', 0);
        $pdf->Cell(40, $h, '', 'LR', 0, '', 0);
        $pdf->Cell(68, $h, '', 'LR', 0, '', 0);
        $pdf->Cell(68, $h, '', 'LR', 0, '', 0);
        $pdf->Cell(68, $h, '', 'LR', 0, '', 0);
        $pdf->MultiCell(68, $h, '', 'LR', 'J', 0);

        $pdf->setXY($x, $y);
      }
      $max_y_page = $pdf->PageNo();

      /* Place attributes below product_name */
      $pdf->setXY($x, $y_top + $h_product_name);
      /* END add (Loose Chicken Software Development, david@loosechicken.com 01-07-2011) */

      $pdf->SetFont('Arial', '', 8);
      $pdf->SetTextColor(40, 40, 40);
      foreach ($prod_attrs as $prod_attr) {
        $pdf->Cell(25, 10, '', 'LR', 0, '', 1);
        $pdf->Cell(120, 10, '  - ' . $prod_attr, 'LR', 0, '', 1);
        $pdf->Cell(120, 10, '', 'LR', 0, '', 1);
        $pdf->Cell(40, 10, '', 'LR', 0, '', 1);
        $pdf->Cell(68, 10, '', 'LR', 0, '', 1);
        $pdf->Cell(68, 10, '', 'LR', 0, '', 1);
        $pdf->Cell(68, 10, '', 'LR', 0, '', 1);
        $pdf->MultiCell(68, 10, '', 'LR', 'J', 1);
      }
      $x = $pdf->GetX();
      $y = $pdf->GetY();

      /* BEGIN add (Loose Chicken Software Development, david@loosechicken.com 01-07-2011) */
      /*
       * If after drawing the attributes the cursor is not as low as after drawing
       * the model and product name lower the cursor for the drawing of the bottom line
       */
      if (($max_y > $y) && ($max_y_page == $pdf->PageNo())) {
        $y = $max_y;
      }
      /* END add (Loose Chicken Software Development, david@loosechicken.com 01-07-2011) */

      $pdf->Line($x, $y, ($x + 25 + 120 + 120 + 40 + 68 + 68 + 68 + 68), $y);
      $pdf->SetTextColor(0, 0, 0);
      $pdf->SetFont('Arial', '', 10);
      /* END modification (Loose Chicken Software Development 01-03-2011) */
    }
  }
  /* order cost summary */

  $pdf->Ln();
  for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
    $title = strip_tags($order->totals[$i]['title']);
    $text = strip_tags($order->totals[$i]['text']);
    /* $pdf->Cell(516, 14, $title, 0, 0, 'R');
      $pdf->MultiCell(50, 14, $text, 0, 'R'); */

    $pdf->Cell(516, 14, html_entity_decode($title, ENT_QUOTES, "ISO-8859-15"), 0, 0, 'R');


    //echo substr($text,0,5);
    if (substr($text, 0, 5) == '&euro') {
      $a = str_replace('&euro;', '€', $text);
      $pdf->MultiCell(50, 14, html_entity_decode($a, ENT_QUOTES, "ISO-8859-15"), 0, 0, 'R');
    } else {
      $pdf->MultiCell(50, 14, html_entity_decode($text, ENT_QUOTES, "ISO-8859-15"), 0, 0, 'R');
    }
  }
  /* order balance */

  /* $orders_balance = $db->Execute("select orders_id , balance_due
    from " . TABLE_ORDERS . "
    where orders_id = '" . zen_db_input($order->info['id']) . "'
    order by orders_id");
    $balance_due = $orders_balance->fields['balance_due']; */
  $pdf->SetFont('Arial', 'B', '');

  require_once(DIR_WS_CLASSES . 'super_order.php');
  $so = new super_order($order->info['id']);
  $pdf->Cell(516, 14, 'Amount Paid: ', 0, 0, 'R');

// $pdf->MultiCell(50, 14, $currencies->format($so->amount_applied), 0, 'R');
//  $pdf->MultiCell(50, 14, $currencies->format($balance_due), 0, 'R');
// $pdf->MultiCell(50, 14, $currencies->format($so->balance_due), 0, 'R');

  $var1 = substr($currencies->format($so->amount_applied), 0, 5);
  if ($var1 == '&euro') {
    $pdf->MultiCell(50, 14, '€' . $so->amount_applied, 0, 'R');
  } else {
    $pdf->MultiCell(50, 14, html_entity_decode($currencies->format($so->amount_applied), ENT_QUOTES, "ISO-8859-15"), 0, 'R');
  }

  $pdf->Cell(516, 14, 'Balance Due:', 0, 0, 'R');

  $var = substr($currencies->format($so->balance_due), 0, 5);
  if ($var == '&euro') {
    $pdf->MultiCell(50, 14, '€' . $so->balance_due, 0, 'R');
  } else {
    $pdf->MultiCell(50, 14, html_entity_decode($currencies->format($so->balance_due), ENT_QUOTES, "ISO-8859-15"), 0, 'R');
  }

  //$pdf->SetFont('Arial','', 7);

  if (ORDER_COMMENTS_PACKING_SLIP > 0) {
    $pdf->Ln();
    $pdf->SetFillColor(180, 180, 180);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->MultiCell(576, 14, 'Order Comments & Status', 'B');
    $pdf->SetFont('Arial', '', 7);
    $pdf->SetFillColor(256, 256, 256);

    $orders_history = $db->Execute("select orders_status_id, date_added, customer_notified, comments
                    from " . TABLE_ORDERS_STATUS_HISTORY . "
                    where orders_id = '" . zen_db_input($order->info['id']) . "' and customer_notified >= 0
                    order by date_added");

    $count_comments = 0;
    if ($orders_history->RecordCount() >= 1 && ORDER_COMMENTS_PACKING_SLIP == 1) {
      while (!$orders_history->EOF) {
        if ($orders_history->fields['comments'] != '' && strpos($orders_history->fields['comments'], 'PayPal status:') === false) {
          $count_comments++;
          $pdf->Cell(120, 14, zen_datetime_short($orders_history->fields['date_added']));
          $pdf->MultiCell(456, 14, $orders_status_array[$orders_history->fields['orders_status_id']]);
          $pdf->Cell(27, 14, '', 0, 0, '', 1);

          /* BEGIN modify (Loose Chicken Software Development, david@loosechicken.com 01-07-2011) */
          /* removed function zen_db_output, which was converting output into html code */
          //$pdf->MultiCell(549, 14, (zen_db_output($orders_history->fields['comments'])), 'B');
          $pdf->MultiCell(549, 14, $orders_history->fields['comments'], 'B');
          /* END modify (Loose Chicken Software Development, david@loosechicken.com 01-07-2011) */

          $pdf->SetLineWidth(.5);
          $pdf->Line(18, $pdf->GetY(), 594, $pdf->GetY());
        }
        $orders_history->MoveNext();
        /* if (ORDER_COMMENTS_PACKING_SLIP == 1 && $count_comments >= 1) {
          break;
          } */
      }
    }
    if ($count_comments == 0) {
      $pdf->MultiCell(576, 14, TEXT_NO_ORDER_HISTORY, 'B');
    }
  }
}
