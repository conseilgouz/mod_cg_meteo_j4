<?php
/**
* CG meteo module from https://openweathermap.org
* Version			: 2.0.3
* Package			: Joomla 4.0.x
* copyright 		: Copyright (C) 2021 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
namespace ConseilGouz\Module\CGMeteo\Site\Helper;
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;

class MeteoHelper {
	private $city = '';
	private $domain_open = 'https://api.openweathermap.org/data/2.5';
	// private $domain_xu = 'http://api.apixu.com/v1/forecast.xml';
	private $domain_bit = 'https://api.weatherbit.io/v2.0/forecast/daily?';
	private $domain_darksky = 'https://api.darksky.net/forecast';
	
	private $current_conditions = array();
	private $forecast_conditions = array();
	private $is_found = false;
	private $response;
        //--------------------------------Meteo depuis Yahoo V2-------------------------------------------------//
function buildBaseString($baseURI, $method, $params) {
    $r = array();
    ksort($params);
    foreach($params as $key => $value) {
        $r[] = "$key=" . rawurlencode($value);
    }
    return $method . "&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r));
}
function buildAuthorizationHeader($oauth) {
    $r = 'Authorization: OAuth ';
    $values = array();
    foreach($oauth as $key=>$value) {
        $values[] = "$key=\"" . rawurlencode($value) . "\"";
    }
    $r .= implode(', ', $values);
    return $r;
}
		
	function meteo_yahoo ($woeid, $unit='c') {
		trigger_error('Yahoo erreur : yahoo service indisponible depuis 1er janvier 2019');
			return null;
	}
        //------------------------------Meteo depuis WeatherBit.IO ------------------------------------------------------------//
	function meteo_bit ($lat,$long,$city, $unit='c',$appid ='ec02dd82ca384ce5a1c038d1e89f9d81') {
		$getContentCode = "";
		$this->url = $this->domain_bit . "key=".$appid."&lat=".$lat."&lon=".$long."&days=3&lang=fr";
		//recupere les donnees sur le serveur WeatherBit.IO
		if (extension_loaded("curl")) {
			$getContentCode = $this->getCurlContent($this->url);
			if ($getContentCode != 200) { 
				$getContentCode = $this->getHttpContent($this->url, $getContentCode);
				}
		} else {
			$getContentCode = $this->getHttpContent($this->url, $getContentCode);
		}
		if($getContentCode == 200) { // pas d'erreur
			$content = $this->response;
			$xml = json_decode($content); //simplexml_load_string($content);
			$this->is_found = false;
			if(!isset($xml->ClientError)) {
				$this->city = (string)$city;
				//units
				$this->current_conditions['unit_temp'] = $unit;
				$this->current_conditions['unit_dest'] = "km";
				$this->current_conditions['unit_pres'] = "";
				$this->current_conditions['unit_vit'] = "km";
				
				$this->current_conditions['leve'] = "";
				//condition
				foreach($xml->data as $value) {
					if ($this->current_conditions['leve'] == "") { // Current day
					    $this->current_conditions['tz'] = $xml->timezone;
					    $this->current_conditions['leve'] = (string)$value->sunrise_ts;
						$this->current_conditions['couche'] = (string)$value->sunset_ts;
						$this->current_conditions['condition'] = (string)$value->weather->description;
						$this->current_conditions['temp'] =(int)$value->temp;
						$this->current_conditions['icon'] = (string)$value->weather->icon;
						$this->current_conditions['date'] = (string)$value->valid_date;
				//atmosphere
						$this->current_conditions['humidity'] = (string)$value->rh;
						$this->current_conditions['pop'] = (string)$value->pop;
						$this->current_conditions['pressure'] = (string)$value->pres;
						// $this->current_conditions['rising'] = (string)$value->is_day;
				//vent
						$this->current_conditions['chill'] = (string)$value->rh;
						$this->current_conditions['direction'] = (string)$value->wind_cdir;
						$this->current_conditions['speed'] = (string)$value->wind_spd;

					}
					$this->forecast_conditions_temp = array();
					$this->forecast_conditions_temp['day_of_week'] = (string)$value->valid_date;
					$this->forecast_conditions_temp['date'] = (string)$value->valid_date;
					$this->forecast_conditions_temp['low'] = (int)$value->min_temp  ;
					$this->forecast_conditions_temp['high'] = (int)$value->max_temp;
					$this->forecast_conditions_temp['icon'] = (string)$value->weather->icon;
					$this->forecast_conditions_temp['condition'] = (string)$value->weather->description;
					$this->forecast_conditions []= $this->forecast_conditions_temp;
				}
				$this->is_found = true;
			}
		} else {
			if ($getContentCode) trigger_error('XU API erreur '.$getContentCode);
			return null;
		}
	}
        //------------------------------Meteo depuis XU API ------------------------------------------------------------//
	function meteo_xu ($lat,$long,$city, $unit='c',$appid ='') {
		trigger_error('XU API service indisponible depuis 1er Octobre 2019');
			return null;
	}
        //------------------------ Meteo depuis Open Weather Map API ------------------------------------------------------//
	function meteo_open ($lat,$long, $city, $unit='c',$appid ='6e1ac4dcfa20f4ce89badbb873599a7b') {
		$getContentCode = "";
		if ($unit == 'c') {
			$units = "metric";
		} else {
			$units = "imperial";
		}
		$this->city = (string)$city;
		// get current weather
		$this->url = $this->domain_open . "/weather?lat=" . $lat . "&lon=".$long."&mode=xml&lang=fr&cnt=3&units=".$units."&appid=".$appid;
		//recupere les donnees sur le serveur OpenWeatherMap
		if (extension_loaded("curl")) {
			$getContentCode = $this->getCurlContent($this->url);
			if ($getContentCode != 200) { 
				$getContentCode = $this->getHttpContent($this->url, $getContentCode);
			}
		} else {
			$getContentCode = $this->getHttpContent($this->url, $getContentCode);
		}
		if($getContentCode == 200) { // pas d'erreur
			$content = $this->response;
			$xml = simplexml_load_string($content);
			$this->is_found = false;
			if(!isset($xml->ClientError)) {
				
			    $this->current_conditions['leve'] = (string)HTMLHelper::date($xml->city->sun->attributes()->rise,'Y-m-dTH:i:s');
			    $this->current_conditions['couche'] = (string)HTMLHelper::date($xml->city->sun->attributes()->set,'Y-m-dTH:i:s');
				//units
				$this->current_conditions['unit_temp'] = $unit;
				$this->current_conditions['unit_dest'] = "m";
				$this->current_conditions['unit_pres'] = "";
				$this->current_conditions['unit_vit'] = "km";
				//condition
				$this->current_conditions['condition'] = (string)$xml->weather->attributes()->value;
				$this->current_conditions['temp'] = (int)$xml->temperature->attributes()->value;
				$this->current_conditions['icon'] = (string)$xml->weather->attributes()->icon;
				$this->current_conditions['date'] = (string)$xml->dt;  ///////////////////////////////
				//atmosphere
				$this->current_conditions['humidity'] = (string)$xml->humidity->attributes()->value;
				$this->current_conditions['visibility'] = (string)$xml->visibility->attributes()->value;
				$this->current_conditions['pressure'] = (string)$xml->pressure->attributes()->value;
				//vent
				$this->current_conditions['chill'] = (string)$xml->humidity->attributes()->value;
				$this->current_conditions['direction'] = (string)$xml->wind->direction->attributes()->code;
				$this->current_conditions['speed'] = (string)$xml->wind->speed->attributes()->value;
				$this->is_found = true;
			}
		} else {
			if ($getContentCode) trigger_error('Open Weather Map API : Erreur '.$getContentCode);
			return null;
		}
		$getContentCode = "";
		// get 3 days forecast
		$this->url = $this->domain_open . "/forecast/daily?lat=" . $lat . "&lon=".$long."&mode=xml&lang=fr&cnt=3&units=".$units."&appid=".$appid;
		//recupere les donnees sur le serveur OpenWeatherMap
		if (extension_loaded("curl")) {
			$getContentCode = $this->getCurlContent($this->url);
			if ($getContentCode != 200) { 
				$getContentCode = $this->getHttpContent($this->url, $getContentCode);
			}
		} else {
			$getContentCode = $this->getHttpContent($this->url, $getContentCode);
		}
		if($getContentCode == 200) { // pas d'erreur
			$content = $this->response;
			$xml = simplexml_load_string($content);
			$this->is_found = false;
			if(!isset($xml->ClientError)) {
				foreach($xml->forecast->time as $value) {
					$this->forecast_conditions_temp = array();
					$this->forecast_conditions_temp['day_of_week'] = (string)$value->attributes()->day;
					$this->forecast_conditions_temp['date'] = (string)$value->attributes()->day;
					$this->forecast_conditions_temp['low'] = (int)$value->temperature->attributes()->min;
					$this->forecast_conditions_temp['high'] = (int)$value->temperature->attributes()->max;
					$this->forecast_conditions_temp['icon'] = (string)$value->symbol->attributes()->var;
					$this->forecast_conditions_temp['condition'] = (string)$value->symbol->attributes()->name;
					$this->forecast_conditions []= $this->forecast_conditions_temp;
				}
				$this->is_found = true;
			}
		} else {
			if ($getContentCode) trigger_error('Open Weather Map API : Erreur '.$getContentCode);
			return null;
		}
	}
       //------------------------------Meteo depuis DarkSky API ------------------------------------------------------------//
	function meteo_darksky ($lat,$long,$city, $unit='c',$appid ='9a5cd5cae52ccc74e8c655a533d640fd') {
		$getContentCode = "";
		$this->url = $this->domain_darksky . "/".$appid."/".$lat.",".$long."?exclude=minutely,hourly,alerts,flags&lang=fr&units=si";
		//recupere les donnees sur le serveur
		if (extension_loaded("curl")) {
			$getContentCode = $this->getCurlContent($this->url);
			if ($getContentCode != 200) { 
				$getContentCode = $this->getHttpContent($this->url, $getContentCode);
				}
		} else {
			$getContentCode = $this->getHttpContent($this->url, $getContentCode);
		}
		if($getContentCode == 200) { // pas d'erreur
			$content = $this->response;
			$xml = json_decode($content);
			$this->is_found = false;
			if(!isset($xml->ClientError)) {
				$this->city = (string)$city;
				//units
				$this->current_conditions['unit_temp'] = $unit;
				$this->current_conditions['unit_dest'] = "km";
				$this->current_conditions['unit_pres'] = "";
				$this->current_conditions['unit_vit'] = "km";
				//condition
				$this->current_conditions['condition'] = (string)$xml->currently->summary;
				$this->current_conditions['temp'] = (int)$xml->currently->temperature;
				$this->current_conditions['icon'] = (string)$xml->currently->icon;
				$this->current_conditions['date'] = (string)$xml->currently->time;
				//atmosphere
				$this->current_conditions['humidity'] = (string)$xml->currently->humidity;
				$this->current_conditions['visibility'] = (string)$xml->currently->visibility;
				$this->current_conditions['pressure'] = (string)$xml->currently->pressure;
				// ======================$this->current_conditions['rising'] = (string)$xml->current->is_day;
				//vent
				$this->current_conditions['chill'] = (string)$xml->currently->humidity;
				$this->current_conditions['direction'] = (string)$xml->currently->windBearing;
				$this->current_conditions['speed'] = (string)$xml->currently->windSpeed;
				$this->current_conditions['leve'] = "";
				$this->current_conditions['tz'] = (string)$xml->timezone; // time zone
				foreach($xml->daily->data as $value) {
					if ($this->current_conditions['leve'] == "") {
						$this->current_conditions['leve'] = (string)$value->sunriseTime;
						$this->current_conditions['couche'] = (string)$value->sunsetTime;
					}
					$this->forecast_conditions_temp = array();
					$this->forecast_conditions_temp['day_of_week'] = (string)$value->time;
					$this->forecast_conditions_temp['date'] = (string)$value->time;
					$this->forecast_conditions_temp['low'] = (int)$value->temperatureMin  ;
					$this->forecast_conditions_temp['high'] = (int)$value->temperatureMax;
					$this->forecast_conditions_temp['icon'] = (string)$value->icon;
					$this->forecast_conditions_temp['condition'] = (string)$value->summary;
					$this->forecast_conditions []= $this->forecast_conditions_temp;
				}
				$this->is_found = true;
			}
		} else {
			if ($getContentCode) trigger_error('DarkSky API erreur '.$getContentCode);
			return null;
		}
	}	
    private function prepareTime($time) {
        $f_date = date("Y-m-d")." ".$time;
        $pos = strpos($f_date, "pm");
        $f_date = preg_replace('/ [a-z][a-z]/', ':00', $f_date);
        return strtotime($f_date) + (($pos !== FALSE) ? 12*3600 : 0);
    }
	private function getCurlContent($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_URL, $url);
        $this->response = curl_exec($curl);
		$infos = curl_getinfo($curl);
        curl_close ($curl);
        return $infos['http_code'];
    }
	private function getHttpContent($url, $infos)
    {
		if ($this->response = @file_get_contents($url)) {
        	return 200;
		}
                $infos = $http_response_header[0];
		return '2000'.' '.$infos;
    }
	function getCity() {
		return $this->city;
	}
	function getUnit_system() {
		return $this->current_conditions['unit_temp'];
	}
	function getCurrent() {
		return $this->current_conditions;
	}
	function getForecast() {
		return $this->forecast_conditions;
	}
	function isFound() {
		return $this->is_found;
	}
}