<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Dodo implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * gameoptions.inc.php
 *
 * Dodo game options description
 * 
 * In this file, you can define your game options (= game variants).
 *   
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in dodo.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

$game_options = array(

    // note: game variant ID should start at 100 (ie: 100, 101, 102, ...). The maximum is 199.
    100 => array(
                'name' => totranslate('Normal or Misere Object of the Game'),    
                'values' => array(

                            1 => array( 'name' => totranslate('NORMAL GOAL - If you cant move, you win.'), 'tmdisplay' => totranslate('NORMAL GOAL - If you cant move, you win.') ),

                            2 => array( 'name' => totranslate('MISERE GOAL - If you cant move, you lose.'), 'tmdisplay' => totranslate('MISERE GOAL - If you cant move, you lose.') )

                        ),

                'default' => 1
            )

);

