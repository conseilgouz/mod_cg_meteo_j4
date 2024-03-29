<?php
/**
* Simple meteo module using APIXU : https://www.apixu.com/
* Version			: 2.1.0
* Package			: Joomla 4.x/5.x
* copyright 		: Copyright (C) 2023 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

defined( '_JEXEC' ) or die( 'Direct Access not allowed.' );
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Uri\Uri;

$return = "";
$font_color = $params->get('meteo_font_color',"#4c0ed8");
$return .= '<style type="text/css">';
$return .= '.cg_meteo_previsions {color:'.$font_color.' !important;}';
$return .= '.cg_meteo_date {color:'.$font_color.' !important;}';
$return .= '.cg_meteo_city {color:'.$font_color.' !important;}';

$return .= '</style>';

$return .= "<div class='meteo_previsions fg-row'>";
$i = 0;
foreach ($previsions as $prevision) {
	if ( (($params->get( 'meteo_previsions',1) == 0) && ($i > 0)) || ($i > 2) ) { break; }
	$low = $prevision['low']; $high = $prevision['high'];
	// recalcul la temperature si necessaire
	$icon = basename($prevision['icon']);
	if (empty($icon)) {
		$icon = "warning.png";
	}
	if (strpos($prevision['icon'],'/day/') > 0) { 
		$path_icon = URI::base().'media/mod_cg_meteo/icons/xu/day/'.$icon;
	} else {
		$path_icon = URI::base().'media/mod_cg_meteo/icons/xu/night/'.$icon;
	}
	$img = '<img src="'.$path_icon.'" alt="'.$prevision['condition'].'" title="'.$prevision['condition'].'"  align="'.$params->get('img_align').'" style="margin:-10px 0 5px 5px;"/>';
	$return .= "<div class='cg_meteo_previsions fg-c4' ";
	$return .= ($params->get( 'meteo_previsions',1) == 0) ? "style='width:100%;'" : ""; // pas de prevision: on prend toute la largeur
	$return .= "><b>".JText::_(date('D',strtotime($prevision['date'])))."</b><br/>"; 
	$return	.= $img."<br/>"
			.$low."&nbsp;<sup>o</sup>".$unit."<br/>".$high."&nbsp;<sup>o</sup>".$unit;
    if ($i == 0) { // aujourd'hui
		$img = "";
		$icon = basename($actuelle['icon']);
		if (empty($icon)) {
			$icon = "warning.png";
		}
		if (strpos($actuelle['icon'],'/day/') > 0) { 
			$path_icon = URI::base().'media/mod_cg_meteo/icons/xu/day/'.$icon;
		} else {
			$path_icon = URI::base().'media/mod_cg_meteo/icons/xu/night/'.$icon;
		}
		$img = "<img class='cg_meteo_img' src='".$path_icon."' alt='".$actuelle['condition']."'  title='".$actuelle['condition']."' align=".$params->get('img_align')."' style = 'margin:".$img_margin.";' />";
		$label = "<span class='infobulle_meteo' style='top:".$params->get( 'meteo_top',0)."em;'>".JText::_('MOD_METEO_NOW')."<br/>";
		$label .= $img;
		$label .= "<div><p>".$actuelle['condition']."</p> ";
		$label .= $tmp_actuelle;
		$label .= '<p />'.JText::sprintf('METEOFP_PRESSION', $actuelle['pressure'], $actuelle['unit_pres']);
		$label .= JText::sprintf('METEOFP_HUMIDITE', $actuelle['humidity']);
		$label .= JText::sprintf('METEOFP_VISIBILITE', $actuelle['visibility'], $actuelle['unit_dest']);
		if ($actuelle['speed']) {
			$label .= JText::sprintf('METEOFP_VENT', $actuelle['speed'], $actuelle['unit_vit']);
		} else {
			$label .= JText::_('METEOFP_CALME');
		}
		// 01.02.01 : wind dir = string
		$wind_dir = "";
		$wind_dir = JText::_( 'METEOWU_DIRECTION_'.$actuelle['direction'] );
        if ($wind_dir == "") {
			$label .= '&nbsp;'.'('.$actuelle['direction'].')';
        } else { 
			$label .= '&nbsp;'.$wind_dir;
        }
		$label .= '</p>';
		$time12_24_leve = substr($actuelle['leve'], -2);
		$heures_leve = (int)substr($actuelle['leve'], 0 ,2);
		$minutes_leve = substr($actuelle['leve'], -5 ,2);
		if ($params->get('t12_24', '24') == "24") {
			if ($time12_24_leve == "pm") {
				$heures_leve = $heures_leve + 12;
			}
			$time12_24_leve = "";
		}
		$time12_24_couche = substr($actuelle['couche'], -2);
		$heures_couche = (int)substr($actuelle['couche'], 0 ,2);
		$minutes_couche = substr($actuelle['couche'], -5 ,2);
		if ($params->get('t12_24', '24') == "24") {
			if ($time12_24_couche == "pm") {
				$heures_couche = $heures_couche + 12;
			}
			$time12_24_couche = "";
		}
		$label .= JText::sprintf('METEOFP_LEVE', $heures_leve, $minutes_leve, $time12_24_leve);
		$label .= JText::sprintf('METEOFP_COUCHE',$heures_couche , $minutes_couche, $time12_24_couche)."</div>";
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
