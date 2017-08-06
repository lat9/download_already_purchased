<?php
// -----
// Part of the "Download Already Purchased" plugin created by lat9.
// Copyright (C) 2017, Vinos de Frutas Tropicales
//
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// -----
// If not already recorded, insert the plugin's configuration setting into Configuration->Attribute Settings, along
// with the other download-related settings.
//
if (!defined('DOWNLOAD_ALREADY_PURCHASED_MESSAGING')) {
    $db->Execute(
        "INSERT INTO " . TABLE_CONFIGURATION . " 
            ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, date_added, sort_order, use_function, set_function ) 
         VALUES 
            ( 'Download Already Purchased: Messaging', 'DOWNLOAD_ALREADY_PURCHASED_MESSAGING', 'Disabled', '<br />Choose the method with which to communicate the prior purchase of a product-download to the customer, one of:<ol><li><strong>Disabled:</strong> No special handling; the download can always be re-purchased without message.</li><li><strong>Call on Expiration:</strong> Regardless the status of a product-download\'s expiration, the item cannot be re-purchased.  If the download has not expired, the customer is directed to the order-information page where the active download link is displayed.  Otherwise, a message is displayed indicating that the customer has previously purchased the product and should contact the store to get the download <em>reset</em></li><li><strong>Enforce Expiration:</strong> If the download has not expired, the item cannot be re-purchased.  The customer is, instead, directed to the order-information page where the active download link is displayed.  Otherwise, the customer can re-purchase the product.</li></ol>', 13, now(), 15, NULL, 'zen_cfg_select_option(array(\'Disabled\', \'Enforce Expiration\', \'Call On Expiration\'),')");
}

// -----
// Additional configuration settings, to provide product and/or category exclusions.
//
if (!defined('DOWNLOAD_ALREADY_PURCHASED_EXCLUDE_PRODUCTS')) {
    $db->Execute(
        "INSERT INTO " . TABLE_CONFIGURATION . " 
            ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, date_added, sort_order, use_function, set_function ) 
         VALUES 
            ( 'Download Already Purchased: Product Exclusions', 'DOWNLOAD_ALREADY_PURCHASED_EXCLUDE_PRODUCTS', '', '<br />Enter the comma-separated list of product ID values to be excluded from the &quot;already purchased&quot; handling.', 13, now(), 16, NULL, NULL ) ,
            ( 'Download Already Purchased: Category Exclusions', 'DOWNLOAD_ALREADY_PURCHASED_EXCLUDE_CATEGORIES', '', '<br />Enter the comma-separated list of category ID values for which the associated products are to be excluded from the &quot;already purchased&quot; handling.', 13, now(), 17, NULL, NULL )"
    );
}