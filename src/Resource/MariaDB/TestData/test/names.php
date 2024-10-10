<?php

namespace Tokenmapper\Resource\MariaDB\TestData\test;

use \Approach\Resource\MariaDB;
use Tokenmapper\Resource\MariaDB\Aspect\TestData\test\names\user_trait as aspects;

class names extends MariaDB\Table{

	/** Link minted Resource to its Aspects Profile */
	use aspects;
	// GetSourceName yourself from somewhere

	// Change the user_trait to add functionality to this generated class
	const DATABASE_CLASS = '\Tokenmapper\Resource\MariaDB\TestData\test';
	const SERVER_CLASS = '\Tokenmapper\Resource\MariaDB\TestData';
	const CONNECTOR_CLASS = '\Approach\Service\MariaDB\Connector';
	const RESOURCE_CLASS = '\Tokenmapper\Resource\MariaDB';
	const DATABASE_NAME = 'test';
	const SERVER_NAME = 'p:TestData';
	const RESOURCE_PROTO = 'MariaDB';
	const NAME = 'names';
	const COMMENT = '';
	const ENGINE = 'InnoDB';
	const ROW_FORMAT = 'Dynamic';
	const TABLE_COLLATION = 'utf8mb4_0900_ai_ci';
	const CREATE_OPTIONS = '';
	const TABLE_ROWS = '0';
	const AVG_ROW_LENGTH = '0';
	const DATA_LENGTH = '16384';
	const MAX_DATA_LENGTH = '0';
	const INDEX_LENGTH = '0';
	const DATA_FREE = '0';
	const AUTO_INCREMENT = '';
	const CREATE_TIME = '2024-06-25 21:59:01';
	const UPDATE_TIME = '';
	const CHECK_TIME = '';
	const CHECKSUM = '';
	const TABLE_COMMENT = '';
}
