<?php

/*
    From this file, you can edit the various meta-information of your game.

    Once you modified the file, don't forget to click on "Reload game informations" from the Control Panel in order in can be taken into account.

    See documentation about this file here:
    http://en.doc.boardgamearena.com/Game_meta-information:_gameinfos.inc.php

*/

$gameinfos = array( 

// Name of the game in English (will serve as the basis for translation) 
'game_name' => "Coins",

// Game designer (or game designers, separated by commas)
'designer' => 'Mark Steere',       

// Game artist (or game artists, separated by commas)
'artist' => 'Tenuun',         

// Year of FIRST publication of this game. Can be negative.
'year' => 2020,                 

// Game publisher (use empty string if there is no publisher)
'publisher' => 'Mark Steere Games',                     

// Url of game publisher website
'publisher_website' => 'http://www.marksteeregames.com/index.html',   

// Board Game Geek ID of the publisher
'publisher_bgg_id' => 7854,

// Board game geek ID of the game
'bgg_id' => 373385,


// Players configuration that can be played (ex: 2 to 4 players)
'players' => array( 3 ),    

// Suggest players to play with this number of players. Must be null if there is no such advice, or if there is only one possible player configuration.
'suggest_player_number' => null,

// Discourage players to play with these numbers of players. Must be null if there is no such advice.
'not_recommend_player_number' => null,



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
'player_colors' => array( "ff0000", "ffff00", "0000ff" ),

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
    'min' => 750,

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
    totranslate("INTRODUCTION Coins is a trick taking card game for three players. A Spanish deck of 48 cards is 
                 used."),
    totranslate("SUITS Cups, Coins, Swords, Clubs."),
    totranslate("SUIT RELATIVE VALUES Cups contain Coins, Coins buy Swords, Swords chop Clubs, Clubs smash Cups."),
    totranslate("RANKS (high to low)  Ace, King (Rey), Knight (Caballo), Jack (Sota), 9 to 2."),
    totranslate("TRICK CARD VALUES There are three types of tricks, as follows."),
    totranslate("1. The three cards of the trick are of three different suits. Suit A beats suit B, and suit B beats 
                 suit C (see SUIT RELATIVE VALUES above). Suit A wins the trick."),
    totranslate("2. Two cards of the trick are of the same suit and the third is of a different suit. The higher 
                 ranked, same-suit card wins the trick (see RANKS above). "),
    totranslate("3. The three cards of the trick are all of the same suit. The highest ranked card wins the trick."),
    totranslate("OBJECT OF THE GAME Players initally agree on a target score such as 10 or 15 or 20 points. Each
                 card of the Coins suit is worth one point. As soon as one of the players reaches the target score, 
                 he wins the game."),
    totranslate("PLAY The dealer of the first hand of the game is the oldest player. In subsequent hands, the player 
                 to the right of the winner of the last trick deals. The player to the dealers right shuffles the 
                 cards. The player to the dealers left cuts the deck."),
    totranslate("The entire deck is dealt face down, one card at a time, clockwise, beginning on the dealers left. 
                 After all the cards have been dealt, the player to the dealers left leads the trick by placing any 
                 card from his hand face up on the table. Then the player to that players left likewise plays any 
                 one of his cards. Finally, the third player (the dealer) plays any card. "),
    totranslate("The player with the highest valued card in the trick (see TRICK CARD VALUES above) wins the trick.
                 The trick winner adds the number of Coins in the trick to his total, collects the three cards of 
                 the trick, and places them face down on his stack of tricks. The winner of the trick leads the 
                 next trick."),
    totranslate("The deck is a Mongolian interpretation of Spanish cards.")
//    ...
),

// Games tags (categories)
//  You can attribute a maximum of ten "tags" for your game.
//  Each tag has a specific ID (ex: 22 for the category "Prototype", 101 for the tag "Science-fiction theme game")
//  Please see the "Game meta information" entry in the BGA Studio documentation for a full list of available tags:
//  https://en.doc.boardgamearena.com/Game_meta-information:_gameinfos.inc.php#Tags
//  IMPORTANT: this list should be ORDERED, with the most important tag first.
//  NOTE: tags are only read during the first deploy from the file gameinfos.inc.php; afterwards, BGA is responsible for setting tags for a game.

'tags' => array( 3, 11, 28, 29, 1, 200, 205, 212, 220 ),


//////// BGA SANDBOX ONLY PARAMETERS (DO NOT MODIFY)

// simple : A plays, B plays, C plays, A plays, B plays, ...
// circuit : A plays and choose the next player C, C plays and choose the next player D, ...
// complex : A+B+C plays and says that the next player is A+B
'is_sandbox' => false,
'turnControl' => 'simple'

////////
);
