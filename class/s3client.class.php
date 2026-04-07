<?php
/* Copyright (C) 2026 Dolicraft <contact@dolicraft.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file class/s3client.class.php
 * \ingroup dolicrafts3
 * \brief S3-compatible client using native PHP (no AWS SDK dependency)
 *
 * Supports: AWS S3, OVH Object Storage, Scaleway, Wasabi, MinIO, DigitalOcean Spaces,
 * Backblaze B2, Cloudflare R2, and any S3-compatible provider.
 */

/**
 * Class DolicraftS3Client
 * Lightweight S3-compatible client using AWS Signature V4
 */
class DolicraftS3Client
{
	/** @var string S3 endpoint URL */
	private $endpoint;

	/** @var string AWS region */
	private $region;

	/** @var string Access key ID */
	private $accessKey;

	/** @var string Secret access key */
	private $secretKey;

	/** @var string Bucket name */
	private $bucket;

	/** @var string Path prefix inside bucket */
	private $pathPrefix;

	/** @var bool Use path-style URLs (required for MinIO, some providers) */
	private $usePathStyle;

	/** @var string Last error message */
	public $error = '';

	/** @var array Last error details */
	public $errors = array();

	/** @var string Service name for signing */
	private $service = 's3';

	/**
	 * Constructor
	 *
	 * @param array $config Configuration array with keys: endpoint, region, access_key, secret_key, bucket, path_prefix, use_path_style
	 */
	public function __construct($config = array())
	{
		$this->endpoint = rtrim($config['endpoint'] ?? '', '/');
		$this->region = $config['region'] ?? 'us-east-1';
		$this->accessKey = $config['access_key'] ?? '';
		$this->secretKey = $config['secret_key'] ?? '';
		$this->bucket = $config['bucket'] ?? '';
		$this->pathPrefix = trim($config['path_prefix'] ?? '', '/');
		$this->usePathStyle = !empty($config['use_path_style']);
	}

	/**
	 * Load configuration from Dolibarr constants
	 *
	 * @param DoliDB $db Database handler
	 * @return DolicraftS3Client
	 */
	public static function fromDolibarrConf($db)
	{
		global $conf;

		$config = array(
			'endpoint'       => getDolGlobalString('DOLICRAFTS3_ENDPOINT'),
			'region'         => getDolGlobalString('DOLICRAFTS3_REGION', 'us-east-1'),
			'access_key'     => getDolGlobalString('DOLICRAFTS3_ACCESS_KEY'),
			'secret_key'     => getDolGlobalString('DOLICRAFTS3_SECRET_KEY'),
			'bucket'         => getDolGlobalString('DOLICRAFTS3_BUCKET'),
			'path_prefix'    => getDolGlobalString('DOLICRAFTS3_PATH_PREFIX'),
			'use_path_style' => getDolGlobalInt('DOLICRAFTS3_USE_PATH_STYLE'),
		);

		return new self($config);
	}

	/**
	 * Get the full object key with prefix
	 *
	 * @param string $key Object key
	 * @return string Full key with prefix
	 */
	private function getFullKey($key)
	{
		$key = ltrim($key, '/');
		if ($this->pathPrefix !== '') {
			return $this->pathPrefix.'/'.$key;
		}
		return $key;
	}

	/**
	 * Build the host for requests
	 *
	 * @return string Host
	 */
	private function getHost()
	{
		$parsed = parse_url($this->endpoint);
		if ($this->usePathStyle) {
			return $parsed['host'].(isset($parsed['port']) ? ':'.$parsed['port'] : '');
		}
		return $this->bucket.'.'.$parsed['host'].(isset($parsed['port']) ? ':'.$parsed['port'] : '');
	}

	/**
	 * Build the base URL for requests
	 *
	 * @param string $path Path to append
	 * @return string Full URL
	 */
	private function getUrl($path = '')
	{
		$path = ltrim($path, '/');
		if ($this->usePathStyle) {
			return $this->endpoint.'/'.$this->bucket.($path !== '' ? '/'.$path : '');
		}
		$parsed = parse_url($this->endpoint);
		$scheme = $parsed['scheme'] ?? 'https';
		$host = $parsed['host'];
		$port = isset($parsed['port']) ? ':'.$parsed['port'] : '';
		return $scheme.'://'.$this->bucket.'.'.$host.$port.($path !== '' ? '/'.$path : '');
	}

	/**
	 * AWS Signature V4 signing
	 *
	 * @param string $method HTTP method
	 * @param string $uri URI path
	 * @param array  $queryParams Query parameters
	 * @param array  $headers Headers
	 * @param string $payload Request body
	 * @return array Signed headers
	 */
	private function signRequest($method, $uri, $queryParams = array(), $headers = array(), $payload = '')
	{
		$timestamp = gmdate('Ymd\THis\Z');
		$datestamp = gmdate('Ymd');

		$headers['host'] = $this->getHost();
		$headers['x-amz-date'] = $timestamp;
		$headers['x-amz-content-sha256'] = hash('sha256', $payload);

		// Canonical headers
		ksort($headers);
		$canonicalHeaders = '';
		$signedHeadersList = array();
		foreach ($headers as $key => $value) {
			$canonicalHeaders .= strtolower($key).':'.trim($value)."\n";
			$signedHeadersList[] = strtolower($key);
		}
		$signedHeaders = implode(';', $signedHeadersList);

		// Canonical query string
		ksort($queryParams);
		$canonicalQueryString = http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);

		// Canonical request
		$canonicalRequest = implode("\n", array(
			$method,
			$uri,
			$canonicalQueryString,
			$canonicalHeaders,
			$signedHeaders,
			$headers['x-amz-content-sha256'],
		));

		// String to sign
		$credentialScope = $datestamp.'/'.$this->region.'/'.$this->service.'/aws4_request';
		$stringToSign = implode("\n", array(
			'AWS4-HMAC-SHA256',
			$timestamp,
			$credentialScope,
			hash('sha256', $canonicalRequest),
		));

		// Signing key
		$kDate = hash_hmac('sha256', $datestamp, 'AWS4'.$this->secretKey, true);
		$kRegion = hash_hmac('sha256', $this->region, $kDate, true);
		$kService = hash_hmac('sha256', $this->service, $kRegion, true);
		$kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

		$signature = hash_hmac('sha256', $stringToSign, $kSigning);

		$headers['Authorization'] = 'AWS4-HMAC-SHA256 Credential='.$this->accessKey.'/'.$credentialScope
			.', SignedHeaders='.$signedHeaders
			.', Signature='.$signature;

		return $headers;
	}

	/**
	 * Execute an HTTP request
	 *
	 * @param string $method HTTP method
	 * @param string $objectKey Object key (path in bucket)
	 * @param array  $queryParams Query parameters
	 * @param string $payload Request body
	 * @param array  $extraHeaders Additional headers
	 * @return array{code: int, headers: array, body: string}|false Response or false on error
	 */
	private function request($method, $objectKey = '', $queryParams = array(), $payload = '', $extraHeaders = array())
	{
		$encodedKey = $objectKey !== '' ? '/'.implode('/', array_map('rawurlencode', explode('/', $objectKey))) : '/';
		$uri = $this->usePathStyle ? '/'.$this->bucket.$encodedKey : $encodedKey;

		$headers = $extraHeaders;
		$signedHeaders = $this->signRequest($method, $uri, $queryParams, $headers, $payload);

		$url = $this->getUrl($objectKey);
		if (!empty($queryParams)) {
			$url .= '?'.http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);
		}

		$curlHeaders = array();
		foreach ($signedHeaders as $key => $value) {
			$curlHeaders[] = $key.': '.$value;
		}

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL            => $url,
			CURLOPT_CUSTOMREQUEST  => $method,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER     => $curlHeaders,
			CURLOPT_TIMEOUT        => 300,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_HEADER         => true,
		));

		if ($payload !== '') {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		}

		$response = curl_exec($ch);

		if (curl_errno($ch)) {
			$this->error = 'cURL error: '.curl_error($ch);
			$this->errors[] = $this->error;
			curl_close($ch);
			return false;
		}

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		curl_close($ch);

		$responseHeaders = substr($response, 0, $headerSize);
		$responseBody = substr($response, $headerSize);

		return array(
			'code'    => $httpCode,
			'headers' => $this->parseHeaders($responseHeaders),
			'body'    => $responseBody,
		);
	}

	/**
	 * Parse HTTP response headers
	 *
	 * @param string $headerString Raw headers
	 * @return array Parsed headers
	 */
	private function parseHeaders($headerString)
	{
		$headers = array();
		foreach (explode("\r\n", $headerString) as $line) {
			if (strpos($line, ':') !== false) {
				list($key, $value) = explode(':', $line, 2);
				$headers[strtolower(trim($key))] = trim($value);
			}
		}
		return $headers;
	}

	/**
	 * Parse S3 XML error response
	 *
	 * @param string $xmlBody Response body
	 * @return string Error message
	 */
	private function parseS3Error($xmlBody)
	{
		if (empty($xmlBody)) {
			return 'Empty response';
		}
		$xml = @simplexml_load_string($xmlBody);
		if ($xml && isset($xml->Message)) {
			return (string) $xml->Code.': '.(string) $xml->Message;
		}
		return 'Unknown S3 error';
	}

	/**
	 * Test connection to S3 bucket
	 *
	 * @return array{success: bool, message: string}
	 */
	public function testConnection()
	{
		$response = $this->request('HEAD');

		if ($response === false) {
			return array('success' => false, 'message' => $this->error);
		}

		if ($response['code'] === 200) {
			return array('success' => true, 'message' => 'Connection successful');
		}

		if ($response['code'] === 404) {
			return array('success' => false, 'message' => 'Bucket not found: '.$this->bucket);
		}

		if ($response['code'] === 403) {
			return array('success' => false, 'message' => 'Access denied. Check your credentials.');
		}

		return array('success' => false, 'message' => 'HTTP '.$response['code'].': '.$this->parseS3Error($response['body']));
	}

	/**
	 * List objects in bucket
	 *
	 * @param string $prefix Prefix filter
	 * @param string $delimiter Delimiter (use '/' for directory-like listing)
	 * @param int    $maxKeys Maximum number of keys to return
	 * @param string $continuationToken Continuation token for pagination
	 * @return array{objects: array, prefixes: array, truncated: bool, next_token: string}|false
	 */
	public function listObjects($prefix = '', $delimiter = '/', $maxKeys = 1000, $continuationToken = '')
	{
		$fullPrefix = $this->getFullKey($prefix);
		if ($fullPrefix !== '' && substr($fullPrefix, -1) !== '/') {
			$fullPrefix .= '/';
		}
		// Remove leading slash if pathPrefix is empty and prefix is empty
		$fullPrefix = ltrim($fullPrefix, '/');

		$queryParams = array(
			'list-type'  => '2',
			'prefix'     => $fullPrefix,
			'delimiter'  => $delimiter,
			'max-keys'   => (string) $maxKeys,
		);

		if ($continuationToken !== '') {
			$queryParams['continuation-token'] = $continuationToken;
		}

		$response = $this->request('GET', '', $queryParams);

		if ($response === false) {
			return false;
		}

		if ($response['code'] !== 200) {
			$this->error = $this->parseS3Error($response['body']);
			$this->errors[] = $this->error;
			return false;
		}

		$xml = @simplexml_load_string($response['body']);
		if (!$xml) {
			$this->error = 'Failed to parse S3 response';
			$this->errors[] = $this->error;
			return false;
		}

		$objects = array();
		if (isset($xml->Contents)) {
			foreach ($xml->Contents as $content) {
				$key = (string) $content->Key;
				// Remove prefix to get relative path
				if ($this->pathPrefix !== '') {
					$key = preg_replace('#^'.preg_quote($this->pathPrefix.'/', '#').'#', '', $key);
				}
				$objects[] = array(
					'key'           => $key,
					'full_key'      => (string) $content->Key,
					'size'          => (int) $content->Size,
					'last_modified' => (string) $content->LastModified,
					'etag'          => trim((string) $content->ETag, '"'),
					'storage_class' => (string) ($content->StorageClass ?? 'STANDARD'),
				);
			}
		}

		$prefixes = array();
		if (isset($xml->CommonPrefixes)) {
			foreach ($xml->CommonPrefixes as $cp) {
				$p = (string) $cp->Prefix;
				if ($this->pathPrefix !== '') {
					$p = preg_replace('#^'.preg_quote($this->pathPrefix.'/', '#').'#', '', $p);
				}
				$prefixes[] = rtrim($p, '/');
			}
		}

		return array(
			'objects'    => $objects,
			'prefixes'   => $prefixes,
			'truncated'  => ((string) ($xml->IsTruncated ?? 'false')) === 'true',
			'next_token' => (string) ($xml->NextContinuationToken ?? ''),
		);
	}

	/**
	 * Upload a file to S3
	 *
	 * @param string $objectKey Destination key in bucket
	 * @param string $filePath Local file path to upload
	 * @param string $contentType MIME type (auto-detected if empty)
	 * @return bool Success
	 */
	public function uploadFile($objectKey, $filePath, $contentType = '')
	{
		if (!file_exists($filePath)) {
			$this->error = 'File not found: '.$filePath;
			$this->errors[] = $this->error;
			return false;
		}

		$content = file_get_contents($filePath);
		if ($content === false) {
			$this->error = 'Cannot read file: '.$filePath;
			$this->errors[] = $this->error;
			return false;
		}

		if ($contentType === '') {
			$contentType = $this->detectContentType($filePath);
		}

		$fullKey = $this->getFullKey($objectKey);

		$headers = array(
			'Content-Type'   => $contentType,
			'Content-Length' => (string) strlen($content),
		);

		$response = $this->request('PUT', $fullKey, array(), $content, $headers);

		if ($response === false) {
			return false;
		}

		if ($response['code'] !== 200) {
			$this->error = $this->parseS3Error($response['body']);
			$this->errors[] = $this->error;
			return false;
		}

		return true;
	}

	/**
	 * Upload content string to S3
	 *
	 * @param string $objectKey Destination key
	 * @param string $content Content to upload
	 * @param string $contentType MIME type
	 * @return bool Success
	 */
	public function putObject($objectKey, $content, $contentType = 'application/octet-stream')
	{
		$fullKey = $this->getFullKey($objectKey);

		$headers = array(
			'Content-Type'   => $contentType,
			'Content-Length' => (string) strlen($content),
		);

		$response = $this->request('PUT', $fullKey, array(), $content, $headers);

		if ($response === false) {
			return false;
		}

		if ($response['code'] !== 200) {
			$this->error = $this->parseS3Error($response['body']);
			$this->errors[] = $this->error;
			return false;
		}

		return true;
	}

	/**
	 * Download a file from S3
	 *
	 * @param string $objectKey Object key to download
	 * @param string $localPath Local file path to save to (if empty, returns content)
	 * @return string|bool File content if no localPath, true on success, false on error
	 */
	public function getObject($objectKey, $localPath = '')
	{
		$fullKey = $this->getFullKey($objectKey);

		$response = $this->request('GET', $fullKey);

		if ($response === false) {
			return false;
		}

		if ($response['code'] !== 200) {
			$this->error = $this->parseS3Error($response['body']);
			$this->errors[] = $this->error;
			return false;
		}

		if ($localPath !== '') {
			$dir = dirname($localPath);
			if (!is_dir($dir)) {
				mkdir($dir, 0755, true);
			}
			$result = file_put_contents($localPath, $response['body']);
			if ($result === false) {
				$this->error = 'Cannot write to: '.$localPath;
				$this->errors[] = $this->error;
				return false;
			}
			return true;
		}

		return $response['body'];
	}

	/**
	 * Delete an object from S3
	 *
	 * @param string $objectKey Object key to delete
	 * @return bool Success
	 */
	public function deleteObject($objectKey)
	{
		$fullKey = $this->getFullKey($objectKey);

		$response = $this->request('DELETE', $fullKey);

		if ($response === false) {
			return false;
		}

		if ($response['code'] !== 204 && $response['code'] !== 200) {
			$this->error = $this->parseS3Error($response['body']);
			$this->errors[] = $this->error;
			return false;
		}

		return true;
	}

	/**
	 * Get object metadata (HEAD request)
	 *
	 * @param string $objectKey Object key
	 * @return array|false Metadata array or false on error
	 */
	public function headObject($objectKey)
	{
		$fullKey = $this->getFullKey($objectKey);

		$response = $this->request('HEAD', $fullKey);

		if ($response === false) {
			return false;
		}

		if ($response['code'] !== 200) {
			$this->error = 'Object not found or access denied';
			$this->errors[] = $this->error;
			return false;
		}

		return array(
			'content_type'   => $response['headers']['content-type'] ?? '',
			'content_length' => (int) ($response['headers']['content-length'] ?? 0),
			'last_modified'  => $response['headers']['last-modified'] ?? '',
			'etag'           => trim($response['headers']['etag'] ?? '', '"'),
		);
	}

	/**
	 * Copy an object within S3
	 *
	 * @param string $sourceKey Source object key
	 * @param string $destKey Destination object key
	 * @return bool Success
	 */
	public function copyObject($sourceKey, $destKey)
	{
		$fullSourceKey = $this->getFullKey($sourceKey);
		$fullDestKey = $this->getFullKey($destKey);

		$headers = array(
			'x-amz-copy-source' => '/'.$this->bucket.'/'.rawurlencode($fullSourceKey),
		);

		$response = $this->request('PUT', $fullDestKey, array(), '', $headers);

		if ($response === false) {
			return false;
		}

		if ($response['code'] !== 200) {
			$this->error = $this->parseS3Error($response['body']);
			$this->errors[] = $this->error;
			return false;
		}

		return true;
	}

	/**
	 * Generate a pre-signed URL for temporary access
	 *
	 * @param string $objectKey Object key
	 * @param int    $expires Expiration time in seconds (default 3600)
	 * @param string $method HTTP method (GET or PUT)
	 * @return string Pre-signed URL
	 */
	public function getPresignedUrl($objectKey, $expires = 3600, $method = 'GET')
	{
		$fullKey = $this->getFullKey($objectKey);
		$encodedKey = implode('/', array_map('rawurlencode', explode('/', $fullKey)));
		$uri = $this->usePathStyle ? '/'.$this->bucket.'/'.$encodedKey : '/'.$encodedKey;

		$timestamp = gmdate('Ymd\THis\Z');
		$datestamp = gmdate('Ymd');
		$credentialScope = $datestamp.'/'.$this->region.'/'.$this->service.'/aws4_request';

		$queryParams = array(
			'X-Amz-Algorithm'     => 'AWS4-HMAC-SHA256',
			'X-Amz-Credential'    => $this->accessKey.'/'.$credentialScope,
			'X-Amz-Date'          => $timestamp,
			'X-Amz-Expires'       => (string) $expires,
			'X-Amz-SignedHeaders' => 'host',
		);

		ksort($queryParams);
		$canonicalQueryString = http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);

		$host = $this->getHost();
		$canonicalHeaders = 'host:'.$host."\n";

		$canonicalRequest = implode("\n", array(
			$method,
			$uri,
			$canonicalQueryString,
			$canonicalHeaders,
			'host',
			'UNSIGNED-PAYLOAD',
		));

		$stringToSign = implode("\n", array(
			'AWS4-HMAC-SHA256',
			$timestamp,
			$credentialScope,
			hash('sha256', $canonicalRequest),
		));

		$kDate = hash_hmac('sha256', $datestamp, 'AWS4'.$this->secretKey, true);
		$kRegion = hash_hmac('sha256', $this->region, $kDate, true);
		$kService = hash_hmac('sha256', $this->service, $kRegion, true);
		$kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

		$signature = hash_hmac('sha256', $stringToSign, $kSigning);

		$url = $this->getUrl($fullKey);
		$url .= '?'.$canonicalQueryString.'&X-Amz-Signature='.$signature;

		return $url;
	}

	/**
	 * Detect MIME content type from file extension
	 *
	 * @param string $filePath File path
	 * @return string MIME type
	 */
	private function detectContentType($filePath)
	{
		if (function_exists('mime_content_type')) {
			$type = mime_content_type($filePath);
			if ($type) {
				return $type;
			}
		}

		$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
		$types = array(
			'pdf' => 'application/pdf', 'doc' => 'application/msword',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'xls' => 'application/vnd.ms-excel',
			'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
			'gif' => 'image/gif', 'svg' => 'image/svg+xml', 'webp' => 'image/webp',
			'zip' => 'application/zip', 'gz' => 'application/gzip',
			'csv' => 'text/csv', 'txt' => 'text/plain', 'html' => 'text/html',
			'json' => 'application/json', 'xml' => 'application/xml',
			'mp4' => 'video/mp4', 'mp3' => 'audio/mpeg',
		);

		return $types[$ext] ?? 'application/octet-stream';
	}

	/**
	 * Get human-readable file size
	 *
	 * @param int $bytes Size in bytes
	 * @return string Formatted size
	 */
	public static function formatSize($bytes)
	{
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		$i = 0;
		while ($bytes >= 1024 && $i < count($units) - 1) {
			$bytes /= 1024;
			$i++;
		}
		return round($bytes, 2).' '.$units[$i];
	}
}
