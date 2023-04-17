<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Cephalopod implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * states.inc.php
 *
 * Cephalopod game states description
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
    

    //
    //  selectSquare
    //  ############
    //
    //      selectDeterminingSquare 
    //          Select a square that completely determines the move and finishes the turn 
    //          E.g., a square next to nothing or next to only sixes would result in the placement of a 1.
    //          E.g., a square next to two, three, or four dice, none of which can be combined, or exactly two of which can be combined.
    //
    //      selectNonDeterminingSquare 
    //          E.g., a square next to three or more dice which can be combined in different ways.
    //
    2 => array(
        "name" => "selectSquare",
        "description" => clienttranslate('${actplayer} must select a square.'),
        "descriptionmyturn" => clienttranslate('${you} must select a square.'),
        "type" => "activeplayer",
        "args" => "argSelectSquare",  // 'selectableSquares'
        "possibleactions" => array( "selectSquare" ),
        "transitions" => array( "selectDeterminingSquare" => 6, "selectNonDeterminingSquare" => 3 ) // Determining square completely determines the move.  
    ),                                                                                              // E.g., a square next to nothing or next to only sixes.


    //
    //  selectDie
    //  #########
    //
    //      selectDeterminingDie 
    //          Select a die that completely determines the dice to be captured and finishes the turn.
    //          E.g., there are exactly 2 dice next to the square.
    //          E.g., choosing 3 from (2,3,4) forces the (2,3) combination.  Can't be (3,4).
    //
    //      selectFirstDie 
    //          Select a non-determining first of three or four possible dice next to the square. One non-determining die is not enough to support a final decision.
    //
    //      selectPossibleFinalDie 
    //          Select the non-determining second of three or four possible dice adjacent to the square or the non-determining third of four possible dice adjacent to the square.  
    //          Not a determining die selection, but could be the final selected die if the player wants it to be.
    //
    //      unselectAll 
    //          If player clicks any square not containing a selectable die, the selected square and all selectable and selected dice are unselected.
    //
    3 => array(
        "name" => "selectDie",
        "description" => clienttranslate('${actplayer} must select a die.'),
        "descriptionmyturn" => clienttranslate('${you} must select a die.'),
        "type" => "activeplayer",
        "args" => "argSelectDie",  // 'tripleSetOfArgs' - INCLUDES 3 ARRAYS: SELECTED SQUARE, SELECTED DICE, AND SELECTABLE DICE
        "possibleactions" => array( "selectDie", "unselectAll" ), // Unselect square and all dice
        "transitions" => array( "selectDeterminingDie" => 6 , "selectFirstDie" => 3, "selectPossibleFinalDie" => 4, "unselectAll" => 2 )
    ),


    //
    //  finalizeThisCombination
    //  #######################
    //
    //      finalizeThisCombination 
    //          Click the finalize button and select the current combination of selected dice for capture.
    //
    //      selectDeterminingDie 
    //          Select a die that completely determines the dice to be captured and finishes the turn.
    //
    //      selectPossibleFinalDie 
    //          Select the non-determining third of four possible dice adjacent to the square.  
    //          Not determining, but could be the final selected die if player wants it to be.
    //
    //      unselectAll 
    //          If player clicks anywhere but on a selectable die, the selected square and all selectable and selected dice are unselected
    //
    4 => array(
        "name" => "finalizeThisCombination",
        "description" => clienttranslate('${actplayer} must finalize their turn or select a die.'),
        "descriptionmyturn" => clienttranslate('${you} must finalize your turn or select a die.'),
        "type" => "activeplayer",
        "args" => "argSelectDie",  // 'tripleSetOfArgs' - INCLUDES 3 ARRAYS: SELECTED SQUARE, SELECTED DICE, AND SELECTABLE DICE
        "possibleactions" => array( "selectDie", "finalize", "unselectAll" ),
        "transitions" => array( "finalizeThisCombination" => 6, "selectDeterminingDie" => 6, "selectPossibleFinalDie" => 4, "unselectAll" => 2 )
    ),


    //
    //  nextPlayer
    //  ##########
    //
    //      The usual nextPlayer state 
    //
    6 => array(
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


