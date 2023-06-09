{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Coins implementation : © <Mark Steere> <marksteere@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    coins_coins.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks    
-->

<div id="card_table" class="card_table">

    <div id="playertables">

        <!-- BEGIN player -->
        <div class="playertable playertable_{DIR}">
            <div class="playertablecard" id="playertablecard_{PLAYER_ID}">
            </div>

            <div class="playertablename" style="color:#{PLAYER_COLOR}">
                {PLAYER_NAME}
            </div>
        </div>
        <!-- END player -->

    </div>

    <div id="myhand_wrap" class="hand_container">
    <!-- <div id="myhand_wrap">  -->
        <!-- <h3>{MY_HAND}</h3> -->
        <div id="myhand">
        </div>
    </div>

</div>



<script type="text/javascript">

var jstpl_cardontable = '<div class="cardontable" id="cardontable_${player_id}" style="background-position:-${x}px -${y}px">\
                        </div>';

</script>  

{OVERALL_GAME_FOOTER}
