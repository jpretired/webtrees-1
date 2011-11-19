<?php
// Module Administration User Interface.
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
// $Id$

define('WT_SCRIPT_NAME', 'admin_module_reports.php');
require 'includes/session.php';

$controller=new WT_Controller_Base();
$controller
	->requireAdminLogin()
	->setPageTitle(WT_I18N::translate('Module administration'));

require WT_ROOT.'includes/functions/functions_edit.php';

// New modules may have been added...
$installed_modules=WT_Module::getInstalledModules();
foreach ($installed_modules as $module_name=>$module) {
	// New module
	WT_DB::prepare("INSERT IGNORE INTO `##module` (module_name) VALUES (?)")->execute(array($module_name));
}

// Disable modules that no longer exist.  Don't delete the config.  The module
// may have only been removed temporarily, e.g. during an upgrade / migration
$module_names=WT_DB::prepare("SELECT module_name FROM `##module` WHERE status='enabled'")->fetchOneColumn();
foreach ($module_names as $module_name) {
	if (!array_key_exists($module_name, $installed_modules)) {
		WT_DB::prepare(
			"UPDATE `##module` SET status='disabled' WHERE module_name=?"
		)->execute(array($module_name));
	}
}

$action = safe_POST('action');

if ($action=='update_mods') {
	foreach (WT_Module::getInstalledModules() as $module) {
		$module_name=$module->getName();
		foreach (get_all_gedcoms() as $ged_id=>$ged_name) {
			if ($module instanceof WT_Module_Report) {
				$value = safe_POST("reportaccess-{$module_name}-{$ged_id}", WT_REGEX_INTEGER, $module->defaultAccessLevel());
				WT_DB::prepare(
					"REPLACE INTO `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, 'report', ?)"
				)->execute(array($module_name, $ged_id, $value));
			}
		}
	}
}

$controller->pageHeader();
?>

<div align="center">
	<div id="tabs">
		<form method="post" action="<?php echo WT_SCRIPT_NAME; ?>">
			<input type="hidden" name="action" value="update_mods" />
			<table id="reports_table" class="modules_table">
				<thead>
					<tr>
					<th><?php echo WT_I18N::translate('Report'); ?></th>
					<th><?php echo WT_I18N::translate('Description'); ?></th>
					<th><?php echo WT_I18N::translate('Access level'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$order = 1;
					foreach (WT_Module::getInstalledReports() as $module) { 
						if (array_key_exists($module->getName(), $module->getActiveModules())) {
							echo '<tr>';
						} else {
							echo '<tr class="rela">';
						}
						?>
							<td><?php echo $module->getTitle(); ?></td>
							<td><?php echo $module->getDescription(); ?></td>
							<td>
								<table class="modules_table2">
									<?php
									foreach (get_all_gedcoms() as $ged_id=>$ged_name) {
										$varname = 'reportaccess-'.$module->getName().'-'.$ged_id;
										$access_level=WT_DB::prepare(
											"SELECT access_level FROM `##module_privacy` WHERE gedcom_id=? AND module_name=? AND component='report'"
										)->execute(array($ged_id, $module->getName()))->fetchOne();
										if ($access_level===null) {
											$access_level=$module->defaultAccessLevel();
										}
										echo '<tr><td>',  WT_I18N::translate('%s', get_gedcom_setting($ged_id, 'title')), '</td><td>';
										echo edit_field_access_level($varname, $access_level);
									}
									?>
								</table>
							</td>
						</tr>
						<?php
						$order++;
						}
						?>
				</tbody>
			</table>
			<input type="submit" value="<?php echo WT_I18N::translate('Save'); ?>" />
		</form>
	</div>
</div>
