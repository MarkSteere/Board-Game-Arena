<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Impasse implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * states.inc.php
 *
 * Impasse game states description
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
        "name" => "selectOrigin",
        "description" => clienttranslate('${actplayer} must select a stack of one or two checkers.'),
        "descriptionmyturn" => clienttranslate('${you} must select a stack of one or two checkers.'),
        "type" => "activeplayer",
        "args" => "argSelectOrigin",  // 'selectableOrigins'

        "possibleactions" => array( "selectOrigin" ),

        "transitions" => array( "selectOrigin" => 3, "bearOff" => 6, "zombiePass" => 6, "endGame" => 99 )
    ),


    3 => array(
        "name" => "selectDestination",
        "description" => clienttranslate('${actplayer} must select a destination.'),
        "descriptionmyturn" => clienttranslate('${you} must select a destination.'),
        "type" => "activeplayer",
        "args" => "argSelectDestination",  // 'selectableDestinations'

        "possibleactions" => array( "selectDestination", "unselectOrigin" ),

        "transitions" => array( "selectDestination" => 6,  "selectCrownableDestination" => 4, "selectCrownableDestNoCrownsAvailable" => 6, "unselectOrigin" => 2, "zombiePass" => 6, "endGame" => 99 )
    ),


    4 => array(
        "name" => "selectCrown",
        "description" => clienttranslate('${actplayer} must select a crown.'),
        "descriptionmyturn" => clienttranslate('${you} must select a crown.'),
        "type" => "activeplayer",
        "args" => "argSelectCrown",  // 'selectableCrowns'

        "possibleactions" => array( "selectCrown" ),

        "transitions" => array( "selectCrown" => 6, "zombiePass" => 6, "endGame" => 99 )
    ),


    5 => array(
        "name" => "removeImpasseChecker",
        "description" => clienttranslate('Impasse. ${actplayer} must remove a checker.'),
        "descriptionmyturn" => clienttranslate('Impasse. ${you} must remove a checker.'),
        "type" => "activeplayer",
        "args" => "argRemoveImpasseChecker",  // 'removableImpasseCheckers'

        "possibleactions" => array( "removeImpasseChecker" ),

        "transitions" => array( "removeImpasseChecker" => 6, "removeImpasseChecker_createUncrowned" => 4, "removeImpasseChecker_createUncrowned_noCrownsAvailable" => 6, "zombiePass" => 6, "endGame" => 99 )
    ),


    6 => array(
        "name" => "nextPlayer",
        "type" => "game",
        "action" => "stNextPlayer",
        "updateGameProgression" => true, 
        
        "transitions" => array( "movesAvailable" => 2, "noMovesAvailable" => 5, "endGame" => 99  )
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



