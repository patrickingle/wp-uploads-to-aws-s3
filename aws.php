<?php
require(dirname(__FILE__).'/aws/aws-autoloader.php');

use Aws\S3\S3Client;

class aws_class {
	private $client;
	private $s3;
	
	public function __construct($key, $secret, $region) {
		$credentials = array(
        					'key'      => $key,
        					'secret'   => $secret
    					);
		// Instantiate an Amazon S3 client.
		$this->s3 = S3Client::factory([
		    'version' => 'latest',
		    'region'  => $region,
		    'credentials' => $credentials,
		    'http' => array(
		    				'verify' => false
		    			),
		    'debug' => false
		]);
		
	}
	
	public function upload($bucket, $local_file, $newfile, $metadata=array()) {
		set_time_limit(0);
		
		try {
		    $this->s3->putObject([
		        'Bucket' => $bucket,
		        'Key'    => $newfile,
		        'Body'   => fopen($local_file, 'r'),
		        'ACL'    => 'public-read',
		        'Metadata' => $metadata
		    ]);
		} catch (Exception $e) {
		    error_log($e->getMessage());
		}
	}
	
	public function download($bucket, $key, $filename) {
		$result = $this->s3->getObject(array( 
												'Bucket' => $bucket, 
												'Key' => $key, 
												'ResponseContentDisposition' => 'attachment; filename="'.$filename.'"' 
											)
									);
		return $result['Body'];		
	}
	
	public function remove($bucket, $filename) {
		try {
			$this->s3->deleteObject([
				'Bucket' => $bucket,
				'Key' => $filename
			]);			
		} catch(Exception $e) {
		    error_log($e->getMessage());
		}
	}
	
}


?>