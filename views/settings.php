<?php

/**
 * OpenVPN server view.
 *
 * @category   apps
 * @package    openvpn
 * @subpackage views
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/openvpn/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  
//  
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('base');
$this->lang->load('network');
$this->lang->load('openvpn');

///////////////////////////////////////////////////////////////////////////////
// Form handler
///////////////////////////////////////////////////////////////////////////////

if ($auto_configure) {
    $read_only = TRUE;
    $buttons = array();
} else if ($form_type === 'edit') {
    $read_only = FALSE;
    $buttons = array(
        form_submit_update('submit'),
        anchor_cancel('/app/openvpn/settings'),
    );
} else {
    $read_only = TRUE;
    $buttons = array(
        anchor_edit('/app/openvpn/settings/edit')
    );
}

///////////////////////////////////////////////////////////////////////////////
// Auto configure help
///////////////////////////////////////////////////////////////////////////////

if ($auto_configure) {
    echo infobox_highlight(
        lang('base_automatic_configuration_enabled'),
        lang('openvpn_auto_configure_help') . '<br>' .
        "<p align='center'>" . anchor_custom('/app/openvpn/settings/disable_auto_configure', lang('base_disable_auto_configuration')) . "</p>"
    );
}

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

echo form_open('openvpn/settings/edit');
echo form_header(lang('base_settings'));

echo field_input('hostname', $hostname, lang('network_internet_hostname'), TRUE);
echo field_input('domain', $domain, lang('network_internet_domain'), $read_only);
echo field_input('dns_server', $dns_server, lang('network_dns_server'), $read_only);
echo field_input('wins_server', $wins_server, lang('network_wins_server'), $read_only);

echo field_button_set($buttons);

echo form_footer();
echo form_close();
