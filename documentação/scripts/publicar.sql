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