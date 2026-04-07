<?php
/* Copyright (C) 2026 Dolicraft <contact@dolicraft.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file index.php
 * \ingroup dolicrafts3
 * \brief S3 file browser page
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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
dol_include_once('/dolicrafts3/class/s3client.class.php');
dol_include_once('/dolicrafts3/lib/dolicrafts3.lib.php');

$langs->loadLangs(array("dolicrafts3@dolicrafts3"));

// Security check
if (!$user->hasRight('dolicrafts3', 'read')) {
	accessforbidden();
}

$action = GETPOST('action', 'aZ09');
$prefix = GETPOST('prefix', 'alpha');

// Ensure prefix ends with / if not empty
if ($prefix !== '' && $prefix !== null && substr($prefix, -1) !== '/') {
	$prefix .= '/';
}
if ($prefix === null) {
	$prefix = '';
}

//
// Actions
//
$s3 = DolicraftS3Client::fromDolibarrConf($db);

if ($action == 'delete' && $user->hasRight('dolicrafts3', 'delete')) {
	$fileToDelete = GETPOST('file_key', 'alpha');
	if ($fileToDelete) {
		$result = $s3->deleteObject($fileToDelete);
		if ($result) {
			setEventMessages($langs->trans("S3FileDeleted", $fileToDelete), null, 'mesgs');
		} else {
			setEventMessages($langs->trans("S3DeleteError").': '.$s3->error, null, 'errors');
		}
	}
}

if ($action == 'upload' && $user->hasRight('dolicrafts3', 'upload')) {
	if (!empty($_FILES['s3file']['name'])) {
		$uploadDir = $prefix;
		$fileName = dol_sanitizeFileName($_FILES['s3file']['name']);
		$objectKey = $uploadDir.$fileName;

		$result = $s3->uploadFile($objectKey, $_FILES['s3file']['tmp_name']);
		if ($result) {
			setEventMessages($langs->trans("S3FileUploaded", $fileName), null, 'mesgs');
		} else {
			setEventMessages($langs->trans("S3UploadError").': '.$s3->error, null, 'errors');
		}
	}
}

if ($action == 'download' && $user->hasRight('dolicrafts3', 'read')) {
	$fileToDownload = GETPOST('file_key', 'alpha');
	if ($fileToDownload) {
		$content = $s3->getObject($fileToDownload);
		if ($content !== false) {
			$filename = basename($fileToDownload);
			$meta = $s3->headObject($fileToDownload);
			$contentType = $meta ? $meta['content_type'] : 'application/octet-stream';

			header('Content-Type: '.$contentType);
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			header('Content-Length: '.strlen($content));
			header('Cache-Control: no-cache');
			echo $content;
			exit;
		} else {
			setEventMessages($langs->trans("S3DownloadError").': '.$s3->error, null, 'errors');
		}
	}
}

//
// View
//
llxHeader('', $langs->trans("S3Browser"), '', '', 0, 0, '', '', '', 'mod-dolicrafts3 page-index');

print load_fiche_titre($langs->trans("S3Browser"), '', 'dolicrafts3@dolicrafts3');

// Check configuration
if (!getDolGlobalString('DOLICRAFTS3_ENDPOINT') || !getDolGlobalString('DOLICRAFTS3_BUCKET')) {
	print '<div class="warning">';
	print $langs->trans("S3NotConfigured");
	print ' <a href="'.dol_buildpath('/dolicrafts3/admin/setup.php', 1).'">'.$langs->trans("S3GoToSetup").'</a>';
	print '</div>';
	llxFooter();
	$db->close();
	exit;
}

// Breadcrumb navigation
print '<div class="titre" style="margin-bottom: 10px;">';
print '<a href="'.$_SERVER['PHP_SELF'].'">'.$langs->trans("S3Root").'</a>';
if ($prefix) {
	$parts = explode('/', rtrim($prefix, '/'));
	$buildPath = '';
	foreach ($parts as $part) {
		$buildPath .= $part.'/';
		print ' / <a href="'.$_SERVER['PHP_SELF'].'?prefix='.urlencode($buildPath).'">'.$part.'</a>';
	}
}
print '</div>';

// Upload form
if ($user->hasRight('dolicrafts3', 'upload')) {
	print '<div class="fichecenter" style="margin-bottom: 15px;">';
	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?prefix='.urlencode($prefix).'" enctype="multipart/form-data">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="upload">';
	print '<input type="file" name="s3file" class="flat">';
	print ' <input type="submit" class="button button-upload small" value="'.$langs->trans("S3Upload").'">';
	print '</form>';
	print '</div>';
}

// List objects
$listing = $s3->listObjects($prefix);

if ($listing === false) {
	print '<div class="error">'.$langs->trans("S3ListError").': '.$s3->error.'</div>';
} else {
	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("Name").'</td>';
	print '<td class="right">'.$langs->trans("Size").'</td>';
	print '<td class="center">'.$langs->trans("DateModification").'</td>';
	print '<td class="center">'.$langs->trans("Actions").'</td>';
	print '</tr>';

	// Directories (common prefixes)
	if (!empty($listing['prefixes'])) {
		foreach ($listing['prefixes'] as $dir) {
			print '<tr class="oddeven">';
			print '<td>';
			print img_picto('', 'folder', 'class="pictofixedwidth"');
			$dirPath = $prefix.$dir.'/';
			// Remove double slashes
			$dirPath = preg_replace('#/+#', '/', $dirPath);
			print '<a href="'.$_SERVER['PHP_SELF'].'?prefix='.urlencode($dirPath).'">';
			print dol_escape_htmltag($dir);
			print '</a>';
			print '</td>';
			print '<td class="right">-</td>';
			print '<td class="center">-</td>';
			print '<td class="center">-</td>';
			print '</tr>';
		}
	}

	// Files
	if (!empty($listing['objects'])) {
		foreach ($listing['objects'] as $obj) {
			// Skip "directory" entries (size 0, key ends with /)
			if ($obj['size'] == 0 && substr($obj['key'], -1) === '/') {
				continue;
			}

			$displayName = basename($obj['key']);
			if ($displayName === '') {
				continue;
			}

			print '<tr class="oddeven">';
			print '<td>';
			print img_picto('', 'generic', 'class="pictofixedwidth"');
			print dol_escape_htmltag($displayName);
			print '</td>';
			print '<td class="right">'.DolicraftS3Client::formatSize($obj['size']).'</td>';
			print '<td class="center">'.dol_print_date(strtotime($obj['last_modified']), 'dayhour').'</td>';
			print '<td class="center nowraponall">';

			// Download button
			print '<a class="paddingright" href="'.$_SERVER['PHP_SELF'].'?action=download&prefix='.urlencode($prefix).'&file_key='.urlencode($obj['key']).'&token='.newToken().'" title="'.$langs->trans("Download").'">';
			print img_picto($langs->trans("Download"), 'download');
			print '</a>';

			// Delete button
			if ($user->hasRight('dolicrafts3', 'delete')) {
				print '<a class="paddingleft" href="'.$_SERVER['PHP_SELF'].'?action=delete&prefix='.urlencode($prefix).'&file_key='.urlencode($obj['key']).'&token='.newToken().'" onclick="return confirm(\''.$langs->trans("S3ConfirmDelete").'\');" title="'.$langs->trans("Delete").'">';
				print img_picto($langs->trans("Delete"), 'delete');
				print '</a>';
			}

			print '</td>';
			print '</tr>';
		}
	}

	if (empty($listing['prefixes']) && empty($listing['objects'])) {
		print '<tr class="oddeven"><td colspan="4" class="center opacitymedium">'.$langs->trans("S3EmptyFolder").'</td></tr>';
	}

	print '</table>';
}

llxFooter();
$db->close();
