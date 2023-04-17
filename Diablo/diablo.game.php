<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * Diablo implementation : © <Your name here> <Your email address here>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * diablo.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class Diablo extends Table
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
            "black_checker_base_id" => 20,    
            "yellow_checker_base_id" => 21,                 

            "number_of_black_checkers" => 28,         
            "number_of_yellow_checkers" => 29,  
            
            "rolled_die_0" => 30,
            "rolled_die_1" => 31,
            "rolled_die_0_id" => 32,
            "rolled_die_1_id" => 33,

            "was_move_A_to_unoccupied" => 40,                    
            //"move_A_x" => 42,                    
            //"move_A_y" => 43,    
            
            "played_die" => 44,    
            
            "last_move_indicator_x" => 50,                    
            "last_move_indicator_y" => 51,                    
            "last_move_indicator_id" => 52,  
            
            "number_of_moves" => 53,  
            
            "board_size" => 101                   
        ) );        
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "diablo";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {    
        //
        // Initialize global variables
        //
        self::setGameStateValue( "black_checker_base_id", 100000 );     // ABBCCC ... A = COLOR, BB = VALUE, CCC = ID
        self::setGameStateValue( "yellow_checker_base_id", 200000 );
        self::setGameStateValue( "rolled_die_0_id", 30000 );
        self::setGameStateValue( "rolled_die_1_id", 40000 );

        self::setGameStateValue( "last_move_indicator_x", 0 );
        self::setGameStateValue( "last_move_indicator_y", 0 );
        self::setGameStateValue( "last_move_indicator_id", 99999 );

        self::setGameStateValue( "number_of_moves", 0 );

        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
 
        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";


            //
            // FOR BOARD FILL
            //
            if( $color == '000000' )
                $black_player_id = $player_id;
            else
                $yellow_player_id = $player_id;


        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );

        //self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );

        self::reloadPlayersBasicInfos();
        
        //
        // Load the board with checkers 
        //
        self::initializeBoard ( $black_player_id, $yellow_player_id );

        self::rollDice ( );



        //
        //  TEMPORARY - BLANK OUT 30 CHECKERS
        //
        /*
        self::incGameStateValue ("number_of_black_checkers", -12 );

        self::incGameStateValue ("number_of_yellow_checkers", -12 );


        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 0, 0 )";      
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 1, 0 )";      
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 2, 0 )";      
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 3, 0 )";      
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 4, 0 )";      
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 5, 0 )";      
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 0, 2 )";  
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 1, 2 )";      
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 2, 2 )";      
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 3, 2 )";      
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 4, 2 )";      
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 5, 2 )";      
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 0, 3 )";      
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 1, 3 )";      
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 2, 3 )";      
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 3, 3 )";      
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 4, 3 )";      
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 5, 3 )";      
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 0, 5 )";      
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 1, 5 )";      
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 2, 5 )";      
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 3, 5 )";      
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 4, 5 )";      
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 5, 5 )";      
        self::DbQuery( $sql );
        */



        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();



        //
        // UPDATE DICE ROLL HISTORY PANEL 
        //
        $die_0 = self::getGameStateValue ("rolled_die_0");
        $die_1 = self::getGameStateValue ("rolled_die_1");

        self::notifyAllPlayers( "diceRolledHistory", clienttranslate( '${player_name} ${die_0}-${die_1}' ),     
            array(
                'player_name' => self::getActivePlayerName(),
                'die_0' => $die_0,
                'die_1' => $die_1
            ) );

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
        $result = array();
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
    
        $active_player_id = self::getActivePlayerId();    

        $result['active_player_id'] = $active_player_id;

        $active_player_color = self::getPlayerColor ( $active_player_id );

        $result['active_player_color'] = $active_player_color;

        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );
  

        //
        // Get board occupied cells
        // 
        $sql = "SELECT board_x x, board_y y, board_player player, checker_id checker_id, is_origin_selected is_origin_selected
                FROM board WHERE board_player IS NOT NULL";

        $result['board'] = self::getObjectListFromDB( $sql );


        //
        // NUMBER OF CHECKERS
        //
        //$activePlayerId = self::getActivePlayerId();
        $other_player_id = self::getOtherPlayerId($active_player_id);

        //$active_player_color = self::getPlayerColor ( $activePlayerId );

        $numberOfCheckers = array();

        if ( $active_player_color == "000000" )
        {
            $numberOfCheckers[$active_player_id] = self::getGameStateValue( "number_of_black_checkers" );
            $numberOfCheckers[$other_player_id] = self::getGameStateValue( "number_of_yellow_checkers" );
        }
        else
        {
            $numberOfCheckers[$active_player_id] = self::getGameStateValue( "number_of_yellow_checkers" );
            $numberOfCheckers[$other_player_id] = self::getGameStateValue( "number_of_black_checkers" );
        }
        
        $result['numberOfCheckers'] = $numberOfCheckers;


        $die_0 = self::getGameStateValue ( "rolled_die_0" );
        $die_1 = self::getGameStateValue ( "rolled_die_1" );

        $rolled_dice = array ( $die_0, $die_1 );

        $result['rolled_dice'] = $rolled_dice;


        $die_0_id = self::getGameStateValue ( "rolled_die_0_id" );
        $die_1_id = self::getGameStateValue ( "rolled_die_1_id" );

        $rolled_dice_IDs = array ( $die_0_id, $die_1_id );

        $result['rolled_dice_IDs'] = $rolled_dice_IDs;


		//Get the board size
		$result['board_size'] = self::getGameStateValue("board_size");
  

  
        //
        // LAST MOVE INDICATOR 
        //
        $last_move = array ( );

        $last_move [ 0 ] = self::getGameStateValue( "last_move_indicator_x" );

        $last_move [ 1 ] = self::getGameStateValue( "last_move_indicator_y" );

        $last_move [ 2 ] = self::getGameStateValue( "last_move_indicator_id" );

        $result['last_move'] = $last_move;



        //
        // NUMBER OF MOVES 
        //
        $result['number_of_moves'] = self::getGameStateValue( "number_of_moves" );



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
        $number_of_black_checkers = self::getGameStateValue ( "number_of_black_checkers");

        $number_of_yellow_checkers = self::getGameStateValue ( "number_of_yellow_checkers");

        $lesser_number_of_checkers = min ( $number_of_black_checkers, $number_of_yellow_checkers );


        $N = self::getGameStateValue ( "board_size" );

        switch ( $N )
        {
            case 6:
                $removed_checkers_percent = ( 18 - $lesser_number_of_checkers ) / 18 * 100;
                break;

            case 8:
                $removed_checkers_percent = ( 32 - $lesser_number_of_checkers ) / 32 * 100;
                break;

            case 10:
                $removed_checkers_percent = ( 50 - $lesser_number_of_checkers ) / 50 * 100;
                break;
        }

        return 2 * $removed_checkers_percent - ( $removed_checkers_percent ** 2 / 100 );   
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

//
//  GET SELECTABLE ORIGINS FIRST TURN
//  #################################
//
//  Return array of active player occupied squares
//              
//
function getSelectableOriginsFirstTurn ( $active_player_id )
{                       
    $legal_moves = array();

    $board = self::getBoard();


    //
	//Get the board size
    //
    $N=self::getGameStateValue("board_size");
                  

    //
    // VISIT ALL SQUARES
    //
    for ( $y = 0; $y < $N; $y++ )                       // Start on bottom row.  $y = 0
    {
        for ( $x = 0; $x < $N; $x++ )                   
        {
            if (    $board [ $x ] [ $y ] == $active_player_id    )                  
            {
                if(    ! isset ( $legal_moves [ $x ] )    )
                    $legal_moves [ $x ] = array();

                $legal_moves [ $x ] [ $y ] = true;
            }
        }
    }

    return $legal_moves;
}




//
//  GET SELECTABLE ORIGINS MOVE A
//  #############################
//
//  Return array of selectable origins for Move A
//              
//
function getSelectableOriginsMoveA ( $active_player_id )
{                       
    $legal_moves = array();


    $board = self::getBoard();

    $checker_IDs = self::getCheckerIDs();


    $N = self::getGameStateValue ( "board_size" );


    $die_0 = self::getGameStateValue ("rolled_die_0");

    $die_1 = self::getGameStateValue ("rolled_die_1");

                 

    //
    // VISIT ALL SQUARES
    //
    for ( $y = 0; $y < $N; $y++ )                       // Start on bottom row.  $y = 0
    {
        for ( $x = 0; $x < $N; $x++ )                   
        {
            if ( $board [ $x ] [ $y ] == $active_player_id )   // ACTIVE PLAYER              
            {
                if (    self::hasMoves_A ( $x, $y, $active_player_id, $board, $checker_IDs, $N, $die_0, $die_1 )    )   // HAS LEGAL MOVES              
                {
                    if(    ! isset ( $legal_moves [ $x ] )    )
                        $legal_moves [ $x ] = array();

                    $legal_moves [ $x ] [ $y ] = true;
                }
            }
        }
    }

                 
    return $legal_moves;
}




//
//  GET SELECTABLE ORIGINS MOVE B 
//  #############################
//
//  Move B can use other die to...
//      Merge with friendly stack, or 
//      Capture equal or smaller enemy stack
//
//  If move A was not to unoccupied square 
//      Move B can use other die to move to unoccupied square
//      
//
//
//  ###  OLD  ###  If move A was not to unoccupied square
//  ###  OLD  ###      Move B must use other die to either...
//  ###  OLD  ###          Make single die merge to friendly stack
//  ###  OLD  ###          Make single die capture
//          
//  ###  OLD  ###  Else (move A was to unoccupied square) 
//  ###  OLD  ###      Move B must use other die to either...
//  ###  OLD  ###          Capture enemy from move A 
//  ###  OLD  ###          Merge stack at move A to friendly stack 
//  ###  OLD  ###          Merge friendly stack to stack at move A
//
function getSelectableOriginsMoveB ( $active_player_id )
{                       
    $legal_moves = array();


    $board = self::getBoard();

    $checker_IDs = self::getCheckerIDs();


    $N = self::getGameStateValue ( "board_size" );


    $die_0 = self::getGameStateValue ("rolled_die_0");

    $die_1 = self::getGameStateValue ("rolled_die_1");


    //
    // GET UNPLAYED DIE, NOT PLAYED IN MOVE A 
    //
    $played_die = self::getGameStateValue ("played_die");

    if ( $played_die == $die_0 )
    {
        $unplayed_die = $die_1;
    }
    else 
    {
        $unplayed_die = $die_0;
    }
                  

    //
    // VISIT ALL SQUARES
    //
    for ( $y = 0; $y < $N; $y++ )                       // Start on bottom row.  $y = 0
    {
        for ( $x = 0; $x < $N; $x++ )                   
        {
            if (    $board [ $x ] [ $y ] == $active_player_id     )   // FRIENDLY ORIGIN STACK             
            {
                if (    self::hasMoves_B ( $x, $y, $active_player_id, $board, $checker_IDs, $N, $unplayed_die )     )   // HAS LEGAL MOVES              
                {
                    if(    ! isset ( $legal_moves [ $x ] )    )
                        $legal_moves [ $x ] = array();

                    $legal_moves [ $x ] [ $y ] = true;
                }
            }
        }
    }

    return $legal_moves;
}



//
//  GET SELECTED ORIGIN AND SELECTABLE DESTINATIONS FIRST TURN
//  ##########################################################
//
//  Return array of destinations for first turn
//
//  If any of the following destinations are on the board, add them to the legal moves:
//
//      selected_origin_x + rolled_die_0, selected_origin_y
//      selected_origin_x - rolled_die_0, selected_origin_y
//      selected_origin_x + rolled_die_1, selected_origin_y
//      selected_origin_x - rolled_die_1, selected_origin_y
//      selected_origin_x, selected_origin_y + rolled_die_0, 
//      selected_origin_x, selected_origin_y - rolled_die_0, 
//      selected_origin_x, selected_origin_y + rolled_die_1, 
//      selected_origin_x, selected_origin_y - rolled_die_1, 
//              
//
function getSelectedOrigin_SelectableDestinationsFirstTurn ( $active_player_id )
{          
    //
    //  SELECTED ORIGIN 
    //
    $selected_origin = self::getSelectedOriginCoords ( );

    $legal_moves = array();

    $board = self::getBoard();

    $inactive_player_id = self::getOtherPlayerId ( $active_player_id );


    //
	//Get the board size
    //
    $N=self::getGameStateValue("board_size");
                  

    //
    // SELECTABLE DESTINATIONS 
    //
    $die_0 = self::getGameStateValue ( "rolled_die_0");

    $die_1 = self::getGameStateValue ( "rolled_die_1");


    $possible_destinations = array (

        array ( $selected_origin [ 0 ] + $die_0, $selected_origin [ 1 ] ),
        array ( $selected_origin [ 0 ] - $die_0, $selected_origin [ 1 ] ),
        array ( $selected_origin [ 0 ] + $die_1, $selected_origin [ 1 ] ),
        array ( $selected_origin [ 0 ] - $die_1, $selected_origin [ 1 ] ),
        array ( $selected_origin [ 0 ], $selected_origin [ 1 ]  + $die_0 ),
        array ( $selected_origin [ 0 ], $selected_origin [ 1 ]  - $die_0 ),
        array ( $selected_origin [ 0 ], $selected_origin [ 1 ]  + $die_1 ),
        array ( $selected_origin [ 0 ], $selected_origin [ 1 ]  - $die_1 ),

    );


    foreach ( $possible_destinations as $possible_destination )
    {
        if (    self::isSquareOnBoard ( $possible_destination [ 0 ], $possible_destination [ 1 ], $N )    )
        {
            if(    ! isset ( $legal_moves [    $possible_destination [ 0 ]    ] )    )
                $legal_moves [    $possible_destination [ 0 ]    ] = array();

            $legal_moves [    $possible_destination [ 0 ]    ] [    $possible_destination [ 1 ]    ] = true;
        }
    }

 
    $selectedOrigin_selectableDestinationsFirstTurn = array ( $selected_origin, $legal_moves );

    return $selectedOrigin_selectableDestinationsFirstTurn;
}



//
//  GET SELECTED ORIGIN AND SELECTABLE DESTINATIONS MOVE A
//  ######################################################
//
//  SELECTED_X,SELECTED_Y 
//
//  For the 8 vectors 
//
//      If destination is friendly stack
//          Add to legal moves 
//      Else if destination is smaller enemy stack
//          Add to list of legal moves 
//      Else if destination is unoccupied square 
//          Add to list of legal moves 
//          
//
//
function getSelectedOrigin_SelectableDestinationsMoveA ( $player_id )
{              
    //
    //  SELECTED ORIGIN 
    //
    $selected_origin = self::getSelectedOriginCoords ( );

    $selected_x = $selected_origin [ 0 ];
    $selected_y = $selected_origin [ 1 ];


    $other_player_id = self::getOtherPlayerId ( $player_id );


    $legal_moves = array();

    $board = self::getBoard();

    $checker_IDs = self::getCheckerIDs();

    $selected_checker_id = self::getCheckerID ( $selected_x, $selected_y );

    $selected_stack_height = self::getStackHeight ( $selected_checker_id );



    //
	//Get the board size
    //
    $N=self::getGameStateValue("board_size");
                  

    $die_0 = self::getGameStateValue ( "rolled_die_0");

    $die_1 = self::getGameStateValue ( "rolled_die_1");



    //
    // X + die_0, Y
    //
    if (    self::isSelectableDestination_A 
            ($selected_x, $selected_y, $selected_x + $die_0, $selected_y, $die_1, $selected_stack_height, $board, $checker_IDs, $player_id, $other_player_id, $N )    )
    {
            if(    ! isset ( $legal_moves [ $selected_x + $die_0 ] )    )
                $legal_moves [ $selected_x + $die_0 ] = array();

            $legal_moves [ $selected_x + $die_0 ] [ $selected_y ] = true;
    }

    //
    // X - die_0, Y
    //
    if (    self::isSelectableDestination_A 
            ($selected_x, $selected_y, $selected_x - $die_0, $selected_y, $die_1, $selected_stack_height, $board, $checker_IDs, $player_id, $other_player_id, $N )    )
    {
            if(    ! isset ( $legal_moves [ $selected_x - $die_0 ] )    )
                $legal_moves [ $selected_x - $die_0 ] = array();

            $legal_moves [ $selected_x - $die_0 ] [ $selected_y ] = true;
    }

    //
    // X + die_1, Y
    //
    if (    self::isSelectableDestination_A 
            ($selected_x, $selected_y, $selected_x + $die_1, $selected_y, $die_0, $selected_stack_height, $board, $checker_IDs, $player_id, $other_player_id, $N )    )
    {
            if(    ! isset ( $legal_moves [ $selected_x + $die_1 ] )    )
                $legal_moves [ $selected_x + $die_1 ] = array();

            $legal_moves [ $selected_x + $die_1 ] [ $selected_y ] = true;
    }

    //
    // X - die_1, Y
    //
    if (    self::isSelectableDestination_A 
            ($selected_x, $selected_y, $selected_x - $die_1, $selected_y, $die_0, $selected_stack_height, $board, $checker_IDs, $player_id, $other_player_id, $N )    )
    {
            if(    ! isset ( $legal_moves [ $selected_x - $die_1 ] )    )
                $legal_moves [ $selected_x - $die_1 ] = array();

            $legal_moves [ $selected_x - $die_1 ] [ $selected_y ] = true;
    }

    //
    // X, Y + die_0
    //
    if (    self::isSelectableDestination_A 
            ($selected_x, $selected_y, $selected_x, $selected_y + $die_0, $die_1, $selected_stack_height, $board, $checker_IDs, $player_id, $other_player_id, $N )    )
    {
            if(    ! isset ( $legal_moves [ $selected_x ] )    )
                $legal_moves [ $selected_x ] = array();

            $legal_moves [ $selected_x ] [ $selected_y + $die_0 ] = true;
    }

    //
    // X, Y - die_0
    //
    if (    self::isSelectableDestination_A 
            ($selected_x, $selected_y, $selected_x, $selected_y - $die_0, $die_1, $selected_stack_height, $board, $checker_IDs, $player_id, $other_player_id, $N )    )
    {
            if(    ! isset ( $legal_moves [ $selected_x ] )    )
                $legal_moves [ $selected_x ] = array();

            $legal_moves [ $selected_x ] [ $selected_y - $die_0 ] = true;
    }

    //
    // X, Y + die_1
    //
    if (    self::isSelectableDestination_A 
            ($selected_x, $selected_y, $selected_x, $selected_y + $die_1, $die_0, $selected_stack_height, $board, $checker_IDs, $player_id, $other_player_id, $N )    )
    {
            if(    ! isset ( $legal_moves [ $selected_x ] )    )
                $legal_moves [ $selected_x ] = array();

            $legal_moves [ $selected_x ] [ $selected_y + $die_1 ] = true;
    }

    //
    // X, Y - die_1
    //
    if (    self::isSelectableDestination_A 
            ($selected_x, $selected_y, $selected_x, $selected_y - $die_1, $die_0, $selected_stack_height, $board, $checker_IDs, $player_id, $other_player_id, $N )    )
    {
            if(    ! isset ( $legal_moves [ $selected_x ] )    )
                $legal_moves [ $selected_x ] = array();

            $legal_moves [ $selected_x ] [ $selected_y - $die_1 ] = true;
    }


 
    $selectedOrigin_selectableDestinationsMoveA = array ( $selected_origin, $legal_moves );

    return $selectedOrigin_selectableDestinationsMoveA;
}




//
//  IS SELECTABLE DESTINATION A
//  ###########################
//
//  If friendly stack 
//      Return true 
//  
//  If smaller enemy stack 
//      Return true 
//  
//  If unoccupied square 
//      Return true 
//
//
//  Return false
// 
//
function isSelectableDestination_A 
         ($origin_x, $origin_y, $destination_x, $destination_y, $other_die, $selected_stack_height, $board, $checker_IDs, $player_id, $other_player_id, $N )
{            
    if      (    self::isFriendlyStack ( $destination_x, $destination_y, $board, $player_id, $N )    )
        return true;

    else if (    self::isSmallerEnemyStack ( $destination_x, $destination_y, $selected_stack_height, $board, $checker_IDs, $other_player_id, $N )    )
        return true;

    else if (    self::isUnoccupiedSquare ( $destination_x, $destination_y, $board, $N )    )
        return true;


    return false;
}



//
//  GET SELECTED ORIGIN AND SELECTABLE DESTINATIONS MOVE B
//  ######################################################
//
//  SELECTED_X, SELECTED_Y 
//
//  PLAYED DIE
//
//  For the 4 vectors 
//      If destination is friendly stack
//          Add to legal moves 
//      Else if destination is smaller enemy stack
//          Add to list of legal moves 
//      Else if destination is unoccupied square AND move A was not to unoccupied square 
//          Add to list of legal moves 
//          
//          
//
function getSelectedOrigin_SelectableDestinationsMoveB ( $player_id )
{                       
    //
    //  SELECTED ORIGIN 
    //
    $selected_origin = self::getSelectedOriginCoords ( );

    $selected_x = $selected_origin [ 0 ];
    $selected_y = $selected_origin [ 1 ];


    $other_player_id = self::getOtherPlayerId ( $player_id );


    $legal_moves = array();

    $board = self::getBoard();

    $checker_IDs = self::getCheckerIDs();

    $selected_checker_id = self::getCheckerID ( $selected_x, $selected_y );

    $selected_stack_height = self::getStackHeight ( $selected_checker_id );



    //
	//Get the board size
    //
    $N=self::getGameStateValue("board_size");
                  


    //
    // GET UNPLAYED DIE, NOT PLAYED IN MOVE A 
    //
    $die_0 = self::getGameStateValue ( "rolled_die_0");

    $die_1 = self::getGameStateValue ( "rolled_die_1");


    $played_die = self::getGameStateValue ("played_die");

    if ( $played_die == $die_0 )
    {
        $unplayed_die = $die_1;
    }
    else 
    {
        $unplayed_die = $die_0;
    }


    //
    // X + unplayed_die, Y
    //
    if (    self::isSelectableDestination_B ($selected_x + $unplayed_die, $selected_y, $selected_stack_height, $board, $checker_IDs, $player_id, $other_player_id, $N )    )
    {
            if(    ! isset ( $legal_moves [ $selected_x + $unplayed_die ] )    )
                $legal_moves [ $selected_x + $unplayed_die ] = array();

            $legal_moves [ $selected_x + $unplayed_die ] [ $selected_y ] = true;
    }

    //
    // X - unplayed_die, Y
    //
    if (    self::isSelectableDestination_B ($selected_x - $unplayed_die, $selected_y, $selected_stack_height, $board, $checker_IDs, $player_id, $other_player_id, $N )    )
    {
            if(    ! isset ( $legal_moves [ $selected_x - $unplayed_die ] )    )
                $legal_moves [ $selected_x - $unplayed_die ] = array();

            $legal_moves [ $selected_x - $unplayed_die ] [ $selected_y ] = true;
    }

    //
    // X, Y + unplayed_die
    //
    if (    self::isSelectableDestination_B ($selected_x, $selected_y + $unplayed_die, $selected_stack_height, $board, $checker_IDs, $player_id, $other_player_id, $N )    )
    {
            if(    ! isset ( $legal_moves [ $selected_x ] )    )
                $legal_moves [ $selected_x ] = array();

            $legal_moves [ $selected_x ] [ $selected_y + $unplayed_die ] = true;
    }

    //
    // X, Y - unplayed_die
    //
    if (    self::isSelectableDestination_B ($selected_x, $selected_y - $unplayed_die, $selected_stack_height, $board, $checker_IDs, $player_id, $other_player_id, $N )    )
    {
            if(    ! isset ( $legal_moves [ $selected_x ] )    )
                $legal_moves [ $selected_x ] = array();

            $legal_moves [ $selected_x ] [ $selected_y - $unplayed_die ] = true;
    }


 
    $selectedOrigin_selectableDestinationsMoveA = array ( $selected_origin, $legal_moves );

    return $selectedOrigin_selectableDestinationsMoveA;
}




//
//  IS SELECTABLE DESTINATION B
//  ###########################
//
//              
//
function isSelectableDestination_B ($destination_x, $destination_y, $selected_stack_height, $board, $checker_IDs, $player_id, $other_player_id, $N )
{            
    //
    //  FRIENDLY STACK DESTINATION OK 
    //
    if (    self::isFriendlyStack ( $destination_x, $destination_y, $board, $player_id, $N )    )
        return true;

    //
    //  EQUAL OR SMALLER ENEMY STACK DESTINATION OK 
    //
    if (    self::isSmallerEnemyStack ( $destination_x, $destination_y, $selected_stack_height, $board, $checker_IDs, $other_player_id, $N )    )
        return true;


    //
    //  IF MOVE A WAS NOT TO UNOCCUPIED SQUARE 
    //
    //      UNOCCUPIED SQUARE DESTINATION OK
    //
    $was_move_A_to_unoccupied = self::getGameStateValue ("was_move_A_to_unoccupied");

    if ( ! $was_move_A_to_unoccupied )                                                  // MOVE_A NOT TO UNOCCUPIED SQUARE
    {
        if (    self::isUnoccupiedSquare ( $destination_x, $destination_y, $board, $N )    )
            return true;
    }


    return false;
}




//
//  getRemovableCheckers ( $active_player_id ) 
//  ##########################################
//
//    
//  Return array of all friendly stacks
//
//
//
function getRemovableCheckers ( $active_player_id )
{                       
    $removable_checkers = array();

    $board = self::getBoard();

    $N = self::getGameStateValue ( "board_size" );


    //
    // FIND ALL FRIENDLY STACKS
    //
    for ( $y = 0; $y < $N; $y++ )                       // Start on bottom row.  $y = 0
    {
        for ( $x = 0; $x < $N; $x++ )                   
        {
            if (    $board [ $x ] [ $y ] == $active_player_id     )   // FRIENDLY STACK             
            {
                if(    ! isset ( $removable_checkers [ $x ] )    )
                    $removable_checkers [ $x ] = array();

                $removable_checkers [ $x ] [ $y ] = true;
            }
        }
    }


    return $removable_checkers;
}




function getStackHeight ( $checker_id )
{
    return floor (    ( $checker_id % 100000 ) / 1000    );
}


function isSquareOnBoard  ($x, $y, $N )
{    
    if ( $x >= 0 && $x < $N && $y >= 0 && $y < $N )
    {
        return true;
    }
    else 
    {
        return false;
    }
}


function isOccupiedSquare ( $x, $y, $board )
{
    if ( $board [ $x ] [ $y ] == NULL )
    {
        return false;
    }
    else 
    {
        return true;
    }
}


function getCheckerID ( $x, $y )
{      
    $sql = "SELECT checker_id checker_id FROM board WHERE ( board_x, board_y) = ( $x, $y )"; // should be only one

    $result = self::DbQuery( $sql );


    if ( $result->num_rows > 0 ) 
    {
        while (    $row = $result->fetch_assoc()    ) 
        {
            $checker_IDs [ ] = $row [ "checker_id" ];
        }
    } 

    $checker_id = $checker_IDs [0];

    return $checker_id;
}


function getSelectedOriginCoords ( )
{
    $selected_origin = array ( );


    $sql = "SELECT board_x x, board_y y FROM board WHERE is_origin_selected = 1";  // should be only one

    $result = self::DbQuery( $sql );

    $row = $result->fetch_assoc ( );

    if (    isset ( $row )    )
    {
        $selected_origin [ 0 ] = $row [ "x" ];
        $selected_origin [ 1 ] = $row [ "y" ];
    }

    return $selected_origin;
}


function getOccupant ( $x, $y, $board, $N )
{                       
    if ( $x >= 0 && $x < $N )                                                       
    {
        if ( $y >= 0 && $y < $N )    
        {
            return $board [ $x ] [ $y ];
        }
        else 
        {
            return NULL;
        }
    }
    else 
    {
        return NULL;
    }
}



function getBoard()
{
    return self::getDoubleKeyCollectionFromDB( "SELECT board_x x, board_y y, board_player player
                                                FROM board", true );
}


function getCheckerIDs()
{
    return self::getDoubleKeyCollectionFromDB( "SELECT board_x x, board_y y, checker_id checker_id
                                                FROM board", true );
}


public function getOtherPlayerId($player_id)
{
    $sql = "SELECT player_id FROM player WHERE player_id != $player_id";
    return $this->getUniqueValueFromDB( $sql );
}


function getColorName( $color )
{
    if ($color=='000000')
    {
        return clienttranslate('BLACK');
    }
    else 
    {
        return clienttranslate('YELLOW');
    }
} 


function getPlayerColor ( $player_id ) 
{
    $players = self::loadPlayersBasicInfos();

    if (isset ( $players [ $player_id ] ) )
        return $players[ $player_id ][ 'player_color' ];
    else
        return null;
}




//
//  HAS MOVES - MOVE A
//  ##################
//
//  MOVES TO FRIENDLY STACKS 
//
//  For each of the 4 die_0 vectors (left, right, up, down)
//      If there is a friendly stack  
//          Return true
// 
//  For each of the 4 die_1 vectors 
//      If there is a friendly stack  
//          Return true 
//
// 
//  MOVES TO EQUAL OR SMALLER ENEMY STACKS 
//
//  For each of the 4 die_0 vectors 
//      If there is an equal or smaller enemy stack  
//          Return true
// 
//  For each of the 4 die_1 vectors 
//      If there is an equal or smaller enemy stack  
//          Return true
//
//
//  MOVES TO UNOCCUPIED SQUARES
//
//  For each of the 4 die_0 vectors (left, right, up, down)
//      If there is an unoccupied square  
//          Return true
// 
//  For each of the 4 die_1 vectors 
//      If there is an unoccupied square  
//          Return true 
//
//
//
//  Return false 
//
function hasMoves_A ( $x, $y, $player_id, $board, $checker_IDs, $N, $die_0, $die_1 )
{            
    $other_player_id = self::getOtherPlayerId ( $player_id );

    $stack_height_x_y = self::getStackHeight ( $checker_IDs [ $x ] [ $y ] );


    //
    //  MOVE TO FRIENDLY STACK
    //
    if (    self::isFriendlyStack ( $x + $die_0, $y, $board, $player_id, $N )    )
        return true;

    if (    self::isFriendlyStack ( $x - $die_0, $y, $board, $player_id, $N )    )
        return true;

    if (    self::isFriendlyStack ( $x + $die_1, $y, $board, $player_id, $N )    )
        return true;

    if (    self::isFriendlyStack ( $x - $die_1, $y, $board, $player_id, $N )    )
        return true;

    if (    self::isFriendlyStack ( $x, $y + $die_0, $board, $player_id, $N )    )
        return true;

    if (    self::isFriendlyStack ( $x, $y - $die_0, $board, $player_id, $N )    )
        return true;

    if (    self::isFriendlyStack ( $x, $y + $die_1, $board, $player_id, $N )    )
        return true;

    if (    self::isFriendlyStack ( $x, $y - $die_1, $board, $player_id, $N )    )
        return true;



    //
    //  MOVE TO EQUAL OR SMALLER ENEMY STACK
    //
    if (    self::isSmallerEnemyStack ( $x + $die_0, $y, $stack_height_x_y, $board, $checker_IDs, $other_player_id, $N )    )
        return true;

    if (    self::isSmallerEnemyStack ( $x - $die_0, $y, $stack_height_x_y, $board, $checker_IDs, $other_player_id, $N )    )
        return true;

    if (    self::isSmallerEnemyStack ( $x + $die_1, $y, $stack_height_x_y, $board, $checker_IDs, $other_player_id, $N )    )
        return true;

    if (    self::isSmallerEnemyStack ( $x - $die_1, $y, $stack_height_x_y, $board, $checker_IDs, $other_player_id, $N )    )
        return true;

    if (    self::isSmallerEnemyStack ( $x, $y + $die_0, $stack_height_x_y, $board, $checker_IDs, $other_player_id, $N )    )
        return true;

    if (    self::isSmallerEnemyStack ( $x, $y - $die_0, $stack_height_x_y, $board, $checker_IDs, $other_player_id, $N )    )
        return true;

    if (    self::isSmallerEnemyStack ( $x, $y + $die_1, $stack_height_x_y, $board, $checker_IDs, $other_player_id, $N )    )
        return true;

    if (    self::isSmallerEnemyStack ( $x, $y - $die_1, $stack_height_x_y, $board, $checker_IDs, $other_player_id, $N )    )
        return true;




    //
    //  MOVE TO UNOCCUPIED SQUARE
    //
    if (    self::isUnoccupiedSquare ( $x + $die_0, $y, $board, $N )    )
        return true;

    if (    self::isUnoccupiedSquare ( $x - $die_0, $y, $board, $N )    )
        return true;

    if (    self::isUnoccupiedSquare ( $x + $die_1, $y, $board, $N )    )
        return true;

    if (    self::isUnoccupiedSquare ( $x - $die_1, $y, $board, $N )    )
        return true;

    if (    self::isUnoccupiedSquare ( $x, $y + $die_0, $board, $N )    )
        return true;

    if (    self::isUnoccupiedSquare ( $x, $y - $die_0, $board, $N )    )
        return true;

    if (    self::isUnoccupiedSquare ( $x, $y + $die_1, $board, $N )    )
        return true;

    if (    self::isUnoccupiedSquare ( $x, $y - $die_1, $board, $N )    )
        return true;


    return false;
}



//
//  HAS MOVES - MOVE B
//  ##################
//
//  If (x,y) has move to friendly stack 
//      Return true 
//
//  If (x,y) has move to equal or smaller enemy stack 
//      Return true 
//
//  If Move A was not to unoccupied square 
//      If (x,y) has move to unoccupied square 
//          Return true 
//
//  Return false 
//
//
//          
function hasMoves_B ( $x, $y, $player_id, $board, $checker_IDs, $N, $unplayed_die )
{    
    $other_player_id = self::getOtherPlayerId ( $player_id );
        
    $was_move_A_to_unoccupied = self::getGameStateValue ("was_move_A_to_unoccupied");


    //
    //  MOVE TO FRIENDLY STACK OK
    //
    if (    self::isFriendlyStack ( $x + $unplayed_die, $y, $board, $player_id, $N )    )
        return true;

    if (    self::isFriendlyStack ( $x - $unplayed_die, $y, $board, $player_id, $N )    )
        return true;

    if (    self::isFriendlyStack ( $x, $y + $unplayed_die, $board, $player_id, $N )    )
        return true;

    if (    self::isFriendlyStack ( $x, $y - $unplayed_die, $board, $player_id, $N )    )
        return true;


    //
    //  MOVE TO EQUAL OR SMALLER ENEMY STACK OK
    //
    $stack_height_x_y = self::getStackHeight ( $checker_IDs [ $x ] [ $y ] );

    if (    self::isSmallerEnemyStack ( $x + $unplayed_die, $y, $stack_height_x_y, $board, $checker_IDs, $other_player_id, $N )    )
        return true;

    if (    self::isSmallerEnemyStack ( $x - $unplayed_die, $y, $stack_height_x_y, $board, $checker_IDs, $other_player_id, $N )    )
        return true;

    if (    self::isSmallerEnemyStack ( $x, $y + $unplayed_die, $stack_height_x_y, $board, $checker_IDs, $other_player_id, $N )    )
        return true;

    if (    self::isSmallerEnemyStack ( $x, $y - $unplayed_die, $stack_height_x_y, $board, $checker_IDs, $other_player_id, $N )    )
        return true;



    //
    //  IF MOVE A WAS NOT TO UNOCCUPIED 
    //
    //      MOVE TO UNOCCUPIED OK
    //
    if (    ! $was_move_A_to_unoccupied    )
    {
        if (    self::isUnoccupiedSquare ( $x + $unplayed_die, $y, $board, $N)    )
            return true;

        if (    self::isUnoccupiedSquare ( $x - $unplayed_die, $y, $board, $N)    )
            return true;

        if (    self::isUnoccupiedSquare ( $x, $y + $unplayed_die, $board, $N)    )
            return true;

        if (    self::isUnoccupiedSquare ( $x, $y - $unplayed_die, $board, $N)    )
            return true;
    }


    return false;
}



function isFriendlyStack ( $offset_x, $offset_y, $board, $player_id, $N )
{    
    if (    self::isSquareOnBoard ( $offset_x, $offset_y, $N )    )
    {
        if (    $board [ $offset_x ] [ $offset_y ] == $player_id    )
        {
            return true;
        }
    }

    return false;
}



function isSmallerEnemyStack ( $offset_x, $offset_y, $stack_height_x_y, $board, $checker_IDs, $other_player_id, $N )
{    
    if (    self::isSquareOnBoard ( $offset_x, $offset_y, $N )    )
    {
        if ( $board [ $offset_x ] [ $offset_y ] == $other_player_id )   // ENEMY STACK 
        {
            $stack_height_at_offset = self::getStackHeight ( $checker_IDs [ $offset_x ] [ $offset_y ] );


            if ( $stack_height_at_offset <= $stack_height_x_y )         // EQUAL OR SMALLER STACK
            {
                return true;
            }
        }
    }


    return false;
}



function isUnoccupiedSquare ( $x, $y, $board, $N )
{    
    if (    self::isSquareOnBoard ( $x, $y, $N )    )
    {
        if (    $board [ $x ] [ $y ] == NULL    )
        {
            return true;
        }
    }

    return false;
}



//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

//
//  SELECT ORIGIN FIRST TURN
//  ########################
//
//
//  If it's a stack that has moves
//      Mark as selected in database 
//      Notify frontend to highlight the selected stack
//      State change: selectOriginFirstTurn => selectDestinationFirstTurn
//      
//
function selectOriginFirstTurn ( $x, $y )
{
    self::checkAction( 'selectOriginFirstTurn' );  


    //
    // Check if this is a selectable origin 
    //
    if (    ($x + $y) % 2 == 1    ) // LIGHT SQUARE, BLACK OCCUPIED
    {
        $sql = "UPDATE board SET is_origin_selected = 1 WHERE ( board_x, board_y) = ($x, $y)";  
    
        self::DbQuery( $sql );

        
        //
        // NOTIFY FRONTEND OF SELECTED ORIGIN
        //
        self::notifyAllPlayers( "originSelected", clienttranslate( '' ), 
            array(
            'x' => $x,
            'y' => $y,
            ) );
    

        //     
        // ADVANCE TO NEXT STATE: selectOriginFirstTurn => selectDestinationFirstTurn
        //
        $this->gamestate->nextState( 'selectOriginFirstTurn' );   
            
    }
    else
    {
        throw new feException( "Not a valid origin stack." );
    }
}





//
//  SELECT DESTINATION FIRST TURN 
//
//
//  Get selected origin coords
//
//  Set is_selected to 0
//
//  Delete the selected origin stack from the database 
//
//  If destination is a light colored square, and therefore black occupied
//      Change the value of the destination stack to black 2 in database
//      Notify the frontend to 
//          Remove the origin stack 
//          Remove the black destination stack
//          Slide a new black 2 to the destination square
//
//  Else (destination stack is dark colored square, and therefore yellow occupied)
//      Change the value of the destination stack to origin checker ID in database
//      Notify the frontend to 
//          Remove the yellow destination stack
//          Reduce the number of yellow checkers global
//          Slide the black 1 from the origin to the destination
//      
//
//  State change: selectDestinationFirstTurn => nextPlayer
//   
//
function selectDestinationFirstTurn ( $x, $y )
{
    self::checkAction( 'selectDestinationFirstTurn' ); 
    

    $active_player_id = self::getActivePlayerId();  // BLACK IS ACTIVE PLAYER ON FIRST TURN

    $inactive_player_id = self::getOtherPlayerId ( $active_player_id );

    //$player_color = self::getPlayerColor ( $active_player_id );

	//Get the board size
    $N=self::getGameStateValue("board_size");

    $board = self::getBoard ( );


    $selected_origin = self::getSelectedOriginCoords ( );

    $selected_x = $selected_origin [ 0 ];
    $selected_y = $selected_origin [ 1 ];

    $origin_checker_id = self::getCheckerID ( $selected_x, $selected_y );


    // 
    // HISTORY PANEL MOVE INFORMATION
    //
    $distance_moved_x = abs ( $selected_x - $x );

    $distance_moved_y = abs ( $selected_y - $y );


    $played_die = max ( $distance_moved_x, $distance_moved_y );


    $x_from_coord = chr( ord( "A" ) + $selected_x );

    $y_from_coord = $selected_y + 1;


    $x_to_coord = chr( ord( "A" ) + $x );

    $y_to_coord = $y + 1;


    //
    // UNSELECT ORIGIN IN DATABASE                          
    //                                                      
    $sql = "UPDATE board SET is_origin_selected = 0 WHERE 1";                                      
                                                            
    self::DbQuery( $sql );                                  


    // 
    //  GET OLD DESTINATION CHECKER ID FOR FRONTEND REMOVAL 
    //
    $old_destination_checker_id = self::getCheckerID ($x,$y);


    //
    // REMOVE BLACK CHECKER FROM ORIGIN IN DATABASE 
    //
    $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( $selected_x, $selected_y )";  
    
    self::DbQuery( $sql );


    if (    ($x + $y) % 2 == 1    )     // DESTINATION LIGHT COLORED SQUARE, BLACK CHECKER 
    {
        //
        //  GET A NEW BLACK CHECKER ID, STACK HEIGHT 2
        // 
        //  SUBSTITUTE INTO DESTINATION SQUARE IN DATABASE 
        //
        $new_destination_checker_id = self::getGameStateValue ("black_checker_base_id");
        self::incGameStateValue ("black_checker_base_id", 1);

        $new_destination_checker_id += 2000;    // BLACK CHECKER, STACK HEIGHT 2

        $sql = "UPDATE board SET checker_id = $new_destination_checker_id WHERE ( board_x, board_y ) = ( $x, $y )";  
    
        self::DbQuery( $sql );


        // 
        //  NOTIFY FRONTEND 
        //
        //      ORIGIN CHECKER REMOVED 
        //      OLD DESTINATION BLACK STACK REMOVED
        //      NEW DESTINATION BLACK STACK PLACED 
        //
        self::notifyAllPlayers( "checkerRemoved", clienttranslate( '' ), 
            array(
            'removed_checker_id' => $origin_checker_id,
            'player_id' => $active_player_id
            ) );

        self::notifyAllPlayers( "checkerRemoved", clienttranslate( '' ), 
            array(
            'removed_checker_id' => $old_destination_checker_id,
            'player_id' => $active_player_id
            ) );

        self::notifyAllPlayers( "checkerPlaced", clienttranslate( '' ), 
            array(
            'x' => $x,
            'y' => $y,
            'player_id' => $active_player_id,
            'placed_checker_id' => $new_destination_checker_id,
            'N' => $N
            ) );



        // 
        // UPDATE HISTORY PANEL ABOUT FRIENDLY MERGE
        //
        self::notifyAllPlayers( "playerHistory", clienttranslate( '${player_name}: ${played_die} = ${x_from_coord}${y_from_coord}-${x_to_coord}${y_to_coord}' ),     
                array(
                    'player_name' => self::getActivePlayerName(),
                    'played_die' => $played_die,
                    'x_from_coord' => $x_from_coord,
                    'y_from_coord' => $y_from_coord,
                    'x_to_coord' => $x_to_coord,
                    'y_to_coord' => $y_to_coord
                ) );
    }
    else                                // DESTINATION DARK COLORED SQUARE, YELLOW CHECKER 
    {
        // 
        //  DECRIMENT NUMBER OF YELLOW CHECKERS 
        //
        self::incGameStateValue ("number_of_yellow_checkers", -1);

        //
        //  PUT ORIGIN BLACK CHECKER ID INTO DESTINATION SQUARE IN DATABASE 
        //
        $sql = "UPDATE board SET board_player = $active_player_id, checker_id = $origin_checker_id WHERE ( board_x, board_y ) = ( $x, $y )";  
    
        self::DbQuery( $sql );


        // 
        //  NOTIFY FRONTEND 
        //
        //      DESTINATION YELLOW STACK REMOVED
        //      ORIGIN BLACK STACK SLID TO DESTINATION
        //
        self::notifyAllPlayers( "checkerRemoved", clienttranslate( '' ), 
            array(
            'removed_checker_id' => $old_destination_checker_id,
            'player_id' => $inactive_player_id
            ) );

        self::notifyAllPlayers( "checkerSlid", clienttranslate( '' ), 
            array(
            'destination_x' => $x,
            'destination_y' => $y,
            'slid_checker_id' => $origin_checker_id
            ) );


        //
        //  UPDATE PLAYER PANEL FOR YELLOW
        //
        self::notifyAllPlayers("playerPanel",
            "",
            array(
            "number_of_checkers" => self::getGameStateValue( "number_of_yellow_checkers" ),
            "player_id" => $inactive_player_id 
            ));



        // 
        // UPDATE HISTORY PANEL ABOUT ENEMY CAPTURE (!)
        //
        self::notifyAllPlayers( "playerHistory", clienttranslate( '${player_name}: ${played_die} = ${x_from_coord}${y_from_coord}-${x_to_coord}${y_to_coord} !' ),     
                array(
                    'player_name' => self::getActivePlayerName(),
                    'played_die' => $played_die,
                    'x_from_coord' => $x_from_coord,
                    'y_from_coord' => $y_from_coord,
                    'x_to_coord' => $x_to_coord,
                    'y_to_coord' => $y_to_coord
                ) );
    }



    //
    // LAST MOVE INDICATOR
    //
    self::setGameStateValue ("last_move_indicator_x", $x);
    self::setGameStateValue ("last_move_indicator_y", $y);

    self::incGameStateValue ("number_of_moves", 1);
    //self::incGameStateValue ("last_move_indicator_id", 1);




    //
    // NOTIFY FRONTEND ABOUT FIRST MOVE 
    //
    self::notifyAllPlayers( "addLastMoveIndicator", clienttranslate( '' ), 
        array(
        'x' => $x,
        'y' => $y,
        'id' => self::getGameStateValue ("last_move_indicator_id"),
        'number_of_moves' => self::getGameStateValue ("number_of_moves")
        ) );




    //
    // Go to the next state: selectDestination => nextPlayer
    //
    $this->gamestate->nextState( 'selectDestinationFirstTurn' );
}




//
//  SELECT ORIGIN MOVE A
//  ####################
//
//
//  If it's a stack that has moves
//      Mark as selected in database 
//      Notify frontend to highlight the selected stack
//      State change: selectOriginMoveA => selectDestinationMoveA
//      
//
function selectOriginMoveA ( $x, $y )
{
    self::checkAction( 'selectOriginMoveA' );  


    $active_player_id = self::getActivePlayerId(); 


    $board = self::getBoard();

    $checker_IDs = self::getCheckerIDs();


    $N = self::getGameStateValue ( "board_size" );


    $die_0 = self::getGameStateValue ("rolled_die_0");

    $die_1 = self::getGameStateValue ("rolled_die_1");



    //
    //  CHECK IF THIS IS A SELECTABLE ORIGIN
    //
    if (    self::hasMoves_A ( $x, $y, $active_player_id, $board, $checker_IDs, $N, $die_0, $die_1 )    )
    {
        /*
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'selectOriginMoveA - hasMoves_A' ), 
        array(
                
            ) );
        */

        $sql = "UPDATE board SET is_origin_selected = 1 WHERE ( board_x, board_y) = ($x, $y)";  
    
        self::DbQuery( $sql );

        
        //
        // NOTIFY FRONTEND OF SELECTED ORIGIN
        //
        self::notifyAllPlayers( "originSelected", clienttranslate( '' ), 
            array(
            'x' => $x,
            'y' => $y,
            ) );
    

        //     
        // ADVANCE TO NEXT STATE: selectOriginMoveA => selectDestinationMoveA
        //
        $this->gamestate->nextState( 'selectOriginMoveA' );               
    }
    else
    {
        throw new feException( "Not a valid origin stack." );
    }
}





//
//  SELECT DESTINATION MOVE A 
//
//
//  Get selected origin coords
//
//  Set is_selected to 0
//
//  Delete the selected origin stack from the database 
//
//  If destination player_id == active player id
//      Create new stack.  Height = origin stack height + destination stack height.  Update checker_id in database 
//
//      Set globals was_move_A_to_unoccupied = 0                    
//
//      Notify the frontend to... 
//          Remove the origin stack 
//          Remove the destination stack
//          Slide a new stack (total height, color = active player color) to the destination square
//
//  Else if destination player_id == inactive player id
//      Add active player ID and origin checker ID to destination in database
//
//      Set globals was_move_A_to_unoccupied = 0                    
//
//      Notify the frontend to... 
//          Remove the destination stack
//          Reduce the number of destination color checkers global
//          Slide the stack from the origin to the destination
//
//      If zero enemy checkers 
//          End game
//      
//  Else (destination player_id == NULL) ... unoccupied square 
//      Add active player ID and origin checker ID to destination in database
//
//      Set globals was_move_A_to_unoccupied = 1                    
//
//      Notify the frontend to... 
//          Slide the stack from the origin to the destination
//
//
//  If there are moves available for move B 
//      State change: selectDestinationMoveA => selectOriginMoveB
//  Else (no moves available for move B) 
//      State change: selectDestinationMoveA_noMovesB => removeCheckerMoveB
//
//
//
function selectDestinationMoveA ( $x, $y )
{
    self::checkAction( 'selectDestinationMoveA' ); 
    

    $active_player_id = self::getActivePlayerId();

    $active_player_color = self::getPlayerColor ( $active_player_id );


    $inactive_player_id = self::getOtherPlayerId ( $active_player_id );


    $N=self::getGameStateValue("board_size");


    $board = self::getBoard ( );

    $checker_IDs = self::getCheckerIDs ( );


    $selected_origin = self::getSelectedOriginCoords ( );

    $selected_x = $selected_origin [ 0 ];
    $selected_y = $selected_origin [ 1 ];


    $origin_checker_id = self::getCheckerID ( $selected_x, $selected_y );

    $origin_stack_height = self::getStackHeight ( $origin_checker_id );


    $old_destination_checker_id = self::getCheckerID ( $x, $y );

    $old_destination_stack_height = self::getStackHeight ( $old_destination_checker_id );


    // 
    // HISTORY PANEL MOVE INFORMATION
    //
    $distance_moved_x = abs ( $selected_x - $x );

    $distance_moved_y = abs ( $selected_y - $y );


    $played_die = max ( $distance_moved_x, $distance_moved_y );


    $x_from_coord = chr( ord( "A" ) + $selected_x );

    $y_from_coord = $selected_y + 1;


    $x_to_coord = chr( ord( "A" ) + $x );

    $y_to_coord = $y + 1;


    //
    // UNSELECT ORIGIN IN DATABASE                          
    //                                                      
    $sql = "UPDATE board SET is_origin_selected = 0 WHERE 1";                                      
                                                            
    self::DbQuery( $sql );                                  


    //
    // REMOVE SELECTED CHECKER ORIGIN STACK FROM DATABASE 
    //
    $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( $selected_x, $selected_y )";  
    
    self::DbQuery( $sql );


    $destination_player_id = $board [ $x ] [ $y ];


    if ( $destination_player_id == $active_player_id )                      // DESTINATION PLAYER ID == ACTIVE PLAYER ID
    {
        //
        //  CREATE NEW DESTINATION STACK 
        //
        if ( $active_player_color == "000000")
        {
            $new_destination_checker_id = self::getGameStateValue ( "black_checker_base_id");

            self::incGameStateValue ( "black_checker_base_id", 1);
        }
        else 
        {
            $new_destination_checker_id = self::getGameStateValue ( "yellow_checker_base_id");

            self::incGameStateValue ( "yellow_checker_base_id", 1);
        }
        
        $new_stack_height = $origin_stack_height + $old_destination_stack_height;

        $new_destination_checker_id += 1000 * $new_stack_height;


        // 
        //  SUBSTITUTE INTO DESTINATION SQUARE IN DATABASE 
        //
        $sql = "UPDATE board SET checker_id = $new_destination_checker_id WHERE ( board_x, board_y ) = ( $x, $y )";  
    
        self::DbQuery( $sql );


        // 
        //  NOTIFY FRONTEND 
        //
        //      ORIGIN CHECKER REMOVED 
        //      OLD DESTINATION BLACK STACK REMOVED
        //      NEW DESTINATION BLACK STACK PLACED 
        //
        self::notifyAllPlayers( "checkerRemoved", clienttranslate( '' ), 
            array(
            'removed_checker_id' => $origin_checker_id,
            'player_id' => $active_player_id
            ) );

        self::notifyAllPlayers( "checkerRemoved", clienttranslate( '' ), 
            array(
            'removed_checker_id' => $old_destination_checker_id,
            'player_id' => $active_player_id
            ) );

        self::notifyAllPlayers( "checkerPlaced", clienttranslate( '' ), 
            array(
            'x' => $x,
            'y' => $y,
            'player_id' => $active_player_id,
            'placed_checker_id' => $new_destination_checker_id,
            'N' => $N
            ) );


        // 
        // UPDATE HISTORY PANEL ABOUT FRIENDLY MERGE
        //
        self::notifyAllPlayers( "playerHistory", clienttranslate( '${player_name}: ${played_die} = ${x_from_coord}${y_from_coord}-${x_to_coord}${y_to_coord}' ),     
                array(
                    'player_name' => self::getActivePlayerName(),
                    'played_die' => $played_die,
                    'x_from_coord' => $x_from_coord,
                    'y_from_coord' => $y_from_coord,
                    'x_to_coord' => $x_to_coord,
                    'y_to_coord' => $y_to_coord
                ) );


        //
        //  SET was_move_A_to_unoccupied TO FALSE     
        //
        self::setGameStateValue ("was_move_A_to_unoccupied", 0);

    }
    else if ( $destination_player_id == $inactive_player_id )               // DESTINATION PLAYER ID == INACTIVE PLAYER ID 
    {
        // 
        //  DECRIMENT NUMBER OF ENEMY CHECKERS BY REMOVED STACK HEIGHT 
        //
        if ( $active_player_color == "000000")
        {
            self::incGameStateValue ("number_of_yellow_checkers", -$old_destination_stack_height);

            //
            //  UPDATE PLAYER PANEL FOR YELLOW
            //
            self::notifyAllPlayers("playerPanel",
                "",
                array(
                "number_of_checkers" => self::getGameStateValue( "number_of_yellow_checkers" ),
                "player_id" => $inactive_player_id 
                ));
        }
        else 
        {
            self::incGameStateValue ("number_of_black_checkers", -$old_destination_stack_height);

            //
            //  UPDATE PLAYER PANEL FOR BLACK
            //
            self::notifyAllPlayers("playerPanel",
                "",
                array(
                "number_of_checkers" => self::getGameStateValue( "number_of_black_checkers" ),
                "player_id" => $inactive_player_id 
                ));
        }


        //
        //  PUT ORIGIN CHECKER ID INTO DESTINATION SQUARE IN DATABASE 
        //
        $sql = "UPDATE board SET board_player = $active_player_id, checker_id = $origin_checker_id WHERE ( board_x, board_y ) = ( $x, $y )";  
    
        self::DbQuery( $sql );


        // 
        //  NOTIFY FRONTEND 
        //
        //      DESTINATION STACK REMOVED
        //      ORIGIN STACK SLID TO DESTINATION
        //
        self::notifyAllPlayers( "checkerRemoved", clienttranslate( '' ), 
            array(
            'removed_checker_id' => $old_destination_checker_id,
            'player_id' => $inactive_player_id
            ) );

        self::notifyAllPlayers( "checkerSlid", clienttranslate( '' ), 
            array(
            'destination_x' => $x,
            'destination_y' => $y,
            'slid_checker_id' => $origin_checker_id
            ) );


        // 
        // UPDATE HISTORY PANEL ABOUT ENEMY CAPTURE (*)
        //
        self::notifyAllPlayers( "playerHistory", clienttranslate( '${player_name}: ${played_die} = ${x_from_coord}${y_from_coord}-${x_to_coord}${y_to_coord} !' ),     
                array(
                    'player_name' => self::getActivePlayerName(),
                    'played_die' => $played_die,
                    'x_from_coord' => $x_from_coord,
                    'y_from_coord' => $y_from_coord,
                    'x_to_coord' => $x_to_coord,
                    'y_to_coord' => $y_to_coord
                ) );


        //
        //  SET was_move_A_to_unoccupied TO FALSE     
        //
        self::setGameStateValue ("was_move_A_to_unoccupied", 0);

    }
    else                                                                    // DESTINATION PLAYER ID == NULL  ... UNOCCUPIED SQUARE  
    {
        //
        //  PUT ORIGIN CHECKER ID INTO DESTINATION SQUARE IN DATABASE 
        //
        $sql = "UPDATE board SET board_player = $active_player_id, checker_id = $origin_checker_id WHERE ( board_x, board_y ) = ( $x, $y )";  
    
        self::DbQuery( $sql );


        // 
        //  NOTIFY FRONTEND 
        //
        //      ORIGIN STACK SLID TO DESTINATION
        //
        self::notifyAllPlayers( "checkerSlid", clienttranslate( '' ), 
            array(
            'destination_x' => $x,
            'destination_y' => $y,
            'slid_checker_id' => $origin_checker_id
            ) );


        // 
        // UPDATE HISTORY PANEL ABOUT MOVE TO UNOCCIPIED SQUARE (_)
        //
        self::notifyAllPlayers( "playerHistory", clienttranslate( '${player_name}: ${played_die} = ${x_from_coord}${y_from_coord}-${x_to_coord}${y_to_coord} _' ),     
                array(
                    'player_name' => self::getActivePlayerName(),
                    'played_die' => $played_die,
                    'x_from_coord' => $x_from_coord,
                    'y_from_coord' => $y_from_coord,
                    'x_to_coord' => $x_to_coord,
                    'y_to_coord' => $y_to_coord
                ) );

        //
        //  SET was_move_A_to_unoccupied TO TRUE
        //
        self::setGameStateValue ("was_move_A_to_unoccupied", 1);

        //self::setGameStateValue ("move_A_x", $x);                       // DON'T NEED THIS NOW ######################################

        //self::setGameStateValue ("move_A_y", $y);                       // DON'T NEED THIS NOW ######################################
    }


    // 
    //  RECORD LAST MOVE MOVE IN GLOBALS
    //
    self::setGameStateValue ("last_move_indicator_x", $x);
    self::setGameStateValue ("last_move_indicator_y", $y);

    self::incGameStateValue ("number_of_moves", 1);


    //
    // NOTIFY FRONTEND ABOUT LAST MOVE 
    //
    self::notifyAllPlayers( "slideLastMoveIndicator", clienttranslate( '' ),   // SLIDE LAST MOVE INDICATOR FROM OLD POSITION
        array(
        'x' => $x,
        'y' => $y,
        'id' => self::getGameStateValue ("last_move_indicator_id")
        //'number_of_moves' => self::getGameStateValue ("number_of_moves")
        ) );


    //
    //  RECORD PLAYED DIE IN GLOBALS 
    //
    $die_0 = self::getGameStateValue ("rolled_die_0");

    $die_1 = self::getGameStateValue ("rolled_die_1");


    $hor_distance_moved = abs ( $selected_x - $x );

    $ver_distance_moved = abs ( $selected_y - $y );

    $positive_distance_moved = max ( $hor_distance_moved, $ver_distance_moved );


    if ( $positive_distance_moved == $die_0 )
    {
        self::setGameStateValue ("played_die", $die_0 );
    }
    else 
    {
        self::setGameStateValue ("played_die", $die_1 );
    }


    //
    //  CHECK FOR NO MORE INACTIVE PLAYER CHECKERS - GAME END 
    //
    if ( $active_player_color == "000000")  //  BLACK PLAYER ACTIVE
    {
        $number_of_inactive_player_checkers = self::getGameStateValue ( "number_of_yellow_checkers");  //  NUMBER OF YELLOW CHECKERS
    }
    else                                    //  YELLOW PLAYER ACTIVE
    {
        $number_of_inactive_player_checkers = self::getGameStateValue ( "number_of_black_checkers");  //  NUMBER OF BLACK CHECKERS
    }

    if ( $number_of_inactive_player_checkers == 0 ) 
    {
        // 
        //  END GAME 
        //
        //  ACTIVE PLAYER WINS
        //
        $sql = "UPDATE player
                SET player_score = 1 WHERE player_id = $active_player_id";
                self::DbQuery( $sql );


        $newScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );

        self::notifyAllPlayers( "newScores", "", array(
            "scores" => $newScores
        ) );

        $this->gamestate->nextState( 'endGame' );
    }


    //
    //  CHECK FOR AVAILABLE MOVES FOR MOVE B 
    //
    $selectableOriginsMoveB = self::getSelectableOriginsMoveB ( $active_player_id );

    if (    count ( $selectableOriginsMoveB ) == 0    )                 //  NO AVAILABLE MOVE_B
    {
        //
        // Go to the next state: selectDestinationMoveA_noMovesB => removeCheckerMoveB 
        //
        $this->gamestate->nextState( 'selectDestinationMoveA_noMovesB' );
    }
    else                                                                //  AVAILABLE MOVE_B
    {
        //
        // Go to the next state: selectDestinationMoveA => selectOriginMoveB 
        //
        $this->gamestate->nextState( 'selectDestinationMoveA' );
    }
}




//
//  SELECT ORIGIN MOVE B
//  ####################
//
//
//  If it's a stack that has moves
//      Mark as selected in database 
//      Notify frontend to highlight the selected stack
//      State change: selectOriginMoveB => selectDestinationMovesB
//      
//
function selectOriginMoveB ( $x, $y )
{
    self::checkAction( 'selectOriginMoveB' );  


    $active_player_id = self::getActivePlayerId(); 


    $board = self::getBoard();

    $checker_IDs = self::getCheckerIDs();


    $N = self::getGameStateValue ( "board_size" );


    $die_0 = self::getGameStateValue ("rolled_die_0");

    $die_1 = self::getGameStateValue ("rolled_die_1");


    //
    // GET UNPLAYED DIE, NOT PLAYED IN MOVE A 
    //
    $played_die = self::getGameStateValue ("played_die");

    if ( $played_die == $die_0 )
    {
        $unplayed_die = $die_1;
    }
    else 
    {
        $unplayed_die = $die_0;
    }



    //
    // Check if this is a selectable origin 
    //
    //if (    true    )
    if (    self::hasMoves_B ( $x, $y, $active_player_id, $board, $checker_IDs, $N, $unplayed_die )    )
    {
        /*
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'selectOriginMoveB - hasMoves_B' ), 
        array(
                
            ) );
        */

        $sql = "UPDATE board SET is_origin_selected = 1 WHERE ( board_x, board_y) = ($x, $y)";  
    
        self::DbQuery( $sql );

        
        //
        // NOTIFY FRONTEND OF SELECTED ORIGIN
        //
        self::notifyAllPlayers( "originSelected", clienttranslate( '' ), 
            array(
            'x' => $x,
            'y' => $y,
            ) );
    

        //     
        // ADVANCE TO NEXT STATE: selectOriginMoveB => selectDestinationMoveB
        //
        $this->gamestate->nextState( 'selectOriginMoveB' );   
            
    }
    else
    {
        throw new feException( "Not a valid origin stack." );
    }
}




//
//  SELECT DESTINATION MOVE B 
//  #########################
//
//
//  Get selected origin coords
//
//  Set is_selected to 0
//
//  Delete the selected origin stack from the database 
//
//  If destination player_id == active player id
//      Create new stack.  Height = origin stack height + destination stack height.  Update checker_id in database 
//
//      Notify the frontend to... 
//          Remove the origin stack 
//          Remove the destination stack
//          Slide a new stack (total height, color = active player color) to the destination square
//
//  Else (destination player_id == inactive player id)
//      Add active player ID and origin checker ID to destination in database
//
//      Notify the frontend to... 
//          Remove the destination stack
//          Reduce the number of destination color checkers global
//          Slide the stack from the origin to the destination
//      
//      Notify the frontend to... 
//          Slide the stack from the origin to the destination
//
//
//  State change: selectDestinationMoveB => nextPlayer
//   
//
function selectDestinationMoveB ( $x, $y )
{
    self::checkAction( 'selectDestinationMoveB' ); 
    

    $active_player_id = self::getActivePlayerId();

    $active_player_color = self::getPlayerColor ( $active_player_id );


    $inactive_player_id = self::getOtherPlayerId ( $active_player_id );


    $N=self::getGameStateValue("board_size");


    $board = self::getBoard ( );

    $checker_IDs = self::getCheckerIDs ( );


    $selected_origin = self::getSelectedOriginCoords ( );

    $selected_x = $selected_origin [ 0 ];
    $selected_y = $selected_origin [ 1 ];


    $origin_checker_id = self::getCheckerID ( $selected_x, $selected_y );

    $origin_stack_height = self::getStackHeight ( $origin_checker_id );


    $old_destination_checker_id = self::getCheckerID ( $x, $y );

    $old_destination_stack_height = self::getStackHeight ( $old_destination_checker_id );


    // 
    // HISTORY PANEL MOVE INFORMATION
    //
    $distance_moved_x = abs ( $selected_x - $x );

    $distance_moved_y = abs ( $selected_y - $y );


    $played_die = max ( $distance_moved_x, $distance_moved_y );


    $x_from_coord = chr( ord( "A" ) + $selected_x );

    $y_from_coord = $selected_y + 1;


    $x_to_coord = chr( ord( "A" ) + $x );

    $y_to_coord = $y + 1;


    //
    //  ####  RESET WAS MOVE_A TO UNOCCUPIED SQUARE  ####          
    //
    //  ###########  DO THIS AFTER MOVE A INSTEAD  ###########
    //
    //self::setGameStateValue ("was_move_A_to_unoccupied", 0);


    //
    // UNSELECT ORIGIN IN DATABASE                          
    //                                                      
    $sql = "UPDATE board SET is_origin_selected = 0 WHERE 1";                                      
                                                            
    self::DbQuery( $sql );                                  


    //
    // REMOVE SELECTED CHECKER ORIGIN STACK FROM DATABASE 
    //
    $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( $selected_x, $selected_y )";  
    
    self::DbQuery( $sql );


    $destination_player_id = $board [ $x ] [ $y ];


    if ( $destination_player_id == $active_player_id )                      // DESTINATION PLAYER ID == ACTIVE PLAYER ID
    {
        //
        //  CREATE NEW DESTINATION STACK 
        //
        if ( $active_player_color == "000000")
        {
            $new_destination_checker_id = self::getGameStateValue ( "black_checker_base_id");

            self::incGameStateValue ( "black_checker_base_id", 1);
        }
        else 
        {
            $new_destination_checker_id = self::getGameStateValue ( "yellow_checker_base_id");

            self::incGameStateValue ( "yellow_checker_base_id", 1);
        }
        
        $new_stack_height = $origin_stack_height + $old_destination_stack_height;

        $new_destination_checker_id += 1000 * $new_stack_height;


        // 
        //  SUBSTITUTE INTO DESTINATION SQUARE IN DATABASE 
        //
        $sql = "UPDATE board SET checker_id = $new_destination_checker_id WHERE ( board_x, board_y ) = ( $x, $y )";  
    
        self::DbQuery( $sql );


        // 
        //  NOTIFY FRONTEND 
        //
        //      ORIGIN CHECKER REMOVED 
        //      OLD DESTINATION BLACK STACK REMOVED
        //      NEW DESTINATION BLACK STACK PLACED 
        //
        self::notifyAllPlayers( "checkerRemoved", clienttranslate( '' ), 
            array(
            'removed_checker_id' => $origin_checker_id,
            'player_id' => $active_player_id
            ) );

        self::notifyAllPlayers( "checkerRemoved", clienttranslate( '' ), 
            array(
            'removed_checker_id' => $old_destination_checker_id,
            'player_id' => $active_player_id
            ) );

        self::notifyAllPlayers( "checkerPlaced", clienttranslate( '' ), 
            array(
            'x' => $x,
            'y' => $y,
            'player_id' => $active_player_id,
            'placed_checker_id' => $new_destination_checker_id,
            'N' => $N
            ) );


        // 
        // UPDATE HISTORY PANEL ABOUT FRIENDLY MERGE
        //
        self::notifyAllPlayers( "playerHistory", clienttranslate( '${player_name}: ${played_die} = ${x_from_coord}${y_from_coord}-${x_to_coord}${y_to_coord}' ),     
                array(
                    'player_name' => self::getActivePlayerName(),
                    'played_die' => $played_die,
                    'x_from_coord' => $x_from_coord,
                    'y_from_coord' => $y_from_coord,
                    'x_to_coord' => $x_to_coord,
                    'y_to_coord' => $y_to_coord
                ) );
    }
    //else      // DESTINATION PLAYER ID == INACTIVE PLAYER ID   #### BUG #### Have to specify inactive player id in else if.  Then include option for unoccpied square.
    else if ( $destination_player_id == $inactive_player_id )     // DESTINATION PLAYER ID == INACTIVE PLAYER ID  
    {
        // 
        //  DECRIMENT NUMBER OF ENEMY CHECKERS BY REMOVED STACK HEIGHT 
        //
        if ( $active_player_color == "000000")
        {
            self::incGameStateValue ("number_of_yellow_checkers", -$old_destination_stack_height);

            //
            //  UPDATE PLAYER PANEL FOR YELLOW
            //
            self::notifyAllPlayers("playerPanel",
                "",
                array(
                "number_of_checkers" => self::getGameStateValue( "number_of_yellow_checkers" ),
                "player_id" => $inactive_player_id 
                ));
        }
        else 
        {
            self::incGameStateValue ("number_of_black_checkers", -$old_destination_stack_height);

            //
            //  UPDATE PLAYER PANEL FOR BLACK
            //
            self::notifyAllPlayers("playerPanel",
                "",
                array(
                "number_of_checkers" => self::getGameStateValue( "number_of_black_checkers" ),
                "player_id" => $inactive_player_id 
                ));
        }


        //
        //  PUT ORIGIN CHECKER ID INTO DESTINATION SQUARE IN DATABASE 
        //
        $sql = "UPDATE board SET board_player = $active_player_id, checker_id = $origin_checker_id WHERE ( board_x, board_y ) = ( $x, $y )";  
    
        self::DbQuery( $sql );


        // 
        //  NOTIFY FRONTEND 
        //
        //      DESTINATION STACK REMOVED
        //      ORIGIN STACK SLID TO DESTINATION
        //
        self::notifyAllPlayers( "checkerRemoved", clienttranslate( '' ), 
            array(
            'removed_checker_id' => $old_destination_checker_id,
            'player_id' => $inactive_player_id
            ) );

        self::notifyAllPlayers( "checkerSlid", clienttranslate( '' ), 
            array(
            'destination_x' => $x,
            'destination_y' => $y,
            'slid_checker_id' => $origin_checker_id
            ) );



        // 
        // UPDATE HISTORY PANEL ABOUT ENEMY CAPTURE (!)
        //
        self::notifyAllPlayers( "playerHistory", clienttranslate( '${player_name}: ${played_die} = ${x_from_coord}${y_from_coord}-${x_to_coord}${y_to_coord} !' ),     
                array(
                    'player_name' => self::getActivePlayerName(),
                    'played_die' => $played_die,
                    'x_from_coord' => $x_from_coord,
                    'y_from_coord' => $y_from_coord,
                    'x_to_coord' => $x_to_coord,
                    'y_to_coord' => $y_to_coord
                ) );
    }
    else      // DESTINATION UNOCCUPED SQUARE  
    {
        //
        //  PUT ORIGIN CHECKER ID INTO DESTINATION SQUARE IN DATABASE 
        //
        $sql = "UPDATE board SET board_player = $active_player_id, checker_id = $origin_checker_id WHERE ( board_x, board_y ) = ( $x, $y )";  
    
        self::DbQuery( $sql );


        // 
        //  NOTIFY FRONTEND 
        //
        //      ORIGIN STACK SLID TO DESTINATION
        //
        self::notifyAllPlayers( "checkerSlid", clienttranslate( '' ), 
            array(
            'destination_x' => $x,
            'destination_y' => $y,
            'slid_checker_id' => $origin_checker_id
            ) );

        // 
        // UPDATE HISTORY PANEL ABOUT MOVE TO UNOCCUPIED SQUARE
        //
        self::notifyAllPlayers( "playerHistory", clienttranslate( '${player_name}: ${played_die} = ${x_from_coord}${y_from_coord}-${x_to_coord}${y_to_coord}' ),     
                array(
                    'player_name' => self::getActivePlayerName(),
                    'played_die' => $played_die,
                    'x_from_coord' => $x_from_coord,
                    'y_from_coord' => $y_from_coord,
                    'x_to_coord' => $x_to_coord,
                    'y_to_coord' => $y_to_coord
                ) );
    }



    // 
    //  RECORD LAST MOVE MOVE IN GLOBALS
    //
    self::setGameStateValue ("last_move_indicator_x", $x);
    self::setGameStateValue ("last_move_indicator_y", $y);

    self::incGameStateValue ("number_of_moves", 1);



    //
    // NOTIFY FRONTEND ABOUT LAST MOVE 
    //
    self::notifyAllPlayers( "slideLastMoveIndicator", clienttranslate( '' ),   // SLIDE LAST MOVE INDICATOR FROM OLD POSITION
        array(
        'x' => $x,
        'y' => $y,
        'id' => self::getGameStateValue ("last_move_indicator_id")
        //'number_of_moves' => self::getGameStateValue ("number_of_moves")
        ) );



    //
    //  CHECK FOR NO MORE INACTIVE PLAYER CHECKERS - GAME END 
    //
    if ( $active_player_color == "000000")  //  BLACK PLAYER ACTIVE
    {
        $number_of_inactive_player_checkers = self::getGameStateValue ( "number_of_yellow_checkers");  //  NUMBER OF YELLOW CHECKERS
    }
    else                                    //  YELLOW PLAYER ACTIVE
    {
        $number_of_inactive_player_checkers = self::getGameStateValue ( "number_of_black_checkers");  //  NUMBER OF BLACK CHECKERS
    }

    if ( $number_of_inactive_player_checkers == 0 ) 
    {
        // 
        //  END GAME 
        //
        //  ACTIVE PLAYER WINS
        //
        $sql = "UPDATE player
                SET player_score = 1 WHERE player_id = $active_player_id";
                self::DbQuery( $sql );


        $newScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );

        self::notifyAllPlayers( "newScores", "", array(
            "scores" => $newScores
        ) );

        $this->gamestate->nextState( 'endGame' );
    }



    //
    // Go to the next state: selectDestinationMoveB => nextPlayer
    //
    $this->gamestate->nextState( 'selectDestinationMoveB' );
}




//
//  REMOVE CHECKER MOVE A 
//  #####################
//
//
//  Get old stack height 
//
//  Remove old stack in database 
//
//  Notify frontend to remove old stack
//
//  Decriment number of checkers for active player color
//
//  Notify frontend to decriment number of checkers in player panel
//
//
//  If old stack height > 1 
//      Make new shorter stack
//      Add to database 
//      Notify frontend about added stack
//      State change: removeCheckerMoveA => removeCheckerMoveB
//
//  Else (removed stack was height 1)
//      If number of active player checkers == 0
//          End game
//
//      Else (at least 1 active player checker remaining)
//          State change: removeCheckerMoveA => removeCheckerMoveB
//   
//
function removeCheckerMoveA ( $x, $y )
{
    self::checkAction( 'removeCheckerMoveA' ); 
    

    $active_player_id = self::getActivePlayerId();

    $active_player_color = self::getPlayerColor ( $active_player_id );


    $inactive_player_id = self::getOtherPlayerId ( $active_player_id );


    $N=self::getGameStateValue("board_size");


    $board = self::getBoard ( );

    $checker_IDs = self::getCheckerIDs ( );


    $old_checker_id = self::getCheckerID ( $x, $y );

    $old_stack_height = self::getStackHeight ( $old_checker_id );



    //
    // REMOVE CHECKER STACK FROM DATABASE 
    //
    $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( $x, $y )";  
    
    self::DbQuery( $sql );

    // 
    //  NOTIFY FRONTEND TO REMOVE CHECKER
    //
    self::notifyAllPlayers( "checkerRemoved", clienttranslate( '' ), 
        array(
        'removed_checker_id' => $old_checker_id,
        'player_id' => $active_player_id
        ) );

    //
    //  DECRIMENT NUMBER OF CHECKERS FOR ACTIVE PLAYER
    //
    if ( $active_player_color == "000000")
    {
        self::incGameStateValue ( "number_of_black_checkers", -1);  //  ONE LESS CHECKER ON BOARD

        //
        //  UPDATE PLAYER PANEL FOR BLACK
        //
        self::notifyAllPlayers("playerPanel",
            "",
            array(
            "number_of_checkers" => self::getGameStateValue( "number_of_black_checkers" ),
            "player_id" => $active_player_id 
            ));
    }
    else // ACTIVE PLAYER COLOR YELLOW
    {
        self::incGameStateValue ( "number_of_yellow_checkers", -1);  //  ONE LESS CHECKER ON BOARD

        //
        //  UPDATE PLAYER PANEL FOR YELLOW
        //
        self::notifyAllPlayers("playerPanel",
            "",
            array(
            "number_of_checkers" => self::getGameStateValue( "number_of_yellow_checkers" ),
            "player_id" => $active_player_id 
            ));
    }


    // 
    // UPDATE HISTORY PANEL ABOUT REMOVED CHECKER
    //
    $x_from_coord = chr( ord( "A" ) + $x );

    $y_from_coord = $y + 1;


    self::notifyAllPlayers( "playerHistory", clienttranslate( '${player_name}: ${x_from_coord}${y_from_coord} =>' ),     
            array(
                'player_name' => self::getActivePlayerName(),
                'x_from_coord' => $x_from_coord,
                'y_from_coord' => $y_from_coord
            ) );



    // 
    //  RECORD LAST MOVE MOVE IN GLOBALS
    //
    self::setGameStateValue ("last_move_indicator_x", $x);
    self::setGameStateValue ("last_move_indicator_y", $y);

    self::incGameStateValue ("number_of_moves", 1);



    //
    // NOTIFY FRONTEND ABOUT LAST MOVE 
    //
    self::notifyAllPlayers( "slideLastMoveIndicator", clienttranslate( '' ),   // SLIDE LAST MOVE INDICATOR FROM OLD POSITION
        array(
        'x' => $x,
        'y' => $y,
        'id' => self::getGameStateValue ("last_move_indicator_id")
        //'number_of_moves' => self::getGameStateValue ("number_of_moves")
        ) );



    //
    //  CHECK IF STACK HEIGHT > 1
    //
    if ( $old_stack_height > 1 )
    {
        //
        //  CREATE NEW STACK 
        //
        if ( $active_player_color == "000000")
        {
            $new_checker_id = self::getGameStateValue ( "black_checker_base_id");

            self::incGameStateValue ( "black_checker_base_id", 1);
        }
        else // ACTIVE PLAYER COLOR YELLOW
        {
            $new_checker_id = self::getGameStateValue ( "yellow_checker_base_id");

            self::incGameStateValue ( "yellow_checker_base_id", 1);
        }


        //
        // NEW STACK HEIGHT AND ID 
        //
        $new_stack_height = $old_stack_height - 1;

        $new_checker_id += 1000 * $new_stack_height;


        // 
        //  ADD NEW CHECKER INTO DATABASE 
        //
        $sql = "UPDATE board SET board_player = $active_player_id, checker_id = $new_checker_id WHERE ( board_x, board_y ) = ( $x, $y )";  
    
        self::DbQuery( $sql );


        // 
        //  NOTIFY FRONTEND TO PLACE NEW CHECKER
        //
        self::notifyAllPlayers( "checkerPlaced", clienttranslate( '' ), 
            array(
            'x' => $x,
            'y' => $y,
            'player_id' => $active_player_id,
            'placed_checker_id' => $new_checker_id,
            'N' => $N
            ) );


        //
        // Go to the next state: removeCheckerMoveA => removeCheckerMoveB
        //
        $this->gamestate->nextState( 'removeCheckerMoveA' );
    }
    else // OLD STACK HEIGHT == 1 
    {
        //
        //  CHECK FOR NO MORE ACTIVE PLAYER CHECKERS - GAME END 
        //
        if ( $active_player_color == "000000")  //  BLACK PLAYER ACTIVE
        {
            $number_of_active_player_checkers = self::getGameStateValue ( "number_of_black_checkers");  //  NUMBER OF BLACK CHECKERS
        }
        else                                    //  YELLOW PLAYER ACTIVE
        {
            $number_of_active_player_checkers = self::getGameStateValue ( "number_of_yellow_checkers");  //  NUMBER OF YELLOW CHECKERS
        }

        if ( $number_of_active_player_checkers == 0 ) 
        {
            // 
            //  END GAME 
            //
            //  INACTIVE PLAYER WINS
            //
            $sql = "UPDATE player
                    SET player_score = 1 WHERE player_id = $inactive_player_id";
                    self::DbQuery( $sql );


            $newScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );

            self::notifyAllPlayers( "newScores", "", array(
                "scores" => $newScores
            ) );

            $this->gamestate->nextState( 'endGame' );

            /*
            self::notifyAllPlayers( "backendMessage", clienttranslate( 'Inactive player wins' ), 
            array(
            ) );
            */
        }
        else 
        {
            //
            // Go to the next state: removeCheckerMoveA => removeCheckerMoveB
            //
            $this->gamestate->nextState( 'removeCheckerMoveA' );
        }
    }
}




    
//
//  REMOVE CHECKER MOVE B 
//  #####################
//
//
//  Get old stack height 
//
//  Remove old stack in database 
//
//  Notify frontend to remove old stack
//
//  Decriment number of checkers for active player color
//
//  Notify frontend to decriment number of checkers in player panel
//
//
//  If old stack height > 1 
//      Make new shorter stack
//      Add to database 
//      Notify frontend about added stack
//      State change: removeCheckerMoveA => removeCheckerMoveB
//
//  Else (removed stack was height 1)
//      If number of active player checkers == 0
//          End game
//
//      Else (at least 1 active player checker remaining)
//          State change: removeCheckerMoveA => removeCheckerMoveB
//   
//
function removeCheckerMoveB ( $x, $y )
{
    self::checkAction( 'removeCheckerMoveB' ); 
    

    $active_player_id = self::getActivePlayerId();

    $active_player_color = self::getPlayerColor ( $active_player_id );


    $inactive_player_id = self::getOtherPlayerId ( $active_player_id );


    $N=self::getGameStateValue("board_size");


    $board = self::getBoard ( );

    $checker_IDs = self::getCheckerIDs ( );


    $old_checker_id = self::getCheckerID ( $x, $y );

    $old_stack_height = self::getStackHeight ( $old_checker_id );



    //
    // REMOVE CHECKER STACK FROM DATABASE 
    //
    $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( $x, $y )";  
    
    self::DbQuery( $sql );

    // 
    //  NOTIFY FRONTEND TO REMOVE CHECKER
    //
    self::notifyAllPlayers( "checkerRemoved", clienttranslate( '' ), 
        array(
        'removed_checker_id' => $old_checker_id,
        'player_id' => $active_player_id
        ) );

    //
    //  DECRIMENT NUMBER OF CHECKERS FOR ACTIVE PLAYER
    //
    if ( $active_player_color == "000000")
    {
        self::incGameStateValue ( "number_of_black_checkers", -1);  //  ONE LESS CHECKER ON BOARD

        //
        //  UPDATE PLAYER PANEL FOR BLACK
        //
        self::notifyAllPlayers("playerPanel",
            "",
            array(
            "number_of_checkers" => self::getGameStateValue( "number_of_black_checkers" ),
            "player_id" => $active_player_id 
            ));
    }
    else // ACTIVE PLAYER COLOR YELLOW
    {
        self::incGameStateValue ( "number_of_yellow_checkers", -1);  //  ONE LESS CHECKER ON BOARD

        //
        //  UPDATE PLAYER PANEL FOR YELLOW
        //
        self::notifyAllPlayers("playerPanel",
            "",
            array(
            "number_of_checkers" => self::getGameStateValue( "number_of_yellow_checkers" ),
            "player_id" => $active_player_id 
            ));
    }


    // 
    // UPDATE HISTORY PANEL ABOUT REMOVED CHECKER
    //
    $x_from_coord = chr( ord( "A" ) + $x );

    $y_from_coord = $y + 1;


    self::notifyAllPlayers( "playerHistory", clienttranslate( '${player_name}: ${x_from_coord}${y_from_coord} =>' ),     
            array(
                'player_name' => self::getActivePlayerName(),
                'x_from_coord' => $x_from_coord,
                'y_from_coord' => $y_from_coord
            ) );



    // 
    //  RECORD LAST MOVE MOVE IN GLOBALS
    //
    self::setGameStateValue ("last_move_indicator_x", $x);
    self::setGameStateValue ("last_move_indicator_y", $y);

    self::incGameStateValue ("number_of_moves", 1);


    //
    // NOTIFY FRONTEND ABOUT LAST MOVE 
    //
    self::notifyAllPlayers( "slideLastMoveIndicator", clienttranslate( '' ),   // SLIDE LAST MOVE INDICATOR FROM OLD POSITION
        array(
        'x' => $x,
        'y' => $y,
        'id' => self::getGameStateValue ("last_move_indicator_id")
        //'number_of_moves' => self::getGameStateValue ("number_of_moves")
        ) );


    //
    //  CHECK IF STACK HEIGHT > 1
    //
    if ( $old_stack_height > 1 )
    {
        //
        //  CREATE NEW STACK 
        //
        if ( $active_player_color == "000000")
        {
            $new_checker_id = self::getGameStateValue ( "black_checker_base_id");

            self::incGameStateValue ( "black_checker_base_id", 1);
        }
        else // ACTIVE PLAYER COLOR YELLOW
        {
            $new_checker_id = self::getGameStateValue ( "yellow_checker_base_id");

            self::incGameStateValue ( "yellow_checker_base_id", 1);
        }


        //
        // NEW STACK HEIGHT AND ID 
        //
        $new_stack_height = $old_stack_height - 1;

        $new_checker_id += 1000 * $new_stack_height;


        // 
        //  ADD NEW CHECKER INTO DATABASE 
        //
        $sql = "UPDATE board SET board_player = $active_player_id, checker_id = $new_checker_id WHERE ( board_x, board_y ) = ( $x, $y )";  
    
        self::DbQuery( $sql );


        // 
        //  NOTIFY FRONTEND TO PLACE NEW CHECKER
        //
        self::notifyAllPlayers( "checkerPlaced", clienttranslate( '' ), 
            array(
            'x' => $x,
            'y' => $y,
            'player_id' => $active_player_id,
            'placed_checker_id' => $new_checker_id,
            'N' => $N
            ) );


        //
        // Go to the next state: removeCheckerMoveB => nextPlayer
        //
        $this->gamestate->nextState( 'removeCheckerMoveB' );
    }
    else // OLD STACK HEIGHT == 1 
    {
        //
        //  CHECK FOR NO MORE ACTIVE PLAYER CHECKERS - GAME END 
        //
        if ( $active_player_color == "000000")  //  BLACK PLAYER ACTIVE
        {
            $number_of_active_player_checkers = self::getGameStateValue ( "number_of_black_checkers");  //  NUMBER OF BLACK CHECKERS
        }
        else                                    //  YELLOW PLAYER ACTIVE
        {
            $number_of_active_player_checkers = self::getGameStateValue ( "number_of_yellow_checkers");  //  NUMBER OF YELLOW CHECKERS
        }

        if ( $number_of_active_player_checkers == 0 ) 
        {
            // 
            //  END GAME 
            //
            //  INACTIVE PLAYER WINS
            //
            $sql = "UPDATE player
                    SET player_score = 1 WHERE player_id = $inactive_player_id";
                    self::DbQuery( $sql );


            $newScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );

            self::notifyAllPlayers( "newScores", "", array(
                "scores" => $newScores
            ) );

            $this->gamestate->nextState( 'endGame' );

            /*
            self::notifyAllPlayers( "backendMessage", clienttranslate( 'Inactive player wins' ), 
            array(
            ) );
            */
        }
        else 
        {
            //
            // Go to the next state: removeCheckerMoveA => nextPlayer
            //
            $this->gamestate->nextState( 'removeCheckerMoveB' );
        }
    }
}




function unselectOrigin ( )
{
    self::checkAction( 'unselectOrigin' );  

    //
    // UNSELECT ORIGIN IN DATABASE 
    //
    $sql = "UPDATE board SET is_origin_selected = 0
            WHERE 1";  

    self::DbQuery( $sql );

    //
    // NOTIFY FRONTEND ABOUT UNSELECTED ORIGIN 
    //
    self::notifyAllPlayers( "originUnselected", clienttranslate( '' ), 
        array(
        ) );

    //
    // Go to the next state
    //
    $this->gamestate->nextState( 'unselectOrigin' );
            
    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( 'Unselect origin' ), 
        array(
        ) );
    */
}




//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

function argSelectOriginFirstTurn()
{
    return array(
        'selectableOriginsFirstTurn' => self::getSelectableOriginsFirstTurn ( self::getActivePlayerId() )
    );
}


function argSelectDestinationFirstTurn()
{
    return array(
        'selectedOrigin_selectableDestinationsFirstTurn' => self::getSelectedOrigin_SelectableDestinationsFirstTurn ( self::getActivePlayerId() )
    );
}


function argSelectOriginMoveA()
{
    return array(
        'selectableOriginsMoveA' => self::getSelectableOriginsMoveA ( self::getActivePlayerId() )
    );
}


function argSelectDestinationMoveA()
{
    return array(
        'selectedOrigin_selectableDestinationsMoveA' => self::getSelectedOrigin_SelectableDestinationsMoveA ( self::getActivePlayerId() )
    );
}


function argSelectOriginMoveB()
{
    return array(
        'selectableOriginsMoveB' => self::getSelectableOriginsMoveB ( self::getActivePlayerId() )
    );
}


function argSelectDestinationMoveB()
{
    return array(
        'selectedOrigin_selectableDestinationsMoveB' => self::getSelectedOrigin_SelectableDestinationsMoveB ( self::getActivePlayerId() )
    );
}


function argRemoveCheckerMoveA()
{
    return array(
        'removableCheckersMoveA' => self::getRemovableCheckers ( self::getActivePlayerId() )
    );
}



function argRemoveCheckerMoveB()
{
    return array(
        'removableCheckersMoveB' => self::getRemovableCheckers ( self::getActivePlayerId() )
    );
}



//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

//
//  ST NEXT PLAYER 
//  ##############
//
//  Roll dice for next turn
//
//  Show dice for next turn 
//
//  Activate next player 
//
//  Show player's dice roll in history panel
//
//  If there are moves available for move A 
//      State change: nextTurn => selectOriginMoveA
//  Else (no moves available for move A) 
//      State change: nextTurn_noMovesA => removeCheckerMoveA
//          
function stNextPlayer()
{
    $active_player_id = self::getActivePlayerId ( );

    $inactive_player_id = self::getOtherPlayerId ( $active_player_id );


    //
    //  REMOVE DICE FROM LAST TURN - NOTIFY FRONTEND
    //
    $die_0_id = self::getGameStateValue ( "rolled_die_0_id");
    $die_1_id = self::getGameStateValue ( "rolled_die_1_id");

    self::notifyAllPlayers( "diceRemoved", clienttranslate( '' ), 
        array(
        'die_0_id' => $die_0_id,
        'die_1_id' => $die_1_id,
        'player_id' => $inactive_player_id
        ) );
 

    //
    //  ROLL DICE FOR NEXT TURN 
    //
    self::rollDice ( );

    $die_0 = self::getGameStateValue ( "rolled_die_0");

    $die_1 = self::getGameStateValue ( "rolled_die_1");

    $die_0_id = self::getGameStateValue ( "rolled_die_0_id");

    $die_1_id = self::getGameStateValue ( "rolled_die_1_id");


    //
    //  SHOW DICE FOR NEXT TURN - FRONTEND
    //
    self::notifyAllPlayers( "diceRolled", clienttranslate( '' ), 
        array(
        'die_0' => $die_0,
        'die_1' => $die_1,
        'die_0_id' => $die_0_id,
        'die_1_id' => $die_1_id,
        'player_id' => $inactive_player_id
        ) );
 

    // 
    //  ACTIVATE NEXT PLAYER 
    //
    $active_player_id = self::activeNextPlayer();


    //
    // UPDATE DICE ROLL HISTORY PANEL 
    //
    $die_0 = self::getGameStateValue ("rolled_die_0");
    $die_1 = self::getGameStateValue ("rolled_die_1");

    self::notifyAllPlayers( "diceRolledHistory", clienttranslate( '${player_name} ${die_0}-${die_1}' ),     
        array(
            'player_name' => self::getActivePlayerName(),
            'die_0' => $die_0,
            'die_1' => $die_1
        ) );


    //
    //  CHECK FOR AVAILABLE MOVES FOR MOVE A 
    //
    $selectableOriginsMoveA = self::getSelectableOriginsMoveA ( $active_player_id );

    if (    count ( $selectableOriginsMoveA ) == 0    )                 //  NO AVAILABLE MOVE_A
    {
        //
        // Go to the next state: selectDestinationMoveA_noMovesA => removeChecker 
        //
        self::giveExtraTime( $active_player_id );  
        
        $this->gamestate->nextState( 'nextTurn_noMovesA' );
    }
    else                                                                //  AVAILABLE MOVE_A
    {
        //
        // Go to the next state: nextTurn => selectOriginMoveA
        //
        self::giveExtraTime( $active_player_id );  
        
        $this->gamestate->nextState( 'nextTurn' );
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





    // 
    //  INITIALIZE BOARD - LOAD DATABASE WITH CHECKER ID VALUES 
    //
    function initializeBoard ( $black_player_id, $yellow_player_id )
    {

        $N=self::getGameStateValue("board_size");

        $sql = "INSERT INTO board (board_x, board_y, board_player, checker_id, is_origin_selected) VALUES ";

        $sql_values = array();

        
        for ($i = 0; $i < $N; ++$i)
        {
            for ($j = 0; $j < $N; ++$j)
            {
                if (    ($i + $j) % 2 == 0    )     // DARK SQUARE 
                {
                    $checker_id_value = self::getGameStateValue( "yellow_checker_base_id" );

                    self::incGameStateValue( "yellow_checker_base_id", 1 );

                    self::incGameStateValue( "number_of_yellow_checkers", 1 );


                    $checker_id_value += 1000;      // STACK VALUE = 1

                    $sql_values[] = "($i, $j, $yellow_player_id, $checker_id_value, 0)";                   
                }
                else                                // LIGHT SQUARE 
                {
                    $checker_id_value = self::getGameStateValue( "black_checker_base_id" );

                    self::incGameStateValue( "black_checker_base_id", 1 );

                    self::incGameStateValue( "number_of_black_checkers", 1 );


                    $checker_id_value += 1000;      // STACK VALUE = 1

                    $sql_values[] = "($i, $j, $black_player_id, $checker_id_value, 0)";                   
                }
            }
        }

        $sql .= implode( $sql_values, ',' );
        self::DbQuery( $sql );
    }



    function rollDice( )
    {
        $N=self::getGameStateValue("board_size");

        $die_0 = bga_rand ( 1, $N / 2 );

        $die_1 = bga_rand ( 1, $N / 2 );

        self::setGameStateValue( "rolled_die_0", $die_0 );

        self::setGameStateValue( "rolled_die_1", $die_1 );


        self::incGameStateValue( "rolled_die_0_id", 1 );

        self::incGameStateValue( "rolled_die_1_id", 1 );
    }

}


/*
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'abcde: ${var_1}, ${var_2}' ), 
        array(
            "var_1" => $x,
            "var_2" => $y
        ) );
*/