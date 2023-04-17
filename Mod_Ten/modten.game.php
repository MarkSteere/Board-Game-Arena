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
  * modten.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class ModTen extends Table
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

            "trickSuit" => 10,
            "trickSuitID" => 11,
            "modTen" => 12,
            "modTenID" => 13,

            "b_WrongSuit" => 20,
            "b_PlayerAchievedModTen" => 21

        ) );  

        $this->cards = self::getNew( "module.common.deck" );
        $this->cards->init( "card" );
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "modten";
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
 
        $start_points = 0;

        $sql = "INSERT INTO player (player_id, player_score, player_color, player_canal, player_name, player_avatar, cards_played) VALUES ";

        $values = array();

        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$start_points','$color','".$player['player_canal']."',
                          '".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."','')";
            //$values[] = "('".$player_id."','$start_points','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
        }
        $sql .= implode( $values, ',' );

        self::DbQuery( $sql );

        self::reloadPlayersBasicInfos();

        
        //
        //  INITIALIZE GLOBALS
        //
        self::setGameStateInitialValue( 'trickSuit', 0 );

        self::setGameStateInitialValue( 'trickSuitID', 30000 );

        self::setGameStateInitialValue( 'modTen', 0 );

        self::setGameStateInitialValue( 'modTenID', 40000 );


        self::setGameStateInitialValue( 'b_WrongSuit', 0 );

        self::setGameStateInitialValue( 'b_PlayerAchievedModTen', 0 );


        /************ Start the game initialization *****/

        //
        //  CREATE CARDS
        //
        //  All decks missing 10s and face cards
        //
        //  Number of players == 2 
        //      Single deck missing 5s
        //          1, 2, 3, 4, 6, 7, 8, 9 of all suits
        //              32 cards
        //
        //  Number of players == 3
        //      Single deck missing 5s and Ace and 9 of Spades
        //          2, 3, 4, 6, 7, 8 of all suits AND 1 and 9 of Hearts, Diamonds, and Clubs
        //              30 cards
        //
        //  Number of players == 4
        //      Double deck
        //          1, 1, 2, 2, 3, 3, 4, 4, 5, 5, 6, 6, 7, 7, 8, 8, 9, 9 of all suits
        //              72 cards
        //
        //  Number of players == 5 or 6
        //      Double deck missing Ace and 9 of Spades, Ace and 9 of Diamonds, Ace and 9 of Clubs
        //          2, 2, 3, 3, 4, 4, 5, 5, 6, 6, 7, 7, 8, 8 AND 1 of Hearts, 1 of Hearts, 9 of Hearts, 9 of Hearts
        //              60 cards
        //
        //


        $cards = array();


        $number_of_players = count ($players);

        switch ( $number_of_players )
        {
            case 2:
                foreach( $this->suits  as  $suit_id => $suit ) // Hearts, Spades, Diamonds, Clubs
                {
                    for( $value=1; $value<=4; $value++ )   //  1, 2, 3, 4
                    {
                        $cards[] = array( 'type' => $suit_id, 'type_arg' => $value, 'nbr' => 1);    // One of each card
                    }

                    for( $value=6; $value<=9; $value++ )   //  6, 7, 8, 9
                    {
                        $cards[] = array( 'type' => $suit_id, 'type_arg' => $value, 'nbr' => 1);
                    }
                }

                break;

            case 3:
                foreach( $this->suits  as  $suit_id => $suit ) // Hearts, Spades, Diamonds, Clubs
                {
                    for( $value=2; $value<=4; $value++ )   //  2, 3, 4,
                    {
                        $cards[] = array( 'type' => $suit_id, 'type_arg' => $value, 'nbr' => 1);    // One of each card
                    }

                    for( $value=6; $value<=8; $value++ )   //  6, 7, 8
                    {
                        $cards[] = array( 'type' => $suit_id, 'type_arg' => $value, 'nbr' => 1);
                    }
                }

                $cards[] = array( 'type' => 1, 'type_arg' => 1, 'nbr' => 1);         // 1 of Hearts, Diamonds, and Clubs

                $cards[] = array( 'type' => 3, 'type_arg' => 1, 'nbr' => 1);

                $cards[] = array( 'type' => 4, 'type_arg' => 1, 'nbr' => 1);


                $cards[] = array( 'type' => 1, 'type_arg' => 9, 'nbr' => 1);         // 9 of Hearts, Diamonds, and Clubs

                $cards[] = array( 'type' => 3, 'type_arg' => 9, 'nbr' => 1);

                $cards[] = array( 'type' => 4, 'type_arg' => 9, 'nbr' => 1);

                break;

            case 4:
                foreach( $this->suits  as  $suit_id => $suit ) // Hearts, Spades, Diamonds, Clubs
                {
                    for( $value=1; $value<=9; $value++ )   //  1, 2, 3, 4, 5, 6, 7, 8, 9 of all suits
                    {
                        $cards[] = array( 'type' => $suit_id, 'type_arg' => $value, 'nbr' => 2);    // 2 of each card
                    }
                }

                break;


            case 5:
            case 6:
                foreach( $this->suits  as  $suit_id => $suit ) // Hearts, Spades, Diamonds, Clubs
                {
                    for( $value=2; $value<=8; $value++ )   //  2, 3, 4, 5, 6, 7, 8 of all suits
                    {
                        $cards[] = array( 'type' => $suit_id, 'type_arg' => $value, 'nbr' => 2);    // 2 of each card
                    }
                }

                $cards[] = array( 'type' => 1, 'type_arg' => 1, 'nbr' => 2);         // 1 of Hearts

                $cards[] = array( 'type' => 1, 'type_arg' => 9, 'nbr' => 2);         // 9 of Hearts

                break;                
        }


        $this->cards->createCards( $cards, 'deck' );



        //
        //
        //  STARTING PLAYER OF FIRST HAND DETERMINED RANDOMLY IN stNewHand
        //

        //
        //  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  
        //  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  
        //  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  BUG  
        //
        //  activeNextPlayer was commented out.  There was an intermittent bug of game starting with player id = 0 
        //
        //  Now uncommenting activeNextPlayer 
        //

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();
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
  


        //
        //  ACTIVE PLAYER ID 
        //
        $active_player_id = self::getActivePlayerId();    

        $result['active_player_id'] = $active_player_id;



        //
        //  NUMBER OF PLAYERS 
        //
        $players = self::loadPlayersBasicInfos();

        $number_of_players = count ( $players );

        switch ( $number_of_players )
        {
            case 2:
                $n_initial_cards_in_hand = 16;
                break;

            case 3:
                $n_initial_cards_in_hand = 10;
                break;

            case 4:
                $n_initial_cards_in_hand = 18;
                break;

            case 5:
                $n_initial_cards_in_hand = 12;
                break;

            case 6:
                $n_initial_cards_in_hand = 10;
                break;
        }


        $result['number_of_players'] = $number_of_players;



        //
        //  TRICK SUIT AND MODTEN 
        //
        $trick_suit = self::getGameStateValue ( 'trickSuit' );
        $result['trick_suit'] = $trick_suit;

        $trick_suit_id = self::getGameStateValue ( 'trickSuitID' );
        $result['trick_suit_id'] = $trick_suit_id;


        $mod_ten = self::getGameStateValue ( 'modTen' );
        $result['mod_ten'] = $mod_ten;

        $mod_ten_id = self::getGameStateValue ( 'modTenID' );
        $result['mod_ten_id'] = $mod_ten_id;


        //
        //  MOVE LISTS 
        //
        $move_list_player_ids = self::loadPlayersBasicInfos();

        $move_lists = array ( );

        foreach ( $move_list_player_ids  as  $move_list_player_id => $move_list_player )
        {
            $move_string = self::getUniqueValueFromDb( "SELECT cards_played FROM player WHERE player_id='$move_list_player_id'" );

            $move_lists [ $move_list_player_id ] = $move_string;
        }

        $result['move_lists'] = $move_lists;



        //
        //  NUMBER OF PLAYED CARDS 
        //
        $player_ids = self::loadPlayersBasicInfos();

        $n_played_cards = array();

        foreach ( $player_ids  as  $player_id => $player )
        {
            $n_cards_in_hand = $this->cards->countCardInLocation( 'hand', $player_id );

            $n_played_cards[$player_id] = $n_initial_cards_in_hand - $n_cards_in_hand;
        }
        
        $result['n_played_cards'] = $n_played_cards;



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
        //$number_of_cards = $this->cards->countCardInLocation( 'hand' );

        $players = self::loadPlayersBasicInfos();


        //
        //  PLAYER WITH LOWEST NUMBER OF CARDS 
        //
        $min_number_of_cards = 1000;

        foreach( $players as $player_id => $player )
        {
            $number_of_cards = $this->cards->countCardInLocation( 'hand', $player_id );

            $min_number_of_cards = min ( $number_of_cards, $min_number_of_cards );
        }


        $number_of_players = count ($players);

        switch ( $number_of_players )
        {
            case 2:
                return (    ( 16 - $min_number_of_cards ) / 16 * 100    ); 

            case 3:
                return (    ( 10 - $min_number_of_cards ) / 10 * 100    ); 

            case 4:
                return (    ( 18 - $min_number_of_cards ) / 18 * 100    ); 

            case 5:
                return (    ( 12 - $min_number_of_cards ) / 12 * 100    ); 

            case 6:
                return (    ( 10 - $min_number_of_cards ) / 10 * 100    ); 
        }
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    // Return players => position (1, 2, 3, 4, 5, 6) from the point of view
    //  of current player (current player must be on top)
    function getPlayersToPosition()
    {
        $result = array();
    
        $current_player = self::getCurrentPlayerId();
        

        $players = self::loadPlayersBasicInfos();

        $nextPlayer = self::createNextPlayerTable( array_keys( $players ) );


        $positions = array( );

        $number_of_players = count ($players);

        switch ( $number_of_players )
        {
            case 2:
                $positions = array( '1', '2' );
                break;

            case 3:
                $positions = array( '1', '2', '3' );
                break;

            case 4:
                $positions = array( '1', '2', '3', '4' );
                break;

            case 5:
                $positions = array( '1', '2', '3', '4', '5' );
                break;

            case 6:
                $positions = array( '1', '2', '3', '4', '5', '6' );
                break;
        }
        
        
        
        if( ! isset( $nextPlayer[ $current_player ] ) )
        {
            // Spectator mode: take any player for south
            $player_id = $nextPlayer[0];
            $result[ $player_id ] = array_shift( $positions );
        }
        else
        {
            // Normal mode: current player is on south
            $player_id = $current_player;
            $result[ $player_id ] = array_shift( $positions );
        }
        
        while( count( $positions ) > 0 )
        {
            $player_id = $nextPlayer[ $player_id ];
            $result[ $player_id ] = array_shift( $positions );
        }

        return $result;
    }






//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    //  #####################
    //  ####  PLAY CARD  #### 
    //  #####################
    //
    //  If a player got to this point, trying to play a card, it was already determined in 
    //  stNextPlayer that either this player has cards of the current trick suit, or it's 
    //  the beginning of a new trick.
    //
    //
    //  If trick suit == 0 (ie new trick)
    //      Set trickSuit = card suit
    //      Increment trickSuitID
    //      Add trick suit to frontend
    //
    //  Else if trick suit == card suit 
    //      If mod_ten = 0 
    //          Set trick suit = 0 
    //          Remove trick suit from front end 
    //          Set b_TakeAnotherTurn = true
    //
    //  Else ( not new trick and card suit != trick suit )
    //      Return
    //
    //  Set modTen to (mod_ten + card rank) % 10 
    //  Remove mod_ten from frontend
    //  Increment modTenID
    //  Add mod_ten to frontend
    //
    //  If b_TakeAnotherTurn == false 
    //      Activate next player 
    //
    //  State change to nextPlayer
    //
    //
    function playCard( $card_id )
    {
        self::checkAction( "playCard" );
        
        $active_player_id = self::getActivePlayerId();
        
        // Get all cards in player hand
        // (note: we must get ALL cards in player's hand in order to check if the card played is correct)
        
        $playerhands = $this->cards->getCardsInLocation( 'hand', $active_player_id );

                
        // Check that the card is in this hand
        $bIsInHand = false;

        $currentCard = null;

        foreach( $playerhands as $card )
        {
            if( $card['id'] == $card_id )
            {
                $bIsInHand = true;
                $currentCard = $card;

                break;
            }            
        }
        if( ! $bIsInHand )
            throw new feException( "This card is not in your hand" );



        //
        //  ############
        //
        //  CARD IN HAND 
        //
        //  ############
        //
        $b_wrongSuit = false;

        $b_newSuit = false;

        $b_player_achieved_mod_ten = false;


        $rank_str = "";

        $new_suit_str = "";


        $trick_suit = self::getGameStateValue ( 'trickSuit' );

        $trick_suit_id = self::getGameStateValue ( 'trickSuitID' );


        $mod_ten = self::getGameStateValue ( 'modTen' );

        $mod_ten_id = self::getGameStateValue ( "modTenID");


        $card_suit = $currentCard['type'];

        $card_rank = $currentCard['type_arg'];


        $new_mod_ten = ( $mod_ten + $card_rank ) % 10;



        // 
        //  TRICK SUIT 
        //
        if ( $trick_suit == 0 )                                                     //  IF TRICK SUIT = 0.  NEW TRICK, NEW SUIT
        {
            self::setGameStateValue ( 'trickSuit', $card_suit );                    //      SET TRICK SUIT = CARD SUIT

            $trick_suit_id = self::incGameStateValue ( "trickSuitID", 1);           //      INC TRICK SUIT ID 


            self::notifyAllPlayers( "backendMessage", clienttranslate( '${player_name} ${value_displayed} ${suit_displayed}' ), //    NOTIFY FRONTEND ABOUT CARD PLAYED
            array(
                'player_name' => self::getActivePlayerName(),
                'value_displayed' => $this->values_label[ $currentCard['type_arg'] ],
                'suit_displayed' => $this->suits[ $currentCard['type'] ]['name']
            ) );


            //
            //  ADD TRICK SUIT TO FRONTEND 
            //
            self::notifyAllPlayers( 'trickSuitAdded', clienttranslate('New suit: ${suit_displayed}'), array(
                'trick_suit' => $card_suit,
                'trick_suit_id' => $trick_suit_id,
                'player_id' => $active_player_id,
                'suit_displayed' => $this->suits[ $currentCard['type'] ]['name']
            ) );


            //
            //  SUIT STRING
            //
            $b_newSuit = true;

            switch ( $card_suit )
            {
                case 1: 
                    $new_suit_str = "<b>H</b>";

                    break;

                case 2: 
                    $new_suit_str = "<b>S</b>";

                    break;

                case 3: 
                    $new_suit_str = "<b>D</b>";

                    break;

                case 4: 
                    $new_suit_str = "<b>C</b>";

                    break;
            }
        }
        else if ( $trick_suit == $card_suit )                                       //  ELSE IF TRICK SUIT = CARD SUIT
        {
            self::notifyAllPlayers( "backendMessage", clienttranslate( '${player_name} ${value_displayed} ${suit_displayed}' ), //    NOTIFY FRONTEND ABOUT CARD PLAYED
            array(
                'player_name' => self::getActivePlayerName(),
                'value_displayed' => $this->values_label[ $currentCard['type_arg'] ],
                'suit_displayed' => $this->suits[ $currentCard['type'] ]['name']
            ) );

            if ( $new_mod_ten == 0 )                                                //      IF NEW MOD TEN == 0
            {
                self::setGameStateValue ( 'trickSuit', 0 );                         //          SET TRICK SUIT = 0

                self::notifyAllPlayers( "trickSuitRemoved", clienttranslate( '' ),  //          REMOVE TRICK SUIT FROM FRONTEND
                array(
                    'trick_suit_id' => $trick_suit_id,
                    'player_id' => $active_player_id
                ) );

                //
                //  NOTE THAT PLAYER ACHIEVED MOD TEN 
                //
                //      USED LATER IN THIS FUNCTION 
                //      AND IN stNextPlayer
                //
                $b_player_achieved_mod_ten = true;

                self::setGameStateValue ( 'b_PlayerAchievedModTen', 1 );
            }
        }
        else    //  NOT NEW TRICK AND CARD SUIT != TRICK SUIT
        {
            self::notifyAllPlayers( "backendMessage", clienttranslate( 'Wrong suit.' ), 
            array(
            ) );


            $b_wrongSuit = true;

            self::setGameStateValue ( 'b_WrongSuit', 1 );

        }


        if ( ! $b_wrongSuit )                                                   //  IF NOT ATTEMPTING TO PLAY THE WRONG SUIT...
        {
            //
            //  REMOVE OLD MOD TEN FROM FRONTEND
            //
            self::notifyAllPlayers( "modTenRemoved", clienttranslate( '' ), 
            array(
                'mod_ten_id' => $mod_ten_id,
                'player_id' => $active_player_id
            ) );

            $mod_ten_id = self::incGameStateValue ( "modTenID", 1);         // INC MOD TEN ID


            //
            //  ADD NEW MOD TEN 
            //
            //  GLOBAL
            self::setGameStateValue ( 'modTen', $new_mod_ten );

            //  FRONTEND
            self::notifyAllPlayers( 'modTenAdded', clienttranslate('Mod ten: ${mod_ten}'), array(
                'mod_ten' => $new_mod_ten,
                'mod_ten_id' => $mod_ten_id,
                'player_id' => $active_player_id
            ) );


            //
            //  REMOVE CARD FROM HAND 
            //
            //  DECK
            $this->cards->moveCard( $card_id, 'cardsontable', $active_player_id );
        

            //
            //  REMOVE CARD FROM FRONTEND 
            //
            self::notifyAllPlayers( 'removeCard', clienttranslate(''), array(
                'card_id' => $card_id,
                'player_id' => $active_player_id
            ) );



            //
            //  UPDATE DATABASE MOVE STRINGS AND NOTIFY FRONTEND
            //  ################################################
            //

            //
            //  ADD NEW SUIT FOR ALL PLAYERS
            //
            if ( $b_newSuit )
            {
                $players = self::loadPlayersBasicInfos();

                foreach ( $players  as  $player_id => $player )
                {
                    //  UPDATE DATABASE
                    $move_string = self::getUniqueValueFromDb( "SELECT cards_played FROM player WHERE player_id='$player_id'" );

                    $move_string.= $new_suit_str." ";

                    $sql = "UPDATE player SET cards_played = '$move_string' WHERE player_id='$player_id'";   
            
                    self::DbQuery( $sql );

                    self::notifyAllPlayers( "moveStringUpdated", clienttranslate( '' ), 
                    array(
                        "player_id" => $player_id,

                        "move_string" => $move_string
                    ) );
                }
            }


            //
            //  ADD RANK FOR ACTIVE PLAYER
            //
            //  UPDATE DATABASE
            $move_string = self::getUniqueValueFromDb( "SELECT cards_played FROM player WHERE player_id='$active_player_id'" );


            //
            //  IF PLAYER ACHIEVED MOD TEN 
            //
            //      ADD SOME COLOR TO THE RANK IN THE MOVE LIST 
            //
            if ( $b_player_achieved_mod_ten )
            {
                $move_string.= "<span style = color:#c30000>{$card_rank} </span>";
            }
            else 
            {
                $move_string.= "{$card_rank} ";
            }


            $sql = "UPDATE player SET cards_played = '$move_string' WHERE player_id='$active_player_id'";   
            
            self::DbQuery( $sql );

            //  NOTIFY FRONTEND
            self::notifyAllPlayers( "moveStringUpdated", clienttranslate( '' ), 
            array(
                "player_id" => $active_player_id,

                "move_string" => $move_string
            ) );






            //
            // UPDATE PLAYER PANEL - SHOW ACTIVE PLAYER'S NUMBER OF PLAYED CARDS
            //
            //
            //      NUMBER OF PLAYERS 
            //
            $players = self::loadPlayersBasicInfos();

            $number_of_players = count ( $players );

            switch ( $number_of_players )
            {
                case 2:
                    $n_initial_cards_in_hand = 16;
                    break;

                case 3:
                    $n_initial_cards_in_hand = 10;
                    break;

                case 4:
                    $n_initial_cards_in_hand = 18;
                    break;

                case 5:
                    $n_initial_cards_in_hand = 12;
                    break;

                case 6:
                    $n_initial_cards_in_hand = 10;
                    break;
            }

            $n_cards_in_hand = $this->cards->countCardInLocation( 'hand', $active_player_id );

            $n_played_cards = $n_initial_cards_in_hand - $n_cards_in_hand;

            self::notifyAllPlayers("playerPanel",
                "",
                array(
                "n_played_cards" => $n_played_cards,
                "player_id" => $active_player_id ));



            //
            //  CHECK FOR LAST CARD PLAYED, WIN 
            //
            $cards = $this->cards->getCardsInLocation( "hand", $active_player_id );

            if (    count ( $cards ) == 0    )      //  PLAYED LAST CARD
            {
                // 
                //  CALCULATE SCORES 
                //
                $remaining_number_of_cards = array ( );

                $players = self::loadPlayersBasicInfos();

                $number_of_players = count ( $players );

                switch ( $number_of_players )
                {
                    case 2:
                        $number_of_cards_per_hand = 16;
                        break;

                    case 3:
                        $number_of_cards_per_hand = 10;
                        break;

                    case 4:
                        $number_of_cards_per_hand = 18;
                        break;

                    case 5:
                        $number_of_cards_per_hand = 12;
                        break;

                    case 6:
                        $number_of_cards_per_hand = 10;
                        break;
                }



                //
                //  EACH PLAYER'S SCORE IS...
                //
                //      NUMBER OF CARDS THEY PLAYED 
                //
                $player_to_points = array();
        
                foreach( $players as $player_id => $player )
                {
                    $cards = $this->cards->getCardsInLocation( "hand", $player_id );

                    $number_of_unplayed_cards = count ( $cards );

                    $player_to_points[ $player_id ] = $number_of_cards_per_hand - $number_of_unplayed_cards;    // NUMBER OF PLAYED CARDS
                }  
                

                //
                //  UPDATE SCORES 
                //
                foreach( $player_to_points as $player_id => $points )
                {
                    $sql = "UPDATE player SET player_score=$points WHERE player_id='$player_id' ";

                    self::DbQuery( $sql );
                }

                $newScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );

                self::notifyAllPlayers( "newScores", '', array( 'newScores' => $newScores ) );
        

                //
                //  END THE GAME 
                //
                $this->gamestate->nextState( "endGame" );
                return ;

            }

        }
        
        // 
        //  NEXT PLAYER 
        //
        $this->gamestate->nextState( 'playCard' );
    }
    

    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////




//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
    
    function stNewHand()
    {
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'MOD TEN' ), 
        array(
        ) );
        //self::incStat( 1, "handNbr" );
    

        //
        //  CONSOLIDATE CARDS 
        //
        // Take back all cards (from any location => null) to deck
        $this->cards->moveAllCardsInLocation( null, "deck" );


        //
        //  SHUFFLE THE DECK 
        //
        $this->cards->shuffle( 'deck' );

    
        //
        // DEAL THE CARDS 
        //
        $players = self::loadPlayersBasicInfos();

        $number_of_players = count ($players);

        switch ( $number_of_players )
        {
            case 2:
                foreach( $players as $player_id => $player )
                {
                    $cards = $this->cards->pickCards( 16, 'deck', $player_id );     // 16 cards
                }

                break;


            case 3:
                foreach( $players as $player_id => $player )
                {
                    $cards = $this->cards->pickCards( 10, 'deck', $player_id );     // 10 cards
                }

                break;


            case 4:
                foreach( $players as $player_id => $player )
                {
                    $cards = $this->cards->pickCards( 18, 'deck', $player_id );     // 18 cards
                }

                break;


            case 5:
                foreach( $players as $player_id => $player )
                {
                    $cards = $this->cards->pickCards( 12, 'deck', $player_id );     // 12 cards
                }

                break;


            case 6:
                foreach( $players as $player_id => $player )
                {
                    $cards = $this->cards->pickCards( 10, 'deck', $player_id );     // 10 cards
                }

                break;
        }        
        

        //
        //  RANDOM PLAYER TO START 
        //
        $player_ids =  array_keys($this->loadPlayersBasicInfos());  

        $random_index = bga_rand ( 0, $number_of_players - 1 );

        $this->gamestate->changeActivePlayer( $player_ids [ $random_index ] );


        $this->gamestate->nextState( "startHand" );  
    }




    //
    //  ST NEXT PLAYER
    //  ##############
    //
    //
    //      
    //
    function stNextPlayer()
    {        
        $b_wrong_suit = self::getGameStateValue ("b_WrongSuit");

        $b_player_achieved_mod_ten = self::getGameStateValue ("b_PlayerAchievedModTen");

        $active_player_id = self::getActivePlayerId();


        if ( $b_wrong_suit )
        {
            $player_id = $active_player_id;

            self::setGameStateValue ( "b_WrongSuit", 0 );
        }
        else if ( $b_player_achieved_mod_ten )
        {
            $player_id = $active_player_id;

            self::setGameStateValue ( "b_PlayerAchievedModTen", 0 );

            self::giveExtraTime( $player_id );
        }
        else   //   GO ON TO THE NEXT PLAYER
        {
            //
            //  FIND NEXT PLAYER WHO HAS CARDS OF TRICK SUIT 
            //
            $trick_suit = self::getGameStateValue ("trickSuit");


            $players = self::loadPlayersBasicInfos();
            $number_of_players = count( $players );


            $nextPlayers = $this->getNextPlayerTable();

            $player_id = $active_player_id;

            for ( $i = 0; $i < $number_of_players; $i++ )
            {
                $player_id = $nextPlayers[$player_id];


                $cards = $this->cards->getCardsInLocation( "hand", $player_id );

                foreach( $cards as $card )
                {
                    if( $card['type'] == $trick_suit )    
                    {
                        break 2;
                    }
                }

                // 
                //  PLAYER DIDN'T HAVE ANY CARDS OF TRICK SUIT, SO...
                //      ADD A SKIPPED TURN SYMBOL (_) TO THEIR MOVE LIST 
                //
                $move_string = self::getUniqueValueFromDb( "SELECT cards_played FROM player WHERE player_id='$player_id'" );

                $move_string.= "_ ";     //  _ = SKIPPED TURN

                $sql = "UPDATE player SET cards_played = '$move_string' WHERE player_id='$player_id'";   
            
                self::DbQuery( $sql );

                //  NOTIFY FRONTEND
                self::notifyAllPlayers( "moveStringUpdated", clienttranslate( '' ), 
                array(
                    "player_id" => $player_id,
    
                    "move_string" => $move_string
                ) );
            }


            $this->gamestate->changeActivePlayer( $player_id );

            self::giveExtraTime( $player_id );
        }
      

        $this->gamestate->nextState( 'nextPlayer' );          
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



