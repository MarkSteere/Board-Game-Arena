{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Herd implementation : © <Mark Steere> <marksteere@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    herd_herd.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->


<div id="board" class="board_{BOARD_SIZE}">
    
    <div id="overlay_billboard_1" class="overlay_billboard" style="left: 0px; top: 0px;"></div>

	<!-- BEGIN clickable_cell -->
        <div id="clickable_cell_{U}_{V}" class="clickable_cell_{BOARD_SIZE}" style="left: {LEFT}px; top: {TOP}px; "></div>
	<!-- END clickable_cell -->    
    
	<!-- BEGIN placement_cell -->
        <div id="placement_cell_{U}_{V}" class="placement_cell_{BOARD_SIZE}" style="left: {LEFT}px; top: {TOP}px; "></div>
    <!-- END placement_cell -->


	<!-- BEGIN dice_square -->
        <div id="dice_square_{X}" class="dice_square" style="left: {LEFT}px; top: {TOP}px; "></div>
    <!-- END dice_square -->

	<div id="stones"></div>
</div>



<script type="text/javascript">

// Javascript HTML templates

var jstpl_black_stone='<div class="black_stone_{BOARD_SIZE}" id="${stone_id}" style="${bkgPos}; pointer-events: none; z-index: 3"></div>';
var jstpl_white_stone='<div class="white_stone_{BOARD_SIZE}" id="${stone_id}" style="${bkgPos}; pointer-events: none; z-index: 3"></div>';


var jstpl_die='<div class="die" id="${die_id}" style="${bkgPos}; pointer-events: none; z-index: 3"></div>';


var jstpl_billboard_content='<div class="coords_overlay_{BOARD_SIZE}" id="coords_overlay_1" style="${bkgPos}; pointer-events: none; z-index: 3"></div>';


var jstpl_player_board = '<div style="font-size:4px;">&nbsp;</div><div class="cp_board"><div class="icon_number_of_stones_${code_img}" id="icon-number-of-stones_${player_id}"></div><span id="number-of-stones_p${player_id}">0</span></div>';

</script>  

{OVERALL_GAME_FOOTER}
