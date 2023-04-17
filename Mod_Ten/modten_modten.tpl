{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- ModTen implementation : © <Mark Steere> <marksteere@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    modten_modten.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
-->

<div id="board" class="board_{N_PLAYERS}">

    <div id="played_card_lists" class="played_card_lists_{N_PLAYERS}">

        <!-- BEGIN player -->
        <div class="name_and_played_cards name_and_played_cards_{PLAYER_LIST_POSITION}">
            <div class="played_cards_name" style="color:#{PLAYER_COLOR}; font-size:20px; font-weight:bold;">
                {PLAYER_NAME}
            </div>

            <div class="played_cards_list" id="played_cards_list_{PLAYER_ID}" style="font-size:16px;">
            </div>

        </div>
        <!-- END player -->

    </div>


    <div id="rank_trick_suit_container_id" class="rank_trick_suit_container">
        <div id="rank_container_id" class = "rank_container">
        </div>
        <div id="trick_suit_container_id" class = "trick_suit_container">
        </div>
    </div>


    <div id="myhand_wrap" class="hand_container hand_container_{N_PLAYERS}">
        <div id="myhand">
        </div>
    </div>

	<div id="images"></div>

</div>






<script type="text/javascript">

// Javascript HTML templates

var jstpl_rank='<div class="rank" id="${rank_id}" style="${bkgPos}; pointer-events: none; z-index: 3"></div>';

var jstpl_trick_suit='<div class="trick_suit" id="${trick_suit_id}" style="${bkgPos}; pointer-events: none; z-index: 3"></div>';


var jstpl_player_board = '<div style="font-size:4px;">&nbsp;</div><div class="cp_board"><span id="n_played_cards_p${player_id}">0</span> / {HAND_SIZE}</div>';

</script>  

{OVERALL_GAME_FOOTER}
