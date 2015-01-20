-- 2014-06-24
ALTER TABLE `msstone_app`.`client` 
CHANGE COLUMN `port_of_discharge` `destination_port` VARCHAR(200) NULL DEFAULT NULL ;

ALTER TABLE `msstone_app`.`client` 
ADD COLUMN `doc_exig_proforma_invoice` CHAR(1) NOT NULL DEFAULT 'N' AFTER `doc_exig_certif_orig`,
ADD COLUMN `doc_exig_fumigation_certificate` CHAR(1) NOT NULL DEFAULT 'N' AFTER `doc_exig_proforma_invoice`;