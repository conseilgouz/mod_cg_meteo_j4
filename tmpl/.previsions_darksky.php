<?php
/**
* Simple meteo module from https://darksky.net/dev
* Version			: 2.0.2
* Package			: Joomla 3.9.x
* copyright 		: Copyright (C) 2021 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

defined( '_JEXEC' ) or die( 'Direct Access not allowed.' );
$return = "";
$font_color = $params->get('meteo_font_color',"#4c0ed8");

$icons = array("clear-day" =>"32","clear-night"=>"31","partly-cloudy-day" => "28", "partly-cloudy-night" => "33", 
        "rain" => "39", "snow" => "13", "sleet" => "10", "wind"=>"23", "fog"=>"20","cloudy" => "26");

$return .= '<style type="text/css">';
$return .= '.cg_meteo_previsions {color:'.$font_color.' !important;}';
$return .= '.cg_meteo_date {color:'.$font_color.' !important;}';
$return .= '.cg_meteo_city {color:'.$font_color.' !important;}';

$return .= '</style>';

$return .= "<div class='meteo_previsions fg-row'>";
$i = 0;
foreach ($previsions as $prevision) {
	if ( (($params->get( 'meteo_previsions',1) == 0) && ($i > 0)) || ($i > 2) ) { break; }
	date_default_timezone_set($actuelle['tz']);
	$low = $prevision['low']; $high = $prevision['high'];
	// recalcul la temperature si necessaire
	$icon = basename($prevision['icon']);
	if (empty($icon) || !(array_key_exists($icon,$icons))) {
		$icon =  "44.png";
	} else { 
		$icon = $icons[$icon].".png";
	}
	$path_icon = JURI::base().'media/mod_cg_meteo/icons/'.$params->get('icon_yahoo_previsions').'/'.$icon;
	$img = '<img src="'.$path_icon.'" alt="'.$prevision['condition'].'" title="'.$prevision['condition'].'"  align="'.$params->get('img_align').'" style="margin:-10px 0 5px 5px;"/>';
	$return .= "<div class='cg_meteo_previsions fg-c4' ";
	$return .= ($params->get( 'meteo_previsions',1) == 0) ? "style='width:100%;'" : ""; // pas de pr�vision: on prend toute la largeur
	$return .= "><b>".JText::_(date('D',$prevision['date']))."</b><br/>"; 
	$return	.= $img."<br/>"
			.$low."&nbsp;<sup>o</sup>".$unit."<br/>".$high."&nbsp;<sup>o</sup>".$unit;
    if ($i == 0) { // aujourd'hui
		$img = "";
                $icon = basename($actuelle['icon']);
                if (empty($icon) || !(array_key_exists($icon,$icons))) {
                    $icon =  "44.png";
                } else { 
                    $icon = $icons[$icon].".png";
                }
		$path_icon = JURI::base().'modules/mod_simple_meteo/icons/'.$params->get('icon_yahoo_previsions').'/'.$icon;
		$img = "<img class='cg_meteo_img' src='".$path_icon."' alt='".$actuelle['condition']."'  title='".$actuelle['condition']."' align=".$params->get('img_align')."' style = 'margin:".$img_margin.";' />";
		$label = "<span class='infobulle_meteo' style='top:".$params->get( 'meteo_top',0)."em;'>".JText::_('MOD_METEO_NOW')."<br/>";
		$label .= $img;
		$label .= "<div><p>".$actuelle['condition']."</p> ";
		$label .= $tmp_actuelle;
		$label .= '<p />'.JText::sprintf('METEOFP_PRESSION', $actuelle['pressure'], $actuelle['unit_pres']);
		$label .= JText::sprintf('METEOFP_HUMIDITE', ($actuelle['humidity'] * 100));
		$label .= JText::sprintf('METEOFP_VISIBILITE', $actuelle['visibility'], $actuelle['unit_dest']);
		if ($actuelle['speed']) {
			$label .= JText::sprintf('METEOFP_VENT', $actuelle['speed'], $actuelle['unit_vit']);
		} else {
			$label .= JText::_('METEOFP_CALME');
		}
		//si direction = 0 => direction variable.
		if ($actuelle['direction'])  $label .= '&nbsp;'.JText::_( 'METEOFO_DIRECTION_'.(ceil(($actuelle['direction']-5.62)/11.25)+1) );
		$label .= '</p>';
		if ($params->get('t12_24', '24') == "24") {
                    $time12_24_leve = date('H:i',$actuelle['leve']);
                    $time12_24_couche = date('H:i',$actuelle['couche']);
		} else {
                    $time12_24_leve = date('h:i a',$actuelle['leve']);
                    $time12_24_couche = date('H:i',$actuelle['couche']);
                }
		$label .= JText::sprintf('METEO_LEVE', $time12_24_leve);
		$label .= JText::sprintf('METEO_COUCHE', $time12_24_couche)."</div>";
		$label .= "</span>";
		$return .= "<div style='display: block;overflow: hidden; padding-bottom: 5px;' class='cg_meteo_actuelle'>\n";
		$return .= "<div class='cg_meteo_img_span'  >".$label."</div>";
		unset($label);
		$return .= "</div>";
    }	// fin d'aujoourd'hui
	$return .= '</div>';
	$i++;
}
$return .= "</div>";
	
return $return;
?>
