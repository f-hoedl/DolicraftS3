<?php
/* Copyright (C) 2026 Dolicraft <contact@dolicraft.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file admin/documentation.php
 * \ingroup dolicrafts3
 * \brief Documentation page for DolicraftS3 module
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

llxHeader('', $langs->trans("DocTitle"), '', '', 0, 0, '', '', '', 'mod-dolicrafts3 page-admin-documentation');

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans("DocTitle"), $linkback, 'title_setup');

$head = dolicrafts3AdminPrepareHead();
print dol_get_fiche_head($head, 'documentation', $langs->trans("DocTitle"), -1, 'dolicrafts3@dolicrafts3');

print '<div class="fichecenter">';

// ========================================================================
// 1. Introduction
// ========================================================================
print '<div class="underbanner clearboth"></div>';
print '<div class="titre">'.$langs->trans("DocIntroTitle").'</div>';
print '<div class="opacitymedium" style="margin-bottom:15px;">'.$langs->trans("DocIntroText").'</div>';

// Feature list
print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><td>'.$langs->trans("DocFeatures").'</td><td>'.$langs->trans("Description").'</td></tr>';

$features = array(
	'DocFeatureMultiCloud',
	'DocFeatureNativePHP',
	'DocFeatureBrowser',
	'DocFeatureUpload',
	'DocFeaturePresigned',
	'DocFeatureSync',
	'DocFeaturePermissions',
	'DocFeatureLogs',
	'DocFeatureMultilang',
);
foreach ($features as $i => $feat) {
	print '<tr class="oddeven">';
	print '<td><strong>'.$langs->trans($feat).'</strong></td>';
	print '<td>'.$langs->trans($feat.'Desc').'</td>';
	print '</tr>';
}
print '</table>';

print '<br>';

// ========================================================================
// 2. Installation
// ========================================================================
print '<div class="titre">'.$langs->trans("DocInstallTitle").'</div>';
print '<div class="opacitymedium" style="margin-bottom:10px;">'.$langs->trans("DocInstallText").'</div>';

print '<div class="info" style="padding:12px; margin-bottom:15px;">';
print '<strong>'.$langs->trans("DocInstallStep1").'</strong><br>';
print $langs->trans("DocInstallStep1Text").'<br><br>';
print '<strong>'.$langs->trans("DocInstallStep2").'</strong><br>';
print $langs->trans("DocInstallStep2Text").'<br><br>';
print '<strong>'.$langs->trans("DocInstallStep3").'</strong><br>';
print $langs->trans("DocInstallStep3Text");
print '</div>';

print '<br>';

// ========================================================================
// 3. Configuration
// ========================================================================
print '<div class="titre">'.$langs->trans("DocConfigTitle").'</div>';
print '<div class="opacitymedium" style="margin-bottom:10px;">'.$langs->trans("DocConfigText").'</div>';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameter").'</td>';
print '<td>'.$langs->trans("Description").'</td>';
print '<td>'.$langs->trans("DocRequired").'</td>';
print '</tr>';

$params = array(
	array('S3ProviderSelect', 'DocParamProviderDesc', $langs->trans("No")),
	array('S3Endpoint',       'DocParamEndpointDesc', '<strong>'.$langs->trans("Yes").'</strong>'),
	array('S3Region',         'DocParamRegionDesc',   $langs->trans("No")),
	array('S3AccessKey',      'DocParamAccessKeyDesc', '<strong>'.$langs->trans("Yes").'</strong>'),
	array('S3SecretKey',      'DocParamSecretKeyDesc', '<strong>'.$langs->trans("Yes").'</strong>'),
	array('S3Bucket',         'DocParamBucketDesc',   '<strong>'.$langs->trans("Yes").'</strong>'),
	array('S3PathPrefix',     'DocParamPrefixDesc',   $langs->trans("No")),
	array('S3UsePathStyle',   'DocParamPathStyleDesc', $langs->trans("No")),
	array('S3SyncMode',       'DocParamSyncModeDesc', $langs->trans("No")),
);

foreach ($params as $p) {
	print '<tr class="oddeven">';
	print '<td><strong>'.$langs->trans($p[0]).'</strong></td>';
	print '<td>'.$langs->trans($p[1]).'</td>';
	print '<td class="center">'.$p[2].'</td>';
	print '</tr>';
}
print '</table>';

print '<br>';

// ========================================================================
// 4. Provider-specific guides
// ========================================================================
print '<div class="titre">'.$langs->trans("DocProvidersTitle").'</div>';
print '<div class="opacitymedium" style="margin-bottom:10px;">'.$langs->trans("DocProvidersText").'</div>';

$providerGuides = array(
	'aws'          => array('DocProviderAWS', 'DocProviderAWSSteps'),
	'ovh'          => array('DocProviderOVH', 'DocProviderOVHSteps'),
	'scaleway'     => array('DocProviderScaleway', 'DocProviderScalewaySteps'),
	'wasabi'       => array('DocProviderWasabi', 'DocProviderWasabiSteps'),
	'minio'        => array('DocProviderMinIO', 'DocProviderMinIOSteps'),
	'digitalocean' => array('DocProviderDO', 'DocProviderDOSteps'),
	'backblaze'    => array('DocProviderB2', 'DocProviderB2Steps'),
	'cloudflare'   => array('DocProviderR2', 'DocProviderR2Steps'),
);

foreach ($providerGuides as $key => $guide) {
	print '<div style="margin-bottom:15px; padding:10px; border:1px solid #ddd; border-radius:6px;">';
	print '<strong style="font-size:1.1em;">'.$langs->trans($guide[0]).'</strong><br>';
	print '<div style="margin-top:8px; white-space:pre-line;">'.$langs->trans($guide[1]).'</div>';
	print '</div>';
}

print '<br>';

// ========================================================================
// 5. Usage - File Browser
// ========================================================================
print '<div class="titre">'.$langs->trans("DocUsageBrowserTitle").'</div>';
print '<div class="opacitymedium" style="margin-bottom:10px;">'.$langs->trans("DocUsageBrowserText").'</div>';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><td>'.$langs->trans("Action").'</td><td>'.$langs->trans("DocHowTo").'</td></tr>';
print '<tr class="oddeven"><td><strong>'.$langs->trans("DocBrowseFolders").'</strong></td><td>'.$langs->trans("DocBrowseFoldersHow").'</td></tr>';
print '<tr class="oddeven"><td><strong>'.$langs->trans("DocUploadFile").'</strong></td><td>'.$langs->trans("DocUploadFileHow").'</td></tr>';
print '<tr class="oddeven"><td><strong>'.$langs->trans("DocDownloadFile").'</strong></td><td>'.$langs->trans("DocDownloadFileHow").'</td></tr>';
print '<tr class="oddeven"><td><strong>'.$langs->trans("DocDeleteFile").'</strong></td><td>'.$langs->trans("DocDeleteFileHow").'</td></tr>';
print '<tr class="oddeven"><td><strong>'.$langs->trans("DocMultiUpload").'</strong></td><td>'.$langs->trans("DocMultiUploadHow").'</td></tr>';
print '</table>';

print '<br>';

// ========================================================================
// 6. Sync modes
// ========================================================================
print '<div class="titre">'.$langs->trans("DocSyncTitle").'</div>';
print '<div class="opacitymedium" style="margin-bottom:10px;">'.$langs->trans("DocSyncText").'</div>';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><td>'.$langs->trans("Mode").'</td><td>'.$langs->trans("DocBehavior").'</td><td>'.$langs->trans("DocRecommendedFor").'</td></tr>';
print '<tr class="oddeven"><td><strong>'.$langs->trans("S3SyncManual").'</strong></td><td>'.$langs->trans("DocSyncManualDesc").'</td><td>'.$langs->trans("DocSyncManualUse").'</td></tr>';
print '<tr class="oddeven"><td><strong>'.$langs->trans("S3SyncAutoUpload").'</strong></td><td>'.$langs->trans("DocSyncAutoDesc").'</td><td>'.$langs->trans("DocSyncAutoUse").'</td></tr>';
print '<tr class="oddeven"><td><strong>'.$langs->trans("S3SyncFullSync").'</strong></td><td>'.$langs->trans("DocSyncFullDesc").'</td><td>'.$langs->trans("DocSyncFullUse").'</td></tr>';
print '</table>';

print '<br>';

// ========================================================================
// 7. Permissions
// ========================================================================
print '<div class="titre">'.$langs->trans("DocPermissionsTitle").'</div>';
print '<div class="opacitymedium" style="margin-bottom:10px;">'.$langs->trans("DocPermissionsText").'</div>';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><td>'.$langs->trans("Permission").'</td><td>'.$langs->trans("DocGrantsAccess").'</td></tr>';
print '<tr class="oddeven"><td><strong>'.$langs->trans("Permission500101").'</strong></td><td>'.$langs->trans("DocPermReadDesc").'</td></tr>';
print '<tr class="oddeven"><td><strong>'.$langs->trans("Permission500102").'</strong></td><td>'.$langs->trans("DocPermUploadDesc").'</td></tr>';
print '<tr class="oddeven"><td><strong>'.$langs->trans("Permission500103").'</strong></td><td>'.$langs->trans("DocPermDeleteDesc").'</td></tr>';
print '<tr class="oddeven"><td><strong>'.$langs->trans("Permission500104").'</strong></td><td>'.$langs->trans("DocPermConfigDesc").'</td></tr>';
print '</table>';

print '<br>';

// ========================================================================
// 8. Technical architecture
// ========================================================================
print '<div class="titre">'.$langs->trans("DocTechTitle").'</div>';
print '<div class="opacitymedium" style="margin-bottom:10px;">'.$langs->trans("DocTechText").'</div>';

print '<div class="info" style="padding:12px; margin-bottom:15px; font-family:monospace; font-size:0.9em; white-space:pre;">';
print 'dolicrafts3/
';
print '  admin/
';
print '    setup.php              '.$langs->trans("DocTreeSetup").'
';
print '    documentation.php      '.$langs->trans("DocTreeDoc").'
';
print '    about.php              '.$langs->trans("DocTreeAbout").'
';
print '  class/
';
print '    s3client.class.php     '.$langs->trans("DocTreeClient").'
';
print '  core/modules/
';
print '    modDolicraftS3.class.php  '.$langs->trans("DocTreeDescriptor").'
';
print '  css/                     '.$langs->trans("DocTreeCSS").'
';
print '  img/                     '.$langs->trans("DocTreeImg").'
';
print '  langs/{en,fr,es,de,it,pt}/  '.$langs->trans("DocTreeLangs").'
';
print '  lib/
';
print '    dolicrafts3.lib.php    '.$langs->trans("DocTreeLib").'
';
print '  sql/                     '.$langs->trans("DocTreeSQL").'
';
print '  index.php                '.$langs->trans("DocTreeBrowser").'
';
print '  upload.php               '.$langs->trans("DocTreeUpload").'
';
print '</div>';

print '<br>';

// ========================================================================
// 9. FAQ / Troubleshooting
// ========================================================================
print '<div class="titre">'.$langs->trans("DocFAQTitle").'</div>';

$faqs = array(
	array('DocFAQ1Q', 'DocFAQ1A'),
	array('DocFAQ2Q', 'DocFAQ2A'),
	array('DocFAQ3Q', 'DocFAQ3A'),
	array('DocFAQ4Q', 'DocFAQ4A'),
	array('DocFAQ5Q', 'DocFAQ5A'),
);

foreach ($faqs as $i => $faq) {
	print '<div style="margin-bottom:12px; padding:10px; border-left:3px solid #4A90D9; background:#f8f9fa;">';
	print '<strong>'.$langs->trans($faq[0]).'</strong><br>';
	print '<span class="opacitymedium">'.$langs->trans($faq[1]).'</span>';
	print '</div>';
}

print '<br>';

// ========================================================================
// 10. Support
// ========================================================================
print '<div class="titre">'.$langs->trans("DocSupportTitle").'</div>';
print '<div style="padding:15px; border:1px solid #357ABD; border-radius:6px; background:#f0f7ff;">';
print $langs->trans("DocSupportText");
print '<br><br>';
print '<strong>'.$langs->trans("EMail").':</strong> <a href="mailto:contact@dolicraft.com">contact@dolicraft.com</a><br>';
print '<strong>'.$langs->trans("Website").':</strong> <a href="https://dolicraft.com" target="_blank" rel="noopener noreferrer">https://dolicraft.com</a>';
print '</div>';

print '</div>'; // fichecenter

print dol_get_fiche_end();

llxFooter();
$db->close();
