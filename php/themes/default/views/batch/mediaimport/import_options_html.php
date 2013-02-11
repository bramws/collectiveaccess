<?php
/* ----------------------------------------------------------------------
 * views/editor/objects/import_options_html.php : 
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2012-2013 Whirl-i-Gig
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
 
 	$t_object = $this->getVar('t_object');
 	$t_rep = $this->getVar('t_rep');
 	
 	$va_last_settings = $this->getVar('batch_mediaimport_last_settings');
 
	print $vs_control_box = caFormControlBox(
		caJSButton($this->request, __CA_NAV_BUTTON_SAVE__, _t("Execute media import"), 'caBatchMediaImportForm', array('onclick' => 'caShowConfirmBatchExecutionPanel(); return false;')).' '.
		caNavButton($this->request, __CA_NAV_BUTTON_CANCEL__, _t("Cancel"), 'batch', 'MediaImport', 'Index/'.$this->request->getActionExtra(), array()),
		'', 
		''
	);
?>
	<div class="sectionBox">
<?php
		print caFormTag($this->request, 'Save/'.$this->request->getActionExtra(), 'caBatchMediaImportForm', null, 'POST', 'multipart/form-data', '_top', array('disableUnsavedChangesWarning' => true, 'noTimestamp' => true));
?>
		<div class='bundleLabel'>
			<span class="formLabelText"><?php print _t('Directory to import'); ?></span> 
			<div class="bundleContainer">
				<div class="caLabelList" >
						<!--- begin directoryBrowser --->
				<div id="directoryBrowser" class='directoryBrowser'>
					<!-- Content for directory browser is dynamically inserted here by ca.hierbrowser -->
				</div><!-- end directoryBrowser -->
<script type="text/javascript">
	var oDirBrowser;
	jQuery(document).ready(function() {
		oDirBrowser = caUI.initDirectoryBrowser('directoryBrowser', {
			levelDataUrl: '<?php print caNavUrl($this->request, 'batch', 'MediaImport', 'GetDirectoryLevel'); ?>',
			initDataUrl: '<?php print caNavUrl($this->request, 'batch', 'MediaImport', 'GetDirectoryAncestorList'); ?>',
			
			openDirectoryIcon: '<img src="<?php print $this->request->getThemeUrlPath(); ?>/graphics/buttons/arrow_grey_right.gif" border="0" title="Edit"/>',
			
			folderIcon: '<img src="<?php print $this->request->getThemeUrlPath(); ?>/graphics/icons/folder_small.png" border="0" title="Folder" style="margin-right: 7px;"/>',
			fileIcon: '<img src="<?php print $this->request->getThemeUrlPath(); ?>/graphics/icons/file_small.png" border="0" title="File" style="margin-right: 7px;"/>',
			
			displayFiles: true,
			allowFileSelection: false,
			
			initItemID: '<?php print $va_last_settings['importFromDirectory']; ?>',
			indicatorUrl: '<?php print $this->request->getThemeUrlPath(); ?>/graphics/icons/indicator.gif',
			
			currentSelectionDisplayID: 'browseCurrentSelection',
			
			onSelection: function(item_id, path, name, type) {
				if (type == 'DIR') { jQuery('#caDirectoryValue').val(path); }
			}
		});
	});
</script>
<?php
		print caHTMLHiddenInput('directory', array('value' => '', 'id' => 'caDirectoryValue'));		
?>	
				</div>
				<div style="margin: 8px 0 5px 0;">
<?php 
				$va_opts = array('id' => 'caIncludeSubDirectories', 'value' => 1);
				if (isset($va_last_settings['includeSubDirectories']) && $va_last_settings['includeSubDirectories']) {
					$va_opts['checked'] = 1;
				} 
				print caHTMLCheckboxInput('include_subdirectories', $va_opts).' '._t('Include all sub-directories'); 
?>	
				</div>
			</div>
		</div>
		<div class='bundleLabel'>
			<span class="formLabelText"><?php print _t('Import mode'); ?></span> 
				<div class="bundleContainer">
					<div class="caLabelList" >
						<p>
<?php
			print $this->getVar('import_mode');
?>	
						</p>
					</div>
				</div>
		</div>
		<div class='bundleLabel'>
			<span class="formLabelText"><?php print _t('Type'); ?></span> 
				<div class="bundleContainer">
					<div class="caLabelList">
						<p>
							<table style="width: 100%;">
								<tr>
									<td class='formLabel'>
<?php
										print _t('Type used for newly created objects')."<br/>\n".$this->getVar('ca_objects_type_list')."\n";
?>	
									</td>
									<td class='formLabel'>
<?php
										print _t('Type used for newly created object representations')."<br/>\n".$this->getVar('ca_object_representations_type_list')."</div>\n";
?>				
									</td>
								</tr>
							</table>
						<p>
					</div>
				</div>
		</div>
		<div class='bundleLabel'>
			<span class="formLabelText"><?php print _t('Set'); ?></span> 
			<div class="bundleContainer">
				<div class="caLabelList" id="caMediaImportSetControls">
					<p>
						<table>
							<tr>
								<td><?php 
									$va_attrs = array('value' => 'add', 'checked' => 1, 'id' => 'caAddToSet');
									if (isset($va_last_settings['setMode']) && ($va_last_settings['setMode'] == 'add')) { $va_attrs['checked'] = 1; }
									print caHTMLRadioButtonInput('set_mode', $va_attrs); 
								?></td>
								<td class='formLabel'><?php print _t('Add imported media to set %1', caHTMLSelect('set_id', $this->getVar('available_sets'), array('id' => 'caAddToSetID', 'class' => 'searchSetsSelect', 'width' => '300px'), array('value' => null, 'width' => '170px'))); ?></td>
							</tr>
							<tr>
								<td><?php 
									$va_attrs = array('value' => 'create', 'id' => 'caCreateSet');
									if (isset($va_last_settings['setMode']) && ($va_last_settings['setMode'] == 'create')) { $va_attrs['checked'] = 1; }
									print caHTMLRadioButtonInput('set_mode', $va_attrs); 
								?></td>
								<td class='formLabel'><?php print _t('Create set %1 with imported media', caHTMLTextInput('set_create_name', array('value' => '', 'width' => '200px', 'id' => 'caSetCreateName'))); ?></td>
							</tr>
							<tr>
								<td><?php 
									$va_attrs = array('value' => 'none', 'id' => 'caNoSet');
									if (isset($va_last_settings['setMode']) && ($va_last_settings['setMode'] == 'none')) { $va_attrs['checked'] = 1; }
									print caHTMLRadioButtonInput('set_mode', $va_attrs); 
								?></td>
								<td class='formLabel'><?php print _t('Do not associate imported media with a set'); ?></td>
							</tr>
						</table>
						<script type="text/javascript">
							jQuery(document).ready(function() {
								jQuery("#caAddToSet").click(function() {
									jQuery("#caAddToSetID").prop('disabled', false);
									jQuery("#caSetCreateName").prop('disabled', true);
								});
								jQuery("#caCreateSet").click(function() {
									jQuery("#caAddToSetID").prop('disabled', true);
									jQuery("#caSetCreateName").prop('disabled', false);
								});
								jQuery("#caNoSet").click(function() {
									jQuery("#caAddToSetID").prop('disabled', true);
									jQuery("#caSetCreateName").prop('disabled', true);
								});
								
								jQuery("#caMediaImportSetControls").find("input:checked").click();
							});
							
						</script>
					</p>
				</div>
			</div>
		</div>
		<div class='bundleLabel'>
			<span class="formLabelText"><?php print _t('Identifier'); ?></span> 
			<div class="bundleContainer">
				<div class="caLabelList" id="caMediaImportIdnoControls">
					<p>
						<table>
							<tr>
								<td><?php 
									$va_attrs = array('value' => 'form', 'id' => 'caIdnoFormMode');
									if (isset($va_last_settings['idnoMode']) && ($va_last_settings['idnoMode'] == 'form')) { $va_attrs['checked'] = 1; }
									print caHTMLRadioButtonInput('idno_mode', $va_attrs); 
								?></td>
								<td class='formLabel' id='caIdnoFormModeForm'><?php print _t('Set object identifier to %1', $t_object->htmlFormElement('idno', '^ELEMENT', array('request' => $this->request))); ?></td>
							</tr>
							<tr>
								<td><?php 
									$va_attrs = array('value' => 'filename', 'id' => 'caIdnoFilenameMode');
									if (isset($va_last_settings['idnoMode']) && ($va_last_settings['idnoMode'] == 'filename')) { $va_attrs['checked'] = 1; }
									print caHTMLRadioButtonInput('idno_mode', $va_attrs); 
								?></td>
								<td class='formLabel'><?php print _t('Set object identifier to file name'); ?></td>
							</tr>
						</table>
						<script type="text/javascript">
							jQuery(document).ready(function() {
								jQuery("#caIdnoFormMode").click(function() {
									jQuery("#caIdnoFormModeForm input").prop('disabled', false);
								});
								jQuery("#caIdnoFilenameMode").click(function() {
									jQuery("#caIdnoFormModeForm input").prop('disabled', true);
								});
								
								jQuery("#caMediaImportIdnoControls").find("input:checked").click();
							});
							
						</script>
					</p>
				</div>
			</div>
		</div>
		<div class='bundleLabel'>
			<span class="formLabelText"><?php print _t('Status &amp; access'); ?></span> 
				<div class="bundleContainer">
					<div class="caLabelList" >
						<p>
							<table style="width: 100%;">
								<tr>
									<td class='formLabel'>
<?php 
											print _t('Set object status to<br/>%1', $t_object->htmlFormElement('status', '', array('name' => 'ca_objects_status')));
											print "<br/>";
											print _t('Set object access to<br/>%1', $t_object->htmlFormElement('access', '', array('name' => 'ca_objects_access')));
?>									
									</td>
									<td class='formLabel'>
<?php 
											print _t('Set representation status to<br/>%1', $t_rep->htmlFormElement('status', '', array('name' => 'ca_object_representations_status')));
											print "<br/>";
											print _t('Set representation access to<br/>%1', $t_rep->htmlFormElement('access', '', array('name' => 'ca_object_representations_access')));
?>									
									</td>								
								</tr>
							</table>
						</p>
					</div>
				</div>
		</div>
			
<?php
			print $this->render("mediaimport/confirm_html.php");
			
			print $vs_control_box; 
?>
		</form>
	</div>

	<div class="editorBottomPadding"><!-- empty --></div>
	
	<script type="text/javascript">
		function caShowConfirmBatchExecutionPanel() {
			var msg = '<?php print addslashes(_t("You are about to import files from <em>%1</em>")); ?>';
			msg = msg.replace("%1", jQuery('#caDirectoryValue').val());
			caConfirmBatchExecutionPanel.showPanel();
			jQuery('#caConfirmBatchExecutionPanelAlertText').html(msg);
		}
	</script>