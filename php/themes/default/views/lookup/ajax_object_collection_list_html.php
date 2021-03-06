<?php
/* ----------------------------------------------------------------------
 * lookup/ajax_object_collection_list_html.php : 
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
 * ----------------------------------------------------------------------
 */
 	foreach($this->getVar('collection_list') as $vn_item_id => $va_item) {
		print str_replace("|", "-", $va_item['_display'])."|ca_collections-".$vn_item_id."|".$va_item['type_id']."|".$va_item['_query']."\n";
	}
	foreach($this->getVar('object_list') as $vn_item_id => $va_item) {
		print str_replace("|", "-", $va_item['_display'])."|ca_objects-".$vn_item_id."|".$va_item['type_id']."|".$va_item['_query']."\n";
	}
?>