<?php
return [
    'driver' => 'sql',
    
    'encrypt_data'  => false,
    'key'           => 'secret_salt_key',   #
    'path'          => '/',                 #
	'rotate'		=> 0,				    # Rotate every 30 min(60 * 30).
    'domain'        => null,                #
    'http_only'     => true,                #
    'secure'        => null,                #
    'expiration'    => 0,                   #

	'name'			=> '_Bittr_SESSID',		#
    'match_ip'      => false,               #
    'match_browser' => false,               #
	'save_path'     => __DIR__ . '/Tmp',    #
    'cache_limiter' => 'none',           	#

    'sql'           => [
        'driver'    => 'mysql',
        'host'      => '127.0.0.1',         #
        'db_name'   => 'session',
     #  'db_table'  => 'session',
        'db_user'   => 'root',
        'db_pass'   => '',
        'persistent_conn' => true,
    ]


];
