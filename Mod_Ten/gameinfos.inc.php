<?php

/*
    From this file, you can edit the various meta-information of your game.

    Once you modified the file, don't forget to click on "Reload game informations" from the Control Panel in order in can be taken into account.

    See documentation about this file here:
    http://en.doc.boardgamearena.com/Game_meta-information:_gameinfos.inc.php

*/

$gameinfos = array( 

// Name of the game in English (will serve as the basis for translation) 
'game_name' => "Mod Ten",

// Game designer (or game designers, separated by commas)
'designer' => 'Mark Steere',       

// Game artist (or game artists, separated by commas)
'artist' => 'Mark Steere',         

// Year of FIRST publication of this game. Can be negative.
'year' => 2022,                 

// Game publisher (use empty string if there is no publisher)
'publisher' => 'Mark Steere Games',                     

// Url of game publisher website
'publisher_website' => 'http://www.mypublishingcompany.com/',   

// Board Game Geek ID of the publisher
'publisher_bgg_id' => 1234,

// Board game geek ID of the game
'bgg_id' => 0,


// Players configuration that can be played (ex: 2 to 4 players)
'players' => array( 2,3,4,5,6 ),    

// Suggest players to play with this number of players. Must be null if there is no such advice, or if there is only one possible player configuration.
// NB: the automatic lobby will try first the lowest number of players if this is not specified. So you _have to_ specify this parameter if the lowest player number is not compatible with the default options.
'suggest_player_number' => 2,

// Discourage players to play with these numbers of players. Must be null if there is no such advice.
'not_recommend_player_number' => null,
// 'not_recommend_player_number' => array( 2, 3 ),      // <= example: this is not recommended to play this game with 2 or 3 players


// Estimated game duration, in minutes (used only for the launch, afterward the real duration is computed)
'estimated_duration' => 15,           

// Time in second add to a player when "giveExtraTime" is called (speed profile = fast)
'fast_additional_time' => 20,           

// Time in second add to a player when "giveExtraTime" is called (speed profile = medium)
'medium_additional_time' => 30,           

// Time in second add to a player when "giveExtraTime" is called (speed profile = slow)
'slow_additional_time' => 40,           

// If you are using a tie breaker in your game (using "player_score_aux"), you must describe here
// the formula used to compute "player_score_aux". This description will be used as a tooltip to explain
// the tie breaker to the players.
// Note: if you are NOT using any tie breaker, leave the empty string.
//
// Example: 'tie_breaker_description' => totranslate( "Number of remaining cards in hand" ),
'tie_breaker_description' => "",

// If in the game, all losers are equal (no score to rank them or explicit in the rules that losers are not ranked between them), set this to true 
// The game end result will display "Winner" for the 1st player and "Loser" for all other players
'losers_not_ranked' => false,

// Allow to rank solo games for games where it's the only available mode (ex: Thermopyles). Should be left to false for games where solo mode exists in addition to multiple players mode.
'solo_mode_ranked' => false,

// Game is "beta". A game MUST set is_beta=1 when published on BGA for the first time, and must remains like this until all bugs are fixed.
'is_beta' => 1,                     

// Is this game cooperative (all players wins together or loose together)
'is_coop' => 0,

// Language dependency. If false or not set, there is no language dependency. If true, all players at the table must speak the same language.
// If an array of shortcode languages such as array( 1 => 'en', 2 => 'fr', 3 => 'it' ) then all players at the table must speak the same language, and this language must be one of the listed languages.
// NB: the default will be the first language in this list spoken by the player, so you should list them by popularity/preference.
'language_dependency' => false,

// Complexity of the game, from 0 (extremely simple) to 5 (extremely complex)
'complexity' => 3,    

// Luck of the game, from 0 (absolutely no luck in this game) to 5 (totally luck driven)
'luck' => 3,    

// Strategy of the game, from 0 (no strategy can be setup) to 5 (totally based on strategy)
'strategy' => 3,    

// Diplomacy of the game, from 0 (no interaction in this game) to 5 (totally based on interaction and discussion between players)
'diplomacy' => 0,    

// Colors attributed to players
'player_colors' => array( "ff0000", "ffff00", "0000ff", "000000", "00ff00", "ff00ff" ),

// Favorite colors support : if set to "true", support attribution of favorite colors based on player's preferences (see reattributeColorsBasedOnPreferences PHP method)
// NB: this parameter is used only to flag games supporting this feature; you must use (or not use) reattributeColorsBasedOnPreferences PHP method to actually enable or disable the feature.
'favorite_colors_support' => false,

// When doing a rematch, the player order is swapped using a "rotation" so the starting player is not the same
// If you want to disable this, set this to true
'disable_player_order_swap_on_rematch' => false,

// Game interface width range (pixels)
// Note: game interface = space on the left side, without the column on the right
'game_interface_width' => array(

    // Minimum width
    //  default: 740
    //  maximum possible value: 740 (ie: your game interface should fit with a 740px width (correspond to a 1024px screen)
    //  minimum possible value: 320 (the lowest value you specify, the better the display is on mobile)
    'min' => 740,

    // Maximum width
    //  default: null (ie: no limit, the game interface is as big as the player's screen allows it).
    //  maximum possible value: unlimited
    //  minimum possible value: 740
    'max' => null
),

// Game presentation
// Short game presentation text that will appear on the game description page, structured as an array of paragraphs.
// Each paragraph must be wrapped with totranslate() for translation and should not contain html (plain text without formatting).
// A good length for this text is between 100 and 150 words (about 6 to 9 lines on a standard display)
'presentation' => array(
    totranslate("INTRODUCTION Mod Ten is a shedding card game for 2, 3, 4, 5, or 6 players. Mark Steere 
                 designed Mod Ten in November, 2022."),
    totranslate("DECK A stripped deck is used, either single or double."),
    totranslate("Single deck - Remove 5s, 10s, and face cards."),
    totranslate("For 3, 5, or 6 players, additionally remove:"),
    totranslate("The Ace and 9 of Spades."),
    totranslate("Double deck - Remove 10s and face cards."),
    totranslate("For 3, 5, or 6 players, additionally remove:"),
    totranslate("Both Aces and 9s of Spades,"),
    totranslate("Both Aces and 9s of Clubs,"),
    totranslate("Both Aces and 9s of Diamonds."),
    totranslate("RANKS High to low: 9, 8, 7, 6, 5, 4, 3, 2, Ace. Suits are irrelevant."),
    totranslate("DEAL The dealer of the first hand of the game is selected at random. In subsequent hands, 
                 the player to the right of the the last player to shed in a card in the previous hand deals. 
                 The player to the dealers right shuffles the cards. The player to the dealers left cuts 
                 the deck. The entire deck is dealt face down, one card at a time, clockwise, beginning on 
                 the dealers left. In a two-player game, cards may optionally be dealt face up."),
    totranslate("PLAY The player to the dealers left leads by placing any card from his hand face up on the 
                 table. Continuing clockwise, players must follow suit if possible. If not, their turn is 
                 skipped. If your played card brings the sum of table cards to a multiple of 10, you collect 
                 all the table cards, place them face down on the discard pile, and lead another card of any 
                 suit."),
    totranslate("OBJECT OF THE GAME The first player to play his last card wins the game."),
    totranslate("UFM provided invaluable assistance in development.")
//    ...
),

// Games tags (categories)
//  You can attribute a maximum of ten "tags" for your game.
//  Each tag has a specific ID (ex: 22 for the category "Prototype", 101 for the tag "Science-fiction theme game")
//  Please see the "Game meta information" entry in the BGA Studio documentation for a full list of available tags:
//  https://en.doc.boardgamearena.com/Game_meta-information:_gameinfos.inc.php#Tags
//  IMPORTANT: this list should be ORDERED, with the most important tag first.
//  NOTE: tags are only read during the first deploy from the file gameinfos.inc.php; afterwards, BGA is responsible for setting tags for a game.

'tags' => array( 3, 11, 28, 29, 1, 200, 205, 212 ),


//////// BGA SANDBOX ONLY PARAMETERS (DO NOT MODIFY)

// simple : A plays, B plays, C plays, A plays, B plays, ...
// circuit : A plays and choose the next player C, C plays and choose the next player D, ...
// complex : A+B+C plays and says that the next player is A+B
'is_sandbox' => false,
'turnControl' => 'simple'

////////
);
