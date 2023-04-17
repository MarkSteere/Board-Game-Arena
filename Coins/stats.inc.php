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
 * stats.inc.php
 *
 * Coins game statistics description
 *
 */


$stats_type = array(

    // Statistics global to table
    "table" => array(
        "handNbr" => array(   "id"=> 10,
                                "name" => totranslate("Number of hands"), 
                                "type" => "int" ),


    ),
    
    // Statistics existing for each player
    "player" => array(
    
        "getCoins" => array(   "id"=> 11,
                                "name" => totranslate("Total Coins cards collected"), 
                                "type" => "int" ),
        "getNoPointCards" => array(   "id"=> 13,
                                "name" => totranslate("Get no cards with ponts during a hand"), 
                                "type" => "int" )  
    )
);



