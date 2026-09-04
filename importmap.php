<?php

/**
 * Configuration de l'AssetMapper (importmap).
 *
 * "app" est le point d'entrée JavaScript du site. On y ajoutera les
 * librairies externes le jour venu, ex. :
 *   php bin/console importmap:require chart.js
 *
 * @see https://symfony.com/doc/current/frontend/asset_mapper.html
 */
return [
    'app' => [
        'path' => './assets/js/app.js',
        'entrypoint' => true,
    ],
];
