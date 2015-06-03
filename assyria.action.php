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
 * assyria.action.php
 *
 */
class action_assyria extends APP_GameAction {

    public function __default() {
        if (self::isArg('notifwindow')) {
            $this->view = 'common_notifwindow';
            $this->viewArgs['table'] = self::getArg('table', AT_posint, TRUE);
        } else {
            $this->view = 'assyria_assyria';
        }
    }

    public function chooseFoodCard() {
        self::setAjaxMode();

        $id = self::getArg('id', AT_posint, TRUE);
        $this->game->chooseFoodCard($id);

        self::ajaxResponse();
    }

    public function chooseFoodColumn() {
        self::setAjaxMode();

        $column = self::getArg('column', AT_posint, TRUE);
        $this->game->chooseFoodColumn($column);

        self::ajaxResponse();
    }

    public function placeHut() {
        self::setAjaxMode();

        $q = self::getArg('q', AT_int, TRUE);
        $r = self::getArg('r', AT_int, TRUE);
        $this->game->placeHut($q, $r);

        self::ajaxResponse();
    }

    public function useFoodCard() {
        self::setAjaxMode();

        $id = self::getArg('id', AT_posint, TRUE);
        $this->game->useFoodCard($id);

        self::ajaxResponse();
    }

    public function chooseStartingSpace() {
        self::setAjaxMode();

        $q = self::getArg('q', AT_int, TRUE);
        $r = self::getArg('r', AT_int, TRUE);
        $this->game->chooseStartingSpace($q, $r);

        self::ajaxResponse();
    }

    public function resupplyHut() {
        self::setAjaxMode();

        $q = self::getArg('q', AT_int, TRUE);
        $r = self::getArg('r', AT_int, TRUE);
        $this->game->resupplyHut($q, $r);

        self::ajaxResponse();
    }

    public function placeWell() {
        self::setAjaxMode();

        $q = self::getArg('q', AT_int, TRUE);
        $r = self::getArg('r', AT_int, TRUE);
        $t = self::getArg('t', AT_int, TRUE);
        $this->game->placeWell($q, $r, $t);

        self::ajaxResponse();
    }

    public function passPlaceWell() {
        self::setAjaxMode();

        $this->game->passPlaceWell();

        self::ajaxResponse();
    }

    public function buildZiggurat() {
        self::setAjaxMode();

        $q = self::getArg('q', AT_int, TRUE);
        $r = self::getArg('r', AT_int, TRUE);
        $this->game->buildZiggurat($q, $r);

        self::ajaxResponse();
    }

    public function extendZigguratCenter() {
        self::setAjaxMode();

        $q = self::getArg('q', AT_int, TRUE);
        $r = self::getArg('r', AT_int, TRUE);
        $this->game->extendZigguratCenter($q, $r);

        self::ajaxResponse();
    }

    public function extendZigguratRoof() {
        self::setAjaxMode();

        $q = self::getArg('q', AT_int, TRUE);
        $r = self::getArg('r', AT_int, TRUE);
        $this->game->extendZigguratRoof($q, $r);

        self::ajaxResponse();
    }

    public function influenceHigherDignitary() {
        self::setAjaxMode();

        $this->game->influenceHigherDignitary();

        self::ajaxResponse();
    }

    public function influenceMiddleDignitary() {
        self::setAjaxMode();

        $this->game->influenceMiddleDignitary();

        self::ajaxResponse();
    }

    public function influenceLowerDignitary() {
        self::setAjaxMode();

        $this->game->influenceLowerDignitary();

        self::ajaxResponse();
    }

    public function makeOffering() {
        self::setAjaxMode();

        $offering = self::getArg('offering', AT_posint, TRUE);
        $this->game->makeOffering($offering);

        self::ajaxResponse();
    }

    public function buyFoodCard() {
        self::setAjaxMode();

        $id = self::getArg('id', AT_posint, TRUE);
        $this->game->buyFoodCard($id);

        self::ajaxResponse();
    }

    public function buyPlowCard() {
        self::setAjaxMode();

        $id = self::getArg('id', AT_posint, TRUE);
        $this->game->buyPlowCard($id);

        self::ajaxResponse();
    }

    public function passPerformAction() {
        self::setAjaxMode();

        $this->game->passPerformAction();

        self::ajaxResponse();
    }
}
