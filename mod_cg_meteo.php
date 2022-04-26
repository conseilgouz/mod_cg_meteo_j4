<?php
/**
* Simple meteo module
* Version			: 2.0.3
* Package			: Joomla 4.0.x
* copyright 		: Copyright (C) 2021 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined( '_JEXEC' ) or die;
use Joomla\CMS\Factory;
use ConseilGouz\Module\CGMeteo\Site\Helper\MeteoHelper;

$modulefield	= 'media/mod_cg_meteo/';

$helper = new MeteoHelper;
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

$wa->registerAndUseStyle('cgstyle', $modulefield.'css/style.css');
$wa->registerAndUseStyle('cgup', $modulefield.'css/up.css');

if ($params->get("country","fr") == "fr") {
	$ville = $params->get('ville');
	$long = $params->get('long');
	$lat = $params->get('lat');
} else {
	$ville = $params->get('ville_wd');
	$long = $params->get('long_wd');
	$lat = $params->get('lat_wd');
}

$output = "<div id='cg_meteo_".$module->id."' name='cg_meteo_".$module->id."' method=get class='mod_cg_meteo".$params->get( 'moduleclass_sfx' )."'>\n";

if ($params->get('temp_unit') == 'f') { $temp_unit = 'f'; } else { $temp_unit = 'c'; };

if ($params->get('meteo_api','yahoo') == "open" ) {
	if ($params->get('needopen','0') == 1) {
		$helper->meteo_open($lat,$long, $ville, $temp_unit,$params->get('openkey') );
	} else {
		$helper->meteo_open($lat,$long, $ville, $temp_unit);
	}
}
if ($params->get('meteo_api','yahoo') == "yahoo" ) {
	$helper->meteo_yahoo($params->get('woeid'), $temp_unit);
}
if ($params->get('meteo_api','yahoo') == "xu" ){
	if ($params->get('needxu','0') == 1) {
            $helper->meteo_xu($lat,$long, $ville,$temp_unit,$params->get('xukey'));
        } else {
            $helper->meteo_xu($lat,$long, $ville,$temp_unit);
        }
}
if ($params->get('meteo_api','yahoo') == "bit" ){
	if ($params->get('needbit','0') == 1) {
            $helper->meteo_bit($lat,$long, $ville,$temp_unit,$params->get('bitkey'));
        } else {
            $helper->meteo_bit($lat,$long, $ville,$temp_unit);
        }
}
if ($params->get('meteo_api','yahoo') == "darksky" ){
	if ($params->get('needdarksky','0') == 1) {
            $helper->meteo_darksky($lat,$long, $ville,$temp_unit,$params->get('darkskykey'));
        } else {
            $helper->meteo_darksky($lat,$long, $ville,$temp_unit);
        }
}

if ($helper->isfound()) {
	$icon_path = JURI::Base(false)."media/mod_cg_meteo/icons/";
	$actuelle = $helper->getCurrent();
	$img_margin = "5px 0";
	if ($params->get('img_align')=="left") { $img_margin = "0 10px 5px 0";  }
	if ($params->get('img_align')=="right") { $img_margin = "0 0 5px 10px";  }
	$now = date('');
	if ($params->get('afficher_date', 0)) $output .=  "<div class='cg_meteo_date'>".JHtml::date( $now, JText::_($params->get('afficher_date')))."</div>";
	if ($params->get('afficher_ville', 1)) $output .=  "<div class='cg_meteo_city'>".$helper->getCity()."</div>";

	switch ($params->get('temp_unit')) {
		case 'c':
			$tmp_actuelle = $actuelle['temp']."&nbsp;&deg;C";
			$unit = "C";
			break;
		case 'f':
			$tmp_actuelle = $actuelle['temp']."&nbsp;&deg;F";
			$unit = "F";
				break;
				default:
					if ($helper->getUnit_system() == 'C') {
						$tmp_actuelle = "(&nbsp;".round(($actuelle['temp']*9/5)+32)."&nbsp;&deg;F&nbsp;/&nbsp;";
						$tmp_actuelle .= $actuelle['temp']."&nbsp;&deg;C&nbsp;)";
						$unit = false;
					} else {
						$tmp_actuelle = "(&nbsp;".$actuelle['temp']."&nbsp;&deg;F&nbsp;/&nbsp;";
						$tmp_actuelle .= round(($actuelle['temp']-32)*5/9)."&nbsp;&deg;C&nbsp;)";
						$unit = false;
					}
				}
	// output previsions
	$previsions = $helper->getForecast();
	$unit_system = $helper->getUnit_system();
	if (!$unit) $unit = ($unit_system=="US") ? "F" : "C";
	if ($params->get('meteo_api','yahoo') == "open" ) $output .= include dirname(__FILE__).'/tmpl/previsions_open.php';
	if ($params->get('meteo_api','yahoo') == "yahoo" ) $output .= include dirname(__FILE__).'/tmpl/previsions_yahoo.php';
	if ($params->get('meteo_api','yahoo') == "xu" ) $output .= include dirname(__FILE__).'/tmpl/previsions_xu.php';
	if ($params->get('meteo_api','yahoo') == "darksky" ) $output .= include dirname(__FILE__).'/tmpl/previsions_darksky.php';
	if ($params->get('meteo_api','yahoo') == "bit" ) $output .= include dirname(__FILE__).'/tmpl/previsions_bit.php';
	
} else {
	$output .=error_get_last()['message'];
}
$output .= "</div>";
	
echo $output;
?>
