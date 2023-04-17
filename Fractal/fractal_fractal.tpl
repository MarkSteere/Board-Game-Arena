{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Fractal implementation : © <Mark Steere> <marksteere@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    fractal_fractal.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->

<div id="board" class="fractal_board">
    
    <!-- BEGIN clickable_square -->
        <div id="clickable_square_{DIV_U}_{DIV_V}" class="clickable_square_{CLASS_U}_{CLASS_V}" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END clickable_square -->
   
        <div id="tiles"></div>
</div>




<script type="text/javascript">

// Javascript HTML templates

var jstpl_player_board = '<div style="font-size:4px;">&nbsp;</div><div class="cp_board"><div class="icon_placed_tiles_${code_img}" id="icon-placed-tiles_${player_id}"></div><span id="placed-tiles_p${player_id}">0</span></div>';


var jstpl_tile_S='<div class="tile_S" id="${tile_id}" style="${bkgPos}; pointer-events: none; z-index: 3"></div>';  // Small tile table

var jstpl_tile_M='<div class="tile_M" id="${tile_id}" style="${bkgPos}; pointer-events: none; z-index: 3"></div>';  // Medium tile table

var jstpl_tile_L='<div class="tile_L" id="${tile_id}" style="${bkgPos}; pointer-events: none; z-index: 3"></div>';  // Large tile table




</script>  

{OVERALL_GAME_FOOTER}
