<?php
return [
    'driver' => 'files',

    'files'  => [
        'encrypt_data'  => false,
        'salt'          => 'secret_salt_key',   #
        'path'          => '/',                 #
		'rotate'		=> 0,				    # Rotate every 30 min(60 * 30).
        'domain'        => null,                #
        'http_only'     => true,                #
        'secure'        => null,
        'expiration'    => 0,                   #

		'name'			=> '_Bittr_SESSID',		#
        'match_ip'      => true,               #
        'match_browser' => true,               #
		'save_path'     => __DIR__ . '/Tmp',    #
        'cache_limiter' => 'none'           	#

    ],

    'cookie' => [
        # ...
    ]


];
