<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Diablo implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * gameoptions.inc.php
 *
 * Diablo game options description
 * 
 * In this file, you can define your game options (= game variants).
 *   
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in diablo.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

$game_options = array(

    101 => array(
        'name' => totranslate('Board size'),    
        'values' => array(

                   // Size 6
                   6 => array( 'name' => totranslate( '6x6 board' ), 'size' => 6, 'tmdisplay' => totranslate('6x6 board') ),

                   // Size 8
                   8 => array( 'name' => totranslate( '8x8 board' ), 'size' => 8, 'tmdisplay' => totranslate('8x8 board') ),

                   // Size 10
                   10 => array( 'name' => totranslate( '10x10 board' ), 'size' => 10, 'tmdisplay' => totranslate('10x10 board') )

                ),

        'default' => 8
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
