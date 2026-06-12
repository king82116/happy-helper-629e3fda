-- ============================================================
-- MotoRace dedicated result table — independent of Wingo.
-- Each row = one drawn period (game_code + issue_number) with
-- a comma-joined ranking of motos 1..10 (1st..10th place).
-- Run once in phpMyAdmin against the same DB.
-- ============================================================

CREATE TABLE IF NOT EXISTS `moto_race_results` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `game_code` VARCHAR(32) NOT NULL,
  `issue_number` VARCHAR(64) NOT NULL,
  `ranking` VARCHAR(64) NOT NULL,
  `end_time` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_game_issue` (`game_code`, `issue_number`),
  KEY `idx_game_end` (`game_code`, `end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;