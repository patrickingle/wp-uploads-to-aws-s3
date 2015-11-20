<?php
/**
* Plugin Name: WP Uploads To AWS S3
* Plugin URI: https://github.com/patrickingle/wp-uploads-to-aws-s3
* Description: Creates a backup copy of the Media Upload file upload to an AWS S3 bucket
* Author: PHK Corporation
* Author URI: http://www.phkcorp.com
* Version: 1.0.0
* License: GPLv2
*/
$aws = include dirname(__FILE__).'/aws/config.php';
$s3 = $aws['s3'];
require dirname(__FILE__) . '/aws.php';

// create variable on include
$AWS = new aws_class($s3['key'], $s3['secret'], $s3['region']);


function wpuaws3_custom_upload_filter( $file ) {
	global $s3, $AWS;
	
	// Replace all spaces in the filename with a hyphen, as the filter for
	// wp_delete_file file argument is passing in a filename with hyphens substitution for spaces,
	// and thus prevents deleting from the AWS S3 bucket because the name is different.
	$file['name'] = str_ireplace(' ','-',$file['name']);
	
	$upload_dir = wp_upload_dir();
	$url = parse_url($upload_dir['url']);
	$path = ltrim($url['path'],'/');
	
	$aws_upload_filepath = $path.'/'.$file['name'];

	$AWS->upload($s3['bucket'],$file['tmp_name'],$aws_upload_filepath);		

    return $file;
}

add_filter('wp_handle_upload_prefilter', 'wpuaws3_custom_upload_filter' );

function wpuaws3_custom_delete_file( $file ) {
	global $s3, $AWS;
	
	// Get the upload directory
	$upload_dir = wp_upload_dir();
	// No extract the upload baseurl, without the host
	$baseurl = parse_url($upload_dir['baseurl']);
	// Replace all backslashes with slashes, and remove the leading slash
	$baseurl = ltrim(str_ireplace('\\','/',$baseurl['path']),'\\');
	// Replace all backslashes with slashes, to use with comparing with the path
	$basedir = str_ireplace('\\','/',$upload_dir['basedir']);
	// Locate the starting position of baseurl in the path, this gives us the root path ending point
	$pos = strpos($basedir,$baseurl);
	// Add one extra position to account for the trailing slash on the root path
	$length = $pos + 1;
	// Extract the basepath file name from the $file parameter and conver backslashes to slashes
	$aws_upload_filepath = str_ireplace('\\','/',substr($file,$length));
	
	$AWS->remove($s3['bucket'], $aws_upload_filepath);
	
	return $file;
}

add_filter('wp_delete_file', 'wpuaws3_custom_delete_file', 10, 1);

function wpuaws3_activation_hook() {
	error_log('Activating '.__FILE__);
}
register_activation_hook( __FILE__, 'wpuaws3_activation_hook' );


?>