<?php
/** ---------------------------------------------------------------------
 * app/lib/core/BaseRelationshipModel.php :
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2009-2013 Whirl-i-Gig
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
 * @subpackage BaseModel
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License version 3
 *
 * ----------------------------------------------------------------------
 */
 
 /**
  *
  */
 
 require_once(__CA_LIB_DIR__.'/core/BaseModel.php');
 require_once(__CA_LIB_DIR__.'/core/IRelationshipModel.php');
 require_once(__CA_APP_DIR__.'/helpers/htmlFormHelpers.php');
 require_once(__CA_MODELS_DIR__.'/ca_relationship_types.php');
 require_once(__CA_MODELS_DIR__.'/ca_acl.php');
 
	class BaseRelationshipModel extends BaseModel implements IRelationshipModel {
		# ------------------------------------------------------
		/**
		 * 
		 */
		public function insert($pa_options=null) {
			if ($vn_rc = parent::insert($pa_options)) {
				$t_left = $this->getAppDatamodel()->getInstanceByTableNum($this->getLeftTableNum());
				$t_right = $this->getAppDatamodel()->getInstanceByTableNum($this->getRightTableNum());
				
				foreach(array($this->getRightTableName() => $t_left, $this->getLeftTableName() => $t_right) as $vs_other_table_name => $t_instance) {
					if ((bool)$t_instance->getProperty('SUPPORTS_ACL_INHERITANCE')) {
						if (is_array($va_inheritors = $t_instance->getProperty('ACL_INHERITANCE_LIST')) && in_array($vs_other_table_name, $va_inheritors)) {
							ca_acl::applyACLInheritanceToRelatedRowFromRow($t_instance, ($vs_other_table_name == $this->getLeftTableName()) ? $this->get($this->getRightTableFieldName()) : $this->get($this->getLeftTableFieldName()), $vs_other_table_name, ($vs_other_table_name == $this->getLeftTableName()) ? $this->get($this->getLeftTableFieldName()) : $this->get($this->getRightTableFieldName()));
						}
					}
				}
			}
			return $vn_rc;
		}
		# ------------------------------------------------------
		/**
		 * 
		 */
		public function update($pa_options=null) {
			if ($vn_rc = parent::update($pa_options)) {
				$t_left = $this->getAppDatamodel()->getInstanceByTableNum($this->getLeftTableNum());
				$t_right = $this->getAppDatamodel()->getInstanceByTableNum($this->getRightTableNum());
				
				foreach(array($this->getRightTableName() => $t_left, $this->getLeftTableName() => $t_right) as $vs_other_table_name => $t_instance) {
					if ((bool)$t_instance->getProperty('SUPPORTS_ACL_INHERITANCE')) {
						if (is_array($va_inheritors = $t_instance->getProperty('ACL_INHERITANCE_LIST')) && in_array($vs_other_table_name, $va_inheritors)) {
							ca_acl::applyACLInheritanceToRelatedRowFromRow($t_instance, ($vs_other_table_name == $this->getLeftTableName()) ? $this->get($this->getRightTableFieldName()) : $this->get($this->getLeftTableFieldName()), $vs_other_table_name, ($vs_other_table_name == $this->getLeftTableName()) ? $this->get($this->getLeftTableFieldName()) : $this->get($this->getRightTableFieldName()));
						}
					}
				}
			}
			return $vn_rc;
		}
		# ------------------------------------------------------
		/**
		 * 
		 */
		public function delete($pb_delete_related=false, $pa_options=null, $pa_fields=null, $pa_table_list=null) {
			$t_left = $this->getAppDatamodel()->getInstanceByTableNum($this->getLeftTableNum());
			$vn_left_id = $this->get($this->getLeftTableFieldName());
			$vn_right_id = $this->get($this->getRightTableFieldName());
			
			$t_right = $this->getAppDatamodel()->getInstanceByTableNum($this->getRightTableNum());
			if ($vn_rc = parent::delete($pb_delete_related, $pa_options, $pa_fields, $pa_table_list)) {
				foreach(array($this->getRightTableName() => $t_left, $this->getLeftTableName() => $t_right) as $vs_other_table_name => $t_instance) {
					if ((bool)$t_instance->getProperty('SUPPORTS_ACL_INHERITANCE')) {
						if (is_array($va_inheritors = $t_instance->getProperty('ACL_INHERITANCE_LIST')) && in_array($vs_other_table_name, $va_inheritors)) {
							ca_acl::applyACLInheritanceToRelatedRowFromRow($t_instance, ($vs_other_table_name == $this->getLeftTableName()) ? $vn_right_id : $vn_left_id, $vs_other_table_name, ($vs_other_table_name == $this->getLeftTableName()) ? $vn_left_id : $vn_right_id, array('deleteACLOnly' => true));
						}
					}
				}
			}
			return $vn_rc;
		}
		# ------------------------------------------------------
		/**
		 * Returns name of the "left" table (by convention the table mentioned first in the relationship table name)
		 * (eg. if the table name is ca_objects_x_entities then the "left" name is ca_objects)
		 */
		public function getLeftTableName() {
			return $this->RELATIONSHIP_LEFT_TABLENAME;
		}
 		# ------------------------------------------------------
 		/**
		 * Returns name of the "right" table (by convention the table mentioned second in the relationship table name)
		 * (eg. if the table name is ca_objects_x_entities then the "right" name is ca_entities)
		 */
		public function getRightTableName() {
			return $this->RELATIONSHIP_RIGHT_TABLENAME;
		}
		# ------------------------------------------------------
		/**
		 * Returns table number of the "left" table (by convention the table mentioned first in the relationship table name)
		 * (eg. if the table name is ca_objects_x_entities then the "left" number corresponds to ca_objects)
		 */
		public function getLeftTableNum() {
			return $this->getAppDatamodel()->getTableNum($this->getLeftTableName());
		}
		# ------------------------------------------------------
		/**
		 * Returns table number of the "right" table (by convention the table mentioned second in the relationship table name)
		 * (eg. if the table name is ca_objects_x_entities then the "right" number corresponds to ca_entities)
		 */
		public function getRightTableNum() {
			return $this->getAppDatamodel()->getTableNum($this->getRightTableName());
		}
		# ------------------------------------------------------
		/**
		 * Returns name of the "left" table (by convention the table mentioned first in the relationship table name) field name
		 * (eg. if the table name is ca_objects_x_entities then the "left" name is ca_objects)
		 */
		public function getLeftTableFieldName() {
			return $this->RELATIONSHIP_LEFT_FIELDNAME;
		}
 		# ------------------------------------------------------
		/**
		 * Returns name of the "right" table (by convention the table mentioned first in the relationship table name) field name
		 * (eg. if the table name is ca_objects_x_entities then the "left" name is ca_objects)
		 */
		public function getRightTableFieldName() {
			return $this->RELATIONSHIP_RIGHT_FIELDNAME;
		}
 		# ------------------------------------------------------
		/**
		 * Returns name of the foreign key pointing to ca_relationship_types (typically = 'type_id')
		 */
		public function getTypeFieldName() {
			return $this->RELATIONSHIP_TYPE_FIELDNAME;
		}
 		# ------------------------------------------------------
		/**
		 * Returns an array of relationship types for this relation, subject to sub type restrictions
		 */
		public function getRelationshipTypes($pn_sub_type_left_id=null, $pn_sub_type_right_id=null, $pa_options=null) {
			$o_db = $this->getDb();
			
			$vs_sub_type_left_sql = '';
			if ($pn_sub_type_left_id) {
				// Support hierarchical subtypes - if the subtype restriction is a type with parents then include those as well
				// Allows subtypes to "inherit" bindings from parent types
				$t_list_item = new ca_list_items($pn_sub_type_left_id);
				$va_ancestor_ids = $t_list_item->getHierarchyAncestors(null, array('idsOnly' => true, 'includeSelf' => true));
				
				$vs_sub_type_left_sql = '(crt.sub_type_left_id IS NULL OR crt.sub_type_left_id IN ('.join(',', $va_ancestor_ids).'))';
			}
			$vs_sub_type_right_sql = '';
			if ($pn_sub_type_right_id) {
				// Support hierarchical subtypes - if the subtype restriction is a type with parents then include those as well
				// Allows subtypes to "inherit" bindings from parent types
				$t_list_item = new ca_list_items($pn_sub_type_right_id);
				$va_ancestor_ids = $t_list_item->getHierarchyAncestors(null, array('idsOnly' => true, 'includeSelf' => true));
				$vs_sub_type_right_sql = '(crt.sub_type_right_id IS NULL OR crt.sub_type_right_id IN ('.join(',', $va_ancestor_ids).'))';
			}
			
			$vs_restrict_to_relationship_type_sql = '';
			if (isset($pa_options['restrict_to_relationship_types']) && $pa_options['restrict_to_relationship_types']) {
				if (!is_array($pa_options['restrict_to_relationship_types'])) {
					$pa_options['restrict_to_relationship_types'] = array($pa_options['restrict_to_relationship_types']);
				}
				if (sizeof($pa_options['restrict_to_relationship_types'])) {
					$va_restrict_to_type_list = array();
					$t_rel_type = new ca_relationship_types();
					
					foreach($pa_options['restrict_to_relationship_types'] as $vs_type_code) {
						$va_criteria = array('table_num' => $this->tableNum());
						if (is_numeric($vs_type_code)) {
							$va_criteria['type_id'] = (int)$vs_type_code;
						} else {
							$va_criteria['type_code'] = $vs_type_code;
						}
						if ($t_rel_type->load(array($va_criteria))) {
							$va_restrict_to_type_list[] = "(crt.hier_left >= ".$t_rel_type->get('hier_left')." AND crt.hier_right <= ".$t_rel_type->get('hier_right').")";
						}
					}
					if (sizeof($va_restrict_to_type_list)) {
						$vs_restrict_to_relationship_type_sql = " AND (".join(' OR ', $va_restrict_to_type_list).")";
					}
				}
			}
			
			$vs_sql = "
				SELECT *
				FROM ca_relationship_types crt
				INNER JOIN ca_relationship_type_labels AS crtl ON crt.type_id = crtl.type_id
				WHERE
					(crt.table_num = ?) ".(($vs_sub_type_left_sql || $vs_sub_type_right_sql) ? ' AND ' : '')." 
					{$vs_sub_type_left_sql} ".(($vs_sub_type_left_sql && $vs_sub_type_right_sql) ? ' AND ' : '')." {$vs_sub_type_right_sql}
					AND (crt.parent_id IS NOT NULL)
					{$vs_restrict_to_relationship_type_sql}
			";
	
			$qr_res = $o_db->query($vs_sql, $this->tableNum());
			
			$va_types = array();
			while($qr_res->nextRow()) {
				$vs_key = '';
				if (strlen($vs_rank = $qr_res->get('rank'))) {
					$vs_key .= (int)sprintf("%08d", (int)$qr_res->get('rank'));
				}
				$vs_key .= $qr_res->get('typename_forward');
				$va_types[$vs_key][(int)$qr_res->get('type_id')][(int)$qr_res->get('locale_id')] = $qr_res->getRow();
			}
			ksort($va_types, SORT_NUMERIC);
			$va_sorted_types = array();
			foreach($va_types as $vs_k => $va_v) {
				foreach($va_v as $vn_type_id => $va_types_by_locale) {
					$va_sorted_types[$vn_type_id] = $va_types_by_locale;
				}
			}
			return caExtractValuesByUserLocale($va_sorted_types, null, null, array());
		}
		# ------------------------------------------------------
		/**
		 * Returns an HTML <select> element of relationship types with values=type_id and option text = to the typename
		 */
		public function getRelationshipTypesAsHTMLSelect($ps_orientation, $pn_sub_type_left_id=null, $pn_sub_type_right_id=null) {
			$vs_left_table_name = $this->getLeftTableName();
			$vs_right_table_name = $this->getRightTableName();
			if (!in_array($ps_orientation, array($vs_left_table_name, $vs_right_table_name))) { $ps_orientation = $vs_left_table_name; }
			
			$va_types = $this->getRelationshipTypes($pn_sub_type_left_id, $pn_sub_type_right_id);
			$va_options = array();
			
			$va_parent_ids = array();
			$vn_l = 0;
			foreach($va_types as $va_type) {
				if (($vn_i = array_search($va_type['parent_id'], $va_parent_ids)) === false) {
					$va_parent_ids[] = $va_type['parent_id']; 
					$vn_l++;
				} else {
					if ($vn_i < sizeof($va_parent_ids) - 1) {
						$vn_l = sizeof($va_parent_ids) - 1 - $vn_i;
					}
				}
			
				$va_options[str_repeat("&#160;&#160;&#160;", $vn_l-1).(($ps_orientation == $vs_left_table_name) ? $va_type['typename'] : $va_type['typename_reverse'])] = $va_type['type_id'];
			}
			
			return caHTMLSelect('type_id', $va_options);
		}
		# ------------------------------------------------------
		/**
		 * Returns an associative array of relationship types for the relationship
		 * organized by the sub_type_id specified by $ps_orientation. If $ps_orientation is the name of the "right" table
		 * then sub_type_left_id is used for keys in the array, if $ps_orientation is the name of the "left" table
		 * then sub_type_right_id is used for keys.
		 *
		 * For example, for ca_objects_x_entities, if $ps_orientation is ca_objects then then sub_type_right_id is
		 * used as the key; if ca_entities is passed then sub_type_left_id is used; if a table name is passed that
		 * is not either side of the relation then an empty array is returned
		 *
		 */
		public function getRelationshipTypesBySubtype($ps_orientation, $pn_type_id, $pa_options=null) {
			if (!$this->hasField('type_id')) { return array(); }
			$vs_left_table_name = $this->getLeftTableName();
			$vs_right_table_name = $this->getRightTableName();
			
			$o_db = $this->getDb();
			$t_rel_type = new ca_relationship_types();
					
			$vs_restrict_to_relationship_type_sql = '';
			if (isset($pa_options['restrict_to_relationship_types']) && $pa_options['restrict_to_relationship_types']) {
				if(!is_array($pa_options['restrict_to_relationship_types'])) {
					$pa_options['restrict_to_relationship_types'] = array($pa_options['restrict_to_relationship_types']);
				}
				if(sizeof($pa_options['restrict_to_relationship_types'])) {
					$va_restrict_to_type_list = array();
					
					foreach($pa_options['restrict_to_relationship_types'] as $vs_type_code) {
						if (!strlen(trim($vs_type_code))) { continue; }
						
						$va_criteria = array('table_num' => $this->tableNum());
						if (is_numeric($vs_type_code)) {
							$va_criteria['type_id'] = (int)$vs_type_code;
						} else {
							$va_criteria['type_code'] = $vs_type_code;
						}
						if ($t_rel_type->load($va_criteria)) {
							$va_restrict_to_type_list[] = "(crt.hier_left >= ".$t_rel_type->get('hier_left')." AND crt.hier_right <= ".$t_rel_type->get('hier_right').")";
						}
					}
					
					if (sizeof($va_restrict_to_type_list)) {
						$vs_restrict_to_relationship_type_sql = " AND (".join(' OR ', $va_restrict_to_type_list).")";
					}
				}
			}
			
			$qr_res = $o_db->query($x="
				SELECT *
				FROM ca_relationship_types crt
				INNER JOIN ca_relationship_type_labels AS crtl ON crt.type_id = crtl.type_id
				WHERE
					(crt.table_num = ?)
					{$vs_restrict_to_relationship_type_sql}
			", $this->tableNum());		
			
			// Support hierarchical subtypes - if the subtype restriction is a type with parents then include those as well
			// Allows subtypes to "inherit" bindings from parent types
			$t_list_item = new ca_list_items($pn_type_id);
			if (!is_array($va_ancestor_ids = $t_list_item->getHierarchyAncestors(null, array('idsOnly' => true, 'includeSelf' => true)))) {
				$va_ancestor_ids = array();
			}
			// remove hierarchy root from ancestor list, otherwise invalid bindings 
			// from root nodes (which are not "real" rel types) may be inherited
			$va_ancestor_ids = array_diff($va_ancestor_ids, array($t_list_item->getHierarchyRootID()));
			
			$va_types = array();
			$va_parent_ids = array();
			$vn_l = 0;
							
			$vn_root_id = ($t_rel_type->load(array('parent_id' => null, 'table_num' =>  $this->tableNum()))) ? $t_rel_type->getPrimaryKey() : null;
			$va_hier = array();
			
			if ($vs_left_table_name === $vs_right_table_name) {
				// ----------------------------------------------------------------------------------------
				// self relationship
				while($qr_res->nextRow()) {
					$vn_parent_id = $qr_res->get('parent_id');
					$va_hier[$vn_parent_id][] = $qr_res->get('type_id');
					
					$va_row = $qr_res->getRow();
					
					// skip type if it has a subtype set and it's not in our list
					$vs_subtype_orientation = null;
					$vs_subtype = null;
					if (
						$qr_res->get('sub_type_left_id') && !(((in_array($qr_res->get('sub_type_left_id'), $va_ancestor_ids))))
					) { // not left
						
						if ($qr_res->get('sub_type_right_id') && !((in_array($qr_res->get('sub_type_right_id'), $va_ancestor_ids)))) {
							// not left and not right
							continue;
						} else {
							// not left and right
							$vs_subtype = $qr_res->get('sub_type_left_id');	
							$vs_subtype_orientation = "left";
						}
					} else if (
						$qr_res->get('sub_type_left_id') && in_array($qr_res->get('sub_type_left_id'), $va_ancestor_ids)
					) { // left
						if ($qr_res->get('sub_type_right_id') && ((in_array($qr_res->get('sub_type_right_id'), $va_ancestor_ids)))) {
							// left and right
							$vs_subtype = $qr_res->get('sub_type_right_id');
							$vs_subtype_orientation = "";
						} else {
							// left and not right
							$vs_subtype_orientation = "right";
							$vs_subtype = $qr_res->get('sub_type_right_id');	
						}
					}
					if (!$vs_subtype) { $vs_subtype = 'NULL'; }
					
					switch($vs_subtype_orientation) {
						case 'left':
							$va_tmp = $va_row;
							$vs_key = (strlen($va_tmp['rank']) > 0)  ? sprintf("%08d", (int)$va_tmp['rank']).preg_replace('![^A-Za-z0-9_]+!', '_', $va_tmp['typename_reverse']) : preg_replace('![^A-Za-z0-9_]+!', '_', $va_tmp['typename_reverse']);
							$va_tmp['typename'] = $va_tmp['typename_reverse'];
							unset($va_tmp['typename_reverse']);		// we pass the typename adjusted for direction in 'typename', so there's no need to include typename_reverse in the returned values

							$va_types[$vn_parent_id][$vs_subtype][$vs_key][$qr_res->get('type_id')][$qr_res->get('locale_id')] = $va_tmp;	
							
							break;
						case 'right':
							$va_tmp = $va_row;
							
							$vs_key = (strlen($va_tmp['rank']) > 0)  ? sprintf("%08d", (int)$va_tmp['rank']).preg_replace('![^A-Za-z0-9_]+!', '_', $va_tmp['typename']) : preg_replace('![^A-Za-z0-9_]+!', '_', $va_tmp['typename']);
							//$va_tmp['typename'] = $va_tmp['typename'];
							unset($va_tmp['typename_reverse']);		// we pass the typename adjusted for direction in 'typename', so there's no need to include typename_reverse in the returned values

							$va_types[$vn_parent_id][$vs_subtype][$vs_key][$qr_res->get('type_id')][$qr_res->get('locale_id')] = $va_tmp;
							break;
						default:
							$va_tmp = $va_row;
							if ($va_tmp['typename'] == $va_tmp['typename_reverse']) {
								//
								// If the sides of the self-relationship are the same then treat it like a normal relationship type: one entry in the
								// list and a plain type_id value
								//
								//$va_tmp['typename'] =  $va_tmp['typename'];
								unset($va_tmp['typename_reverse']);		// we pass the typename adjusted for direction in 'typename', so there's no need to include typename_reverse in the returned values
	
								$va_tmp['direction'] = null;
								
								$vs_key = (strlen($va_tmp['rank']) > 0)  ? sprintf("%08d", (int)$va_tmp['rank']).preg_replace('![^A-Za-z0-9_]+!', '_', $va_tmp['typename']) : preg_replace('![^A-Za-z0-9_]+!', '_', $va_tmp['typename']);
								$va_types[$vn_parent_id][$vs_subtype][$vs_key][$qr_res->get('type_id')][$qr_res->get('locale_id')] = $va_tmp;
								
							} else {
								//
								// If each side of the self-relationship type are different then add both to the list with special type_id values that
								// indicate the directionality of the typename (ltor = left to right = "typename"; rtor = right to left = "typename_reverse")
								//
								$va_tmp = $va_row;
								//$va_tmp['typename'] =  $va_tmp['typename'];
								unset($va_tmp['typename_reverse']);		// we pass the typename adjusted for direction in 'typename', so there's no need to include typename_reverse in the returned values
	
								$vs_key = (strlen($va_tmp['rank']) > 0)  ? sprintf("%08d", (int)$va_tmp['rank']).preg_replace('![^A-Za-z0-9_]+!', '_', $va_tmp['typename']) : preg_replace('![^A-Za-z0-9_]+!', '_', $va_tmp['typename']);
								$va_tmp['direction'] = 'ltor';
								$va_types[$vn_parent_id][$vs_subtype][$vs_key]['ltor_'.$qr_res->get('type_id')][$qr_res->get('locale_id')] = $va_tmp;
								
								$va_tmp = $va_row;
								$va_tmp['typename'] =  $va_tmp['typename_reverse'];
								unset($va_tmp['typename_reverse']);		// we pass the typename adjusted for direction in 'typename', so there's no need to include typename_reverse in the returned values
		
								$vs_key = (strlen($va_tmp['rank']) > 0)  ? sprintf("%08d", (int)$va_tmp['rank']).preg_replace('![^A-Za-z0-9_]+!', '_', $va_tmp['typename_reverse']) : preg_replace('![^A-Za-z0-9_]+!', '_', $va_tmp['typename_reverse']);
								$va_tmp['direction'] = 'rtol';
								$va_types[$vn_parent_id][$vs_subtype][$vs_key]['rtol_'.$qr_res->get('type_id')][$qr_res->get('locale_id')] = $va_tmp;
							}
							
							break;
					}
				}
				
					
				$va_types = $this->_processRelationshipHierarchy($vn_root_id, $va_hier, $va_types, 1);
				
				$va_processed_types = array('_type_map' => array());
				$va_subtype_lookups = array();

				foreach($va_types as $vs_subtype => $va_types_by_subtype) {
					$va_types_by_locale = array();
					foreach($va_types_by_subtype as $vs_key => $va_types_by_key) {
						$va_types_by_locale += $va_types_by_key;
					}
				
					// include mapping from parent type used in restriction to child types that inherit the binding
					if (($vs_subtype != 'NULL') && (!isset($va_subtype_lookups[$vs_subtype]) || !$va_subtype_lookups[$vs_subtype])) {
						$va_children = $t_list_item->getHierarchyChildren($vs_subtype, array('idsOnly' => true));
						foreach($va_children as $vn_child) {
							$va_processed_types['_type_map'][$vn_child] = $vs_subtype;
						}
						$va_subtype_lookups[$vs_subtype] = true;
					}
					$va_processed_types[$vs_subtype] = caExtractValuesByUserLocale($va_types_by_locale, null, null, array('returnList' => true));					
				}
				
			} else {
				// ----------------------------------------------------------------------------------------
				// regular relationship
				if (!in_array($ps_orientation, array($vs_left_table_name, $vs_right_table_name))) { return array(); }
				
				while($qr_res->nextRow()) {
					$vn_parent_id = $qr_res->get('parent_id');
					$va_hier[$vn_parent_id][] = $qr_res->get('type_id');
					
					$va_row = $qr_res->getRow();
					if ($ps_orientation == $vs_left_table_name) {
						// right-to-left
						
						// skip type if it has a subtype set and it's not in our list
						if (!((!$qr_res->get('sub_type_left_id') || (in_array($qr_res->get('sub_type_left_id'), $va_ancestor_ids))))) { continue; }
						$vs_subtype = $qr_res->get('sub_type_right_id');
						
						$vs_key = (strlen($va_row['rank']) > 0)  ? sprintf("%08d", (int)$va_row['rank']).preg_replace('![^A-Za-z0-9_]+!', '_', $va_row['typename']) : preg_replace('![^A-Za-z0-9_]+!', '_', $va_row['typename']);
							
					} else {
						// left-to-right
						
						// skip type if it has a subtype set and it's not in our list
						if (!((!$qr_res->get('sub_type_right_id') || (in_array($qr_res->get('sub_type_right_id'), $va_ancestor_ids))))) { continue; }
						$vs_subtype = $qr_res->get('sub_type_left_id');	
						
						$va_row['typename'] = $va_row['typename_reverse'];
						
						$vs_key = (strlen($va_row['rank']) > 0)  ? sprintf("%08d", (int)$va_row['rank']).preg_replace('![^A-Za-z0-9_]+!', '_', $va_row['typename_reverse']) : preg_replace('![^A-Za-z0-9_]+!', '_', $va_row['typename_reverse']);
					}
					unset($va_row['typename_reverse']);		// we pass the typename adjusted for direction in '_display', so there's no need to include typename_reverse in the returned values
					if (!$vs_subtype) { $vs_subtype = 'NULL'; }
					
					$vn_type_id = $qr_res->get('type_id');
					$va_types[$vn_parent_id][$vs_subtype][$vs_key][$vn_type_id][$qr_res->get('locale_id')] = $va_row;
				}
				
				$va_types = $this->_processRelationshipHierarchy($vn_root_id, $va_hier, $va_types, 1);
			
				$va_processed_types = array('_type_map' => array());
				$va_subtype_lookups = array();
			
				foreach($va_types as $vs_subtype => $va_types_by_subtype) {
					$va_types_by_locale = array();
					foreach($va_types_by_subtype as $vs_key => $va_types_by_key) {
						$va_types_by_locale += $va_types_by_key;
					}
					
					// include mapping from parent type used in restriction to child types that inherit the binding
					if (($vs_subtype != 'NULL') && (!isset($va_subtype_lookups[$vs_subtype]) || !$va_subtype_lookups[$vs_subtype])) {
						$va_children = $t_list_item->getHierarchyChildren($vs_subtype, array('idsOnly' => true));
						foreach($va_children as $vn_child) {
							$va_processed_types['_type_map'][$vn_child] = $vs_subtype;
						}
						$va_subtype_lookups[$vs_subtype] = true;
					}
					$va_processed_types[$vs_subtype] = caExtractValuesByUserLocale($va_types_by_locale, null, null, array('returnList' => true));					
				}
			}
			return $va_processed_types;
		}
		# ------------------------------------------------------
		private function _processRelationshipHierarchy($pn_id, $pa_hier, $pa_types, $pn_level) {
			$va_types_to_return = array();
			if(!is_array($pa_hier[$pn_id])) { return null; }
			if (!is_Array($pa_types[$pn_id])) { print_R($pa_types); print "id was $pn_id<br>";  die;}
			foreach($pa_types[$pn_id] as $vs_sub_types => $va_list) {	// items in this level
				ksort($va_list);
				foreach($va_list as $vs_key => $va_list_by_type_id) {
					foreach($va_list_by_type_id as $vn_type_id => $va_item) {
						$va_types_to_return[$vs_sub_types][$vs_key][$vn_type_id] = $va_item;		// output item
					
						// look for sub items
						if (is_array($pa_hier[$vn_type_id])) {
							if (is_array($va_tmp = $this->_processRelationshipHierarchy($vn_type_id, $pa_hier, $pa_types, $pn_level + 1))) {
								foreach($va_tmp as $x_vs_sub_types => $x_va_list) {
									foreach($x_va_list as $x_vs_key => $x_va_list_by_type_id) {
										foreach($x_va_list_by_type_id as $x_vn_type_id => $x_va_item) {
											foreach($x_va_item as $vn_i => $x_va_item_entry) {
												$x_va_item[$vn_i]['typename'] = '&#160;&#160;&#160;&#160;'.$x_va_item_entry['typename'] ;
											}
											$va_types_to_return[$x_vs_sub_types][$x_vs_key][$x_vn_type_id] = $x_va_item;
										}
									}
								}
							}
						}
					}
				}
			}
			
			return $va_types_to_return;
		}
		# ------------------------------------------------------
	}
?>
