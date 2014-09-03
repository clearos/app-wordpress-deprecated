<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'wordpress';
$app['version'] = '1.6.5';
$app['release'] = '1';
$app['vendor'] = 'ClearFoundation';
$app['packager'] = 'ClearFoundation';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('wordpress_app_description');

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('wordpress_app_name');
$app['category'] = lang('base_category_server');
$app['subcategory'] = lang('base_subcategory_web');

/////////////////////////////////////////////////////////////////////////////
// Controllers
/////////////////////////////////////////////////////////////////////////////

$app['controllers']['wordpress']['title'] = $app['name'];
$app['controllers']['settings']['title'] = lang('base_settings');
$app['controllers']['upload']['title'] = lang('base_app_upload');
$app['controllers']['advanced']['title'] = lang('base_app_advanced_settings');

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['requires'] = array(
    'app-webapp',
    'app-system-database >= 1:1.6.1',
);

$app['core_requires'] = array(
    'app-webapp-core',
    'app-system-database-core >= 1:1.6.1',
    'webapp-wordpress',
);

$app['core_directory_manifest'] = array(
    '/var/clearos/wordpress' => array(),
    '/var/clearos/wordpress/archive' => array(),
    '/var/clearos/wordpress/backup' => array(),
);

$app['core_file_manifest'] = array(
    'webapp-wordpress-flexshare.conf' => array(
        'target' => '/etc/clearos/flexshare.d/webapp-wordpress.conf',
        'config' => TRUE,
        'config_params' => 'noreplace'
    ),
    'webapp-wordpress-httpd.conf' => array(
        'target' => '/etc/httpd/conf.d/webapp-wordpress.conf',
        'config' => TRUE,
        'config_params' => 'noreplace'
    )
);
