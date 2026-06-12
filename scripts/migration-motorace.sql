-- ============================================================
-- MotoRace Phase 2 — bet storage
-- Run this once in phpMyAdmin against the same DB as Wingo.
-- ============================================================

CREATE TABLE IF NOT EXISTS `moto_race_bets` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `game_code` VARCHAR(32) NOT NULL,
  `issue_number` VARCHAR(64) NOT NULL,
  `play_type` VARCHAR(32) NOT NULL,
  `play_bet` VARCHAR(16) NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `bet_count` INT(11) NOT NULL DEFAULT 1,
  `multiplier` INT(11) NOT NULL DEFAULT 1,
  `total_amount` DECIMAL(15,2) NOT NULL,
  `fee` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `bet_amount` DECIMAL(15,2) NOT NULL,
  `rate` DECIMAL(8,2) NOT NULL,
  `status` ENUM('pending','win','lose') NOT NULL DEFAULT 'pending',
  `win_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `result_ranking` VARCHAR(64) DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `settled_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user`        (`user_id`),
  KEY `idx_user_game`   (`user_id`, `game_code`),
  KEY `idx_issue`       (`game_code`, `issue_number`),
  KEY `idx_status`      (`status`),
  KEY `idx_created`     (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;