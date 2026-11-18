-- ============================================================================
-- DrawChaos (CORET! CHAOS) — Full MySQL Database Schema & Initial Seed Data
-- Matches the 13-Table dbdiagram.io Entity-Relationship Diagram (ERD)
-- Database: drawchaos_db
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `drawchaos_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `drawchaos_db`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `artworks`;
DROP TABLE IF EXISTS `round_points`;
DROP TABLE IF EXISTS `strokes`;
DROP TABLE IF EXISTS `curse_uses`;
DROP TABLE IF EXISTS `curses`;
DROP TABLE IF EXISTS `guesses`;
DROP TABLE IF EXISTS `rounds`;
DROP TABLE IF EXISTS `words`;
DROP TABLE IF EXISTS `game_players`;
DROP TABLE IF EXISTS `games`;
DROP TABLE IF EXISTS `room_players`;
DROP TABLE IF EXISTS `rooms`;
DROP TABLE IF EXISTS `players`;

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------------------------
-- 1. Table: players
-- ----------------------------------------------------------------------------
CREATE TABLE `players` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nickname` VARCHAR(50) NOT NULL,
  `avatar` VARCHAR(255) NULL DEFAULT NULL,
  `guest_token_hash` VARCHAR(64) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `players_guest_token_hash_unique` (`guest_token_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. Table: rooms
-- ----------------------------------------------------------------------------
CREATE TABLE `rooms` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` CHAR(4) NOT NULL,
  `host_player_id` BIGINT UNSIGNED NOT NULL,
  `status` VARCHAR(20) NOT NULL,
  `settings` JSON NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rooms_code_unique` (`code`),
  KEY `rooms_host_player_id_foreign` (`host_player_id`),
  CONSTRAINT `rooms_host_player_id_foreign`
    FOREIGN KEY (`host_player_id`) REFERENCES `players` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. Table: room_players
-- ----------------------------------------------------------------------------
CREATE TABLE `room_players` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `room_id` BIGINT UNSIGNED NOT NULL,
  `player_id` BIGINT UNSIGNED NOT NULL,
  `is_ready` BOOLEAN NOT NULL DEFAULT FALSE,
  `joined_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `left_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `room_players_room_id_foreign` (`room_id`),
  KEY `room_players_player_id_foreign` (`player_id`),
  CONSTRAINT `room_players_room_id_foreign`
    FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `room_players_player_id_foreign`
    FOREIGN KEY (`player_id`) REFERENCES `players` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. Table: games
-- ----------------------------------------------------------------------------
CREATE TABLE `games` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `room_id` BIGINT UNSIGNED NOT NULL,
  `status` VARCHAR(20) NOT NULL,
  `started_at` TIMESTAMP NULL DEFAULT NULL,
  `finished_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `games_room_id_foreign` (`room_id`),
  CONSTRAINT `games_room_id_foreign`
    FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 5. Table: game_players
-- ----------------------------------------------------------------------------
CREATE TABLE `game_players` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `game_id` BIGINT UNSIGNED NOT NULL,
  `player_id` BIGINT UNSIGNED NOT NULL,
  `turn_order` INT NOT NULL,
  `total_score` INT NOT NULL DEFAULT 0,
  `chaos_energy` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `game_players_game_id_foreign` (`game_id`),
  KEY `game_players_player_id_foreign` (`player_id`),
  CONSTRAINT `game_players_game_id_foreign`
    FOREIGN KEY (`game_id`) REFERENCES `games` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `game_players_player_id_foreign`
    FOREIGN KEY (`player_id`) REFERENCES `players` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 6. Table: words
-- ----------------------------------------------------------------------------
CREATE TABLE `words` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `phrase` VARCHAR(255) NOT NULL,
  `category` VARCHAR(50) NULL DEFAULT NULL,
  `difficulty` VARCHAR(20) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `words_phrase_unique` (`phrase`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 7. Table: rounds
-- ----------------------------------------------------------------------------
CREATE TABLE `rounds` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `game_id` BIGINT UNSIGNED NOT NULL,
  `artist_player_id` BIGINT UNSIGNED NOT NULL,
  `word_id` BIGINT UNSIGNED NOT NULL,
  `round_number` INT NOT NULL,
  `status` VARCHAR(20) NOT NULL,
  `started_at` TIMESTAMP NULL DEFAULT NULL,
  `ended_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `rounds_game_id_foreign` (`game_id`),
  KEY `rounds_artist_player_id_foreign` (`artist_player_id`),
  KEY `rounds_word_id_foreign` (`word_id`),
  CONSTRAINT `rounds_game_id_foreign`
    FOREIGN KEY (`game_id`) REFERENCES `games` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `rounds_artist_player_id_foreign`
    FOREIGN KEY (`artist_player_id`) REFERENCES `players` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `rounds_word_id_foreign`
    FOREIGN KEY (`word_id`) REFERENCES `words` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 8. Table: guesses
-- ----------------------------------------------------------------------------
CREATE TABLE `guesses` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `round_id` BIGINT UNSIGNED NOT NULL,
  `player_id` BIGINT UNSIGNED NOT NULL,
  `content` VARCHAR(255) NOT NULL,
  `is_correct` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `guesses_round_id_foreign` (`round_id`),
  KEY `guesses_player_id_foreign` (`player_id`),
  CONSTRAINT `guesses_round_id_foreign`
    FOREIGN KEY (`round_id`) REFERENCES `rounds` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `guesses_player_id_foreign`
    FOREIGN KEY (`player_id`) REFERENCES `players` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 9. Table: curses
-- ----------------------------------------------------------------------------
CREATE TABLE `curses` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(50) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `energy_cost` INT NOT NULL,
  `duration_ms` INT NOT NULL,
  `cooldown_ms` INT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `curses_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 10. Table: curse_uses
-- ----------------------------------------------------------------------------
CREATE TABLE `curse_uses` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `round_id` BIGINT UNSIGNED NOT NULL,
  `player_id` BIGINT UNSIGNED NOT NULL,
  `curse_id` BIGINT UNSIGNED NOT NULL,
  `payload` JSON NULL DEFAULT NULL,
  `activated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `curse_uses_round_id_foreign` (`round_id`),
  KEY `curse_uses_player_id_foreign` (`player_id`),
  KEY `curse_uses_curse_id_foreign` (`curse_id`),
  CONSTRAINT `curse_uses_round_id_foreign`
    FOREIGN KEY (`round_id`) REFERENCES `rounds` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `curse_uses_player_id_foreign`
    FOREIGN KEY (`player_id`) REFERENCES `players` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `curse_uses_curse_id_foreign`
    FOREIGN KEY (`curse_id`) REFERENCES `curses` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 11. Table: strokes
-- ----------------------------------------------------------------------------
CREATE TABLE `strokes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `round_id` BIGINT UNSIGNED NOT NULL,
  `sequence` INT NOT NULL,
  `color` VARCHAR(20) NOT NULL,
  `brush_size` INT NOT NULL,
  `points` JSON NOT NULL,
  `started_at_ms` INT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `strokes_round_id_foreign` (`round_id`),
  CONSTRAINT `strokes_round_id_foreign`
    FOREIGN KEY (`round_id`) REFERENCES `rounds` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 12. Table: round_points
-- ----------------------------------------------------------------------------
CREATE TABLE `round_points` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `round_id` BIGINT UNSIGNED NOT NULL,
  `player_id` BIGINT UNSIGNED NOT NULL,
  `reason` VARCHAR(50) NOT NULL,
  `points` INT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `round_points_round_id_foreign` (`round_id`),
  KEY `round_points_player_id_foreign` (`player_id`),
  CONSTRAINT `round_points_round_id_foreign`
    FOREIGN KEY (`round_id`) REFERENCES `rounds` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `round_points_player_id_foreign`
    FOREIGN KEY (`player_id`) REFERENCES `players` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 13. Table: artworks (1-to-1 with rounds via UNIQUE round_id)
-- ----------------------------------------------------------------------------
CREATE TABLE `artworks` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `round_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `image_path` VARCHAR(500) NULL DEFAULT NULL,
  `gif_path` VARCHAR(500) NULL DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `artworks_round_id_unique` (`round_id`),
  CONSTRAINT `artworks_round_id_foreign`
    FOREIGN KEY (`round_id`) REFERENCES `rounds` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- INITIAL SEED DATA (Words, Curses, Players, Room CHAO, Active Game & Round)
-- ============================================================================

INSERT INTO `words` (`id`, `phrase`, `category`, `difficulty`, `created_at`, `updated_at`) VALUES
(1, 'Kucing Naik Helikopter', 'Aksi Mustahil', 'medium', NOW(), NOW()),
(2, 'Jerapah', 'Flora & Fauna', 'easy', NOW(), NOW()),
(3, 'Pocong Main Skateboard', 'Horor Komedi', 'hard', NOW(), NOW()),
(4, 'T-Rex Sepatu Roda', 'Prasejarah Rusuh', 'medium', NOW(), NOW()),
(5, 'Koki Pizza Terbalik', 'Kuliner Bencana', 'medium', NOW(), NOW()),
(6, 'Monalisa Sakit Gigi', 'Parodi Klasik', 'hard', NOW(), NOW()),
(7, 'Roket Kehabisan Bensin', 'Antariksa Gagal', 'medium', NOW(), NOW()),
(8, 'Burung Merak Rusuh', 'Fauna Panik', 'easy', NOW(), NOW());

INSERT INTO `curses` (`id`, `slug`, `name`, `energy_cost`, `duration_ms`, `cooldown_ms`, `created_at`, `updated_at`) VALUES
(1, 'earthquake', 'Gempa Bumi', 30, 5000, 8000, NOW(), NOW()),
(2, 'invert', 'Kuas Mabuk', 45, 4000, 10000, NOW(), NOW()),
(3, 'blindfold', 'Mati Lampu', 60, 3000, 12000, NOW(), NOW()),
(4, 'sticker', 'Sticker Bomb', 25, 6000, 6000, NOW(), NOW());

INSERT INTO `players` (`id`, `nickname`, `avatar`, `guest_token_hash`, `created_at`, `updated_at`) VALUES
(1, 'KucingNgebut', 'cat-neon', 'a1b2c3d4e5f60001a1b2c3d4e5f60001a1b2c3d4e5f60001a1b2c3d4e5f60001', NOW(), NOW()),
(2, 'BocilMaut', 'dino-glitch', 'a1b2c3d4e5f60002a1b2c3d4e5f60002a1b2c3d4e5f60002a1b2c3d4e5f60002', NOW(), NOW()),
(3, 'RaccoonGila', 'raccoon-cyber', 'a1b2c3d4e5f60003a1b2c3d4e5f60003a1b2c3d4e5f60003a1b2c3d4e5f60003', NOW(), NOW()),
(4, 'GhostyBoo', 'ghost-mint', 'a1b2c3d4e5f60004a1b2c3d4e5f60004a1b2c3d4e5f60004a1b2c3d4e5f60004', NOW(), NOW()),
(5, 'BangJago', 'blob-yellow', 'a1b2c3d4e5f60005a1b2c3d4e5f60005a1b2c3d4e5f60005a1b2c3d4e5f60005', NOW(), NOW()),
(6, 'SitiCubit', 'bot-pixel', 'a1b2c3d4e5f60006a1b2c3d4e5f60006a1b2c3d4e5f60006a1b2c3d4e5f60006', NOW(), NOW());

INSERT INTO `rooms` (`id`, `code`, `host_player_id`, `status`, `settings`, `created_at`, `updated_at`) VALUES
(1, 'CHAO', 1, 'playing', '{"max_players": 8, "max_rounds": 5, "round_duration_sec": 60, "stakes": 5800}', NOW(), NOW());

INSERT INTO `room_players` (`id`, `room_id`, `player_id`, `is_ready`, `joined_at`, `left_at`) VALUES
(1, 1, 1, TRUE, NOW(), NULL),
(2, 1, 2, TRUE, NOW(), NULL),
(3, 1, 3, TRUE, NOW(), NULL),
(4, 1, 4, FALSE, NOW(), NULL),
(5, 1, 5, TRUE, NOW(), NULL),
(6, 1, 6, TRUE, NOW(), NULL);

INSERT INTO `games` (`id`, `room_id`, `status`, `started_at`, `finished_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'playing', NOW(), NULL, NOW(), NOW());

INSERT INTO `game_players` (`id`, `game_id`, `player_id`, `turn_order`, `total_score`, `chaos_energy`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1180, 75, NOW(), NOW()),
(2, 1, 2, 2, 1420, 90, NOW(), NOW()),
(3, 1, 3, 3, 960, 80, NOW(), NOW()),
(4, 1, 4, 4, 540, 60, NOW(), NOW()),
(5, 1, 5, 5, 890, 70, NOW(), NOW()),
(6, 1, 6, 6, 640, 65, NOW(), NOW());

INSERT INTO `rounds` (`id`, `game_id`, `artist_player_id`, `word_id`, `round_number`, `status`, `started_at`, `ended_at`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 2, 'drawing', NOW(), NULL, NOW(), NOW());

INSERT INTO `artworks` (`id`, `round_id`, `title`, `image_path`, `gif_path`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Karya: Kehancuran Mental @BocilMaut, Est. 2026', 'artworks/tragedi-kucing-di-udara.webp', 'replays/panic-CHAO-round-2-1.gif', 'completed', NOW(), NOW());
