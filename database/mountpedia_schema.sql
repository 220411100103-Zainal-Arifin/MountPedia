-- MountPedia database schema
-- Generated from the Laravel migrations and Eloquent models.
-- Compatible with MySQL/MariaDB.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `regions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `province` VARCHAR(100) NOT NULL,
  `regency` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `uq_regions` UNIQUE (`province`, `regency`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `mountains` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `region_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(140) NOT NULL,
  `elevation_mdpl` SMALLINT UNSIGNED NOT NULL,
  `latitude` DECIMAL(9,6) NULL,
  `longitude` DECIMAL(9,6) NULL,
  `managing_authority` VARCHAR(150) NULL,
  `description` TEXT NULL,
  `image_url` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mountains_slug_unique` (`slug`),
  KEY `idx_mountains_elevation` (`elevation_mdpl`),
  FULLTEXT KEY `ft_mountains_name` (`name`),
  CONSTRAINT `mountains_region_id_foreign`
    FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `trails` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `mountain_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(140) NOT NULL,
  `basecamp_name` VARCHAR(150) NULL,
  `basecamp_latitude` DECIMAL(9,6) NULL,
  `basecamp_longitude` DECIMAL(9,6) NULL,
  `start_elevation_mdpl` SMALLINT UNSIGNED NULL,
  `distance_km` DECIMAL(5,2) NULL,
  `duration_up_hours` DECIMAL(4,1) NULL,
  `duration_down_hours` DECIMAL(4,1) NULL,
  `difficulty` ENUM('mudah','sedang','sulit','ekstrem') NULL,
  `description` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `uq_trails_slug` UNIQUE (`mountain_id`, `slug`),
  CONSTRAINT `trails_mountain_id_foreign`
    FOREIGN KEY (`mountain_id`) REFERENCES `mountains` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `campsites` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `mountain_id` BIGINT UNSIGNED NOT NULL,
  `trail_id` BIGINT UNSIGNED NULL,
  `name` VARCHAR(120) NOT NULL,
  `elevation_mdpl` SMALLINT UNSIGNED NULL,
  `capacity_tents` SMALLINT UNSIGNED NULL,
  `has_water_source` TINYINT(1) NOT NULL DEFAULT 0,
  `water_note` VARCHAR(255) NULL,
  `latitude` DECIMAL(9,6) NULL,
  `longitude` DECIMAL(9,6) NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `campsites_mountain_id_foreign`
    FOREIGN KEY (`mountain_id`) REFERENCES `mountains` (`id`) ON DELETE CASCADE,
  CONSTRAINT `campsites_trail_id_foreign`
    FOREIGN KEY (`trail_id`) REFERENCES `trails` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `trip_types` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(20) NOT NULL,
  `name` VARCHAR(60) NOT NULL,
  `description` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trip_types_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `trail_status_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `mountain_id` BIGINT UNSIGNED NOT NULL,
  `trail_id` BIGINT UNSIGNED NULL,
  `status` ENUM('buka','tutup','dibatasi') NOT NULL,
  `reason` VARCHAR(255) NULL,
  `starts_on` DATE NOT NULL,
  `ends_on` DATE NULL,
  `source_name` VARCHAR(150) NULL,
  `source_url` VARCHAR(255) NULL,
  `reported_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status_current` (`mountain_id`, `trail_id`, `reported_at`),
  CONSTRAINT `trail_status_logs_mountain_id_foreign`
    FOREIGN KEY (`mountain_id`) REFERENCES `mountains` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trail_status_logs_trail_id_foreign`
    FOREIGN KEY (`trail_id`) REFERENCES `trails` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permit_fees` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `mountain_id` BIGINT UNSIGNED NOT NULL,
  `trail_id` BIGINT UNSIGNED NULL,
  `trip_type_id` BIGINT UNSIGNED NOT NULL,
  `visitor_type` ENUM('wni','wna') NOT NULL DEFAULT 'wni',
  `day_type` ENUM('weekday','weekend_libur') NOT NULL DEFAULT 'weekday',
  `fee_idr` INT UNSIGNED NOT NULL,
  `fee_unit` ENUM('per_orang','per_orang_per_malam') NOT NULL DEFAULT 'per_orang',
  `valid_from` DATE NULL,
  `valid_until` DATE NULL,
  `source_url` VARCHAR(255) NULL,
  `notes` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `idx_permit_lookup` (`mountain_id`, `trip_type_id`, `visitor_type`, `day_type`),
  CONSTRAINT `permit_fees_mountain_id_foreign`
    FOREIGN KEY (`mountain_id`) REFERENCES `mountains` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permit_fees_trail_id_foreign`
    FOREIGN KEY (`trail_id`) REFERENCES `trails` (`id`) ON DELETE SET NULL,
  CONSTRAINT `permit_fees_trip_type_id_foreign`
    FOREIGN KEY (`trip_type_id`) REFERENCES `trip_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gear_categories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(80) NOT NULL,
  `sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gear_categories_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gear_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `gear_category_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(140) NULL,
  `image_url` VARCHAR(255) NULL,
  `description` VARCHAR(255) NULL,
  `brand` VARCHAR(80) NULL,
  `body` TEXT NULL,
  `features` TEXT NULL,
  `usage_notes` TEXT NULL,
  `specs` JSON NULL,
  `is_rentable` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gear_items_name_unique` (`name`),
  UNIQUE KEY `gear_items_slug_unique` (`slug`),
  CONSTRAINT `gear_items_gear_category_id_foreign`
    FOREIGN KEY (`gear_category_id`) REFERENCES `gear_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gear_recommendations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `trip_type_id` BIGINT UNSIGNED NOT NULL,
  `gear_item_id` BIGINT UNSIGNED NOT NULL,
  `mountain_id` BIGINT UNSIGNED NULL,
  `min_nights` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `max_nights` TINYINT UNSIGNED NULL,
  `priority` ENUM('wajib','disarankan','opsional') NOT NULL DEFAULT 'disarankan',
  `quantity_note` VARCHAR(100) NULL,
  `notes` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  KEY `idx_gear_rec` (`trip_type_id`, `mountain_id`, `min_nights`),
  CONSTRAINT `gear_recommendations_trip_type_id_foreign`
    FOREIGN KEY (`trip_type_id`) REFERENCES `trip_types` (`id`),
  CONSTRAINT `gear_recommendations_gear_item_id_foreign`
    FOREIGN KEY (`gear_item_id`) REFERENCES `gear_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `gear_recommendations_mountain_id_foreign`
    FOREIGN KEY (`mountain_id`) REFERENCES `mountains` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rental_shops` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `region_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(170) NOT NULL,
  `address` VARCHAR(255) NULL,
  `latitude` DECIMAL(9,6) NULL,
  `longitude` DECIMAL(9,6) NULL,
  `phone` VARCHAR(30) NULL,
  `whatsapp` VARCHAR(30) NULL,
  `opening_hours` VARCHAR(100) NULL,
  `description` TEXT NULL,
  `image_url` VARCHAR(255) NULL,
  `terms` TEXT NULL,
  `is_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rental_shops_slug_unique` (`slug`),
  CONSTRAINT `rental_shops_region_id_foreign`
    FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shop_mountains` (
  `rental_shop_id` BIGINT UNSIGNED NOT NULL,
  `mountain_id` BIGINT UNSIGNED NOT NULL,
  `distance_km_to_basecamp` DECIMAL(5,1) NULL,
  `note` VARCHAR(100) NULL,
  PRIMARY KEY (`rental_shop_id`, `mountain_id`),
  CONSTRAINT `shop_mountains_rental_shop_id_foreign`
    FOREIGN KEY (`rental_shop_id`) REFERENCES `rental_shops` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shop_mountains_mountain_id_foreign`
    FOREIGN KEY (`mountain_id`) REFERENCES `mountains` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rental_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rental_shop_id` BIGINT UNSIGNED NOT NULL,
  `gear_item_id` BIGINT UNSIGNED NULL,
  `item_name` VARCHAR(150) NOT NULL,
  `price_per_day_idr` INT UNSIGNED NOT NULL,
  `stock_note` VARCHAR(100) NULL,
  `notes` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  KEY `idx_rental_gear` (`gear_item_id`),
  CONSTRAINT `rental_items_rental_shop_id_foreign`
    FOREIGN KEY (`rental_shop_id`) REFERENCES `rental_shops` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rental_items_gear_item_id_foreign`
    FOREIGN KEY (`gear_item_id`) REFERENCES `gear_items` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `email_verified_at` TIMESTAMP NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(20) NOT NULL DEFAULT 'user',
  `remember_token` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_index` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache` (
  `key` VARCHAR(255) NOT NULL,
  `value` MEDIUMTEXT NOT NULL,
  `expiration` BIGINT NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` VARCHAR(255) NOT NULL,
  `owner` VARCHAR(255) NOT NULL,
  `expiration` BIGINT NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `mountain_photos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `mountain_id` BIGINT UNSIGNED NOT NULL,
  `path` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(200) NULL,
  `credit` VARCHAR(150) NULL,
  `credit_url` VARCHAR(255) NULL,
  `width` SMALLINT UNSIGNED NULL,
  `height` SMALLINT UNSIGNED NULL,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `idx_photos_order` (`mountain_id`, `sort_order`),
  CONSTRAINT `mountain_photos_mountain_id_foreign`
    FOREIGN KEY (`mountain_id`) REFERENCES `mountains` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rental_shop_photos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rental_shop_id` BIGINT UNSIGNED NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(150) NULL,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `rental_shop_photos_rental_shop_id_sort_order_index`
    (`rental_shop_id`, `sort_order`),
  CONSTRAINT `rental_shop_photos_rental_shop_id_foreign`
    FOREIGN KEY (`rental_shop_id`) REFERENCES `rental_shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rental_shop_reviews` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rental_shop_id` BIGINT UNSIGNED NOT NULL,
  `reviewer_name` VARCHAR(80) NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL,
  `comment` TEXT NULL,
  `visited_on` DATE NULL,
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `rental_shop_reviews_rental_shop_id_is_published_index`
    (`rental_shop_id`, `is_published`),
  CONSTRAINT `rental_shop_reviews_rental_shop_id_foreign`
    FOREIGN KEY (`rental_shop_id`) REFERENCES `rental_shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gear_item_photos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `gear_item_id` BIGINT UNSIGNED NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(150) NULL,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `gear_item_photos_gear_item_id_sort_order_index`
    (`gear_item_id`, `sort_order`),
  CONSTRAINT `gear_item_photos_gear_item_id_foreign`
    FOREIGN KEY (`gear_item_id`) REFERENCES `gear_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
