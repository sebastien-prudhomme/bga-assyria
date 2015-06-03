
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- assyria implementation : © <Your name here> <Your email address here>
--
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

-- This is the file where you are describing the database schema of your game
-- Basically, you just have to export from PhpMyAdmin your table structure and copy/paste
-- this export here.
-- Note that the database itself and the standard tables ("global", "stats", "gamelog" and "player") are
-- already created and must not be created here

-- Note: The database schema is created from this file when the game starts. If you modify this file,
--       you have to restart a game to see your changes in database.

ALTER TABLE `player` ADD `player_turn_order` tinyint(3) unsigned NOT NULL;
ALTER TABLE `player` ADD `player_hut` tinyint(3) unsigned NOT NULL;
ALTER TABLE `player` ADD `player_ziggurat_base` tinyint(3) unsigned NOT NULL;
ALTER TABLE `player` ADD `player_ziggurat_center` tinyint(3) unsigned NOT NULL;
ALTER TABLE `player` ADD `player_ziggurat_roof` tinyint(3) unsigned NOT NULL;
ALTER TABLE `player` ADD `player_camel` tinyint(3) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_offering` tinyint(3) unsigned NOT NULL DEFAULT '0';

CREATE TABLE IF NOT EXISTS `card` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_type` varchar(32) NOT NULL,
  `card_type_arg` int(11) NOT NULL,
  `card_location` varchar(32) NOT NULL,
  `card_location_arg` int(11) NOT NULL,
  PRIMARY KEY (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `hexagon` (
  `hexagon_q` tinyint(3) NOT NULL,
  `hexagon_r` tinyint(3) NOT NULL,
  `hexagon_type` varchar(32) NOT NULL,
  `hexagon_type_arg` int(11) NOT NULL,
  PRIMARY KEY (`hexagon_q`, `hexagon_r`, `hexagon_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `harvest` (
  `location` tinyint(3) NOT NULL,
  `player_id` int(11) NOT NULL,
  PRIMARY KEY (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `higher_dignitary` (
  `location` tinyint(3) NOT NULL,
  `player_id` int(11) NOT NULL,
  PRIMARY KEY (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `lower_dignitary` (
  `location` tinyint(3) NOT NULL,
  `player_id` int(11) NOT NULL,
  PRIMARY KEY (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `middle_dignitary` (
  `location` tinyint(3) NOT NULL,
  `player_id` int(11) NOT NULL,
  PRIMARY KEY (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `well` (
  `well_q` tinyint(3) NOT NULL,
  `well_r` tinyint(3) NOT NULL,
  `well_t` tinyint(3) NOT NULL,
  PRIMARY KEY (`well_q`, `well_r`, `well_t`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
