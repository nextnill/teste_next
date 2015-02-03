ALTER TABLE `msstone_app`.`lot_transport` 
ADD COLUMN `poblo_obs` VARCHAR(200) NULL DEFAULT NULL AFTER `draft_size`;

ALTER TABLE `msstone_app`.`parameters` 
ADD COLUMN `poblo_obs_interim_sobra` VARCHAR(200) NOT NULL DEFAULT '' AFTER `lot_prefix`,
ADD COLUMN `poblo_obs_final_sobra` VARCHAR(200) NOT NULL DEFAULT '' AFTER `poblo_obs_interim_sobra`,
ADD COLUMN `poblo_obs_inspected_without_lot` VARCHAR(200) NOT NULL DEFAULT '' AFTER `poblo_obs_final_sobra`;
