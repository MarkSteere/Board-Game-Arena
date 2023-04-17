<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Redstone implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * gameoptions.inc.php
 *
 * Redstone game options description
 * 
 * In this file, you can define your game options (= game variants).
 *   
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in redstone.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

$game_options = array(

    101 => array(
        'name' => totranslate('Board size'),    
        'values' => array(

                   // Size 9
                   9 => array( 'name' => totranslate( '9x9 goban' ), 'size' => 9, 'tmdisplay' => totranslate('9x9 goban') ),

                   // Size 11
                   11 => array( 'name' => totranslate( '11x11 goban' ), 'size' => 11, 'tmdisplay' => totranslate('11x11 goban') ),

                   // Size 13
                   13 => array( 'name' => totranslate( '13x13 goban' ), 'size' => 13, 'tmdisplay' => totranslate('13x13 goban') ),

                   // Size 15
                   15 => array( 'name' => totranslate( '15x15 goban' ), 'size' => 15, 'tmdisplay' => totranslate('15x15 goban') ),

                   // Size 19
                   19 => array( 'name' => totranslate( '19x19 goban' ), 'size' => 19, 'tmdisplay' => totranslate('19x19 goban') )
                ),

        'default' => 11
    )
);



$game_preferences = array(
    100 => array(
            'name' => totranslate( 'Show coordinates' ),
            'needReload' => true, // after user changes this preference game interface would auto-reload
            'values' => array(
                    1 => array( 'name' => totranslate( 'Yes' ), 'showCoords' => 'show_coords_yes' ),
                    2 => array( 'name' => totranslate( 'No' ), 'showCoords' => 'show_coords_no' )
            ),

            'default' => 2 
    ),

    102 => array(
            'name' => totranslate( 'Highlight legal placements' ),
            'needReload' => true, // after user changes this preference game interface would auto-reload
            'values' => array(
                    1 => array( 'name' => totranslate( 'Yes' ), 'highlightLegalPlacements' => 'highlight_legal_placements_yes' ),
                    2 => array( 'name' => totranslate( 'No' ), 'highlightLegalPlacements' => 'highlight_legal_placements_no' )
            ),

            'default' => 2 
    ),

    103 => array(
            'name' => totranslate( 'Last move indicator' ),
            'needReload' => true, // after user changes this preference game interface would auto-reload
            'values' => array(
                    1 => array( 'name' => totranslate( 'On' ), 'lastMoveIndicator' => 'last_move_indicator_on' ),
                    2 => array( 'name' => totranslate( 'Off' ), 'lastMoveIndicator' => 'last_move_indicator_off' )
            ),

            'default' => 1 
    )
);

