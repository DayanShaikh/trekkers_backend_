<?php

return [
	'endpoint' => [
		'aws' => [
			'host' => env('SOLR_HOST', 'ec2-52-91-128-89.compute-1.amazonaws.com'),
			'port' => env('SOLR_PORT', '80'),
			'path' => env('SOLR_PATH', '/'),
			'core' => env('SOLR_CORE', 'ignite-new'),
			'username' => env('SOLR_USERNAME', 'user'),
			'password' => env('SOLR_PASSWORD', 'WLHZdb3TP4qM'),
		]
	]
];
