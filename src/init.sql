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
ENGINE = InnoDB
ROW_FORMAT = DEFAULT;

INSERT INTO `user` (`user_id`, `username`, `password`, `email`, `dark_mode`, `access_level`) VALUES
(1, 'admin', '$2y$10$6kGHn0/1ppJvVBnII/kR4.if1p.frLAGInkYcsrY.2K8CbQWjoa4y', 'admin@admin.hu', b'1', 'admin'),
(2, 'béla_a', '$2y$10$uXv3a201mqgI/U6U7iIoUOzqfg58KhR74zeD9uq59S79IZayI35iy', 'bela@gmail.com', b'0', 'guest'),
(3, 'Bori.ri', '$2y$10$iAUVSjRvQgSUyTAI24fzR.rayQEl2COvOvB.c0.ZmafW7ctZW.YkW', 'bori@gmail.com', b'0', 'employee'),
(4, 'galanisz', '$2y$10$MfDD4BVtmO4SyKR1GwsaR.3uYtc6AqwwzC2xCKCoNJWZxlrynSAxG', 'gaal@gmail.com', b'1', 'project_lead'),
(5, 'pali12', '$2y$10$Bu6vytBdzZlL2UrHxVF/ee8vTuxqJFxbbYhde25hQfpHky0LUkVKK', 'pali@gmail.com', b'1', 'project_lead'),
(6, 'NagyGergo', '$2y$10$94Iwmajk54x2sbZEq1PrL.a/XOkQJ/0LbYVZAuO1zIE8pVsmWY5Pi', 'nagygeri@gmail.com', b'0', 'employee'),
(7, 'bonifá.c', '$2y$10$PRVKOLSrGLjGnrsY0So4yO9Y5Zixwf6chto7.423w8AcHfbUdhQiG', 'boni@gmail.com', b'1', 'employee'),
(8, 'kaRtsi', '$2y$10$osir48k01Dbh9kuxHTFanOOaA6GPz/2EfQLQG94N5WMsCg8kdP9T.', 'kartsi@gmail.com', b'0', 'project_lead'),
(9, 'mihaG', '$2y$10$c6OoMEabPHYHUdsfhrHMZO6lMP9bLuR3bbDbcUURPv4RV6v4yYYmm', 'miha@gmail.com', b'1', 'employee');


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

INSERT INTO `project` (`project_id`, `title`, `description`, `due_date`, `status`) VALUES
(1, 'dolor sit', 'Donec lacinia sem ut nunc varius', '2023-06-01', 'in_progress'),
(2, 'Donec lorem lorem', 'finibus', '2023-06-10', 'in_progress'),
(3, 'semper', 'dolor quam sit amet elit', '2023-08-31', 'not_started'),
(4, 'eu', 'Duis aliquet iaculis neque sed consectetur', '2025-07-01', 'in_progress'),
(5, 'Lorem epsum', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam maximus, ex vel aliquet bibendum, metus metus tempus nibh, in feugiat massa nunc eget sapien. Curabitur sit amet libero dolor. Suspendisse quis lectus dolor. Ut et est eget sapien tempus auctor a et nibh. Donec convallis est augue, nec blandit felis placerat a. Aliquam sagittis lacus ut magna porta, non ornare neque efficitur. Curabitur ultricies felis vitae nisi faucibus interdum. Donec tincidunt arcu in convallis tincidunt. Morbi porttitor eleifend iaculis. Nam at nunc in eros condimentum lobortis. Donec scelerisque mi lacus, eu vestibulum dui malesuada eu.  Quisque elementum scelerisque nisi, a elementum dolor sodales tincidunt. Vivamus eu sem ut mauris viverra vestibulum in sit amet turpis. Nulla sit amet volutpat eros gravid', '2023-06-01', 'finished'),
(6, 'epsum', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam maximus, ex vel aliquet bibendum, metus metus tempus nibh, in feugiat massa nunc eget sapien. Curabitur sit amet libero dolor. Suspendisse quis lectus dolor. Ut et est eget sapien tempus auctor a et nibh. Donec convallis est augue, nec blandit felis placerat a. Aliquam sagittis lacus ut magna porta, non ornare neque efficitur. Curabitur ultricies felis vitae nisi faucibus interdum. Donec tincidunt arcu in convallis tincidunt. Morbi porttitor eleifend iaculis. Nam at nunc in eros condimentum lobortis. Donec scelerisque mi lacus, eu vestibulum dui malesuada eu.', '2023-05-19', 'not_started'),
(7, 'lorem', 'Quisque elementum scelerisque nisi, a elementum dolor sodales tincidunt. Vivamus eu sem ut mauris viverra vestibulum in sit amet turpis. Nulla sit amet volutpat eros gravida.', '2023-04-01', 'in_progress'),
(8, 'Curabitu', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec consequat, orci nec commodo finibus, lectus est mattis ante, eget finibus lectus odio at urna. Aliquam ut mi non lorem imperdiet gravida eu eget nibh. Cras dui sapien, volutpat eu rutrum at, dapibus quis ante. Etiam a tellus viverra, pretium massa eu, pretium mi.', '2023-08-01', 'in_progress'),
(9, 'Loremeps', 'Pellentesque eu nibh at magna elementum porttitor. Curabitur a felis in neque condimentum sollicitudin a vel tellus. Ut tincidunt magna id orci malesuada rutrum.', '2023-05-01', 'in_progress'),
(10, 'hegdif', 'Sed sit amet vulputate nulla, maximus lobortis purus. Donec at erat pretium ipsum venenatis tempor. Ut id mauris posuere, eleifend lacus vitae, porta nibh.', '2023-06-10', 'not_started'),
(11, 'Morbi gravida', 'Nulla varius, nisi at', '2024-01-19', 'not_started'),
(12, 'et netu', 'dapibus odio', '2023-07-05', 'not_started'),
(13, 'Nullam', 'nec fringilla', '2023-06-01', 'not_started');


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
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_user_has_project_project1`
    FOREIGN KEY (`project_project_id`)
    REFERENCES `project_management`.`project` (`project_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

INSERT INTO `user_has_project` (`user_id`, `project_project_id`) VALUES
(3, 7),
(3, 9),
(6, 5),
(7, 1),
(9, 2),
(9, 4),
(9, 8);


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
