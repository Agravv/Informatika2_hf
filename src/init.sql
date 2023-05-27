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
-- ? funkcionális elvárás: Az adatbázis séma legalább 2 adatbázistáblából kell álljon. Mindegyik táblában legalább 3 oszlop szerepeljen, 
-- ? az adatbázis táblák között legalább 1 külső kulcs hivatkozás kell legyen.

-- -----------------------------------------------------
-- Table `project_management`.`user`
-- -----------------------------------------------------
-- ? Pontozási szempontok: Az adatbázisban NOT NULL constraint használata (indokolható helyen): 3p
-- ? Pontozási szempontok: Az adatbázisban auto_increment használata: 2p
-- ? Pontozási szempontok: Kiválasztott CSS (vagy egyéb, megjelenésre vonatkozó beállítás) felhasználónkénti tárolása: 5p
CREATE TABLE IF NOT EXISTS `project_management`.`user` (
  `user_id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `dark_mode` TINYINT NOT NULL DEFAULT 1,
  `access_level` ENUM('admin', 'guest', 'project_lead', 'employee') NOT NULL DEFAULT 'guest',
  PRIMARY KEY (`user_id`),
  UNIQUE INDEX `username_UNIQUE` (`username` ASC),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC))
ENGINE = InnoDB;

INSERT INTO `user` (`user_id`, `username`, `password`, `email`, `dark_mode`, `access_level`) VALUES
(1, 'admin', '$2y$10$R3R6q0AkhRaKHC4cl9H8W.jWgXJ4pSlk2D/yGN1x3GxkHdFy27oLW', 'admin@admin.hu', 1, 'admin'),
(2, 'béla_a', '$2y$10$uXv3a201mqgI/U6U7iIoUOzqfg58KhR74zeD9uq59S79IZayI35iy', 'bela@gmail.com', 0, 'guest'),
(3, 'Bori.ri', '$2y$10$iAUVSjRvQgSUyTAI24fzR.rayQEl2COvOvB.c0.ZmafW7ctZW.YkW', 'bori@gmail.com', 0, 'employee'),
(4, 'galanisz', '$2y$10$MfDD4BVtmO4SyKR1GwsaR.3uYtc6AqwwzC2xCKCoNJWZxlrynSAxG', 'gaal@gmail.com', 1, 'project_lead'),
(6, 'NagyGergo', '$2y$10$94Iwmajk54x2sbZEq1PrL.a/XOkQJ/0LbYVZAuO1zIE8pVsmWY5Pi', 'nagygeri@gmail.com', 0, 'employee'),
(7, 'bonifá.c', '$2y$10$PRVKOLSrGLjGnrsY0So4yO9Y5Zixwf6chto7.423w8AcHfbUdhQiG', 'boni@gmail.com', 1, 'employee'),
(8, 'kaRtsi', '$2y$10$osir48k01Dbh9kuxHTFanOOaA6GPz/2EfQLQG94N5WMsCg8kdP9T.', 'kartsi@gmail.com', 0, 'project_lead'),
(9, 'mihaG', '$2y$10$c6OoMEabPHYHUdsfhrHMZO6lMP9bLuR3bbDbcUURPv4RV6v4yYYmm', 'miha@gmail.com', 1, 'employee'),
(10, 'Bajza', '$2y$10$2psCxudTHZ48NvXovhSn9e9ilQcF7xYdekwjmjrA1yzGYCijtqwIS', 'bajza@gmail.com', 0, 'project_lead'),
(11, 'KeriGari', '$2y$10$LdWfoMFERRnIetLgjdklPeWqeblaDTxlRABGno1ev73R9HQIrqzIm', 'kertesz@gmail.com', 0, 'guest');


-- -----------------------------------------------------
-- Table `project_management`.`project`
-- -----------------------------------------------------
-- ? Pontozási szempontok: Az adatbázisban NOT NULL constraint használata (indokolható helyen): 3p
-- ? Pontozási szempontok: Az adatbázisban auto_increment használata: 2p
CREATE TABLE IF NOT EXISTS `project_management`.`project` (
  `project_id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(80) NOT NULL,
  `description` VARCHAR(800) NULL,
  `due_date` DATE NOT NULL,
  `status` ENUM('not_started', 'in_progress', 'finished') NOT NULL DEFAULT 'not_started',
  PRIMARY KEY (`project_id`))
ENGINE = InnoDB;

INSERT INTO `project` (`project_id`, `title`, `description`, `due_date`, `status`) VALUES
(1, 'donec', 'Donec lacinia sem ut nunc varius', '2023-06-01', 'in_progress'),
(2, 'Donec lorem lorem', 'Praesent porta diam', '2023-06-10', 'finished'),
(4, 'eu', 'Duis aliquet iaculis neque sed consectetur', '2025-07-01', 'not_started'),
(6, 'dolor ac', 'augue, sit amet mollis', '2023-05-21', 'in_progress'),
(7, 'eps', 'eposum', '2023-05-01', 'not_started'),
(8, 'Curabitu', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec consequat, orci nec commodo finibus, lectus est mattis ante, eget finibus lectus odio at urna. Aliquam ut mi non lorem imperdiet gravida eu eget nibh. Cras dui sapien, volutpat eu rutrum at, dapibus quis ante. Etiam a tellus viverra, pretium massa eu, pretium mi.', '2023-08-01', 'not_started'),
(9, 'prijekt', 'nec fringilla', '2023-05-20', 'in_progress'),
(10, 'rutrum ne', 'Sed sit amet vulputate nulla, maximus lobortis purus. Donec at erat pretium ipsum venenatis tempor. Ut id mauris posuere, eleifend lacus vitae, porta nibh.', '2023-06-10', 'finished'),
(11, 'Morbi gravida', 'Nulla varius, nisi at', '2024-01-19', 'not_started'),
(12, 'et netu', 'dapibus odio', '2023-07-05', 'in_progress'),
(13, 'bagam', 'Quisque elementum scelerisque nisi, a elementum dolor sodales tincidunt. Vivamus eu sem ut mauris viverra vestibulum in sit amet turpis. Nulla sit amet volutpat eros gravida.', '2023-04-01', 'not_started'),
(14, 'dictum', 'Nullam ultricies lacinia posuere.', '2023-05-19', 'in_progress'),
(15, 'miha', 'lorem epsum epsum lorem', '2023-05-19', 'in_progress'),
(16, 'Lorem epsum', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam maximus, ex vel aliquet bibendum, metus metus tempus nibh, in feugiat massa nunc eget sapien. Curabitur sit amet libero dolor. Suspendisse quis lectus dolor. Ut et est eget sapien tempus auctor a et nibh. Donec convallis est augue, nec blandit felis placerat a. Aliquam sagittis lacus ut magna porta, non ornare neque efficitur. Curabitur ultricies felis vitae nisi faucibus interdum. Donec tincidunt arcu in convallis tincidunt. Morbi porttitor eleifend iaculis. Nam at nunc in eros condimentum lobortis. Donec scelerisque mi lacus, eu vestibulum dui malesuada eu.  Quisque elementum scelerisque nisi, a elementum dolor sodales tincidunt. Vivamus eu sem ut mauris viverra vestibulum in sit amet turpis. Nulla sit amet volutpat eros gravid', '2023-06-02', 'finished'),
(18, 'lorem epsum epsum lorem', 'lorem epsum epsum lorem', '2023-05-19', 'not_started'),
(21, 'nisi ac', 'ac turpis egestas', '2023-05-19', 'in_progress'),
(22, 'epsum boripsum', 'epsum boripsum', '2023-05-19', 'not_started');


-- -----------------------------------------------------
-- Table `project_management`.`user_has_project`
-- -----------------------------------------------------
-- ? Pontozási szempontok: Az adatbázisban NOT NULL constraint használata (indokolható helyen): 3p
-- ? Pontozási szempontok: Az adatbázisban összetett kulcs használata: 5p
CREATE TABLE IF NOT EXISTS `project_management`.`user_has_project` (
  `user_id` INT NOT NULL,
  `project_id` INT NOT NULL,
  PRIMARY KEY (`user_id`, `project_id`),
  INDEX `fk_user_has_project_project1_idx` (`project_id` ASC),
  INDEX `fk_user_has_project_user_idx` (`user_id` ASC),
  CONSTRAINT `fk_user_has_project_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `project_management`.`user` (`user_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_user_has_project_project1`
    FOREIGN KEY (`project_id`)
    REFERENCES `project_management`.`project` (`project_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

INSERT INTO `user_has_project` (`user_id`, `project_id`) VALUES
(3, 6),
(3, 12),
(4, 2),
(4, 9),
(4, 10),
(4, 11),
(4, 12),
(4, 16),
(6, 10),
(6, 14),
(6, 16),
(6, 21),
(7, 1),
(7, 15),
(8, 1),
(8, 6),
(8, 14),
(9, 9),
(10, 15),
(10, 21),
(10, 22);


-- -----------------------------------------------------
-- Table `project_management`.`task`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `project_management`.`task` (
  `task_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(80) NOT NULL,
  `description` VARCHAR(800) NULL,
  `project_project_id` INT NOT NULL,
  PRIMARY KEY (`task_id`),
  INDEX `fk_task_project1_idx` (`project_project_id` ASC),
  CONSTRAINT `fk_task_project1`
    FOREIGN KEY (`project_project_id`)
    REFERENCES `project_management`.`project` (`project_id`)
    ON DELETE  CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

INSERT INTO `task` (`task_id`, `name`, `description`, `project_project_id`) VALUES
(1, 'Nunc auctor', 'nec at diam', 5),
(5, 'justo', 'Etiam convallis finibus', 5),
(6, 'dolor bibendum', 'mattis', 12),
(7, 'imus', 'tortor', 7),
(8, 'ipsum', 'Duis suscipit lectus at porta ante ipsum primis in faucibus', 1),
(9, 'lorem', 'sollicitudin sed', 13),
(10, 'boripsum', '', 22),
(11, 'sit amet sed magna', 'Ut consectetur sapien egestas justo volutpat facilisis. Maecenas sed magna ut elit suscipit', 8),
(12, 'rutrum neque', 'eu finibus mauris', 8),
(13, 'Praesent molestie', 'libero lorem', 11),
(14, 'Proin ante risus', 'ante', 21),
(15, 'posuere blandit leo', 'elementum tortor', 9),
(16, 'Nullam sit', 'amet', 6),
(17, 'vestibulum ligula', 'cubilia curae', 14),
(18, 'Integer pretium', 'pharetra erat ', 16),
(19, 'arcu at tortor', 'volutpat', 16),
(20, 'Cras dui nisi', 'Proin eleifend ac nunc viverra malesuada. Quisque luctus faucibus commodo.', 16);


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
