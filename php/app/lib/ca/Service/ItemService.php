<?php
/** ---------------------------------------------------------------------
 * app/lib/ca/Service/ItemService.php
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2012 Whirl-i-Gig
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
 * @subpackage WebServices
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License version 3
 *
 * ----------------------------------------------------------------------
 */

 /**
  *
  */

require_once(__CA_LIB_DIR__."/ca/Service/BaseJSONService.php");  
require_once(__CA_MODELS_DIR__."/ca_lists.php");


class ItemService extends BaseJSONService {	
	# -------------------------------------------------------
	public function __construct($po_request,$ps_table=""){
		parent::__construct($po_request,$ps_table);
	}
	# -------------------------------------------------------
	public function dispatch(){
		switch($this->getRequestMethod()){
			case "GET":
				if($this->opn_id>0){
					if(sizeof($this->getRequestBodyArray())==0){
						return $this->getAllItemInfo();
					} else {
						return $this->getSpecificItemInfo();
					}
				} else {
					// do something here? (get all records!?)
					return array();
				}
				break;
			case "PUT":
				if(sizeof($this->getRequestBodyArray())==0){
					$this->addError(_t("Missing request body for PUT"));
					return false;
				}
				if($this->opn_id>0){
					return $this->editItem();
				} else {
					return $this->addItem();
				}
				break;
			case "DELETE":
				if($this->opn_id>0){
					return $this->deleteItem();
				} else {
					$this->addError(_t("No identifier specified"));
					return false;
				}
				break;
			default:
				$this->addError(_t("Invalid HTTP request method"));
				return false;
		}
	}
	# -------------------------------------------------------
	protected function getSpecificItemInfo(){
		if(!($t_instance = $this->_getTableInstance($this->ops_table,$this->opn_id))){
			return false;
		}

		$va_post = $this->getRequestBodyArray();

		$va_return = array();
		if(!is_array($va_post["bundles"])){
			return false;
		}
		foreach($va_post["bundles"] as $vs_bundle => $va_options){
			if($this->_isBadBundle($vs_bundle)){
				continue;
			}

			if(!is_array($va_options)){
				$va_options = array();
			}

			$va_return[$vs_bundle] = $t_instance->get($vs_bundle,$va_options);
		}

		return $va_return;
	}
	# -------------------------------------------------------
	/**
	 * Try to return everything useful for the specified record
	 */
	protected function getAllItemInfo(){
		if(!($t_instance = $this->_getTableInstance($this->ops_table,$this->opn_id))){
			return false;
		}
		$t_list = new ca_lists();
		$t_locales = new ca_locales();

		$va_locales = $t_locales->getLocaleList(array("available_for_cataloguing_only" => true));
		
		$va_return = array();

		// labels

		$va_labels = $t_instance->get($this->ops_table.".preferred_labels",array("returnAllLocales" => true));
		$va_labels = end($va_labels);
		if(is_array($va_labels)){
			foreach($va_labels as $vn_locale_id => $va_labels_by_locale){
				foreach($va_labels_by_locale as $va_tmp){
					$va_return["preferred_labels"][$va_locales[$vn_locale_id]["code"]][] = $va_tmp[$t_instance->getLabelDisplayField()];	
				}
			}
		}

		$va_labels = $t_instance->get($this->ops_table.".nonpreferred_labels",array("returnAllLocales" => true));
		$va_labels = end($va_labels);
		if(is_array($va_labels)){
			foreach($va_labels as $vn_locale_id => $va_labels_by_locale){
				foreach($va_labels_by_locale as $va_tmp){
					$va_return["nonpreferred_labels"][$va_locales[$vn_locale_id]["code"]][] = $va_tmp[$t_instance->getLabelDisplayField()];
				}
			}
		}

		// "intrinsic" fields
		foreach($t_instance->getFieldsArray() as $vs_field_name => $va_field_info){
			$vs_list = null;
			if(!is_null($vs_val = $t_instance->get($vs_field_name))){
				$va_return[$vs_field_name] = array(
					"value" => $vs_val,
				);
				if(isset($va_field_info["LIST"])){ // fields like "access" and "status"
					$va_tmp = end($t_list->getItemFromListByItemValue($va_field_info["LIST"],$vs_val));
					foreach($va_locales as $vn_locale_id => $va_locale){
						$va_return[$vs_field_name]["display_text"][$va_locale["code"]] = 
							$va_tmp[$vn_locale_id]["name_singular"];
					}
				}
				if(isset($va_field_info["LIST_CODE"])){ // typical example: type_id
					$va_item = $t_list->getItemFromListByItemID($va_field_info["LIST_CODE"],$vs_val);
					$t_item = new ca_list_items($va_item["item_id"]);
					$va_labels = $t_item->getLabels(null,__CA_LABEL_TYPE_PREFERRED__);
					foreach($va_locales as $vn_locale_id => $va_locale){
						if($vs_label = $va_labels[$va_item["item_id"]][$vn_locale_id][0]["name_singular"]){
							$va_return[$vs_field_name]["display_text"][$va_locale["code"]] = $vs_label;
						}
					}
				}
			}
		}

		// representations for objects
		if($this->ops_table == "ca_objects"){
			$va_return['representations'] = $t_instance->getRepresentations();
		}

		// attributes
		$va_codes = $t_instance->getApplicableElementCodes();
		foreach($va_codes as $vs_code){
			if($va_vals = $t_instance->get($this->ops_table.".".$vs_code,
				array("convertCodesToDisplayText" => true,"returnAllLocales" => true)))
			{
				$va_vals_by_locale = end($va_vals); // i seriously have no idea what that additional level of nesting in the return format is for
				$va_attribute_values = array();
				foreach($va_vals_by_locale as $vn_locale_id => $va_locale_vals) {
					foreach($va_locale_vals as $vs_val_id => $va_actual_data){
						$vs_locale_code = isset($va_locales[$vn_locale_id]["code"]) ? $va_locales[$vn_locale_id]["code"] : "none";
						$va_attribute_values[$vs_val_id][$vs_locale_code] = $va_actual_data;
					}

					$va_return[$this->ops_table.".".$vs_code] = array_values($va_attribute_values);
				}
			}
		}

		// relationships
		// yes, not all combinations between these tables have 
		// relationships but it also doesn't hurt to query
		foreach($this->opa_valid_tables as $vs_rel_table){

			//
			// set-related hacks
			if($this->ops_table == "ca_sets" && $vs_rel_table=="ca_tours"){ // throws SQL error in getRelatedItems
				continue;
			}
			// you'd expect the set items to be included for sets but
			// we don't wan't to list set items as allowed related table
			// which is why we add them by hand here
			if($this->ops_table == "ca_sets"){
				$va_tmp = $t_instance->getItems();
				$va_set_items = array();
				foreach($va_tmp as $va_loc){
					foreach($va_loc as $va_item){
						$va_set_items[] = $va_item;
					}
				}
				$va_return["related"]["ca_set_items"] = $va_set_items;
			}
			// end set-related hacks
			//

			$va_related_items = $t_instance->get($vs_rel_table,array("returnAsArray" => true));
			if(is_array($va_related_items) && sizeof($va_related_items)>0){
				$va_return["related"][$vs_rel_table] = array_values($va_related_items);
			}
		}

		return $va_return;
	}
	# -------------------------------------------------------
	private function addItem(){
		if(!($t_instance = $this->_getTableInstance($this->ops_table))){
			return false;
		}

		$t_locales = new ca_locales();
		$va_post = $this->getRequestBodyArray();

		// intrinsic fields
		if(is_array($va_post["intrinsic_fields"]) && sizeof($va_post["intrinsic_fields"])){
			foreach($va_post["intrinsic_fields"] as $vs_field_name => $vs_value){
				$t_instance->set($vs_field_name,$vs_value);
			}
		} else {
			$this->addError(_t("No intrinsic fields specified"));
			return false;
		}

		// attributes
		if(is_array($va_post["attributes"]) && sizeof($va_post["attributes"])){
			foreach($va_post["attributes"] as $vs_attribute_name => $va_values){
				foreach($va_values as $va_value){
					if($va_value["locale"]){
						$va_value["locale_id"] = $t_locales->localeCodeToID($va_value["locale"]);
						unset($va_value["locale"]);
					}
					$t_instance->addAttribute($va_value,$vs_attribute_name);
				}
			}
		}

		$t_instance->setMode(ACCESS_WRITE);
		$t_instance->insert();

		// AFTER INSERT STUFF

		// preferred labels
		if(is_array($va_post["preferred_labels"]) && sizeof($va_post["preferred_labels"])){
			foreach($va_post["preferred_labels"] as $va_label){
				if($va_label["locale"]){
					$vn_locale_id = $t_locales->localeCodeToID($va_label["locale"]);
					unset($va_label["locale"]);
				}
				$t_instance->addLabel($va_label,$vn_locale_id,null,true);
			}
		}

		// nonpreferred labels
		if(is_array($va_post["nonpreferred_labels"]) && sizeof($va_post["nonpreferred_labels"])){
			foreach($va_post["nonpreferred_labels"] as $va_label){
				if($va_label["locale"]){
					$vn_locale_id = $t_locales->localeCodeToID($va_label["locale"]);
					unset($va_label["locale"]);
				}
				if($va_label["type_id"]){
					$vn_type_id = $va_label["type_id"];
					unset($va_label["type_id"]);
				} else {
					$vn_type_id = null;
				}
				$t_instance->addLabel($va_label,$vn_locale_id,$vn_type_id,false);
			}
		}

		// relationships
		if(is_array($va_post["related"]) && sizeof($va_post["related"])>0){
			foreach($va_post["related"] as $vs_table => $va_relationships){
				foreach($va_relationships as $va_relationship){
					$vs_source_info = isset($va_relationship["source_info"]) ? $va_relationship["source_info"] : null;
					$vs_effective_date = isset($va_relationship["effective_date"]) ? $va_relationship["effective_date"] : null;
					$vs_direction = isset($va_relationship["direction"]) ? $va_relationship["direction"] : null;

					$t_rel_instance = $this->_getTableInstance($vs_table);

					$vs_pk = isset($va_relationship[$t_rel_instance->primaryKey()]) ? $va_relationship[$t_rel_instance->primaryKey()] : null;
					$vs_type_id = isset($va_relationship["type_id"]) ? $va_relationship["type_id"] : null;

					$t_instance->addRelationship($vs_table,$vs_pk,$vs_type_id,$vs_effective_date,$vs_source_info,$vs_direction);

					// @TODO add relationship attributes as soon as they're implemented
				}
			}
		}

		if($t_instance->numErrors()>0){
			foreach($t_instance->getErrors() as $vs_error){
				$this->addError($vs_error);
			}
			// don't leave orphaned record in case something
			// went wrong with labels or relationships
			if($t_instance->getPrimaryKey()){
				$t_instance->delete();
			}
			return false;
		} else {
			return array($t_instance->primaryKey() => $t_instance->getPrimaryKey());
		}
	}
	# -------------------------------------------------------
	private function editItem(){
		if(!($t_instance = $this->_getTableInstance($this->ops_table,$this->opn_id))){
			return false;
		}

		$t_locales = new ca_locales();
		$va_post = $this->getRequestBodyArray();

		// intrinsic fields
		if(is_array($va_post["intrinsic_fields"]) && sizeof($va_post["intrinsic_fields"])){
			foreach($va_post["intrinsic_fields"] as $vs_field_name => $vs_value){
				$t_instance->set($vs_field_name,$vs_value);
			}
		}

		// attributes
		if(is_array($va_post["remove_attributes"])){
			foreach($va_post["remove_attributes"] as $vs_code_to_delete){
				$t_instance->removeAttributes($vs_code_to_delete);
			}
		} else if ($va_post["remove_all_attributes"]){
			$t_instance->removeAttributes();
		}

		if(is_array($va_post["attributes"]) && sizeof($va_post["attributes"])){
			foreach($va_post["attributes"] as $vs_attribute_name => $va_values){
				foreach($va_values as $va_value){
					if($va_value["locale"]){
						$va_value["locale_id"] = $t_locales->localeCodeToID($va_value["locale"]);
						unset($va_value["locale"]);
					}
					$t_instance->addAttribute($va_value,$vs_attribute_name);
				}
			}
		}

		$t_instance->setMode(ACCESS_WRITE);
		$t_instance->update();

		// AFTER UPDATE STUFF

		// yank all labels?
		if ($va_post["remove_all_labels"]){
			$t_instance->removeAllLabels();
		}

		// preferred labels
		if(is_array($va_post["preferred_labels"]) && sizeof($va_post["preferred_labels"])){
			foreach($va_post["preferred_labels"] as $va_label){
				if($va_label["locale"]){
					$vn_locale_id = $t_locales->localeCodeToID($va_label["locale"]);
					unset($va_label["locale"]);
				}
				$t_instance->addLabel($va_label,$vn_locale_id,null,true);
			}
		}

		// nonpreferred labels
		if(is_array($va_post["nonpreferred_labels"]) && sizeof($va_post["nonpreferred_labels"])){
			foreach($va_post["nonpreferred_labels"] as $va_label){
				if($va_label["locale"]){
					$vn_locale_id = $t_locales->localeCodeToID($va_label["locale"]);
					unset($va_label["locale"]);
				}
				if($va_label["type_id"]){
					$vn_type_id = $va_label["type_id"];
					unset($va_label["type_id"]);
				} else {
					$vn_type_id = null;
				}
				$t_instance->addLabel($va_label,$vn_locale_id,$vn_type_id,false);
			}
		}

		// relationships
		if (is_array($va_post["remove_relationships"])){
			foreach($va_post["remove_relationships"] as $vs_table){
				$t_instance->removeRelationships($vs_table);
			}
		}

		if($va_post["remove_all_relationships"]){
			foreach($this->opa_valid_tables as $vs_table){
				$t_instance->removeRelationships($vs_table);	
			}
		}

		if(is_array($va_post["related"]) && sizeof($va_post["related"])>0){
			foreach($va_post["related"] as $vs_table => $va_relationships){
				foreach($va_relationships as $va_relationship){
					$vs_source_info = isset($va_relationship["source_info"]) ? $va_relationship["source_info"] : null;
					$vs_effective_date = isset($va_relationship["effective_date"]) ? $va_relationship["effective_date"] : null;
					$vs_direction = isset($va_relationship["direction"]) ? $va_relationship["direction"] : null;

					$t_rel_instance = $this->_getTableInstance($vs_table);

					$vs_pk = isset($va_relationship[$t_rel_instance->primaryKey()]) ? $va_relationship[$t_rel_instance->primaryKey()] : null;
					$vs_type_id = isset($va_relationship["type_id"]) ? $va_relationship["type_id"] : null;

					$t_instance->addRelationship($vs_table,$vs_pk,$vs_type_id,$vs_effective_date,$vs_source_info,$vs_direction);
				}
			}
		}

		if($t_instance->numErrors()>0){
			foreach($t_instance->getErrors() as $vs_error){
				$this->addError($vs_error);
			}
			return false;
		} else {
			return array($t_instance->primaryKey() => $t_instance->getPrimaryKey());
		}

	}
	# -------------------------------------------------------
	private function deleteItem(){
		if(!($t_instance = $this->_getTableInstance($this->ops_table,$this->opn_id))){
			return false;
		}

		$va_post = $this->getRequestBodyArray();

		$vb_delete_related = isset($va_post["delete_related"]) ? $va_post["delete_related"] : false;
		$vb_hard_delete = isset($va_post["hard"]) ? $va_post["hard"] : false;

		$t_instance->setMode(ACCESS_WRITE);
		$t_instance->delete($vb_delete_related,array("hard" => $vb_hard_delete));


		if($t_instance->numErrors()>0){
			foreach($t_instance->getErrors() as $vs_error){
				$this->addError($vs_error);
			}
			return false;
		} else {
			return array("deleted" => $this->opn_id);
		}
	}
	# -------------------------------------------------------	
}

?>