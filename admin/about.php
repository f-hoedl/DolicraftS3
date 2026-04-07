<?php
/* Copyright (C) 2026 Dolicraft <contact@dolicraft.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file admin/about.php
 * \ingroup dolicrafts3
 * \brief About page for DolicraftS3 module
 */

$res = 0;
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
dol_include_once('/dolicrafts3/lib/dolicrafts3.lib.php');

$langs->loadLangs(array("admin", "dolicrafts3@dolicrafts3"));

if (!$user->admin) {
	accessforbidden();
}

llxHeader('', $langs->trans("DolicraftS3About"), '', '', 0, 0, '', '', '', 'mod-dolicrafts3 page-admin-about');

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans("DolicraftS3About"), $linkback, 'title_setup');

$head = dolicrafts3AdminPrepareHead();
print dol_get_fiche_head($head, 'about', $langs->trans("DolicraftS3About"), -1, 'dolicrafts3@dolicrafts3');

// --- Dolicraft branding section ---
print '<div class="fichecenter">';

print '<div class="underbanner clearboth"></div>';

print '<table class="border centpercent tableforfield">';
print '<tr class="liste_titre"><td colspan="2">'.$langs->trans("AboutModule").'</td></tr>';
print '<tr><td class="titlefield">'.$langs->trans("ModuleName").'</td><td><strong>DolicraftS3</strong></td></tr>';
print '<tr><td>'.$langs->trans("Version").'</td><td>1.0.0</td></tr>';
print '<tr><td>'.$langs->trans("Description").'</td><td>'.$langs->trans("DolicraftS3Desc").'</td></tr>';
print '<tr><td>'.$langs->trans("License").'</td><td>GPLv3+</td></tr>';
print '</table>';

print '<br>';

// --- Dolicraft company info ---
print '<table class="border centpercent tableforfield">';
print '<tr class="liste_titre"><td colspan="2">'.$langs->trans("AboutDolicraft").'</td></tr>';
print '<tr><td class="titlefield">'.$langs->trans("Publisher").'</td><td><strong>Dolicraft</strong></td></tr>';
print '<tr><td>'.$langs->trans("AboutDolicraftDesc").'</td><td>'.$langs->trans("AboutDolicraftDescValue").'</td></tr>';
print '<tr><td>'.$langs->trans("Website").'</td><td><a href="https://dolicraft.com" target="_blank" rel="noopener noreferrer">https://dolicraft.com</a></td></tr>';
print '<tr><td>'.$langs->trans("EMail").'</td><td><a href="mailto:contact@dolicraft.com">contact@dolicraft.com</a></td></tr>';
print '<tr><td>'.$langs->trans("AboutServices").'</td><td>'.$langs->trans("AboutServicesValue").'</td></tr>';
print '</table>';

print '<br>';

// --- Supported providers ---
print '<div class="titre">'.$langs->trans("S3SupportedProviders").'</div>';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Provider").'</td>';
print '<td>'.$langs->trans("DefaultEndpoint").'</td>';
print '<td>'.$langs->trans("Notes").'</td>';
print '</tr>';

$providers = dolicrafts3GetProviders();
foreach ($providers as $key => $prov) {
	print '<tr class="oddeven">';
	print '<td><strong>'.dol_escape_htmltag($prov['label']).'</strong></td>';
	print '<td><code>'.dol_escape_htmltag($prov['endpoint']).'</code></td>';
	print '<td>'.dol_escape_htmltag($prov['help']).'</td>';
	print '</tr>';
}
print '</table>';

print '</div>';

print dol_get_fiche_end();

llxFooter();
$db->close();
