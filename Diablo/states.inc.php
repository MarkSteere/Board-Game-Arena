<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Diablo implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * states.inc.php
 *
 * Diablo game states description
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
        "name" => "selectOriginFirstTurn",
        "description" => clienttranslate('${actplayer} must select a checker to move.'),
        "descriptionmyturn" => clienttranslate('${you} must select a checker to move.'),
        "type" => "activeplayer",
        "args" => "argSelectOriginFirstTurn",  // 'selectableOriginsFirstTurn'

        "possibleactions" => array( "selectOriginFirstTurn" ),

        "transitions" => array( "selectOriginFirstTurn" => 3, "zombiePass" => 10, "endGame" => 99 )
    ),


    3 => array(
        "name" => "selectDestinationFirstTurn",
        "description" => clienttranslate('${actplayer} must select a destination.'),
        "descriptionmyturn" => clienttranslate('${you} must select a destination.'),
        "type" => "activeplayer",
        "args" => "argSelectDestinationFirstTurn",  // 'selectedOrigin_selectableDestinationsFirstTurn'     combined

        "possibleactions" => array( "selectDestinationFirstTurn", "unselectOrigin" ),

        "transitions" => array( "selectDestinationFirstTurn" => 10, "unselectOrigin" => 2, "zombiePass" => 10, "endGame" => 99 )
    ),


    4 => array(
        "name" => "selectOriginMoveA",
        "description" => clienttranslate('${actplayer} must select a stack for move A.'),
        "descriptionmyturn" => clienttranslate('${you} must select a stack for move A.'),
        "type" => "activeplayer",
        "args" => "argSelectOriginMoveA",  // 'selectableOriginsMoveA'

        "possibleactions" => array( "selectOriginMoveA" ),

        "transitions" => array( "selectOriginMoveA" => 5, "zombiePass" => 10, "endGame" => 99 )
    ),


    5 => array(
        "name" => "selectDestinationMoveA",
        "description" => clienttranslate('${actplayer} must select a destination for move A.'),
        "descriptionmyturn" => clienttranslate('${you} must select a destination for move A.'),
        "type" => "activeplayer",
        "args" => "argSelectDestinationMoveA",  // 'selectedOrigin_selectableDestinationsMoveA'     combined

        "possibleactions" => array( "selectDestinationMoveA", "unselectOrigin" ),

        "transitions" => array( "selectDestinationMoveA" => 6, "selectDestinationMoveA_noMovesB" => 9, "unselectOrigin" => 4, "zombiePass" => 10, "endGame" => 99 ) 
    ),


    6 => array(
        "name" => "selectOriginMoveB",
        "description" => clienttranslate('${actplayer} must select a stack for move B.'),
        "descriptionmyturn" => clienttranslate('${you} must select a stack for move B.'),
        "type" => "activeplayer",
        "args" => "argSelectOriginMoveB",  // 'selectableOriginsMoveB'

        "possibleactions" => array( "selectOriginMoveB" ),

        "transitions" => array( "selectOriginMoveB" => 7, "zombiePass" => 10, "endGame" => 99 )
    ),


    7 => array(
        "name" => "selectDestinationMoveB",
        "description" => clienttranslate('${actplayer} must select a destination for move B.'),
        "descriptionmyturn" => clienttranslate('${you} must select a destination for move B.'),
        "type" => "activeplayer",
        "args" => "argSelectDestinationMoveB",  // 'selectedOrigin_selectableDestinationsMoveB'     combined

        "possibleactions" => array( "selectDestinationMoveB", "unselectOrigin" ),

        "transitions" => array( "selectDestinationMoveB" => 10, "unselectOrigin" => 6, "zombiePass" => 10, "endGame" => 99 )
    ),

    
    8 => array(
        "name" => "removeCheckerMoveA",
        "description" => clienttranslate('${actplayer} must remove a checker (move A).'),
        "descriptionmyturn" => clienttranslate('${you} must remove a checker (move A).'),
        "type" => "activeplayer",
        "args" => "argRemoveCheckerMoveA",  // 'removableCheckersMoveA'     

        "possibleactions" => array( "removeCheckerMoveA" ),

        "transitions" => array( "removeCheckerMoveA" => 9, "zombiePass" => 10, "endGame" => 99 )
    ),


    9 => array(
        "name" => "removeCheckerMoveB",
        "description" => clienttranslate('${actplayer} must remove a checker (move B).'),
        "descriptionmyturn" => clienttranslate('${you} must remove a checker (move B).'),
        "type" => "activeplayer",
        "args" => "argRemoveCheckerMoveB",  // 'removableCheckersMoveB'     

        "possibleactions" => array( "removeCheckerMoveB" ),

        "transitions" => array( "removeCheckerMoveB" => 10, "zombiePass" => 10, "endGame" => 99 )  
    ),
    


    10 => array(
        "name" => "nextPlayer",
        "type" => "game",
        "action" => "stNextPlayer",
        "updateGameProgression" => true,        
        "transitions" => array( "nextTurn" => 4, "nextTurn_noMovesA" => 8 )
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



