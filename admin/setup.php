<?php
/* Copyright (C) 2026 Dolicraft <contact@dolicraft.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file admin/setup.php
 * \ingroup dolicrafts3
 * \brief Admin setup page for DolicraftS3 module
 */

// Load Dolibarr environment
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
dol_include_once('/dolicrafts3/class/s3client.class.php');

// Load translation files
$langs->loadLangs(array("admin", "dolicrafts3@dolicrafts3"));

// Security check
if (!$user->admin) {
	accessforbidden();
}

// Get providers list
$providers = dolicrafts3GetProviders();

//
// Actions
//
$action = GETPOST('action', 'aZ09');

if ($action == 'update') {
	$error = 0;

	$provider = GETPOST('provider', 'alpha');
	$endpoint = GETPOST('endpoint', 'alpha');
	$region = GETPOST('region', 'alpha');
	$accessKey = GETPOST('access_key', 'alpha');
	$secretKey = GETPOST('secret_key', 'none');
	$bucket = GETPOST('bucket', 'alpha');
	$pathPrefix = GETPOST('path_prefix', 'alpha');
	$usePathStyle = GETPOSTINT('use_path_style');
	$syncMode = GETPOST('sync_mode', 'alpha');

	if (empty($endpoint)) {
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesaliases("S3Endpoint")), null, 'errors');
		$error++;
	}
	if (empty($accessKey)) {
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesaliases("S3AccessKey")), null, 'errors');
		$error++;
	}
	if (empty($bucket)) {
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesaliases("S3Bucket")), null, 'errors');
		$error++;
	}

	if (!$error) {
		dolibarr_set_const($db, 'DOLICRAFTS3_PROVIDER', $provider, 'chaine', 0, '', $conf->entity);
		dolibarr_set_const($db, 'DOLICRAFTS3_ENDPOINT', $endpoint, 'chaine', 0, '', $conf->entity);
		dolibarr_set_const($db, 'DOLICRAFTS3_REGION', $region, 'chaine', 0, '', $conf->entity);
		dolibarr_set_const($db, 'DOLICRAFTS3_ACCESS_KEY', $accessKey, 'chaine', 0, '', $conf->entity);
		if ($secretKey !== '**********') {
			dolibarr_set_const($db, 'DOLICRAFTS3_SECRET_KEY', $secretKey, 'chaine', 0, '', $conf->entity);
		}
		dolibarr_set_const($db, 'DOLICRAFTS3_BUCKET', $bucket, 'chaine', 0, '', $conf->entity);
		dolibarr_set_const($db, 'DOLICRAFTS3_PATH_PREFIX', $pathPrefix, 'chaine', 0, '', $conf->entity);
		dolibarr_set_const($db, 'DOLICRAFTS3_USE_PATH_STYLE', $usePathStyle, 'chaine', 0, '', $conf->entity);
		dolibarr_set_const($db, 'DOLICRAFTS3_SYNC_MODE', $syncMode, 'chaine', 0, '', $conf->entity);

		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	}
}

if ($action == 'testconnection') {
	$s3 = DolicraftS3Client::fromDolibarrConf($db);
	$result = $s3->testConnection();

	if ($result['success']) {
		setEventMessages($langs->trans("S3ConnectionSuccess"), null, 'mesgs');
	} else {
		setEventMessages($langs->trans("S3ConnectionFailed").': '.$result['message'], null, 'errors');
	}
}

//
// View
//
$form = new Form($db);

$page_name = "DolicraftS3Setup";
llxHeader('', $langs->trans($page_name), '', '', 0, 0, '', '', '', 'mod-dolicrafts3 page-admin-setup');

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

$head = dolicrafts3AdminPrepareHead();
print dol_get_fiche_head($head, 'settings', $langs->trans($page_name), -1, 'dolicrafts3@dolicrafts3');

print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="update">';

print '<table class="noborder centpercent">';

// Provider selection
print '<tr class="liste_titre"><td colspan="2">'.$langs->trans("S3Provider").'</td></tr>';

print '<tr class="oddeven"><td class="titlefield">'.$langs->trans("S3ProviderSelect").'</td><td>';
print '<select name="provider" id="provider" class="flat minwidth300">';
foreach ($providers as $key => $prov) {
	$selected = (getDolGlobalString('DOLICRAFTS3_PROVIDER', 'custom') == $key) ? ' selected' : '';
	print '<option value="'.$key.'"'.$selected.'>'.$prov['label'].'</option>';
}
print '</select>';
print '<span class="opacitymedium small" id="provider_help" style="margin-left: 10px;"></span>';
print '</td></tr>';

// Connection settings
print '<tr class="liste_titre"><td colspan="2">'.$langs->trans("S3Connection").'</td></tr>';

print '<tr class="oddeven"><td class="titlefield fieldrequired">'.$langs->trans("S3Endpoint").'</td><td>';
print '<input type="text" name="endpoint" id="endpoint" class="flat minwidth400" value="'.dol_escape_htmltag(getDolGlobalString('DOLICRAFTS3_ENDPOINT')).'" placeholder="https://s3.eu-west-3.amazonaws.com">';
print '</td></tr>';

print '<tr class="oddeven"><td>'.$langs->trans("S3Region").'</td><td>';
print '<input type="text" name="region" id="region" class="flat minwidth200" value="'.dol_escape_htmltag(getDolGlobalString('DOLICRAFTS3_REGION', 'us-east-1')).'" placeholder="us-east-1">';
print '</td></tr>';

print '<tr class="oddeven"><td class="fieldrequired">'.$langs->trans("S3AccessKey").'</td><td>';
print '<input type="text" name="access_key" class="flat minwidth300" value="'.dol_escape_htmltag(getDolGlobalString('DOLICRAFTS3_ACCESS_KEY')).'" autocomplete="off">';
print '</td></tr>';

print '<tr class="oddeven"><td class="fieldrequired">'.$langs->trans("S3SecretKey").'</td><td>';
$secretDisplay = getDolGlobalString('DOLICRAFTS3_SECRET_KEY') ? '**********' : '';
print '<input type="password" name="secret_key" class="flat minwidth300" value="'.dol_escape_htmltag($secretDisplay).'" autocomplete="new-password">';
print '</td></tr>';

// Bucket settings
print '<tr class="liste_titre"><td colspan="2">'.$langs->trans("S3BucketSettings").'</td></tr>';

print '<tr class="oddeven"><td class="fieldrequired">'.$langs->trans("S3Bucket").'</td><td>';
print '<input type="text" name="bucket" class="flat minwidth300" value="'.dol_escape_htmltag(getDolGlobalString('DOLICRAFTS3_BUCKET')).'" placeholder="my-dolibarr-bucket">';
print '</td></tr>';

print '<tr class="oddeven"><td>'.$langs->trans("S3PathPrefix").'</td><td>';
print '<input type="text" name="path_prefix" class="flat minwidth300" value="'.dol_escape_htmltag(getDolGlobalString('DOLICRAFTS3_PATH_PREFIX')).'" placeholder="dolibarr/documents">';
print '<br><span class="opacitymedium small">'.$langs->trans("S3PathPrefixHelp").'</span>';
print '</td></tr>';

// Advanced settings
print '<tr class="liste_titre"><td colspan="2">'.$langs->trans("S3Advanced").'</td></tr>';

print '<tr class="oddeven"><td>'.$langs->trans("S3UsePathStyle").'</td><td>';
print $form->selectyesno('use_path_style', getDolGlobalInt('DOLICRAFTS3_USE_PATH_STYLE'), 1);
print '<br><span class="opacitymedium small">'.$langs->trans("S3UsePathStyleHelp").'</span>';
print '</td></tr>';

print '<tr class="oddeven"><td>'.$langs->trans("S3SyncMode").'</td><td>';
$syncModes = array(
	'manual'      => $langs->trans("S3SyncManual"),
	'auto_upload' => $langs->trans("S3SyncAutoUpload"),
	'full_sync'   => $langs->trans("S3SyncFullSync"),
);
print $form->selectarray('sync_mode', $syncModes, getDolGlobalString('DOLICRAFTS3_SYNC_MODE', 'manual'), 0, 0, 0, '', 0, 0, 0, '', 'minwidth200');
print '<br><span class="opacitymedium small">'.$langs->trans("S3SyncModeHelp").'</span>';
print '</td></tr>';

print '</table>';

print '<div class="center" style="margin-top: 15px;">';
print '<input type="submit" class="button button-save" value="'.$langs->trans("Save").'">';
print '</div>';

print '</form>';

// Test connection button
print '<div class="center" style="margin-top: 10px;">';
print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'" style="display:inline;">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="testconnection">';
print '<input type="submit" class="button" value="'.$langs->trans("S3TestConnection").'">';
print '</form>';
print '</div>';

print dol_get_fiche_end();

// JavaScript for dynamic provider settings
print '<script type="text/javascript">
var providers = '.json_encode($providers).';

$(document).ready(function() {
	$("#provider").change(function() {
		var p = providers[$(this).val()];
		if (p) {
			if (p.endpoint && !$("#endpoint").val()) {
				$("#endpoint").val(p.endpoint);
			}
			if (p.region) {
				$("#region").val(p.region);
			}
			$("select[name=use_path_style]").val(p.use_path_style);
			$("#provider_help").text(p.help);
		}
	}).trigger("change");
});
</script>';

llxFooter();
$db->close();
