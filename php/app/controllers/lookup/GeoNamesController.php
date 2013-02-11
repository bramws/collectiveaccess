<?php
/* ----------------------------------------------------------------------
 * app/controllers/lookup/GeoNamesController.php :
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2009-2012 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * This source code is free and modifiable under the terms of
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 *
 * ----------------------------------------------------------------------
 */
require_once(__CA_APP_DIR__."/helpers/displayHelpers.php");
require_once(__CA_MODELS_DIR__."/ca_locales.php");


class GeoNamesController extends ActionController {
 	# -------------------------------------------------------
 	public function __construct(&$po_request, &$po_response, $pa_view_paths=null) {
 		parent::__construct($po_request, $po_response, $pa_view_paths);
 	}
 	# -------------------------------------------------------
 	# AJAX handlers
 	# -------------------------------------------------------
	public function Get($pa_additional_query_params=null, $pa_options=null) {
		global $g_ui_locale_id;

		$ps_query = $this->request->getParameter('term', pString);
		$ps_type = $this->request->getParameter('type', pString);
		$vo_conf = Configuration::load();
		$vs_user = trim($vo_conf->get("geonames_user"));
		
		$va_items = array();
		if (unicode_strlen($ps_query) >= 3) {
			$vs_base = "http://api.geonames.org/search";
			$t_locale = new ca_locales($g_ui_locale_id);
			$vs_lang = $t_locale->get("language");
			$va_params = array(
				"q" => $ps_query,
				"lang" => $vs_lang,
				'style' => 'full',
				'username' => $vs_user
			);

			foreach ($va_params as $vs_key => $vs_value) {
				$vs_query_string .= "$vs_key=" . urlencode($vs_value) . "&";
			}

			try {
				$vo_xml = new SimpleXMLElement($x= @file_get_contents("{$vs_base}?$vs_query_string"));
				
				$va_attr = $vo_xml->status ? $vo_xml->status->attributes() : null;
				if ($va_attr && isset($va_attr['value']) && ((int)$va_attr['value'] > 0)) { 
					$va_items[0] = array(
						'displayname' => _t('Connection to GeoNames with username "%1" was rejected with the message "%2". Check your configuration and make sure your GeoNames.org account is enabled for web services.', $vs_user, $va_attr['message']),
						'country' => '',
						'continent' => '',
						'fcl' => '',
						'lat' => '',
						'lng' => '',
						'idno' => ''
					);
					$va_items[0]['label'] = $va_items[0]['displayname'];
				} else {
					foreach($vo_xml->children() as $vo_child){
						if($vo_child->getName()!="totalResultsCount"){
							$va_items[(string)$vo_child->geonameId] = array(
								'displayname' => $vo_child->name,
								'label' => $vo_child->name.
											($vo_child->countryName ? ', '.$vo_child->countryName : '').
											($vo_child->continentCode ? ', '.$vo_child->continentCode : '').
											($vo_child->lat ? " [".$vo_child->lat."," : '').
											($vo_child->lng ? $vo_child->lng."]" : '').
											($vo_child->fclName ? " (".$vo_child->fclName.")" : ''),
								'country' => $vo_child->countryName ? $vo_child->countryName : null,
								'continent' => $vo_child->continentCode ? $vo_child->continentCode : null,
								'fcl' => $vo_child->fclName ? $vo_child->fclName : null,
								'lat' => $vo_child->lat ? $vo_child->lat : null,
								'lng' => $vo_child->lng ? $vo_child->lng : null,
								'idno' => $vo_child->geonameId,
								'id' => (string)$vo_child->geonameId
							);
						}
					}
				}
			} catch (Exception $e) {
				$va_items[0] = array(
					'displayname' => _t('Could not connect to GeoNames'),
					'country' => '',
					'continent' => '',
					'fcl' => '',
					'lat' => '',
					'lng' => '',
					'idno' => '',
					'id' => 0
				);
				$va_items[0]['label'] = $va_items[0]['displayname'];
			}
		}

		$this->view->setVar('geonames_list', $va_items);
		return $this->render('ajax_geonames_list_html.php');
	}
	# -------------------------------------------------------
}
