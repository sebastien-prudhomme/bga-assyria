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
 * assyria.view.php
 *
 */

require_once( APP_BASE_PATH . "view/common/game.view.php" );

class view_assyria_assyria extends game_view {
    protected function getGameName() {
        return "assyria";
    }

    protected function build_page($viewArgs) {
        self::_buildTableInfos();
        self::_buildDraftArea();
        self::_buildHexagonLocations();
        self::_buildWellLocations();
        self::_buildCamelLocations();
        self::_buildTurnOrderLocations();
        self::_buildHigherDignitaryLocations();
        self::_buildMiddleDignitaryLocations();
        self::_buildLowerDignitaryLocations();
        self::_buildExpansionLocations();
        self::_buildOfferingLocations();
        self::_buildHarvestLocations();
        self::_buildSowingLocations();
        self::_buildPlayerAreas();
    }

    private function _buildTableInfos() {
        $this->tpl['REIGN_TITLE'] = self::_("Reign n°");
        $this->tpl['TURN_TITLE'] = self::_("Turn n°");
    }

    private function _buildDraftArea() {
        $draft_card_number = $this->game->cards->countCardInLocation('draft');

        if ($draft_card_number == 0) {
            $this->tpl['DRAFT_DISPLAY'] = "none";
        } else {
            $this->tpl['DRAFT_DISPLAY'] = "";
        }

        $this->tpl['DRAFT_TITLE'] = self::_("Initial draft");
    }

    private function _buildHexagonLocations() {
        $X_ORIGIN = 47;
        $Y_ORIGIN = 45;

        $X_SCALE = 29.1;
        $Y_SCALE = 29.1;

        $this->page->begin_block("assyria_assyria", "hexagon_location");

        foreach ($this->game->HEXAGON_SPACES as $hexagon) {
            $q = $hexagon['q'];
            $r = $hexagon['r'];

            $this->page->insert_block("hexagon_location", array(
                'Q' => $q,
                'R' => $r,
                'LEFT' => round($X_ORIGIN + $X_SCALE * 3 / 2 * $q),
                'TOP' => round($Y_ORIGIN + $Y_SCALE * sqrt(3) * ($r + $q / 2))
            ));
        }
    }

    private function _buildWellLocations() {
        $X_ORIGIN = 64;
        $Y_ORIGIN = 62;

        $X_SCALE = 29.1;
        $Y_SCALE = 29.1;

        $this->page->begin_block("assyria_assyria", "well_location");

        foreach ($this->game->WELL_SPACES as $well) {
            $q = $well['q'];
            $r = $well['r'];
            $t = $well['t'];

            $this->page->insert_block("well_location", array(
                'Q' => $q,
                'R' => $r,
                'T' => $t,
                'LEFT' => round($X_ORIGIN + $X_SCALE * 3 / 2 * $q + $t * $X_SCALE),
                'TOP' => round($Y_ORIGIN + $Y_SCALE * sqrt(3) * ($r + $q / 2))
            ));
        }
    }

    private function _buildCamelLocations() {
        $X_ORIGIN = 524;
        $Y_ORIGIN = 305;

        $Y_SCALE = 19.2;

        $this->page->begin_block("assyria_assyria", "camel_location");

        for ($i = 0; $i < 11; $i++) {
            $this->page->insert_block("camel_location", array(
                'I' => $i,
                'LEFT' => $X_ORIGIN,
                'TOP' => round($Y_ORIGIN - $i * $Y_SCALE)
            ));
        }
    }

    private function _buildTurnOrderLocations() {
        $X_ORIGIN = 493;
        $Y_ORIGIN = 378;

        $X_SCALE = 19.3;

        $this->page->begin_block("assyria_assyria", "turn_order_location");

        for ($i = 0; $i < 4; $i++) {
            $this->page->insert_block("turn_order_location", array(
                'I' => $i,
                'LEFT' => round($X_ORIGIN + $i * $X_SCALE),
                'TOP' => $Y_ORIGIN
            ));
        }
    }

    private function _buildHigherDignitaryLocations() {
        $X_ORIGIN = 609;
        $Y_ORIGIN = 32;

        $Y_SCALE = 19.2;

        $this->page->begin_block("assyria_assyria", "higher_dignitary_location");

        for ($i = 0; $i < 3; $i++) {
            $this->page->insert_block("higher_dignitary_location", array(
                'I' => $i,
                'LEFT' => $X_ORIGIN,
                'TOP' => round($Y_ORIGIN + $i * $Y_SCALE)
            ));
        }
    }

    private function _buildMiddleDignitaryLocations() {
        $X_ORIGIN = 609;
        $Y_ORIGIN = 132;

        $Y_SCALE = 19.2;

        $this->page->begin_block("assyria_assyria", "middle_dignitary_location");

        for ($i = 0; $i < 4; $i++) {
            $this->page->insert_block("middle_dignitary_location", array(
                'I' => $i,
                'LEFT' => $X_ORIGIN,
                'TOP' => round($Y_ORIGIN + $i * $Y_SCALE)
            ));
        }
    }

    private function _buildLowerDignitaryLocations() {
        $X_ORIGIN = 609;
        $Y_ORIGIN = 251;

        $Y_SCALE = 19.2;

        $this->page->begin_block("assyria_assyria", "lower_dignitary_location");

        for ($i = 0; $i < 6; $i++) {
            $this->page->insert_block("lower_dignitary_location", array(
                'I' => $i,
                'LEFT' => $X_ORIGIN,
                'TOP' => round($Y_ORIGIN + $i * $Y_SCALE)
            ));
        }
    }

    private function _buildExpansionLocations() {
        $X_ORIGIN = 678;
        $Y_ORIGIN = 30;

        $Y_SCALE = 88.0;

        $this->page->begin_block("assyria_assyria", "expansion_location");

        for ($i = 0; $i < 4; $i++) {
            $this->page->insert_block("expansion_location", array(
                'I' => $i,
                'LEFT' => $X_ORIGIN,
                'TOP' => round($Y_ORIGIN + $i * $Y_SCALE)
            ));
        }
    }

    private function _buildOfferingLocations() {
        $X_ORIGIN = 183;
        $Y_ORIGIN = 448;

        $X_SCALE = 19.2;

        $this->page->begin_block("assyria_assyria", "offering_location");

        for ($i = 0; $i < 8; $i++) {
            $this->page->insert_block("offering_location", array(
                'I' => $i,
                'LEFT' => round($X_ORIGIN + $i * $X_SCALE),
                'TOP' => $Y_ORIGIN
            ));
        }
    }

    private function _buildHarvestLocations() {
        $X_ORIGIN = 361;
        $Y_ORIGIN = 448;

        $X_SCALE = 53.5;

        $this->page->begin_block("assyria_assyria", "harvest_location");

        for ($i = 0; $i < 5; $i++) {
            $this->page->insert_block("harvest_location", array(
                'I' => $i,
                'LEFT' => round($X_ORIGIN + $i * $X_SCALE),
                'TOP' => $Y_ORIGIN
            ));
        }
    }

    private function _buildSowingLocations() {
        $X_ORIGIN = 345;
        $Y_ORIGIN = 468;

        $X_SCALE = 53.4;
        $Y_SCALE = 83.0;

        $this->page->begin_block("assyria_assyria", "sowing_location");

        for ($i = 0; $i < 10; $i++) {
            $this->page->insert_block("sowing_location", array(
                'I' => $i,
                'LEFT' => round($X_ORIGIN + ($i % 5) * $X_SCALE),
                'TOP' => round($Y_ORIGIN + (int) ($i / 5) * $Y_SCALE)
            ));
        }
    }

    private function _buildPlayerAreas() {
        $this->page->begin_block("assyria_assyria", "player_area");

        $players = $this->game->loadPlayersBasicInfos();

        foreach (array_keys($players) as $player_id) {
            $this->page->insert_block("player_area", array(
                'PLAYER_ID' => $player_id,
                'PLAYER_NAME' => $players[$player_id]['player_name'],
                'PLAYER_COLOR' => $players[$player_id]['player_color']
            ));
        }
    }
}
