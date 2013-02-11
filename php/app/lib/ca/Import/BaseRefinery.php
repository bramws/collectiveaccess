<?php
/** ---------------------------------------------------------------------
 * app/lib/ca/BaseRefinery.php : 
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2013 Whirl-i-Gig
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
 * @subpackage Dashboard
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License version 3
 *
 * ----------------------------------------------------------------------
 */
 
 /**
  *
  */
 
 	require_once(__CA_LIB_DIR__.'/core/ApplicationVars.php'); 	
 
	abstract class BaseRefinery {
		# -------------------------------------------------------
		/** 
		 *
		 */
		static $s_refinery_settings = array();
		
		/** 
		 *
		 */
		protected $ops_name = null;
		
		/** 
		 *
		 */
		protected $ops_title = null;
		
		/** 
		 *
		 */
		protected $ops_description = null;
		# -------------------------------------------------------
		public function __construct() {
		
		}
		# -------------------------------------------------------
		/**
		 *
		 */
		public function getRefinerySettings() {
			return BaseRefinery::$s_refinery_settings[$this->getName()]; 
		}
		# -------------------------------------------------------
		/**
		 *
		 */
		public function getName() {
			return $this->ops_name; 
		}
		# -------------------------------------------------------
		/**
		 *
		 */
		public function getTitle() {
			return $this->ops_title; 
		}
		# -------------------------------------------------------
		/**
		 *
		 */
		public function getDescription() {
			return $this->ops_description; 
		}
		
		# -------------------------------------------------------
		/**
		 *
		 */
		public static function parsePlaceholder($ps_placeholder, $pa_source_data, $pa_item, $ps_delimiter=null, $pn_index=0) {
			$ps_placeholder = trim($ps_placeholder);
			if ($ps_placeholder[0] == '^') {
				$vs_val = $pa_source_data[substr($ps_placeholder, 1)];
			} else {
				$vs_val = $ps_placeholder;
			}
			
			if ($ps_delimiter) {
				$va_val = explode($ps_delimiter, $vs_val);
				if ($pn_index < sizeof($va_val)) {
					if (!($vs_val = $va_val[$pn_index])) { $vs_val = ''; }
				} else {
					$vs_val = array_shift($va_val);
				}
			}
			$vs_val = trim($vs_val);
			
			if (is_array($pa_item['settings']['original_values']) && (($vn_i = array_search(mb_strtolower($vs_val), $pa_item['settings']['original_values'])) !== false)) {
				$vs_val = $pa_item['settings']['replacement_values'][$vn_i];
			}
			
			return trim($vs_val);
		}
		# -------------------------------------------------------
		/**
		 * Process a mapped value
		 *
		 * @param array $pa_destination_data Array of data that to be imported. Will contain the product of all mappings performed on the current source row *to date*. The refinery can make any required additions and modifications to this data; since it's passed by reference those changes will be returned.
		 * @param array $pa_group Specification and settings for the mapping group being processed
		 * @param mixed $pa_item Specification and settings for the mapping item being processed. Settings are in an array under the "settings" key
		 * @param array $pa_source_data The entire source row. You can extract the current value being processed by plugging the item "source" specification into $pa_source_data
		 * @param array $pa_options Refinery-specific processing options
		 *
		 * @return array The value(s) to add 
		 */
		abstract function refine(&$pa_destination_data, $pa_group, $pa_item, $pa_source_data, $pa_options=null);
		# -------------------------------------------------------	
	}
?>