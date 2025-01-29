<?php
// -----
// Part of the "Download Already Purchased" plugin created by lat9.
// Copyright (C) 2017-2025, Vinos de Frutas Tropicales
//
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

if (isset($_GET['action'], $downloadAlreadyPurchased, $_POST['id']) && $_GET['action'] === 'add_product') {
    // -----
    // If the product being added is a download, check to see if the customer has previously purchased
    // the product.  The common class-function returns either boolean false (if no qualifying prior purchase
    // was found) or a message to be displayed to the customer.
    //
    if ($downloadAlreadyPurchased->isEnabled() && is_array($_POST['id'])) {
        foreach ($_POST['id'] as $option_id => $option_value_id) {
            $message = $downloadAlreadyPurchased->checkDownloadPriorPurchaseAddCart($_POST['products_id'], $option_id, $option_value_id);
            if ($message !== false) {
                $messageStack->add('product_info', $message, 'caution');
                unset($_GET['action']);
                break;
            }
        }
    }
}
