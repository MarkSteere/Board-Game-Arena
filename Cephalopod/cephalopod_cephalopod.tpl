{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Cephalopod implementation : © <Mark Steere> <marksteere@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    cephalopod_cephalopod.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->

<div id="board" class="board{BOARD_SIZE}">
    
    <div id="overlay_billboard_1" class="overlay_billboard" style="left: 0px; top: 0px;"></div>

    <!-- BEGIN square -->
        <div id="square_{X}_{Y}" class="square" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END square -->
   
        <div id="dice"></div>
</div>


<script type="text/javascript">

// Javascript HTML templates

var jstpl_die='<div class="die" id="${die_id}" style="${bkgPos}; pointer-events: none; z-index: 3"></div>';


var jstpl_last_move_indicator='<div class="last_move_indicator" id="${last_move_id}" style="${bkgPos}; pointer-events: none; z-index: 5"></div>';


var jstpl_billboard_content='<div class="coords_overlay{BOARD_SIZE}" id="coords_overlay_1" style="${bkgPos}; pointer-events: none; z-index: 3"></div>';


var jstpl_player_board = '<div style="font-size:4px;">&nbsp;</div><div class="cp_board"><div class="icon_placed_dice_${code_img}" id="icon-placed-dice_${player_id}"></div><span id="placed-dice_p${player_id}">0</span></div>';


</script>  

{OVERALL_GAME_FOOTER}
