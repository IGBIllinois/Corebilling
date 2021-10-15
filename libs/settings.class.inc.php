<?php

class settings {

	private const TITLE = "Core Billing";
	private const ENABLE_LOG = false;
	private const NEWS_AGE = 120;
	private const DATASERVER_ENABLED = false;
	private const TIMEZONE = "UTC";

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
}
?>
