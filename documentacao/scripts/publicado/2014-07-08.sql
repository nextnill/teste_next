SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- 2014-07-01
ALTER TABLE `msstone_app`.`lot_transport` 
ADD COLUMN `down_packing_list` TINYINT(4) NOT NULL DEFAULT 0 AFTER `items_count`,
ADD COLUMN `down_commercial_invoice` TINYINT(4) NOT NULL DEFAULT 0 AFTER `down_packing_list`,
ADD COLUMN `down_draft` TINYINT(4) NOT NULL DEFAULT 0 AFTER `down_commercial_invoice`;

-- 2014-07-03
ALTER TABLE `msstone_app`.`lot_transport` 
ADD COLUMN `client_notify_address` VARCHAR(650) NULL DEFAULT NULL AFTER `down_draft`,
ADD COLUMN `shipped_from` VARCHAR(50) NULL DEFAULT NULL AFTER `client_notify_address`,
ADD COLUMN `shipped_to` VARCHAR(50) NULL DEFAULT NULL AFTER `shipped_from`,
ADD COLUMN `bl` VARCHAR(50) NULL DEFAULT NULL AFTER `shipped_to`,
ADD COLUMN `vessel` VARCHAR(50) NULL DEFAULT NULL AFTER `bl`,
ADD COLUMN `packing_list_dated` DATE NULL DEFAULT NULL AFTER `vessel`
ADD COLUMN `tot_weight` DECIMAL(11,3) NULL DEFAULT NULL AFTER `packing_list_dated`;;

CREATE TABLE IF NOT EXISTS `msstone_app`.`commercial_invoice_item` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `excluido` CHAR(1) NOT NULL DEFAULT 'N',
  `date_record` DATETIME NOT NULL,
  `lot_transport_id` INT(11) NOT NULL,
  `client_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `quality_id` INT(11) NOT NULL,
  `value` DECIMAL(11,2) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_commercial_invoice_item_lot_transport1_idx` (`lot_transport_id` ASC),
  INDEX `fk_commercial_invoice_item_client1_idx` (`client_id` ASC),
  INDEX `fk_commercial_invoice_item_product1_idx` (`product_id` ASC),
  INDEX `fk_commercial_invoice_item_quality1_idx` (`quality_id` ASC),
  CONSTRAINT `fk_commercial_invoice_item_lot_transport1`
    FOREIGN KEY (`lot_transport_id`)
    REFERENCES `msstone_app`.`lot_transport` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_commercial_invoice_item_client1`
    FOREIGN KEY (`client_id`)
    REFERENCES `msstone_app`.`client` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_commercial_invoice_item_product1`
    FOREIGN KEY (`product_id`)
    REFERENCES `msstone_app`.`product` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_commercial_invoice_item_quality1`
    FOREIGN KEY (`quality_id`)
    REFERENCES `msstone_app`.`quality` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

-- 2014-07-04
ALTER TABLE `msstone_app`.`lot_transport` 
ADD COLUMN `client_consignee` VARCHAR(500) NULL DEFAULT NULL AFTER `client_notify_address`,
ADD COLUMN `commercial_invoice_date` DATE NULL DEFAULT NULL AFTER `tot_weight`;

ALTER TABLE `msstone_app`.`lot_transport` 
ADD COLUMN `commercial_invoice_number` VARCHAR(50) NULL DEFAULT NULL AFTER `commercial_invoice_date`,
ADD COLUMN `packing_list_ref` VARCHAR(50) NULL DEFAULT NULL AFTER `commercial_invoice_number`;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
