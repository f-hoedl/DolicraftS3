<?php
/* Copyright (C) 2026 Dolicraft <contact@dolicraft.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file lib/dolicrafts3.lib.php
 * \ingroup dolicrafts3
 * \brief Library for DolicraftS3 module
 */

/**
 * Prepare admin pages header
 *
 * @return array Array of tabs
 */
function dolicrafts3AdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("dolicrafts3@dolicrafts3");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/dolicrafts3/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;

	$head[$h][0] = dol_buildpath("/dolicrafts3/admin/documentation.php", 1);
	$head[$h][1] = $langs->trans("Documentation");
	$head[$h][2] = 'documentation';
	$h++;

	$head[$h][0] = dol_buildpath("/dolicrafts3/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	complete_head_from_modules($conf, $langs, null, $head, $h, 'dolicrafts3@dolicrafts3');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'dolicrafts3@dolicrafts3', 'remove');

	return $head;
}

/**
 * Get list of supported S3 providers with their default configurations
 *
 * @return array Array of providers
 */
function dolicrafts3GetProviders()
{
	return array(
		'custom' => array(
			'label'          => 'Custom S3-Compatible',
			'endpoint'       => '',
			'region'         => 'us-east-1',
			'use_path_style' => 0,
			'help'           => 'Any S3-compatible storage provider',
		),
		'aws' => array(
			'label'          => 'Amazon Web Services (AWS S3)',
			'endpoint'       => 'https://s3.{region}.amazonaws.com',
			'region'         => 'eu-west-3',
			'use_path_style' => 0,
			'help'           => 'Standard AWS S3 service',
		),
		'ovh' => array(
			'label'          => 'OVHcloud Object Storage',
			'endpoint'       => 'https://s3.{region}.cloud.ovh.net',
			'region'         => 'gra',
			'use_path_style' => 0,
			'help'           => 'OVHcloud S3-compatible Object Storage (High Performance)',
		),
		'scaleway' => array(
			'label'          => 'Scaleway Object Storage',
			'endpoint'       => 'https://s3.{region}.scw.cloud',
			'region'         => 'fr-par',
			'use_path_style' => 0,
			'help'           => 'Scaleway S3-compatible Object Storage',
		),
		'wasabi' => array(
			'label'          => 'Wasabi Hot Cloud Storage',
			'endpoint'       => 'https://s3.{region}.wasabisys.com',
			'region'         => 'eu-central-1',
			'use_path_style' => 0,
			'help'           => 'Wasabi Hot Cloud Storage - no egress fees',
		),
		'minio' => array(
			'label'          => 'MinIO (Self-Hosted)',
			'endpoint'       => 'http://localhost:9000',
			'region'         => 'us-east-1',
			'use_path_style' => 1,
			'help'           => 'Self-hosted MinIO server - requires path-style access',
		),
		'digitalocean' => array(
			'label'          => 'DigitalOcean Spaces',
			'endpoint'       => 'https://{region}.digitaloceanspaces.com',
			'region'         => 'fra1',
			'use_path_style' => 0,
			'help'           => 'DigitalOcean Spaces object storage',
		),
		'backblaze' => array(
			'label'          => 'Backblaze B2',
			'endpoint'       => 'https://s3.{region}.backblazeb2.com',
			'region'         => 'eu-central-003',
			'use_path_style' => 0,
			'help'           => 'Backblaze B2 Cloud Storage with S3-compatible API',
		),
		'cloudflare' => array(
			'label'          => 'Cloudflare R2',
			'endpoint'       => 'https://{account_id}.r2.cloudflarestorage.com',
			'region'         => 'auto',
			'use_path_style' => 1,
			'help'           => 'Cloudflare R2 - zero egress fees. Replace {account_id} with your Cloudflare account ID.',
		),
	);
}
