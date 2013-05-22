<?php

/**
 * OpenVPN controller.
 *
 * @category   apps
 * @package    openvpn
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/pptpd/
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
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * OpenVPN controller.
 *
 * @category   apps
 * @package    openvpn
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/pptpd/
 */

class OpenVPN extends ClearOS_Controller
{
    /**
     * OpenVPN server summary view.
     *
     * @return view
     */

    function index()
    {
        // Show Certificate Manager widget if it is not initialized
        //---------------------------------------------------------

        $this->load->module('certificate_manager/certificate_status');

        if (! $this->certificate_status->is_initialized()) {
            $this->certificate_status->widget();
            return;
        }

        // Load libraries
        //---------------

        $this->lang->load('openvpn');

        // Load views
        //-----------

        $views = array('openvpn/server', 'openvpn/settings', 'openvpn/policy');

        $this->page->view_forms($views, lang('openvpn_app_name'));
    }
}
