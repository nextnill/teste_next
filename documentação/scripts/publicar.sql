CREATE TABLE IF NOT EXISTS `user_client_group` (
  `user_id` INT(11) NOT NULL,
  `client_group_id` INT(11) NOT NULL,
  PRIMARY KEY (`user_id`, `client_group_id`),
  INDEX `fk_user_client_group_client_group_id_idx` (`client_group_id` ASC),
  CONSTRAINT `fk_user_client_group_user_id`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_client_group_client_group_id`
    FOREIGN KEY (`client_group_id`)
    REFERENCES `client_group` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci


CREATE TABLE IF NOT EXISTS `price_list` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `excluido` CHAR(1) NULL DEFAULT 'N',
  `client_id` INT(11) NULL DEFAULT NULL,
  `date_ref` DATE NULL DEFAULT NULL,
  `comments` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_price_list_client_idx` (`client_id` ASC),
  CONSTRAINT `fk_price_list_client`
    FOREIGN KEY (`client_id`)
    REFERENCES `client` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `price_list_quality` (
  `price_list_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `quality_id` INT(11) NOT NULL,
  `value` DECIMAL(11,2) NULL DEFAULT NULL,
  PRIMARY KEY (`price_list_id`, `product_id`, `quality_id`),
  INDEX `fk_price_list_quality_product_idx` (`product_id` ASC),
  INDEX `fk_price_list_quality_quality_idx` (`quality_id` ASC),
  CONSTRAINT `fk_price_list_quality_price_list`
    FOREIGN KEY (`price_list_id`)
    REFERENCES `price_list` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_price_list_quality_product`
    FOREIGN KEY (`product_id`)
    REFERENCES `product` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_price_list_quality_quality`
    FOREIGN KEY (`quality_id`)
    REFERENCES `quality` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;