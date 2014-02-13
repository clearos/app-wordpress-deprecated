<?php

/**
 * WordPress webapp driver.
 *
 * @category   apps
 * @package    wordpress
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2014 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/wordpress/
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

namespace clearos\apps\wordpress;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('wordpress');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

use \clearos\apps\base\File as File;
use \clearos\apps\system_database\System_Database as System_Database;
use \clearos\apps\webapp\Webapp_Engine as Webapp_Engine;

clearos_load_library('base/File');
clearos_load_library('system_database/System_Database');
clearos_load_library('webapp/Webapp_Engine');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * WordPress webapp driver.
 *
 * @category   apps
 * @package    wordpress
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2014 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/wordpress/
 */

class Webapp_Driver extends Webapp_Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * WordPress webapp constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);

        parent::__construct('wordpress');
    }

    /**
     * Returns admin URLs.
     *
     * @return array list of admin URLs
     * @throws Engine_Exception
     */

    function get_admin_urls()
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_load_config();

        $urls = array();

        if ($this->get_hostname_access())
            $urls[] = 'https://' . $this->get_hostname() . '/wp-admin';

        if ($this->get_directory_access())
            $urls[] = 'https://' . $this->_get_ip_for_url() . $this->get_directory() . '/wp-admin';

        return $urls;
    }

    /**
     * Returns database admin URL.
     *
     * @return string database admin URL
     * @throws Engine_Exception
     */

    function get_database_url()
    {
        clearos_profile(__METHOD__, __LINE__);

        return '/app/wordpress/database/login';
    }

    /**
     * Returns default directory access policy.
     *
     * @return boolean default directory access policy
     */

    function get_directory_access_default()
    {
        clearos_profile(__METHOD__, __LINE__);

        return TRUE;
    }

    /**
     * Returns default hostname access policy.
     *
     * @return boolean default hostname access policy
     */

    function get_hostname_access_default()
    {
        clearos_profile(__METHOD__, __LINE__);

        return TRUE;
    }

    /**
     * Returns webapp nickname.
     *
     * @return string webapp nickname
     */

    public function get_nickname()
    {
        clearos_profile(__METHOD__, __LINE__);

        return 'wordpress';
    }

    /**
     * Returns getting started message to guide end user.
     *
     * @return string getting started message
     */

    public function get_getting_started_message()
    {
        clearos_profile(__METHOD__, __LINE__);

        return lang('wordpress_getting_started_message');
    }

    /**
     * Returns getting started URL.
     *
     * @return string getting started URL
     */

    public function get_getting_started_url()
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($this->get_directory_access())
            return 'https://' . $this->_get_ip_for_url() . $this->get_directory() . '/wp-admin/install.php';

        if ($this->get_hostname_access())
            return 'https://' . $this->get_hostname() . '/wp-admin/install.php';
    }

    /**
     * Hook called by Webapp engine after unpacking files.
     *
     * @return void
     */

    // protected function _post_unpacking_hook()
    public function _post_unpacking_hook()
    {
        clearos_profile(__METHOD__, __LINE__);

        $database = new System_Database();
        $password = $database->get_password('wordpress');

        $target_path = $this->path_install . '/' . self::PATH_WEBROOT . '/' . self::PATH_LIVE . '/';

        $sample = new File($target_path . '/wp-config-sample.php', TRUE);

        $lines = $sample->get_contents_as_array();
        $new_lines = array();
        $salts = array('AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT');

        foreach ($lines as $line) {
            if (preg_match("/^define\('DB_NAME'/", $line)) {
                $new_lines[] = "define('DB_NAME', 'wordpress');";
            } else if (preg_match("/^define\('DB_USER'/", $line)) {
                $new_lines[] = "define('DB_USER', 'wordpress');";
            } else if (preg_match("/^define\('DB_PASSWORD'/", $line)) {
                $new_lines[] = "define('DB_PASSWORD', '" . $password . "');";
            } else if (preg_match("/^define\('DB_HOST'/", $line)) {
                $new_lines[] = "define('DB_HOST', '127.0.0.1:3308');";
            } else {
                $matched = FALSE;

                foreach ($salts as $salt) {
                    if (preg_match("/^define\('$salt'/", $line)) {
                        $matched = TRUE;
                        $new_lines[] = "define('$salt', '" . $this->_generate_password() . "');";
                    }
                }

                if (! $matched)
                    $new_lines[] = $line;
            }
        }

        $config = new File($target_path . '/wp-config.php', TRUE);

        if ($config->exists())
            $config->delete();

        $config->create('apache', 'apache', '0660');

        $config->dump_contents_from_array($new_lines);
    }
}
