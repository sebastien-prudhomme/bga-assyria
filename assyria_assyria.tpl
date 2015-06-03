{OVERALL_GAME_HEADER}

<script type="text/javascript">
    var jstpl_player_counter = '<div class="player_counter">\
    <span id="hut_help_${id}"><span id="hut_${id}" class="counter_text"></span><div id="hut_icon_${id}" class="counter_icon huts hut hut_${color}"></div></span>\
    • <span id="ziggurat_base_help_${id}"><span id="ziggurat_base_${id}" class="counter_text"></span><div id="ziggurat_base_icon_${id}" class="counter_icon ziggurats ziggurat_base ziggurat_base_${color}"></div></span>\
    • <span id="ziggurat_center_help_${id}"><span id="ziggurat_center_${id}" class="counter_text"></span><div id="ziggurat_center_icon_${id}" class="counter_icon ziggurats ziggurat_center ziggurat_center_${color}"></div></span>\
    • <span id="ziggurat_roof_help_${id}"><span id="ziggurat_roof_${id}" class="counter_text"></span><div id="ziggurat_roof_icon_${id}" class="counter_icon ziggurats ziggurat_roof ziggurat_roof_${color}"></div></span>\
    </div>';

    var jstpl_higher_dignitary = '<div class="huts hut hut_${color}" id="higher_dignitary_${location}" style="position: absolute; left: 2px; top: -1px;"></div>';
    var jstpl_middle_dignitary = '<div class="huts hut hut_${color}" id="middle_dignitary_${location}" style="position: absolute; left: 2px; top: -1px;"></div>';
    var jstpl_lower_dignitary = '<div class="huts hut hut_${color}" id="lower_dignitary_${location}" style="position: absolute; left: 2px; top: -1px;"></div>';
    var jstpl_player_camel = '<div class="discs disc disc_${color}" id="player_camel_${color}"></div>';
    var jstpl_player_offering = '<div class="discs disc disc_${color}" id="player_offering_${color}"></div>';
    var jstpl_player_turn_order = '<div class="discs disc disc_${color}" id="player_turn_order_${color}" style="position: absolute; top: 4px;"></div>';
    var jstpl_forbidden = '<div class="hexagon_forbidden" id="forbidden_${q}_${r}" style="position: absolute; left: -13px; top: 0;"></div>';
    var jstpl_hut = '<div class="huts hut hut_${color}" id="hut_${q}_${r}" style="position: absolute; left: 6px; top: 29px;"></div>';
    var jstpl_hut_resupplied = '<div class="huts hut hut_${color}" id="hut_${q}_${r}" style="position: absolute; left: 6px; top: 14px;"></div>';
    var jstpl_hut_space = '<div class="huts hut_space hut_space_${color}" id="hut_space_${q}_${r}" style="position: absolute; left: 6px; top: 29px;"></div>';
    var jstpl_starting_space = '<div class="ziggurats starting_space starting_space_${color}" id="starting_space_${q}_${r}" style="position: absolute; left: -2px; top: 7px;"></div>';
    var jstpl_well = '<div class="wells well" id="well_${q}_${r}_${t}" style="position: absolute;"></div>';
    var jstpl_well_space = '<div class="wells well_space" id="well_space_${q}_${r}_${t}"></div>';
    var jstpl_ziggurat_base = '<div class="ziggurats ziggurat_base ziggurat_base_${color}" id="ziggurat_base_${q}_${r}" style="position: absolute; left: -2px; top: 7px;"></div>';
    var jstpl_ziggurat_base_built = '<div class="ziggurats ziggurat_base ziggurat_base_${color}" id="ziggurat_base_${q}_${r}" style="position: absolute; left: -2px; top: 7px;"></div>';
    var jstpl_ziggurat_center = '<div class="ziggurats ziggurat_center ziggurat_center_${color}" id="ziggurat_center_${q}_${r}" style="position: absolute; left: 3px; top: 12px;"></div>';
    var jstpl_ziggurat_center_built = '<div class="ziggurats ziggurat_center ziggurat_center_${color}" id="ziggurat_center_${q}_${r}" style="position: absolute; left: 3px; top: 12px;"></div>';
    var jstpl_ziggurat_roof = '<div class="ziggurats ziggurat_roof ziggurat_roof_${color}" id="ziggurat_roof_${q}_${r}" style="position: absolute; left: 8px; top: 17px;"></div>';
    var jstpl_ziggurat_roof_built = '<div class="ziggurats ziggurat_roof ziggurat_roof_${color}" id="ziggurat_roof_${q}_${r}" style="position: absolute; left: 8px; top: 17px;"></div>';
</script>

<div id="draft_area_panel" style="display: {DRAFT_DISPLAY};">
    <div id="draft_area" class="whiteblock" style="height: 115px;">
        <h3>{DRAFT_TITLE}</h3>
        <div id="draft_area_card"></div>
    </div>
</div>

<div class="board">
    <!-- BEGIN hexagon_location -->
    <div id="hexagon_location_{Q}_{R}" class="hexagon_location" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END hexagon_location -->

    <!-- BEGIN well_location -->
    <div id="well_location_{Q}_{R}_{T}" class="well_location" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END well_location -->

    <!-- BEGIN camel_location -->
    <div id="camel_location_{I}" class="disc_location" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END camel_location -->

    <div id="camel_help" style="position: absolute; left: 521px; top: 88px; width: 38px; height: 236px;"></div>

    <!-- BEGIN turn_order_location -->
    <div id="turn_order_location_{I}" class="disc_location" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END turn_order_location -->

    <div id="turn_order_help" style="position: absolute; left: 491px; top: 364px; width: 79px; height: 33px;"></div>
    <div id="higher_dignitary_help" style="position: absolute; left: 595px; top: 16px; width: 77px; height: 95px;"></div>

    <!-- BEGIN higher_dignitary_location -->
    <div id="higher_dignitary_location_{I}" class="disc_location" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END higher_dignitary_location -->

    <div id="middle_dignitary_help" style="position: absolute; left: 595px; top: 116px; width: 77px; height: 114px;"></div>

    <!-- BEGIN middle_dignitary_location -->
    <div id="middle_dignitary_location_{I}" class="disc_location" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END middle_dignitary_location -->

    <div id="lower_dignitary_help" style="position: absolute; left: 595px; top: 235px; width: 77px; height: 152px;"></div>

    <!-- BEGIN lower_dignitary_location -->
    <div id="lower_dignitary_location_{I}" class="disc_location" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END lower_dignitary_location -->

    <!-- BEGIN expansion_location -->
    <div id="expansion_location_{I}" class="card_location" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END expansion_location -->

    <div id="plow_location" class="card_location" style="left: 623px; top: 397px;"></div>
    <div id="well_help" style="position: absolute; left: 50px; top: 355px; width: 39px; height: 40px;"></div>
    <div id="ziggurat_help" style="position: absolute; left: 18px; top: 423px; width: 151px; height: 57px;"></div>
    <div id="offering_help" style="position: absolute; left: 180px; top: 423px; width: 156px; height: 57px;"></div>

    <!-- BEGIN offering_location -->
    <div id="offering_location_{I}" class="disc_location" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END offering_location -->

    <!-- BEGIN harvest_location -->
    <div id="harvest_location_{I}" class="disc_location" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END harvest_location -->

    <!-- BEGIN sowing_location -->
    <div id="sowing_location_{I}" class="card_location" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END sowing_location -->
</div>

<div id="stock" class="whiteblock" style="float: left; width: calc(100% - 753px); min-width: 234px;">
    <h3>{REIGN_TITLE}<span id="reign"></span> • {TURN_TITLE}<span id="turn"></span></h3>
    <div>
        <div id="well_stock_help" style="display: inline-block; vertical-align: middle;">
            <div id="well_icon" class="counter_icon wells well"></div>
            <div id="well_stock" style="text-align: center"></div>
        </div>
        <div id="expansion_deck_help" style="display: inline-block; vertical-align: middle;">
            <div id="expansion_deck" class="counter_icon expansion_deck"></div>
            <div id="expansion_deck_number" style="text-align: center;"></div>
        </div>
        <div id="expansion_discard_help" style="display: inline-block; vertical-align: middle;">
            <div id="expansion_discard_area" class="counter_icon" style="width: 50px;"></div>
            <div id="expansion_discard_number" style="text-align: center;"></div>
        </div>
        <div id="card_deck_help" style="display: inline-block; vertical-align: middle;">
            <div id="card_deck" class="counter_icon card_deck" style="visibility: hidden;"></div>
            <div id="card_deck_number" style="text-align: center;"></div>
        </div>
        <div id="discard_help" style="display: inline-block; vertical-align: middle;">
            <div id="discard_area" class="counter_icon" style="width: 50px; margin-right: 0;"></div>
            <div id="discard_number" style="text-align: center;"></div>
        </div>
    </div>
</div>

<!-- BEGIN player_area -->
<div id="player_areas">
    <div id="player_area_{PLAYER_ID}" class="whiteblock" style="float: left; width: calc(100% - 753px); min-width: 220px;">
        <h3 style="color:#{PLAYER_COLOR}">{PLAYER_NAME}</h3>
        <div id="player_area_card_{PLAYER_ID}"></div>
    </div>
</div>
<!-- END player_area -->

{OVERALL_GAME_FOOTER}
