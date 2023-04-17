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
 * gameoptions.inc.php
 *
 * Cephalopod game options description
 * 
 * In this file, you can define your game options (= game variants).
 *   
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in cephalopod.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

$game_options = array(

         101 => array(
                'name' => totranslate('Board size'),    
                'values' => array(

                            // Size 3
                            3 => array( 'name' => totranslate('Size 3 - small'), 'size' => 3, 'tmdisplay' => totranslate('Small board') ),
                            
                            // Size 5
                            5 => array( 'name' => totranslate('Size 5 - large'), 'size' => 5, 'tmdisplay' => totranslate('Large board') )
 
                            ),

                'default' => 5 

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


