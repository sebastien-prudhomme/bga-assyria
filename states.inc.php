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
 * states.inc.php
 *
 */

$machinestates = array(
    1 => array(
        'name' => 'gameSetup',
        'description' => clienttranslate('Game setup'),
        'type' => 'manager',
        'action' => 'stGameSetup',
        'transitions' => array('' => 2)
    ),
    2 => array(
        'name' => 'chooseStartingSpace',
        'description' => clienttranslate('${actplayer} must choose a starting space'),
        'descriptionmyturn' => clienttranslate('${you} must choose a starting space'),
        'type' => 'activeplayer',
        'possibleactions' => array('chooseStartingSpace'),
        'transitions' => array('startingSpaceChosen' => 3),
        'args' => 'argChooseStartingSpace'
    ),
    3 => array(
        'name' => 'startingSpaceChosen',
        'description' => '',
        'type' => 'game',
        'action' => 'stStartingSpaceChosen',
        'transitions' => array('chooseStartingSpace' => 2, 'startingSpaceChosen' => 3, 'chooseFoodCard' => 4)
    ),
    4 => array(
        'name' => 'chooseFoodCard',
        'description' => clienttranslate('${actplayer} must choose a card'),
        'descriptionmyturn' => clienttranslate('${you} must choose a card'),
        'type' => 'activeplayer',
        'possibleactions' => array('chooseFoodCard'),
        'transitions' => array('foodCardChosen' => 5),
        'args' => 'argChooseFoodCard'
    ),
    5 => array(
        'name' => 'foodCardChosen',
        'description' => '',
        'type' => 'game',
        'action' => 'stFoodCardChosen',
        'transitions' => array('chooseFoodCard' => 4, 'foodCardChosen' => 5, 'chooseFoodColumn' => 6)
    ),
    6 => array(
        'name' => 'chooseFoodColumn',
        'description' => clienttranslate('${actplayer} must choose a column of cards'),
        'descriptionmyturn' => clienttranslate('${you} must choose a column of cards'),
        'type' => 'activeplayer',
        'possibleactions' => array('chooseFoodColumn'),
        'transitions' => array('foodColumnChosen' => 7),
        'args' => 'argChooseFoodColumn'
    ),
    7 => array(
        'name' => 'foodColumnChosen',
        'description' => '',
        'type' => 'game',
        'action' => 'stFoodColumnChosen',
        'transitions' => array('chooseFoodColumn' => 6, 'placeHut' => 8, 'hutPlaced' => 9, 'useFoodCard' => 10, 'foodCardUsed' => 11, 'placeWell' => 14, 'countRevenuePrestige' => 17)
    ),
    8 => array(
        'name' => 'placeHut',
        'description' => clienttranslate('${actplayer} must place ${hut_number} hut(s)'),
        'descriptionmyturn' => clienttranslate('${you} must place ${hut_number} hut(s)'),
        'type' => 'activeplayer',
        'possibleactions' => array('placeHut'),
        'transitions' => array('hutPlaced' => 9),
        'args' => 'argPlaceHut'
    ),
    9 => array(
        'name' => 'hutPlaced',
        'description' => '',
        'type' => 'game',
        'action' => 'stHutPlaced',
        'transitions' => array('placeHut' => 8, 'hutPlaced' => 9, 'useFoodCard' => 10, 'foodCardUsed' => 11, 'placeWell' => 14, 'countRevenuePrestige' => 17)
    ),
    10 => array(
        'name' => 'useFoodCard',
        'description' => clienttranslate('${actplayer} must use a plow/food card to resupply its huts'),
        'descriptionmyturn' => clienttranslate('${you} must use a plow/food card to resupply your huts'),
        'type' => 'activeplayer',
        'possibleactions' => array('useFoodCard'),
        'transitions' => array('foodCardUsed' => 11),
        'args' => 'argUseFoodCard'
    ),
    11 => array(
        'name' => 'foodCardUsed',
        'description' => '',
        'type' => 'game',
        'action' => 'stFoodCardUsed',
        'transitions' => array('resupplyHut' => 12, 'hutResupplied' => 13)
    ),
    12 => array(
        'name' => 'resupplyHut',
        'description' => clienttranslate('${actplayer} must resupply ${hut_number} hut(s)'),
        'descriptionmyturn' => clienttranslate('${you} must resupply ${hut_number} hut(s)'),
        'type' => 'activeplayer',
        'possibleactions' => array('resupplyHut'),
        'transitions' => array('hutResupplied' => 13),
        'args' => 'argResupplyHut'
    ),
    13 => array(
        'name' => 'hutResupplied',
        'description' => '',
        'type' => 'game',
        'action' => 'stHutResupplied',
        'transitions' => array('resupplyHut' => 12, 'hutResupplied' => 13, 'useFoodCard' => 10, 'foodCardUsed' => 11, 'placeWell' => 14, 'countRevenuePrestige' => 17)
    ),
    14 => array(
        'name' => 'placeWell',
        'description' => clienttranslate('${actplayer} may place ${well_number} well(s) or pass'),
        'descriptionmyturn' => clienttranslate('${you} may place ${well_number} well(s)'),
        'type' => 'activeplayer',
        'possibleactions' => array('placeWell', 'passPlaceWell'),
        'transitions' => array('wellPlaced' => 15, 'placeWellPassed' => 17),
        'args' => 'argPlaceWell'
    ),
    15 => array(
        'name' => 'wellPlaced',
        'description' => '',
        'type' => 'game',
        'action' => 'stWellPlaced',
        'transitions' => array('placeWell' => 14, 'countRevenuePrestige' => 17)
    ),
    16 => array(
        'name' => 'placeWellPassed',
        'description' => '',
        'type' => 'game',
        'action' => 'stPlaceWellPassed',
        'transitions' => array('countRevenuePrestige' => 17)
    ),
    17 => array(
        'name' => 'countRevenuePrestige',
        'description' => '',
        'type' => 'game',
        'action' => 'stCountRevenuePrestige',
        'transitions' => array('placeHut' => 8, 'hutPlaced' => 9, 'useFoodCard' => 10, 'foodCardUsed' => 11, 'placeWell' => 14, 'countRevenuePrestige' => 17, 'performAction' => 18, 'endPerformAction' => 29)
    ),
    18 => array(
        'name' => 'performAction',
        'description' => clienttranslate('${actplayer} may build/extend a ziggurat, influence a dignitary, make an offering, buy a plow/food card or pass'),
        'descriptionmyturn' => clienttranslate('${you} may build/extend a ziggurat, influence a dignitary, make an offering, buy a plow/food card'),
        'type' => 'activeplayer',
        'possibleactions' => array('buildZiggurat', 'extendZigguratCenter', 'extendZigguratRoof', 'influenceHigherDignitary', 'influenceMiddleDignitary', 'influenceLowerDignitary', 'makeOffering', 'buyFoodCard', 'buyPlowCard', 'passPerformAction'),
        'transitions' => array('zigguratBuilt' => 19, 'zigguratCenterExtended' => 20, 'zigguratRoofExtended' => 21, 'higherDignitaryInfluenced' => 22, 'middleDignitaryInfluenced' => 23, 'lowerDignitaryInfluenced' => 24, 'offeringMade' => 25, 'foodCardBought' => 26, 'plowCardBought' => 27, 'performActionPassed' => 28),
        'args' => 'argPerformAction'
    ),
    19 => array(
        'name' => 'zigguratBuilt',
        'description' => '',
        'type' => 'game',
        'action' => 'stZigguratBuilt',
        'transitions' => array('performAction' => 18, 'endPerformAction' => 29)
    ),
    20 => array(
        'name' => 'zigguratCenterExtended',
        'description' => '',
        'type' => 'game',
        'action' => 'stZigguratCenterExtended',
        'transitions' => array('performAction' => 18, 'endPerformAction' => 29)
    ),
    21 => array(
        'name' => 'zigguratRoofExtended',
        'description' => '',
        'type' => 'game',
        'action' => 'stZigguratRoofExtended',
        'transitions' => array('performAction' => 18, 'endPerformAction' => 29)
    ),
    22 => array(
        'name' => 'higherDignitaryInfluenced',
        'description' => '',
        'type' => 'game',
        'action' => 'stHigherDignitaryInfluenced',
        'transitions' => array('performAction' => 18, 'endPerformAction' => 29)
    ),
    23 => array(
        'name' => 'middleDignitaryInfluenced',
        'description' => '',
        'type' => 'game',
        'action' => 'stMiddleDignitaryInfluenced',
        'transitions' => array('performAction' => 18, 'endPerformAction' => 29)
    ),
    24 => array(
        'name' => 'lowerDignitaryInfluenced',
        'description' => '',
        'type' => 'game',
        'action' => 'stLowerDignitaryInfluenced',
        'transitions' => array('performAction' => 18, 'endPerformAction' => 29)
    ),
    25 => array(
        'name' => 'offeringMade',
        'description' => '',
        'type' => 'game',
        'action' => 'stOfferingMade',
        'transitions' => array('performAction' => 18, 'endPerformAction' => 29)
    ),
    26 => array(
        'name' => 'foodCardBought',
        'description' => '',
        'type' => 'game',
        'action' => 'stFoodCardBought',
        'transitions' => array('performAction' => 18, 'endPerformAction' => 29)
    ),
    27 => array(
        'name' => 'plowCardBought',
        'description' => '',
        'type' => 'game',
        'action' => 'stPlowCardBought',
        'transitions' => array('performAction' => 18, 'endPerformAction' => 29)
    ),
    28 => array(
        'name' => 'performActionPassed',
        'description' => '',
        'type' => 'game',
        'action' => 'stPerformActionPassed',
        'transitions' => array('endPerformAction' => 29)
    ),
    29 => array(
        'name' => 'endPerformAction',
        'description' => '',
        'type' => 'game',
        'action' => 'stEndPerformAction',
        'transitions' => array('performAction' => 18, 'endPerformAction' => 29, 'chooseFoodColumn' => 6, 'gameEnd' => 99),
        'updateGameProgression' => true
    ),
    99 => array(
        'name' => 'gameEnd',
        'description' => clienttranslate('End of game'),
        'type' => 'manager',
        'action' => 'stGameEnd',
        'args' => 'argGameEnd'
    )
);
