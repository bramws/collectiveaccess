<?php
/** ---------------------------------------------------------------------
 * app/lib/ca/Utils/DataMigrationUtils.php :
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2010-2013 Whirl-i-Gig
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
 * @package CollectiveAccess
 * @subpackage Utils
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License version 3
 *
 * ----------------------------------------------------------------------
 */
 
 /**
  *
  */

 	require_once(__CA_MODELS_DIR__.'/ca_entities.php');
 	require_once(__CA_MODELS_DIR__.'/ca_entity_labels.php');
 	require_once(__CA_MODELS_DIR__.'/ca_places.php');
 	require_once(__CA_MODELS_DIR__.'/ca_collections.php');
 	require_once(__CA_MODELS_DIR__.'/ca_lists.php');
 	require_once(__CA_MODELS_DIR__.'/ca_storage_locations.php');
 	
	define("__CA_DATA_IMPORT_ERROR__", 0);
	define("__CA_DATA_IMPORT_WARNING__", 1);
	define("__CA_DATA_IMPORT_NOTICE__", 2);
 
	class DataMigrationUtils {
		# -------------------------------------------------------
		/**
		 * @var encoding of source data
		 */
		static $s_source_encoding = 'ISO-8859-1';
		
		/** 
		 * @var encoding of target data (should almost always be UTF-8)
		 */
		static $s_target_encoding = 'UTF-8';
		
		/**
		 * @var cache of created list item_ids
		 */
		static $s_cached_list_item_ids = array();
		
		# -------------------------------------------------------
		/**
		 * Sets the source text encoding to be used by DataMigrationUtils::transformTextEncoding()
		 */
		static function setSourceTextEncoding($ps_encoding) {
			DataMigrationUtils::$s_source_encoding = $ps_encoding;
		}
		# -------------------------------------------------------
		/** 
		 * Returns entity_id for the entity with the specified name, regardless of specified type. If the entity does not already 
		 * exist then it will be created with the specified name, type and locale, as well as with any specified values in the $pa_values array.
		 * $pa_values keys should be either valid entity fields or attributes.
		 *
		 * @param array $pa_entity_name Array with values for entity label
		 * @param int $pn_type_id The type_id of the entity type to use if the entity needs to be created
		 * @param int $pn_locale_id The locale_id to use if the entity needs to be created (will be used for both the entity locale as well as the label locale)
		 * @param array $pa_values An optional array of additional values to populate newly created entity records with. These values are *only* used for newly created entities; they will not be applied if the entity named already exists. The array keys should be names of ca_entities fields or valid entity attributes. Values should be either a scalar (for single-value attributes) or an array of values for (multi-valued attributes)
		 * @param array $pa_options An optional array of options, which include:
		 *				outputErrors - if true, errors will be printed to console [default=true]
		 *				dontCreate - if true then new entities will not be created [default=false]
		 *				matchOnDisplayName  if true then entities are looked up exclusively using displayname, otherwise forename and surname fields are used [default=false]
		 * 				transaction - if Transaction object is passed, use it for all Db-related tasks [default=null]
		 *				generateIdnoWithTemplate = A template to use when setting the idno. The template is a value with automatically-set SERIAL values replaced with % characters. Eg. 2012.% will set the created row's idno value to 2012.121 (assuming that 121 is the next number in the serial sequence.) The template is NOT used if idno is passed explicitly as a value in $pa_values.
		 */
		static function getEntityID($pa_entity_name, $pn_type_id, $pn_locale_id, $pa_values=null, $pa_options=null) {
			if (!is_array($pa_options)) { $pa_options = array(); }
			if(!isset($pa_options['outputErrors'])) { $pa_options['outputErrors'] = true; }
			
			$t_entity = new ca_entities();
			if (isset($pa_options['transaction']) && $pa_options['transaction'] instanceof Transaction){
				$t_entity->setTransaction($pa_options['transaction']);
			}
			
			$vb_exists = false;
			if (isset($pa_options['matchOnDisplayName']) && $pa_options['matchOnDisplayName']) {
				$t_entity_label = new ca_entity_labels();
				if ($t_entity_label->load(array('displayname' => $pa_entity_name['displayname']))) {
					$va_entity_ids = array($t_entity_label->get('entity_id'));
					$vb_exists = true;
				}
			} else {
				$vb_exists = (sizeof($va_entity_ids = $t_entity->getEntityIDsByName($pa_entity_name['forename'], $pa_entity_name['surname'])) > 0);
			}
			if (!$vb_exists) {
				if (isset($pa_options['dontCreate']) && $pa_options['dontCreate']) { return false; }
				
				$t_entity->setMode(ACCESS_WRITE);
				$t_entity->set('locale_id', $pn_locale_id);
				$t_entity->set('type_id', $pn_type_id);
				$t_entity->set('source_id', isset($pa_values['source_id']) ? $pa_values['source_id'] : null);
				$t_entity->set('access', isset($pa_values['access']) ? $pa_values['access'] : 0);
				$t_entity->set('status', isset($pa_values['status']) ? $pa_values['status'] : 0);
				
				if (!($vs_idno = isset($pa_values['idno']) ? $pa_values['idno'] : null)) {
					if(isset($pa_options['generateIdnoWithTemplate']) && $pa_options['generateIdnoWithTemplate']) {
						$vs_idno = $t_entity->setIdnoTWithTemplate($pa_options['generateIdnoWithTemplate'], array('dontSetValue' => true));
					}
				}
				
				$t_entity->set('idno', $vs_idno);
				$t_entity->set('lifespan', isset($pa_values['lifespan']) ? $pa_values['lifespan'] : null);
				$t_entity->set('parent_id', isset($pa_values['parent_id']) ? $pa_values['parent_id'] : null);
				unset($pa_values['access']);	
				unset($pa_values['status']);
				unset($pa_values['idno']);
				unset($pa_values['source_id']);
				unset($pa_values['lifespan']);
				
				$t_entity->insert();
				
				if ($t_entity->numErrors()) {
					if(isset($pa_options['outputErrors']) && $pa_options['outputErrors']) {
						print "ERROR INSERTING entity (".$pa_entity_name['forename']."/".$pa_entity_name['surname']."): ".join('; ', $t_entity->getErrors())."\n";
					}
					return null;
				}
				
				$t_entity->addLabel($pa_entity_name, $pn_locale_id, null, true);
				
				if (is_array($pa_values)) {
					foreach($pa_values as $vs_element => $va_value) { 					
						if (is_array($va_value)) {
							// array of values (complex multi-valued attribute)
							$t_entity->addAttribute(
								array_merge($va_value, array(
									'locale_id' => $pn_locale_id
								)), $vs_element);
						} else {
							// scalar value (simple single value attribute)
							if ($va_value) {
								$t_entity->addAttribute(array(
									'locale_id' => $pn_locale_id,
									$vs_element => $va_value
								), $vs_element);
							}
						}
					}
					$t_entity->update();
				
					if ($t_entity->numErrors()) {
						if(isset($pa_options['outputErrors']) && $pa_options['outputErrors']) {
							print "ERROR ADDING ATTRIBUTES TO entity (".$pa_entity_name['forename']."/".$pa_entity_name['surname']."): ".join('; ', $t_entity->getErrors())."\n";
						}
					}
				}
				
				$vn_entity_id = $t_entity->getPrimaryKey();
			} else {
				$vn_entity_id = array_shift($va_entity_ids);
			}
				
			return $vn_entity_id;
		}
		# -------------------------------------------------------
		/** 
		 * Returns place_id for the place with the specified name, regardless of specified type. If the place does not already 
		 * exist then it will be created with the specified name, type and locale, as well as with any specified values in the $pa_values array.
		 * $pa_values keys should be either valid place fields or attributes.
		 *
		 * @param string $ps_place_name Place label name
		 * @param int $pn_parent_id The parent_id of the place; must be set to a non-null value
		 * @param int $pn_type_id The type_id of the place type to use if the place needs to be created
		 * @param int $pn_locale_id The locale_id to use if the place needs to be created (will be used for both the place locale as well as the label locale)
		 * @param array $pa_values An optional array of additional values to populate newly created place records with. These values are *only* used for newly created places; they will not be applied if the place named already exists. The array keys should be names of ca_places fields or valid entity attributes. Values should be either a scalar (for single-value attributes) or an array of values for (multi-valued attributes)
		 * @param array $pa_options An optional array of options, which include:
		 *				outputErrors - if true, errors will be printed to console [default=true]
		 *				dontCreate - if true then new entities will not be created [default=false]
		 * 				transaction - if Transaction object is passed, use it for all Db-related tasks [default=null]
		 *				returnInstance = return ca_places instance rather than place_id. Default is false.
		 */
		static function getPlaceID($ps_place_name, $pn_parent_id, $pn_type_id, $pn_locale_id, $pa_values=null, $pa_options=null) {
			if (!is_array($pa_options)) { $pa_options = array(); }
			if(!isset($pa_options['outputErrors'])) { $pa_options['outputErrors'] = true; }
			
			$t_place = new ca_places();
			if (isset($pa_options['transaction']) && $pa_options['transaction'] instanceof Transaction){
				$t_place->setTransaction($pa_options['transaction']);
			}

			if (sizeof($va_place_ids = $t_place->getPlaceIDsByName($ps_place_name, $pn_parent_id)) == 0) {
				if (isset($pa_options['dontCreate']) && $pa_options['dontCreate']) { return false; }
				
				$t_place->setMode(ACCESS_WRITE);
				$t_place->set('locale_id', $pn_locale_id);
				$t_place->set('type_id', $pn_type_id);
				$t_place->set('parent_id', $pn_parent_id);
				$t_place->set('source_id', isset($pa_values['source_id']) ? $pa_values['source_id'] : null);
				$t_place->set('access', isset($pa_values['access']) ? $pa_values['access'] : 0);
				$t_place->set('status', isset($pa_values['status']) ? $pa_values['status'] : 0);
				$t_place->set('idno', isset($pa_values['idno']) ? $pa_values['idno'] : null);
				$t_place->set('lifespan', isset($pa_values['lifespan']) ? $pa_values['lifespan'] : null);
				$t_place->set('hierarchy_id', isset($pa_values['hierarchy_id']) ? $pa_values['hierarchy_id'] : null);
				unset($pa_values['access']);	
				unset($pa_values['status']);
				unset($pa_values['idno']);
				unset($pa_values['source_id']);
				unset($pa_values['lifespan']);
				unset($pa_values['hierarchy_id']);
				
				if (is_array($pa_values)) {
					foreach($pa_values as $vs_element => $va_value) { 					
						if (is_array($va_value)) {
							// array of values (complex multi-valued attribute)
							$t_place->addAttribute(
								array_merge($va_value, array(
									'locale_id' => $pn_locale_id
								)), $vs_element);
						} else {
							// scalar value (simple single value attribute)
							if ($va_value) {
								$t_place->addAttribute(array(
									'locale_id' => $pn_locale_id,
									$vs_element => $va_value
								), $vs_element);
							}
						}
					}
				}
				$t_place->insert();
				
				if ($t_place->numErrors()) {
					if(isset($pa_options['outputErrors']) && $pa_options['outputErrors']) {
						print "ERROR INSERTING place (".$ps_place_name."): ".join('; ', $t_place->getErrors())."\n";
						print_R($pa_values);
					}
					return null;
				}
				$t_place->addLabel(array('name' => $ps_place_name), $pn_locale_id, null, true);
				
				
				$vn_place_id = $t_place->getPrimaryKey();
				if (isset($pa_options['returnInstance']) && $pa_options['returnInstance']) {
					return $t_place;
				}
			} else {
				$vn_place_id = array_shift($va_place_ids);
				if (isset($pa_options['returnInstance']) && $pa_options['returnInstance']) {
					return new ca_places($vn_place_id);
				}
			}
				
			return $vn_place_id;
		}
		# -------------------------------------------------------
		/** 
		 * Returns occurrence_id for the occurrence with the specified name, regardless of specified type. If the occurrence does not already 
		 * exist then it will be created with the specified name, type and locale, as well as with any specified values in the $pa_values array.
		 * $pa_values keys should be either valid occurrence fields or attributes.
		 *
		 * @param string $ps_occurrence_name Occurrence label name
		 * @param int $pn_parent_id The parent_id of the occurrence; must be set to a non-null value
		 * @param int $pn_type_id The type_id of the occurrence type to use if the occurrence needs to be created
		 * @param int $pn_locale_id The locale_id to use if the occurrence needs to be created (will be used for both the occurrence locale as well as the label locale)
		 * @param array $pa_values An optional array of additional values to populate newly created occurrence records with. These values are *only* used for newly created occurrences; they will not be applied if the occurrence named already exists. The array keys should be names of ca_occurrences fields or valid entity attributes. Values should be either a scalar (for single-value attributes) or an array of values for (multi-valued attributes)
		 * @param array $pa_options An optional array of options, which include:
		 *				outputErrors - if true, errors will be printed to console [default=true]
		 *				dontCreate - if true then new entities will not be created [default=false]
		 * 				transaction - if Transaction object is passed, use it for all Db-related tasks [default=null]
		 *				generateIdnoWithTemplate = A template to use when setting the idno. The template is a value with automatically-set SERIAL values replaced with % characters. Eg. 2012.% will set the created row's idno value to 2012.121 (assuming that 121 is the next number in the serial sequence.) The template is NOT used if idno is passed explicitly as a value in $pa_values.
		 */
		static function getOccurrenceID($ps_occ_name, $pn_parent_id, $pn_type_id, $pn_locale_id, $pa_values=null, $pa_options=null) {
			if (!is_array($pa_options)) { $pa_options = array(); }
			if(!isset($pa_options['outputErrors'])) { $pa_options['outputErrors'] = true; }
			
			$t_occurrence = new ca_occurrences();
			if (isset($pa_options['transaction']) && $pa_options['transaction'] instanceof Transaction){
				$t_occurrence->setTransaction($pa_options['transaction']);
			}

			if (sizeof($va_occurrence_ids = $t_occurrence->getOccurrenceIDsByName($ps_occ_name, $pn_parent_id, $pn_type_id)) == 0) {
				if (isset($pa_options['dontCreate']) && $pa_options['dontCreate']) { return false; }
				
				$t_occurrence->setMode(ACCESS_WRITE);
				$t_occurrence->set('locale_id', $pn_locale_id);
				$t_occurrence->set('type_id', $pn_type_id);
				$t_occurrence->set('parent_id', $pn_parent_id);
				$t_occurrence->set('source_id', isset($pa_values['source_id']) ? $pa_values['source_id'] : null);
				$t_occurrence->set('access', isset($pa_values['access']) ? $pa_values['access'] : 0);
				$t_occurrence->set('status', isset($pa_values['status']) ? $pa_values['status'] : 0);
				
				if (!($vs_idno = isset($pa_values['idno']) ? $pa_values['idno'] : null)) {
					if(isset($pa_options['generateIdnoWithTemplate']) && $pa_options['generateIdnoWithTemplate']) {
						$vs_idno = $t_occurrence->setIdnoTWithTemplate($pa_options['generateIdnoWithTemplate'], array('dontSetValue' => true));
					}
				}
				
				$t_occurrence->set('idno', $vs_idno);
				$t_occurrence->set('hier_occurrence_id', isset($pa_values['hier_occurrence_id']) ? $pa_values['hier_occurrence_id'] : null);
				unset($pa_values['access']);	
				unset($pa_values['status']);
				unset($pa_values['idno']);
				unset($pa_values['source_id']);
				unset($pa_values['hier_occurrence_id']);
				
				if (is_array($pa_values)) {
					foreach($pa_values as $vs_element => $va_value) { 					
						if (is_array($va_value)) {
							// array of values (complex multi-valued attribute)
							$t_occurrence->addAttribute(
								array_merge($va_value, array(
									'locale_id' => $pn_locale_id
								)), $vs_element);
						} else {
							// scalar value (simple single value attribute)
							if ($va_value) {
								$t_occurrence->addAttribute(array(
									'locale_id' => $pn_locale_id,
									$vs_element => $va_value
								), $vs_element);
							}
						}
					}
				}
				$t_occurrence->insert();
				
				if ($t_occurrence->numErrors()) {
					if(isset($pa_options['outputErrors']) && $pa_options['outputErrors']) {
						print "ERROR INSERTING occurrence (".$ps_occ_name."): ".join('; ', $t_occurrence->getErrors())."\n";
						print_R($pa_values);
					}
					return null;
				}
				$t_occurrence->addLabel(array('name' => $ps_occ_name), $pn_locale_id, null, true);
				
				
				$vn_occurrence_id = $t_occurrence->getPrimaryKey();
			} else {
				$vn_occurrence_id = array_shift($va_occurrence_ids);
			}
				
			return $vn_occurrence_id;
		}
		# -------------------------------------------------------
		/** 
		 *
		 * @param array $pa_options An optional array of options, which include:
		 *				dontCreate - if true then new items will not be created [default=false]
		 *				matchOnLabel =  if true then list items are looked up exclusively using labels [default=false]
		 *				cache = cache item_ids of previously created/loaded items [default=true]
		 *
		 */
		static function getListItemID($pm_list_code_or_id, $ps_item_idno, $pn_type_id, $pn_locale_id, $pa_values=null, $pa_options=null) {
			if (!is_array($pa_options)) { $pa_options = array(); }
			if(!isset($pa_options['outputErrors'])) { $pa_options['outputErrors'] = true; }
			if(!isset($pa_options['cache'])) { $pa_options['cache'] = true; }
			
			if ($pa_options['cache'] && isset(DataMigrationUtils::$s_cached_list_item_ids[$pm_list_code_or_id.'/'.$ps_item_idno])) {
				return DataMigrationUtils::$s_cached_list_item_ids[$pm_list_code_or_id.'/'.$ps_item_idno];
			}
			
			if (!($vn_list_id = ca_lists::getListID($pm_list_code_or_id))) { 
				return DataMigrationUtils::$s_cached_list_item_ids[$pm_list_code_or_id.'/'.$ps_item_idno] = null; 
			}
			
			$t_list = new ca_lists();
			$t_item = new ca_list_items();
			if (isset($pa_options['transaction']) && $pa_options['transaction'] instanceof Transaction){
				$t_list->setTransaction($pa_options['transaction']);
				$t_item->setTransaction($pa_options['transaction']);
			}
			
			if (isset($pa_options['matchOnLabel']) && $pa_options['matchOnLabel']) {
				if ($vn_item_id = $t_list->getItemIDFromListByLabel($pm_list_code_or_id, $pa_values['name_singular'] ? $pa_values['name_singular'] : $ps_item_idno)) {
					return DataMigrationUtils::$s_cached_list_item_ids[$pm_list_code_or_id.'/'.$ps_item_idno] = $vn_item_id;
				}
			} else {
				if ($t_item->load(array('list_id' => $vn_list_id, 'idno' => $ps_item_idno))) {
					return DataMigrationUtils::$s_cached_list_item_ids[$pm_list_code_or_id.'/'.$ps_item_idno] = $t_item->getPrimaryKey();
				}
			}
				
			if (isset($pa_options['dontCreate']) && $pa_options['dontCreate']) { return false; }
			//
			// Need to create list item
			//
			if (!$t_list->load($vn_list_id)) {
				return null;
			}
			if ($t_item = $t_list->addItem($ps_item_idno, $pa_values['is_enabled'], $pa_values['is_default'], $pa_values['parent_id'], $pn_type_id, $ps_item_idno, '', (int)$pa_values['status'], (int)$pa_values['access'], $pa_values['rank'])) {
				$t_item->addLabel(
					array(
						'name_singular' => $pa_values['name_singular'] ? $pa_values['name_singular'] : $ps_item_idno,
						'name_plural' => $pa_values['name_plural'] ? $pa_values['name_plural'] : $ps_item_idno
					), $pn_locale_id, null, true
				);
				
				return DataMigrationUtils::$s_cached_list_item_ids[$pm_list_code_or_id.'/'.$ps_item_idno] = $t_item->getPrimaryKey();
			}
			return null;
		}
		# -------------------------------------------------------
		/** 
		 * Returns collection_id for the collection with the specified name, regardless of specified type. If the collection does not already 
		 * exist then it will be created with the specified name, type and locale, as well as with any specified values in the $pa_values array.
		 * $pa_values keys should be either valid collection fields or attributes.
		 *
		 * @param string $ps_collection_name Collection label name
		 * @param int $pn_type_id The type_id of the collection type to use if the collection needs to be created
		 * @param int $pn_locale_id The locale_id to use if the collection needs to be created (will be used for both the collection locale as well as the label locale)
		 * @param array $pa_values An optional array of additional values to populate newly created collection records with. These values are *only* used for newly created collections; they will not be applied if the collection named already exists. The array keys should be names of collection fields or valid collection attributes. Values should be either a scalar (for single-value attributes) or an array of values for (multi-valued attributes)
		 * @param array $pa_options An optional array of options, which include:
		 *				outputErrors - if true, errors will be printed to console [default=true]
		 *				dontCreate - if true then new collections will not be created [default=false]
		 * 				transaction - if Transaction object is passed, use it for all Db-related tasks [default=null]
		 *				generateIdnoWithTemplate = A template to use when setting the idno. The template is a value with automatically-set SERIAL values replaced with % characters. Eg. 2012.% will set the created row's idno value to 2012.121 (assuming that 121 is the next number in the serial sequence.) The template is NOT used if idno is passed explicitly as a value in $pa_values.
		 */
		static function getCollectionID($ps_collection_name, $pn_type_id, $pn_locale_id, $pa_values=null, $pa_options=null) {
			if (!is_array($pa_options)) { $pa_options = array(); }
			if(!isset($pa_options['outputErrors'])) { $pa_options['outputErrors'] = true; }
			
			$t_collection = new ca_collections();
			if (isset($pa_options['transaction']) && $pa_options['transaction'] instanceof Transaction){
				$t_collection->setTransaction($pa_options['transaction']);
			}

			if (sizeof($va_collection_ids = $t_collection->getCollectionIDsByName($ps_collection_name)) == 0) {
				if (isset($pa_options['dontCreate']) && $pa_options['dontCreate']) { return false; }
				
				$t_collection->setMode(ACCESS_WRITE);
				$t_collection->set('locale_id', $pn_locale_id);
				$t_collection->set('type_id', $pn_type_id);
				$t_collection->set('source_id', isset($pa_values['source_id']) ? $pa_values['source_id'] : null);
				$t_collection->set('access', isset($pa_values['access']) ? $pa_values['access'] : 0);
				$t_collection->set('status', isset($pa_values['status']) ? $pa_values['status'] : 0);
				
				if (!($vs_idno = isset($pa_values['idno']) ? $pa_values['idno'] : null)) {
					if(isset($pa_options['generateIdnoWithTemplate']) && $pa_options['generateIdnoWithTemplate']) {
						$vs_idno = $t_collection->setIdnoTWithTemplate($pa_options['generateIdnoWithTemplate'], array('dontSetValue' => true));
					}
				}
				
				$t_collection->set('idno', $vs_idno);
				$t_collection->set('parent_id', isset($pa_values['parent_id']) ? $pa_values['parent_id'] : null);
				unset($pa_values['access']);	
				unset($pa_values['status']);
				unset($pa_values['idno']);
				unset($pa_values['source_id']);
				
				if (is_array($pa_values)) {
					foreach($pa_values as $vs_element => $va_value) { 					
						if (is_array($va_value)) {
							// array of values (complex multi-valued attribute)
							$t_collection->addAttribute(
								array_merge($va_value, array(
									'locale_id' => $pn_locale_id
								)), $vs_element);
						} else {
							// scalar value (simple single value attribute)
							if ($va_value) {
								$t_collection->addAttribute(array(
									'locale_id' => $pn_locale_id,
									$vs_element => $va_value
								), $vs_element);
							}
						}
					}
				}
				$t_collection->insert();
				
				if ($t_collection->numErrors()) {
					if(isset($pa_options['outputErrors']) && $pa_options['outputErrors']) {
						print "ERROR INSERTING collection (".$ps_collection_name."): ".join('; ', $t_collection->getErrors())."\n";
						print_R($pa_values);
					}
					return null;
				}
				$t_collection->addLabel(array('name' => $ps_collection_name), $pn_locale_id, null, true);
				
				
				$vn_collection_id = $t_collection->getPrimaryKey();
			} else {
				$vn_collection_id = array_shift($va_collection_ids);
			}
				
			return $vn_collection_id;
		}
		# -------------------------------------------------------
		/** 
		 * Returns location_id for the storage location with the specified name, regardless of specified type. If the location does not already 
		 * exist then it will be created with the specified name, type and locale, as well as with any specified values in the $pa_values array.
		 * $pa_values keys should be either valid storage location fields or attributes.
		 *
		 * @param string $ps_location_name Storage location label name
		 * @param int $pn_parent_id The parent_id of the location; must be set to a non-null value
		 * @param int $pn_type_id The type_id of the location type to use if the location needs to be created
		 * @param int $pn_locale_id The locale_id to use if the location needs to be created (will be used for both the location locale as well as the label locale)
		 * @param array $pa_values An optional array of additional values to populate newly created location records with. These values are *only* used for newly created locations; they will not be applied if the location named already exists. The array keys should be names of ca_storage_locations fields or valid entity attributes. Values should be either a scalar (for single-value attributes) or an array of values for (multi-valued attributes)
		 * @param array $pa_options An optional array of options, which include:
		 *				outputErrors - if true, errors will be printed to console [default=true]
		 *				dontCreate - if true then new entities will not be created [default=false]
		 * 				transaction - if Transaction object is passed, use it for all Db-related tasks [default=null]
		 */
		static function getStorageLocationID($ps_location_name, $pn_parent_id, $pn_type_id, $pn_locale_id, $pa_values=null, $pa_options=null) {
			if (!is_array($pa_options)) { $pa_options = array(); }
			if(!isset($pa_options['outputErrors'])) { $pa_options['outputErrors'] = true; }
			
			$t_location = new ca_storage_locations();
			if (isset($pa_options['transaction']) && $pa_options['transaction'] instanceof Transaction){
				$t_location->setTransaction($pa_options['transaction']);
			}
			if (sizeof($va_location_ids = $t_location->getLocationIDsByName($ps_location_name, $pn_parent_id)) == 0) {
				if (isset($pa_options['dontCreate']) && $pa_options['dontCreate']) { return false; }
				
				$t_location->setMode(ACCESS_WRITE);
				$t_location->set('locale_id', $pn_locale_id);
				$t_location->set('type_id', $pn_type_id);
				$t_location->set('parent_id', $pn_parent_id);
				$t_location->set('access', isset($pa_values['access']) ? $pa_values['access'] : 0);
				$t_location->set('status', isset($pa_values['status']) ? $pa_values['status'] : 0);
				$t_location->set('idno', isset($pa_values['idno']) ? $pa_values['idno'] : null);
				unset($pa_values['access']);	
				unset($pa_values['status']);
				unset($pa_values['idno']);
				
				if (is_array($pa_values)) {
					foreach($pa_values as $vs_element => $va_value) { 					
						if (is_array($va_value)) {
							// array of values (complex multi-valued attribute)
							$t_location->addAttribute(
								array_merge($va_value, array(
									'locale_id' => $pn_locale_id
								)), $vs_element);
						} else {
							// scalar value (simple single value attribute)
							if ($va_value) {
								$t_location->addAttribute(array(
									'locale_id' => $pn_locale_id,
									$vs_element => $va_value
								), $vs_element);
							}
						}
					}
				}
				$t_location->insert();
				
				if ($t_location->numErrors()) {
					if(isset($pa_options['outputErrors']) && $pa_options['outputErrors']) {
						print "ERROR INSERTING storage location (".$ps_location_name."): ".join('; ', $t_location->getErrors())."\n";
						print_R($pa_values);
					}
					return null;
				}
				$t_location->addLabel(array('name' => $ps_location_name), $pn_locale_id, null, true);
				
				
				$vn_location_id = $t_location->getPrimaryKey();
			} else {
				$vn_location_id = array_shift($va_location_ids);
			}
				
			return $vn_location_id;
		}
		# -------------------------------------------------------
		/** 
		 * Returns object_id for the object_id with the specified name, regardless of specified type. If the object does not already 
		 * exist then it will be created with the specified name, type and locale, as well as with any specified values in the $pa_values array.
		 * $pa_values keys should be either valid object fields or attributes.
		 *
		 * @param string $ps_object_name Object label name
		 * @param int $pn_parent_id The parent_id of the object; must be set to a non-null value
		 * @param int $pn_type_id The type_id of the object type to use if the object needs to be created
		 * @param int $pn_locale_id The locale_id to use if the object needs to be created (will be used for both the object locale as well as the label locale)
		 * @param array $pa_values An optional array of additional values to populate newly created object records with. These values are *only* used for newly created objects; they will not be applied if the object named already exists. The array keys should be names of ca_objects fields or valid entity attributes. Values should be either a scalar (for single-value attributes) or an array of values for (multi-valued attributes)
		 * @param array $pa_options An optional array of options, which include:
		 *				outputErrors - if true, errors will be printed to console [default=true]
		 *				dontCreate - if true then new entities will not be created [default=false]
		 * 				transaction - if Transaction object is passed, use it for all Db-related tasks [default=null]
		 */
		static function getObjectID($ps_object_name, $pn_parent_id, $pn_type_id, $pn_locale_id, $pa_values=null, $pa_options=null) {
			if (!is_array($pa_options)) { $pa_options = array(); }
			if(!isset($pa_options['outputErrors'])) { $pa_options['outputErrors'] = true; }
			
			$t_object = new ca_objects();
			if (isset($pa_options['transaction']) && $pa_options['transaction'] instanceof Transaction){
				$t_object->setTransaction($pa_options['transaction']);
			}

			if (sizeof($va_object_ids = $t_object->getObjectIDsByName($ps_object_name, $pn_parent_id, $pn_type_id)) == 0) {
				if (isset($pa_options['dontCreate']) && $pa_options['dontCreate']) { return false; }
				
				$t_object->setMode(ACCESS_WRITE);
				$t_object->set('locale_id', $pn_locale_id);
				$t_object->set('type_id', $pn_type_id);
				$t_object->set('parent_id', $pn_parent_id);
				$t_object->set('source_id', isset($pa_values['source_id']) ? $pa_values['source_id'] : null);
				$t_object->set('access', isset($pa_values['access']) ? $pa_values['access'] : 0);
				$t_object->set('status', isset($pa_values['status']) ? $pa_values['status'] : 0);
				$t_object->set('idno', isset($pa_values['idno']) ? $pa_values['idno'] : null);
				$t_object->set('hier_object_id', isset($pa_values['hier_object_id']) ? $pa_values['hier_object_id'] : null);
				unset($pa_values['access']);	
				unset($pa_values['status']);
				unset($pa_values['idno']);
				unset($pa_values['source_id']);
				unset($pa_values['hier_object_id']);
				
				if (is_array($pa_values)) {
					foreach($pa_values as $vs_element => $va_value) { 					
						if (is_array($va_value)) {
							// array of values (complex multi-valued attribute)
							$t_object->addAttribute(
								array_merge($va_value, array(
									'locale_id' => $pn_locale_id
								)), $vs_element);
						} else {
							// scalar value (simple single value attribute)
							if ($va_value) {
								$t_object->addAttribute(array(
									'locale_id' => $pn_locale_id,
									$vs_element => $va_value
								), $vs_element);
							}
						}
					}
				}
				$t_object->insert();
				
				if ($t_object->numErrors()) {
					if(isset($pa_options['outputErrors']) && $pa_options['outputErrors']) {
						print "ERROR INSERTING object (".$ps_object_name."): ".join('; ', $t_object->getErrors())."\n";
						print_R($pa_values);
					}
					return null;
				}
				
				$t_object->addLabel(array('name' => $ps_object_name), $pn_locale_id, null, true);
				
				$vn_object_id = $t_object->getPrimaryKey();
			} else {
				$vn_object_id = array_shift($va_object_ids);
			}
				
			return $vn_object_id;
		}
		# -------------------------------------------------------
		/** 
		 * Returns loan_id for the loan with the specified name, regardless of specified type. If the loan does not already 
		 * exist then it will be created with the specified name, type and locale, as well as with any specified values in the $pa_values array.
		 * $pa_values keys should be either valid loan fields or attributes.
		 *
		 * @param string $ps_loan_name Loan label name
		 * @param int $pn_type_id The type_id of the loan type to use if the loan needs to be created
		 * @param int $pn_locale_id The locale_id to use if the loan needs to be created (will be used for both the loan locale as well as the label locale)
		 * @param array $pa_values An optional array of additional values to populate newly created loan records with. These values are *only* used for newly created loans; they will not be applied if the loan named already exists. The array keys should be names of loan fields or valid loan attributes. Values should be either a scalar (for single-value attributes) or an array of values for (multi-valued attributes)
		 * @param array $pa_options An optional array of options, which include:
		 *				outputErrors - if true, errors will be printed to console [default=true]
		 *				dontCreate - if true then new loans will not be created [default=false]
		 * 				transaction - if Transaction object is passed, use it for all Db-related tasks [default=null]
		 *				generateIdnoWithTemplate = A template to use when setting the idno. The template is a value with automatically-set SERIAL values replaced with % characters. Eg. 2012.% will set the created row's idno value to 2012.121 (assuming that 121 is the next number in the serial sequence.) The template is NOT used if idno is passed explicitly as a value in $pa_values.
		 */
		static function getLoanID($ps_loan_name, $pn_type_id, $pn_locale_id, $pa_values=null, $pa_options=null) {
			if (!is_array($pa_options)) { $pa_options = array(); }
			if(!isset($pa_options['outputErrors'])) { $pa_options['outputErrors'] = true; }
			
			$t_loan = new ca_loans();
			if (isset($pa_options['transaction']) && $pa_options['transaction'] instanceof Transaction){
				$t_loan->setTransaction($pa_options['transaction']);
			}

			if (sizeof($va_loan_ids = $t_loan->getLoanIDsByName($ps_loan_name)) == 0) {
				if (isset($pa_options['dontCreate']) && $pa_options['dontCreate']) { return false; }
				
				$t_loan->setMode(ACCESS_WRITE);
				$t_loan->set('locale_id', $pn_locale_id);
				$t_loan->set('type_id', $pn_type_id);
				$t_loan->set('access', isset($pa_values['access']) ? $pa_values['access'] : 0);
				$t_loan->set('status', isset($pa_values['status']) ? $pa_values['status'] : 0);
				
				if (!($vs_idno = isset($pa_values['idno']) ? $pa_values['idno'] : null)) {
					if(isset($pa_options['generateIdnoWithTemplate']) && $pa_options['generateIdnoWithTemplate']) {
						$vs_idno = $t_loan->setIdnoTWithTemplate($pa_options['generateIdnoWithTemplate'], array('dontSetValue' => true));
					}
				}
				
				$t_loan->set('idno', $vs_idno);
				$t_loan->set('parent_id', isset($pa_values['parent_id']) ? $pa_values['parent_id'] : null);
				unset($pa_values['access']);	
				unset($pa_values['status']);
				unset($pa_values['idno']);
				
				if (is_array($pa_values)) {
					foreach($pa_values as $vs_element => $va_value) { 					
						if (is_array($va_value)) {
							// array of values (complex multi-valued attribute)
							$t_loan->addAttribute(
								array_merge($va_value, array(
									'locale_id' => $pn_locale_id
								)), $vs_element);
						} else {
							// scalar value (simple single value attribute)
							if ($va_value) {
								$t_loan->addAttribute(array(
									'locale_id' => $pn_locale_id,
									$vs_element => $va_value
								), $vs_element);
							}
						}
					}
				}
				
				$t_loan->insert();
				
				if ($t_loan->numErrors()) {
					if(isset($pa_options['outputErrors']) && $pa_options['outputErrors']) {
						print "ERROR INSERTING loan (".$ps_loan_name."): ".join('; ', $t_loan->getErrors())."\n";
						print_R($pa_values);
					}
					return null;
				}
				
				$t_loan->addLabel(array('name' => $ps_loan_name), $pn_locale_id, null, true);
				
				$vn_loan_id = $t_loan->getPrimaryKey();
			} else {
				$vn_loan_id = array_shift($va_loan_ids);
			}
				
			return $vn_loan_id;
		}
		# -------------------------------------------------------
		/**
		 *
		 */
		static function transformTextEncoding($ps_text) {
			$ps_text = str_replace("‘", "'", $ps_text);
			$ps_text = str_replace("’", "'", $ps_text);
			$ps_text = str_replace("”", '"', $ps_text);
			$ps_text = str_replace("“", '"', $ps_text);
			$ps_text = str_replace("–", "-", $ps_text);
			$ps_text = str_replace("…", "...", $ps_text);
			return iconv(DataMigrationUtils::$s_source_encoding, DataMigrationUtils::$s_target_encoding, $ps_text);
		}
		# -------------------------------------------------------
		/**
		 * Takes a string and returns an array with the name parsed into pieces according to common heuristics
		 *
		 * @param string $ps_text The name text
		 * @param array $pa_options Optional array of options. Supported options are:
		 *		locale = locale code to use when applying rules; if omitted current user locale is employed
		 *
		 * @return array Array containing parsed name, keyed on ca_entity_labels fields (eg. forename, surname, middlename, etc.)
		 */
		static function splitEntityName($ps_text, $pa_options=null) {
			global $g_ui_locale;
			$ps_text = trim(preg_replace("![ ]+!", " ", $ps_text));
			
			if (isset($pa_options['locale']) && $pa_options['locale']) {
				$vs_locale = $pa_options['locale'];
			} else {
				$vs_locale = $g_ui_locale;
			}
		
			if (file_exists($vs_lang_filepath = __CA_LIB_DIR__.'/ca/Utils/DataMigrationUtils/'.$vs_locale.'.lang')) {
				$o_config = Configuration::load($vs_lang_filepath);
				$va_titles = $o_config->getList('titles');
				$va_corp_suffixes = $o_config->getList('corporation_suffixes');
			} else {
				$o_config = null;
				$va_titles = array();
				$va_corp_suffixes = array();
			}
			
			$va_name = array();
			if (strpos($ps_text, ',') !== false) {
				// is comma delimited
				$va_tmp = explode(',', $ps_text);
				$va_name['surname'] = $va_tmp[0];
				
				if(sizeof($va_tmp) > 1) {
					$va_name['forename'] = $va_tmp[1];
				}
			} else {
				// check for titles
				$ps_text = preg_replace('![^A-Za-z0-9 \-]+!', '', $ps_text);
				foreach($va_titles as $vs_title) {
					if (preg_match("!^({$vs_title})!", $ps_text, $va_matches)) {
						$va_name['prefix'] = $va_matches[1];
						$ps_text = str_replace($va_matches[1], '', $ps_text);
					}
				}
				
				// check for suffixes
				foreach($va_corp_suffixes as $vs_suffix) {
					if (preg_match("!({$vs_suffix})$!", $ps_text, $va_matches)) {
						$va_name['suffix'] = $va_matches[1];
						$ps_text = str_replace($va_matches[1], '', $ps_text);
					}
				}
				
				$va_tmp = preg_split('![ ]+!', trim($ps_text));
				
				$va_name = array(
					'surname' => '', 'forename' => '', 'middlename' => '', 'displayname' => ''
				);
				switch(sizeof($va_tmp)) {
					case 1:
						$va_name['surname'] = $ps_text;
						break;
					case 2:
						$va_name['forename'] = $va_tmp[0];
						$va_name['surname'] = $va_tmp[1];
						break;
					case 3:
						$va_name['forename'] = $va_tmp[0];
						$va_name['middlename'] = $va_tmp[1];
						$va_name['surname'] = $va_tmp[2];
						break;
					case 4:
					default:
						if (strpos($ps_text, ' '._t('and').' ') !== false) {
							$va_name['surname'] = array_pop($va_tmp);
							$va_name['forename'] = join(' ', $va_tmp);
						} else {
							$va_name['forename'] = array_shift($va_tmp);
							$va_name['middlename'] = array_shift($va_tmp);
							$va_name['surname'] = join(' ', $va_tmp);
						}
						break;
				}
			}
			
			$va_name['displayname'] = $ps_text;
			foreach($va_name as $vs_k => $vs_v) {
				$va_name[$vs_k] = trim($vs_v);
			}
			
			return $va_name;
		}
		# -------------------------------------------------------
		/**
		 * 
		 */
		static function postError($po_object, $ps_message, $pn_level=__CA_DATA_IMPORT_ERROR__, $pa_options=null) {
			if (!$po_object->numErrors()) { return null; }
			$vs_error = '';
			switch($pn_level) {
				case __CA_DATA_IMPORT_NOTICE__:
					$vs_error .= "[Notice]";
					break;
				case __CA_DATA_IMPORT_WARNING__:
					$vs_error .= "[Warning]";
					break;
				default:
				case __CA_DATA_IMPORT_ERROR__:
					$vs_error .= "[Error]";
					break;
			}
			
			$vs_error .= " {$ps_message} ".join("; ", $po_object->getErrors());
			
			if (!isset($pa_options['dontPrint']) || !$pa_options['dontPrint']) {
				print "{$vs_error}\n";
			}
			
			return $vs_error;
		}
		# -------------------------------------------------------
	}
?>