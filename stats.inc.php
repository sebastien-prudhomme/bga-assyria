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
 * stats.inc.php
 *
 */

$stats_type = array(
    'table' => array(
    ),
    'player' => array(
        "pointsScoredWithHuts" => array(
            "id"=> 10,
            "name" => totranslate("Points scored with huts"),
            "type" => "int"
        ),
        "pointsScoredWithZigguratTiles" => array(
            "id"=> 11,
            "name" => totranslate("Points scored with ziggurats"),
            "type" => "int"
        ),
        "pointsScoredWithWells" => array(
            "id"=> 12,
            "name" => totranslate("Points scored with wells"),
            "type" => "int"
        ),
        "pointsScoredWithInfluence" => array(
            "id"=> 13,
            "name" => totranslate("Points scored with influence on dignitaries"),
            "type" => "int"
        ),
        "pointsScoredWithHigherDignitary" => array(
            "id"=> 14,
            "name" => totranslate("Points scored with the higher dignitary"),
            "type" => "int"
        ),
        "pointsScoredWithOfferings" => array(
            "id"=> 15,
            "name" => totranslate("Points scored with offerings"),
            "type" => "int"
        ),
        "pointsScoredWithRemainingPlow" => array(
            "id"=> 16,
            "name" => totranslate("Points scored with remaining plow"),
            "type" => "int"
        ),
        "pointsScoredWithRemainingCamels" => array(
            "id"=> 17,
            "name" => totranslate("Points scored with remaining camels"),
            "type" => "int"
        )
    )
);
