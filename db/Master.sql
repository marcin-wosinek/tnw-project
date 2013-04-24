-- -----------------------------------------------------
-- Table `sys_role`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `sys_role` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(50) NOT NULL ,
  `id_parent` INT NULL ,
  `edit_allowed` TINYINT NOT NULL DEFAULT 1 ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) ,
  INDEX `fk_sys_role_sys_role1` (`id_parent` ASC) ,
  CONSTRAINT `fk_sys_role_sys_role1`
    FOREIGN KEY (`id_parent` )
    REFERENCES `sys_role` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `sys_account`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `sys_account` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `id_sys_role` INT NOT NULL ,
  `email` VARCHAR(255) NOT NULL ,
  `username` VARCHAR(255) NOT NULL ,
  `password` BINARY(20) NOT NULL ,
  `row_status` TINYINT NOT NULL DEFAULT 1 ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `email_UNIQUE` (`email` ASC) ,
  INDEX `fk_system_account_system_role` (`id_sys_role` ASC) ,
  UNIQUE INDEX `username_UNIQUE` (`username` ASC) ,
  CONSTRAINT `fk_system_account_system_role`
    FOREIGN KEY (`id_sys_role` )
    REFERENCES `sys_role` (`id` )
    ON DELETE RESTRICT
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `sys_session`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `sys_session` (
  `id` VARCHAR(45) NOT NULL ,
  `id_sys_account` INT NOT NULL ,
  `hostname` VARCHAR(15) NOT NULL ,
  `user_agent_hash` VARCHAR(45) NOT NULL ,
  `timestamp_created` INT UNSIGNED NOT NULL ,
  `timestamp_accessed` INT UNSIGNED NOT NULL ,
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) ,
  INDEX `fk_sys_session_sys_account1` (`id_sys_account` ASC) ,
  PRIMARY KEY (`id_sys_account`) ,
  CONSTRAINT `fk_sys_session_sys_account1`
    FOREIGN KEY (`id_sys_account` )
    REFERENCES `sys_account` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `sys_resource`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `sys_resource` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(255) NOT NULL ,
  `type` ENUM('page','model') NOT NULL ,
  `group` VARCHAR(45) NULL ,
  `id_parent` INT NULL ,
  `edit_allowed` TINYINT NOT NULL DEFAULT 1 ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_sys_resource_sys_resource1` (`id_parent` ASC) ,
  CONSTRAINT `fk_sys_resource_sys_resource1`
    FOREIGN KEY (`id_parent` )
    REFERENCES `sys_resource` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `sys_resource_privilege`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `sys_resource_privilege` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(255) NULL ,
  `id_sys_resource` INT NOT NULL ,
  `edit_allowed` TINYINT NOT NULL DEFAULT 1 ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_sys_privilege_sys_resource1` (`id_sys_resource` ASC) ,
  CONSTRAINT `fk_sys_privilege_sys_resource1`
    FOREIGN KEY (`id_sys_resource` )
    REFERENCES `sys_resource` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `sys_role_resource_privilege`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `sys_role_resource_privilege` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `id_sys_role` INT NOT NULL ,
  `id_sys_resource_privilege` INT NOT NULL ,
  `permission` ENUM('allow','deny') NOT NULL ,
  `edit_allowed` TINYINT NOT NULL DEFAULT 1 ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_sys_role_privilege_sys_role1` (`id_sys_role` ASC) ,
  INDEX `fk_sys_role_resource_privilege_sys_resource_privilege1` (`id_sys_resource_privilege` ASC) ,
  CONSTRAINT `fk_sys_role_privilege_sys_role1`
    FOREIGN KEY (`id_sys_role` )
    REFERENCES `sys_role` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_sys_role_resource_privilege_sys_resource_privilege1`
    FOREIGN KEY (`id_sys_resource_privilege` )
    REFERENCES `sys_resource_privilege` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `sys_translations`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sys_translations` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`keyword` VARCHAR( 256 ) NOT NULL ,
`en_US` VARCHAR( 256 ) NOT NULL ,
`nl_NL` VARCHAR( 256 ) NOT NULL
) ENGINE = INNODB
CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Compulsory Data
-- -----------------------------------------------------

INSERT INTO `sys_role` (
	`id` ,
	`name` ,
	`id_parent` ,
	`edit_allowed`
) VALUES (
	NULL , 'guest', NULL , '0' ),(
	NULL , 'kwgldev', '1', '0');

INSERT INTO `sys_resource` (
	`id` ,
	`name` ,
	`type` ,
	`group` ,
	`id_parent` ,
	`edit_allowed`
) VALUES (
	NULL , 'default', 'page', NULL , NULL , '0'), (
	NULL , 'kwgldev', 'page', NULL , NULL , '0'), (
	NULL , 'kwgldev-index-index', 'page', NULL , NULL , '0'), (
	NULL , 'default-index-index', 'page', NULL , NULL , '1'
);

INSERT INTO `sys_resource_privilege` (
	`id` ,
	`name` ,
	`id_sys_resource` ,
	`edit_allowed`
) VALUES (
	NULL , NULL , '1', '0'), (
	NULL , NULL , '2', '0'), (
	NULL , NULL , '3', '0'), (
	NULL , NULL , '4', '1');

INSERT INTO `sys_role_resource_privilege` (
	`id` ,
	`id_sys_role` ,
	`id_sys_resource_privilege` ,
	`permission` ,
	`edit_allowed`
) VALUES (
	NULL , '1', '3', 'allow', '0'), (
	NULL , '2', '2', 'allow', '0'), (
	NULL , '1', '4', 'allow', '1');

INSERT INTO `sys_account` (
	`id` ,
	`id_sys_role` ,
	`email` ,
	`username` ,
	`password` ,
	`row_status`
) VALUES (
NULL , '2', 'kwgldev@kominski.net', 'kwgldev@kominski.net', UNHEX('65a18c6bb2ae1473ec16b5a7b5382fa4a0021290') , '1');

INSERT INTO `sys_translations` (`id`, `keyword`, `en_US`, `nl_NL`) VALUES (NULL, 'txt_test', 'Test in English', 'Test in Dutch');