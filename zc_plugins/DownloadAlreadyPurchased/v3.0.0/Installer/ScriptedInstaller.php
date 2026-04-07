<?php
use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    protected function executeInstall()
    {
        if (!defined('DOWNLOAD_ALREADY_PURCHASED_MESSAGING')) {
            $this->addConfigurationKey('DOWNLOAD_ALREADY_PURCHASED_MESSAGING', [
                'configuration_title' => 'Download Already Purchased: Messaging',
                'configuration_value' => 'Disabled',
                'configuration_description' => '<br>Choose the method with which to communicate the prior purchase of a product-download to the customer, one of:<ol><li><strong>Disabled:</strong> No special handling; the download can always be re-purchased without message.</li><li><strong>Call on Expiration:</strong> Regardless the status of a product-download\'s expiration, the item cannot be re-purchased.  If the download has not expired, the customer is directed to the order-information page where the active download link is displayed.  Otherwise, a message is displayed indicating that the customer has previously purchased the product and should contact the store to get the download <em>reset</em></li><li><strong>Enforce Expiration:</strong> If the download has not expired, the item cannot be re-purchased.  The customer is, instead, directed to the order-information page where the active download link is displayed.  Otherwise, the customer can re-purchase the product.</li></ol>',
                'configuration_group_id' => 13,
                'sort_order' => 15,
                'set_function' => 'zen_cfg_select_option([\'Disabled\', \'Enforce Expiration\', \'Call On Expiration\'],',
            ]);
        }
        if (!defined('DOWNLOAD_ALREADY_PURCHASED_EXCLUDE_PRODUCTS')) {
            $this->addConfigurationKey('DOWNLOAD_ALREADY_PURCHASED_EXCLUDE_PRODUCTS', [
                'configuration_title' => 'Download Already Purchased: Product Exclusions',
                'configuration_value' => '',
                'configuration_description' => '<br>Enter the comma-separated list of product ID values to be excluded from the &quot;already purchased&quot; handling.',
                'configuration_group_id' => 13,
                'sort_order' => 16,
            ]);
        }
        if (!defined('DOWNLOAD_ALREADY_PURCHASED_EXCLUDE_CATEGORIES')) {
            $this->addConfigurationKey('DOWNLOAD_ALREADY_PURCHASED_EXCLUDE_CATEGORIES', [
                'configuration_title' => 'Download Already Purchased: Category Exclusions',
                'configuration_value' => '',
                'configuration_description' => 'br>Enter the comma-separated list of category ID values for which the associated products are to be excluded from the &quot;already purchased&quot; handling.',
                'configuration_group_id' => 13,
                'sort_order' => 17,
            ]);
        }
        parent::executeInstall();
    }

    protected function executeUninstall()
    {
        $this->deleteConfigurationKeys(['DOWNLOAD_ALREADY_PURCHASED_MESSAGING', 'DOWNLOAD_ALREADY_PURCHASED_EXCLUDE_PRODUCTS', 'DOWNLOAD_ALREADY_PURCHASED_EXCLUDE_CATEGORIES']);

        parent::executeUninstall();
    }
}
