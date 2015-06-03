<?php
/**
 * ------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Assyria implementation: © Sebastien Prud'homme <daikinee@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * material.inc.php
 *
 */

$this->HEXAGON_SPACES = array(
    array('q' => 0, 'r' => 0, 'type' => 'palm', 'region' => 'outside'),
    array('q' => 0, 'r' => 1, 'type' => 'date', 'region' => 'outside'),
    array('q' => 0, 'r' => 2, 'type' => 'grape', 'region' => 'river1'),
    array('q' => 0, 'r' => 3, 'type' => 'salt', 'region' => 'inside'),
    array('q' => 0, 'r' => 4, 'type' => 'barley', 'region' => 'river2'),
    array('q' => 0, 'r' => 5, 'type' => 'grape', 'region' => 'outside'),
    array('q' => 1, 'r' => 0, 'type' => 'barley', 'region' => 'outside'),
    array('q' => 1, 'r' => 1, 'type' => 'salt', 'region' => 'river1'),
    array('q' => 1, 'r' => 2, 'type' => 'palm', 'region' => 'inside'),
    array('q' => 1, 'r' => 3, 'type' => 'grape', 'region' => 'river2'),
    array('q' => 1, 'r' => 4, 'type' => 'salt', 'region' => 'outside'),
    array('q' => 1, 'r' => 5, 'type' => 'palm', 'region' => 'outside'),
    array('q' => 2, 'r' => -1, 'type' => 'salt', 'region' => 'outside'),
    array('q' => 2, 'r' => 0, 'type' => 'date', 'region' => 'river1'),
    array('q' => 2, 'r' => 1, 'type' => 'grape', 'region' => 'inside'),
    array('q' => 2, 'r' => 2, 'type' => 'barley', 'region' => 'inside'),
    array('q' => 2, 'r' => 3, 'type' => 'date', 'region' => 'river2'),
    array('q' => 2, 'r' => 4, 'type' => 'barley', 'region' => 'outside'),
    array('q' => 2, 'r' => 5, 'type' => 'date', 'region' => 'outside'),
    array('q' => 3, 'r' => -1, 'type' => 'barley', 'region' => 'outside'),
    array('q' => 3, 'r' => 0, 'type' => 'palm', 'region' => 'river1'),
    array('q' => 3, 'r' => 1, 'type' => 'date', 'region' => 'inside'),
    array('q' => 3, 'r' => 2, 'type' => 'palm', 'region' => 'river2'),
    array('q' => 3, 'r' => 3, 'type' => 'grape', 'region' => 'outside'),
    array('q' => 3, 'r' => 4, 'type' => 'palm', 'region' => 'outside'),
    array('q' => 4, 'r' => -2, 'type' => 'grape', 'region' => 'outside'),
    array('q' => 4, 'r' => -1, 'type' => 'date', 'region' => 'outside'),
    array('q' => 4, 'r' => 0, 'type' => 'barley', 'region' => 'river1'),
    array('q' => 4, 'r' => 1, 'type' => 'grape', 'region' => 'inside'),
    array('q' => 4, 'r' => 2, 'type' => 'salt', 'region' => 'river2'),
    array('q' => 4, 'r' => 3, 'type' => 'barley', 'region' => 'outside'),
    array('q' => 4, 'r' => 4, 'type' => 'salt', 'region' => 'outside'),
    array('q' => 5, 'r' => -2, 'type' => 'salt', 'region' => 'outside'),
    array('q' => 5, 'r' => -1, 'type' => 'grape', 'region' => 'river1'),
    array('q' => 5, 'r' => 0, 'type' => 'palm', 'region' => 'inside'),
    array('q' => 5, 'r' => 1, 'type' => 'date', 'region' => 'inside'),
    array('q' => 5, 'r' => 2, 'type' => 'palm', 'region' => 'river2'),
    array('q' => 5, 'r' => 3, 'type' => 'date', 'region' => 'outside'),
    array('q' => 6, 'r' => -3, 'type' => 'palm', 'region' => 'outside'),
    array('q' => 6, 'r' => -2, 'type' => 'barley', 'region' => 'outside'),
    array('q' => 6, 'r' => -1, 'type' => 'date', 'region' => 'river1'),
    array('q' => 6, 'r' => 0, 'type' => 'salt', 'region' => 'inside'),
    array('q' => 6, 'r' => 1, 'type' => 'barley', 'region' => 'river2'),
    array('q' => 6, 'r' => 2, 'type' => 'grape', 'region' => 'outside'),
    array('q' => 6, 'r' => 3, 'type' => 'palm', 'region' => 'outside'),
    array('q' => 7, 'r' => -3, 'type' => 'salt', 'region' => 'outside'),
    array('q' => 7, 'r' => -2, 'type' => 'grape', 'region' => 'outside'),
    array('q' => 7, 'r' => -1, 'type' => 'palm', 'region' => 'river1'),
    array('q' => 7, 'r' => 0, 'type' => 'grape', 'region' => 'inside'),
    array('q' => 7, 'r' => 1, 'type' => 'salt', 'region' => 'river2'),
    array('q' => 7, 'r' => 2, 'type' => 'date', 'region' => 'outside'),
    array('q' => 8, 'r' => -4, 'type' => 'date', 'region' => 'outside'),
    array('q' => 8, 'r' => -3, 'type' => 'palm', 'region' => 'outside'),
    array('q' => 8, 'r' => -2, 'type' => 'salt', 'region' => 'river1'),
    array('q' => 8, 'r' => -1, 'type' => 'barley', 'region' => 'inside'),
    array('q' => 8, 'r' => 0, 'type' => 'date', 'region' => 'river2'),
    array('q' => 8, 'r' => 1, 'type' => 'grape', 'region' => 'outside'),
    array('q' => 8, 'r' => 2, 'type' => 'palm', 'region' => 'outside'),
    array('q' => 9, 'r' => -4, 'type' => 'barley', 'region' => 'outside'),
    array('q' => 9, 'r' => -3, 'type' => 'date', 'region' => 'outside'),
    array('q' => 9, 'r' => -2, 'type' => 'grape', 'region' => 'river1'),
    array('q' => 9, 'r' => -1, 'type' => 'salt', 'region' => 'inside'),
    array('q' => 9, 'r' => 0, 'type' => 'barley', 'region' => 'river2'),
    array('q' => 9, 'r' => 1, 'type' => 'salt', 'region' => 'outside')
);

$this->STARTING_SPACES = array(
    2 => array(
        array('q' => 1, 'r' => 0),
        array('q' => 1, 'r' => 4),
        array('q' => 4, 'r' => -1),
        array('q' => 4, 'r' => 3)
    ),
    3 => array(
        array('q' => 1, 'r' => 4),
        array('q' => 4, 'r' => -1),
        array('q' => 6, 'r' => 2)
    ),
    4 => array(
        array('q' => 1, 'r' => 0),
        array('q' => 1, 'r' => 4),
        array('q' => 8, 'r' => -3),
        array('q' => 8, 'r' => 1)
    )
);

$this->WELL_SPACES = array(
    array('q' => 1, 'r' => 0, 't' => -1),
    array('q' => 1, 'r' => 1, 't' => -1),
    array('q' => 1, 'r' => 4, 't' => -1),
    array('q' => 2, 'r' => 0, 't' => -1),
    array('q' => 2, 'r' => 3, 't' => -1),
    array('q' => 2, 'r' => 4, 't' => -1),
    array('q' => 3, 'r' => -1, 't' => -1),
    array('q' => 3, 'r' => 3, 't' => -1),
    array('q' => 3, 'r' => 4, 't' => -1),
    array('q' => 4, 'r' => -1, 't' => -1),
    array('q' => 4, 'r' => 2, 't' => -1),
    array('q' => 4, 'r' => 3, 't' => -1),
    array('q' => 5, 'r' => -2, 't' => -1),
    array('q' => 5, 'r' => -1, 't' => -1),
    array('q' => 5, 'r' => 2, 't' => -1),
    array('q' => 5, 'r' => 3, 't' => -1),
    array('q' => 6, 'r' => -2, 't' => -1),
    array('q' => 6, 'r' => 2, 't' => -1),
    array('q' => 7, 'r' => -3, 't' => -1),
    array('q' => 7, 'r' => -2, 't' => -1),
    array('q' => 7, 'r' => 1, 't' => -1),
    array('q' => 7, 'r' => 2, 't' => -1),
    array('q' => 8, 'r' => -3, 't' => -1),
    array('q' => 8, 'r' => -2, 't' => -1),
    array('q' => 8, 'r' => 1, 't' => -1),
    array('q' => 9, 'r' => -4, 't' => -1),
    array('q' => 9, 'r' => -3, 't' => -1),
    array('q' => 9, 'r' => 0, 't' => -1),
    array('q' => 9, 'r' => 1, 't' => -1),
    array('q' => 0, 'r' => 1, 't' => 1),
    array('q' => 0, 'r' => 4, 't' => 1),
    array('q' => 0, 'r' => 5, 't' => 1),
    array('q' => 1, 'r' => 0, 't' => 1),
    array('q' => 1, 'r' => 4, 't' => 1),
    array('q' => 1, 'r' => 5, 't' => 1),
    array('q' => 2, 'r' => 0, 't' => 1),
    array('q' => 2, 'r' => 3, 't' => 1),
    array('q' => 2, 'r' => 4, 't' => 1),
    array('q' => 3, 'r' => -1, 't' => 1),
    array('q' => 3, 'r' => 0, 't' => 1),
    array('q' => 3, 'r' => 3, 't' => 1),
    array('q' => 3, 'r' => 4, 't' => 1),
    array('q' => 4, 'r' => -1, 't' => 1),
    array('q' => 4, 'r' => 3, 't' => 1),
    array('q' => 5, 'r' => -2, 't' => 1),
    array('q' => 5, 'r' => -1, 't' => 1),
    array('q' => 5, 'r' => 2, 't' => 1),
    array('q' => 5, 'r' => 3, 't' => 1),
    array('q' => 6, 'r' => -2, 't' => 1),
    array('q' => 6, 'r' => -1, 't' => 1),
    array('q' => 6, 'r' => 2, 't' => 1),
    array('q' => 7, 'r' => -3, 't' => 1),
    array('q' => 7, 'r' => -2, 't' => 1),
    array('q' => 7, 'r' => 1, 't' => 1),
    array('q' => 7, 'r' => 2, 't' => 1),
    array('q' => 8, 'r' => -3, 't' => 1),
    array('q' => 8, 'r' => -2, 't' => 1),
    array('q' => 8, 'r' => 1, 't' => 1)
);

$this->EXPANSION_CARDS = array(
    array('type' => 'expansion', 'type_arg' => 2, 'nbr' => 2),
    array('type' => 'expansion', 'type_arg' => 3, 'nbr' => 4),
    array('type' => 'expansion', 'type_arg' => 4, 'nbr' => 2)
);

$this->FOOD_CARDS = array(
    array('type' => 'grape', 'type_arg' => 1, 'nbr' => 3),
    array('type' => 'grape', 'type_arg' => 2, 'nbr' => 2),
    array('type' => 'grape', 'type_arg' => 3, 'nbr' => 2),
    array('type' => 'palm', 'type_arg' => 1, 'nbr' => 3),
    array('type' => 'palm', 'type_arg' => 2, 'nbr' => 2),
    array('type' => 'palm', 'type_arg' => 3, 'nbr' => 2),
    array('type' => 'salt', 'type_arg' => 1, 'nbr' => 3),
    array('type' => 'salt', 'type_arg' => 2, 'nbr' => 2),
    array('type' => 'salt', 'type_arg' => 3, 'nbr' => 2),
    array('type' => 'barley', 'type_arg' => 1, 'nbr' => 3),
    array('type' => 'barley', 'type_arg' => 2, 'nbr' => 2),
    array('type' => 'barley', 'type_arg' => 3, 'nbr' => 2),
    array('type' => 'date', 'type_arg' => 1, 'nbr' => 3),
    array('type' => 'date', 'type_arg' => 2, 'nbr' => 2),
    array('type' => 'date', 'type_arg' => 3, 'nbr' => 2),
    array('type' => 'wild', 'type_arg' => 1, 'nbr' => 5)
);

$this->DRAFT_CARD_NUMBER = array(
    2 => 2,
    3 => 3,
    4 => 4
);

$this->HUT_NUMBER = 10;

$this->HIGHER_DIGNITARY_COST = 4;
$this->MIDDLE_DIGNITARY_COST = 3;
$this->LOWER_DIGNITARY_COST = 2;

$this->PLOW_COST = 2;

$this->SOWING_CARD_NUMBER = array(
    2 => 3,
    3 => 4,
    4 => 5
);

$this->REIGN_NUMBER = 3;

$this->REIGN_TURN_NUMBER = array(
    1 => 2,
    2 => 3,
    3 => 3
);

$this->CARD_TYPE_TRANSLATIONS = array(
    'barley' => clienttranslate('barley(s)'),
    'date' => clienttranslate('date(s)'),
    'grape' => clienttranslate('grape(s)'),
    'palm' => clienttranslate('palm(s)'),
    'plow' => clienttranslate('plow'),
    'salt' => clienttranslate('salt(s)'),
    'wild' => clienttranslate('wild')
);

$this->HEXAGON_TYPE_TRANSLATIONS = array(
    'barley' => clienttranslate('barley'),
    'date' => clienttranslate('date'),
    'grape' => clienttranslate('grape'),
    'palm' => clienttranslate('palm'),
    'salt' => clienttranslate('salt')
);

$this->OBSOLETE_TRANSLATIONS = array(
    clienttranslate('${player_name} chooses a starting space on ${hexagon_icon} ${hexagon_type}'),
    clienttranslate('${player_name} places a hut on ${hexagon_icon} ${hexagon_type}'),
    clienttranslate('${player_name} resupplies a hut on ${hexagon_icon} ${hexagon_type}'),
    clienttranslate('${actplayer} must use a plow/food card to resupply huts'),
    clienttranslate('${you} must use a plow/food card to resupply huts')
);

$this->WELL_NUMBER = array(
    2 => 8,
    3 => 12,
    4 => 16
);

$this->ZIGGURAT_BASE_COST = 6;
$this->ZIGGURAT_CENTER_COST = 3;
$this->ZIGGURAT_ROOF_COST = 2;

$this->ZIGGURAT_BASE_NUMBER = 4;
$this->ZIGGURAT_CENTER_NUMBER = 4;
$this->ZIGGURAT_ROOF_NUMBER = 4;
