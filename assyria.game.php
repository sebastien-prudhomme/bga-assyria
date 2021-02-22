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
 * assyria.game.php
 *
 */
require_once( APP_GAMEMODULE_PATH . 'module/table/table.game.php' );

class assyria extends Table {

    function __construct() {
        parent::__construct();

        self::initGameStateLabels(array(
            "well_stock" => 10,
            "reign" => 11,
            "turn" => 12,
            "counter" => 13,
            "card_id" => 14,
            "offering" => 15
        ));

        $this->cards = self::getNew("module.common.deck");
        $this->cards->init("card");
    }

    protected function getGameName() {
        return "assyria";
    }

    protected function setupNewGame($players, $options = array()) {
        $default_colors = array("ff0000", "008000", "0000ff", "ffff00");

        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, player_turn_order, player_hut, player_ziggurat_base, player_ziggurat_center, player_ziggurat_roof) VALUES ";
        $player_turn_order = 1;
        $values = array();
        foreach ($players as $player_id => $player) {
            $color = array_shift($default_colors);
            $values[] = "('" . $player_id . "','$color','" . $player['player_canal'] . "','" . addslashes($player['player_name']) . "','" . addslashes($player['player_avatar']) . "','" . $player_turn_order . "','" . $this->HUT_NUMBER . "','" . $this->ZIGGURAT_BASE_NUMBER . "','" . $this->ZIGGURAT_CENTER_NUMBER . "','" . $this->ZIGGURAT_ROOF_NUMBER . "')";
            $player_turn_order++;
        }
        $sql .= implode($values, ',');
        self::DbQuery($sql);
        self::reloadPlayersBasicInfos();

        self::setGameStateInitialValue('well_stock', $this->WELL_NUMBER[count($players)]);
        self::setGameStateInitialValue('reign', 1);
        self::setGameStateInitialValue('turn', 1);

        self::initStat("player", "pointsScoredWithHuts", 0);
        self::initStat("player", "pointsScoredWithZigguratTiles", 0);
        self::initStat("player", "pointsScoredWithWells", 0);
        self::initStat("player", "pointsScoredWithInfluence", 0);
        self::initStat("player", "pointsScoredWithHigherDignitary", 0);
        self::initStat("player", "pointsScoredWithOfferings", 0);
        self::initStat("player", "pointsScoredWithRemainingPlow", 0);
        self::initStat("player", "pointsScoredWithRemainingCamels", 0);

        // RULES: (4 players) place the Bonus card beside the card
        if (count($players) == 4) {
            $this->cards->createCards(array(array('type' => 'bonus', 'type_arg' => 4, 'nbr' => 1)), 'bonus_discard');
        }

        // RULES: place one Expansion card with a value of 4 in the first
        //        expansion space
        $this->cards->createCards(array(array('type' => 'expansion', 'type_arg' => 4, 'nbr' => 1)), 'expansion', 0);

        // RULES: shuffle the remaining Expansion cards
        $this->cards->createCards($this->EXPANSION_CARDS, 'expansion_deck');
        $this->cards->shuffle('expansion_deck');

        foreach (array_keys($players) as $player_id) {
            $this->cards->createCards(array(array('type' => 'plow', 'type_arg' => 1, 'nbr' => 1)), 'hand', $player_id);
        }

        $this->cards->createCards($this->FOOD_CARDS, 'deck');
        $this->cards->shuffle('deck');

        self::_setupSowing();

        if (count($players) < 4) {
            foreach ($this->HEXAGON_SPACES as $hexagon) {
                $hexagon_q = $hexagon['q'];

                if (($hexagon_q == 8) || ($hexagon_q == 9)) {
                    $hexagon_r = $hexagon['r'];

                    $sql = "INSERT INTO hexagon (hexagon_q, hexagon_r, hexagon_type) VALUES ($hexagon_q, $hexagon_r, 'forbidden')";
                    self::DbQuery($sql);
                }
            }

            if (count($players) < 3) {
                foreach ($this->HEXAGON_SPACES as $hexagon) {
                    $hexagon_q = $hexagon['q'];

                    if (($hexagon_q == 6) || ($hexagon_q == 7)) {
                        $hexagon_r = $hexagon['r'];

                        $sql = "INSERT INTO hexagon (hexagon_q, hexagon_r, hexagon_type) VALUES ($hexagon_q, $hexagon_r, 'forbidden')";
                        self::DbQuery($sql);
                    }
                }
            }
        }

        self::activeFirstPlayer();
    }

    private function _setupSowing() {
        $playersNumber = self::getPlayersNumber();
        $sowing_card_number = $this->SOWING_CARD_NUMBER[$playersNumber];

        for ($i = 0; $i < 2; $i++) {
            for ($j = 0; $j < $sowing_card_number; $j++) {
                // Reshuffle discard into deck?
                $cardNumber = $this->cards->countCardInLocation('deck');

                if ($cardNumber == 0) {
                    self::notifyAllPlayers("waitAnimations", "", array());

                    $this->cards->moveAllCardsInLocation('discard', 'deck');
                    $this->cards->shuffle('deck');

                    self::notifyAllPlayers("deckReshuffled", "", array());
                }

                $sowing_card = $this->cards->pickCardForLocation('deck', 'sowing', ($i * 5) + $j);

                self::notifyAllPlayers("sowingPicked", "", array(
                    'sowing_card' => $sowing_card
                ));
            }

            self::notifyAllPlayers("waitAnimations", "", array());

            $sql = "SELECT card_id AS id, card_type AS type, card_type_arg AS type_arg, card_location AS location, card_location_arg AS location_arg FROM card WHERE card_location = 'sowing' AND card_location_arg >= " . ($i * 5) . " AND card_location_arg < " . (($i * 5) + $sowing_card_number);
            $sowing_cards = self::getCollectionFromDB($sql);

            uasort($sowing_cards, 'self::sowingCompare');

            $location_arg = $i * 5;

            foreach ($sowing_cards as $sowing_card) {
                if ($sowing_card['location_arg'] != $location_arg) {
                    $this->cards->moveCard($sowing_card['id'], 'sowing', $location_arg);

                    self::notifyAllPlayers("sowingSorted", "", array(
                        'sowing_card' => $sowing_card,
                        'location' => $location_arg
                    ));
                }

                $location_arg++;
            }

            self::notifyAllPlayers("waitAnimations", "", array());
        }
    }

    private function _nextStatePlaceHut() {
        $hutSpaces = self::getHutSpaces();

        // Place hut automatically?
        if (count($hutSpaces) == 1) {
            $hutSpace = reset($hutSpaces);
            self::_placeHut($hutSpace['q'], $hutSpace['r']);
        } else {
            $activePlayerId = self::getActivePlayerId();
            self::giveExtraTime($activePlayerId);

            $this->gamestate->nextState('placeHut');
        }
    }

    private function _nextStateUseFoodCard() {
        $foodCards = self::getAllowedFoodCards();

        if (count($foodCards) == 0) {
            self::_stHutsNotResupplied();
        // Use food automatically?
        } elseif (count($foodCards) == 1) {
            $foodCard = reset($foodCards);
            self::_useFoodCard($foodCard['id']);
        } else {
            $activePlayerId = self::getActivePlayerId();
            self::giveExtraTime($activePlayerId);

            $this->gamestate->nextState('useFoodCard');
        }
    }

    private function _nextStateResupplyHut() {
        $allowedHuts = self::getAllowedHuts();
        $counter = self::getGameStateValue('counter');

        if (count($allowedHuts) == $counter) {
            $hut = reset($allowedHuts);
            self::_resupplyHut($hut['q'], $hut['r']);
        } else {
            $activePlayerId = self::getActivePlayerId();
            self::giveExtraTime($activePlayerId);

            $this->gamestate->nextState('resupplyHut');
        }
    }

    private function _nextStatePerformAction() {
        $zigguratSpaces = self::getZigguratSpaces();
        $zigguratBases = self::getExtendableZigguratBases();
        $zigguratCenters = self::getExtendableZigguratCenters();
        $higherDignitary = self::getHigherDignitary();
        $middleDignitary = self::getMiddleDignitary();
        $lowerDignitary = self::getLowerDignitary();
        $offerings = self::getOfferings();
        $foodCards = self::getAvailableFoodCards();
        $plowCard = self::getPlowCard();

        $totalCount = count($zigguratSpaces);
        $totalCount += count($zigguratBases);
        $totalCount += count($zigguratCenters);

        if ($higherDignitary != null) {
            $totalCount += 1;
        }

        if ($middleDignitary != null) {
            $totalCount += 1;
        }

        if ($lowerDignitary != null) {
            $totalCount += 1;
        }

        $totalCount += count($offerings);
        $totalCount += count($foodCards);

        if ($plowCard != null) {
            $totalCount += 1;
        }

        if ($totalCount == 0) {
            $this->gamestate->nextState('endPerformAction');
        } else {
            $activePlayerId = self::getActivePlayerId();
            self::giveExtraTime($activePlayerId);

            $this->gamestate->nextState('performAction');
        }
    }

    protected function getAllDatas() {
        $result = array('players' => array());

        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score, player_turn_order turn_order, player_hut hut, player_ziggurat_base ziggurat_base, player_ziggurat_center ziggurat_center, player_ziggurat_roof ziggurat_roof, player_camel camel, player_offering offering FROM player";
        $result['players'] = self::getCollectionFromDb($sql);

        $result['well_stock'] = self::getGameStateValue('well_stock');
        $result['reign'] = self::getGameStateValue('reign');
        $result['turn'] = self::getGameStateValue('turn');
        $result['card_deck_number'] = $this->cards->countCardInLocation('deck');
        $result['discard'] = $this->cards->getCardsInLocation('discard', null, 'card_location_arg');
        $result['expansion_deck_number'] = $this->cards->countCardInLocation('expansion_deck');
        $result['expansion_discard'] = $this->cards->getCardsInLocation('expansion_discard', null, 'card_location_arg');

        // Cards in player hand
        foreach (array_keys($result['players']) as $player_id) {
            $result['hands'][$player_id] = $this->cards->getCardsInLocation('hand', $player_id);
        }

        $result['draft_cards'] = $this->cards->getCardsInLocation('draft');
        $result['expansions'] = $this->cards->getCardsInLocation('expansion');
        $result['plows'] = $this->cards->getCardsInLocation('plow', null, 'card_location_arg');
        $result['sowings'] = $this->cards->getCardsInLocation('sowing');

        $result['board_hexagons'] = self::getObjectListFromDB("SELECT hexagon_q AS q, hexagon_r AS r, hexagon_type AS type, hexagon_type_arg AS type_arg FROM hexagon");
        $result['board_wells'] = self::getObjectListFromDB("SELECT well_q AS q, well_r AS r, well_t AS t FROM well");
        $result['harvests'] = self::getObjectListFromDB("SELECT location, player_id  FROM harvest");
        $result['higher_dignitary'] = self::getObjectListFromDB("SELECT location, player_id  FROM higher_dignitary");
        $result['middle_dignitary'] = self::getObjectListFromDB("SELECT location, player_id  FROM middle_dignitary");
        $result['lower_dignitary'] = self::getObjectListFromDB("SELECT location, player_id  FROM lower_dignitary");

        return $result;
    }

    function getGameProgression() {
        $total_turn_number = 0;

        for ($i = 1; $i <= $this->REIGN_NUMBER; $i++) {
            $total_turn_number += $this->REIGN_TURN_NUMBER[$i];
        }

        $reign = self::getGameStateValue('reign');
        $turn = self::getGameStateValue('turn');

        $current_turn_number = $turn;

        if ($reign > 1) {
            for ($i = 1; $i < $reign; $i++) {
                $current_turn_number += $this->REIGN_TURN_NUMBER[$i];
            }
        }

        $progression = (int)((($current_turn_number - 1) / $total_turn_number) * 100);

        return $progression;
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////

function loadPlayersInfos() {
    $sql = "SELECT player_id, player_turn_order FROM player";
    $playersInfos = self::getCollectionFromDb($sql);

    return $playersInfos;
}

function activeFirstPlayer() {
    $sql = "SELECT player_id FROM player
            WHERE player_turn_order = 1";

    $first_player_id = self::getUniqueValueFromDB($sql);

    $this->gamestate->changeActivePlayer($first_player_id);

    return $first_player_id;
}

function activeNextPlayer() {
    $playersInfos = self::loadPlayersInfos();
    $activePlayerId = self::getActivePlayerId();

    $activePlayerNumber = $playersInfos[$activePlayerId]['player_turn_order'];
    $playersNumber = self::getPlayersNumber();

    if ($activePlayerNumber == $playersNumber) {
        $nextPlayerNumber = 1;
    } else {
        $nextPlayerNumber = $activePlayerNumber + 1;
    }

    foreach (array_keys($playersInfos) as $player_id) {
        if ($playersInfos[$player_id]['player_turn_order'] == $nextPlayerNumber) {
            $next_player_id = $player_id;
            break;
        }
    }

    $this->gamestate->changeActivePlayer($next_player_id);

    return $next_player_id;
}

function activePrevPlayer() {
    $playersInfos = self::loadPlayersInfos();
    $activePlayerId = self::getActivePlayerId();

    $activePlayerNumber = $playersInfos[$activePlayerId]['player_turn_order'];
    $playersNumber = self::getPlayersNumber();

    if ($activePlayerNumber == 1) {
        $prevPlayerNumber = $playersNumber;
    } else {
        $prevPlayerNumber = $activePlayerNumber - 1;
    }

    foreach (array_keys($playersInfos) as $player_id) {
        if ($playersInfos[$player_id]['player_turn_order'] == $prevPlayerNumber) {
            $prev_player_id = $player_id;
            break;
        }
    }

    $this->gamestate->changeActivePlayer($prev_player_id);

    return $prev_player_id;
}

    function getStartingSpaces() {
        $playersNumber = self::getPlayersNumber();
        $allStartingSpaces = $this->STARTING_SPACES[$playersNumber];

        $chosenStartingSpaces = self::getObjectListFromDB("SELECT hexagon_q AS q, hexagon_r AS r FROM hexagon");

        $result = array_udiff($allStartingSpaces, $chosenStartingSpaces, 'self::hexagonCompare');

        return $result;
    }

    function hexagonCompare($a, $b) {
        if ($a['q'] < $b['q'])
            return -1;

        if ($a['q'] > $b['q'])
            return 1;

        if ($a['r'] < $b['r'])
            return -1;

        if ($a['r'] > $b['r'])
            return 1;

        return 0;
    }

    function sowingCompare($a, $b) {
        // RULES: wild cards are always placed on the right
        if (($a['type'] == 'wild') && ($b['type'] != 'wild')) {
            return 1;
        }

        if (($a['type'] != 'wild') && ($b['type'] == 'wild')) {
            return -1;
        }

        // RULES: if several cards have the same number of symbols on
        //        them, they are arranged by drawing order
        if ($a['type_arg'] == $b['type_arg']) {
            return ($a['location_arg'] < $b['location_arg']) ? -1 : 1;
        }

        // RULES: cards are arranged from left to right in ascending order
        //        according to the number of symbols (1 to 3) on them
        return ($a['type_arg'] < $b['type_arg']) ? -1 : 1;
    }

    function wellCompare($a, $b) {
        if ($a['q'] < $b['q'])
            return -1;

        if ($a['q'] > $b['q'])
            return 1;

        if ($a['r'] < $b['r'])
            return -1;

        if ($a['r'] > $b['r'])
            return 1;

        if ($a['t'] < $b['t'])
            return -1;

        if ($a['t'] > $b['t'])
            return 1;

        return 0;
    }

    function getFoodColumns() {
        $playersNumber = self::getPlayersNumber();
        $sowing_card_number = $this->SOWING_CARD_NUMBER[$playersNumber];
        $allFoodColumns = range(0, $sowing_card_number - 1);

        $chosenFoodColumns = self::getObjectListFromDB("SELECT location FROM harvest", TRUE);

        $result = array_diff($allFoodColumns, $chosenFoodColumns);

        return $result;
    }

    function getRegionHexagons($region) {
        $result = array();

        foreach ($this->HEXAGON_SPACES as $hexagon) {
            $hexagon_region = $hexagon['region'];

            if ($hexagon_region == $region) {
                $result[] = $hexagon;
            }
        }

        return $result;
    }

    function getRiverRevenue($river) {
        $activePlayerId = self::getActivePlayerId();

        $river_hexagons = self::getRegionHexagons($river);
        $player_huts = self::getObjectListFromDB("SELECT hexagon_q AS q, hexagon_r AS r FROM hexagon WHERE hexagon_type = 'hut_resupplied' AND hexagon_type_arg = $activePlayerId");

        $river_huts = array_uintersect($river_hexagons, $player_huts, 'self::hexagonCompare');
        $river_huts_number = count($river_huts);

        if ($river_huts_number == 0) {
            return 0;
        } else {
            return (3 + ($river_huts_number - 1) * 2);
        }
    }

    function getInsidePrestige() {
        $activePlayerId = self::getActivePlayerId();

        $inside_hexagons = self::getRegionHexagons('inside');
        $player_huts = self::getObjectListFromDB("SELECT hexagon_q AS q, hexagon_r AS r FROM hexagon WHERE hexagon_type = 'hut_resupplied' AND hexagon_type_arg = $activePlayerId");

        $inside_huts = array_uintersect($inside_hexagons, $player_huts, 'self::hexagonCompare');
        $inside_huts_number = count($inside_huts);

        return ($inside_huts_number * 2);
    }

    function getOutsidePrestige() {
        $activePlayerId = self::getActivePlayerId();

        $outside_hexagons = self::getRegionHexagons('outside');
        $player_huts = self::getObjectListFromDB("SELECT hexagon_q AS q, hexagon_r AS r FROM hexagon WHERE hexagon_type = 'hut_resupplied' AND hexagon_type_arg = $activePlayerId");

        $outside_huts = array_uintersect($outside_hexagons, $player_huts, 'self::hexagonCompare');
        $outside_huts_number = count($outside_huts);

        return $outside_huts_number;
    }

    function getWellPrestige() {
        $reign = self::getGameStateValue('reign');

        return (7 - $reign);
    }

    function getZigguratPrestige() {
        $activePlayerId = self::getActivePlayerId();

        $player_ziggurats_number = self::getUniqueValueFromDB("SELECT COUNT(*) FROM hexagon WHERE hexagon_type IN ('ziggurat_base', 'ziggurat_center', 'ziggurat_roof') AND hexagon_type_arg = $activePlayerId");

        return $player_ziggurats_number;
    }

    function getHutSpaces() {
        // RULES: huts must be placed on a empty hexagon adjacent to one of the
        //        player's own huts or ziggurats
        $activePlayerId = self::getActivePlayerId();
        $player_spaces = self::getObjectListFromDB("SELECT hexagon_q AS q, hexagon_r AS r FROM hexagon WHERE hexagon_type_arg = $activePlayerId");

        $all_adjacent_spaces = array();

        foreach ($player_spaces as $player_space) {
            $q = $player_space['q'];
            $r = $player_space['r'];

            $all_adjacent_spaces[] = array('q' => $q + 1, 'r' => $r);
            $all_adjacent_spaces[] = array('q' => $q + 1, 'r' => $r - 1);
            $all_adjacent_spaces[] = array('q' => $q, 'r' => $r - 1);
            $all_adjacent_spaces[] = array('q' => $q - 1, 'r' => $r);
            $all_adjacent_spaces[] = array('q' => $q - 1, 'r' => $r + 1);
            $all_adjacent_spaces[] = array('q' => $q, 'r' => $r + 1);
        }

        $unique_adjacent_spaces = array();

        foreach ($all_adjacent_spaces as $adjacent_space) {
            if (!in_array($adjacent_space, $unique_adjacent_spaces)) {
                $unique_adjacent_spaces[] = $adjacent_space;
            }
        }

        $taken_spaces = self::getObjectListFromDB("SELECT hexagon_q AS q, hexagon_r AS r FROM hexagon");
        $not_taken_adjacent_spaces = array_udiff($unique_adjacent_spaces, $taken_spaces, 'self::hexagonCompare');

        $result = array_uintersect($not_taken_adjacent_spaces, $this->HEXAGON_SPACES, 'self::hexagonCompare');

        return $result;
    }

    function getAllowedFoodCards() {
        $activePlayerId = self::getActivePlayerId();
        $player_huts = self::getObjectListFromDB("SELECT hexagon_q AS q, hexagon_r AS r FROM hexagon WHERE hexagon_type = 'hut' AND hexagon_type_arg = $activePlayerId");

        if (count($player_huts) == 0) {
            return array();
        }

        $hut_hexagons = array_uintersect($this->HEXAGON_SPACES, $player_huts, 'self::hexagonCompare');

        $hut_types = array();

        foreach ($hut_hexagons as $hut_hexagon) {
            $hut_type = $hut_hexagon['type'];

            if (!in_array($hut_type, $hut_types)) {
                $hut_types[] = $hut_type;
            }
        }

        $player_cards = $this->cards->getCardsInLocation('hand', $activePlayerId);

        $result = array();

        foreach ($player_cards as $card_id => $card) {
            $card_type = $card['type'];

            if (($card_type == 'wild') || ($card_type == 'plow')) {
                $result[$card_id] = $card;
            } else {
                foreach($hut_types as $hut_type) {
                    if ($card_type == $hut_type) {
                        $result[$card_id] = $card;
                        continue;
                    }
                }
            }
        }

        return $result;
    }

    private function getAllowedHuts() {
        $activePlayerId = self::getActivePlayerId();
        $player_huts = self::getObjectListFromDB("SELECT hexagon_q AS q, hexagon_r AS r FROM hexagon WHERE hexagon_type = 'hut' AND hexagon_type_arg = $activePlayerId");

        $hut_hexagons = array_uintersect($this->HEXAGON_SPACES, $player_huts, 'self::hexagonCompare');

        $card_id = self::getGameStateValue('card_id');
        $card = $this->cards->getCard($card_id);
        $card_type = $card['type'];

        $result = array();

        foreach ($hut_hexagons as $hut_hexagon) {
            $hut_type = $hut_hexagon['type'];

            if (($card_type == 'wild') || ($card_type == 'plow') || ($card_type == $hut_type)) {
                $q = $hut_hexagon['q'];
                $r = $hut_hexagon['r'];

                $result[] = array('q' => $q, 'r' => $r);
            }
        }

        return $result;
    }

    function getWellSpaces() {
        // RULES: wells are placed at the intersection of 3 hexagons on which
        //        the player currently owns a hut
        $activePlayerId = self::getActivePlayerId();
        $player_huts = self::getObjectListFromDB("SELECT hexagon_q AS q, hexagon_r AS r FROM hexagon WHERE hexagon_type = 'hut_resupplied' AND hexagon_type_arg = $activePlayerId");

        $all_wells = array();

        foreach ($this->WELL_SPACES as $well) {
            $q = $well['q'];
            $r = $well['r'];
            $t = $well['t'];

            $adjacent_spaces = array();

            $adjacent_spaces[] = array('q' => $q, 'r' => $r);
            $adjacent_spaces[] = array('q' => $q + $t, 'r' => $r);
            $adjacent_spaces[] = array('q' => $q + $t, 'r' => $r - $t);

            $adjacent_player_huts = array_uintersect($adjacent_spaces, $player_huts, 'self::hexagonCompare');

            if ( count($adjacent_player_huts) == 3) {
                $all_wells[] = $well;
            }
        }

        // RULES: wells can't be placed on top of other wells
        $taken_wells = self::getObjectListFromDB("SELECT well_q AS q, well_r AS r, well_t AS t FROM well");
        $result = array_udiff($all_wells, $taken_wells, 'self::wellCompare');

        return $result;
    }

    function initializeHutCounter() {
        // RULES: the player takes a number of huts from their stock equal to
        //        the value of the Expansion card for the current turn
        $activePlayerId = self::getActivePlayerId();
        $turn = self::getGameStateValue('turn');

        $expansions = $this->cards->getCardsInLocation('expansion', $turn - 1);
        $expansion = reset($expansions);

        $sql = "SELECT player_hut FROM player WHERE player_id = $activePlayerId";
        $player_hut = self::getUniqueValueFromDB($sql);

        // RULES: if the player doesn't have enough huts, they take all
        //        the remaining huts from their stock
        $hutNumber = min($expansion['type_arg'], $player_hut);

        self::setGameStateValue('counter', $hutNumber);
    }

    function getZigguratSpaces() {
        $activePlayerId = self::getActivePlayerId();

        // RULES: to build a ziggurat, the player must spend 6 camels
        $sql = "SELECT player_camel FROM player WHERE player_id = $activePlayerId";
        $player_camel = self::getUniqueValueFromDB($sql);

        if ($player_camel < $this->ZIGGURAT_BASE_COST) {
            return array();
        }

        // RULES: then replace 1 of their huts with 1 ziggurat base
        $sql = "SELECT player_ziggurat_base FROM player WHERE player_id = $activePlayerId";
        $player_ziggurat_base = self::getUniqueValueFromDB($sql);

        if ($player_ziggurat_base == 0) {
            return array();
        }

        // RULES: a ziggurat must be built on a hexagon which is occupied by one
        //        of the player's huts
        $player_huts = self::getObjectListFromDB("SELECT hexagon_q AS q, hexagon_r AS r FROM hexagon WHERE hexagon_type = 'hut_resupplied' AND hexagon_type_arg = $activePlayerId");

        // RULES: a ziggurat can't be built on a river hexagon
        $river1_hexagons = self::getRegionHexagons('river1');
        $river2_hexagons = self::getRegionHexagons('river2');
        $river_hexagons = array_merge($river1_hexagons, $river2_hexagons);

        $non_river_huts = array_udiff($player_huts, $river_hexagons, 'self::hexagonCompare');

        // RULES: a ziggurat can't be built on a hexagon next to a well
        $wells = self::getObjectListFromDB("SELECT well_q AS q, well_r AS r, well_t AS t FROM well");

        $well_adjacent_spaces = array();

        foreach ($wells as $well) {
            $q = $well['q'];
            $r = $well['r'];
            $t = $well['t'];

            $well_adjacent_spaces[] = array('q' => $q, 'r' => $r);
            $well_adjacent_spaces[] = array('q' => $q + $t, 'r' => $r);
            $well_adjacent_spaces[] = array('q' => $q + $t, 'r' => $r - $t);
        }

        $result = array_udiff($non_river_huts, $well_adjacent_spaces, 'self::hexagonCompare');

        return $result;
    }

    function getExtendableZigguratBases() {
        $activePlayerId = self::getActivePlayerId();

        $sql = "SELECT player_camel FROM player WHERE player_id = $activePlayerId";
        $player_camel = self::getUniqueValueFromDB($sql);

        if ($player_camel < $this->ZIGGURAT_CENTER_COST) {
            return array();
        }

        $sql = "SELECT player_ziggurat_center FROM player WHERE player_id = $activePlayerId";
        $player_ziggurat_center = self::getUniqueValueFromDB($sql);

        if ($player_ziggurat_center == 0) {
            return array();
        }

        $board_ziggurat_bases = self::getObjectListFromDB("SELECT hexagon_q AS q, hexagon_r AS r FROM hexagon WHERE hexagon_type = 'ziggurat_base' AND hexagon_type_arg = $activePlayerId");
        $board_ziggurat_centers = self::getObjectListFromDB("SELECT hexagon_q AS q, hexagon_r AS r FROM hexagon WHERE hexagon_type IN ('ziggurat_center', 'ziggurat_center_built') AND hexagon_type_arg = $activePlayerId");

        $result = array_udiff($board_ziggurat_bases, $board_ziggurat_centers, 'self::hexagonCompare');

        return $result;
    }

    function getExtendableZigguratCenters() {
        $activePlayerId = self::getActivePlayerId();

        $sql = "SELECT player_camel FROM player WHERE player_id = $activePlayerId";
        $player_camel = self::getUniqueValueFromDB($sql);

        if ($player_camel < $this->ZIGGURAT_ROOF_COST) {
            return array();
        }

        $sql = "SELECT player_ziggurat_roof FROM player WHERE player_id = $activePlayerId";
        $player_ziggurat_roof = self::getUniqueValueFromDB($sql);

        if ($player_ziggurat_roof == 0) {
            return array();
        }

        $result = self::getObjectListFromDB("SELECT hexagon_q AS q, hexagon_r AS r FROM hexagon WHERE hexagon_type = 'ziggurat_center' AND hexagon_type_arg = $activePlayerId");

        $board_ziggurat_centers = self::getObjectListFromDB("SELECT hexagon_q AS q, hexagon_r AS r FROM hexagon WHERE hexagon_type = 'ziggurat_center' AND hexagon_type_arg = $activePlayerId");
        $board_ziggurat_roofs = self::getObjectListFromDB("SELECT hexagon_q AS q, hexagon_r AS r FROM hexagon WHERE hexagon_type IN ('ziggurat_roof', 'ziggurat_roof_built') AND hexagon_type_arg = $activePlayerId");

        $result = array_udiff($board_ziggurat_centers, $board_ziggurat_roofs, 'self::hexagonCompare');

        return $result;
    }

    function getHigherDignitary() {
        $activePlayerId = self::getActivePlayerId();

        $sql = "SELECT player_camel FROM player WHERE player_id = $activePlayerId";
        $player_camel = self::getUniqueValueFromDB($sql);

        if ($player_camel < $this->HIGHER_DIGNITARY_COST) {
            return null;
        }

        $sql = "SELECT player_hut FROM player WHERE player_id = $activePlayerId";
        $player_hut = self::getUniqueValueFromDB($sql);

        if ($player_hut == 0) {
            return null;
        }

        $dignitaryNumber = self::getUniqueValueFromDB("SELECT COUNT(*) FROM higher_dignitary");

        if ($dignitaryNumber == 3) {
            return null;
        } else {
            return $dignitaryNumber;
        }
    }

    function getMiddleDignitary() {
        $activePlayerId = self::getActivePlayerId();

        $sql = "SELECT player_camel FROM player WHERE player_id = $activePlayerId";
        $player_camel = self::getUniqueValueFromDB($sql);

        if ($player_camel < $this->MIDDLE_DIGNITARY_COST) {
            return null;
        }

        $sql = "SELECT player_hut FROM player WHERE player_id = $activePlayerId";
        $player_hut = self::getUniqueValueFromDB($sql);

        if ($player_hut == 0) {
            return null;
        }

        $dignitaryNumber = self::getUniqueValueFromDB("SELECT COUNT(*) FROM middle_dignitary");

        if ($dignitaryNumber == 4) {
            return null;
        } else {
            return $dignitaryNumber;
        }
    }

    function getLowerDignitary() {
        $activePlayerId = self::getActivePlayerId();

        $sql = "SELECT player_camel FROM player WHERE player_id = $activePlayerId";
        $player_camel = self::getUniqueValueFromDB($sql);

        if ($player_camel < $this->LOWER_DIGNITARY_COST) {
            return null;
        }

        $sql = "SELECT player_hut FROM player WHERE player_id = $activePlayerId";
        $player_hut = self::getUniqueValueFromDB($sql);

        if ($player_hut == 0) {
            return null;
        }

        $dignitaryNumber = self::getUniqueValueFromDB("SELECT COUNT(*) FROM lower_dignitary");

        if ($dignitaryNumber == 6) {
            return null;
        } else {
            return $dignitaryNumber;
        }
    }

    function getOfferings() {
        $activePlayerId = self::getActivePlayerId();

        $sql = "SELECT player_camel FROM player WHERE player_id = $activePlayerId";
        $player_camel = self::getUniqueValueFromDB($sql);

        $maxCamel = min($player_camel, 3 - self::getGameStateValue('offering'));

        if ($maxCamel == 0) {
            return array();
        }

        $sql = "SELECT player_offering FROM player WHERE player_id = $activePlayerId";
        $player_offering = self::getUniqueValueFromDB($sql);

        if ($player_offering == 7) {
            return array();
        }

        $maxOffering = min($player_offering + $maxCamel, 7);

        return range($player_offering + 1, $maxOffering);
    }

    function getCardCamels($card) {
        $card_type = $card['type'];

        if ($card_type == 'wild') {
            return 2;
        }

        $card_type_arg = $card['type_arg'];

        if ($card_type_arg >= 2) {
            return 2;
        }

        return 1;
    }

    function getHexagonType($q, $r) {
        foreach ($this->HEXAGON_SPACES as $hexagon) {
            $hexagon_q = $hexagon['q'];
            $hexagon_r = $hexagon['r'];
            $hexagon_type = $hexagon['type'];

            if (($hexagon_q == $q) && ($hexagon_r == $r)) {
                return $hexagon_type;
            }
        }
    }

    function getIcon($type) {
        return "<div class='log_icon icons icon icon_$type'></div>";
    }

    function getAvailableFoodCards() {
        if (self::getGameStateValue('card_id') != 0) {
            return array();
        }

        $activePlayerId = self::getActivePlayerId();

        $sql = "SELECT player_camel FROM player WHERE player_id = $activePlayerId";
        $player_camel = self::getUniqueValueFromDB($sql);

        $cards = $this->cards->getCardsInLocation('sowing');

        $result = array();

        foreach ($cards as $card_id => $card) {
            $cardCamels = self::getCardCamels($card);

            if ($player_camel >= $cardCamels) {
                $card['camels'] = $cardCamels;

                $result[$card_id] = $card;
            }
        }

        return $result;
    }

    function getPlowCard() {
        $activePlayerId = self::getActivePlayerId();

        $sql = "SELECT player_camel FROM player WHERE player_id = $activePlayerId";
        $player_camel = self::getUniqueValueFromDB($sql);

        if ($player_camel < $this->PLOW_COST) {
            return null;
        }

        $playerPlowNumber = self::getUniqueValueFromDB("SELECT COUNT(*) FROM card WHERE card_type = 'plow' AND card_location = 'hand' AND card_location_arg = $activePlayerId");

        if ($playerPlowNumber != 0) {
            return null;
        }

        return $this->cards->getCardOnTop('plow');
    }

    function dignitariesRankCompare($a, $b) {
        if ($a[0] < $b[0])
            return 1;

        if ($a[0] > $b[0])
            return -1;

        if ($a[1] < $b[1])
            return 1;

        if ($a[1] > $b[1])
            return -1;

        if ($a[2] < $b[2])
            return 1;

        if ($a[2] > $b[2])
            return -1;

        return 0;
    }

    function getDignitariesRank() {
        $rank = array();

        $players = self::getObjectListFromDB("SELECT player_id FROM player", TRUE);

        foreach ($players as $player_id) {
            $influence = 0;
            $hut_number= 0;
            $higher_location = 0;

            $lower_dignitary = self::getObjectListFromDB("SELECT location FROM lower_dignitary WHERE player_id = $player_id ORDER by location");
            $lower_dignitary_number = count($lower_dignitary);

            if ($lower_dignitary_number != 0) {
                $influence = $influence + $lower_dignitary_number;
                $hut_number = $hut_number + $lower_dignitary_number;

                $higher_hut = reset($lower_dignitary);
                $higher_location = 6 - $higher_hut['location'];
            }

            $middle_dignitary = self::getObjectListFromDB("SELECT location FROM middle_dignitary WHERE player_id = $player_id ORDER by location");
            $middle_dignitary_number = count($middle_dignitary);

            if ($middle_dignitary_number != 0) {
                $influence = $influence + ($middle_dignitary_number * 2);
                $hut_number = $hut_number + $middle_dignitary_number;

                $higher_hut = reset($middle_dignitary);
                $higher_location = 10 - $higher_hut['location'];
            }

            $higher_dignitary = self::getObjectListFromDB("SELECT location FROM higher_dignitary WHERE player_id = $player_id ORDER by location");
            $higher_dignitary_number = count($higher_dignitary);

            if ($higher_dignitary_number != 0) {
                $influence = $influence + ($higher_dignitary_number * 3);
                $hut_number = $hut_number + $higher_dignitary_number;

                $higher_hut = reset($higher_dignitary);
                $higher_location = 13 - $higher_hut['location'];
            }

            if ($influence != 0) {
                $rank[$player_id] = array($influence, $hut_number, $higher_location);
            }
        }

        uasort($rank, 'self::dignitariesRankCompare');

        return array_keys($rank);
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
////////////

    function chooseFoodCard($id) {
        self::checkAction('chooseFoodCard');

        $foodCards = $this->cards->getCardsInLocation('draft');

        // Check if it's an allowed food card
        if (!array_key_exists($id, $foodCards)) {
            throw new BgaVisibleSystemException(self::_("You can't choose this card"));
        }

        self::_chooseFoodCard($id);
    }

    function _chooseFoodCard($id) {
        $activePlayerId = self::getActivePlayerId();

        $this->cards->moveCard($id, 'hand', $activePlayerId);

        $card = $this->cards->getCard($id);

        self::notifyAllPlayers("foodCardChosen", clienttranslate('${player_name} chooses ${card_number} ${card_icon} ${card_type}'), array(
            'i18n' => array('card_type'),
            'player_id' => $activePlayerId,
            'player_name' => self::getActivePlayerName(),
            'card_number' => $card['type_arg'],
            'card_icon' => self::getIcon($card['type']),
            'card_type' => $this->CARD_TYPE_TRANSLATIONS[$card['type']],
            'card' => $card
        ));

        $this->gamestate->nextState('foodCardChosen');
    }

    function chooseFoodColumn($column) {
        self::checkAction('chooseFoodColumn');

        $foodColumns = self::getFoodColumns();

        // Check if it's an allowed food column
        if (!in_array($column, $foodColumns)) {
            throw new BgaVisibleSystemException(self::_("You can't choose this column"));
        }

        self::_chooseFoodColumn($column);
    }

    function _chooseFoodColumn($column) {
        $activePlayerId = self::getActivePlayerId();

        $sql = "INSERT INTO harvest (location, player_id) VALUES ($column, $activePlayerId)";
        self::DbQuery($sql);

        $first_sowing_cards = $this->cards->getCardsInLocation('sowing', $column);
        $first_sowing_card = reset($first_sowing_cards);

        $second_sowing_cards = $this->cards->getCardsInLocation('sowing', $column + 5);
        $second_sowing_card = reset($second_sowing_cards);

        $this->cards->moveCards(array($first_sowing_card['id'], $second_sowing_card['id']), 'hand', $activePlayerId);

        self::notifyAllPlayers("foodColumnChosen", clienttranslate('${player_name} chooses ${first_sowing_number} ${first_sowing_icon} ${first_sowing_type} and ${second_sowing_number} ${second_sowing_icon} ${second_sowing_type}') , array(
            'i18n' => array('first_sowing_type', 'second_sowing_type'),
            'player_id' => $activePlayerId,
            'player_name' => self::getActivePlayerName(),
            'first_sowing_number' => $first_sowing_card['type_arg'],
            'first_sowing_icon' => self::getIcon($first_sowing_card['type']),
            'first_sowing_type' => $this->CARD_TYPE_TRANSLATIONS[$first_sowing_card['type']],
            'second_sowing_number' => $second_sowing_card['type_arg'],
            'second_sowing_icon' => self::getIcon($second_sowing_card['type']),
            'second_sowing_type' => $this->CARD_TYPE_TRANSLATIONS[$second_sowing_card['type']],
            'column' => $column,
            'cards' => $first_sowing_cards + $second_sowing_cards
        ));

        $this->gamestate->nextState('foodColumnChosen');
    }

    function chooseStartingSpace($q, $r) {
        self::checkAction('chooseStartingSpace');

        $startingSpaces = self::getStartingSpaces();

        // Check if it's an allowed starting space
        if (!in_array(array('q' => $q, 'r' => $r), $startingSpaces)) {
            throw new BgaVisibleSystemException(self::_("You can't choose this starting space"));
        }

        self::_chooseStartingSpace($q, $r);
    }

    function _chooseStartingSpace($q, $r) {
        $activePlayerId = self::getActivePlayerId();

        $sql = "INSERT INTO hexagon (hexagon_q, hexagon_r, hexagon_type, hexagon_type_arg) VALUES ($q, $r, 'ziggurat_base', $activePlayerId)";
        self::DbQuery($sql);

        // Update ziggurat base count
        $sql = "UPDATE player
                SET player_ziggurat_base = player_ziggurat_base - 1
                WHERE player_id = $activePlayerId";
        self::DbQuery($sql);

        $hexagon_type = self::getHexagonType($q, $r);

        self::notifyAllPlayers("startingSpaceChosen", clienttranslate('${player_name} chooses a starting space on a ${hexagon_icon} ${hexagon_type} symbol'), array(
            'i18n' => array('hexagon_type'),
            'player_id' => $activePlayerId,
            'player_name' => self::getActivePlayerName(),
            'hexagon_icon' => self::getIcon($hexagon_type),
            'hexagon_type' => $this->HEXAGON_TYPE_TRANSLATIONS[$hexagon_type],
            'q' => $q,
            'r' => $r
        ));

        $this->gamestate->nextState('startingSpaceChosen');
    }

    function placeHut($q, $r) {
        self::checkAction('placeHut');

        $hutSpaces = self::getHutSpaces();

        // Check if it's an allowed hut space
        if (!in_array(array('q' => $q, 'r' => $r), $hutSpaces)) {
            throw new BgaVisibleSystemException(self::_("You can't place a hut here"));
        }

        self::_placeHut($q, $r);
    }

    function _placeHut($q, $r) {
        $activePlayerId = self::getActivePlayerId();

        $sql = "INSERT INTO hexagon (hexagon_q, hexagon_r, hexagon_type, hexagon_type_arg) VALUES ($q, $r, 'hut', $activePlayerId)";
        self::DbQuery($sql);

        $counter = self::getGameStateValue('counter');
        self::setGameStateValue('counter', $counter - 1);

        $sql = "UPDATE player
                SET player_hut = player_hut - 1
                WHERE player_id = $activePlayerId";
        self::DbQuery($sql);

        $hexagon_type = self::getHexagonType($q, $r);

        self::notifyAllPlayers("hutPlaced", clienttranslate('${player_name} places a hut on a ${hexagon_icon} ${hexagon_type} symbol'), array(
            'i18n' => array('hexagon_type'),
            'player_id' => $activePlayerId,
            'player_name' => self::getActivePlayerName(),
            'hexagon_icon' => self::getIcon($hexagon_type),
            'hexagon_type' => $this->HEXAGON_TYPE_TRANSLATIONS[$hexagon_type],
            'q' => $q,
            'r' => $r
        ));

        $this->gamestate->nextState('hutPlaced');
    }

    function useFoodCard($id) {
        self::checkAction('useFoodCard');

        $foodCards = self::getAllowedFoodCards();

        // Check if it's an allowed food card
        if (!array_key_exists($id, $foodCards)) {
            throw new BgaVisibleSystemException(self::_("You can't use this card"));
        }

        self::_useFoodCard($id);
    }

    function _useFoodCard($id) {
        $activePlayerId = self::getActivePlayerId();

        $card = $this->cards->getCard($id);

        if ($card['type'] == 'plow') {
            $card_location = 'plow';
        } else {
            $card_location = 'discard';
        }

        $this->cards->insertCardOnExtremePosition($id, $card_location, TRUE);
        self::setGameStateValue('card_id', $id);

        self::notifyAllPlayers("foodCardUsed", clienttranslate('${player_name} uses ${card_number} ${card_icon} ${card_type}'), array(
            'i18n' => array('card_type'),
            'player_id' => $activePlayerId,
            'player_name' => self::getActivePlayerName(),
            'card_number' => $card['type_arg'],
            'card_icon' => self::getIcon($card['type']),
            'card_type' => $this->CARD_TYPE_TRANSLATIONS[$card['type']],
            'card' => $card
        ));

        $this->gamestate->nextState('foodCardUsed');
    }

    function resupplyHut($q, $r) {
        self::checkAction('resupplyHut');

        $allowedHuts = self::getAllowedHuts();

        // Check if it's an allowed hut
        if (!in_array(array('q' => $q, 'r' => $r), $allowedHuts)) {
            throw new BgaVisibleSystemException(self::_("You can't resupply this hut"));
        }

        self::_resupplyHut($q, $r);
    }

    function _resupplyHut($q, $r) {
        $activePlayerId = self::getActivePlayerId();

        $sql = "UPDATE hexagon SET hexagon_type = 'hut_resupplied' WHERE hexagon_q = $q AND hexagon_r = $r";
        self::DbQuery($sql);

        $counter = self::getGameStateValue('counter');
        self::setGameStateValue('counter', $counter - 1);

        $hexagon_type = self::getHexagonType($q, $r);

        self::notifyAllPlayers("hutResupplied", clienttranslate('${player_name} resupplies a hut on a ${hexagon_icon} ${hexagon_type} symbol'), array(
            'i18n' => array('hexagon_type'),
            'player_id' => $activePlayerId,
            'player_name' => self::getActivePlayerName(),
            'hexagon_icon' => self::getIcon($hexagon_type),
            'hexagon_type' => $this->HEXAGON_TYPE_TRANSLATIONS[$hexagon_type],
            'q' => $q,
            'r' => $r
        ));

        $this->gamestate->nextState('hutResupplied');
    }

    function placeWell($q, $r, $t) {
        self::checkAction('placeWell');

        $activePlayerId = self::getActivePlayerId();

        $wellSpaces = self::getWellSpaces();

        // Check if it's an allowed well space
        if (!in_array(array('q' => $q, 'r' => $r, 't' => $t), $wellSpaces)) {
            throw new BgaVisibleSystemException(self::_("You can't place a well here"));
        }

        $sql = "INSERT INTO well (well_q, well_r, well_t) VALUES ($q, $r, $t)";
        self::DbQuery($sql);

        $counter = self::getGameStateValue('counter');
        self::setGameStateValue('counter', $counter - 1);

        $wellStock = self::getGameStateValue('well_stock');
        $wellStock = $wellStock - 1;
        self::setGameStateValue('well_stock', $wellStock);

        self::notifyAllPlayers("wellPlaced", clienttranslate('${player_name} places a well'), array(
            'player_name' => self::getActivePlayerName(),
            'q' => $q,
            'r' => $r,
            't' => $t
        ));

        $point_number = self::getWellPrestige();

        $sql = "UPDATE player SET player_score = player_score + $point_number WHERE player_id = $activePlayerId";
        self::DbQuery($sql);

        self::incStat($point_number, "pointsScoredWithWells", $activePlayerId);

        $sql = "SELECT player_score FROM player WHERE player_id = $activePlayerId";
        $player_score = self::getUniqueValueFromDB($sql);

        self::notifyAllPlayers("pointsScored", clienttranslate('${player_name} scores ${point_number} ${point_icon} point(s) with the well'), array(
            'player_id' => $activePlayerId,
            'player_name' => self::getActivePlayerName(),
            'point_number' => $point_number,
            'point_icon' => self::getIcon('point'),
            'player_score' => $player_score
        ));

        $this->gamestate->nextState('wellPlaced');
    }

    function passPlaceWell() {
        self::checkAction('passPlaceWell');

        self::_passPlaceWell();
    }

    function _passPlaceWell() {
        self::notifyAllPlayers("placeWellPassed", "", array());

        $this->gamestate->nextState('placeWellPassed');
    }

    function buildZiggurat($q, $r) {
        self::checkAction('buildZiggurat');

        $activePlayerId = self::getActivePlayerId();

        $zigguratSpaces = self::getZigguratSpaces();

        // Check if it's an allowed ziggurat space
        if (!in_array(array('q' => $q, 'r' => $r), $zigguratSpaces)) {
            throw new BgaVisibleSystemException(self::_("You can't build a ziggurat here"));
        }

        $sql = "UPDATE player SET player_camel = player_camel - $this->ZIGGURAT_BASE_COST WHERE player_id = $activePlayerId";
        self::DbQuery($sql);

        $sql = "SELECT player_camel FROM player WHERE player_id = $activePlayerId";
        $player_camel = self::getUniqueValueFromDB($sql);

        $sql = "DELETE FROM hexagon WHERE hexagon_q = $q AND hexagon_r = $r AND hexagon_type = 'hut_resupplied' AND hexagon_type_arg = $activePlayerId";
        self::DbQuery($sql);

        $sql = "UPDATE player SET player_hut = player_hut + 1 WHERE player_id = $activePlayerId";
        self::DbQuery($sql);

        $sql = "INSERT INTO hexagon (hexagon_q, hexagon_r, hexagon_type, hexagon_type_arg) VALUES ($q, $r, 'ziggurat_base_built', $activePlayerId)";
        self::DbQuery($sql);

        $sql = "UPDATE player SET player_ziggurat_base = player_ziggurat_base - 1 WHERE player_id = $activePlayerId";
        self::DbQuery($sql);

        self::notifyAllPlayers("zigguratBuilt", clienttranslate('${player_name} builds a ziggurat for ${camel_number} ${camel_icon} camels'), array(
            'player_id' => $activePlayerId,
            'player_name' => self::getActivePlayerName(),
            'camel_number' => $this->ZIGGURAT_BASE_COST,
            'camel_icon' => self::getIcon('camel'),
            'q' => $q,
            'r' => $r,
            'player_camel' => $player_camel
        ));

        $this->gamestate->nextState('zigguratBuilt');
    }

    function extendZigguratCenter($q, $r) {
        self::checkAction('extendZigguratCenter');

        $activePlayerId = self::getActivePlayerId();

        $zigguratBases = self::getExtendableZigguratBases();

        // Check if it's an allowed ziggurat base
        if (!in_array(array('q' => $q, 'r' => $r), $zigguratBases)) {
            throw new BgaVisibleSystemException(self::_("You can't extend this ziggurat"));
        }

        $sql = "UPDATE player SET player_camel = player_camel - $this->ZIGGURAT_CENTER_COST WHERE player_id = $activePlayerId";
        self::DbQuery($sql);

        $sql = "SELECT player_camel FROM player WHERE player_id = $activePlayerId";
        $player_camel = self::getUniqueValueFromDB($sql);

        $sql = "INSERT INTO hexagon (hexagon_q, hexagon_r, hexagon_type, hexagon_type_arg) VALUES ($q, $r, 'ziggurat_center_built', $activePlayerId)";
        self::DbQuery($sql);

        $sql = "UPDATE player SET player_ziggurat_center = player_ziggurat_center - 1 WHERE player_id = $activePlayerId";
        self::DbQuery($sql);

        self::notifyAllPlayers("zigguratCenterExtended", clienttranslate('${player_name} extends a ziggurat for ${camel_number} ${camel_icon} camels'), array(
            'player_id' => $activePlayerId,
            'player_name' => self::getActivePlayerName(),
            'camel_number' => $this->ZIGGURAT_CENTER_COST,
            'camel_icon' => self::getIcon('camel'),
            'q' => $q,
            'r' => $r,
            'player_camel' => $player_camel
        ));

        $this->gamestate->nextState('zigguratCenterExtended');
    }

    function extendZigguratRoof($q, $r) {
        self::checkAction('extendZigguratRoof');

        $activePlayerId = self::getActivePlayerId();

        $zigguratCenters = self::getExtendableZigguratCenters();

        // Check if it's an allowed ziggurat center
        if (!in_array(array('q' => $q, 'r' => $r), $zigguratCenters)) {
            throw new BgaVisibleSystemException(self::_("You can't extend this ziggurat"));
        }

        $sql = "UPDATE player SET player_camel = player_camel - $this->ZIGGURAT_ROOF_COST WHERE player_id = $activePlayerId";
        self::DbQuery($sql);

        $sql = "SELECT player_camel FROM player WHERE player_id = $activePlayerId";
        $player_camel = self::getUniqueValueFromDB($sql);

        $sql = "INSERT INTO hexagon (hexagon_q, hexagon_r, hexagon_type, hexagon_type_arg) VALUES ($q, $r, 'ziggurat_roof_built', $activePlayerId)";
        self::DbQuery($sql);

        $sql = "UPDATE player SET player_ziggurat_roof = player_ziggurat_roof - 1 WHERE player_id = $activePlayerId";
        self::DbQuery($sql);

        self::notifyAllPlayers("zigguratRoofExtended", clienttranslate('${player_name} extends a ziggurat for ${camel_number} ${camel_icon} camels'), array(
            'player_id' => $activePlayerId,
            'player_name' => self::getActivePlayerName(),
            'camel_number' => $this->ZIGGURAT_ROOF_COST,
            'camel_icon' => self::getIcon('camel'),
            'q' => $q,
            'r' => $r,
            'player_camel' => $player_camel
        ));

        $this->gamestate->nextState('zigguratRoofExtended');
    }

    function influenceHigherDignitary() {
        self::checkAction('influenceHigherDignitary');

        $activePlayerId = self::getActivePlayerId();

        $higherDignitary = self::getHigherDignitary();

        // Check if it's allowed
        if ($higherDignitary == null) {
            throw new BgaVisibleSystemException(self::_("You can't influence the higher dignitary"));
        }

        $sql = "UPDATE player SET player_camel = player_camel - $this->HIGHER_DIGNITARY_COST WHERE player_id = $activePlayerId";
        self::DbQuery($sql);

        $sql = "SELECT player_camel FROM player WHERE player_id = $activePlayerId";
        $player_camel = self::getUniqueValueFromDB($sql);

        $sql = "INSERT INTO higher_dignitary (location, player_id) VALUES ($higherDignitary, $activePlayerId)";
        self::DbQuery($sql);

        $sql = "UPDATE player SET player_hut = player_hut - 1 WHERE player_id = $activePlayerId";
        self::DbQuery($sql);

        self::notifyAllPlayers("higherDignitaryInfluenced", clienttranslate('${player_name} influences the higher dignitary for ${camel_number} ${camel_icon} camels'), array(
            'player_id' => $activePlayerId,
            'player_name' => self::getActivePlayerName(),
            'camel_number' => $this->HIGHER_DIGNITARY_COST,
            'camel_icon' => self::getIcon('camel'),
            'location' => $higherDignitary,
            'player_camel' => $player_camel
        ));

        $this->gamestate->nextState('higherDignitaryInfluenced');
    }

    function influenceMiddleDignitary() {
        self::checkAction('influenceMiddleDignitary');

        $activePlayerId = self::getActivePlayerId();

        $middleDignitary = self::getMiddleDignitary();

        // Check if it's allowed
        if ($middleDignitary == null) {
            throw new BgaVisibleSystemException(self::_("You can't influence the middle dignitary"));
        }

        $sql = "UPDATE player SET player_camel = player_camel - $this->MIDDLE_DIGNITARY_COST WHERE player_id = $activePlayerId";
        self::DbQuery($sql);

        $sql = "SELECT player_camel FROM player WHERE player_id = $activePlayerId";
        $player_camel = self::getUniqueValueFromDB($sql);

        $sql = "INSERT INTO middle_dignitary (location, player_id) VALUES ($middleDignitary, $activePlayerId)";
        self::DbQuery($sql);

        $sql = "UPDATE player SET player_hut = player_hut - 1 WHERE player_id = $activePlayerId";
        self::DbQuery($sql);

        self::notifyAllPlayers("middleDignitaryInfluenced", clienttranslate('${player_name} influences the middle dignitary for ${camel_number} ${camel_icon} camels'), array(
            'player_id' => $activePlayerId,
            'player_name' => self::getActivePlayerName(),
            'camel_number' => $this->MIDDLE_DIGNITARY_COST,
            'camel_icon' => self::getIcon('camel'),
            'location' => $middleDignitary,
            'player_camel' => $player_camel
        ));

        $this->gamestate->nextState('middleDignitaryInfluenced');
    }

    function influenceLowerDignitary() {
        self::checkAction('influenceLowerDignitary');

        $activePlayerId = self::getActivePlayerId();

        $lowerDignitary = self::getLowerDignitary();

        // Check if it's allowed
        if ($lowerDignitary == null) {
            throw new BgaVisibleSystemException(self::_("You can't influence the lower dignitary"));
        }

        $sql = "UPDATE player SET player_camel = player_camel - $this->LOWER_DIGNITARY_COST WHERE player_id = $activePlayerId";
        self::DbQuery($sql);

        $sql = "SELECT player_camel FROM player WHERE player_id = $activePlayerId";
        $player_camel = self::getUniqueValueFromDB($sql);

        $sql = "INSERT INTO lower_dignitary (location, player_id) VALUES ($lowerDignitary, $activePlayerId)";
        self::DbQuery($sql);

        $sql = "UPDATE player SET player_hut = player_hut - 1 WHERE player_id = $activePlayerId";
        self::DbQuery($sql);

        self::notifyAllPlayers("lowerDignitaryInfluenced", clienttranslate('${player_name} influences the lower dignitary for ${camel_number} ${camel_icon} camels'), array(
            'player_id' => $activePlayerId,
            'player_name' => self::getActivePlayerName(),
            'camel_number' => $this->LOWER_DIGNITARY_COST,
            'camel_icon' => self::getIcon('camel'),
            'location' => $lowerDignitary,
            'player_camel' => $player_camel
        ));

        $this->gamestate->nextState('lowerDignitaryInfluenced');
    }

    function makeOffering($offering) {
        self::checkAction('makeOffering');

        $activePlayerId = self::getActivePlayerId();

        $offerings = self::getOfferings();

        // Check if it's an allowed offering
        if (!in_array($offering, $offerings)) {
            throw new BgaVisibleSystemException(self::_("You can't make this offering"));
        }

        $sql = "SELECT player_offering FROM player WHERE player_id = $activePlayerId";
        $player_offering = self::getUniqueValueFromDB($sql);

        $camel = $offering - $player_offering;

        $sql = "UPDATE player SET player_camel = player_camel - $camel WHERE player_id = $activePlayerId";
        self::DbQuery($sql);

        $sql = "SELECT player_camel FROM player WHERE player_id = $activePlayerId";
        $player_camel = self::getUniqueValueFromDB($sql);

        $sql = "UPDATE player SET player_offering = $offering WHERE player_id = $activePlayerId";
        self::DbQuery($sql);

        self::incGameStateValue('offering', $camel);

        self::notifyAllPlayers("offeringMade", clienttranslate('${player_name} makes an offering of ${camel_number} ${camel_icon} camel(s)'), array(
            'player_id' => $activePlayerId,
            'player_name' => self::getActivePlayerName(),
            'camel_number' => $camel,
            'camel_icon' => self::getIcon('camel'),
            'player_camel' => $player_camel,
            'player_offering' => $offering
        ));

        $this->gamestate->nextState('offeringMade');
    }

    function buyPlowCard($id) {
        self::checkAction('buyPlowCard');

        $activePlayerId = self::getActivePlayerId();

        $plowCard = self::getPlowCard();

        // Check if it's the allowed plow card
        if ($plowCard['id'] != $id) {
            throw new BgaVisibleSystemException(self::_("You can't buy this card"));
        }

        $sql = "UPDATE player SET player_camel = player_camel - $this->PLOW_COST WHERE player_id = $activePlayerId";
        self::DbQuery($sql);

        $sql = "SELECT player_camel FROM player WHERE player_id = $activePlayerId";
        $player_camel = self::getUniqueValueFromDB($sql);

        $this->cards->moveCard($id, 'hand', $activePlayerId );

        self::notifyAllPlayers("plowCardBought", clienttranslate('${player_name} buys ${card_number} ${card_icon} ${card_type} for ${camel_number} ${camel_icon} camel(s)'), array(
            'i18n' => array('card_type'),
            'player_id' => $activePlayerId,
            'player_name' => self::getActivePlayerName(),
            'card_number' => $plowCard['type_arg'],
            'card_icon' => self::getIcon($plowCard['type']),
            'card_type' => $this->CARD_TYPE_TRANSLATIONS[$plowCard['type']],
            'camel_number' => $this->PLOW_COST,
            'camel_icon' => self::getIcon('camel'),
            'player_camel' => $player_camel,
            'card' => $plowCard
        ));

        $this->gamestate->nextState('plowCardBought');
    }

    function buyFoodCard($id) {
        self::checkAction('buyFoodCard');

        $activePlayerId = self::getActivePlayerId();

        $foodCards = self::getAvailableFoodCards();

        // Check if it's an allowed food card
        if (!array_key_exists($id, $foodCards)) {
            throw new BgaVisibleSystemException(self::_("You can't buy this card"));
        }

        $card = $this->cards->getCard($id);
        $cardCamels = self::getCardCamels($card);

        $sql = "UPDATE player SET player_camel = player_camel - $cardCamels WHERE player_id = $activePlayerId";
        self::DbQuery($sql);

        $sql = "SELECT player_camel FROM player WHERE player_id = $activePlayerId";
        $player_camel = self::getUniqueValueFromDB($sql);

        $this->cards->moveCard($id, 'hand', $activePlayerId);

        self::setGameStateValue('card_id', $id);

        self::notifyAllPlayers("foodCardBought", clienttranslate('${player_name} buys ${card_number} ${card_icon} ${card_type} for ${camel_number} ${camel_icon} camel(s)'), array(
            'i18n' => array('card_type'),
            'player_id' => $activePlayerId,
            'player_name' => self::getActivePlayerName(),
            'card_number' => $card['type_arg'],
            'card_icon' => self::getIcon($card['type']),
            'card_type' => $this->CARD_TYPE_TRANSLATIONS[$card['type']],
            'camel_number' => $cardCamels,
            'camel_icon' => self::getIcon('camel'),
            'player_camel' => $player_camel,
            'card' => $card
        ));

        $this->gamestate->nextState('foodCardBought');
    }

    function passPerformAction() {
        self::checkAction('passPerformAction');

        self::_passPerformAction();
    }

    function _passPerformAction() {
        self::notifyAllPlayers("passPerformAction", "", array());

        $this->gamestate->nextState('performActionPassed');
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    function argChooseStartingSpace() {
        return array(
            'startingSpaces' => self::getStartingSpaces()
        );
    }

    function argChooseFoodCard() {
        return array(
            'foodCards' => $this->cards->getCardsInLocation('draft')
        );
    }

    function argChooseFoodColumn() {
        return array(
            'foodColumns' => self::getFoodColumns()
        );
    }

    function argPlaceHut() {
        return array(
            'hutSpaces' => self::getHutSpaces(),
            'hut_number' => self::getGameStateValue('counter')
        );
    }

    function argUseFoodCard() {
        return array(
            'foodCards' => self::getAllowedFoodCards()
        );
    }

    function argResupplyHut() {
        return array(
            'huts' => self::getAllowedHuts(),
            'hut_number' => self::getGameStateValue('counter')
        );
    }

    function argPlaceWell() {
        return array(
            'wellSpaces' => self::getWellSpaces(),
            'well_number' => self::getGameStateValue('counter')
        );
    }

    function argPerformAction() {
        return array(
            'zigguratSpaces' => self::getZigguratSpaces(),
            'zigguratBases' => self::getExtendableZigguratBases(),
            'zigguratCenters' => self::getExtendableZigguratCenters(),
            'higherDignitary' => self::getHigherDignitary(),
            'middleDignitary' => self::getMiddleDignitary(),
            'lowerDignitary' => self::getLowerDignitary(),
            'offerings' => self::getOfferings(),
            'foodCards' => self::getAvailableFoodCards(),
            'plowCard' => self::getPlowCard()
        );
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    function stStartingSpaceChosen() {
        $playersInfos = self::loadPlayersInfos();
        $activePlayerId = self::getActivePlayerId();

        $activePlayerNumber = $playersInfos[$activePlayerId]['player_turn_order'];
        $playersNumber = self::getPlayersNumber();

        if ($activePlayerNumber == $playersNumber) {
            // Last player
            $draft_cards = $this->cards->pickCardsForLocation($this->DRAFT_CARD_NUMBER[$playersNumber], 'deck', 'draft');

            self::notifyAllPlayers("draftBegan", "", array(
                'draft_cards' => $draft_cards
            ));

            self::giveExtraTime($activePlayerId);

            $this->gamestate->nextState('chooseFoodCard');
        } else {
            // Activate next player
            $activePlayerId = self::activeNextPlayer();

            $startingSpaces = self::getStartingSpaces();

            // Choose starting space automatically?
            if (count($startingSpaces) == 1) {
                $startingSpace = reset($startingSpaces);
                self::_chooseStartingSpace($startingSpace['q'], $startingSpace['r']);
            } else {
                self::giveExtraTime($activePlayerId);

                $this->gamestate->nextState('chooseStartingSpace');
            }
        }
    }

function stFoodCardChosen() {
        $playersInfos = self::loadPlayersInfos();
        $activePlayerId = self::getActivePlayerId();

        $activePlayerNumber = $playersInfos[$activePlayerId]['player_turn_order'];

        if ($activePlayerNumber == 1) {
            // First player
            self::notifyAllPlayers("draftEnded", "", array());
            self::giveExtraTime($activePlayerId);

            $this->gamestate->nextState('chooseFoodColumn');
        } else {
            // Activate previous player
            $activePlayerId = self::activePrevPlayer();

            $foodCards = $this->cards->getCardsInLocation('draft');

            // Choose food card automatically?
            if (count($foodCards) == 1) {
                $foodCard = reset($foodCards);
                self::_chooseFoodCard($foodCard['id']);
            } else {
                self::giveExtraTime($activePlayerId);

                $this->gamestate->nextState('chooseFoodCard');
            }
        }
    }

function stFoodColumnChosen() {
        $playersInfos = self::loadPlayersInfos();
        $activePlayerId = self::getActivePlayerId();

        $activePlayerNumber = $playersInfos[$activePlayerId]['player_turn_order'];
        $playersNumber = self::getPlayersNumber();

        if ($activePlayerNumber == $playersNumber) {
            // Last player
            $players = self::getObjectListFromDB("SELECT player_id FROM harvest ORDER BY location", TRUE);
            $player_turn_order = 1;

            foreach ($players as $player_id) {
                $sql = "UPDATE player
                        SET player_turn_order = $player_turn_order
                        WHERE player_id = $player_id";
                self::DbQuery($sql);

                $player_turn_order++;
            }

            $sql = "DELETE FROM harvest";
            self::DbQuery($sql);

            $sql = "SELECT player_id id, player_turn_order turn_order FROM player ORDER BY player_turn_order";
            $players = self::getCollectionFromDb($sql);

            self::notifyAllPlayers("newTurnOrderDetermined", clienttranslate('New turn order determined'), array(
                'players' => $players
            ));

            self::activeFirstPlayer();

            self::initializeHutCounter();
            $hutCounter = self::getGameStateValue('counter');

            $hutSpaces = self::getHutSpaces();

            if (($hutCounter == 0) || (count($hutSpaces) == 0)) {
                self::_nextStateUseFoodCard();
            } else {
                self::_nextStatePlaceHut();
            }
        } else {
            // Activate next player
            $activePlayerId = self::activeNextPlayer();
            self::giveExtraTime($activePlayerId);

            $this->gamestate->nextState('chooseFoodColumn');
        }
    }

    function stHutPlaced() {
        $hutCounter = self::getGameStateValue('counter');

        $hutSpaces = self::getHutSpaces();

        if (($hutCounter == 0) || (count($hutSpaces) == 0)) {
            self::_nextStateUseFoodCard();
        } else {
            self::_nextStatePlaceHut();
        }
    }

    function stFoodCardUsed() {
        $allowedHuts = self::getAllowedHuts();

        $card_id = self::getGameStateValue('card_id');
        $card = $this->cards->getCard($card_id);
        $hutNumber = min(count($allowedHuts), $card['type_arg']);

        self::setGameStateValue('counter', $hutNumber);

        self::_nextStateResupplyHut();
    }

    function stHutResupplied() {
        $counter = self::getGameStateValue('counter');

        if ($counter == 0) {
            self::_nextStateUseFoodCard();
        } else {
            self::_nextStateResupplyHut();
        }
    }

    function _stHutsNotResupplied() {
        $activePlayerId = self::getActivePlayerId();

        // RULES: the player removes all their huts that have not been
        //        resupplied
        $sql = "SELECT hexagon_q AS q, hexagon_r AS r FROM hexagon WHERE hexagon_type = 'hut' AND hexagon_type_arg = $activePlayerId";
        $player_huts = self::getObjectListFromDB($sql);
        $hut_number = count($player_huts);

        $sql = "DELETE FROM hexagon WHERE hexagon_type = 'hut' AND hexagon_type_arg = $activePlayerId";
        self::DbQuery($sql);

        // RULES: place these huts back in the player's stock
        $sql = "UPDATE player
                SET player_hut = player_hut + $hut_number
                WHERE player_id = $activePlayerId";
        self::DbQuery($sql);

        self::notifyAllPlayers("hutsNotResupplied", clienttranslate('${player_name} removes ${hut_number} not resupplied hut(s)'), array(
            'player_id' => $activePlayerId,
            'player_name' => self::getActivePlayerName(),
            'hut_number' => $hut_number,
            'huts' => $player_huts
        ));

        $wellSpaces = self::getWellSpaces();
        $wellStock = self::getGameStateValue('well_stock');

        $wellNumber = min(count($wellSpaces), $wellStock);

        if ($wellNumber == 0) {
            $this->gamestate->nextState('countRevenuePrestige');
        } else {
            self::setGameStateValue('counter', $wellNumber);
            self::giveExtraTime($activePlayerId);

            $this->gamestate->nextState('placeWell');
        }
    }

    function stWellPlaced() {
        $counter = self::getGameStateValue('counter');

        if ($counter == 0) {
            $this->gamestate->nextState('countRevenuePrestige');
        } else {
            $activePlayerId = self::getActivePlayerId();
            self::giveExtraTime($activePlayerId);

            $this->gamestate->nextState('placeWell');
        }
    }

    function stPlaceWellPassed() {
        $this->gamestate->nextState('countRevenuePrestige');
    }

    function stCountRevenuePrestige() {
        $playersInfos = self::loadPlayersInfos();
        $activePlayerId = self::getActivePlayerId();

        $revenue1 = self::getRiverRevenue('river1');
        $revenue2 = self::getRiverRevenue('river2');

        $camel_number = $revenue1 + $revenue2;

        $sql = "UPDATE player SET player_camel = LEAST(player_camel + $camel_number, 10) WHERE player_id = $activePlayerId";
        self::DbQuery($sql);

        $sql = "SELECT player_camel FROM player WHERE player_id = $activePlayerId";
        $player_camel = self::getUniqueValueFromDB($sql);

        self::notifyAllPlayers("camelsEarned", clienttranslate('${player_name} earns ${camel_number} ${camel_icon} camel(s) with huts'), array(
            'player_id' => $activePlayerId,
            'player_name' => self::getActivePlayerName(),
            'camel_number' => $camel_number,
            'camel_icon' => self::getIcon('camel'),
            'player_camel' => $player_camel
        ));

        $prestige1 = self::getInsidePrestige();
        $prestige2 = self::getOutsidePrestige();
        $prestige3 = self::getZigguratPrestige();

        $point_number = $prestige1 + $prestige2 + $prestige3;

        $sql = "UPDATE player SET player_score = player_score + $point_number WHERE player_id = $activePlayerId";
        self::DbQuery($sql);

        self::incStat($prestige1, "pointsScoredWithHuts", $activePlayerId);
        self::incStat($prestige2, "pointsScoredWithHuts", $activePlayerId);
        self::incStat($prestige3, "pointsScoredWithZigguratTiles", $activePlayerId);

        $sql = "SELECT player_score FROM player WHERE player_id = $activePlayerId";
        $player_score = self::getUniqueValueFromDB($sql);

        self::notifyAllPlayers("pointsScored", clienttranslate('${player_name} scores ${point_number} ${point_icon} point(s) with huts and ziggurats'), array(
            'player_id' => $activePlayerId,
            'player_name' => self::getActivePlayerName(),
            'point_number' => $point_number,
            'point_icon' => self::getIcon('point'),
            'player_score' => $player_score
        ));

        $activePlayerNumber = $playersInfos[$activePlayerId]['player_turn_order'];
        $playersNumber = self::getPlayersNumber();

        if ($activePlayerNumber == $playersNumber) {
            // Last player
            self::setGameStateValue('card_id', 0);
            self::setGameStateValue('offering', 0);

            self::activeFirstPlayer();

            self::_nextStatePerformAction();
        } else {
            // Activate next player
            self::activeNextPlayer();

            self::initializeHutCounter();
            $hutCounter = self::getGameStateValue('counter');

            $hutSpaces = self::getHutSpaces();

            if (($hutCounter == 0) || (count($hutSpaces) == 0)) {
                self::_nextStateUseFoodCard();
            } else {
                self::_nextStatePlaceHut();
            }
        }
    }

    function stZigguratBuilt() {
        self::_nextStatePerformAction();
    }

    function stZigguratCenterExtended() {
        self::_nextStatePerformAction();
    }

    function stZigguratRoofExtended() {
        self::_nextStatePerformAction();
    }

    function stHigherDignitaryInfluenced() {
        self::_nextStatePerformAction();
    }

    function stMiddleDignitaryInfluenced() {
        self::_nextStatePerformAction();
    }

    function stLowerDignitaryInfluenced() {
        self::_nextStatePerformAction();
    }

    function stOfferingMade() {
        self::_nextStatePerformAction();
    }

    function stFoodCardBought() {
        self::_nextStatePerformAction();
    }

    function stPlowCardBought() {
        self::_nextStatePerformAction();
    }

    function stPerformActionPassed() {
        $this->gamestate->nextState('endPerformAction');
    }

    function stEndPerformAction() {
        $playersInfos = self::loadPlayersInfos();
        $activePlayerId = self::getActivePlayerId();

        $activePlayerNumber = $playersInfos[$activePlayerId]['player_turn_order'];
        $playersNumber = self::getPlayersNumber();

        $sql = "UPDATE hexagon SET hexagon_type = 'ziggurat_base' WHERE hexagon_type = 'ziggurat_base_built' AND hexagon_type_arg = $activePlayerId";
        self::DbQuery($sql);

        $sql = "UPDATE hexagon SET hexagon_type = 'ziggurat_center' WHERE hexagon_type = 'ziggurat_center_built' AND hexagon_type_arg = $activePlayerId";
        self::DbQuery($sql);

        $sql = "UPDATE hexagon SET hexagon_type = 'ziggurat_roof' WHERE hexagon_type = 'ziggurat_roof_built' AND hexagon_type_arg = $activePlayerId";
        self::DbQuery($sql);

        if ($activePlayerNumber == $playersNumber) {
            // Last player
            $reign = self::getGameStateValue('reign');
            $turn = self::getGameStateValue('turn');

            if ($turn == $this->REIGN_TURN_NUMBER[$reign]) {
                // RULES: all the huts located on a river hexagon are
                //        removed from the board and go back to their
                //        owner's stock
                $river1_hexagons = self::getRegionHexagons('river1');
                $river2_hexagons = self::getRegionHexagons('river2');
                $river_hexagons = array_merge($river1_hexagons, $river2_hexagons);

                $players = self::getCollectionFromDB("SELECT player_id, player_name FROM player ORDER BY player_turn_order");

                foreach ($players as $player_id => $player) {
                    $player_huts = self::getObjectListFromDB("SELECT hexagon_q AS q, hexagon_r AS r FROM hexagon WHERE hexagon_type = 'hut_resupplied' AND hexagon_type_arg = $player_id");
                    $river_huts = array_values(array_uintersect($player_huts, $river_hexagons, 'self::hexagonCompare'));
                    $hut_number = count($river_huts);

                    if ($hut_number != 0) {
                        foreach ($river_huts as $hut) {
                            $q = $hut['q'];
                            $r = $hut['r'];

                            $sql = "DELETE FROM hexagon WHERE hexagon_q = $q AND hexagon_r = $r AND hexagon_type = 'hut_resupplied' AND hexagon_type_arg = $player_id";
                            self::DbQuery($sql);
                        }

                        $sql = "UPDATE player SET player_hut = player_hut + $hut_number WHERE player_id = $player_id";
                        self::DbQuery($sql);

                        self::notifyAllPlayers("hutsFlooded", clienttranslate('${player_name} removes ${hut_number} flooded hut(s)'), array(
                            'player_id' => $player_id,
                            'player_name' => $player['player_name'],
                            'hut_number' => $hut_number,
                            'huts' => $river_huts
                        ));
                    }
                }

                $dignitaries_rank = self::getDignitariesRank();
                $dignitaries_rank_number = count($dignitaries_rank);
                $dignitaries_rank_index = 1;

                if ($dignitaries_rank_number == 0) {
                    $expansion_cards = $this->cards->getCardsInLocation('expansion', null, 'card_location_arg');

                    foreach ($expansion_cards as $expansion_card) {
                        $this->cards->insertCardOnExtremePosition($expansion_card['id'], $expansion_card['type'] . '_discard', TRUE);
                    }

                    self::notifyAllPlayers("noDignitaryPointsScored", "", array(
                        'expansion_cards' => $expansion_cards
                    ));
                } else {
                    foreach ($dignitaries_rank as $player_id) {
                        $sql = "SELECT player_name FROM player WHERE player_id = $player_id";
                        $player_name = self::getUniqueValueFromDB($sql);

                        $expansions = $this->cards->getCardsInLocation('expansion');
                        $expansion_points = 0;

                        foreach ($expansions as $expansion) {
                            $expansion_points = $expansion_points + $expansion['type_arg'];
                        }

                        $sql = "UPDATE player SET player_score = player_score + $expansion_points WHERE player_id = $player_id";
                        self::DbQuery($sql);

                        self::incStat($expansion_points, "pointsScoredWithInfluence", $player_id);

                        $sql = "SELECT player_score FROM player WHERE player_id = $player_id";
                        $player_score = self::getUniqueValueFromDB($sql);

                        // RULES : (2 players) the second player wins nothing in the
                        //         1st Reign, and the value of the lowest card in
                        //         the other 2 Reigns
                        if (($playersNumber == 2) && ($dignitaries_rank_index == 1)) {
                            if (($dignitaries_rank_index == $dignitaries_rank_number) || ($reign == 1)) {
                                $expansion_cards = $this->cards->getCardsInLocation('expansion', null, 'card_location_arg');
                            } else {
                                $sql = "SELECT card_id FROM card WHERE card_location = 'expansion' ORDER BY card_type_arg, card_location_arg LIMIT 1";
                                $lowest_expansion_card_id = self::getUniqueValueFromDB($sql);

                                $sql = "SELECT card_id AS id, card_type AS type, card_type_arg AS type_arg, card_location AS location, card_location_arg AS location_arg FROM card WHERE card_location = 'expansion' AND card_id != $lowest_expansion_card_id ORDER BY card_location_arg";
                                $expansion_cards = self::getCollectionFromDB($sql);
                            }
                        } else {
                            if ($dignitaries_rank_index == $dignitaries_rank_number) {
                                $expansion_cards = $this->cards->getCardsInLocation('expansion', null, 'card_location_arg');
                            } else {
                                $sql = "SELECT card_id AS id, card_type AS type, card_type_arg AS type_arg, card_location AS location, card_location_arg AS location_arg FROM card WHERE card_location = 'expansion' ORDER BY card_type_arg DESC, card_location_arg LIMIT 1";
                                $expansion_cards = self::getCollectionFromDB($sql);
                            }
                        }

                        foreach ($expansion_cards as $expansion_card) {
                            $this->cards->insertCardOnExtremePosition($expansion_card['id'], $expansion_card['type'] . '_discard', TRUE);
                        }

                        self::notifyAllPlayers("dignitaryPointsScored", clienttranslate('${player_name} scores ${point_number} ${point_icon} point(s) with influence on dignitaries'), array(
                            'player_id' => $player_id,
                            'player_name' => $player_name,
                            'point_number' => $expansion_points,
                            'point_icon' => self::getIcon('point'),
                            'player_score' => $player_score,
                            'expansion_cards' => $expansion_cards
                        ));

                        $dignitaries_rank_index++;
                    }
                }

                foreach ($players as $player_id => $player) {
                    $higher_dignitary = self::getObjectListFromDB("SELECT location FROM higher_dignitary WHERE player_id = $player_id", TRUE);
                    $higher_dignitary_number = count($higher_dignitary);

                    if ($higher_dignitary_number != 0) {
                        switch ($higher_dignitary_number) {
                            case 1:
                                $higher_dignitary_points = 1;
                                break;
                            case 2:
                                $higher_dignitary_points = 4;
                                break;
                            case 3:
                                $higher_dignitary_points = 8;
                                break;
                        }

                        $sql = "UPDATE player SET player_score = player_score + $higher_dignitary_points WHERE player_id = $player_id";
                        self::DbQuery($sql);

                        self::incStat($higher_dignitary_points, "pointsScoredWithHigherDignitary", $player_id);

                        $sql = "SELECT player_score FROM player WHERE player_id = $player_id";
                        $player_score = self::getUniqueValueFromDB($sql);

                        $sql = "DELETE FROM higher_dignitary WHERE player_id = $player_id";
                        self::DbQuery($sql);

                        $sql = "UPDATE player SET player_hut = player_hut + $higher_dignitary_number WHERE player_id = $player_id";
                        self::DbQuery($sql);

                        self::notifyAllPlayers("higherDignitaryBonusProvided", clienttranslate('${player_name} scores ${point_number} ${point_icon} point(s) with the higher dignitary'), array(
                            'player_id' => $player_id,
                            'player_name' => $player['player_name'],
                            'point_number' => $higher_dignitary_points,
                            'point_icon' => self::getIcon('point'),
                            'player_score' => $player_score,
                            'higher_dignitary' => $higher_dignitary
                        ));
                    }
                }

                // RULES: all the players who have placed at least one hut
                //        on one of this dignitary's spaces get a Plow card
                //        from the Plow cards deck
                foreach ($players as $player_id => $player) {
                    $middle_dignitary = self::getObjectListFromDB("SELECT location FROM middle_dignitary WHERE player_id = $player_id", TRUE);
                    $middle_dignitary_number = count($middle_dignitary);

                    if ($middle_dignitary_number != 0) {
                        $sql = "DELETE FROM middle_dignitary WHERE player_id = $player_id";
                        self::DbQuery($sql);

                        $sql = "UPDATE player SET player_hut = player_hut + $middle_dignitary_number WHERE player_id = $player_id";
                        self::DbQuery($sql);

                        $player_plow_number = self::getUniqueValueFromDB("SELECT COUNT(*) FROM card WHERE card_type = 'plow' AND card_location = 'hand' AND card_location_arg = $player_id");

                        // RULES: a player can't own more than one Plow card
                        if ($player_plow_number != 0) {
                            self::notifyAllPlayers("noMiddleDignitaryBonusProvided", "", array(
                                'player_id' => $player_id,
                                'middle_dignitary' => $middle_dignitary
                            ));
                        } else {
                            $plow_card = $this->cards->getCardOnTop('plow');

                            $this->cards->moveCard($plow_card['id'], 'hand', $player_id );

                            self::notifyAllPlayers("middleDignitaryBonusProvided", clienttranslate('${player_name} gets ${card_number} ${card_icon} ${card_type} with the middle dignitary'), array(
                                'i18n' => array('card_type'),
                                'player_id' => $player_id,
                                'player_name' => $player['player_name'],
                                'card_number' => $plow_card['type_arg'],
                                'card_icon' => self::getIcon($plow_card['type']),
                                'card_type' => $this->CARD_TYPE_TRANSLATIONS[$plow_card['type']],
                                'plow_card' => $plow_card,
                                'middle_dignitary' => $middle_dignitary
                            ));
                        }
                    }
                }

                // RULES: for each hut placed on one of this dignitary's
                //        spaces, the players advance their disc by one
                //        space along the camel track
                foreach ($players as $player_id => $player) {
                    $lower_dignitary = self::getObjectListFromDB("SELECT location FROM lower_dignitary WHERE player_id = $player_id", TRUE);
                    $lower_dignitary_number = count($lower_dignitary);

                    if ($lower_dignitary_number != 0) {
                        $sql = "UPDATE player SET player_camel = LEAST(player_camel + $lower_dignitary_number, 10) WHERE player_id = $player_id";
                        self::DbQuery($sql);

                        $sql = "SELECT player_camel FROM player WHERE player_id = $player_id";
                        $player_camel = self::getUniqueValueFromDB($sql);

                        $sql = "DELETE FROM lower_dignitary WHERE player_id = $player_id";
                        self::DbQuery($sql);

                        $sql = "UPDATE player SET player_hut = player_hut + $lower_dignitary_number WHERE player_id = $player_id";
                        self::DbQuery($sql);

                        self::notifyAllPlayers("lowerDignitaryBonusProvided", clienttranslate('${player_name} earns ${camel_number} ${camel_icon} camel(s) with the lower dignitary'), array(
                            'player_id' => $player_id,
                            'player_name' => $player['player_name'],
                            'camel_number' => $lower_dignitary_number,
                            'camel_icon' => self::getIcon('camel'),
                            'player_camel' => $player_camel,
                            'lower_dignitary' => $lower_dignitary
                        ));
                    }
                }

                // RULES: each player scores a number of points equal to the
                //        number of ziggurat sites they own on the board,
                //        multiplied by the number indicated by their disc
                //        on the offerings track
                foreach ($players as $player_id => $player) {
                    // RULES: all ziggurat sites are counted even if the
                    //        building is unfinished (no center or no roof)
                    $sql = "SELECT COUNT(*) FROM hexagon WHERE hexagon_type = 'ziggurat_base' AND hexagon_type_arg = $player_id";
                    $ziggurat_number = self::getUniqueValueFromDB($sql);

                    $sql = "SELECT player_offering FROM player WHERE player_id = $player_id";
                    $player_offering = self::getUniqueValueFromDB($sql);

                    if ($player_offering != 0) {
                        switch ($player_offering) {
                            case 1:
                                $offering_multiplier = 1;
                                break;
                            case 2:
                            case 3:
                                $offering_multiplier = 2;
                                break;
                            case 4:
                            case 5:
                            case 6:
                                $offering_multiplier = 3;
                                break;
                            case 7:
                                $offering_multiplier = 4;
                                break;
                        }

                        $offering_points = $ziggurat_number * $offering_multiplier;

                        $sql = "UPDATE player SET player_score = player_score + $offering_points WHERE player_id = $player_id";
                        self::DbQuery($sql);

                        self::incStat($offering_points, "pointsScoredWithOfferings", $player_id);

                        $sql = "SELECT player_score FROM player WHERE player_id = $player_id";
                        $player_score = self::getUniqueValueFromDB($sql);

                        // RULES: each player's offering disc then goes back to
                        //        the initial space of the track
                        $sql = "UPDATE player SET player_offering = 0 WHERE player_id = $player_id";
                        self::DbQuery($sql);

                        self::notifyAllPlayers("offeringPointsScored", clienttranslate('${player_name} scores ${point_number} ${point_icon} point(s) with offerings'), array(
                            'player_id' => $player_id,
                            'player_name' => $player['player_name'],
                            'point_number' => $offering_points,
                            'point_icon' => self::getIcon('point'),
                            'player_score' => $player_score
                        ));
                    }
                }

                if ($reign == $this->REIGN_NUMBER) {
                    foreach ($players as $player_id => $player) {
                        // RULES: each player scores 1 point per ziggurat tile
                        //        they have built
                        $sql = "SELECT COUNT(*) FROM hexagon WHERE hexagon_type IN ('ziggurat_base', 'ziggurat_center', 'ziggurat_roof') AND hexagon_type_arg = $player_id";
                        $ziggurat_points = self::getUniqueValueFromDB($sql);

                        // RULES: each player scores 1 point per Plow card
                        $sql = "SELECT COUNT(*) FROM card WHERE card_type = 'plow' AND card_location = 'hand' AND card_location_arg = $player_id";
                        $ploy_points = self::getUniqueValueFromDB($sql);

                        // RULES: each player scores 1 point for each group of
                        //        two remaining camels
                        $sql = "SELECT player_camel FROM player WHERE player_id = $player_id";
                        $player_camel = self::getUniqueValueFromDB($sql);
                        $camel_points = (int)($player_camel / 2);

                        $point_number = $ziggurat_points + $ploy_points + $camel_points;

                        $sql = "UPDATE player SET player_score = player_score + $point_number WHERE player_id = $player_id";
                        self::DbQuery($sql);

                        self::incStat($ziggurat_points, "pointsScoredWithZigguratTiles", $player_id);
                        self::incStat($ploy_points, "pointsScoredWithRemainingPlow", $player_id);
                        self::incStat($camel_points, "pointsScoredWithRemainingCamels", $player_id);

                        $sql = "SELECT player_score FROM player WHERE player_id = $player_id";
                        $player_score = self::getUniqueValueFromDB($sql);

                        self::notifyAllPlayers("pointsScored", clienttranslate('${player_name} scores ${point_number} ${point_icon} bonus point(s)'), array(
                            'player_id' => $player_id,
                            'player_name' => $player['player_name'],
                            'point_number' => $point_number,
                            'point_icon' => self::getIcon('point'),
                            'player_score' => $player_score
                        ));
                    }

                    $this->gamestate->nextState('gameEnd');
                } else {
                    // RULES: the first player draws a new Expansion card and
                    //        places it in the first Expansion slot
                    $expansion_card = $this->cards->pickCardForLocation('expansion_deck', 'expansion', 0);

                    // RULES: (4 players) before the start of the 2nd and 3rd
                    //        Reigns, place the Bonus card in its spot on the
                    //        board
                    $bonus_card = null;

                    if ($playersNumber == 4) {
                        $bonus_card = $this->cards->pickCardForLocation('bonus_discard', 'expansion', 3);
                    }

                    // RULES: all players put their huts back at the bottom of
                    //        their hexagons so that they won't hide the food
                    //        symbols
                    $sql = "UPDATE hexagon SET hexagon_type = 'hut' WHERE hexagon_type = 'hut_resupplied'";
                    self::DbQuery($sql);

                    $huts = self::getObjectListFromDB("SELECT hexagon_q AS q, hexagon_r AS r FROM hexagon WHERE hexagon_type = 'hut'");

                    $oldSowings = $this->cards->getCardsInLocation('sowing', null, 'card_location_arg');

                    foreach ($oldSowings as $oldSowing) {
                        $this->cards->insertCardOnExtremePosition($oldSowing['id'], 'discard', TRUE);
                    }

                    self::setGameStateValue('reign', $reign + 1);
                    self::setGameStateValue('turn', 1);

                    self::notifyAllPlayers("reignEnded", clienttranslate('End of reign n°${reign}'), array(
                        'reign' => $reign,
                        'expansion_card' => $expansion_card,
                        'bonus_card' => $bonus_card,
                        'huts' => $huts,
                        'oldSowings' => $oldSowings
                    ));

                    self::_setupSowing();

                    $activePlayerId = self::activeFirstPlayer();
                    self::giveExtraTime($activePlayerId);

                    $this->gamestate->nextState('chooseFoodColumn');
                }
            } else {
                // RULES: the first player draws a new Expansion card and places
                //        it in the slot below the previous card
                $expansion_card = $this->cards->pickCardForLocation('expansion_deck', 'expansion', $turn);

                // RULES: all players put their huts back at the bottom of their
                //        hexagons so that they won't hide the resources
                $sql = "UPDATE hexagon SET hexagon_type = 'hut' WHERE hexagon_type = 'hut_resupplied'";
                self::DbQuery($sql);

                $huts = self::getObjectListFromDB("SELECT hexagon_q AS q, hexagon_r AS r FROM hexagon WHERE hexagon_type = 'hut'");

                $oldSowings = $this->cards->getCardsInLocation('sowing', null, 'card_location_arg');

                foreach ($oldSowings as $oldSowing) {
                    $this->cards->insertCardOnExtremePosition($oldSowing['id'], 'discard', TRUE);
                }

                self::setGameStateValue('turn', $turn + 1);

                self::notifyAllPlayers("turnEnded", clienttranslate('End of turn n°${turn} of reign n°${reign}'), array(
                    'turn' => $turn,
                    'reign' => $reign,
                    'expansion_card' => $expansion_card,
                    'huts' => $huts,
                    'oldSowings' => $oldSowings
                ));

                self::_setupSowing();

                $activePlayerId = self::activeFirstPlayer();
                self::giveExtraTime($activePlayerId);

                $this->gamestate->nextState('chooseFoodColumn');
            }
        } else {
            // Activate next player
            self::setGameStateValue('card_id', 0);
            self::setGameStateValue('offering', 0);

            self::activeNextPlayer();

            self::_nextStatePerformAction();
        }
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    function zombieTurn($state, $active_player) {
        $state_name = $state['name'];

        if ($state_name == 'chooseStartingSpace') {
            $startingSpaces = $state['args']['startingSpaces'];
            $startingSpace = reset($startingSpaces);

            self::_chooseStartingSpace($startingSpace['q'], $startingSpace['r']);

            return;
        }

        if ($state_name == 'chooseFoodCard') {
            $foodCards = $state['args']['foodCards'];
            $foodCard = reset($foodCards);

            self::_chooseFoodCard($foodCard['id']);

            return;
        }

        if ($state_name == 'chooseFoodColumn') {
            $foodColumns = $state['args']['foodColumns'];
            $foodColumn = reset($foodColumns);

            self::_chooseFoodColumn($foodColumn);

            return;
        }

        if ($state_name == 'placeHut') {
            $hutSpaces = $state['args']['hutSpaces'];
            $hutSpace = reset($hutSpaces);

            self::_placeHut($hutSpace['q'], $hutSpace['r']);

            return;
        }

        if ($state_name == 'useFoodCard') {
            $foodCards = $state['args']['foodCards'];
            $foodCard = reset($foodCards);

            self::_useFoodCard($foodCard['id']);

            return;
        }

        if ($state_name == 'resupplyHut') {
            $huts = $state['args']['huts'];
            $hut = reset($huts);

            self::_resupplyHut($hut['q'], $hut['r']);

            return;
        }

        if ($state_name == 'placeWell') {
            self::_passPlaceWell();

            return;
        }

        if ($state_name == 'performAction') {
            self::_passPerformAction();

            return;
        }

        throw new feException("Zombie mode not supported at this game state: " . $state_name);
    }
}
