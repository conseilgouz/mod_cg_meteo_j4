/**
 * @package Simple Meteo Module
 * @version 2.0.3
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @Copyright (C) 2021 ConseilGouz. All Rights Reserved.
 * @author ConseilGouz 
 * 
 * depuis une idée de Yann (alias Daneel) dans son module MeteoFr suite à une discussion du forum Joomla : https://forum.joomla.fr/forum/joomla-3-x/administration/gestion-des-articles-ad/226900-article-non-s%C3%A3%C2%A9curis%C3%A3%C2%A9-en-https-avec-la-vignette-m%C3%A3%C2%A9t%C3%A3%C2%A9o-france
 * adaptation pour récupérer les codes GPS de la ville et vérification du nombre de reponses
*/
var timeout,callDelay=500
apiUrl="https://public.opendatasoft.com/api/records/1.0/search/?dataset=correspondance-code-insee-code-postal&q=",
apiWorld="https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&featuretype=city&q=";

jQuery(document).ready(function(){
	// opendatasoft request : France only
	jQuery('input[id="jform_params_ville"]').on("click mouseup keyup",function(){
		var e=jQuery(this).val() + " "+ jQuery('input[id="jform_params_code"]').val();
		e?(jQuery("#result").html("Chargement..."),timeout&&clearTimeout(timeout),
		timeout=setTimeout(function(){
			jQuery.getJSON(apiUrl+e,function(r){	
				r.nhits != 1&&(jQuery("#result").html("Vous avez "+r.nhits+" resultat(s). veuillez préciser votre demande."),jQuery("#jform_params_long").val(""),jQuery("#jform_params_lat").val("")),
				r.nhits ===1&&r.parameters.q===e&&(jQuery("#result").html("Le code INSEE de "+r.records[0].fields.nom_comm+" est "+r.records[0].fields.insee_com +" (lat : "+r.records[0].fields.geo_point_2d[0]+" , long: "+r.records[0].fields.geo_point_2d[1]+")"),
				jQuery("#jform_params_insee").val(r.records[0].fields.insee_com),jQuery("#jform_params_lat").val(r.records[0].fields.geo_point_2d[0]),jQuery("#jform_params_long").val(r.records[0].fields.geo_point_2d[1]),jQuery("#jform_params_code").val(r.records[0].fields.postal_code))})},callDelay)):jQuery("#result").html("")
	}),
	jQuery('input[id="jform_params_code"]').on("click mouseup keyup",function(){
		var e=jQuery('input[id="jform_params_ville"]').val() + " " +jQuery(this).val();
		e?(jQuery("#result").html("Chargement..."),timeout&&clearTimeout(timeout),
		timeout=setTimeout(function(){jQuery.getJSON(apiUrl+e,function(r){
			r.nhits != 1&&(jQuery("#result").html("Vous avez "+r.nhits+" resultat(s). veuillez préciser votre demande."),jQuery("#jform_params_long").val(""),jQuery("#jform_params_lat").val("")),
			r.nhits ===1&&r.parameters.q===e&&(jQuery("#result").html("Le code insee de "+r.records[0].fields.nom_comm+" est "+r.records[0].fields.insee_com+" (lat : "+r.records[0].fields.geo_point_2d[0]+" , long: "+r.records[0].fields.geo_point_2d[1]+")"),jQuery("#jform_params_ville").val(r.records[0].fields.nom_comm),jQuery("#jform_params_lat").val(r.records[0].fields.geo_point_2d[0]),jQuery("#jform_params_long").val(r.records[0].fields.geo_point_2d[1]),jQuery("#jform_params_code").val(r.records[0].fields.postal_code))})},callDelay)):jQuery("#result").html("")
	}),
	// Open Street request : world
	jQuery('input[id="jform_params_ville_wd"]').on("click mouseup keyup",function(){
		var e=jQuery(this).val() + ","+ jQuery('input[id="jform_params_zip_wd"]').val() + ","+ jQuery('input[id="jform_params_country_wd"]').val();
		var v = jQuery(this).val();
		e?(jQuery("#result_wd").html("Chargement..."),timeout&&clearTimeout(timeout),
		timeout=setTimeout(function(){
			jQuery.getJSON(apiWorld+e,function(r){	
			console.log(r);
				r.length != 1&&(jQuery("#result_wd").html("Vous avez "+r.length+" resultat(s). veuillez préciser votre demande."),jQuery("#jform_params_long_wd").val(""),jQuery("#jform_params_lat_wd").val("")),
				r.length ===1&&(jQuery("#result_wd").html("Le code place de "+v+" est "+r[0].place_id +" (lat : "+r[0].lat+" , long: "+r[0].lon+")"),
				jQuery("#jform_params_insee").val(r[0].place_id),jQuery("#jform_params_lat_wd").val(r[0].lat),jQuery("#jform_params_long_wd").val(r[0].lon),jQuery("#jform_params_zip_wd").val(r[0].address.postcode))})},callDelay)):jQuery("#result_wd").html("")
	})
	jQuery('input[id="jform_params_zip_wd"]').on("click mouseup keyup",function(){
		var e=jQuery('input[id="jform_params_ville_wd"]').val() + "," +jQuery(this).val()+ ","+ jQuery('input[id="jform_params_country_wd"]').val();
		var v=jQuery('input[id="jform_params_ville_wd"]').val();
		e?(jQuery("#result_wd").html("Chargement..."),timeout&&clearTimeout(timeout),
		timeout=setTimeout(function(){
			jQuery.getJSON(apiWorld+e,function(r){	
			console.log(r);
				r.length != 1&&(jQuery("#result_wd").html("Vous avez "+r.length+" resultat(s). veuillez préciser votre demande."),jQuery("#jform_params_long_wd").val(""),jQuery("#jform_params_lat_wd").val("")),
				r.length ===1&&(jQuery("#result_wd").html("Le code place de "+v+" est "+r[0].place_id +" (lat : "+r[0].lat+" , long: "+r[0].lon+")"),
				jQuery("#jform_params_insee").val(r[0].place_id),jQuery("#jform_params_lat_wd").val(r[0].lat),jQuery("#jform_params_long_wd").val(r[0].lon),jQuery("#jform_params_zip_wd").val(r[0].address.postcode))})},callDelay)):jQuery("#result_wd").html("")
	})

	jQuery('input[id="jform_params_country_wd"]').on("click mouseup keyup",function(){
		var e=jQuery('input[id="jform_params_ville_wd"]').val() + "," + jQuery('input[id="jform_params_zip_wd"]').val() + "," +jQuery(this).val();
		var v=jQuery('input[id="jform_params_ville_wd"]').val();
		e?(jQuery("#result_wd").html("Chargement..."),timeout&&clearTimeout(timeout),
		timeout=setTimeout(function(){
			jQuery.getJSON(apiWorld+e,function(r){	
			console.log(r);
				r.length != 1&&(jQuery("#result_wd").html("Vous avez "+r.length+" resultat(s). veuillez préciser votre demande."),jQuery("#jform_params_long_wd").val(""),jQuery("#jform_params_lat_wd").val("")),
				r.length ===1&&(jQuery("#result_wd").html("Le code place de "+v+" est "+r[0].place_id +" (lat : "+r[0].lat+" , long: "+r[0].lon+")"),
				jQuery("#jform_params_insee").val(r[0].place_id),jQuery("#jform_params_lat_wd").val(r[0].lat),jQuery("#jform_params_long_wd").val(r[0].lon),jQuery("#jform_params_zip_wd").val(r[0].address.postcode))})},callDelay)):jQuery("#result_wd").html("")
	})
	
});