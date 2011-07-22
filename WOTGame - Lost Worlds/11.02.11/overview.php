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
 //overview.php   by DxPpLmOs
define('INSIDE', true);
$ugamela_root_path = './';
include($ugamela_root_path . 'extension.inc');
include($ugamela_root_path . 'common.'.$phpEx);
//include('ban.php');

if(!check_user()){ header("Location: login.$phpEx"); die();}

includeLang('overview');
includeLang('tech');
/*
  Checkear el tema de la lista de flotas
*/
include($ugamela_root_path . 'includes/planet_toggle.'.$phpEx);//Esta funcion permite cambiar el planeta actual.

//$planetrow = doquery("SELECT * FROM {{table}} WHERE id={$user['current_planet']}",'planets',true);
//$lunarow = doquery("SELECT * FROM {{table}} WHERE id={$user['current_luna']}",'lunas',true);
//$galaxyrow = doquery("SELECT * FROM {{table}} WHERE id_planet={$planetrow['id']}",'galaxy',true);
$dpath = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];

check_field_current($planetrow);
check_field_current($lunarow);
//die('nO');

switch (@$_GET['mode'])
{
case 'renameplanet':{//Abandonar o renombrar planetas

	if($_POST['action'] == $lang['namer']){

		$newname = trim($_POST['newname']);

		if(!preg_match("/[^A-z0-9\ _\-]/", $newname) == 1 && $newname != ""){
			/*
			  Realmente no lo encuentro muy necesario. e incluso es esguro,
			  porque si o si, se nombra en base al planeta actual
			*/
			$planetrow['name'] = $newname;
			doquery("UPDATE {{table}} SET `name`='$newname' WHERE `id`='{$user['current_planet']}' LIMIT 1","planets");

		}
	}
	elseif($_POST['action'] == $lang['colony_abandon']){

		$parse = $lang;
		$parse['planet_id'] = $planetrow['id'];
		$parse['galaxy_galaxy'] = $galaxyrow['galaxy'];
		$parse['galaxy_system'] = $galaxyrow['system'];
		$parse['galaxy_planet'] = $galaxyrow['planet'];
		$parse['planet_name'] = $planetrow['name'];

		$page .= parsetemplate(gettemplate('overview_deleteplanet'), $parse);

		display($page,$lang['rename_and_abandon_planet']);

	}
	elseif($_POST['deleteid'] == $user['current_planet']){


			$sql = "DELETE FROM ugml_planets
			message($lang['deletemessage_ok'],$lang['colony_abandon'],'overview.php?mode=renameplanet');
		}elseif($user['id_planet'] == $user["current_planet"]){
			message($lang['deletemessage_wrong'],$lang['colony_abandon'],'overview.php?mode=renameplanet');
		}else{message($lang['deletemessage_fail'],$lang['colony_abandon'],'overview.php?mode=renameplanet');}

	}

	$parse = $lang;

	$parse['planet_id'] = $planetrow['id'];
	$parse['galaxy_galaxy'] = $galaxyrow['galaxy'];
	$parse['galaxy_system'] = $galaxyrow['system'];
	$parse['galaxy_planet'] = $galaxyrow['planet'];
	$parse['planet_name'] = $planetrow['name'];
//	$parse['luna_name'] = $lunarow['name'];



	$page .= parsetemplate(gettemplate('overview_renameplanet'), $parse);

	display($page,$lang['rename_and_abandon_planet']);

}

default:{
	$newMessagesCount = $user['new_message'];
$sql = "SELECT ugml_fleet.*,
						GROUP_CONCAT(
							CONCAT(specID, ',', shipCount) 
							SEPARATOR ';')
						AS fleet
    	LEFT JOIN ugml_fleet_spec
    		ON ugml_fleet.fleetID = ugml_fleet_spec.fleetID
		GROUP BY ugml_fleet.fleetID
		ORDER BY ugml_fleet.impactTime,
			ugml_fleet.returnTime";
require_once(LW_DIR.'lib/data/fleet/Fleet.class.php');
	$fleet = Fleet::getInstance(null, $row);
	
	$fleetArray += $fleet->getFleetSet();
	/*
	  Cuando un jugador tiene mas de un planeta, se muestra una lista de ellos a la derecha.
	*/

	$planets_query = doquery("SELECT * FROM {{table}} WHERE id_owner='{$user['id']}' AND planet_type = 1 ORDER BY sortID","planets");
	$c = 1;
	while($p = mysql_fetch_array($planets_query)){

		if($p["id"] != $user["current_planet"]){
			$ap .= "<th>{$p['name']}<br>
			<a href=\"?cp={$p['id']}&re=0\" title=\"{$p['name']}\"><img src=\"{$dpath}planeten/small/s_{$p['image']}.jpg\"></a><br>
			<center>";
			/*
			  Gracias al 'b_building_id' y al 'b_building' podemos mostrar en el overview
			  si se esta construyendo algo en algun planeta.
			*/
			if($p['b_building_id'] != 0){
				if(check_building_progress($p)){
					$ap .= $lang['tech'][$p['b_building_id']];
					$time = pretty_time($p['b_building'] - time());
					$ap .= "<br><font color=\"#7f7f7f\">({$time})</font>";
				}
				else{$ap .= $lang['Free'];}
			}else{$ap .= $lang['Free'];}

			$ap .= "<center></center></center></th>";
			//Para ajustar a dos columnas
			if($c <= 1){$c++;}else{$ap .= "</tr><tr>";$c = 1;	}
		}
	}


                $parse['FLOTA_TEST'] = ($user['authlevel'] == 1||$user['authlevel'] == 3)?'<tr><td><div align="center"><font color="#FFFFFF"><a href="buildings.php?mode=fleet" accesskey="u" target="{mf}">KLIKNIJ TUTAJ NA TEST FLOTY</a></font></div></td></tr>
':'';

	$parse = $lang;
	$parse['moon_img'] ="";
	$parse['moon'] = "";
	//}
	$parse['planet_name'] = $planetrow['name'];
	$parse['planet_diameter'] = $planetrow['diameter'];
	$parse['planet_field_current'] = LWCore::getPlanet()->getUsedFields();
	$parse['planet_field_max'] = LWCore::getPlanet()->getMaxFields();
	$parse['planet_temp_min'] = $planetrow['temp_min'];
	$parse['planet_temp_max'] = $planetrow['temp_max'];
	$parse['galaxy_galaxy'] = $planetrow['galaxy'];
	$parse['galaxy_planet'] = $planetrow['planet'];
	$parse['galaxy_system'] = $planetrow['system'];
	$parse['user_points'] = StringUtil::formatInteger(intval(WCF::getUser()->wotPoints));
	//$parse['user_fleet'] = pretty_number($user['points_fleet_old']/1000); + pretty_number($user['points_builds2']/1000);
	//$parse['user_buili'] = pretty_number($user['points_builds']/1000);
	//$parse['player_points_tech'] = pretty_number($user['points_tech_old']/1000);
	$parse['user_rank'] = intval(WCF::getUser()->wotRank);
	$ile = $user['rank_old'] - $user['rank'];
	if ($ile >= 1)
	{
	$parse['ile'] = "<font color=lime>+".$ile."</font>";
	}
	if ($ile < 0)
	{
	$parse['ile'] = "<font color=red>-".$ile."</font>";
	}
	if ($ile == 0)
	{
	$parse['ile'] = "<font color=lightblue>".$ile."</font>";
	}
	$parse['u_user_rank'] = intval($user['rank']);
	$parse['user_username'] = $user['username'];
	$parse['fleet_list'] = $fpage;
	$parse['energy_used'] = $planetrow["energy_max"]-$planetrow["energy_used"];

	$parse['Have_new_message'] = $Have_new_message;
	$parse['time'] = date("D M d H:i:s",time());

	$parse['dpath'] = $dpath;

	$parse['planet_image'] = $planetrow['image'];
	$parse['anothers_planets'] = $ap;
	$parse['max_users'] = $game_config['users_amount'];
	$parse['metal_debris'] = $galaxyrow['metal'];
	$parse['crystal_debris'] = $galaxyrow['crystal'];
	if(($galaxyrow['metal']!=0||$galaxyrow['crystal']!=0)&&$planetrow[$resource[209]]!=0){
		$parse['get_link'] = " (<a href=\"quickfleet.php?mode=harvest&g={$galaxyrow['system']}&s={$galaxyrow['system']}&p={$galaxyrow['planet']}\">{$lang['Harvest']}</a>)";
	}else{$parse['get_link'] = '';}
	if($planetrow['b_building_id']!=0&&$planetrow['b_building']>time()){
		$parse['building'] = $lang['tech'][$planetrow['b_building_id']].
		'<br><div id="bxx" class="z">'.pretty_time($planetrow['b_building'] - time()).'</div><SCRIPT language=JavaScript>
		pp="'.($planetrow['b_building'] - time()).'";
		pk="'.$planetrow["b_building_id"].'";
		pl="'.$planetrow["id"].'";
		ps="buildings.php";
		t();
	</script>';
	}else{
		$parse['building'] = $lang['Free'];
	}
{
    /*            $query = doquery('SELECT username FROM {{table}} ORDER BY register_time DESC','users',true);
	$parse['last_user'] = $query['username'];
	$query = doquery("SELECT COUNT(DISTINCT(id)) FROM {{table}} WHERE onlinetime>".(time()-900),'users',true);
	$parse['online_users'] = $query[0];
	$parse['users_amount'] = $game_config['users_amount'];*/


}
	$diliziumFeatures = unserialize($user['diliziumFeatures']);
	$tplName = 'overview_body';
	if(@$diliziumFeatures['noAds'] > time()) {
		$tplName = 'overview_body_na';
	}

	$page = parsetemplate(gettemplate($tplName), $parse);


	display($page,$lang['Overview']);

}

}
?>