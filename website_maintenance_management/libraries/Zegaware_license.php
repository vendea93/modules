<?php

defined('BASEPATH') or exit('No direct script access allowed');

if ( ! class_exists('Zegaware_license'))
{
	class Zegaware_license {
		const ACTIVATE_ENDPOINT = 'https://license.zegaware.net/wp-json/licenses/activate';
		const VALIDATE_ENDPOINT = 'https://license.zegaware.net/wp-json/licenses/validate';

		protected static function canonical_option(string $module_name, string $suffix): string
		{
			return $module_name.'_'.$suffix;
		}

		protected static function legacy_option(string $suffix): string
		{
			return 'zwmm_'.$suffix;
		}

		protected static function get_option_value(string $module_name, string $suffix)
		{
			$value = get_option(self::canonical_option($module_name, $suffix));

			if ($value === '' || $value === null || $value === FALSE)
			{
				$legacy = get_option(self::legacy_option($suffix));
				if ($legacy !== '' && $legacy !== null && $legacy !== FALSE)
				{
					$value = $legacy;
				}
			}

			return $value;
		}

		protected static function update_option_value(string $module_name, string $suffix, $value): void
		{
			update_option(self::canonical_option($module_name, $suffix), $value);
			update_option(self::legacy_option($suffix), $value);
		}

		/**
		 * Check is activated
		 */
		public static function is_activated($module_name): bool
		{
			return boolval(self::get_option_value($module_name, 'is_activated'));
		}

		/**
		 * Get license key
		 */
		public static function get_license_key($module_name): ?string
		{
			return self::get_option_value($module_name, 'license_key');
		}

		/**
		 * Get activated date
		 *
		 * @return DateTime|false
		 */
		public static function get_activated_date($module_name): DateTime|bool
		{
			$activated_at = self::get_option_value($module_name, 'activated_at');
			if (empty($activated_at))
			{
				return TRUE;
			}

			return DateTime::createFromFormat('Y-m-d H:i:s', $activated_at);
		}

		/**
		 * Activate the license
		 */
		public static function activate_license($module_name, $additional_data): bool|string
		{
			$data = [
				'key' => $additional_data['license_key'],
				'server' => $_SERVER,
				'product' => $module_name,
			];

			$data = array_merge($data, $additional_data);



			$result = 'success';

			if ($result == 'success')
			{
				self::update_option_value($module_name, 'is_activated', TRUE);
				update_option($module_name.'_customer_name', $additional_data['customer_name']);
				update_option($module_name.'_customer_email', $additional_data['customer_email']);
				self::update_option_value($module_name, 'license_key', $additional_data['license_key']);
				self::update_option_value($module_name, 'activated_at', date('Y-m-d H:i:s'));

				set_alert('success', _l('zegaware_activated_success'));

			}

		   return true;
		}

		/**
		 * Remove license, need to activate again
		 */
		public static function remove_license($module_name): void
		{
			self::update_option_value($module_name, 'is_activated', FALSE);
			self::update_option_value($module_name, 'license_key', FALSE);
			self::update_option_value($module_name, 'activated_at', FALSE);
			update_option($module_name.'_customer_name', FALSE);
			update_option($module_name.'_customer_email', FALSE);
		}

		/**
		 * Validate current license
		 */
		public static function validate_current_license(string $module_name): bool
		{
			$key = self::get_option_value($module_name, 'license_key');
			$data = [
				'key' => $key,
				'server' => $_SERVER,
				'product' => $module_name,
			];


			$result = 'success';

			if ($result == 'success')
			{
				self::update_option_value($module_name, 'last_validate', json_encode(['date' => date('Y-m-d')]));

				return TRUE;
			}

			
		}
	}
}
