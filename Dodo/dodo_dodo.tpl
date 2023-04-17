{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Dodo implementation : © <Mark Steere> <marksteere@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    dodo_dodo.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->


<div id="board">
    
    <div id="info_billboard_1" class="info_billboard" style="left: 0px; top: 20px;"></div>

    <!-- BEGIN square -->
        <div id="square_{U}_{V}" class="square" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END square -->
   
        <div id="checkers"></div>
</div>


<script type="text/javascript">
// Javascript HTML templates

var jstpl_checker='<div class="checker" id="${checker_id}" style="${bkgPos}; pointer-events: none; z-index: 3"></div>';


var jstpl_billboard_content='<div class="misere_stamp" id="misere_stamp_1" style="${bkgPos}; pointer-events: none; z-index: 3"></div>';


</script>  


{OVERALL_GAME_FOOTER}
