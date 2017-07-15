<?php
// -----
// Part of the "Download Already Purchased" plugin created by lat9.
// Copyright (C) 2017, Vinos de Frutas Tropicales
//
if (!defined('IS_ADMIN_FLAG')) { 
    die('Illegal Access'); 
}

$autoLoadConfig[200][] = array(
	'autoType' => 'init_script',
	'loadFile' => 'initAdminDownloadAlreadyPurchased.php'
);
