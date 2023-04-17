<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Silo implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * gameoptions.inc.php
 *
 * Silo game options description
 * 
 * In this file, you can define your game options (= game variants).
 *   
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in silo.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

 $game_options = array(
        101 => array(
                'name' => totranslate('Board size'),    
                'values' => array(

                            // 6x6
                            6 => array( 'name' => totranslate('Standard: 6x3'), 'size' => 6, 'tmdisplay' => totranslate('Standard: 6x3') ),
                            
                            // Optimal one according to Nash
                            8 => array( 'name' => totranslate('Big board: 8x3'), 'size' => 8, 'tmdisplay' => totranslate('Big board: 8x3') )
                        )
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
    )
);




