<?php
/**
* Plugin Name: WP Uploads To AWS S3
* Plugin URI: https://github.com/patrickingle/wp-uploads-to-aws-s3
* Description: Replaces the WP uploads directory with a AWS S3 bucket
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


function wpuaws3_custom_upload_filter($file) {
	global $s3, $AWS;
	
	$upload_dir = wp_upload_dir();
	$url = parse_url($upload_dir['url']);
	$path = ltrim($url['path'],'/');

	
	try {
		$AWS->upload($s3['bucket'],$file['tmp_name'],$path.'/'.$file['name']);		
	} catch(Exception $e) {
		echo $e->getMessage();
	}
    return $file;
}

add_filter('wp_handle_upload_prefilter', 'wpuaws3_custom_upload_filter' );
?>