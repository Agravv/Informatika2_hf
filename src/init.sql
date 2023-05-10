-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema project_management
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema project_management
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `project_management` DEFAULT CHARACTER SET utf8 ;
USE `project_management` ;

-- -----------------------------------------------------
-- Table `project_management`.`user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `project_management`.`user` (
  `user_id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `dark_mode` BIT(1) NOT NULL DEFAULT 0,
  `access_level` ENUM('admin', 'guest', 'project_lead', 'employee') NOT NULL DEFAULT 'guest',
  PRIMARY KEY (`user_id`),
  UNIQUE INDEX `username_UNIQUE` (`username` ASC),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `project_management`.`project`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `project_management`.`project` (
  `project_id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(80) NOT NULL,
  `description` VARCHAR(800) NULL,
  `due_date` DATE NOT NULL,
  `status` ENUM('not_started', 'in_progress', 'finished') NOT NULL DEFAULT 'not_started',
  PRIMARY KEY (`project_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `project_management`.`user_has_project`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `project_management`.`user_has_project` (
  `user_id` INT NOT NULL,
  `project_project_id` INT NOT NULL,
  PRIMARY KEY (`user_id`, `project_project_id`),
  INDEX `fk_user_has_project_user_idx` (`user_id` ASC),
  INDEX `fk_user_has_project_project1_idx` (`project_project_id` ASC),
  CONSTRAINT `fk_user_has_project_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `project_management`.`user` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_has_project_project1`
    FOREIGN KEY (`project_project_id`)
    REFERENCES `project_management`.`project` (`project_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
