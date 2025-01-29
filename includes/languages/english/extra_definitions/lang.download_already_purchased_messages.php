<?php
// -----
// Part of the "Download Already Purchased" plugin created by lat9.
// Copyright (C) 2017-2025, Vinos de Frutas Tropicales
//
// NOTE:  These messages make use of the PHP variable-ordering (to make translations easier); make sure that you ARE NOT
// enclosing the message text using double-quotes ("), since the $ in the variable name will be improperly interpreted
// as a PHP variable!
//

// -----
// This messages, issued by the plugin's "extra_cart_action" and "observer" processing, lets the customer know that they've got an
// active download link, so they don't need to re-purchase.  For each message, there's one form used by the cart-action and one
// used by the observer; the following "sprintf" variables are used in these messages:
//
// %1$s ... The name of the download product, as retrieved from the orders_products table.
// %2$s ... Either a link to the associated account_history_info page to reference the associated customer order or a link to the contact_us page.
//
return [
    'DAP_MESSAGE_DOWNLOAD_AVAILABLE_NOT_ADDED' => 'You have an active download available for <em>%1$s</em>!  Click <a href="%2$s">here</a> to access that download; the product was not added to your cart.',

    'DAP_MESSAGE_DOWNLOAD_EXPIRED_CALL_US_NOT_ADDED' => 'You previously purchased <em>%1$s</em>, but your download link has currently expired.  <a href="%2$s">Contact us</a> and we will re-enable that download; the product was not added to your cart.',

    'DAP_MESSAGE_DOWNLOAD_AVAILABLE_REMOVED' => 'You have an active download available for <em>%1$s</em>!  Click <a href="%2$s">here</a> to access that download; the product was removed from your saved cart.',

    'DAP_MESSAGE_DOWNLOAD_EXPIRED_CALL_US_REMOVED', 'You previously purchased <em>%1$s</em>, but your download link has currently expired.  <a href="%2$s">Contact us</a> and we will re-enable that download; the product was removed from your saved cart.',
];
