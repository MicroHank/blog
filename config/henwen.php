<?php

return [
	'name' => '張瀚文',

	'from' => '屏東',

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
] ;