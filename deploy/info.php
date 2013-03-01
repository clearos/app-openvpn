<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'openvpn';
$app['version'] = '1.4.20';
$app['release'] = '1';
$app['vendor'] = 'ClearFoundation';
$app['packager'] = 'ClearFoundation';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('openvpn_app_description');

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('openvpn_app_name');
$app['category'] = lang('base_category_network');
$app['subcategory'] = lang('base_subcategory_vpn');

/////////////////////////////////////////////////////////////////////////////
// Controllers
/////////////////////////////////////////////////////////////////////////////

$app['controllers']['openvpn']['title'] = $app['name'];
$app['controllers']['settings']['title'] = lang('base_settings');
$app['controllers']['policy']['title'] = lang('base_app_policy');

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['requires'] = array(
    'app-accounts',
    'app-certificate-manager',
    'app-groups',
    'app-users',
    'app-network',
    'app-user-certificates',
);

$app['core_requires'] = array(
    'app-certificate-manager-core',
    'app-network-core >= 1:1.0.7',
    'app-openvpn-plugin-core',
    'csplugin-filewatch',
    'openvpn >= 2.1.4',
);

$app['core_directory_manifest'] = array(
    '/etc/clearos/openvpn.d' => array(),
    '/etc/openvpn/ssl' => array(),
    '/var/clearos/openvpn' => array(),
    '/var/clearos/openvpn/backup' => array(),
    '/var/lib/openvpn' => array(),
);

$app['core_file_manifest'] = array(
    'openvpn.php'=> array('target' => '/var/clearos/base/daemon/openvpn.php'),
    'filewatch-openvpn-network.conf'=> array('target' => '/etc/clearsync.d/filewatch-openvpn-network.conf'),
    'clients.conf'=> array(
        'target' => '/etc/openvpn/clients.conf',
        'config' => TRUE,
        'config_params' => 'noreplace',
    ),
    'clients-tcp.conf'=> array(
        'target' => '/etc/openvpn/clients-tcp.conf',
        'config' => TRUE,
        'config_params' => 'noreplace',
    ),
    'openvpn.conf'=> array(
        'target' => '/etc/clearos/openvpn.conf',
        'config' => TRUE,
        'config_params' => 'noreplace',
    ),
    'authorize' => array(
        'target' => '/etc/clearos/openvpn.d/authorize',
        'mode' => '0644',
        'owner' => 'root',
        'group' => 'root',
        'config' => TRUE,
        'config_params' => 'noreplace',
    ),
);
