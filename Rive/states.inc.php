<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Rive implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * states.inc.php
 *
 * Rive game states description
 *
 */


//    !! It is not a good idea to modify this file when a game is running !!

 
$machinestates = array(

    // The initial state. Please do not modify.
    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array( "" => 2 )
    ),
    
    // Note: ID=2 => your first state

    2 => array(
        "name" => "placeChecker",
        "description" => clienttranslate('${actplayer} must place a checker.'),
        "descriptionmyturn" => clienttranslate('${you} must place a checker.'),
        "type" => "activeplayer",
        "args" => "argPlaceChecker",  // 'selectableCells'

        "possibleactions" => array( "placeChecker" ),

        "transitions" => array( "placeNonGroupingChecker" => 4, "placeGroupingChecker" => 3, "firstMoveChoice" => 12, "zombiePass" => 4 )
    ),

    3 => array(
        "name" => "removeChecker",
        "description" => clienttranslate('${actplayer} must remove a checker.'),
        "descriptionmyturn" => clienttranslate('${you} must remove a checker.'),
        "type" => "activeplayer",
        "args" => "argRemoveChecker",  // 'removableCheckers'

        "possibleactions" => array( "removeChecker" ),

        "transitions" => array( "removeNotLastChecker" => 3, "removeLastChecker" => 2, "zombiePass" => 4 ),
    ),



    12 => array(
        "name" => "nextPlayerFirstMove",
		"type" => "game",
        "action" => "stNextPlayerFirstMove",
        "updateGameProgression" => true,  
        
        "transitions" => array( "placeNonGroupingChecker" => 4, "firstMoveChoice" => 13)
    ),
    
    13 => array(
        "name" => "firstMoveChoice",
		"description" => clienttranslate('${actplayer} may choose to switch colors and keep your first move as their own.'),

		"descriptionmyturn" => clienttranslate('${you} can switch colors and keep your opponents first move as your own.  Or just play on with your current color.'),

        "type" => "activeplayer",
        "args" => "argPlaceChecker",  // 'selectableCells'

        "possibleactions" => array( 'chooseFirstMove', 'placeChecker' ),

        "transitions" => array( "nextTurn" => 4, "placeNonGroupingChecker" => 4)
    ),

  

    4 => array(
        "name" => "nextPlayer",
        "type" => "game",
        "action" => "stNextPlayer",
        "updateGameProgression" => true, 
        
        "transitions" => array( "nextTurn" => 2, "endGame" => 99 )
    ),

   
   
    // Final state.
    // Please do not modify (and do not overload action/args methods).
    99 => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);



