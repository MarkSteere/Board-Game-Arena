<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * ModTen implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * gameoptions.inc.php
 *
 * ModTen game options description
 * 
 * In this file, you can define your game options (= game variants).
 *   
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in modten.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

$game_options = array(

	/*
		100 => array(
				'name' => totranslate('Game options'),
				'values' => array(
						1 => array( 'name' => totranslate( '2 players, single deck' ) ),
						2 => array( 'name' => totranslate( '3 players, single deck' ) ),
						3 => array( 'name' => totranslate( '4 players, single deck' ) ),
						4 => array( 'name' => totranslate( '4 players, double deck' ) ),
						5 => array( 'name' => totranslate( '5 players, double deck' ) ),
						6 => array( 'name' => totranslate( '6 players, double deck' ) )
				),
				'default' => 1
		)
	*/
);


