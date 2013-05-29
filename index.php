<?php

/*
 * This script makes the files residing in the current folder to be offered for download rather than loaded within the browser.
 * @license LGPL
 * @author Slavi Marinov
 * @url http://orbisius.com
 * @version 1.0.0
*/

// full path
try {
    $obj = new Orbisius_Force_Download();
    $obj->check_is_supported();
    $obj->download_file();
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}

/**
 *
 */
class Orbisius_Force_Download {
    private $req_url = '';
    private $req_file = '';
    private $base_dir = '';

    // http://en.wikipedia.org/wiki/Internet_media_type
    // http://pastie.org/5668002
    private $content_types = array(
        'pdf' => 'application/pdf',
        'exe' => 'application/octet-stream',
        'zip' => 'application/zip',
        'gzip' => 'application/gzip',
        'gz' => 'application/x-gzip',
        'z' => 'application/x-compress',

        'cer' => 'application/x-x509-ca-cert',
        'vcf' => 'application/text/x-vCard',
        'vcard' => 'application/text/x-vCard',

        // doc
        "tsv" => "text/tab-separated-values",
        "txt" => "text/plain",
        'dot' => 'application/msword',
        'rtf' => 'application/msword',
        'doc' => 'application/msword',
        'docx' => 'application/msword',
        'xls' => 'application/vnd.xls',
        'xlsx' => 'application/vnd.ms-excel',
        'csv' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.ms-powerpoint',
        'mdb' => 'application/x-msaccess',
        'mpp' => 'application/vnd.ms-project',

        'js' => 'text/javascript',
        'css' => 'text/css',
        'htm' => 'text/html',
        'html' => 'text/html',

        // images
        'gif' => 'image/gif',
        'png' => 'image/png',
        'jpg' => 'image/jpg',
        'jpeg' => 'image/jpg',
        'jfif' => 'image/pipeg',
        'jpe' => 'image/jpeg',
        'bmp' => 'image/bmp',

        'ics' => 'text/calendar',

        // audio & video
        'au' => 'audio/basic',
        'mid' => 'audio/mid',
        'mp3' => 'audio/mpeg',
        'avi' => 'video/x-msvideo',
        'mp4' => 'video/mp4',
        'mp2' => 'video/mpeg',
        'mpa' => 'video/mpeg',
        'mpe' => 'video/mpeg',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'mpv2' => 'video/mpeg',
        'mov' => 'video/quicktime',
        'movie' => 'video/x-sgi-movie',
    );

    /**
     *
     */
    public function __construct() {
        $req_uri = $_SERVER['REQUEST_URI'];
        $req_uri = preg_replace('#\?.*#', '', $req_uri);
        $this->req_url = $req_uri;
        $this->req_file = basename($req_uri);
        $this->base_dir = dirname(__FILE__);
    }

    /**
     * Throws an exception if not supported.
     */
    public function check_is_supported() {
		$file_decoded = urldecode($this->req_file);

        if (        $this->req_file == basename(__FILE__) // req is index.php
				|| (stripos($file_decoded, '..') !== false) // want to go 1 level 1
                || (stripos($file_decoded, '.') === false) // no extension
				) {
            throw new Exception('Invalid file.');
        }

        if (!is_file($this->base_dir . DIRECTORY_SEPARATOR . $this->req_file)
						&& !is_file($this->base_dir . DIRECTORY_SEPARATOR . $file_decoded)) {
			header('HTTP/1.0 404 Not Found');
			throw new Exception('File Not Found.');
		}
    }

    /**
     * Serves the file for download. Forces the browser to show Save as and not open the file in the browser.
     * Makes the script run for 12h just in case and after the file is sent the script stops.
     *
     * Credits:
	 * http://php.net/manual/en/function.readfile.php
     * http://stackoverflow.com/questions/2222955/idiot-proof-cross-browser-force-download-in-php
     *
     * @param string $file
     * @param bool $do_exit - exit after the file has been downloaded.
     */
    public function download_file($file = '', $do_exit = 1) {
        set_time_limit(15 * 3600); // 15 hours

        if (ini_get('zlib.output_compression')) {
            @ini_set('zlib.output_compression', 0);

            if (function_exists('apache_setenv')) {
                @apache_setenv('no-gzip', 1);
            }
        }

        // We have to put our signature here.
        header('X-Download-Software: Orbisius ForceMediaDownload');

        // SSL
        if (!empty($_SERVER['HTTPS'])
                && ($_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443)) {
            header("Cache-control: private");
            header('Pragma: private');

            // IE 6.0 fix for SSL
            // SRC http://ca3.php.net/header
            // Brandon K [ brandonkirsch uses gmail ] 25-Apr-2007 03:34
            header('Cache-Control: maxage=3600'); // Adjust maxage appropriately
        } else {
            header('Pragma: public');
        }

        $file = empty($file) ? $this->req_file : $file;
        $file = $this->base_dir . '/' . $file;

		// we were sent an encoded filename e.g. %20 for spaces
		if (!is_file($file)) {
			$file = urldecode($file);
		}

        $last_modified_time = filemtime($file);
        $etag = md5_file($file);

        header("Last-Modified: " . gmdate("D, d M Y H:i:s", $last_modified_time) . " GMT");
        header("Etag: $etag");

        // Does the user have the same file already?
        if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])
                && (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time || trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag)) {
            header('HTTP/1.1 304 Not Modified');
            exit;
        }

        // the actual file that will be downloaded
        $download_file_name = $file;
        $download_file_name = basename($download_file_name);
        $download_file_name = urldecode($download_file_name); // e.g. %20 in the filename
        $default_content_type = 'application/octet-stream';

        $ext = end(explode('.', $download_file_name));
        $ext = strtolower($ext);

        $content_types_array = $this->content_types;
        $content_type = empty($content_types_array[$ext]) ? $default_content_type : $content_types_array[$ext];

		header('Expires: 0');
 		header('Content-Description: File Transfer');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: ' . $content_type);
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . (string) (filesize($file)));
        header('Content-Disposition: attachment; filename="' . $download_file_name . '"');

		ob_clean();
		flush();

        readfile($file);
        clearstatcache();

		if ($do_exit) {
			exit;
		}
    }
}