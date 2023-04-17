<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Herd implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * states.inc.php
 *
 * Herd game states description
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
        "name" => "placeStoneFirstTurn",
        "description" => clienttranslate('${actplayer} must place a stone.'),
        "descriptionmyturn" => clienttranslate('${you} must place a stone.'),
        "type" => "activeplayer",
        "args" => "argPlaceStone",    // 'selectableCells'
        "possibleactions" => array( "placeStone" ),                                
        "transitions" => array( "placeStone" => 5, "placeLastStone" => 6, "zombiePass" => 6 )
    ),

    4 => array(
        "name" => "removeStone",
        "description" => clienttranslate('${actplayer} must remove an enemy stone.'),
        "descriptionmyturn" => clienttranslate('${you} must remove an enemy stone.'),
        "type" => "activeplayer",
        "args" => "argRemoveStone",    // 'removableStones'
        "possibleactions" => array( "removeStone" ),                                
        "updateGameProgression" => true,        
        "transitions" => array( "removeStone" => 4, "removeLastStone" => 5, "removeLastStone_noPlacementsAvailable" => 6, "zombiePass" => 6 )
    ),

    5 => array(
        "name" => "placeStone",
        "description" => clienttranslate('${actplayer} must place a stone.'),
        "descriptionmyturn" => clienttranslate('${you} must place a stone.'),
        "type" => "activeplayer",
        "args" => "argplaceStone",    // 'selectableCells'
        "possibleactions" => array( "placeStone" ),                                
        "updateGameProgression" => true,        
        "transitions" => array( "placeStone" => 5, "placeLastStone" => 6, "zombiePass" => 6, "endGame" => 99 )
    ),

    6 => array(
        "name" => "nextPlayer",
        "type" => "game",
        "action" => "stNextPlayer",
        "updateGameProgression" => true,        
        "transitions" => array( "nextTurn" => 4, "nextTurn_noEnemyStonesToRemove" => 5, "cantPlay" => 6 )
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


