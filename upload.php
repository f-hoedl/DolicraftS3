<?php
/* Copyright (C) 2026 Dolicraft <contact@dolicraft.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file upload.php
 * \ingroup dolicrafts3
 * \brief Dedicated upload page with multi-file support
 */

$res = 0;
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

dol_include_once('/dolicrafts3/class/s3client.class.php');
dol_include_once('/dolicrafts3/lib/dolicrafts3.lib.php');

$langs->loadLangs(array("dolicrafts3@dolicrafts3"));

if (!$user->hasRight('dolicrafts3', 'upload')) {
	accessforbidden();
}

$action = GETPOST('action', 'aZ09');
$s3 = DolicraftS3Client::fromDolibarrConf($db);

//
// Actions
//
if ($action == 'upload_multi') {
	$destPath = GETPOST('dest_path', 'alpha');
	$destPath = trim($destPath, '/');
	if ($destPath !== '') {
		$destPath .= '/';
	}

	$uploadCount = 0;
	$errorCount = 0;

	if (!empty($_FILES['s3files']['name'][0])) {
		$fileCount = count($_FILES['s3files']['name']);
		for ($i = 0; $i < $fileCount; $i++) {
			if ($_FILES['s3files']['error'][$i] === UPLOAD_ERR_OK) {
				$fileName = dol_sanitizeFileName($_FILES['s3files']['name'][$i]);
				$objectKey = $destPath.$fileName;
				$result = $s3->uploadFile($objectKey, $_FILES['s3files']['tmp_name'][$i]);
				if ($result) {
					$uploadCount++;
				} else {
					$errorCount++;
					setEventMessages($langs->trans("S3UploadError").' ('.$fileName.'): '.$s3->error, null, 'errors');
				}
			}
		}
	}

	if ($uploadCount > 0) {
		setEventMessages($langs->trans("S3FilesUploaded", $uploadCount), null, 'mesgs');
	}
}

//
// View
//
llxHeader('', $langs->trans("S3Upload"), '', '', 0, 0, '', '', '', 'mod-dolicrafts3 page-upload');

print load_fiche_titre($langs->trans("S3Upload"), '', 'dolicrafts3@dolicrafts3');

if (!getDolGlobalString('DOLICRAFTS3_ENDPOINT') || !getDolGlobalString('DOLICRAFTS3_BUCKET')) {
	print '<div class="warning">';
	print $langs->trans("S3NotConfigured");
	print ' <a href="'.dol_buildpath('/dolicrafts3/admin/setup.php', 1).'">'.$langs->trans("S3GoToSetup").'</a>';
	print '</div>';
	llxFooter();
	$db->close();
	exit;
}

print '<div class="fichecenter">';

print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'" enctype="multipart/form-data">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="upload_multi">';

print '<table class="noborder centpercent">';

print '<tr class="liste_titre"><td colspan="2">'.$langs->trans("S3UploadFiles").'</td></tr>';

print '<tr class="oddeven"><td class="titlefield">'.$langs->trans("S3DestinationPath").'</td><td>';
print '<input type="text" name="dest_path" class="flat minwidth300" value="" placeholder="folder/subfolder">';
print '<br><span class="opacitymedium small">'.$langs->trans("S3DestPathHelp").'</span>';
print '</td></tr>';

print '<tr class="oddeven"><td>'.$langs->trans("S3SelectFiles").'</td><td>';
print '<input type="file" name="s3files[]" class="flat" multiple>';
print '</td></tr>';

print '</table>';

print '<div class="center" style="margin-top: 15px;">';
print '<input type="submit" class="button button-upload" value="'.$langs->trans("S3Upload").'">';
print '</div>';

print '</form>';

print '</div>';

llxFooter();
$db->close();
