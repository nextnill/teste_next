-- 2014-06-26
ALTER TABLE `msstone_app`.`client` 
ADD COLUMN `doc_exig_bill_of_lading` CHAR(1) NOT NULL DEFAULT 'N' AFTER `doc_exig_fumigation_certificate`;

ALTER TABLE `msstone_app`.`agency` 
ADD COLUMN `shipping_company` VARCHAR(150) NOT NULL AFTER `excluido`;