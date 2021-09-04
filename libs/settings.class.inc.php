<?php

class settings {

	private const TITLE = "Core Billing";
	private const ENABLE_LOG = false;
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
}
?>
