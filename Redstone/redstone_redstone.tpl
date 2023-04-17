{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Redstone implementation : © <Mark Steere> <marksteere@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    redstone_redstone.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->


<div id="board" class="goban_{BOARD_SIZE}">
    
    <div id="overlay_billboard_1" class="overlay_billboard" style="left: 0px; top: 0px;"></div>

	<!-- BEGIN clickable_square -->
        <div id="clickable_square_{X}_{Y}" class="clickable_square_{BOARD_SIZE}" style="left: {LEFT}px; top: {TOP}px; "></div>
	<!-- END clickable_square -->
    
	<!-- BEGIN placement_square -->
        <div id="placementSquare_{X}_{Y}" class="placement_square_{BOARD_SIZE}" style="left: {LEFT}px; top: {TOP}px; "></div>
    <!-- END placement_square -->
    
	<div id="stones"></div>
</div>




<script type="text/javascript">

// Javascript HTML templates

var jstpl_black_or_red_stone='<div class="black_or_red_stone_{BOARD_SIZE}" id="${stone_id}" style="${bkgPos}; pointer-events: none; z-index: 3"></div>';
var jstpl_white_stone='<div class="white_stone_{BOARD_SIZE}" id="${stone_id}" style="${bkgPos}; pointer-events: none; z-index: 3"></div>';

var jstpl_last_move_indicator='<div class="last_move_indicator" id="${last_move_id}" style="${bkgPos}; pointer-events: none; z-index: 5"></div>';


var jstpl_billboard_content='<div class="coords_overlay{BOARD_SIZE}" id="coords_overlay_1" style="${bkgPos}; pointer-events: none; z-index: 3"></div>';


var jstpl_player_board = '<div style="font-size:4px;">&nbsp;</div><div class="cp_board"><div class="icon_placed_stones_${code_img}" id="icon-placed-stones_${player_id}"></div><span id="placed-stones_p${player_id}">0</span></div>';

</script>  

{OVERALL_GAME_FOOTER}
