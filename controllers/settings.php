<?php

/**
 * OpenVPN settings controller.
 *
 * @category   apps
 * @package    openvpn
 * @subpackage controllers
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
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * OpenVPN settings controller.
 *
 * @category   apps
 * @package    openvpn
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/openvpn/
 */

class Settings extends ClearOS_Controller
{
    /**
     * OpenVPN settings controller
     *
     * @return view
     */

    function index()
    {
        $this->_common('view');
    }

    /**
     * Edit view.
     *
     * @return view
     */

    function edit()
    {
        $this->_common('edit');
    }

    /**
     * Disables auto configuration.
     *
     * @return redirect
     */

    function disable_auto_configure()
    {
        // Load dependencies
        //------------------

        $this->load->library('openvpn/OpenVPN');

        // Disable and redirect
        //---------------------

        $this->openvpn->set_auto_configure_state(FALSE);
        redirect('/openvpn/settings/edit');
    }

    /**
     * View view.
     *
     * @return view
     */

    function view()
    {
        $this->_common('view');
    }

    /**
     * Common view/edit handler.
     *
     * @param string $form_type form type
     *
     * @return view
     */

    function _common($form_type)
    {
        // Load dependencies
        //------------------

        $this->lang->load('base');
        $this->lang->load('openvpn');
        $this->load->library('openvpn/OpenVPN');

        // Set validation rules
        //---------------------
         
        $this->form_validation->set_policy('domain', 'openvpn/OpenVPN', 'validate_domain');
        $this->form_validation->set_policy('wins_server', 'openvpn/OpenVPN', 'validate_wins_server');
        $this->form_validation->set_policy('dns_server', 'openvpn/OpenVPN', 'validate_dns_server');
        $form_ok = $this->form_validation->run();

        // Handle form submit
        //-------------------

        if (($this->input->post('submit') && $form_ok)) {
            try {
                $this->openvpn->set_domain($this->input->post('domain'));
                $this->openvpn->set_wins_server($this->input->post('wins_server'));
                $this->openvpn->set_dns_server($this->input->post('dns_server'));
                $this->openvpn->reset(TRUE);

                $this->page->set_status_updated();
                redirect('/openvpn/settings');
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        // Load view data
        //---------------

        try {
            $data['form_type'] = $form_type;
            $data['domain'] = $this->openvpn->get_domain();
            $data['hostname'] = $this->openvpn->get_server_hostname();
            $data['wins_server'] = $this->openvpn->get_wins_server();
            $data['dns_server'] = $this->openvpn->get_dns_server();
            $data['auto_configure'] = $this->openvpn->get_auto_configure_state();
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------

        $this->page->view_form('settings', $data, lang('base_settings'));
    }
}
