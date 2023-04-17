<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Coins implementation : © <Mark Steere> <marksteere@gmail.com>
 * 
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * material.inc.php
 *
 * Coins game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *   
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */



$this->suits = array(
    1 => array( 'name' => clienttranslate('Cups'),
                'nametr' => self::_('Cups') ),
    2 => array( 'name' => clienttranslate('Coins'),
                'nametr' => self::_('Coins') ),
    3 => array( 'name' => clienttranslate('Swords'),
                'nametr' => self::_('Swords') ),
    4 => array( 'name' => clienttranslate('Clubs'),
                'nametr' => self::_('Clubs') )
);

$this->values_label = array(
    2 =>'2',
    3 => '3',
    4 => '4',
    5 => '5',
    6 => '6',
    7 => '7',
    8 => '8',
    9 => '9',
    10 => clienttranslate('Knave'),
    11 => clienttranslate('Knight'),
    12 => clienttranslate('King'),
    13 => clienttranslate('Ace')
);

