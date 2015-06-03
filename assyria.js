/**
 * ------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Assyria implementation: © Sebastien Prud'homme <daikinee@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * assyria.js
 *
 */

define([
    "dojo/_base/connect",
    "dojo/_base/declare",
    "dojo/_base/fx",
    "dojo/_base/lang",
    "dojo/dom",
    "dojo/dom-attr",
    "dojo/dom-class",
    "dojo/dom-construct",
    "dojo/dom-style",
    "dojo/query",
    "dojo/string",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/stock",
    "ebg/zone"
],
function(connect, declare, fx, lang, dom, domAttr, domClass, domConstruct, domStyle, query, string) {

return declare("bgagame.assyria", ebg.core.gamegui, {
    constructor: function() {
        this.ANIMATION_DURATION = 1000;
        this.ANIMATION_WAIT = 100;

        this.HIGHER_DIGNITARY_COST = 4;
        this.MIDDLE_DIGNITARY_COST = 3;
        this.LOWER_DIGNITARY_COST = 2;

        this.HIGHER_DIGNITARY_INFLUENCE = 3;
        this.MIDDLE_DIGNITARY_INFLUENCE = 2;
        this.LOWER_DIGNITARY_INFLUENCE = 1;

        this.HIGHER_DIGNITARY_POINT_FOR_1 = 1;
        this.HIGHER_DIGNITARY_POINT_FOR_2 = 4;
        this.HIGHER_DIGNITARY_POINT_FOR_3 = 8;

        this.PLOW_COST = 2;

        this.WELL_POINT_DURING_1 = 6;
        this.WELL_POINT_DURING_2 = 5;
        this.WELL_POINT_DURING_3 = 4;

        this.ZIGGURAT_BASE_COST = 6;
        this.ZIGGURAT_CENTER_COST = 3;
        this.ZIGGURAT_ROOF_COST = 2;

        this.card_type_number = 17;
        this.expansion_type_number = 4;
        this.card_width = 50;
        this.card_height = 79;

        this.reign_counter = {};
        this.turn_counter = {};
        this.well_counter = {};
        this.card_deck_counter = {};
        this.discard_counter = {};
        this.expansion_deck_counter = {};
        this.expansion_discard_counter = {};
        this.hut_counters = {};
        this.ziggurat_base_counters = {};
        this.ziggurat_center_counters = {};
        this.ziggurat_roof_counters = {};

        this.draft_card_stock = {};
        this.discard_card_stock = {};
        this.discard_expansion_stock = {};
        this.player_card_stocks = {};
        this.sowing_card_stocks = {};
        this.plow_card_stock = {};
        this.expansion_card_stocks = {};
        this.camel_zones = {};
        this.offering_zones = {};

        this.hexagon_connections = [];
        this.temporary_connections = [];
        this.temporary_tooltips = [];

        declare("bgagame.assyria.counter", ebg.counter, {
            create: function() {
                this.inherited(arguments);
                this._hideZeroValue();
            },
            setValue: function() {
                this.inherited(arguments);
                this._hideZeroValue();
            },
            makeCounterProgress: function() {
                this.inherited(arguments);
                this._hideZeroValue();
            },
            _hideZeroValue: function() {
                if (this.target_value == 0) {
                    domStyle.set(this.span, 'visibility', 'hidden');
                } else {
                    domStyle.set(this.span, 'visibility', 'visible');
                }
            }
        });
    },
    setup: function(gamedatas)
    {
        var camel_help = '<div class="help help_info"></div><div class="help">';
        camel_help += _('Camel track');
        camel_help += '</div>';

        this.addTooltipHtml('camel_help', camel_help);

        var turn_order_help = '<div class="help help_info"></div><div class="help">';
        turn_order_help += _('Turn order track');
        turn_order_help += '</div>';

        this.addTooltipHtml('turn_order_help', turn_order_help);

        var higher_dignitary_help = '<div class="help help_info"></div><div class="help">';
        higher_dignitary_help += string.substitute(_('At end of a reign, the higher dignitary grants ${point_number} influences per hut<br/>and a bonus of ${point_number_for_1}/${point_number_for_2}/${point_number_for_3} ${point_icon} point(s) for 1/2/3 hut(s)'), { point_number: this.HIGHER_DIGNITARY_INFLUENCE, point_number_for_1: this.HIGHER_DIGNITARY_POINT_FOR_1, point_number_for_2: this.HIGHER_DIGNITARY_POINT_FOR_2, point_number_for_3: this.HIGHER_DIGNITARY_POINT_FOR_3, point_icon: this.getIcon('point') });
        higher_dignitary_help += '</div>';

        this.addTooltipHtml('higher_dignitary_help', higher_dignitary_help);

        var middle_dignitary_help = '<div class="help help_info"></div><div class="help">';
        middle_dignitary_help += string.substitute(_('At end of a reign, the middle dignitary grants ${point_number} influences per hut<br/>and a bonus of ${card_number} ${card_icon} ${card_type}'), { point_number: this.MIDDLE_DIGNITARY_INFLUENCE, card_number: 1, card_icon: this.getIcon('plow'), card_type: _('plow') });
        middle_dignitary_help += '</div>';

        this.addTooltipHtml('middle_dignitary_help', middle_dignitary_help);

        var lower_dignitary_help = '<div class="help help_info"></div><div class="help">';
        lower_dignitary_help += string.substitute(_('At end of a reign, the lower dignitary grants ${point_number} influence per hut<br/>and a bonus of ${camel_number} ${camel_icon} camel per hut'), { point_number: this.LOWER_DIGNITARY_INFLUENCE, camel_number: 1, camel_icon: this.getIcon('camel') });
        lower_dignitary_help += '</div>';

        this.addTooltipHtml('lower_dignitary_help', lower_dignitary_help);

        var plow_discard_help = '<div class="help help_info"></div><div class="help">';
        plow_discard_help += _('Plow discard');
        plow_discard_help += '</div>';

        this.addTooltipHtml('plow_location', plow_discard_help);

        var well_help = '<div class="help help_info"></div><div class="help">';
        well_help += string.substitute(_('A well built during reign n°1/2/3 grants ${point_number_during_1}/${point_number_during_2}/${point_number_during_3} ${point_icon} points'), { point_number_during_1: this.WELL_POINT_DURING_1, point_number_during_2: this.WELL_POINT_DURING_2, point_number_during_3: this.WELL_POINT_DURING_3, point_icon: this.getIcon('point') });
        well_help += '</div>';

        this.addTooltipHtml('well_help', well_help);

        var ziggurat_help = '<div class="help help_info"></div><div class="help">';
        ziggurat_help += string.substitute(_('Build a ziggurat for ${camel_number} ${camel_icon} camels'), { camel_number: this.ZIGGURAT_BASE_COST, camel_icon: this.getIcon('camel') });
        ziggurat_help += '<br/>';
        ziggurat_help += string.substitute(_('Extend a ziggurat with a center for ${camel_number} ${camel_icon} camels'), { camel_number: this.ZIGGURAT_CENTER_COST, camel_icon: this.getIcon('camel') });
        ziggurat_help += '<br/>';
        ziggurat_help += string.substitute(_('Extend a ziggurat with a roof for ${camel_number} ${camel_icon} camels'), { camel_number: this.ZIGGURAT_ROOF_COST, camel_icon: this.getIcon('camel') });
        ziggurat_help += '</div>';

        this.addTooltipHtml('ziggurat_help', ziggurat_help);

        var offering_help = '<div class="help help_info"></div><div class="help">';
        offering_help += _('Offering track');
        offering_help += '<br/>';
        offering_help += string.substitute(_('At end of a reign, score a number of ${point_icon} points equal to the number of ziggurat sites,<br/>multiplied by the number indicated by the disc'), { point_icon: this.getIcon('point') });
        offering_help += '</div>';

        this.addTooltipHtml('offering_help', offering_help);

        var well_stock_help = '<div class="help help_info"></div><div class="help">';
        well_stock_help += _('Well stock');
        well_stock_help += '</div>';

        this.addTooltipHtml('well_stock_help', well_stock_help);

        var expansion_deck_help = '<div class="help help_info"></div><div class="help">';
        expansion_deck_help += _('Expansion deck');
        expansion_deck_help += '</div>';

        this.addTooltipHtml('expansion_deck_help', expansion_deck_help);

        var expansion_discard_help = '<div class="help help_info"></div><div class="help">';
        expansion_discard_help += _('Expansion discard');
        expansion_discard_help += '</div>';

        this.addTooltipHtml('expansion_discard_help', expansion_discard_help);

        var card_deck_help = '<div class="help help_info"></div><div class="help">';
        card_deck_help += _('Food deck');
        card_deck_help += '</div>';

        this.addTooltipHtml('card_deck_help', card_deck_help);

        var discard_help = '<div class="help help_info"></div><div class="help">';
        discard_help += _('Food discard');
        discard_help += '</div>';

        this.addTooltipHtml('discard_help', discard_help);

        // Setting up player boards
        for (var player_id in gamedatas.players)
        {
            var player = gamedatas.players[player_id];

            var player_board = dom.byId('player_board_' + player_id);
            domConstruct.place(this.format_block('jstpl_player_counter', player), player_board);

            var hut_help = '<div class="help help_info"></div><div class="help">';
            hut_help += _('Available huts');
            hut_help += '</div>';

            this.addTooltipHtml('hut_help_' + player_id, hut_help);

            var ziggurat_base_help = '<div class="help help_info"></div><div class="help">';
            ziggurat_base_help += _('Available ziggurat bases');
            ziggurat_base_help += '</div>';

            this.addTooltipHtml('ziggurat_base_help_' + player_id, ziggurat_base_help);

            var ziggurat_center_help = '<div class="help help_info"></div><div class="help">';
            ziggurat_center_help += _('Available ziggurat centers');
            ziggurat_center_help += '</div>';

            this.addTooltipHtml('ziggurat_center_help_' + player_id, ziggurat_center_help);

            var ziggurat_roof_help = '<div class="help help_info"></div><div class="help">';
            ziggurat_roof_help += _('Available ziggurat roofs');
            ziggurat_roof_help += '</div>';

            this.addTooltipHtml('ziggurat_roof_help_' + player_id, ziggurat_roof_help);

            var hut_counter = new ebg.counter();
            hut_counter.create(dom.byId('hut_' + player_id));
            hut_counter.setValue(player.hut);

            this.hut_counters[player_id] = hut_counter;

            var ziggurat_base_counter = new ebg.counter();
            ziggurat_base_counter.create(dom.byId('ziggurat_base_' + player_id));
            ziggurat_base_counter.setValue(player.ziggurat_base);

            this.ziggurat_base_counters[player_id] = ziggurat_base_counter;

            var ziggurat_center_counter = new ebg.counter();
            ziggurat_center_counter.create(dom.byId('ziggurat_center_' + player_id));
            ziggurat_center_counter.setValue(player.ziggurat_center);

            this.ziggurat_center_counters[player_id] = ziggurat_center_counter;

            var ziggurat_roof_counter = new ebg.counter();
            ziggurat_roof_counter.create(dom.byId('ziggurat_roof_' + player_id));
            ziggurat_roof_counter.setValue(player.ziggurat_roof);

            this.ziggurat_roof_counters[player_id] = ziggurat_roof_counter;
        }

        // TODO: Set up your game interface here, according to "gamedatas"
        this.reign_counter = new bgagame.assyria.counter();
        this.reign_counter.create(dom.byId('reign'));
        this.reign_counter.setValue(gamedatas.reign);

        this.turn_counter = new bgagame.assyria.counter();
        this.turn_counter.create(dom.byId('turn'));
        this.turn_counter.setValue(gamedatas.turn);

        for (var i in gamedatas.board_hexagons)
        {
            var hexagon = gamedatas.board_hexagons[i];

            this.addObjectOnBoard(hexagon.q, hexagon.r, hexagon.type, hexagon.type_arg);
        }

        for (var i in gamedatas.board_wells)
        {
            var well = gamedatas.board_wells[i];

            this.addWellOnBoard(well.q, well.r, well.t);
        }

        this.well_counter = new bgagame.assyria.counter();
        this.well_counter.create(dom.byId('well_stock'));
        this.well_counter.setValue(gamedatas.well_stock);

        if (gamedatas.card_deck_number != 0) {
            domStyle.set('card_deck', 'visibility', 'visible');
        }

        this.card_deck_counter = new bgagame.assyria.counter();
        this.card_deck_counter.create(dom.byId('card_deck_number'));
        this.card_deck_counter.setValue(gamedatas.card_deck_number);

        this.discard_counter = new bgagame.assyria.counter();
        this.discard_counter.create(dom.byId('discard_number'));
        this.discard_counter.setValue(gamedatas.discard.length);

        this.expansion_deck_counter = new bgagame.assyria.counter();
        this.expansion_deck_counter.create(dom.byId('expansion_deck_number'));
        this.expansion_deck_counter.setValue(gamedatas.expansion_deck_number);

        this.expansion_discard_counter = new bgagame.assyria.counter();
        this.expansion_discard_counter.create(dom.byId('expansion_discard_number'));
        this.expansion_discard_counter.setValue(gamedatas.expansion_discard.length);

        // Setting up discard area
        var discard_card_stock = new ebg.stock();
        discard_card_stock.create(this, dom.byId('discard_area'), this.card_width, this.card_height);
        discard_card_stock.setOverlap(0.1, 0.1);
        discard_card_stock.setSelectionMode(0);
        discard_card_stock.autowidth = true;
        discard_card_stock.item_margin = 0;
        discard_card_stock.order_items = false;

        // Create cards types:
        for (var i = 0; i < this.card_type_number; i++)
        {
            discard_card_stock.addItemType(i, i, g_gamethemeurl + 'img/cards.png', i);
        }

        for (var i in gamedatas.discard)
        {
            var card = gamedatas.discard[i];
            var card_type_id = this.getCardTypeId(card.type, card.type_arg);
            discard_card_stock.addToStockWithId(card_type_id, card.id);
        }

        this.discard_card_stock = discard_card_stock;

        // Setting up expansion discard area
        var discard_expansion_stock = new ebg.stock();
        discard_expansion_stock.create(this, dom.byId('expansion_discard_area'), this.card_width, this.card_height);
        discard_expansion_stock.setOverlap(0.1, 0.1);
        discard_expansion_stock.setSelectionMode(0);
        discard_expansion_stock.autowidth = true;
        discard_expansion_stock.item_margin = 0;
        discard_expansion_stock.order_items = false;

        // Create cards types:
        for (var i = 0; i < this.expansion_type_number; i++)
        {
            discard_expansion_stock.addItemType(i, i, g_gamethemeurl + 'img/expansions.png', i);
        }

        for (var i in gamedatas.expansion_discard)
        {
            var card = gamedatas.expansion_discard[i];
            var card_type_id = this.getExpansionTypeId(card.type, card.type_arg);
            discard_expansion_stock.addToStockWithId(card_type_id, card.id);
        }

        this.discard_expansion_stock = discard_expansion_stock;

        // Setting up draft area
        var draft_card_stock = new ebg.stock();
        draft_card_stock.create(this, dom.byId('draft_area_card'), this.card_width, this.card_height);
        draft_card_stock.setSelectionMode(0);

        // Create cards types:
        for (var i = 0; i < this.card_type_number; i++)
        {
            draft_card_stock.addItemType(i, i, g_gamethemeurl + 'img/cards.png', i);
        }

        for (var i in gamedatas.draft_cards)
        {
            var card = gamedatas.draft_cards[i];
            var card_type_id = this.getCardTypeId(card.type, card.type_arg);
            draft_card_stock.addToStockWithId(card_type_id, card.id);
        }

        this.draft_card_stock = draft_card_stock;

        // Setting up player area
        for (var i in this.gamedatas.playerorder) {
            var player_id = gamedatas.playerorder[i];

            domConstruct.place('player_area_' + player_id, 'player_areas');
        }

        for (var player_id in gamedatas.players)
        {
            var player_card_stock = new ebg.stock();
            player_card_stock.create(this, dom.byId('player_area_card_' + player_id), this.card_width, this.card_height);
            player_card_stock.setSelectionMode(0);

            // Create cards types:
            for (var i = 0; i < this.card_type_number; i++)
            {
                player_card_stock.addItemType(i, i, g_gamethemeurl + 'img/cards.png', i);
            }

            // Cards in player's hand
            for (var i in gamedatas.hands[player_id])
            {
                var card = gamedatas.hands[player_id][i];
                var card_type_id = this.getCardTypeId(card.type, card.type_arg);
                player_card_stock.addToStockWithId(card_type_id, card.id);
            }

            this.player_card_stocks[player_id] = player_card_stock;
        }

        // Setting up turn order track
        for (var player_id in gamedatas.players)
        {
            var in_harvest = false;

            for (var i in gamedatas.harvests)
            {
                var harvest = gamedatas.harvests[i];

                if (harvest.player_id == player_id)
                {
                    in_harvest = true;
                    break;
                }
            }

            if (!in_harvest) {
                domConstruct.place(this.format_block('jstpl_player_turn_order', {
                    color: gamedatas.players[ player_id ].color
                }), 'turn_order_location_' + (gamedatas.players[ player_id ].turn_order - 1));
            }
        }

        // Setting up harvest area
        for (var i in gamedatas.harvests)
        {
            var harvest = gamedatas.harvests[i];

            domConstruct.place(this.format_block('jstpl_player_turn_order', {
                color: gamedatas.players[ harvest.player_id ].color
            }), 'harvest_location_' + harvest.location);
        }

        // Setting up sowing area
        for (var i = 0; i < 10; i++)
        {
            var sowing_card_stock = new ebg.stock();
            sowing_card_stock.create(this, dom.byId('sowing_location_' + i), this.card_width, this.card_height);
            sowing_card_stock.setOverlap(0.1, 0.1);
            sowing_card_stock.setSelectionMode(0);
            sowing_card_stock.item_margin = 0;
            sowing_card_stock.order_items = false;

            // Create cards types:
            for (var j = 0; j < this.card_type_number; j++)
            {
                sowing_card_stock.addItemType(j, j, g_gamethemeurl + 'img/cards.png', j);
            }

            this.sowing_card_stocks[i] = sowing_card_stock;
        }

        for (var i in gamedatas.sowings)
        {
            var card = gamedatas.sowings[i];
            var card_type_id = this.getCardTypeId(card.type, card.type_arg);
            this.sowing_card_stocks[card.location_arg].addToStockWithId(card_type_id, card.id);
        }

        // Setting up higher dignitary area
        for (var i in gamedatas.higher_dignitary)
        {
            var higher_dignitary = gamedatas.higher_dignitary[i];

            this.addDignitaryOnBoard(higher_dignitary.location, 'higher', higher_dignitary.player_id);
        }

        // Setting up middle dignitary area
        for (var i in gamedatas.middle_dignitary)
        {
            var middle_dignitary = gamedatas.middle_dignitary[i];

            this.addDignitaryOnBoard(middle_dignitary.location, 'middle', middle_dignitary.player_id);
        }

        // Setting up lower dignitary area
        for (var i in gamedatas.lower_dignitary)
        {
            var lower_dignitary = gamedatas.lower_dignitary[i];

            this.addDignitaryOnBoard(lower_dignitary.location, 'lower', lower_dignitary.player_id);
        }

        // Setting up plow area
        var plow_card_stock = new ebg.stock();
        plow_card_stock.create(this, dom.byId('plow_location'), this.card_width, this.card_height);
        plow_card_stock.setOverlap(0.1, 0.1);
        plow_card_stock.setSelectionMode(0);
        plow_card_stock.item_margin = 0;
        plow_card_stock.order_items = false;

        // Create cards types:
        for (var i = 0; i < this.card_type_number; i++)
        {
            plow_card_stock.addItemType(i, i, g_gamethemeurl + 'img/cards.png', i);
        }

        for (var i in gamedatas.plows)
        {
            var card = gamedatas.plows[i];
            var card_type_id = this.getCardTypeId(card.type, card.type_arg);
            plow_card_stock.addToStockWithId(card_type_id, card.id);
        }

        this.plow_card_stock = plow_card_stock;

        // Setting up expansion area
        for (var i = 0; i < 4; i++)
        {
            var expansion_card_stock = new ebg.stock();
            expansion_card_stock.create(this, dom.byId('expansion_location_' + i), this.card_width, this.card_height);
            expansion_card_stock.setSelectionMode(0);
            expansion_card_stock.item_margin = 0;

            // Create cards types:
            for (var j = 0; j < this.expansion_type_number; j++)
            {
                expansion_card_stock.addItemType(j, j, g_gamethemeurl + 'img/expansions.png', j);
            }

            this.expansion_card_stocks[i] = expansion_card_stock;
        }

        for (var i in gamedatas.expansions)
        {
            var card = gamedatas.expansions[i];
            var card_type_id = this.getExpansionTypeId(card.type, card.type_arg);
            this.expansion_card_stocks[card.location_arg].addToStockWithId(card_type_id, card.id);
        }

        // Setting up camel track
        for (var i = 0; i < 11; i++)
        {
            var camel_zone = new ebg.zone();

            camel_zone.create( this, 'camel_location_' + i, 18, 10 );
            camel_zone.setPattern( 'custom' );
            camel_zone.autowidth = false;
            camel_zone.autoheight = false;

            camel_zone.itemIdToCoords = function( i, container_width, item_height, item_number ) {
                var res = {};

                res.x = 0;
                res.y = 4 - (i * 4) ;
                res.w = this.item_width;
                res.h = this.item_height;

                return res;
            };

            this.camel_zones[i] = camel_zone;
        }

        for (var player_id in gamedatas.players)
        {
            var player = gamedatas.players[player_id];

            domConstruct.place(this.format_block('jstpl_player_camel', {
                color: player.color
            }), 'camel_location_' + player.camel);

            this.camel_zones[player.camel].placeInZone( 'player_camel_' + player.color, 0 );
        }

        // Setting up offering track
        for (var i = 0; i < 8; i++)
        {
            var offering_zone = new ebg.zone();

            offering_zone.create( this, 'offering_location_' + i, 18, 10 );
            offering_zone.setPattern( 'custom' );
            offering_zone.autowidth = false;
            offering_zone.autoheight = false;

            offering_zone.itemIdToCoords = function( i, container_width, item_height, item_number ) {
                var res = {};

                res.x = 0;
                res.y = 4 - (i * 4) ;
                res.w = this.item_width;
                res.h = this.item_height;

                return res;
            };

            this.offering_zones[i] = offering_zone;
        }

        for (var player_id in gamedatas.players)
        {
            var player = gamedatas.players[player_id];

            domConstruct.place(this.format_block('jstpl_player_offering', {
                color: player.color
            }), 'offering_location_' + player.offering);

            this.offering_zones[player.offering].placeInZone( 'player_offering_' + player.color, 0 );
        }

        // Setup game notifications to handle (see "setupNotifications" method below)
        this.setupNotifications();
    },
    ///////////////////////////////////////////////////
    //// Game & client states
    onEnteringState: function(stateName, args)
    {
        switch (stateName)
        {
            case 'chooseStartingSpace':
                this.updateStartingSpaces(args.args.startingSpaces);
                break;

            case 'chooseFoodCard':
                this.updateFoodCards(args.args.foodCards);
                break;

            case 'chooseFoodColumn':
                this.updateFoodColumns(args.args.foodColumns);
                break;

            case 'placeHut':
                this.updateHutSpaces(args.args.hutSpaces);
                break;

            case 'useFoodCard':
                this.updatePlayerFoodCards(args.args.foodCards);
                break;

            case 'resupplyHut':
                this.updateResupplyHuts(args.args.huts);
                break;

            case 'placeWell':
                this.updateWellSpaces(args.args.wellSpaces);
                break;

            case 'performAction':
                this.updateZigguratSpaces(args.args.zigguratSpaces);
                this.updateZigguratBases(args.args.zigguratBases);
                this.updateZigguratCenters(args.args.zigguratCenters);
                this.updateHigherDignitary(args.args.higherDignitary);
                this.updateMiddleDignitary(args.args.middleDignitary);
                this.updateLowerDignitary(args.args.lowerDignitary);
                this.updateOfferings(args.args.offerings);
                this.updateAvailableFoodCards(args.args.foodCards);
                this.updatePlowCard(args.args.plowCard);
                break;
        }
    },
    onLeavingState: function(stateName)
    {
        switch (stateName)
        {
        }
    },
    onUpdateActionButtons: function(stateName, args)
    {
        if (this.isCurrentPlayerActive())
        {
            switch (stateName)
            {
                case 'placeWell':
                    this.addActionButton('pass', _('or pass'), 'onPassPlaceWell');
                    break;

                case 'performAction':
                    this.addActionButton('pass', _('or pass'), 'onPassPerformAction');
                    break;
            }
        }
    },
    ///////////////////////////////////////////////////
    //// Utility methods

    // Get card type based on its type and type_arg
    getCardTypeId: function(type, type_arg)
    {
        var card_type_id;

        switch (type) {
            case 'grape':
                card_type_id = parseInt(type_arg) - 1;
                break;
            case 'palm':
                card_type_id = 3 + parseInt(type_arg) - 1;
                break;
            case 'salt':
                card_type_id = 6 + parseInt(type_arg) - 1;
                break;
            case 'barley':
                card_type_id = 9 + parseInt(type_arg) - 1;
                break;
            case 'date':
                card_type_id = 12 + parseInt(type_arg) - 1;
                break;
            case 'wild':
                card_type_id = 15;
                break;
            case 'plow':
                card_type_id = 16;
                break;
        }

        return card_type_id;
    },
    // Get expansion type based on its type and type_arg
    getExpansionTypeId: function(type, type_arg)
    {
        var expansion_type_id;

        switch (type) {
            case 'expansion':
                expansion_type_id = parseInt(type_arg) - 2;
                break;
            case 'bonus':
                expansion_type_id = 3;
                break;
        }

        return expansion_type_id;
    },
    getCamelZone: function(node)
    {
        var match = node.parentNode.id.match(/camel_location_(\d+)/);
        var i = match[1];

        return this.camel_zones[i];
    },
    getIcon: function(type)
    {
        return "<div class='log_icon icons icon icon_" + type + "'></div>";
    },
    getOfferingZone: function(node)
    {
        var match = node.parentNode.id.match(/offering_location_(\d+)/);
        var i = match[1];

        return this.offering_zones[i];
    },
    addDignitaryOnBoard: function(location, type, type_arg, animate)
    {
        animate = typeof animate !== 'undefined' ? animate : false;

        var object_node_id = type + '_dignitary_' + location;
        var dignitary_location_node_id = type + '_dignitary_location_' + location;

        domConstruct.place(this.format_block('jstpl_' + type + '_dignitary', {
            location: location,
            color: this.gamedatas.players[ type_arg ].color
        }), dignitary_location_node_id);

        if (animate) {
            var left = domStyle.get(object_node_id, 'left');
            var top = domStyle.get(object_node_id, 'top');

            // Place it on the player panel
            this.placeOnObject(object_node_id, 'hut_icon_' + type_arg);

            // Animate a slide from the player panel to the location
            domStyle.set(object_node_id, 'zIndex', 99);
            var slide = this.slideToObjectPos(object_node_id, dignitary_location_node_id, left, top, this.ANIMATION_DURATION, 0);
            connect.connect(slide, 'onEnd', lang.hitch(this, function(object_node_id) {
                domStyle.set(object_node_id, 'zIndex', 'auto');
            }, object_node_id));
            slide.play();
        }
    },
    addObjectOnBoard: function(q, r, type, type_arg, animate)
    {
        animate = typeof animate !== 'undefined' ? animate : false;

        var object_node_id = type + '_' + q + '_' + r;
        var hexagon_location_node_id = 'hexagon_location_' + q + '_' + r;

        if (type == 'forbidden') {
            domConstruct.place(this.format_block('jstpl_' + type, {
                q: q,
                r: r,
            }), hexagon_location_node_id);
        } else {
            domConstruct.place(this.format_block('jstpl_' + type, {
                q: q,
                r: r,
                color: this.gamedatas.players[ type_arg ].color
            }), hexagon_location_node_id);

            if (type == 'hut_resupplied') {
                var handle_mouse_over = connect.connect(dom.byId(hexagon_location_node_id), 'onmouseover', lang.hitch(this, 'onHutResuppliedOver', q, r));

                this.hexagon_connections.push({
                    q: q,
                    r: r,
                    handle: handle_mouse_over
                });

                var handle_mouse_out = connect.connect(dom.byId(hexagon_location_node_id), 'onmouseout', lang.hitch(this, 'onHutResuppliedOut', q, r));

                this.hexagon_connections.push({
                    q: q,
                    r: r,
                    handle: handle_mouse_out
                });
            }
        }

        if (animate) {
            var left = domStyle.get(object_node_id, 'left');
            var top = domStyle.get(object_node_id, 'top');

            // Place it on the player panel
            this.placeOnObject(object_node_id, type + '_icon_' + type_arg);

            // Animate a slide from the player panel to the location
            domStyle.set(object_node_id, 'zIndex', 99);
            var slide = this.slideToObjectPos(object_node_id, hexagon_location_node_id, left, top, this.ANIMATION_DURATION, 0);
            connect.connect(slide, 'onEnd', lang.hitch(this, function(object_node_id) {
                domStyle.set(object_node_id, 'zIndex', 'auto');
            }, object_node_id));
            slide.play();
        }
    },
    addWellOnBoard: function(q, r, t, animate)
    {
        animate = typeof animate !== 'undefined' ? animate : false;

        var well_node_id = 'well_' + q + '_' + r + '_' + t;
        var well_location_node_id = 'well_location_' + q + '_' + r + '_' + t;

        domConstruct.place(this.format_block('jstpl_well', {
            q: q,
            r: r,
            t: t
        }), well_location_node_id);

        if (animate) {
            var left = domStyle.get(well_node_id, 'left');
            var top = domStyle.get(well_node_id, 'top');

            // Place it on the stock panel
            this.placeOnObject(well_node_id, 'well_icon');

            // Animate a slide from the stock panel to the location
            domStyle.set(well_node_id, 'zIndex', 99);
            var slide = this.slideToObjectPos(well_node_id, well_location_node_id, left, top, this.ANIMATION_DURATION, 0);
            connect.connect(slide, 'onEnd', lang.hitch(this, function(well_node_id) {
                domStyle.set(well_node_id, 'zIndex', 'auto');
            }, well_node_id));
            slide.play();
        }
    },
    removeHexagonConnections: function(q, r)
    {
        for (var i = this.hexagon_connections.length - 1; i >= 0; i--) {
            var hexagon_connection = this.hexagon_connections[i];

            if ((hexagon_connection.q == q) && (hexagon_connection.r == r)) {
                connect.disconnect(hexagon_connection.handle);

                this.hexagon_connections.splice(i, 1);
            }
        }
    },
    removeTemporaryConnections: function()
    {
        while (this.temporary_connections.length > 0) {
            var handle = this.temporary_connections.pop();

            connect.disconnect(handle);
        }
    },
    removeTemporaryTooltips: function()
    {
        while (this.temporary_tooltips.length > 0) {
            var id = this.temporary_tooltips.pop();

            domStyle.set(id, 'cursor', 'default');
            this.removeTooltip(id);
        }
    },
    updateFoodCards: function(foodCards)
    {
        for (var i in foodCards)
        {
            var card = foodCards[i];

            if (this.isCurrentPlayerActive()) {
                var card_node_id = 'draft_area_card_item_' + card.id;

                domClass.remove(card_node_id, 'stockitem_unselectable');
                domStyle.set(card_node_id, 'cursor', 'pointer');
                this.addTooltip(card_node_id, '', _('Choose this card'));
                this.temporary_tooltips.push(card_node_id);

                var handle = connect.connect(dom.byId(card_node_id), 'onclick', lang.hitch(this, 'onChooseFoodCard', card.id));
                this.temporary_connections.push(handle);
            }
        }
    },
    updateFoodColumns: function(foodColumns)
    {
        for (var i in foodColumns)
        {
            var foodColumn = foodColumns[i];

            if (this.isCurrentPlayerActive()) {
                var harvest_location_node_id = 'harvest_location_' + foodColumn;

                domStyle.set(harvest_location_node_id, 'cursor', 'pointer');
                this.addTooltip(harvest_location_node_id, '', _('Choose this column'));
                this.temporary_tooltips.push(harvest_location_node_id);

                var handle = connect.connect(dom.byId(harvest_location_node_id), 'onclick', lang.hitch(this, 'onChooseFoodColumn', foodColumn));
                this.temporary_connections.push(handle);

                for (var j = 0; j < 2; j++) {
                    query('[id^="sowing_location_' + ((j * 5) + foodColumn) + '_item_"]').forEach(function(node) {
                        var card_node_id = domAttr.get(node, 'id');

                        domClass.remove(card_node_id, 'stockitem_unselectable');
                        domStyle.set(card_node_id, 'cursor', 'pointer');
                        this.addTooltip(card_node_id, '', _('Choose this column'));
                        this.temporary_tooltips.push(card_node_id);

                        var handle = connect.connect(dom.byId(card_node_id), 'onclick', lang.hitch(this, 'onChooseFoodColumn', foodColumn));
                        this.temporary_connections.push(handle);
                    }, this);
                }
            }
        }
    },
    updateStartingSpaces: function(startingSpaces)
    {
        for (var i in startingSpaces)
        {
            var startingSpace = startingSpaces[i];
            var hexagon_location_node_id = 'hexagon_location_' + startingSpace.q + '_' + startingSpace.r;

            domConstruct.place(this.format_block('jstpl_starting_space', {
                q: startingSpace.q,
                r: startingSpace.r,
                color: this.gamedatas.players[ this.gamedatas.gamestate.active_player ].color
            }), hexagon_location_node_id);

            if (this.isCurrentPlayerActive()) {
                domStyle.set(hexagon_location_node_id, 'cursor', 'pointer');
                this.addTooltip(hexagon_location_node_id, '', _('Choose this starting space'));
                this.temporary_tooltips.push(hexagon_location_node_id);

                var handle = connect.connect(dom.byId(hexagon_location_node_id), 'onclick', lang.hitch(this, 'onChooseStartingSpace', startingSpace.q, startingSpace.r));
                this.temporary_connections.push(handle);
            }
        }
    },
    updateHutSpaces: function(hutSpaces)
    {
        for (var i in hutSpaces)
        {
            var hutSpace = hutSpaces[i];
            var hexagon_location_node_id = 'hexagon_location_' + hutSpace.q + '_' + hutSpace.r;

            domConstruct.place(this.format_block('jstpl_hut_space', {
                q: hutSpace.q,
                r: hutSpace.r,
                color: this.gamedatas.players[ this.gamedatas.gamestate.active_player ].color
            }), hexagon_location_node_id);

            if (this.isCurrentPlayerActive()) {
                domStyle.set(hexagon_location_node_id, 'cursor', 'pointer');
                this.addTooltip(hexagon_location_node_id, '', _('Place a hut here'));
                this.temporary_tooltips.push(hexagon_location_node_id);

                var handle = connect.connect(dom.byId(hexagon_location_node_id), 'onclick', lang.hitch(this, 'onPlaceHut', hutSpace.q, hutSpace.r));
                this.temporary_connections.push(handle);
            }
        }
    },
    updatePlayerFoodCards: function(foodCards)
    {
        for (var i in foodCards)
        {
            var card = foodCards[i];

            if (this.isCurrentPlayerActive()) {
                var card_node_id = 'player_area_card_' + this.player_id + '_item_' + card.id;

                domClass.remove(card_node_id, 'stockitem_unselectable');
                domStyle.set(card_node_id, 'cursor', 'pointer');
                this.addTooltip(card_node_id, '', _('Use this card'));
                this.temporary_tooltips.push(card_node_id);

                var handle = connect.connect(dom.byId(card_node_id), 'onclick', lang.hitch(this, 'onUseFoodCard', card.id));
                this.temporary_connections.push(handle);
            }
        }
    },
    updateResupplyHuts: function(huts)
    {
        for (var i in huts)
        {
            var hut = huts[i];
            var hexagon_location_node_id = 'hexagon_location_' + hut.q + '_' + hut.r;

            if (this.isCurrentPlayerActive()) {
                domStyle.set(hexagon_location_node_id, 'cursor', 'pointer');
                this.addTooltip(hexagon_location_node_id, '', _('Resupply this hut'));
                this.temporary_tooltips.push(hexagon_location_node_id);

                var handle = connect.connect(dom.byId(hexagon_location_node_id), 'onclick', lang.hitch(this, 'onResupplyHut', hut.q, hut.r));
                this.temporary_connections.push(handle);
            }
        }
    },
    updateWellSpaces: function(wellSpaces)
    {
        for (var i in wellSpaces)
        {
            var wellSpace = wellSpaces[i];
            var well_location_node_id = 'well_location_' + wellSpace.q + '_' + wellSpace.r + '_' + wellSpace.t;

            domConstruct.place(this.format_block('jstpl_well_space', {
                q: wellSpace.q,
                r: wellSpace.r,
                t: wellSpace.t
            }), well_location_node_id);

            if (this.isCurrentPlayerActive()) {
                domStyle.set(well_location_node_id, 'cursor', 'pointer');
                this.addTooltip(well_location_node_id, '', _('Place a well here'));
                this.temporary_tooltips.push(well_location_node_id);

                var handle = connect.connect(dom.byId(well_location_node_id), 'onclick', lang.hitch(this, 'onPlaceWell', wellSpace.q, wellSpace.r, wellSpace.t));
                this.temporary_connections.push(handle);
            }
        }
    },
    updateZigguratSpaces: function(zigguratSpaces)
    {
        for (var i in zigguratSpaces)
        {
            var zigguratSpace = zigguratSpaces[i];
            var hexagon_location_node_id = 'hexagon_location_' + zigguratSpace.q + '_' + zigguratSpace.r;

            if (this.isCurrentPlayerActive()) {
                domStyle.set(hexagon_location_node_id, 'cursor', 'pointer');
                this.addTooltip(hexagon_location_node_id, '', string.substitute(_('Build a ziggurat here for ${camel_number} ${camel_icon} camels'), { camel_number: this.ZIGGURAT_BASE_COST, camel_icon: this.getIcon('camel') }));
                this.temporary_tooltips.push(hexagon_location_node_id);

                var handle = connect.connect(dom.byId(hexagon_location_node_id), 'onclick', lang.hitch(this, 'onBuildZiggurat', zigguratSpace.q, zigguratSpace.r));
                this.temporary_connections.push(handle);
            }
        }
    },
    updateZigguratBases: function(zigguratBases)
    {
        for (var i in zigguratBases)
        {
            var zigguratBase = zigguratBases[i];
            var hexagon_location_node_id = 'hexagon_location_' + zigguratBase.q + '_' + zigguratBase.r;

            if (this.isCurrentPlayerActive()) {
                domStyle.set(hexagon_location_node_id, 'cursor', 'pointer');
                this.addTooltip(hexagon_location_node_id, '', string.substitute(_('Extend this ziggurat for ${camel_number} ${camel_icon} camels'), { camel_number: this.ZIGGURAT_CENTER_COST, camel_icon: this.getIcon('camel') }));
                this.temporary_tooltips.push(hexagon_location_node_id);

                var handle = connect.connect(dom.byId(hexagon_location_node_id), 'onclick', lang.hitch(this, 'onExtendZigguratCenter', zigguratBase.q, zigguratBase.r));
                this.temporary_connections.push(handle);
            }
        }
    },
    updateZigguratCenters: function(zigguratCenters)
    {
        for (var i in zigguratCenters)
        {
            var zigguratCenter = zigguratCenters[i];
            var hexagon_location_node_id = 'hexagon_location_' + zigguratCenter.q + '_' + zigguratCenter.r;

            if (this.isCurrentPlayerActive()) {
                domStyle.set(hexagon_location_node_id, 'cursor', 'pointer');
                this.addTooltip(hexagon_location_node_id, '', string.substitute(_('Extend this ziggurat for ${camel_number} ${camel_icon} camels'), { camel_number: this.ZIGGURAT_ROOF_COST, camel_icon: this.getIcon('camel') }));
                this.temporary_tooltips.push(hexagon_location_node_id);

                var handle = connect.connect(dom.byId(hexagon_location_node_id), 'onclick', lang.hitch(this, 'onExtendZigguratRoof', zigguratCenter.q, zigguratCenter.r));
                this.temporary_connections.push(handle);
            }
        }
    },
    updateHigherDignitary: function(higherDignitary)
    {
        if (higherDignitary != null) {
            if (this.isCurrentPlayerActive()) {
                query('[id^="higher_dignitary_location_"]').forEach(function(node) {
                    var higher_dignitary_location_node_id = domAttr.get(node, 'id');

                    domStyle.set(higher_dignitary_location_node_id, 'cursor', 'pointer');
                    this.addTooltip(higher_dignitary_location_node_id, '', string.substitute(_('Influence the higher dignitary for ${camel_number} ${camel_icon} camels'), { camel_number: this.HIGHER_DIGNITARY_COST, camel_icon: this.getIcon('camel') }));
                    this.temporary_tooltips.push(higher_dignitary_location_node_id);

                    var handle = connect.connect(dom.byId(higher_dignitary_location_node_id), 'onclick', lang.hitch(this, 'onInfluenceHigherDignitary'));
                    this.temporary_connections.push(handle);
                }, this);
            }
        }
    },
    updateMiddleDignitary: function(middleDignitary)
    {
        if (middleDignitary != null) {
            if (this.isCurrentPlayerActive()) {
                query('[id^="middle_dignitary_location_"]').forEach(function(node) {
                    var middle_dignitary_location_node_id = domAttr.get(node, 'id');

                    domStyle.set(middle_dignitary_location_node_id, 'cursor', 'pointer');
                    this.addTooltip(middle_dignitary_location_node_id, '', string.substitute(_('Influence the middle dignitary for ${camel_number} ${camel_icon} camels'), { camel_number: this.MIDDLE_DIGNITARY_COST, camel_icon: this.getIcon('camel') }));
                    this.temporary_tooltips.push(middle_dignitary_location_node_id);

                    var handle = connect.connect(dom.byId(middle_dignitary_location_node_id), 'onclick', lang.hitch(this, 'onInfluenceMiddleDignitary'));
                    this.temporary_connections.push(handle);
                }, this);
            }
        }
    },
    updateLowerDignitary: function(lowerDignitary)
    {
        if (lowerDignitary != null) {
            if (this.isCurrentPlayerActive()) {
                query('[id^="lower_dignitary_location_"]').forEach(function(node) {
                    var lower_dignitary_location_node_id = domAttr.get(node, 'id');

                    domStyle.set(lower_dignitary_location_node_id, 'cursor', 'pointer');
                    this.addTooltip(lower_dignitary_location_node_id, '', string.substitute(_('Influence the lower dignitary for ${camel_number} ${camel_icon} camels'), { camel_number: this.LOWER_DIGNITARY_COST, camel_icon: this.getIcon('camel') }));
                    this.temporary_tooltips.push(lower_dignitary_location_node_id);

                    var handle = connect.connect(dom.byId(lower_dignitary_location_node_id), 'onclick', lang.hitch(this, 'onInfluenceLowerDignitary'));
                    this.temporary_connections.push(handle);
                }, this);
            }
        }
    },
    updateOfferings: function(offerings)
    {
        var camel = 1;

        for (var i in offerings)
        {
            var offering = offerings[i];
            var offering_location_node_id = 'offering_location_' + offering;

            if (this.isCurrentPlayerActive()) {
                domStyle.set(offering_location_node_id, 'cursor', 'pointer');
                this.addTooltip(offering_location_node_id, '', string.substitute(_('Make an offering of ${camel_number} ${camel_icon} camel(s)'), { camel_number: camel, camel_icon: this.getIcon('camel') }));
                this.temporary_tooltips.push(offering_location_node_id);

                var handle = connect.connect(dom.byId(offering_location_node_id), 'onclick', lang.hitch(this, 'onMakeOffering', offering));
                this.temporary_connections.push(handle);
            }

            camel++;
        }
    },
    updateAvailableFoodCards: function(foodCards)
    {
        for (var i in foodCards)
        {
            var card = foodCards[i];

            if (this.isCurrentPlayerActive()) {
                var card_node_id = 'sowing_location_' + card.location_arg + '_item_' + card.id;

                domClass.remove(card_node_id, 'stockitem_unselectable');
                domStyle.set(card_node_id, 'cursor', 'pointer');
                this.addTooltip(card_node_id, '', string.substitute(_('Buy this card for ${camel_number} ${camel_icon} camel(s)'), { camel_number: card.camels, camel_icon: this.getIcon('camel') }));
                this.temporary_tooltips.push(card_node_id);

                var handle = connect.connect(dom.byId(card_node_id), 'onclick', lang.hitch(this, 'onBuyFoodCard', card.id));
                this.temporary_connections.push(handle);
            }
        }
    },

    updatePlowCard: function(plowCard)
    {
        if (plowCard != null) {
            var card_node_id = 'plow_location_item_' + plowCard.id;

            if (this.isCurrentPlayerActive()) {
                domClass.remove(card_node_id, 'stockitem_unselectable');
                domStyle.set(card_node_id, 'cursor', 'pointer');
                this.addTooltip(card_node_id, '', string.substitute(_('Buy this card for ${camel_number} ${camel_icon} camel(s)'), { camel_number: this.PLOW_COST, camel_icon: this.getIcon('camel') }));
                this.temporary_tooltips.push(card_node_id);

                var handle = connect.connect(dom.byId(card_node_id), 'onclick', lang.hitch(this, 'onBuyPlowCard', plowCard.id));
                this.temporary_connections.push(handle);
            }
        }
    },
    ///////////////////////////////////////////////////
    //// Player's action
    onChooseStartingSpace: function(q, r, evt)
    {
        evt.preventDefault();
        evt.stopPropagation();

        if (this.checkAction('chooseStartingSpace'))
        {
            this.ajaxcall("/assyria/assyria/chooseStartingSpace.html", {
                lock: true,
                q: q,
                r: r
            }, this, function(result) {
                this.removeTemporaryConnections();
                this.removeTemporaryTooltips();
            });
        }
    },
    onChooseFoodCard: function(id, evt)
    {
        evt.preventDefault();
        evt.stopPropagation();

        if (this.checkAction('chooseFoodCard'))
        {
            this.ajaxcall("/assyria/assyria/chooseFoodCard.html", {
                lock: true,
                id: id
            }, this, function(result) {
                this.removeTemporaryConnections();
                this.removeTemporaryTooltips();
            });
        }
    },
    onChooseFoodColumn: function(column, evt)
    {
        evt.preventDefault();
        evt.stopPropagation();

        if (this.checkAction('chooseFoodColumn'))
        {
            this.ajaxcall("/assyria/assyria/chooseFoodColumn.html", {
                lock: true,
                column: column
            }, this, function(result) {
                this.removeTemporaryConnections();
                this.removeTemporaryTooltips();
            });
        }
    },
    onPlaceHut: function(q, r, evt)
    {
        evt.preventDefault();
        evt.stopPropagation();

        if (this.checkAction('placeHut'))
        {
            this.ajaxcall("/assyria/assyria/placeHut.html", {
                lock: true,
                q: q,
                r: r
            }, this, function(result) {
                this.removeTemporaryConnections();
                this.removeTemporaryTooltips();
            });
        }
    },
    onUseFoodCard: function(id, evt)
    {
        evt.preventDefault();
        evt.stopPropagation();

        if (this.checkAction('useFoodCard'))
        {
            this.ajaxcall("/assyria/assyria/useFoodCard.html", {
                lock: true,
                id: id
            }, this, function(result) {
                this.removeTemporaryConnections();
                this.removeTemporaryTooltips();
            });
        }
    },
    onResupplyHut: function(q, r, evt)
    {
        evt.preventDefault();
        evt.stopPropagation();

        if (this.checkAction('resupplyHut'))
        {
            this.ajaxcall("/assyria/assyria/resupplyHut.html", {
                lock: true,
                q: q,
                r: r
            }, this, function(result) {
                this.removeTemporaryConnections();
                this.removeTemporaryTooltips();
            });
        }
    },
    onPlaceWell: function(q, r, t, evt)
    {
        evt.preventDefault();
        evt.stopPropagation();

        if (this.checkAction('placeWell'))
        {
            this.ajaxcall("/assyria/assyria/placeWell.html", {
                lock: true,
                q: q,
                r: r,
                t: t
            }, this, function(result) {
                this.removeTemporaryConnections();
                this.removeTemporaryTooltips();
            });
        }
    },
    onPassPlaceWell: function(evt)
    {
        evt.preventDefault();
        evt.stopPropagation();

        if (this.checkAction('passPlaceWell'))
        {
            this.ajaxcall("/assyria/assyria/passPlaceWell.html", {
                lock: true
            }, this, function(result) {
                this.removeTemporaryConnections();
                this.removeTemporaryTooltips();
            });
        }
    },
    onBuildZiggurat: function(q, r, evt)
    {
        evt.preventDefault();
        evt.stopPropagation();

        if (this.checkAction('buildZiggurat'))
        {
            this.ajaxcall("/assyria/assyria/buildZiggurat.html", {
                lock: true,
                q: q,
                r: r
            }, this, function(result) {
                this.removeTemporaryConnections();
                this.removeTemporaryTooltips();
            });
        }
    },
    onExtendZigguratCenter: function(q, r, evt)
    {
        evt.preventDefault();
        evt.stopPropagation();

        if (this.checkAction('extendZigguratCenter'))
        {
            this.ajaxcall("/assyria/assyria/extendZigguratCenter.html", {
                lock: true,
                q: q,
                r: r
            }, this, function(result) {
                this.removeTemporaryConnections();
                this.removeTemporaryTooltips();
            });
        }
    },
    onExtendZigguratRoof: function(q, r, evt)
    {
        evt.preventDefault();
        evt.stopPropagation();

        if (this.checkAction('extendZigguratRoof'))
        {
            this.ajaxcall("/assyria/assyria/extendZigguratRoof.html", {
                lock: true,
                q: q,
                r: r
            }, this, function(result) {
                this.removeTemporaryConnections();
                this.removeTemporaryTooltips();
            });
        }
    },
    onHutResuppliedOut: function(q, r, evt)
    {
        evt.preventDefault();
        evt.stopPropagation();

        var hut_node_id = 'hut_' + q + '_' + r;
        var hexagon_location_node_id = 'hexagon_location_' + q + '_' + r;

        var slide = this.slideToObjectPos(hut_node_id, hexagon_location_node_id, 6, 14, this.ANIMATION_DURATION / 4, 0);
        slide.play();
    },
    onHutResuppliedOver: function(q, r, evt)
    {
        evt.preventDefault();
        evt.stopPropagation();

        var hut_node_id = 'hut_' + q + '_' + r;
        var hexagon_location_node_id = 'hexagon_location_' + q + '_' + r;

        var slide = this.slideToObjectPos(hut_node_id, hexagon_location_node_id, 6, 29, this.ANIMATION_DURATION / 4, 0);
        slide.play();
    },
    onInfluenceHigherDignitary: function(evt)
    {
        evt.preventDefault();
        evt.stopPropagation();

        if (this.checkAction('influenceHigherDignitary'))
        {
            this.ajaxcall("/assyria/assyria/influenceHigherDignitary.html", {
                lock: true
            }, this, function(result) {
                this.removeTemporaryConnections();
                this.removeTemporaryTooltips();
            });
        }
    },
    onInfluenceMiddleDignitary: function(evt)
    {
        evt.preventDefault();
        evt.stopPropagation();

        if (this.checkAction('influenceMiddleDignitary'))
        {
            this.ajaxcall("/assyria/assyria/influenceMiddleDignitary.html", {
                lock: true
            }, this, function(result) {
                this.removeTemporaryConnections();
                this.removeTemporaryTooltips();
            });
        }
    },
    onInfluenceLowerDignitary: function(evt)
    {
        evt.preventDefault();
        evt.stopPropagation();

        if (this.checkAction('influenceLowerDignitary'))
        {
            this.ajaxcall("/assyria/assyria/influenceLowerDignitary.html", {
                lock: true
            }, this, function(result) {
                this.removeTemporaryConnections();
                this.removeTemporaryTooltips();
            });
        }
    },
    onMakeOffering: function(offering, evt)
    {
        evt.preventDefault();
        evt.stopPropagation();

        if (this.checkAction('makeOffering'))
        {
            this.ajaxcall("/assyria/assyria/makeOffering.html", {
                lock: true,
                offering: offering
            }, this, function(result) {
                this.removeTemporaryConnections();
                this.removeTemporaryTooltips();
            });
        }
    },
    onBuyFoodCard: function(id, evt)
    {
        evt.preventDefault();
        evt.stopPropagation();

        if (this.checkAction('buyFoodCard'))
        {
            this.ajaxcall("/assyria/assyria/buyFoodCard.html", {
                lock: true,
                id: id
            }, this, function(result) {
                this.removeTemporaryConnections();
                this.removeTemporaryTooltips();
            });
        }
    },
    onBuyPlowCard: function(id, evt)
    {
        evt.preventDefault();
        evt.stopPropagation();

        if (this.checkAction('buyPlowCard'))
        {
            this.ajaxcall("/assyria/assyria/buyPlowCard.html", {
                lock: true,
                id: id
            }, this, function(result) {
                this.removeTemporaryConnections();
                this.removeTemporaryTooltips();
            });
        }
    },
    onPassPerformAction: function(evt)
    {
        evt.preventDefault();
        evt.stopPropagation();

        if (this.checkAction('passPerformAction'))
        {
            this.ajaxcall("/assyria/assyria/passPerformAction.html", {
                lock: true
            }, this, function(result) {
                this.removeTemporaryConnections();
                this.removeTemporaryTooltips();
            });
        }
    },
    setupNotifications: function()
    {
        connect.subscribe('waitAnimations', function() {});
        connect.subscribe('deckReshuffled', this, "notif_deckReshuffled");
        connect.subscribe('sowingPicked', this, "notif_sowingPicked");
        connect.subscribe('sowingSorted', this, "notif_sowingSorted");
        connect.subscribe('startingSpaceChosen', this, "notif_startingSpaceChosen");
        connect.subscribe('draftBegan', this, "notif_draftBegan");
        connect.subscribe('foodCardChosen', this, "notif_foodCardChosen");
        connect.subscribe('draftEnded', this, "notif_draftEnded");
        connect.subscribe('foodColumnChosen', this, "notif_foodColumnChosen");
        connect.subscribe('newTurnOrderDetermined', this, "notif_newTurnOrderDetermined");
        connect.subscribe('hutPlaced', this, "notif_hutPlaced");
        connect.subscribe('foodCardUsed', this, "notif_foodCardUsed");
        connect.subscribe('hutResupplied', this, "notif_hutResupplied");
        connect.subscribe('hutsNotResupplied', this, "notif_hutsNotResupplied");
        connect.subscribe('wellPlaced', this, "notif_wellPlaced");
        connect.subscribe('placeWellPassed', this, "notif_placeWellPassed");
        connect.subscribe('camelsEarned', this, "notif_camelsEarned");
        connect.subscribe('pointsScored', this, "notif_pointsScored");
        connect.subscribe('zigguratBuilt', this, "notif_zigguratBuilt");
        connect.subscribe('zigguratCenterExtended', this, "notif_zigguratCenterExtended");
        connect.subscribe('zigguratRoofExtended', this, "notif_zigguratRoofExtended");
        connect.subscribe('higherDignitaryInfluenced', this, "notif_higherDignitaryInfluenced");
        connect.subscribe('middleDignitaryInfluenced', this, "notif_middleDignitaryInfluenced");
        connect.subscribe('lowerDignitaryInfluenced', this, "notif_lowerDignitaryInfluenced");
        connect.subscribe('offeringMade', this, "notif_offeringMade");
        connect.subscribe('foodCardBought', this, "notif_foodCardBought");
        connect.subscribe('plowCardBought', this, "notif_plowCardBought");
        connect.subscribe('turnEnded', this, "notif_turnEnded");
        connect.subscribe('hutsFlooded', this, "notif_hutsFlooded");
        connect.subscribe('noDignitaryPointsScored', this, "notif_noDignitaryPointsScored");
        connect.subscribe('dignitaryPointsScored', this, "notif_dignitaryPointsScored");
        connect.subscribe('higherDignitaryBonusProvided', this, "notif_higherDignitaryBonusProvided");
        connect.subscribe('noMiddleDignitaryBonusProvided', this, "notif_noMiddleDignitaryBonusProvided");
        connect.subscribe('middleDignitaryBonusProvided', this, "notif_middleDignitaryBonusProvided");
        connect.subscribe('lowerDignitaryBonusProvided', this, "notif_lowerDignitaryBonusProvided");
        connect.subscribe('offeringPointsScored', this, "notif_offeringPointsScored");
        connect.subscribe('reignEnded', this, "notif_reignEnded");

        this.notifqueue.setSynchronous('waitAnimations', this.ANIMATION_DURATION + this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('deckReshuffled', this.ANIMATION_DURATION + this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('startingSpaceChosen', this.ANIMATION_DURATION + this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('draftBegan', 2 * this.ANIMATION_DURATION + 2 * this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('foodCardChosen', this.ANIMATION_DURATION + this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('draftEnded', this.ANIMATION_DURATION + this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('foodColumnChosen', 2 * this.ANIMATION_DURATION + 2 * this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('newTurnOrderDetermined', this.ANIMATION_DURATION + this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('hutPlaced', this.ANIMATION_DURATION + this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('foodCardUsed', this.ANIMATION_DURATION + this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('hutResupplied', this.ANIMATION_DURATION + this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('hutsNotResupplied', this.ANIMATION_DURATION + this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('wellPlaced', this.ANIMATION_DURATION + this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('placeWellPassed', this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('camelsEarned', this.ANIMATION_DURATION + this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('pointsScored', this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('zigguratBuilt', 3 * this.ANIMATION_DURATION + 3 * this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('zigguratCenterExtended', 2 * this.ANIMATION_DURATION + 2 * this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('zigguratRoofExtended', 2 * this.ANIMATION_DURATION + 2 * this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('higherDignitaryInfluenced', 2 * this.ANIMATION_DURATION + 2 * this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('middleDignitaryInfluenced', 2 * this.ANIMATION_DURATION + 2 * this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('lowerDignitaryInfluenced', 2 * this.ANIMATION_DURATION + 2 * this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('offeringMade', 2 * this.ANIMATION_DURATION + 2 * this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('foodCardBought', 2 * this.ANIMATION_DURATION + 2 * this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('plowCardBought', 2 * this.ANIMATION_DURATION + 2 * this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('turnEnded', 3 * this.ANIMATION_DURATION + 3 * this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('hutsFlooded', this.ANIMATION_DURATION + this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('noDignitaryPointsScored', this.ANIMATION_DURATION + this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('dignitaryPointsScored', this.ANIMATION_DURATION + this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('higherDignitaryBonusProvided', this.ANIMATION_DURATION + this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('noMiddleDignitaryBonusProvided', this.ANIMATION_DURATION + this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('middleDignitaryBonusProvided', 2 * this.ANIMATION_DURATION + 2 * this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('lowerDignitaryBonusProvided', 2 * this.ANIMATION_DURATION + 2 * this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('offeringPointsScored', this.ANIMATION_DURATION + this.ANIMATION_WAIT);
        this.notifqueue.setSynchronous('reignEnded', 3 * this.ANIMATION_DURATION + 3 * this.ANIMATION_WAIT);
    },
    notif_deckReshuffled: function(notif)
    {
        domStyle.set('card_deck', 'visibility', 'visible');
        this.card_deck_counter.setValue(this.discard_counter.getValue());

        this.discard_card_stock.removeAll();
        this.discard_counter.setValue(0);
    },
    notif_sowingPicked: function(notif)
    {
        var card_type_id = this.getCardTypeId(notif.args.sowing_card.type, notif.args.sowing_card.type_arg);
        this.sowing_card_stocks[notif.args.sowing_card.location_arg].addToStockWithId(card_type_id, notif.args.sowing_card.id, 'card_deck');
        this.card_deck_counter.incValue(-1);

        if (this.card_deck_counter.getValue() == 0) {
            domStyle.set('card_deck', 'visibility', 'hidden');
        }
    },
    notif_sowingSorted: function(notif)
    {
        var card_type_id = this.getCardTypeId(notif.args.sowing_card.type, notif.args.sowing_card.type_arg);
        var card_node_id = 'sowing_location_' + notif.args.sowing_card.location_arg + '_item_' + notif.args.sowing_card.id;

        this.sowing_card_stocks[notif.args.location].addToStockWithId(card_type_id, notif.args.sowing_card.id, card_node_id);
        this.sowing_card_stocks[notif.args.sowing_card.location_arg].removeFromStockById(notif.args.sowing_card.id);
    },
    notif_startingSpaceChosen: function(notif)
    {
        // Remove starting spaces
        query('.starting_space_' + this.gamedatas.players[notif.args.player_id].color).forEach(domConstruct.destroy);

        this.addObjectOnBoard(notif.args.q, notif.args.r, 'ziggurat_base', notif.args.player_id, true);
        this.ziggurat_base_counters[notif.args.player_id].incValue(-1);
    },
    notif_draftBegan: function(notif)
    {
        var animation = fx.animateProperty({
            node: 'draft_area_panel',
            duration: this.ANIMATION_DURATION,
            properties: {
                maxHeight: {
                    start: 1,
                    end: function() {
                        return dom.byId('draft_area_panel').scrollHeight;
                    }
                }
            },
            beforeBegin: function() {
                domStyle.set('draft_area_panel', 'overflowY', 'hidden');
                domStyle.set('draft_area_panel', 'display', '');
            }
        });

        animation.play();

        setTimeout(lang.hitch(this, function(notif) {
            for (var i in notif.args.draft_cards)
            {
                var draft_card = notif.args.draft_cards[i];
                var draft_card_type_id = this.getCardTypeId(draft_card.type, draft_card.type_arg);

                this.draft_card_stock.addToStockWithId(draft_card_type_id, draft_card.id, 'card_deck');
            }

            this.card_deck_counter.incValue(-notif.args.draft_cards.length);
        }, notif), this.ANIMATION_DURATION + this.ANIMATION_WAIT);
    },
    notif_foodCardChosen: function(notif)
    {
        var card_type_id = this.getCardTypeId(notif.args.card.type, notif.args.card.type_arg);
        var card_node_id = 'draft_area_card_item_' + notif.args.card.id;

        this.player_card_stocks[notif.args.player_id].addToStockWithId(card_type_id, notif.args.card.id, card_node_id);
        this.draft_card_stock.removeFromStockById(notif.args.card.id);
    },
    notif_draftEnded: function(notif)
    {
        var animation = fx.animateProperty({
            node: 'draft_area_panel',
            duration: this.ANIMATION_DURATION,
            properties: {
                maxHeight: {
                    start: function() {
                        return dom.byId('draft_area_panel').scrollHeight
                    },
                    end: 1
                }
            },
            beforeBegin: function() {
                domStyle.set('draft_area_panel', 'overflowY', 'hidden');
            },
            onEnd: function(){
                domStyle.set('draft_area_panel', 'display', 'none');
            }
        });

        animation.play();
    },
    notif_foodColumnChosen: function(notif)
    {
        var turn_order_node_id = 'player_turn_order_' + this.gamedatas.players[notif.args.player_id].color;
        var harvest_location_node_id = 'harvest_location_' +  notif.args.column;

        domStyle.set(turn_order_node_id, 'zIndex', 99);
        var slide = this.slideToObject(turn_order_node_id, harvest_location_node_id, this.ANIMATION_DURATION, 0);
        connect.connect(slide, 'onEnd', lang.hitch(this, function(turn_order_node_id, harvest_location_node_id) {
            this.attachToNewParent(turn_order_node_id, harvest_location_node_id);
            domStyle.set(turn_order_node_id, 'zIndex', 'auto');
        }, turn_order_node_id, harvest_location_node_id));
        slide.play();

        setTimeout(lang.hitch(this, function(notif) {
            for (var i in notif.args.cards)
            {
                var card = notif.args.cards[i];
                var card_type_id = this.getCardTypeId(card.type, card.type_arg);

                this.player_card_stocks[notif.args.player_id].addToStockWithId(card_type_id, card.id, 'sowing_location_' + card.location_arg + '_item_' + card.id);
                this.sowing_card_stocks[card.location_arg].removeFromStockById(card.id);
            }
        }, notif), this.ANIMATION_DURATION + this.ANIMATION_WAIT);
    },
    notif_newTurnOrderDetermined: function(notif)
    {
        for (var player_id in notif.args.players)
        {
            var turn_order_node_id = 'player_turn_order_' + this.gamedatas.players[player_id].color;
            var turn_order_location_node_id = 'turn_order_location_' + (notif.args.players[player_id].turn_order - 1);

            domStyle.set(turn_order_node_id, 'zIndex', 99);
            var slide = this.slideToObject(turn_order_node_id, turn_order_location_node_id, this.ANIMATION_DURATION, 0);
            connect.connect(slide, 'onEnd', lang.hitch(this, function(turn_order_node_id, turn_order_location_node_id) {
                this.attachToNewParent(turn_order_node_id, turn_order_location_node_id);
                domStyle.set(turn_order_node_id, 'zIndex', 'auto');
            }, turn_order_node_id, turn_order_location_node_id));
            slide.play();
        }
    },
    notif_hutPlaced: function(notif)
    {
        // Remove hut spaces
        query('.hut_space_' + this.gamedatas.players[notif.args.player_id].color).forEach(domConstruct.destroy);

        this.addObjectOnBoard(notif.args.q, notif.args.r, 'hut', notif.args.player_id, true);
        this.hut_counters[notif.args.player_id].incValue(-1);
    },
    notif_foodCardUsed: function(notif) {
        var card_type_id = this.getCardTypeId(notif.args.card.type, notif.args.card.type_arg);
        var card_node_id = 'player_area_card_' + notif.args.player_id + '_item_' + notif.args.card.id;

        if (notif.args.card.type == 'plow') {
            this.plow_card_stock.addToStockWithId(card_type_id, notif.args.card.id, card_node_id);
            this.player_card_stocks[notif.args.player_id].removeFromStockById(notif.args.card.id);
        } else {
            this.discard_card_stock.addToStockWithId(card_type_id, notif.args.card.id, card_node_id);
            this.player_card_stocks[notif.args.player_id].removeFromStockById(notif.args.card.id);

            setTimeout(lang.hitch(this, function(notif) {
                this.discard_counter.incValue(1);
            }, notif), this.ANIMATION_DURATION);
        }
    },
    notif_hutResupplied: function(notif)
    {
        var hut_node_id = 'hut_' + notif.args.q + '_' + notif.args.r;
        var hexagon_location_node_id = 'hexagon_location_' + notif.args.q + '_' + notif.args.r;

        domStyle.set(hut_node_id, 'zIndex', 99);
        var slide = this.slideToObjectPos(hut_node_id, hexagon_location_node_id, 6, 14, this.ANIMATION_DURATION, 0);
        connect.connect(slide, 'onEnd', lang.hitch(this, function(hut_node_id) {
            domStyle.set(hut_node_id, 'zIndex', 'auto');

            var handle_mouse_over = connect.connect(dom.byId(hexagon_location_node_id), 'onmouseover', lang.hitch(this, 'onHutResuppliedOver', notif.args.q, notif.args.r));

            this.hexagon_connections.push({
                q: notif.args.q,
                r: notif.args.r,
                handle: handle_mouse_over
            });

            var handle_mouse_out = connect.connect(dom.byId(hexagon_location_node_id), 'onmouseout', lang.hitch(this, 'onHutResuppliedOut', notif.args.q, notif.args.r));

            this.hexagon_connections.push({
                q: notif.args.q,
                r: notif.args.r,
                handle: handle_mouse_out
            });
        }, hut_node_id));
        slide.play();
    },
    notif_hutsNotResupplied: function(notif)
    {
        for (var i in notif.args.huts)
        {
            var hut = notif.args.huts[i];
            this.slideToObjectAndDestroy('hut_' + hut.q + '_' + hut.r, 'hut_icon_' + notif.args.player_id, this.ANIMATION_DURATION, 0);
        }

        setTimeout(lang.hitch(this, function(notif) {
            this.hut_counters[notif.args.player_id].incValue(notif.args.huts.length);
        }, notif), this.ANIMATION_DURATION);
    },
    notif_wellPlaced: function(notif)
    {
        // Remove well spaces
        query('.well_space').forEach(domConstruct.destroy);

        this.addWellOnBoard(notif.args.q, notif.args.r, notif.args.t, true);
        this.well_counter.incValue(-1);
    },
    notif_placeWellPassed: function(notif)
    {
        // Remove well spaces
        query('.well_space').forEach(domConstruct.destroy);
    },
    notif_camelsEarned: function(notif)
    {
        var camel_node_id = 'player_camel_' + this.gamedatas.players[notif.args.player_id].color;
        var camel_zone = this.getCamelZone(dom.byId(camel_node_id));

        this.camel_zones[notif.args.player_camel].placeInZone(camel_node_id, 0);
        camel_zone.removeFromZone(camel_node_id, false);
    },
    notif_pointsScored: function(notif)
    {
        this.scoreCtrl[notif.args.player_id].toValue(notif.args.player_score);
    },
    notif_zigguratBuilt: function(notif)
    {
        var camel_node_id = 'player_camel_' + this.gamedatas.players[notif.args.player_id].color;
        var camel_zone = this.getCamelZone(dom.byId(camel_node_id));

        this.camel_zones[notif.args.player_camel].placeInZone(camel_node_id, 0);
        camel_zone.removeFromZone(camel_node_id, false);

        setTimeout(lang.hitch(this, function(notif) {
            this.slideToObjectAndDestroy('hut_' + notif.args.q + '_' + notif.args.r, 'hut_icon_' + notif.args.player_id, this.ANIMATION_DURATION, 0);
        }, notif), this.ANIMATION_DURATION + this.ANIMATION_WAIT);

        setTimeout(lang.hitch(this, function(notif) {
            this.hut_counters[notif.args.player_id].incValue(1);
        }, notif), 2 * this.ANIMATION_DURATION + this.ANIMATION_WAIT);

        setTimeout(lang.hitch(this, function(notif) {
            this.addObjectOnBoard(notif.args.q, notif.args.r, 'ziggurat_base', notif.args.player_id, true);
            this.ziggurat_base_counters[notif.args.player_id].incValue(-1);
        }, notif), 2 * this.ANIMATION_DURATION + 2 * this.ANIMATION_WAIT);
    },
    notif_zigguratCenterExtended: function(notif)
    {
        var camel_node_id = 'player_camel_' + this.gamedatas.players[notif.args.player_id].color;
        var camel_zone = this.getCamelZone(dom.byId(camel_node_id));

        this.camel_zones[notif.args.player_camel].placeInZone(camel_node_id, 0);
        camel_zone.removeFromZone(camel_node_id, false);

        setTimeout(lang.hitch(this, function(notif) {
            this.addObjectOnBoard(notif.args.q, notif.args.r, 'ziggurat_center', notif.args.player_id, true);
            this.ziggurat_center_counters[notif.args.player_id].incValue(-1);
        }, notif), this.ANIMATION_DURATION + this.ANIMATION_WAIT);
    },
    notif_zigguratRoofExtended: function(notif)
    {
        var camel_node_id = 'player_camel_' + this.gamedatas.players[notif.args.player_id].color;
        var camel_zone = this.getCamelZone(dom.byId(camel_node_id));

        this.camel_zones[notif.args.player_camel].placeInZone(camel_node_id, 0);
        camel_zone.removeFromZone(camel_node_id, false);

        setTimeout(lang.hitch(this, function(notif) {
            this.addObjectOnBoard(notif.args.q, notif.args.r, 'ziggurat_roof', notif.args.player_id, true);
            this.ziggurat_roof_counters[notif.args.player_id].incValue(-1);
        }, notif), this.ANIMATION_DURATION + this.ANIMATION_WAIT);
    },
    notif_higherDignitaryInfluenced: function(notif)
    {
        var camel_node_id = 'player_camel_' + this.gamedatas.players[notif.args.player_id].color;
        var camel_zone = this.getCamelZone(dom.byId(camel_node_id));

        this.camel_zones[notif.args.player_camel].placeInZone(camel_node_id, 0);
        camel_zone.removeFromZone(camel_node_id, false);

        setTimeout(lang.hitch(this, function(notif) {
            this.addDignitaryOnBoard(notif.args.location, 'higher', notif.args.player_id, true);
            this.hut_counters[notif.args.player_id].incValue(-1);
        }, notif), this.ANIMATION_DURATION + this.ANIMATION_WAIT);
    },
    notif_middleDignitaryInfluenced: function(notif)
    {
        var camel_node_id = 'player_camel_' + this.gamedatas.players[notif.args.player_id].color;
        var camel_zone = this.getCamelZone(dom.byId(camel_node_id));

        this.camel_zones[notif.args.player_camel].placeInZone(camel_node_id, 0);
        camel_zone.removeFromZone(camel_node_id, false);

        setTimeout(lang.hitch(this, function(notif) {
            this.addDignitaryOnBoard(notif.args.location, 'middle', notif.args.player_id, true);
            this.hut_counters[notif.args.player_id].incValue(-1);
        }, notif), this.ANIMATION_DURATION + this.ANIMATION_WAIT);
    },
    notif_lowerDignitaryInfluenced: function(notif)
    {
        var camel_node_id = 'player_camel_' + this.gamedatas.players[notif.args.player_id].color;
        var camel_zone = this.getCamelZone(dom.byId(camel_node_id));

        this.camel_zones[notif.args.player_camel].placeInZone(camel_node_id, 0);
        camel_zone.removeFromZone(camel_node_id, false);

        setTimeout(lang.hitch(this, function(notif) {
            this.addDignitaryOnBoard(notif.args.location, 'lower', notif.args.player_id, true);
            this.hut_counters[notif.args.player_id].incValue(-1);
        }, notif), this.ANIMATION_DURATION + this.ANIMATION_WAIT);
    },
    notif_offeringMade: function(notif)
    {
        var camel_node_id = 'player_camel_' + this.gamedatas.players[notif.args.player_id].color;
        var camel_zone = this.getCamelZone(dom.byId(camel_node_id));

        this.camel_zones[notif.args.player_camel].placeInZone(camel_node_id, 0);
        camel_zone.removeFromZone(camel_node_id, false);

        setTimeout(lang.hitch(this, function(notif) {
            var offering_node_id = 'player_offering_' + this.gamedatas.players[notif.args.player_id].color;
            var offering_zone = this.getOfferingZone(dom.byId(offering_node_id));

            this.offering_zones[notif.args.player_offering].placeInZone(offering_node_id, 0);
            offering_zone.removeFromZone(offering_node_id, false);
        }, notif), this.ANIMATION_DURATION + this.ANIMATION_WAIT);
    },
    notif_foodCardBought: function(notif)
    {
        var camel_node_id = 'player_camel_' + this.gamedatas.players[notif.args.player_id].color;
        var camel_zone = this.getCamelZone(dom.byId(camel_node_id));

        this.camel_zones[notif.args.player_camel].placeInZone(camel_node_id, 0);
        camel_zone.removeFromZone(camel_node_id, false);

        setTimeout(lang.hitch(this, function(notif) {
            var card_type_id = this.getCardTypeId(notif.args.card.type, notif.args.card.type_arg);
            var card_node_id = 'sowing_location_' + notif.args.card.location_arg + '_item_' + notif.args.card.id;

            this.player_card_stocks[notif.args.player_id].addToStockWithId(card_type_id, notif.args.card.id, card_node_id);
            this.sowing_card_stocks[notif.args.card.location_arg].removeFromStockById(notif.args.card.id);
        }, notif), this.ANIMATION_DURATION + this.ANIMATION_WAIT);
    },
    notif_plowCardBought: function(notif)
    {
        var camel_node_id = 'player_camel_' + this.gamedatas.players[notif.args.player_id].color;
        var camel_zone = this.getCamelZone(dom.byId(camel_node_id));

        this.camel_zones[notif.args.player_camel].placeInZone(camel_node_id, 0);
        camel_zone.removeFromZone(camel_node_id, false);

        setTimeout(lang.hitch(this, function(notif) {
            var card_type_id = this.getCardTypeId(notif.args.card.type, notif.args.card.type_arg);
            var card_node_id = 'plow_location_item_' + notif.args.card.id;

            this.player_card_stocks[notif.args.player_id].addToStockWithId(card_type_id, notif.args.card.id, card_node_id);
            this.plow_card_stock.removeFromStockById(notif.args.card.id);
        }, notif), this.ANIMATION_DURATION + this.ANIMATION_WAIT);
    },
    notif_turnEnded: function(notif)
    {
        var expansion_card_type_id = this.getExpansionTypeId(notif.args.expansion_card.type, notif.args.expansion_card.type_arg);
        this.expansion_card_stocks[notif.args.expansion_card.location_arg].addToStockWithId(expansion_card_type_id, notif.args.expansion_card.id, 'expansion_deck');
        this.expansion_deck_counter.incValue(-1);

        setTimeout(lang.hitch(this, function(notif) {
            for (var i in notif.args.huts)
            {
                var hut = notif.args.huts[i];
                var hut_node_id = 'hut_' + hut.q + '_' + hut.r;
                var hexagon_location_node_id = 'hexagon_location_' + hut.q + '_' + hut.r;

                this.removeHexagonConnections(hut.q, hut.r);

                domStyle.set(hut_node_id, 'zIndex', 99);
                var slide = this.slideToObjectPos(hut_node_id, hexagon_location_node_id, 6, 29, this.ANIMATION_DURATION, 0);
                connect.connect(slide, 'onEnd', lang.hitch(this, function(hut_node_id) {
                    domStyle.set(hut_node_id, 'zIndex', 'auto');
                }, hut_node_id));
                slide.play();
            }
        }, notif), this.ANIMATION_DURATION + this.ANIMATION_WAIT);

        setTimeout(lang.hitch(this, function(notif) {
            for (var i in notif.args.oldSowings)
            {
                var card = notif.args.oldSowings[i];
                var card_type_id = this.getCardTypeId(card.type, card.type_arg);

                this.discard_card_stock.addToStockWithId(card_type_id, card.id, 'sowing_location_' + card.location_arg + '_item_' + card.id);
                this.sowing_card_stocks[card.location_arg].removeFromStockById(card.id);
            }
        }, notif), 2 * this.ANIMATION_DURATION + 2 * this.ANIMATION_WAIT);

        setTimeout(lang.hitch(this, function(notif) {
            this.discard_counter.incValue(notif.args.oldSowings.length);
            this.turn_counter.incValue(1);
        }, notif), 3 * this.ANIMATION_DURATION + 2 * this.ANIMATION_WAIT);
    },
    notif_hutsFlooded: function(notif)
    {
        for (var i in notif.args.huts)
        {
            var hut = notif.args.huts[i];

            this.removeHexagonConnections(hut.q, hut.r);

            this.slideToObjectAndDestroy('hut_' + hut.q + '_' + hut.r, 'hut_icon_' + notif.args.player_id, this.ANIMATION_DURATION, 0);
        }

        setTimeout(lang.hitch(this, function(notif) {
            this.hut_counters[notif.args.player_id].incValue(notif.args.huts.length);
        }, notif), this.ANIMATION_DURATION);
    },
    notif_noDignitaryPointsScored: function(notif)
    {
        var expansion_discard_number = 0;

        for (var i in notif.args.expansion_cards)
        {
            var expansion_card = notif.args.expansion_cards[i];
            var expansion_card_type_id = this.getExpansionTypeId(expansion_card.type, expansion_card.type_arg);

            if (expansion_card.type == 'bonus') {
                this.expansion_card_stocks[expansion_card.location_arg].removeFromStockById(expansion_card.id);
            } else {
                this.discard_expansion_stock.addToStockWithId(expansion_card_type_id, expansion_card.id, 'expansion_location_' + expansion_card.location_arg + '_item_' + expansion_card.id);
                this.expansion_card_stocks[expansion_card.location_arg].removeFromStockById(expansion_card.id);

                expansion_discard_number++;
            }
        }

        setTimeout(lang.hitch(this, function(notif) {
                    this.expansion_discard_counter.incValue(expansion_discard_number);
        }, notif), this.ANIMATION_DURATION);
    },
    notif_dignitaryPointsScored: function(notif)
    {
        this.scoreCtrl[notif.args.player_id].toValue(notif.args.player_score);

        var expansion_discard_number = 0;

        for (var i in notif.args.expansion_cards)
        {
            var expansion_card = notif.args.expansion_cards[i];
            var expansion_card_type_id = this.getExpansionTypeId(expansion_card.type, expansion_card.type_arg);

            if (expansion_card.type == 'bonus') {
                this.expansion_card_stocks[expansion_card.location_arg].removeFromStockById(expansion_card.id);
            } else {
                this.discard_expansion_stock.addToStockWithId(expansion_card_type_id, expansion_card.id, 'expansion_location_' + expansion_card.location_arg + '_item_' + expansion_card.id);
                this.expansion_card_stocks[expansion_card.location_arg].removeFromStockById(expansion_card.id);

                expansion_discard_number++;
            }
        }

        setTimeout(lang.hitch(this, function(notif) {
                    this.expansion_discard_counter.incValue(expansion_discard_number);
        }, notif), this.ANIMATION_DURATION);
    },
    notif_higherDignitaryBonusProvided: function(notif)
    {
        this.scoreCtrl[notif.args.player_id].toValue(notif.args.player_score);

        for (var i in notif.args.higher_dignitary)
        {
            var higher_dignitary = notif.args.higher_dignitary[i];
            this.slideToObjectAndDestroy('higher_dignitary_' + higher_dignitary, 'hut_icon_' + notif.args.player_id, this.ANIMATION_DURATION, 0);
        }

        setTimeout(lang.hitch(this, function(notif) {
            this.hut_counters[notif.args.player_id].incValue(notif.args.higher_dignitary.length);
        }, notif), this.ANIMATION_DURATION);
    },
    notif_noMiddleDignitaryBonusProvided: function(notif)
    {
        for (var i in notif.args.middle_dignitary)
        {
            var middle_dignitary = notif.args.middle_dignitary[i];
            this.slideToObjectAndDestroy('middle_dignitary_' + middle_dignitary, 'hut_icon_' + notif.args.player_id, this.ANIMATION_DURATION, 0);
        }

        setTimeout(lang.hitch(this, function(notif) {
            this.hut_counters[notif.args.player_id].incValue(notif.args.middle_dignitary.length);
        }, notif), this.ANIMATION_DURATION);
    },
    notif_middleDignitaryBonusProvided: function(notif)
    {
        var card_type_id = this.getCardTypeId(notif.args.plow_card.type, notif.args.plow_card.type_arg);
        var card_node_id = 'plow_location_item_' + notif.args.plow_card.id;

        this.player_card_stocks[notif.args.player_id].addToStockWithId(card_type_id, notif.args.plow_card.id, card_node_id);
        this.plow_card_stock.removeFromStockById(notif.args.plow_card.id);

        setTimeout(lang.hitch(this, function(notif) {
            for (var i in notif.args.middle_dignitary)
            {
                var middle_dignitary = notif.args.middle_dignitary[i];
                this.slideToObjectAndDestroy('middle_dignitary_' + middle_dignitary, 'hut_icon_' + notif.args.player_id, this.ANIMATION_DURATION, 0);
            }
        }, notif), this.ANIMATION_DURATION + this.ANIMATION_WAIT);

        setTimeout(lang.hitch(this, function(notif) {
            this.hut_counters[notif.args.player_id].incValue(notif.args.middle_dignitary.length);
        }, notif), 2 * this.ANIMATION_DURATION + this.ANIMATION_WAIT);
    },
    notif_lowerDignitaryBonusProvided: function(notif)
    {
        var camel_node_id = 'player_camel_' + this.gamedatas.players[notif.args.player_id].color;
        var camel_zone = this.getCamelZone(dom.byId(camel_node_id));

        this.camel_zones[notif.args.player_camel].placeInZone(camel_node_id, 0);
        camel_zone.removeFromZone(camel_node_id, false);

        setTimeout(lang.hitch(this, function(notif) {
            for (var i in notif.args.lower_dignitary)
            {
                var lower_dignitary = notif.args.lower_dignitary[i];
                this.slideToObjectAndDestroy('lower_dignitary_' + lower_dignitary, 'hut_icon_' + notif.args.player_id, this.ANIMATION_DURATION, 0);
            }
        }, notif), this.ANIMATION_DURATION + this.ANIMATION_WAIT);

        setTimeout(lang.hitch(this, function(notif) {
            this.hut_counters[notif.args.player_id].incValue(notif.args.lower_dignitary.length);
        }, notif), 2 * this.ANIMATION_DURATION + this.ANIMATION_WAIT);
    },
    notif_offeringPointsScored: function(notif)
    {
        this.scoreCtrl[notif.args.player_id].toValue(notif.args.player_score);

        var offering_node_id = 'player_offering_' + this.gamedatas.players[notif.args.player_id].color;
        var offering_zone = this.getOfferingZone(dom.byId(offering_node_id));

        if (offering_zone.container_div != 'offering_location_0') {
            this.offering_zones[0].placeInZone(offering_node_id, 0);
            offering_zone.removeFromZone(offering_node_id, false);
        }
    },
    notif_reignEnded: function(notif)
    {
        var expansion_card_type_id = this.getExpansionTypeId(notif.args.expansion_card.type, notif.args.expansion_card.type_arg);
        this.expansion_card_stocks[notif.args.expansion_card.location_arg].addToStockWithId(expansion_card_type_id, notif.args.expansion_card.id, 'expansion_deck');
        this.expansion_deck_counter.incValue(-1);

        if (notif.args.bonus_card != null) {
            var bonus_card_type_id = this.getExpansionTypeId(notif.args.bonus_card.type, notif.args.bonus_card.type_arg);
            this.expansion_card_stocks[notif.args.bonus_card.location_arg].addToStockWithId(bonus_card_type_id, notif.args.bonus_card.id);
        }

        setTimeout(lang.hitch(this, function(notif) {
            for (var i in notif.args.huts)
            {
                var hut = notif.args.huts[i];
                var hut_node_id = 'hut_' + hut.q + '_' + hut.r;
                var hexagon_location_node_id = 'hexagon_location_' + hut.q + '_' + hut.r;

                this.removeHexagonConnections(hut.q, hut.r);

                domStyle.set(hut_node_id, 'zIndex', 99);
                var slide = this.slideToObjectPos(hut_node_id, hexagon_location_node_id, 6, 29, this.ANIMATION_DURATION, 0);
                connect.connect(slide, 'onEnd', lang.hitch(this, function(hut_node_id) {
                    domStyle.set(hut_node_id, 'zIndex', 'auto');
                }, hut_node_id));
                slide.play();
            }
        }, notif), this.ANIMATION_DURATION + this.ANIMATION_WAIT);

        setTimeout(lang.hitch(this, function(notif) {
            for (var i in notif.args.oldSowings)
            {
                var card = notif.args.oldSowings[i];
                var card_type_id = this.getCardTypeId(card.type, card.type_arg);

                this.discard_card_stock.addToStockWithId(card_type_id, card.id, 'sowing_location_' + card.location_arg + '_item_' + card.id);
                this.sowing_card_stocks[card.location_arg].removeFromStockById(card.id);
            }
        }, notif), 2 * this.ANIMATION_DURATION + 2 * this.ANIMATION_WAIT);

        setTimeout(lang.hitch(this, function(notif) {
            this.discard_counter.incValue(notif.args.oldSowings.length);
            this.reign_counter.incValue(1);
            this.turn_counter.setValue(1);
        }, notif), 3 * this.ANIMATION_DURATION + 2 * this.ANIMATION_WAIT);
    }
});

});
