<?php declare( strict_types=1 );

use PHP_SF\System\Classes\Helpers\TimeZone;

const DEV_MODE = false;
const TEMPLATES_CACHE_ENABLED = true;

const DEFAULT_TIMEZONE = TimeZone::ETC_UTC;

const SERVER_IP = '127.0.0.1';

const APPLICATION_NAME = 'PHP SF Template';

const AVAILABLE_HOSTS = [ SERVER_IP, '127.0.0.1' ];

const ENTITY_DIRECTORY = __DIR__ . '/../App/Entity';

const LANGUAGES_LIST = [ 'en' ];
