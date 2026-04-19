<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_100 extends App_module_migration {

	public function up()
	{
		$this->create_event_tables();
		$this->create_allergens_deetary_types_tables();
		$this->create_menu_categories_sections_tables();
		$this->create_menu_suppliers_table();
		$this->create_menu_ingredients_table();
		$this->create_menu_items_table();
		$this->create_menu_tables();
		$this->create_menu_item_link_tables();
		$this->create_package_tables();
		$this->create_event_staff_tables();
		$this->create_event_note_tables();
		$this->create_event_menu_tables();
	}

	private function create_event_tables()
	{
		$db_charset = $this->ci->db->char_set;
		$event_types_table_name = db_prefix().'catering_event_types';
		$this->ci->db->query(
			"CREATE TABLE IF NOT EXISTS `".$event_types_table_name."` (
			  `etid` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			  `name` VARCHAR(50) NOT NULL,
			  `background_color` VARCHAR(10) DEFAULT NULL,
			  `text_color` VARCHAR(10) DEFAULT '#000000',
			  `sort_order` INT DEFAULT 0,
			  `editable` TINYINT(1) DEFAULT 1,
			  `created_by` INT NOT NULL,
			  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `updated_at` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			   PRIMARY KEY (`etid`),
			   CONSTRAINT `catering_event_types_created_by` FOREIGN KEY (`created_by`) REFERENCES `".db_prefix()."staff`(`staffid`) ON DELETE RESTRICT ON UPDATE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=".$db_charset.";"
		);

		$events_table_name = db_prefix().'catering_events';
		$this->ci->db->query(
			"CREATE TABLE IF NOT EXISTS `".$events_table_name."` (
                `eventid` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `hash` VARCHAR(32) NOT NULL,
                `client_id` INT(11) DEFAULT NULL,
                `lead_id` INT(11) DEFAULT NULL,
                `event_name` VARCHAR(191) NOT NULL,
                `event_type_id` INT UNSIGNED DEFAULT NULL,
                `status` enum('enquiry','quoted','confirmed','in_progress','completed','cancelled','lost') NOT NULL DEFAULT 'enquiry',
                `venue_name` VARCHAR(191) DEFAULT NULL,
                `venue_address` TEXT DEFAULT NULL,
                `event_start` DATETIME NOT NULL,
                `event_end` DATETIME DEFAULT NULL,
                `guest_count_expected` INT(11) DEFAULT 0,
                `guest_count_final` INT(11) DEFAULT NULL,
                `dietary_notes` LONGTEXT DEFAULT NULL,
                `allergen_summary` TEXT DEFAULT NULL,
                `internal_notes` LONGTEXT DEFAULT NULL,
                `project_id` INT(11) DEFAULT NULL,
                `estimate_id` INT(11) DEFAULT NULL,
                `kanban_order` INT DEFAULT 1,
                `invoice_id` INT(11) DEFAULT NULL,
                `created_by` INT(11) NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			    `updated_at` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`eventid`),
                UNIQUE KEY `hash` (`hash`),
                KEY `client_id` (`client_id`),
                KEY `lead_id` (`lead_id`),
                KEY `status` (`status`),
                KEY `event_start` (`event_start`),
                KEY `project_id` (`project_id`),
                CONSTRAINT `catering_events_client_id` FOREIGN KEY (`client_id`) REFERENCES `".db_prefix()."clients` (`userid`) ON DELETE RESTRICT ON UPDATE CASCADE,
                CONSTRAINT `catering_events_lead_id` FOREIGN KEY (`lead_id`) REFERENCES `".db_prefix()."leads` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
                CONSTRAINT `catering_events_event_type_id` FOREIGN KEY (`event_type_id`) REFERENCES `".db_prefix()."catering_event_types` (`etid`) ON DELETE SET NULL ON UPDATE CASCADE,
                CONSTRAINT `catering_events_project_id` FOREIGN KEY (`project_id`) REFERENCES `".db_prefix()."projects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
                CONSTRAINT `catering_events_estimate_id` FOREIGN KEY (`estimate_id`) REFERENCES `".db_prefix()."estimates` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
                CONSTRAINT `catering_events_invoice_id` FOREIGN KEY (`invoice_id`) REFERENCES `".db_prefix()."invoices` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
                CONSTRAINT `catering_events_created_by` FOREIGN KEY (`created_by`) REFERENCES `".db_prefix()."staff` (`staffid`) ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=".$db_charset.";"
		);
	}

	private function create_allergens_deetary_types_tables()
	{
		$this->ci->db->query(
			"CREATE TABLE IF NOT EXISTS `".db_prefix()."catering_dietary_types` (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`code` VARCHAR(50) NOT NULL UNIQUE,
				`label` VARCHAR(100) NOT NULL,
				`icon` VARCHAR(50) NULL,
				`description` TEXT NULL,
				`display_order` INT NOT NULL DEFAULT 0,
				`active` TINYINT(1) NOT NULL DEFAULT 1,
				`created_by` INT(11) NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			    `updated_at` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				INDEX `idx_code` (`code`),
				INDEX `idx_active` (`active`),
				CONSTRAINT `catering_dietary_types_created_by` FOREIGN KEY (`created_by`) REFERENCES `".db_prefix()."staff` (`staffid`) ON DELETE RESTRICT ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.";"
		);

		$this->ci->db->query(
			"CREATE TABLE IF NOT EXISTS `".db_prefix()."catering_allergens` (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`code` VARCHAR(50) NOT NULL UNIQUE,
				`label` VARCHAR(100) NOT NULL,
				`severity` ENUM('mild', 'moderate', 'severe') NOT NULL DEFAULT 'moderate',
				`icon` VARCHAR(50) NULL,
				`description` TEXT NULL,
				`display_order` INT NOT NULL DEFAULT 0,
				`active` TINYINT(1) NOT NULL DEFAULT 1,
				`created_by` INT(11) NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			    `updated_at` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				INDEX `idx_code` (`code`),
				INDEX `idx_active` (`active`),
				CONSTRAINT `catering_allergens_created_by` FOREIGN KEY (`created_by`) REFERENCES `".db_prefix()."staff` (`staffid`) ON DELETE RESTRICT ON UPDATE CASCADE
    		) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.";"
		);
	}

	public function down()
	{
		$tables_to_drop = [
			'catering_event_staff',
			'catering_event_notes',
			'catering_event_types',
			'catering_events',
		];
		foreach ($tables_to_drop as $table)
		{
			$table = db_prefix().$table;
			$this->ci->db->query("DROP TABLE IF EXISTS `$table`");
		}
	}

	private function create_menu_categories_sections_tables()
	{
		$this->ci->db->query(
			"CREATE TABLE IF NOT EXISTS `".db_prefix()."catering_menu_categories` (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`name` VARCHAR(100) NOT NULL,
				`parent_id` INT UNSIGNED NULL,
				`icon` VARCHAR(50) NULL,
				`color` VARCHAR(7) NULL COMMENT 'Hex color code',
				`display_order` INT NOT NULL DEFAULT 0,
				`active` TINYINT(1) NOT NULL DEFAULT 1,
				`created_by` INT(11) NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			    `updated_at` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				INDEX `idx_parent` (`parent_id`),
				INDEX `idx_active` (`active`),
				CONSTRAINT `catering_menu_categories_created_by` FOREIGN KEY (`created_by`) REFERENCES `".db_prefix()."staff` (`staffid`) ON DELETE RESTRICT ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.";"
		);

		$this->ci->db->query(
			"CREATE TABLE IF NOT EXISTS `".db_prefix()."catering_menu_sections` (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`name` VARCHAR(100) NOT NULL,
				`description` TEXT NULL,
				`display_order` INT NOT NULL DEFAULT 0,
				`active` TINYINT(1) NOT NULL DEFAULT 1,
				`created_by` INT(11) NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			    `updated_at` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				INDEX `idx_active` (`active`),
				CONSTRAINT `catering_menu_sections_created_by` FOREIGN KEY (`created_by`) REFERENCES `".db_prefix()."staff` (`staffid`) ON DELETE RESTRICT ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.";"
		);
	}

	private function create_menu_suppliers_table()
	{
		$this->ci->db->query(
			"CREATE TABLE IF NOT EXISTS `".db_prefix()."catering_suppliers` (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`name` VARCHAR(191) NOT NULL,
				`contact` VARCHAR(191) NULL,
				`email` VARCHAR(191) NULL,
				`phone` VARCHAR(50) NULL,
				`address` TEXT NULL,
				`notes` TEXT NULL,
				`active` TINYINT(1) NOT NULL DEFAULT 1,
				`created_by` INT(11) NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			    `updated_at` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				INDEX `idx_active` (`active`),
				CONSTRAINT `catering_suppliers_created_by` FOREIGN KEY (`created_by`) REFERENCES `".db_prefix()."staff` (`staffid`) ON DELETE RESTRICT ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.";"
		);
	}

	private function create_menu_ingredients_table()
	{
		$this->ci->db->query(
			"CREATE TABLE IF NOT EXISTS `".db_prefix()."catering_ingredients` (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`name` VARCHAR(191) NOT NULL,
				`unit` VARCHAR(20) NOT NULL COMMENT 'kg, g, l, ml, pcs',
				`avg_cost_per_unit` DECIMAL(15,4) NOT NULL DEFAULT 0,
				`stock_on_hand` DECIMAL(15,3) NOT NULL DEFAULT 0,
				`reorder_level` DECIMAL(15,3) NOT NULL DEFAULT 0,
				`supplier_id` INT UNSIGNED NULL,
				`active` TINYINT(1) NOT NULL DEFAULT 1,
				`created_by` INT(11) NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			    `updated_at` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				INDEX `idx_supplier` (`supplier_id`),
				INDEX `idx_active` (`active`),
				CONSTRAINT `catering_ingredients_supplier_id` FOREIGN KEY (`supplier_id`) REFERENCES `".db_prefix()."catering_suppliers` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
				CONSTRAINT `catering_ingredients_created_by` FOREIGN KEY (`created_by`) REFERENCES `".db_prefix()."staff` (`staffid`) ON DELETE RESTRICT ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.";"
		);
	}

	private function create_menu_items_table()
	{
		$this->ci->db->query(
			"CREATE TABLE IF NOT EXISTS `".db_prefix()."catering_menu_items` (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`item_name` VARCHAR(191) NOT NULL,
				`category_id` INT UNSIGNED NOT NULL,
				`description` TEXT NULL,
				`unit_cost` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Ingredient cost per portion',
				`unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Sell price per portion',
				`default_portion_size` VARCHAR(50) NULL COMMENT 'e.g., per person',
				`prep_time_minutes` INT NULL,
				`version` INT NOT NULL DEFAULT 1 COMMENT 'Version control',
				`image` VARCHAR(255) NULL,
				`active` TINYINT(1) NOT NULL DEFAULT 1,
				`created_by` INT(11) NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			    `updated_at` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				INDEX `idx_category` (`category_id`),
				INDEX `idx_active` (`active`),
				INDEX `idx_version` (`version`),
				CONSTRAINT `catering_menu_items_category_id` FOREIGN KEY (`category_id`) REFERENCES `".db_prefix()."catering_menu_categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
				CONSTRAINT `catering_menu_items_created_by` FOREIGN KEY (`created_by`) REFERENCES `".db_prefix()."staff` (`staffid`) ON DELETE RESTRICT ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.";"
		);
	}


	private function create_menu_tables()
	{
		$this->ci->db->query(
			"CREATE TABLE IF NOT EXISTS `".db_prefix()."catering_menus` (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`menu_name` VARCHAR(191) NOT NULL,
				`description` TEXT NULL,
				`base_price_per_person` DECIMAL(15,2) NULL,
				`active` TINYINT(1) NOT NULL DEFAULT 1,
				`created_by` INT(11) NOT NULL,
				`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				INDEX `idx_active` (`active`),
				CONSTRAINT `catering_menus_created_by` FOREIGN KEY (`created_by`) REFERENCES `".db_prefix()."staff` (`staffid`) ON DELETE RESTRICT ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.";"
		);

		$this->ci->db->query(
			"CREATE TABLE IF NOT EXISTS `".db_prefix()."catering_menu_items_link` (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`menu_id` INT UNSIGNED NOT NULL,
				`item_id` INT UNSIGNED NOT NULL,
				`section_id` INT UNSIGNED NOT NULL,
				`position` INT NOT NULL DEFAULT 0,
				PRIMARY KEY (`id`),
				INDEX `idx_menu` (`menu_id`),
				INDEX `idx_item` (`item_id`),
				INDEX `idx_menu_section_pos` (`menu_id`, `section_id`, `position`),
				CONSTRAINT `catering_menu_items_link_menu_id` FOREIGN KEY (`menu_id`) REFERENCES `".db_prefix()."catering_menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				CONSTRAINT `catering_menu_items_link_item_id` FOREIGN KEY (`item_id`) REFERENCES `".db_prefix()."catering_menu_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				CONSTRAINT `catering_menu_items_link_section_id` FOREIGN KEY (`section_id`) REFERENCES `".db_prefix()."catering_menu_sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.";"
		);
	}

	private function create_menu_item_link_tables()
	{
		$this->ci->db->query(
			"CREATE TABLE IF NOT EXISTS `".db_prefix()."catering_menu_item_dietary` (
				`item_id` INT UNSIGNED NOT NULL,
				`dietary_type_id` INT UNSIGNED NOT NULL,
				PRIMARY KEY (`item_id`, `dietary_type_id`),
				INDEX `idx_dietary` (`dietary_type_id`),
				CONSTRAINT `catering_menu_item_dietary_item_id` FOREIGN KEY (`item_id`) REFERENCES `".db_prefix()."catering_menu_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				CONSTRAINT `catering_menu_item_dietary_dietary_type_id` FOREIGN KEY (`dietary_type_id`) REFERENCES `".db_prefix()."catering_dietary_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.";"
		);

		$this->ci->db->query(
			"CREATE TABLE IF NOT EXISTS `".db_prefix()."catering_menu_item_allergens` (
				`item_id` INT UNSIGNED NOT NULL,
				`allergen_id` INT UNSIGNED NOT NULL,
				PRIMARY KEY (`item_id`, `allergen_id`),
				INDEX `idx_allergen` (`allergen_id`),
				CONSTRAINT `catering_menu_item_allergens_item_id` FOREIGN KEY (`item_id`) REFERENCES `".db_prefix()."catering_menu_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				CONSTRAINT `catering_menu_item_allergens_allergen_id` FOREIGN KEY (`allergen_id`) REFERENCES `".db_prefix()."catering_allergens` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.";"
		);

		$this->ci->db->query(
			"CREATE TABLE IF NOT EXISTS `".db_prefix()."catering_menu_item_ingredients_link` (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`item_id` INT UNSIGNED NOT NULL,
				`ingredient_id` INT UNSIGNED NOT NULL,
				`qty_per_portion` DECIMAL(15,4) NOT NULL DEFAULT 0,
				PRIMARY KEY (`id`),
				INDEX `idx_item` (`item_id`),
				INDEX `idx_ingredient` (`ingredient_id`),
				CONSTRAINT `catering_menu_item_ingredients_link_item_id` FOREIGN KEY (`item_id`) REFERENCES `".db_prefix()."catering_menu_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				CONSTRAINT `catering_menu_item_ingredients_link_ingredient_id` FOREIGN KEY (`ingredient_id`) REFERENCES `".db_prefix()."catering_ingredients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.";"
		);

		$this->ci->db->query(
			"CREATE TABLE IF NOT EXISTS `".db_prefix()."catering_menu_item_dependencies` (
				`parent_item_id` INT UNSIGNED NOT NULL,
				`dependent_item_id` INT UNSIGNED NOT NULL,
				`dependency_type` ENUM('required', 'suggested', 'incompatible') NOT NULL DEFAULT 'suggested',
				`notes` TEXT NULL,
				PRIMARY KEY (`parent_item_id`, `dependent_item_id`),
				INDEX `idx_dependent` (`dependent_item_id`),
				CONSTRAINT `catering_menu_item_dependencies_parent_item_id` FOREIGN KEY (`parent_item_id`) REFERENCES `".db_prefix()."catering_menu_items` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
				CONSTRAINT `catering_menu_item_dependencies_dependent_item_id` FOREIGN KEY (`dependent_item_id`) REFERENCES `".db_prefix()."catering_menu_items` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.";"
		);
	}

	private function create_package_tables()
	{
		$this->ci->db->query(
			"CREATE TABLE IF NOT EXISTS `".db_prefix()."catering_packages` (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`package_name` VARCHAR(191) NOT NULL,
				`description` TEXT NULL,
				`price_per_person` DECIMAL(15,2) NOT NULL DEFAULT 0,
				`min_guests` INT NOT NULL DEFAULT 1,
				`max_guests` INT NULL,
				`active` TINYINT(1) NOT NULL DEFAULT 1,
				`created_by` INT(11) NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			    `updated_at` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				INDEX `idx_active` (`active`)
			) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.";"
		);

		$this->ci->db->query(
			"CREATE TABLE IF NOT EXISTS `".db_prefix()."catering_package_items_link` (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`package_id` INT UNSIGNED NOT NULL,
				`item_id` INT UNSIGNED NOT NULL,
				`qty_per_guest` DECIMAL(10,3) NOT NULL DEFAULT 1 COMMENT 'Portions per guest',
				PRIMARY KEY (`id`),
				INDEX `idx_package` (`package_id`),
				INDEX `idx_item` (`item_id`),
				CONSTRAINT `catering_package_items_link_item_id` FOREIGN KEY (`item_id`) REFERENCES `".db_prefix()."catering_menu_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				CONSTRAINT `catering_package_items_link_package_id` FOREIGN KEY (`package_id`) REFERENCES `".db_prefix()."catering_packages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.";"
		);
	}

	private function create_event_menu_tables()
	{
		$this->ci->db->query(
			"CREATE TABLE IF NOT EXISTS `".db_prefix()."catering_event_menu` (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`event_id` INT UNSIGNED NOT NULL,
				`menu_id` INT UNSIGNED NULL,
				`package_id` INT UNSIGNED NULL,
				`pricing_mode` ENUM('per_person', 'fixed', 'package') NOT NULL DEFAULT 'per_person',
				`price_per_person` DECIMAL(15,2) NULL,
				`fixed_price` DECIMAL(15,2) NULL,
				`multiplier` DECIMAL(5,2) NOT NULL DEFAULT 1.00,
				`multiplier_reason` TEXT NULL,
				`weekend_surcharge` DECIMAL(15,2) NOT NULL DEFAULT 0,
				`notes` TEXT NULL,
				`created_by` INT(11) NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			    `updated_at` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				INDEX `idx_event` (`event_id`),
				CONSTRAINT `catering_event_menu_event_id` FOREIGN KEY (`event_id`) REFERENCES `".db_prefix()."catering_events` (`eventid`) ON DELETE CASCADE ON UPDATE CASCADE,
				CONSTRAINT `catering_event_menu_menu_id` FOREIGN KEY (`menu_id`) REFERENCES `".db_prefix()."catering_menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				CONSTRAINT `catering_event_menu_package_id` FOREIGN KEY (`package_id`) REFERENCES `".db_prefix()."catering_packages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				CONSTRAINT `catering_event_menu_created_by` FOREIGN KEY (`created_by`) REFERENCES `".db_prefix()."staff` (`staffid`) ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.";"
		);

		$this->ci->db->query(
			"CREATE TABLE IF NOT EXISTS `".db_prefix()."catering_event_menu_items` (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`event_id` INT UNSIGNED NOT NULL,
				`event_menu_id` INT UNSIGNED NOT NULL,
				`item_id` INT UNSIGNED NULL COMMENT 'NULL for custom items',
				`item_version` INT NULL COMMENT 'Snapshot version',
				`section_id` INT UNSIGNED NOT NULL,
				`custom_name` VARCHAR(191) NULL,
				`custom_description` TEXT NULL,
				`portion_per_guest` DECIMAL(10,3) NOT NULL DEFAULT 1,
				`unit_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
				`unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0,
				`position` INT NOT NULL DEFAULT 0,
				`dietary_snapshot` JSON NULL COMMENT 'Snapshot of dietary flags',
				`allergen_snapshot` JSON NULL COMMENT 'Snapshot of allergens',
				`created_by` INT(11) NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			    `updated_at` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				INDEX `idx_event_menu` (`event_menu_id`),
				INDEX `idx_event_position` (`event_menu_id`, `position`),
				INDEX `idx_item_version` (`item_id`, `item_version`),
				CONSTRAINT `catering_event_menu_items_event_id` FOREIGN KEY (`event_id`) REFERENCES `".db_prefix()."catering_events` (`eventid`) ON DELETE RESTRICT ON UPDATE CASCADE,
				CONSTRAINT `catering_event_menu_items_event_menu_id` FOREIGN KEY (`event_menu_id`) REFERENCES `".db_prefix()."catering_event_menu` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
				CONSTRAINT `catering_event_menu_items_event_item_id` FOREIGN KEY (`item_id`) REFERENCES `".db_prefix()."catering_menu_items` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
				CONSTRAINT `catering_event_menu_items_event_section_id` FOREIGN KEY (`section_id`) REFERENCES `".db_prefix()."catering_menu_sections` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
				CONSTRAINT `catering_event_menu_items_created_by` FOREIGN KEY (`created_by`) REFERENCES `".db_prefix()."staff` (`staffid`) ON DELETE RESTRICT ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.";"
		);

		$this->ci->db->query(
			"CREATE TABLE IF NOT EXISTS `".db_prefix()."catering_event_menu_history` (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`event_menu_id` INT UNSIGNED NOT NULL,
				`changed_by` INT(11) NOT NULL COMMENT 'tblstaff.staffid',
				`change_type` ENUM('item_added', 'item_removed', 'quantity_changed', 'price_changed', 'other') NOT NULL,
				`old_value` JSON NULL,
				`new_value` JSON NULL,
				`description` TEXT NULL,
				`changed_at` DATETIME NOT NULL,
				PRIMARY KEY (`id`),
				INDEX `idx_event_menu` (`event_menu_id`),
				INDEX `idx_changed_at` (`changed_at`),
				CONSTRAINT `catering_event_menu_history_changed_by_event_menu_id` FOREIGN KEY (`event_menu_id`) REFERENCES `".db_prefix()."catering_event_menu` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
				CONSTRAINT `catering_event_menu_history_changed_by` FOREIGN KEY (`changed_by`) REFERENCES `".db_prefix()."staff` (`staffid`) ON DELETE RESTRICT ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.";"
		);
	}

	private function create_event_staff_tables()
	{
		$this->ci->db->query(
			"
            CREATE TABLE IF NOT EXISTS `".db_prefix()."catering_event_staff` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `event_id` INT UNSIGNED NOT NULL,
                `staff_id` int(11) NOT NULL,
                `role` varchar(100) NOT NULL,
                `shift_start` datetime NOT NULL,
                `shift_end` datetime NOT NULL,
                `hours` decimal(5,2) DEFAULT NULL,
                `hourly_rate` decimal(10,2) DEFAULT NULL,
                `notes` text DEFAULT NULL,
                `status` enum('pending','confirmed','declined','completed') DEFAULT 'pending',
                `created_by` int(11) NOT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `event_id` (`event_id`),
                KEY `staff_id` (`staff_id`),
                KEY `status` (`status`),
                KEY `shift_start` (`shift_start`),
                KEY `shift_end` (`shift_end`),
                CONSTRAINT `fk_catering_event_staff_event` FOREIGN KEY (`event_id`) REFERENCES `".db_prefix()."catering_events` (`eventid`) ON DELETE CASCADE,
                CONSTRAINT `fk_catering_event_staff_staff` FOREIGN KEY (`staff_id`) REFERENCES `".db_prefix()."staff` (`staffid`) ON DELETE CASCADE,
                CONSTRAINT `fk_catering_event_staff_created_by` FOREIGN KEY (`created_by`) REFERENCES `".db_prefix()."staff` (`staffid`) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.";"
		);

		// Create catering_staff_roles table for predefined roles
		$this->ci->db->query(
			"
            CREATE TABLE IF NOT EXISTS `".db_prefix()."catering_staff_roles` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `role_name` varchar(100) NOT NULL,
                `description` text DEFAULT NULL,
                `default_hourly_rate` decimal(10,2) DEFAULT NULL,
                `color` varchar(7) DEFAULT '#007cba',
                `active` tinyint(1) DEFAULT 1,
                `display_order` int(11) DEFAULT 0,
                `created_at` datetime NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_role_name` (`role_name`),
                KEY `active` (`active`),
                KEY `display_order` (`display_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.";"
		);

		// Insert default staff roles
		$default_roles = [
			['role_name' => 'Head Chef', 'description' => 'Lead chef responsible for kitchen operations', 'default_hourly_rate' => 45.00, 'color' => '#d9534f', 'display_order' => 1],
			['role_name' => 'Chef', 'description' => 'Kitchen staff member', 'default_hourly_rate' => 35.00, 'color' => '#f0ad4e', 'display_order' => 2],
			['role_name' => 'Server', 'description' => 'Front of house service staff', 'default_hourly_rate' => 25.00, 'color' => '#5bc0de', 'display_order' => 3],
			['role_name' => 'Bartender', 'description' => 'Beverage service specialist', 'default_hourly_rate' => 30.00, 'color' => '#5cb85c', 'display_order' => 4],
			['role_name' => 'Manager', 'description' => 'Event manager/supervisor', 'default_hourly_rate' => 40.00, 'color' => '#337ab7', 'display_order' => 5],
			['role_name' => 'Driver', 'description' => 'Delivery and transportation', 'default_hourly_rate' => 20.00, 'color' => '#6c757d', 'display_order' => 6],
		];

		foreach ($default_roles as $role)
		{
			$this->ci->db->reset_query();
			$this->ci->db->where('role_name', $role['role_name']);
			$items_count = $this->ci->db->count_all_results(db_prefix().'catering_staff_roles');
			if ($items_count > 0)
			{
				continue;
			}
			$role['created_at'] = date('Y-m-d H:i:s');
			$this->ci->db->insert(db_prefix().'catering_staff_roles', $role);
		}
	}

	private function create_event_note_tables()
	{
		$this->ci->db->query(
			"CREATE TABLE IF NOT EXISTS `".db_prefix()."catering_event_notes` (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`event_id` INT UNSIGNED NOT NULL,
				`description` TEXT NOT NULL,
				`visible_to_client` TINYINT(1) NOT NULL DEFAULT 0,
				`created_by` INT(11) NOT NULL,
				`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				INDEX `idx_event` (`event_id`),
				INDEX `idx_created_at` (`created_at`),
				INDEX `idx_visible_to_client` (`visible_to_client`),
				CONSTRAINT `catering_event_notes_event_id` FOREIGN KEY (`event_id`) REFERENCES `".db_prefix()."catering_events` (`eventid`) ON DELETE CASCADE ON UPDATE CASCADE,
				CONSTRAINT `catering_event_notes_created_by` FOREIGN KEY (`created_by`) REFERENCES `".db_prefix()."staff` (`staffid`) ON DELETE RESTRICT ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=".$this->ci->db->char_set.";"
		);
	}
}