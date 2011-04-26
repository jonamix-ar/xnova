<?php
/*
  This file is part of WOT Game.

    WOT Game is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WOT Game is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with WOT Game.  If not, see <http://www.gnu.org/licenses/>.
*/

// define (ugamela) constants
define('INSIDE', true);
define('EVENT_HANDLER', true);
define('WCF_DIR', dirname(__FILE__).'/../wcf/');
define('NO_SHUTDOWN_QUERIES', true);
define('DEFAULT_SKINPATH',"http://xen251.linea7.net/game/game/alysium/");
define('TEMPLATE_DIR',"templates/");
define('TEMPLATE_NAME',"OpenGame");
define('DEFAULT_LANG','de');

// include ugamela
$ugamela_root_path = '/srv/www/htdocs/';
include($ugamela_root_path . 'extension.inc');

// mastercook patch
$_SERVER['REQUEST_METHOD'] = '';
$_SERVER['SERVER_PORT'] = '';

// include wcf
if(!defined('WCF_DIR')) define('WCF_DIR', dirname(__FILE__).'/../wcf/');
require_once($ugamela_root_path.'game/global.php');
//require_once($ugamela_root_path.'wotapi/global.php');
restore_error_handler();

// include some ugamela libs
require($ugamela_root_path.'includes/vars.php');
require($ugamela_root_path.'includes/functions.php');
require($ugamela_root_path.'includes/strings.php');

// load config
$sql = "SELECT *
		FROM ugml_config";
$config = WCF::getDB()->getResultList($sql);
$game_config = array();
foreach($config as $row) {
	$game_config[$row['config_name']] = $row['config_value'];
}

if(!defined('WOTAPI_SERVER_RUNTIME')) define('WOTAPI_SERVER_RUNTIME', 5);
define('END_TIME', TIME_NOW + 5 + WOTAPI_SERVER_RUNTIME * 60);
if(!defined('SERVER_ID')) define('SERVER_ID', 8);

// check lock
require_once(LW_DIR.'lib/util/LockUtil.class.php');

do {
	$locked = false;

	// check
	try {
		LockUtil::checkLock('wotapipserver');
	} catch(SystemException $e) {
		echo 'l';
		$locked = true;
		// wait 0.05s
		usleep(50000);
		
		// remove if waiting 15s already
		if(time() - TIME_NOW > 15) {
			LockUtil::removeLock('wotapipserver');
			echo 'removed lock';
		}
	}
} while($locked);
LockUtil::setLock('wotapipserver', END_TIME + 5);

// load
require_once(LW_DIR.'lib/system/io/socket/WOTAPIServer.class.php');
require_once(LW_DIR.'lib/system/io/socket/WOTAPIServerClient.class.php');
require_once(WCF_DIR.'lib/system/io/socket/SocketDaemon.class.php');

if(!defined('WOTAPI_PORT')) define('WOTAPI_PORT', 57154);
if(!defined('CRYPTER_KEY')) define('CRYPTER_KEY', base64_decode('SAG ICH NICHT'));


// fire
$server = SocketDaemon::createServer('WOTAPIServer', 'WOTAPIServerClient', 0, WOTAPI_PORT);
SocketDaemon::process();
?>
