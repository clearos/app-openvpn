<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'openvpn';
$app['version'] = '1.6.7';
$app['release'] = '1';
$app['vendor'] = 'ClearFoundation';
$app['packager'] = 'ClearFoundation';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('openvpn_app_description');
$app['tooltip'] = lang('openvpn_internet_hostname_tooltip');

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
    'app-events-core',
    'app-network-core >= 1:1.6.0',
    'app-openvpn-plugin-core',
    'openvpn >= 2.3.2',
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
    'network-configuration-event'=> array(
        'target' => '/var/clearos/events/network_configuration/openvpn',
        'mode' => '0755'
    ),
    'network-peerdns-event'=> array(
        'target' => '/var/clearos/events/network_peerdns/openvpn',
        'mode' => '0755'
    ),
    'samba-configuration-event'=> array(
        'target' => '/var/clearos/events/samba_configuration/openvpn',
        'mode' => '0755'
    ),
);
