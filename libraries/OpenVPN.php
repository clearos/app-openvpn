<?php

/**
 * OpenVPN class.
 *
 * @category   apps
 * @package    openvpn
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2008-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/openvpn/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace clearos\apps\openvpn;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('base');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Classes
//--------

use \clearos\apps\base\Daemon as Daemon;
use \clearos\apps\base\File as File;
use \clearos\apps\network\Domain as Domain;
use \clearos\apps\network\Hostname as Hostname;
use \clearos\apps\network\Iface_Manager as Iface_Manager;
use \clearos\apps\network\Network_Utils as Network_Utils;
use \clearos\apps\network\Routes as Routes;

clearos_load_library('base/Daemon');
clearos_load_library('base/File');
clearos_load_library('network/Domain');
clearos_load_library('network/Hostname');
clearos_load_library('network/Iface_Manager');
clearos_load_library('network/Network_Utils');
clearos_load_library('network/Routes');

// Exceptions
//-----------

use \Exception as Exception;
use \clearos\apps\base\Engine_Exception as Engine_Exception;
use \clearos\apps\base\File_No_Match_Exception as File_No_Match_Exception;
use \clearos\apps\base\File_Not_Found_Exception as File_Not_Found_Exception;
use \clearos\apps\base\Validation_Exception as Validation_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/File_No_Match_Exception');
clearos_load_library('base/File_Not_Found_Exception');
clearos_load_library('base/Validation_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * OpenVPN class.
 *
 * @category   apps
 * @package    openvpn
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2008-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/openvpn/
 */

class OpenVPN extends Daemon
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const FILE_APP_CONFIG = '/etc/clearos/openvpn.conf';
    const FILE_CLIENTS_CONFIG = '/etc/openvpn/clients.conf';
    const FILE_CLIENTS_CONFIG_TCP = '/etc/openvpn/clients-tcp.conf';
    const DEFAULT_PORT = 1194;
    const DEFAULT_PROTOCOL = "udp";
    const CONSTANT_PROTOCOL_UDP = "udp";
    const CONSTANT_PROTOCOL_TCP = "tcp";
    const TYPE_OS_WINDOWS = "Windows";
    const TYPE_OS_LINUX = "Linux";
    const TYPE_OS_MACOS = "MacOS";

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $is_loaded = FALSE;
    protected $config = array();

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * OpenVPN constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);

        parent::__construct('openvpn');
    }

    /**
     * Auto configures PPTP.
     *
     * @return void
     * @throws Engine_Exception
     */

    public function auto_configure()
    {
        clearos_profile(__METHOD__, __LINE__);

        // Note: this is very similar to the PPTP method.  If you make a bug
        // fix or enhancement here, check the PPTP Server app as well.

        if (! $this->get_auto_configure_state())
            return;

        $ifaces = new Iface_Manager();
        $routes = new Routes();

        // Local / Remote IP configuration
        //--------------------------------

        $raw_routes = $routes->get_most_trusted_routes();
        $routes = array();

        foreach ($raw_routes as $route) {
            list($ip, $prefix) = preg_split('/\//', $route);
            $routes[] = $ip . ' ' . Network_Utils::get_netmask($prefix);
        }

        $this->set_push_routes($routes);

        // DNS server configuration
        //-------------------------

        $ips = $ifaces->get_most_trusted_ips();

        if ((!empty($ips[0])) && clearos_app_installed('dns'))
            $this->set_dns_server($ips[0]);
        else
            $this->set_dns_server('');

        // WINS server configuration
        //--------------------------

        if (clearos_library_installed('samba_common/Samba')) {
            clearos_load_library('samba_common/Samba');
            $samba = new \clearos\apps\samba_common\Samba();
            $is_wins = $samba->get_wins_support();
            $wins_server = $samba->get_wins_server();
        } else {
            $is_wins = FALSE;
            $wins_server = '';
        }

        if ($is_wins && (!empty($ips[0]))) {
            $this->set_wins_server($ips[0]);
        } else if (!empty($wins_server)) {
            $this->set_wins_server($wins_server);
        } else {
            $this->set_wins_server('');
        }

        // Domain
        //-------

        $domain = new Domain();
        $default = $domain->get_default();

        $this->set_domain($default);
    }

    /**
     * Returns auto-configure state.
     *
     * @return boolean state of auto-configure mode
     */

    public function get_auto_configure_state()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::FILE_APP_CONFIG);
            $value = $file->lookup_value("/^auto_configure\s*=\s*/i");
        } catch (File_Not_Found_Exception $e) {
            return FALSE;
        } catch (File_No_Match_Exception $e) {
            return FALSE;
        } catch (Exception $e) {
            throw new Engine_Exception($e->get_message());
        }

        if (preg_match('/yes/i', $value))
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Returns configuration file for requested client type.
     *
     * @param string $type    client type (eg Windows)
     * @param string $file_id unique identifier used in hostname (eg username)
     *
     * @return void
     * @throws Engine_Exception
     */

    public function get_client_configuration($type, $file_id)
    {
        clearos_profile(__METHOD__, __LINE__);

        $host = $this->get_server_hostname();
        $port = $this->get_client_port();
        $protocol = $this->get_client_protocol();

        if ($type == self::TYPE_OS_WINDOWS) {
            $config = "client
remote $host $port
dev tun
proto $protocol
resolv-retry infinite
nobind
persist-key
persist-tun
ca ca-cert.pem
cert client-" . $file_id . "-cert.pem
key client-" . $file_id . "-key.pem
ns-cert-type server
comp-lzo
verb 3
auth-user-pass
";
        } else if (($type == self::TYPE_OS_LINUX) || ($type == self::TYPE_OS_MACOS)) {
            $config = "client
remote $host $port
dev tun
proto $protocol
resolv-retry infinite
nobind
user nobody
group nobody
persist-key
persist-tun
ca ca-cert.pem
cert client-" . $file_id . "-cert.pem
key client-" . $file_id . "-key.pem
ns-cert-type server
comp-lzo
verb 3
auth-user-pass
";
        } else {
            throw new Engine_Exception(OPENVPN_LANG_CONFIGURATION_FILE . " - " . LOCALE_LANG_INVALID, COMMON_WARNING);
        }

        return $config;
    }

    /**
     * Returns port number for desktop client server.
     *
     * @return void
     * @throws Engine_Exception
     */

    public function get_client_port()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        if (empty($this->config['port']))
            return self::DEFAULT_PORT;
        else
            return $this->config['port'];
    }

    /**
     * Returns protocol for desktop client server.
     *
     * @return void
     * @throws Engine_Exception
     */

    public function get_client_protocol()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        if (empty($this->config['proto']))
            return self::DEFAULT_PROTOCOL;
        else
            return $this->config['proto'];
    }

    /**
     * Returns client types.
     *
     * @return array client types
     * @throws Engine_Exception
     */

    public function get_client_types()
    {
        clearos_profile(__METHOD__, __LINE__);

        return array(
            self::TYPE_OS_WINDOWS => lang('openvpn_windows'),
            self::TYPE_OS_LINUX => lang('openvpn_linux'),
            self::TYPE_OS_MACOS => lang('openvpn_mac')
        );
    }

    /**
     * Returns DNS server pushed out to clients.
     *
     * @return string DNS server IP address
     * @throws Engine_Exception
     */

    public function get_dns_server()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        if (empty($this->config['push']['dhcp-option']['DNS']))
            return "";
        else
            return $this->config['push']['dhcp-option']['DNS'];
    }

    /**
     * Returns domain name pushed out to clients.
     *
     * @return string domain name
     * @throws Engine_Exception
     */

    public function get_domain()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        if (empty($this->config['push']['dhcp-option']['DOMAIN']))
            return "";
        else
            return $this->config['push']['dhcp-option']['DOMAIN'];
    }

    /**
     * Returns the hostname to use to connect to this server.
     *
     * @return string OpenVPN server hostname
     * @throws Engine_Exception
     */

    public function get_server_hostname()
    {
        clearos_profile(__METHOD__, __LINE__);

        $hostname = new Hostname();

        return $hostname->get_internet_hostname();
    }

    /**
     * Returns WINS server pushed out to clients.
     *
     * @return string WINS server IP address
     * @throws Engine_Exception
     */

    public function get_wins_server()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        if (empty($this->config['push']['dhcp-option']['WINS']))
            return '';
        else
            return $this->config['push']['dhcp-option']['WINS'];
    }

    /**
     * Sets auto-configure state.
     *
     * @param boolean $state state
     *
     * @return void
     * @throws Engine_Exception, Validation_Exception
     */

    public function set_auto_configure_state($state)
    {
        clearos_profile(__METHOD__, __LINE__);

        $config_value = ($state) ? 'yes' : 'no';

        $file = new File(self::FILE_APP_CONFIG);

        if ($file->exists())
            $file->delete();

        $file->create('root', 'root', '0644');

        $file->add_lines("auto_configure = $config_value\n");
    }

    /**
     * Sets DNS server pushed out to clients.
     *
     * @param string $ip DNS server IP
     *
     * @return void
     * @throws Engine_Exception
     */

    public function set_dns_server($ip)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_dns_server($ip));

        $this->_set_dhcp_parameter('DNS', $ip);
    }

    /**
     * Sets domain name pushed out to clients.
     *
     * @param string $domain domain name
     *
     * @return void
     * @throws Engine_Exception
     */

    public function set_domain($domain)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_domain($domain));

        $this->_set_dhcp_parameter('DOMAIN', $domain);
    }

    /**
     * Sets push routes.
     *
     * @param array $routes routes
     *
     * @return void
     * @throws Engine_Exception
     */

    public function set_push_routes($routes)
    {
        clearos_profile(__METHOD__, __LINE__);

        $configs = array(self::FILE_CLIENTS_CONFIG, self::FILE_CLIENTS_CONFIG_TCP);

        foreach ($configs as $config) {
            $file = new File($config);
            $file->delete_lines("/^push\s+\"route\s+/");

            foreach ($routes as $route)
                $file->add_lines("push \"route $route\"\n");
        }
    }

    /**
     * Sets WINS server pushed out to clients.
     *
     * @param string $ip WINS server IP
     *
     * @return void
     * @throws Engine_Exception
     */

    public function set_wins_server($ip)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! empty($ip))
            Validation_Exception::is_valid($this->validate_wins_server($ip));

        $this->_set_dhcp_parameter('WINS', $ip);
    }

    ///////////////////////////////////////////////////////////////////////////////
    // V A L I D A T I O N  M E T H O D S 
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Validation routine for DNS server.
     *
     * @param string $ip IP address
     *
     * @return string error message if DNS server IP address is invalid
     */

    public function validate_dns_server($ip)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! Network_Utils::is_valid_ip($ip))
            return lang('network_dns_server_invalid');
    }

    /**
     * Validation routine for domain.
     *
     * @param string $domain domain
     *
     * @return string error message if domain is invalid
     */

    public function validate_domain($domain)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! Network_Utils::is_valid_domain($domain, TRUE))
            return lang('network_internet_domain_invalid');
    }

    /**
     * Validation routine for WINS server.
     *
     * @param string $ip IP address
     *
     * @return string error message if WINS server IP address is invalid
     */

    public function validate_wins_server($ip)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! Network_Utils::is_valid_ip($ip))
            return lang('network_wins_server_invalid');
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E  M E T H O D S 
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Loads configuration files.
     *
     * @access private
     *
     * @return void
     * @throws Engine_Exception
     */

    protected function _load_config()
    {
        clearos_profile(__METHOD__, __LINE__);

        $configfile = new File(self::FILE_CLIENTS_CONFIG);

        $lines = $configfile->get_contents_as_array();
        $matches = array();

        foreach ($lines as $line) {
            if (preg_match('/^push\s+"route\s+([^\s+]*)\s+([^"]*)"\s*$/', $line, $matches)) {
                $this->config['push']['route'][$matches[1] . "/" . $matches[2]] = TRUE;
            } else if (preg_match('/^push\s+"dhcp-option\s+([^\s+]*)\s+([^"]*)"\s*$/', $line, $matches)) {
                $this->config['push']['dhcp-option'][$matches[1]] = $matches[2];
            } else if (preg_match('/^push\s+"redirect-gateway"\s*$/', $line, $matches)) {
                $this->config['push']['redirect-gateway'] = TRUE;
            } else if (preg_match('/^push\s+"(.*)"\s*$/', $line, $matches)) {
                // Ignore other push parameters for now
            } else if (preg_match('/^([a-zA-Z][^\s]*)\s+(.*)$/', $line, $matches)) {
                $this->config[$matches[1]] = $matches[2];
            }
        }

        $this->is_loaded = TRUE;
    }

    /**
     * Sets a parameter in the config file.
     *
     * @param string $key   name of the key in the config file
     * @param string $value value for the key
     *
     * @access private
     * @return void
     * @throws Engine_Exception
     */

    protected function _set_dhcp_parameter($key, $value)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->is_loaded = FALSE;

        $configs = array(self::FILE_CLIENTS_CONFIG, self::FILE_CLIENTS_CONFIG_TCP);

        foreach ($configs as $config) {
            $file = new File($config);
            if (empty($value)) {
                $file->delete_lines("/^push\s+\"dhcp-option\s+$key\s+/");
            } else {
                $match = $file->replace_lines("/^push\s+\"dhcp-option\s+$key\s+/", "push \"dhcp-option $key $value\"\n");

                if (!$match)
                    $file->add_lines("push \"dhcp-option $key $value\"\n");
            }
        }
    }
}
