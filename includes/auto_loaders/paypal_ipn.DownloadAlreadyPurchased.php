<?php
// -----
// Part of the "Download Already Purchased" plugin created by lat9.
// Copyright (C) 2017-2025, Vinos de Frutas Tropicales
//

// ----
// Point 80 is where the shopping cart class is loaded and instantiated, need to be there during cart processing.
// 
$autoLoadConfig[78][] = [
    'autoType' => 'class',
    'loadFile' => 'observers/DownloadAlreadyPurchased.php'
];
$autoLoadConfig[78][] = [
    'autoType' => 'classInstantiate',
    'className' => 'DownloadAlreadyPurchased',
    'objectName' => 'downloadAlreadyPurchased'
];
