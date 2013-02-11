<?php
/* ----------------------------------------------------------------------
 * bundles/ca_list_items.php : 
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
 * ----------------------------------------------------------------------
 */
 
 	JavascriptLoadManager::register('hierBrowser');
 
	$vs_id_prefix 		= $this->getVar('placement_code').$this->getVar('id_prefix');
	$t_instance 		= $this->getVar('t_instance');
	$t_item 			= $this->getVar('t_item');				// list item
	$t_item_rel 		= $this->getVar('t_item_rel');
	$va_settings 		= $this->getVar('settings');
	$vs_add_label 		= $this->getVar('add_label');
	$va_rel_types		= $this->getVar('relationship_types');
	$vb_batch			= $this->getVar('batch');
	
	$vs_sort			=	((isset($va_settings['sort']) && $va_settings['sort'])) ? $va_settings['sort'] : '';
	$vb_read_only		=	((isset($va_settings['readonly']) && $va_settings['readonly'])  || ($this->request->user->getBundleAccessLevel($t_instance->tableName(), 'ca_list_items') == __CA_BUNDLE_ACCESS_READONLY__));
	
	$va_initial_values	= $this->getVar('initialValues');
	
	$vn_browse_last_id = (int)$this->request->session->getVar('ca_list_items_'.$vs_id_prefix.'_browse_last_id');

	// params to pass during occurrence lookup
	$va_lookup_params = (isset($va_settings['restrict_to_type']) && $va_settings['restrict_to_type']) ? array('type' => $va_settings['restrict_to_type'], 'noSubtypes' => (int)$va_settings['dont_include_subtypes_in_type_restriction']) : array();

	if ($vb_batch) {
		print caBatchEditorRelationshipModeControl($t_item, $vs_id_prefix);
	}
?>
<div id="<?php print $vs_id_prefix.$t_item->tableNum().'_rel'; ?>" <?php print $vb_batch ? "class='editorBatchBundleContent'" : ''; ?>>
<?php
	//
	// Template to generate display for existing items
	//
?>
	<textarea class='caItemTemplate' style='display: none;'>
<?php
	switch($va_settings['list_format']) {
		case 'list':
?>
		<div id="<?php print $vs_id_prefix; ?>Item_{n}" class="labelInfo listRel">
<?php
	if (!$vb_read_only) {
?>				
			<a href="#" class="caDeleteItemButton listRelDeleteButton"><?php print caNavIcon($this->request, __CA_NAV_BUTTON_DEL_BUNDLE__); ?></a>
<?php
	}
?>
			<a href="<?php print urldecode(caEditorUrl($this->request, 'ca_occurrences', '{occurrence_id}')); ?>" class="caEditItemButton" id="<?php print $vs_id_prefix; ?>_edit_related_{n}"></a>
			{_display}
			({{relationship_typename}})
			<input type="hidden" name="<?php print $vs_id_prefix; ?>_type_id{n}" id="<?php print $vs_id_prefix; ?>_type_id{n}" value="{type_id}"/>
			<input type="hidden" name="<?php print $vs_id_prefix; ?>_id{n}" id="<?php print $vs_id_prefix; ?>_id{n}" value="{id}"/>
		</div>
<?php
			break;
		case 'bubbles':
		default:
?>
<?php
		if ((bool)$va_settings['restrictToTermsRelatedToCollection']) {
?>
			<div id="<?php print $vs_id_prefix; ?>Item_{n}" class="labelInfo">	
				<table class="attributeListItem" cellpadding="5" cellspacing="0">
					<tr>
						<td class="attributeListItem">
<?php
	if ($vs_checklist = ca_lists::getListAsHTMLFormElement(null, $vs_id_prefix."_id{n}", null, array('render' => 'checklist', 'limitToItemsRelatedToCollections' => $t_instance->get('ca_collections.collection_id', array('returnAsArray' => true)), 'limitToItemsRelatedToCollectionWithRelationshipTypes' => $va_settings['restrictToTermsOnCollectionWithRelationshipType'], 'maxColumns' => 3))) {
		print $vs_checklist;
	} else {
?>
		<h2><?php print _t('No collection subjects'); ?></h2>
<?php
	}
?>
							<input type="hidden" name="<?php print $vs_id_prefix; ?>_type_id{n}" id="<?php print $vs_id_prefix; ?>_type_id{n}" value="<?php print array_pop($va_settings['restrictToTermsOnCollectionUseRelationshipType']); ?>"/>
						</td>
<?php
	if (!(bool)$va_settings['restrictToTermsRelatedToCollection']) {
?>
						<td>
							<a href="#" class="caDeleteItemButton"><?php print caNavIcon($this->request, __CA_NAV_BUTTON_DEL_BUNDLE__); ?></a>
						</td>
<?php
	}
?>
					</tr>
				</table>
			</div>
<?php
		} else {
?>
		<div id="<?php print $vs_id_prefix; ?>Item_{n}" class="labelInfo roundedRel">
			<a href="<?php print urldecode(caEditorUrl($this->request, 'ca_list_items', '{item_id}')); ?>" class="caEditItemButton" id="<?php print $vs_id_prefix; ?>_edit_related_{n}">{{label}}</a>
			({{relationship_typename}})
			<input type="hidden" name="<?php print $vs_id_prefix; ?>_type_id{n}" id="<?php print $vs_id_prefix; ?>_type_id{n}" value="{type_id}"/>
			<input type="hidden" name="<?php print $vs_id_prefix; ?>_id{n}" id="<?php print $vs_id_prefix; ?>_id{n}" value="{id}"/>
<?php
			if (!$vb_read_only) {
?>				
				<a href="#" class="caDeleteItemButton"><?php print caNavIcon($this->request, __CA_NAV_BUTTON_DEL_BUNDLE__); ?></a>
<?php
			}
?>			
			<div style="display: none;" class="itemName">{label}</div>
			<div style="display: none;" class="itemIdno">{idno_sort}</div>
		</div>
<?php
		}
	}
?>
	</textarea>
<?php
	//
	// Template to generate controls for creating new relationship
	//
?>

	<textarea class='caNewItemTemplate' style='display: none;'>
		<div style="clear: both; width: 1px; height: 1px;"><!-- empty --></div>
		<div id="<?php print $vs_id_prefix; ?>Item_{n}" class="labelInfo">
<?php
		if (!(bool)$va_settings['useHierarchicalBrowser']) {
?>
				<table class="caListItem">
					<tr>
						<td><input type="text" size="60" name="<?php print $vs_id_prefix; ?>_autocomplete{n}" value="{{label}}" id="<?php print $vs_id_prefix; ?>_autocomplete{n}" class="lookupBg"/></td>
						<td>
						<select name="<?php print $vs_id_prefix; ?>_type_id{n}" id="<?php print $vs_id_prefix; ?>_type_id{n}" style="display: none;"></select>
						<input type="hidden" name="<?php print $vs_id_prefix; ?>_id{n}" id="<?php print $vs_id_prefix; ?>_id{n}" value="{id}"/>
						</td>
						<td>
							<a href="#" class="caDeleteItemButton"><?php print caNavIcon($this->request, __CA_NAV_BUTTON_DEL_BUNDLE__); ?></a>
						</td>
					</tr>
				</table>
<?php
		} else {
			$vn_use_as_root_id = 'null';
			if (sizeof($va_settings['restrict_to_lists']) == 1) {
				$t_item = new ca_list_items();
				if ($t_item->load(array('list_id' => $va_settings['restrict_to_lists'][0], 'parent_id' => null))) {
					$vn_use_as_root_id = $t_item->getPrimaryKey();
				}
			}
?>
				<div style="float: right;"><a href="#" class="caDeleteItemButton"><?php print caNavIcon($this->request, __CA_NAV_BUTTON_DEL_BUNDLE__); ?></a></div>
				<div style='width: 690px; height: <?php print $va_settings['hierarchicalBrowserHeight']; ?>;'>
					
					<div id='<?php print $vs_id_prefix; ?>_hierarchyBrowser{n}' style='width: 100%; height: 100%;' class='hierarchyBrowser'>
						<!-- Content for hierarchy browser is dynamically inserted here by ca.hierbrowser -->
					</div><!-- end hierarchyBrowser -->	</div>
					
				<div style="clear: both; width: 1px; height: 1px;"><!-- empty --></div>
				<div style="float: right;">
					<div class='hierarchyBrowserSearchBar'><?php print _t('Search'); ?>: <input type='text' id='<?php print $vs_id_prefix; ?>_hierarchyBrowserSearch{n}' class='hierarchyBrowserSearchBar' name='search' value='' size='40'/></div>
				</div>
				<div style="float: left;">
					<?php print _t('Type'); ?>: <select name="<?php print $vs_id_prefix; ?>_type_id{n}" id="<?php print $vs_id_prefix; ?>_type_id{n}" style="display: none;"></select>
					<input type="hidden" name="<?php print $vs_id_prefix; ?>_id{n}" id="<?php print $vs_id_prefix; ?>_id{n}" value="{id}"/>
				</div>	
				
				<script type='text/javascript'>
					jQuery(document).ready(function() { 
						var init = true;
						var <?php print $vs_id_prefix; ?>oHierBrowser{n} = caUI.initHierBrowser('<?php print $vs_id_prefix; ?>_hierarchyBrowser{n}', {
							uiStyle: 'horizontal',
							levelDataUrl: '<?php print caNavUrl($this->request, 'lookup', 'ListItem', 'GetHierarchyLevel', array('noSymbols' => 1, 'voc' => 1, 'lists' => join(';', $va_settings['restrict_to_lists']))); ?>',
							initDataUrl: '<?php print caNavUrl($this->request, 'lookup', 'ListItem', 'GetHierarchyAncestorList'); ?>',
							
							bundle: '<?php print $vs_id_prefix; ?>',
							
							selectOnLoad : true,
							browserWidth: "<?php print $va_settings['hierarchicalBrowserWidth']; ?>",
							
							dontAllowEditForFirstLevel: false,
							
							className: 'hierarchyBrowserLevel',
							classNameContainer: 'hierarchyBrowserContainer',
							
							editButtonIcon: '<img src="<?php print $this->request->getThemeUrlPath(); ?>/graphics/buttons/arrow_grey_right.gif" border="0" title="Edit"/>',
							
							initItemID: <?php print $vn_browse_last_id; ?>,
							useAsRootID: <?php print $vn_use_as_root_id; ?>,
							indicatorUrl: '<?php print $this->request->getThemeUrlPath(); ?>/graphics/icons/indicator.gif',
							
							currentSelectionDisplayID: '<?php print $vs_id_prefix; ?>_browseCurrentSelectionText{n}',
							onSelection: function(item_id, parent_id, name, display, type_id) {
								if (!init) {	// Don't actually select the init value, otherwise if you save w/no selection you get "phantom" relationships
									caRelationBundle<?php print $vs_id_prefix; ?>.select('{n}', {id: item_id, type_id: type_id}, display);
								}
								init = false;
							}
						});
						
						jQuery('#<?php print $vs_id_prefix; ?>_hierarchyBrowserSearch{n}').autocomplete(
							{
								source: '<?php print caNavUrl($this->request, 'lookup', 'ListItem', 'Get', array('noInline' => 1, 'noSymbols' => 1)); ?>', 
								minLength: 3, delay: 800, html: false,
								select: function(event, ui) {
									if (parseInt(ui.item.id) > 0) {
										<?php print $vs_id_prefix; ?>oHierBrowser{n}.setUpHierarchy(ui.item.id);	// jump browser to selected item
									}
									event.preventDefault();
									jQuery('#<?php print $vs_id_prefix; ?>_hierarchyBrowserSearch{n}').val('');
								}
							}
						);
	
					});
				</script>
<?php
	}
?>
		</div>
	</textarea>
	
	<div class="bundleContainer">
<?php
	if(sizeof($va_initial_values) && !$vb_read_only && !(bool)$va_settings['restrictToTermsRelatedToCollection'] && !$vs_sort) {
?>
		<div class="caItemListSortControlTrigger" id="<?php print $vs_id_prefix; ?>caItemListSortControlTrigger">
			<?php print _t('Sort by'); ?>
			<img src="<?php print $this->request->getThemeUrlPath(); ?>/graphics/icons/bg.gif" alt='Sort'/>
		</div>
		<div class="caItemListSortControls" id="<?php print $vs_id_prefix; ?>caItemListSortControls">
			<a href='#' onclick="caRelationBundle<?php print $vs_id_prefix; ?>.sort('name'); return false;" class='caItemListSortControl'><?php print _t('name'); ?></a><br/>
			<a href='#' onclick="caRelationBundle<?php print $vs_id_prefix; ?>.sort('idno'); return false;" class='caItemListSortControl'><?php print _t('idno'); ?></a><br/>
			<a href='#' onclick="caRelationBundle<?php print $vs_id_prefix; ?>.sort('type'); return false;" class='caItemListSortControl'><?php print _t('type'); ?></a><br/>
			<a href='#' onclick="caRelationBundle<?php print $vs_id_prefix; ?>.sort('entry'); return false;" class='caItemListSortControl'><?php print _t('entry'); ?></a><br/>
		</div>
<?php
	}
?>

		<div class="caItemList">
		
		</div>
		<input type="hidden" name="<?php print $vs_id_prefix; ?>BundleList" id="<?php print $vs_id_prefix; ?>BundleList" value=""/>
		<div style="clear: both; width: 1px; height: 1px;"><!-- empty --></div>
<?php
	if (!$vb_read_only && !(bool)$va_settings['restrictToTermsRelatedToCollection']) {
?>	
		<div class='button labelInfo caAddItemButton'><a href='#'><?php print caNavIcon($this->request, __CA_NAV_BUTTON_ADD__); ?> <?php print $vs_add_label ? $vs_add_label : _t("Add relationship"); ?></a></div>
<?php
	}
?>
	</div>
</div>
			
<script type="text/javascript">
	var caRelationBundle<?php print $vs_id_prefix; ?>;
	jQuery(document).ready(function() {
<?php
	if (!(bool)$va_settings['restrictToTermsRelatedToCollection']) {
?>
		jQuery('#<?php print $vs_id_prefix; ?>caItemListSortControlTrigger').click(function() { jQuery('#<?php print $vs_id_prefix; ?>caItemListSortControls').slideToggle(200); });
		jQuery('#<?php print $vs_id_prefix; ?>caItemListSortControls a.caItemListSortControl').click(function() {jQuery('#<?php print $vs_id_prefix; ?>caItemListSortControls').slideUp(200); });
		
		caRelationBundle<?php print $vs_id_prefix; ?> = caUI.initRelationBundle('#<?php print $vs_id_prefix.$t_item->tableNum().'_rel'; ?>', {
			fieldNamePrefix: '<?php print $vs_id_prefix; ?>_',
			templateValues: ['label', 'type_id', 'id'],
			initialValues: <?php print json_encode($va_initial_values); ?>,
			itemID: '<?php print $vs_id_prefix; ?>Item_',
			templateClassName: 'caNewItemTemplate',
			initialValueTemplateClassName: 'caItemTemplate',
			itemListClassName: 'caItemList',
			addButtonClassName: 'caAddItemButton',
			deleteButtonClassName: 'caDeleteItemButton',
			showEmptyFormsOnLoad: 1,
			relationshipTypes: <?php print json_encode($this->getVar('relationship_types_by_sub_type')); ?>,
			autocompleteUrl: '<?php print caNavUrl($this->request, 'lookup', 'Vocabulary', 'Get', $va_lookup_params); ?>',
			lists: <?php print json_encode($va_settings['restrict_to_lists']); ?>,
			types: <?php print json_encode($va_settings['restrict_to_types']); ?>,
			readonly: <?php print $vb_read_only ? "true" : "false"; ?>,
			isSortable: <?php print ($vb_read_only || $vs_sort) ? "false" : "true"; ?>,
			listSortOrderID: '<?php print $vs_id_prefix; ?>BundleList',
			listSortItems: 'div.roundedRel'
		});
<?php
	} else {
?>	
		caUI.initChecklistBundle('#<?php print $vs_id_prefix.$t_item->tableNum().'_rel'; ?>', {
			fieldNamePrefix: '<?php print $vs_id_prefix; ?>_',
			templateValues: ['item_id'],
			initialValues: <?php print json_encode($va_initial_values); ?>,
			errors: <?php print json_encode($va_errors); ?>,
			itemID: '<?php print $vs_id_prefix; ?>Item_',
			templateClassName: 'caItemTemplate',
			itemListClassName: 'caItemList',
			minRepeats: <?php print ($vn_n = $this->getVar('min_num_repeats')) ? $vn_n : 0 ; ?>,
			maxRepeats: <?php print ($vn_n = $this->getVar('max_num_repeats')) ? $vn_n : 65535; ?>,
			defaultValues: <?php print json_encode($va_element_value_defaults); ?>,
			readonly: <?php print $vb_read_only ? "1" : "0"; ?>,
			defaultLocaleID: <?php print ca_locales::getDefaultCataloguingLocaleID(); ?>
		});
<?php
	} 
?>
	});
</script>

<?php
	foreach($va_initial_values as $vn_id => $va_info) {
		TooltipManager::add("#{$vs_id_prefix}_edit_related_{$vn_id}", "<h2>".$va_info['label']."</h2>");
	}
?>