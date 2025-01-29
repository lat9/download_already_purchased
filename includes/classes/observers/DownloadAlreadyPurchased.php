<?php
// -----
// Part of the "Download Already Purchased" plugin created by lat9.
// Copyright (C) 2017-2025, Vinos de Frutas Tropicales
//
class DownloadAlreadyPurchased extends base
{
    protected int $orders_id;              //- The last matching order-id
    protected string $date_purchased;      //- The mySQL-format datetime value identifying the date-purchased for the above order-id
    protected int $download_max_days;      //- The maximum number of days-from-purchase, after which the download expires
    protected int $download_count;         //- The number of downloads remaining until the download expires
    protected bool $is_expired;            //- A boolean indicator as to whether the currently-active download has expired.
    protected string $filename;            //- The name of the file (presumed to be in the DIR_FS_DOWNLOAD directory)
    protected string $products_name;       //- The name of the associated product (used in customer messaging)
    protected bool $timeout_enforced;      //- Identifies whether (true) or not (false) the store is configured to "enforce" the download timeouts
    protected bool $enabled;               //- Identifies whether/not this processing is enabled.
    protected array $excluded_products;    //- Contains an array of products to be excluded from this handling.

    public function __construct()
    {
        $this->enabled = false;
        if (DOWNLOAD_ENABLED === 'true' && defined('DOWNLOAD_ALREADY_PURCHASED_MESSAGING') && DOWNLOAD_ALREADY_PURCHASED_MESSAGING !== 'Disabled') {
            $this->enabled = (zen_is_logged_in() && !zen_in_guest_checkout());
            $this->timeout_enforced = (DOWNLOAD_ALREADY_PURCHASED_MESSAGING === 'Enforce Expiration');
            $this->attach(
                $this,
                [
                    'NOTIFIER_CART_RESTORE_CONTENTS_END'
                ]
            );
            $this->initializeExclusions();
        }
    }

    public function update(&$class, string $eventID): void
    {
        // -----
        // When the customer's cart-contents are restored, make sure that there aren't some "lurking" downloads
        // that should be "notified".  Note that the message is issued to the page-header rather than to the
        // shopping_cart page; if the cart's contents have been reduced to 0, then any shopping_cart-specific
        // message isn't displayed!
        //
        if ($_SESSION['cart']->count_contents() > 0) {
            global $messageStack;

            $products_to_remove = [];
            foreach ($_SESSION['cart']->contents as $uprid => $product_details) {
                if (isset($product_details['attributes']) && is_array($product_details['attributes'])) {
                    $products_id = zen_get_prid($uprid);
                    foreach ($product_details['attributes'] as $option_id => $option_value_id) {
                        $message = $this->checkDownloadPriorPurchaseRestoreCart($products_id, $option_id, $option_value_id);
                        if ($message !== false) {
                            $products_to_remove[] = $uprid;
                            $messageStack->add_session('header', $message, 'caution');
                        }
                    }
                }
            }
            if (count($products_to_remove) !== 0) {
                foreach ($products_to_remove as $uprid) {
                    $_SESSION['cart']->remove($uprid);
                }
                zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
            }
        }
    }

    // -----
    // Returns the indicator that identifies whether (true) or not (false) the processing is enabled.
    //
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function checkDownloadPriorPurchaseAddCart($products_id, $option_id, $option_values_id): bool|string
    {
        $message = false;
        if ($this->checkDownloadPriorPurchase($products_id, $option_id, $option_values_id)) {
            if ($this->is_downloadable === false) {
                if (!$this->timeout_enforced) {
                    $contact_us_link = zen_href_link(FILENAME_CONTACT_US, '', 'SSL');
                    $message = sprintf(DAP_MESSAGE_DOWNLOAD_EXPIRED_CALL_US_NOT_ADDED, $this->products_name, $contact_us_link);
                }
            } else {
                $account_history_info_link = zen_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $this->orders_id, 'SSL');
                $message = sprintf(DAP_MESSAGE_DOWNLOAD_AVAILABLE_NOT_ADDED, $this->products_name, $account_history_info_link);
            }
        }
        return $message;
    }

    protected function checkDownloadPriorPurchaseRestoreCart($products_id, $option_id, $option_values_id): bool|string
    {
        $message = false;
        if ($this->checkDownloadPriorPurchase($products_id, $option_id, $option_values_id)) {
            if ($this->is_downloadable === false) {
                if (!$this->timeout_enforced) {
                    $contact_us_link = zen_href_link(FILENAME_CONTACT_US, '', 'SSL');
                    $message = sprintf(DAP_MESSAGE_DOWNLOAD_EXPIRED_CALL_US_REMOVED, $this->products_name, $contact_us_link);
                }
            } else {
                $account_history_info_link = zen_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $this->orders_id, 'SSL');
                $message = sprintf(DAP_MESSAGE_DOWNLOAD_AVAILABLE_REMOVED, $this->products_name, $account_history_info_link);
            }
        }
        return $message;
    }

    // -----
    // Determine whether any product exclusions have been configured and initialize the list of to-be-excluded
    // product_id values.
    //
    protected function initializeExclusions(): void
    {
        $excluded_products = [];

        // -----
        // First, if there are product exclusions, make sure that each products_id identified is an unsigned
        // integer value; if so, add it to the list.
        //
        if (defined('DOWNLOAD_ALREADY_PURCHASED_EXCLUDE_PRODUCTS')) {
            $exclusion_list = explode(',', str_replace(' ', '', DOWNLOAD_ALREADY_PURCHASED_EXCLUDE_PRODUCTS));
            foreach ($exclusion_list as $current_product_id) {
                if (preg_match('/^[0-9]+$/', $current_product_id)) {
                    $excluded_products[] = $current_product_id;
                }
            }
        }

        // -----
        // Next, check each categories_id value and, if it's an unsigned integer value, look up all products for
        // that category in the database.  The database query returns a comma-separated list of id values; add those
        // products to the list of exclusions.
        //
        if (defined('DOWNLOAD_ALREADY_PURCHASED_EXCLUDE_CATEGORIES')) {
            $exclusion_list = explode(',', str_replace(' ', '', DOWNLOAD_ALREADY_PURCHASED_EXCLUDE_CATEGORIES));
            foreach ($exclusion_list as $current_category_id) {
                if (preg_match('/^[0-9]+$/', $current_category_id)) {
                    $result = $GLOBALS['db']->Execute(
                        "SELECT GROUP_CONCAT(products_id SEPARATOR ',') as products_list
                           FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                          WHERE categories_id = $current_category_id"
                    );
                    if (!$result->EOF) {
                        $excluded_products = array_merge($excluded_products, explode(',', $result->fields['products_list']));
                    }
                }
            }
        }

        // -----
        // Finally, record the array of products_id values that are to be excluded from this processing ...
        // after removing any duplicates.
        //
        $this->excluded_products = array_unique($excluded_products);
    }
    
    // -----
    // Returns a boolean indicator, identifying whether (true) or not (false) the specified product is
    // not in the store's defined exclusion list, is downloadable and has been previously purchased.
    //
    protected function checkDownloadPriorPurchase($products_id, $option_id, $option_value_id): bool
    {
        $is_prior_purchase = false;
        $this->is_downloadable = false;

        $products_id = (int)zen_get_prid($products_id);
        if (!in_array($products_id, $this->excluded_products)) {
            $option_id = (int)$option_id;
            $option_value_id = (int)$option_value_id;
            
            if ($this->isProductDownload($products_id, $option_id, $option_value_id) === true) {
                if ($this->gatherPurchaseInfo($products_id, $option_id, $option_value_id) === true) {
                    $is_prior_purchase = true;
                    $this->is_downloadable = $this->isDownloadable();
                }
            }
        }
        return $is_prior_purchase;
    }

    protected function isProductDownload($products_id, $option_id, $option_value_id): bool
    {
        global $db;

        $check = $db->Execute(
            "SELECT pad.products_attributes_id
               FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                    INNER JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                        ON pad.products_attributes_id = pa.products_attributes_id
              WHERE pa.products_id = $products_id
                AND pa.options_id = $option_id
                AND pa.options_values_id = $option_value_id
              LIMIT 1"
        );
        return !$check->EOF;
    }
    
    // -----
    // A file is downloadable if all the following conditions are true:
    //
    // 1) The file is present in the /download directory
    // 2) Either
    //    a) No expiry is enforced (i.e. the download_max_days is 0)
    //    b) The download can expire (i.e. the download_max_days is not 0) and either:
    //       i)  The download-count has not been exceeded (i.e. the value is > 0)
    //       ii) The download-period (determined by the non-zero max_days) is still active.
    //
    // Class-variable inputs, set via (presumed prior) call to gatherPurchaseInfo:
    // - filename ............ the name of the file, presumed to be in the /download directory
    // - date_purchased ...... The SQL datetime formatted date the product was purchased
    // - download_max_days ... The number of post-purchase days that the customer has to download the file.
    // - download_count ...... The number of times the customer can download the file in the configured period.
    //
    // Returns a boolean indication of the above conditions.
    //
    protected function isDownloadable(): bool
    {
        $is_downloadable = false;
        if (file_exists($this->filename)) {
            if ($this->download_max_days === 0) {
                $is_downloadable = true;
            } elseif ($this->download_count > 0) {
                $purchase_datetime = substr($this->date_purchased, 0, 10) . ' 23:59:59';
                $expiry_timestamp = strtotime($purchase_datetime) + $this->download_max_days * 24 * 60 * 60;
                if ($expiry_timestamp > time()) {
                    $is_downloadable = true;
                }
            }
        }
        return $is_downloadable;
    }

    // -----
    // This function determines if the specified product+option+option_value:
    //
    // 1) Is a downloadable product, purchased by the currently-signed-in customer.
    // 2) Is associated with an order for that customer that has been "released" for download operations.
    //
    // NOTE: If the customer has previously purchased more than one of the product-option combination, the
    // most recent order is used.
    //
    // SIDE-EFFECTS:  If an order is found to match these conditions, the following class variables
    // are set: orders_id, download_count, date_purchased, download_max_days, products_name and filename; if not, those
    // variables are all "unset".
    //
    // Returns a boolean indication of whether (true) or not (false) a matching order for the customer
    // was found.
    //
    protected function gatherPurchaseInfo($products_id, $option_id, $option_value_id): bool
    {
        global $db;

        unset($this->orders_id, $this->download_count, $this->date_purchased, $this->download_max_days, $this->filename, $this->products_name);
        $check = $db->Execute(
            "SELECT o.orders_id, o.date_purchased, op.products_name, opd.download_maxdays, opd.download_count, opd.orders_products_filename
               FROM " . TABLE_ORDERS . " o
                    INNER JOIN " . TABLE_ORDERS_PRODUCTS . " op
                        ON op.orders_id = o.orders_id
                       AND op.products_id = $products_id
                    INNER JOIN " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " opa
                        ON opa.orders_products_id = op.orders_products_id
                       AND opa.products_options_id = $option_id
                       AND opa.products_options_values_id = $option_value_id
                    INNER JOIN " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd
                        ON opd.orders_products_id = op.orders_products_id
                       AND opd.orders_products_filename != ''
              WHERE o.customers_id = " . (int)$_SESSION['customer_id'] . "
                AND o.orders_status >= " . (int)DOWNLOADS_CONTROLLER_ORDERS_STATUS . "
                AND o.orders_status <= " . (int)DOWNLOADS_CONTROLLER_ORDERS_STATUS_END . "
           ORDER BY o.orders_id DESC
              LIMIT 1"
        );
        if (!$check->EOF) {
            $this->orders_id = (int)check->fields['orders_id'];
            $this->download_count = (int)$check->fields['download_count'];
            $this->date_purchased = $check->fields['date_purchased'];
            $this->download_max_days = (int)$check->fields['download_maxdays'];
            $this->products_name = $check->fields['products_name'];
            $this->filename = DIR_FS_DOWNLOAD . $check->fields['orders_products_filename'];
        }
        return !$check->EOF;
    }
}
