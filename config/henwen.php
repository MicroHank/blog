<?php

return [
	'name' => '張瀚文',

	'github_uri' => 'https://github.com/MicroHank',
	
	'github_image' => 'https://avatars1.githubusercontent.com/u/5707499?s=460&u=c775ba6248dc33f4d7a27696ce7537224937c8ff&v=4',

	'opendata' => [
		'weather' => [
			'authorization' => 'CWB-D27AD5BF-86AF-4C5E-96ED-4F371649BAAB',
			'api_36_hours' => 'https://opendata.cwb.gov.tw/api/v1/rest/datastore/F-C0032-001',
		],
	],

	'token' => [
		'expired_days' => 7,
	],

	'paginate' => [
		'user' => [
			'list' => 10,
		],
		'member' => [
			'list' => 10,
			'log' => 10,
		]
	],

	'seeder' => [
		'user' => [
			'list' => 3,
			'default_password' => '123456',
		],
		'member' => [
			'list' => 10, // member Table + member_information Table
			'group' => 3, // groups Table
			'user_group' => 10, // user_group Table
		],
	],

	'line' => [
		'client_id' => 'TJa8634yOQRVxLn90k7RPT',
		'client_secret' => 'KlVRL5yyH7FgEzfNz2FLSIIbgLBcxCU4vnNv9V6PTqa',
	],

	'smtp' => [
		'host' => 'smtp.gmail.com',
		'port' => 25,
		'login_user' => 'henwen.work@gmail.com',
		'login_passwd' => 'aulhehrupgdnzirt',
		'smtp_auth' => true,
		'smtp_debug' => 2,
		'is_ssl' => false,
		'is_smtp' => true,
	],
] ;