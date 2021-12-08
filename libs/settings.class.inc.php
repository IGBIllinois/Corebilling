<?php

class settings {

	private const TITLE = "Core Billing";
	private const ENABLE_LOG = false;
	private const NEWS_AGE = 120;
	private const DATASERVER_ENABLED = false;
	private const TIMEZONE = "UTC";
	private const LDAP_HOST = "localhost";
	private const LDAP_PORT = 389;
	private const LDAP_BASE_DN = "";
	private const LDAP_SSL = false;
	private const LDAP_TLS = false;
	private const LDAP_BIND_USER = "";
	private const LDAP_BIND_PASS = "";
	private const SESSION_TIMEOUT = 300;

	public static function get_version() {
		return VERSION;
	}

	public static function get_codewebsite_url() {
		return CODEWEBSITE_URL;
	}

	public static function get_title() {
		if (defined("TITLE") && (TITLE != "")) {
			return TITLE; 
		}
		return self::TITLE;
	}

	public static function get_log_enabled() {
		if (defined("ENABLE_LOG") && (is_bool(ENABLE_LOG))) {
			return ENABLE_LOG;
		}
		return self::ENABLE_LOG;
	}
	public static function get_log_dir() {
		return LOG_DIR;

	}

	public static function get_log_file() {
		return realpath(__DIR__ . "/../" . self::get_log_dir()) . "/" . LOG_FILE; 

	}
	public static function get_device_log() {
		return realpath(__DIR__ . "/../" . self::get_log_dir()) . "/" . LOG_DEVICE;

	}
	public static function get_password_reset_url() {
		if (defined("PASSWORD_RESET_URL") && (PASSWORD_RESET_URL != "")) {
			return PASSWORD_RESET_URL;
		}
		return false;
	}

	public static function get_news_age() {
		if (defined("NEWS_AGE") && (is_int(NEWS_AGE))) {
                        return NEWS_AGE;
                }
                return self::NEWS_AGE;

	}

	public static function get_dataserver_enabled() {
		if (defined("DATASERVER_ENABLED") && (is_bool(DATASERVER_ENABLED))) {
			return DATASERVER_ENABLED;
		}
		return self::DATASERVER_ENABLED;

	}

	public static function get_dataserver_root_dir() {
		if (defined("DATASERVER_ROOT_DIR") && (DATASERVER_ROOT_DIR != "")) {
			return DATASERVER_ROOT_DIR;
		}
		return false;
	}
	public static function get_timezone() {
		if (defined("TIMEZONE") && (TIMEZONE != '')) {
			return TIMEZONE;
		}
		return self::TIMEZONE;

	}
	public static function get_user_exceptions() {
		if (defined("USER_EXCEPTIONS_ARRAY") && is_array(USER_EXCEPTIONS_ARRAY)) {
			return array_map('strtolower',USER_EXCEPTIONS_ARRAY);
		}
		return array();
	
	}

	public static function get_ldap_host() {
		if (defined("LDAP_HOST")) {
			return LDAP_HOST;
		}
		return self::LDAP_HOST;
	}

	public static function get_ldap_port() {
		if (defined("LDAP_PORT")) {
			return LDAP_PORT;
		}
		return self::LDAP_PORT;
	}
	public static function get_ldap_base_dn() {
		if (defined("LDAP_BASE_DN")) {
			return LDAP_BASE_DN;
		}
		return self::LDAP_BASE_DN;
	}
	public static function get_ldap_ssl() {
		if (defined("LDAP_SSL")) {
			return LDAP_SSL;
		}
		return self::LDAP_SSL;
	}

	public static function get_ldap_tls() {
		if (defined("LDAP_TLS")) {
			return LDAP_TLS;
		}
		return self::LDAP_TLS;
	}
	public static function get_ldap_bind_user() {
		if (defined("LDAP_BIND_USER")) {
			return LDAP_BIND_USER;
		}
		return self::LDAP_BIND_USER;
	}
	public static function get_ldap_bind_password() {
		if (defined("LDAP_BIND_PASS")) {
			return LDAP_BIND_PASS;
		}
		return self::LDAP_BIND_PASS;
	}
	public static function get_session_name() {
		if (defined("SESSION_NAME")) {
			return SESSION_NAME;
		}
		return NULL;
	}
	public static function get_session_timeout() {
		if (defined("SESSION_TIMEOUT")) {
			return SESSION_TIMEOUT;
		}
		return self::SESSION_TIMEOUT;
	}

	public static function get_corebillingservice_url() {
		if (defined("COREBILLINGSERVICE_URL")) {
			return COREBILLINGSERVICE_URL;
		}
		return false;
	}
}
?>
