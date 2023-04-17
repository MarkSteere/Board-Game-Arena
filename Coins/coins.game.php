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
  * coins.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class Coins extends Table
{
	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        
        self::initGameStateLabels( array( 
                         //"trickSuit" => 11,
                         "gameLength" => 100 
                      
        ) );        

        $this->cards = self::getNew( "module.common.deck" );
        $this->cards->init( "card" );
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "coins";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {    
        $sql = "DELETE FROM player WHERE 1 ";
        self::DbQuery( $sql ); 
 
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
 
        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.

        $start_points = 0;

        $sql = "INSERT INTO player (player_id, player_score, player_color, player_canal, player_name, player_avatar) VALUES ";

        $values = array();

        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$start_points','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
        }
        $sql .= implode( $values, ',' );

        self::DbQuery( $sql );

        self::reloadPlayersBasicInfos();


        // Set current trick color to zero (= no trick color)
        //self::setGameStateInitialValue( 'trickSuit', 0 );

        self::initStat( "table", "handNbr", 0 );

        self::initStat( "player", "getCoins", 0 );
        self::initStat( "player", "getNoPointCards", 0 );



        // Create cards
        $cards = array();
        foreach( $this->suits  as  $suit_id => $suit ) // cups, coins, swords, clubs
        {
            for( $value=2; $value<=13; $value++ )   //  2, 3, 4, ... , 9, Knave, King, Ace
            {
                $cards[] = array( 'type' => $suit_id, 'type_arg' => $value, 'nbr' => 1);
            }
        }

        $this->cards->createCards( $cards, 'deck' );
       

        //
        //
        //  STARTING PLAYER OF FIRST HAND DETERMINED RANDOMLY IN stNewHand
        //
        // Activate first player (which is in general a good idea :) )
        //$this->activeNextPlayer();
        //
        //


        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array( 'players' => array() );

        $player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!

        // Get information about players
        // Note: you can retrieve some extra field you add for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score ";
        $sql .= "FROM player ";
        $sql .= "WHERE 1 ";
        $dbres = self::DbQuery( $sql );
        while( $player = mysql_fetch_assoc( $dbres ) )
        {
            $result['players'][ $player['id'] ] = $player;
        }
        
  
        // Cards in player hand      
        $result['hand'] = $this->cards->getCardsInLocation( 'hand', $player_id );
  
        // Cards played on the table
        $result['cardsontable'] = $this->cards->getCardsInLocation( 'cardsontable' );
  
        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        $maximumScore = self::getUniqueValueFromDb( "SELECT MAX( player_score ) FROM player" );

        $winning_score = self::getGameStateValue ("gameLength");

        
        return min (    100,  100 - ( $winning_score - $maximumScore ) / $winning_score * 100   );   
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */

    // Return players => direction (W/S/E) from the point of view
    //  of current player (current player must be on south)
    function getPlayersToDirection()
    {
        $result = array();
    
        $players = self::loadPlayersBasicInfos();
        $nextPlayer = self::createNextPlayerTable( array_keys( $players ) );

        $current_player = self::getCurrentPlayerId();
        
        $directions = array( 'S', 'W', 'E' );
        
        if( ! isset( $nextPlayer[ $current_player ] ) )
        {
            // Spectator mode: take any player for south
            $player_id = $nextPlayer[0];
            $result[ $player_id ] = array_shift( $directions );
        }
        else
        {
            // Normal mode: current player is on south
            $player_id = $current_player;
            $result[ $player_id ] = array_shift( $directions );
        }
        
        while( count( $directions ) > 0 )
        {
            $player_id = $nextPlayer[ $player_id ];
            $result[ $player_id ] = array_shift( $directions );
        }
        return $result;
    }



//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in coins.action.php)
    */

    // Play a card from player hand
    function playCard( $card_id )
    {
        self::checkAction( "playCard" );
        
        $player_id = self::getActivePlayerId();
        
        // Get all cards in player hand
        // (note: we must get ALL cards in player's hand in order to check if the card played is correct)
        
        $playerhands = $this->cards->getCardsInLocation( 'hand', $player_id );

        //$bFirstCard = ( count( $playerhands ) == 12 );
                
        //$currentTrickSuit = self::getGameStateValue( 'trickSuit' ) ;
                
        // Check that the card is in this hand
        $bIsInHand = false;
        $currentCard = null;
        //$bAtLeastOneCardOfCurrentTrickSuit = false;
        //$bAtLeastOneCardWithoutPoints = false;
        //$bAtLeastOneCardNotCoins = false;
        foreach( $playerhands as $card )
        {
            if( $card['id'] == $card_id )
            {
                $bIsInHand = true;
                $currentCard = $card;
            }            
        }
        if( ! $bIsInHand )
            throw new feException( "This card is not in your hand" );
            


        // Checks are done! now we can play our card
        $this->cards->moveCard( $card_id, 'cardsontable', $player_id );
        

        // And notify
        self::notifyAllPlayers( 'playCard', clienttranslate('${player_name} ${value_displayed} ${suit_displayed}'), array(
            'i18n' => array( 'color_displayed', 'value_displayed' ),
            'card_id' => $card_id,
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'value' => $currentCard['type_arg'],
            'value_displayed' => $this->values_label[ $currentCard['type_arg'] ],
            'suit' => $currentCard['type'],
            'suit_displayed' => $this->suits[ $currentCard['type'] ]['name']
        ) );
        
        // Next player
        $this->gamestate->nextState( 'playCard' );
    }
    

    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    /*
    
    Example for game state "MyGameState":
    
    function argMyGameState()
    {
        // Get some values from the current game situation in database...
    
        // return values:
        return array(
            'variable1' => $value1,
            'variable2' => $value2,
            ...
        );
    }    
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
    
    function stNewHand()
    {
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'NEW HAND' ), 
        array(
        ) );

        self::incStat( 1, "handNbr" );
    
        // Take back all cards (from any location => null) to deck
        $this->cards->moveAllCardsInLocation( null, "deck" );
        $this->cards->shuffle( 'deck' );
    
        // Deal 16 cards to each players
        // Create deck, shuffle it and give 16 initial cards
        $players = self::loadPlayersBasicInfos();
        foreach( $players as $player_id => $player )
        {
            $cards = $this->cards->pickCards( 16, 'deck', $player_id );
            

            // Notify player about his cards
            self::notifyPlayer( $player_id, 'newHand', '', array( 
                'cards' => $cards
            ) );
        }        
        

        if (    self::getStat ( "handNbr" ) == 1    )
        {

            $player_ids =  array_keys($this->loadPlayersBasicInfos());  

            $random_index = rand ( 0, 2 );

            $this->gamestate->changeActivePlayer( $player_ids [ $random_index ] );
        }


        $this->gamestate->nextState( "startHand" );  
    }



    //
    //  ST NEXT PLAYER
    //  ##############
    //
    //
    //  If end of trick 
    //      Determine trick winner  
    //      If 2 or 3 cards of same suit 
    //          Highest rank of that suit wins 
    //          
    //      Else (all different suits)
    //          Determine missing suit 
    //
    //          Missing suit = Cups  =>  Winner is Coins 
    //          Missing suit = Coins  =>  Winner is Swords 
    //          Missing suit = Swords  =>  Winner is Clubs 
    //          Missing suit = Clubs  =>  Winner is Cups 
    //
    //          Or, in other words, winner is (Missing suit + 1) mod 4
    //
    //
    //      Count how many coins trick winner has  
    //      Add points to his score 
    //
    //      If score high enough
    //          End game
    //  
    //      Else (score not high enough) 
    //      
    //          Next player = winner of last trick
    //
    //          If end of hand 
    //              Start new hand 
    //
    //          Else (not end of hand)
    //              Start new trick 
    //
    //  Else (not end of trick) 
    //      Go to next player clockwise
    //      
    //
    function stNextPlayer()
    {
        // 
        //  END OF TRICK ?
        //
        if( $this->cards->countCardInLocation( 'cardsontable' ) == 3 )      // END OF TRICK
        {            
            $trick_winner_player_id = null;
            //$currentTrickSuit = self::getGameStateValue( 'trickSuit' );

            $trick_suit = 0;                                                // NO TRICK SUIT

            $bAllDifferentSuits = false;
            

            $players = self::loadPlayersBasicInfos();
        
            foreach( $players as $player_id => $player )
            {
                $player_trick_points [ $player_id ] = 0;
            }   
               

            $cards_on_table = $this->cards->getCardsInLocation( 'cardsontable' );

            $suits_on_table = array ( );

            $ranks_on_table = array ( );

            $player_IDs_on_table = array ( );


            $number_of_suits_of_each_type = array ( );   
            $number_of_suits_of_each_type [ 1 ] = 0;    // Cups
            $number_of_suits_of_each_type [ 2 ] = 0;    // Coins
            $number_of_suits_of_each_type [ 3 ] = 0;    // Swords
            $number_of_suits_of_each_type [ 4 ] = 0;    // Clubs


            $number_of_trick_suits = -1;   // Unspecified number of trick suits


            //
            //  EXTRACT FROM CARDS ON TABLE 
            // 
            //      Player ids 
            //      Suits 
            //      Ranks 
            //
            //      Number of suits of each type
            //
            foreach( $cards_on_table as $card )
            {
                $player_IDs_on_table [ ] = $card['location_arg'];   // location_arg = player_id

                $suits_on_table [ ] = $card['type'];                // type = suit

                $ranks_on_table [ ] = $card['type_arg'];            // type_arg = rank


                ++$number_of_suits_of_each_type [ $card['type'] ];  // number of suits of each type
            }


            //
            //  NUMBER OF DIFFERENT TRICK SUITS 
            //
            if (    $suits_on_table [ 0 ] == $suits_on_table [ 1 ] 
                 && $suits_on_table [ 1 ] == $suits_on_table [ 2 ]  
                 && $suits_on_table [ 0 ] == $suits_on_table [ 2 ]    )
            {
                $number_of_trick_suits = 1;
            }
            else if (    $suits_on_table [ 0 ] == $suits_on_table [ 1 ] 
                 || $suits_on_table [ 1 ] == $suits_on_table [ 2 ]  
                 || $suits_on_table [ 0 ] == $suits_on_table [ 2 ]    )
            {
                $number_of_trick_suits = 2;
            }
            else 
            {
                $number_of_trick_suits = 3;
            }


            // 
            //  DETERMINE TRICK WINNER PLAYER ID 
            //
            if ( $number_of_trick_suits == 1 )      // ALL THE SAME SUIT - FIND PLAYER WITH HIGHEST RANK
            {
                $highest_rank_index = 0;

                $highest_rank = $ranks_on_table [ 0 ];


                if (    $ranks_on_table [ 1 ] > $highest_rank    )
                {
                    $highest_rank_index = 1;

                    $highest_rank = $ranks_on_table [ 1 ];
                }

                if (    $ranks_on_table [ 2 ] > $highest_rank    )
                {
                    $highest_rank_index = 2;
                }


                $trick_winner_player_id = $player_IDs_on_table [ $highest_rank_index ];
            }
            else if ( $number_of_trick_suits == 2 )      // TWO OF THE SAME SUIT - FIND PLAYER WITH HIGHEST RANK OF THAT SUIT
            {
                $trick_suit = array_search ( 2, $number_of_suits_of_each_type );


                $highest_rank_index = -1;

                $highest_rank = -1;


                for ( $i = 0; $i < 3; $i++ )
                {
                    if ( $suits_on_table [ $i ] == $trick_suit )
                    {
                        if ( $ranks_on_table [ $i ] > $highest_rank )
                        {
                            $highest_rank_index = $i;

                            $highest_rank = $ranks_on_table [ $i ];
                        }
                    }
                }


                $trick_winner_player_id = $player_IDs_on_table [ $highest_rank_index ];
            }
            else                                        // THREE DIFFERENT SUITS - FIND PLAYER WITH WINNING SUIT
            {
                $missing_suit = array_search ( 0, $number_of_suits_of_each_type );


                $winning_suit = ( $missing_suit % 4 ) + 1;

                $winning_player_index = array_search ( $winning_suit, $suits_on_table );

                $trick_winner_player_id = $player_IDs_on_table [ $winning_player_index ];
            }


            if( $trick_winner_player_id === null )
                throw new feException( self::_("Error, nobody wins the trick") );
            
             
            
            //
            //  TOTAL THE POINTS IN cardswon FOR EACH PLAYER 
            //
            $number_of_trick_coins = 0;

            foreach( $cards_on_table as $card )
            {
                if ( $card['type'] == 2 )   // Coins 
                {
                    ++$number_of_trick_coins;
                }
            }


            // 
            // APPLY TRICK WINNER'S POINTS TO HIS SCORE IN DATABASE 
            // 
            $sql = "UPDATE player SET player_score=player_score+$number_of_trick_coins
                    WHERE player_id='$trick_winner_player_id' " ;

            self::DbQuery( $sql );



            $newScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );

            self::notifyAllPlayers( "newScores", '', array( 'newScores' => $newScores ) );
        

            // 
            //  MOVE ALL cardsontable CARDS TO cardswon CARDS OF THE TRICK WINNER
            // 
            $this->cards->moveAllCardsInLocation( 'cardsontable', 'cardswon', null, $trick_winner_player_id );

            // Notify
            // Note: we use 2 notifications here in order we can pause the display during the first notification
            //  before we move all cards to the winner (during the second)
            $players = self::loadPlayersBasicInfos();



            self::notifyAllPlayers( 'trickWin', clienttranslate('${player_name} +${points}'), array(
                //'player_id' => $trick_winner_player_id,
                'player_name' => $players[ $trick_winner_player_id ]['player_name'],
                'points' => $number_of_trick_coins
            ) );   
            

            self::notifyAllPlayers( 'giveAllCardsToPlayer','', array(
                'player_id' => $trick_winner_player_id
            ) );


            //
            //  TEST FOR END OF GAME 
            //
            $winning_score = self::getGameStateValue ("gameLength");

            foreach( $newScores as $player_id => $score )
            {
                if( $score >= $winning_score )
                {
                    // Trigger the end of the game !
                    $this->gamestate->nextState( "endGame" );
                    return ;
                }
            }


            // Active this player => he's the one who starts the next trick
            $this->gamestate->changeActivePlayer( $trick_winner_player_id );
            
            if( $this->cards->countCardInLocation( 'hand' ) == 0 )
            {
                // 
                // START NEW HAND 
                //
                self::giveExtraTime( $player_id );

                $this->gamestate->nextState( "endHand" );
            }
            else
            {
                // End of the trick
                self::giveExtraTime( $player_id );

                $this->gamestate->nextState( "nextPlayer" );
            }
        }
        else
        {
            // Standard case (not the end of the trick)
            // => just active the next player
            $player_id = self::activeNextPlayer();
        

            self::giveExtraTime( $player_id );

            $this->gamestate->nextState( 'nextPlayer' );        
        }
    }
    

    
//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];
    	
        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );
            
            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    
    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
        
        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }    
}


/*
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'abcde: ${var_1}, ${var_2}' ), 
        array(
            "var_1" => $x,
            "var_2" => $y
        ) );
*/


/*
    $player_ids =  array_keys($this->loadPlayersBasicInfos());  
*/