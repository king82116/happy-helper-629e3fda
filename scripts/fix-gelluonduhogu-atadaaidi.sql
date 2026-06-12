-- Run once in phpMyAdmin if period ids are not saving (values exceed INT max).
-- Period ids like 20260526100010726 need VARCHAR or BIGINT, not INT(11).

ALTER TABLE `gelluonduhogu`
  MODIFY `atadaaidi` VARCHAR(32) NOT NULL;

-- Optional: same fix for other WinGo interval tables if needed.
-- ALTER TABLE `gelluonduhogu_drei` MODIFY `atadaaidi` VARCHAR(32) NOT NULL;
-- ALTER TABLE `gelluonduhogu_funf` MODIFY `atadaaidi` VARCHAR(32) NOT NULL;
