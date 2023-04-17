<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * Impasse implementation : © <Mark Steere> <marksteere@gmail.com>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * impasse.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class Impasse extends Table
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
            "red_checker_id" => 20,                 
            "yellow_checker_id" => 21,                 
            "red_bullseye_id" => 22,                 
            "yellow_bullseye_id" => 23,                 
            "removed_red_checkers" => 26,         
            "removed_yellow_checkers" => 27,        
            "last_move_x" => 30,                    
            "last_move_y" => 31,                    
            "last_move_id" => 32,                  
            "board_size" => 101,                   
        ) );        
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "impasse";
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
        self::setGameStateValue( "red_checker_id", 10000 );
        self::setGameStateValue( "yellow_checker_id", 20000 );
        self::setGameStateValue( "red_bullseye_id", 30000 );
        self::setGameStateValue( "yellow_bullseye_id", 40000 );
        self::setGameStateValue( "removed_red_checkers", 0 );
        self::setGameStateValue( "removed_yellow_checkers", 0 );




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
            if( $color == 'ff0000' )
                $red_player_id = $player_id;
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
        self::initializeBoard ( $red_player_id, $yellow_player_id );




        //
        //  TEMPORARY - ADD RED SINGLETON TO TEST CROWN TRANSFER MOVE
        //
        /*
        $red_checker_id = self::getGameStateValue( "red_checker_id" );    
                
        $sql = "UPDATE board SET board_player = $red_player_id, checker_id = $red_checker_id WHERE ( board_x, board_y ) = ( 7, 5 )";  
    
        self::DbQuery( $sql );

        self::incGameStateValue( "red_checker_id", 1 );
        */


        //
        //  IMPASSE TEST 
        //
        //
        //  TEMPORARY - ADD TWO YELLOW SINGLES
        //
        /*
        $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );    
                
        $sql = "UPDATE board SET board_player = $yellow_player_id, checker_id = $yellow_checker_id WHERE ( board_x, board_y ) = ( 5, 5 )";  
    
        self::DbQuery( $sql );

        self::incGameStateValue( "yellow_checker_id", 1 );


        $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );    
                
        $sql = "UPDATE board SET board_player = $yellow_player_id, checker_id = $yellow_checker_id WHERE ( board_x, board_y ) = ( 7, 5 )";  
    
        self::DbQuery( $sql );

        self::incGameStateValue( "yellow_checker_id", 1 );

        //
        //  TEMPORARY - REMOVE THREE RED SINGLES
        //
        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 0, 0 )";  
    
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 3, 1 )";  
    
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( 4, 0 )";  
    
        self::DbQuery( $sql );

        //
        //  TEMPORARY - REMOVE TWO RED DOUBLES
        //
        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL, bullseye_id = NULL WHERE ( board_x, board_y ) = ( 1, 7 )";  
    
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL, bullseye_id = NULL WHERE ( board_x, board_y ) = ( 2, 6 )";  
    
        self::DbQuery( $sql );
        */

        /*
        //
        //  TEMPORARY - ADD CROWN IN RED HOME ROW TO TEST BEAR OFF 
        //
        $red_bullseye_id = self::getGameStateValue( "red_bullseye_id" );    
                
        $sql = "UPDATE board SET bullseye_id = $red_bullseye_id WHERE ( board_x, board_y ) = ( 4, 0 )";  
    
        self::DbQuery( $sql );

        self::incGameStateValue( "red_bullseye_id", 1 );



        //
        //  TEMPORARY - ADD CROWN IN YELLOW HOME ROW TO TEST BEAR OFF 
        //
        $yellow_bullseye_id = self::getGameStateValue( "yellow_bullseye_id" );    
                
        $sql = "UPDATE board SET bullseye_id = $yellow_bullseye_id WHERE ( board_x, board_y ) = ( 7, 7 )";  
    
        self::DbQuery( $sql );

        self::incGameStateValue( "red_bullseye_id", 1 );



        //
        //  TEMPORARY - TEST AUTOMATIC CROWN OF RED UNCROWNED CHECKER 
        //      REMOVE CROWN IN RED FAR ROW 
        //      SET is_uncrowned TO 1
        //
        $sql = "UPDATE board SET bullseye_id = NULL, is_uncrowned = 1 WHERE ( board_x, board_y ) = ( 5, 7 )";  
    
        self::DbQuery( $sql );



        //
        //  TEMPORARY - TEST AUTOMATIC CROWN OF YELLOW UNCROWNED CHECKER 
        //      REMOVE CROWN IN YELLOW FAR ROW 
        //      SET is_uncrowned TO 1
        //
        $sql = "UPDATE board SET bullseye_id = NULL, is_uncrowned = 1 WHERE ( board_x, board_y ) = ( 6, 0 )";  
    
        self::DbQuery( $sql );
        */



        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

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
    
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );
  

        //
        // Get board occupied cells
        // 
        $sql = "SELECT board_x x, board_y y, board_player player, checker_id checker_id, bullseye_id bullseye_id, 
                       is_origin_selected is_origin_selected, is_uncrowned is_uncrowned
                    FROM board
                    WHERE board_player IS NOT NULL";
        $result['board'] = self::getObjectListFromDB( $sql );


        //
        // Removed checkers
        //
        $activePlayerId = self::getActivePlayerId();
        $otherPlayerId = self::getOtherPlayerId($activePlayerId);

        $active_player_color = self::getPlayerColor ( $activePlayerId );

        $removedCheckers = array();

        if ( $active_player_color == "ff0000" )
        {
            $removedCheckers[$activePlayerId] = self::getGameStateValue( "removed_red_checkers" );
            $removedCheckers[$otherPlayerId] = self::getGameStateValue( "removed_yellow_checkers" );
        }
        else
        {
            $removedCheckers[$activePlayerId] = self::getGameStateValue( "removed_yellow_checkers" );
            $removedCheckers[$otherPlayerId] = self::getGameStateValue( "removed_red_checkers" );
        }
        
        $result['removedCheckers'] = $removedCheckers;


		//Get the board size
		$result['board_size'] = self::getGameStateValue("board_size");
  
  
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
        $removed_red_checkers = self::getGameStateValue ( "removed_red_checkers");

        $removed_yellow_checkers = self::getGameStateValue ( "removed_yellow_checkers");

        $leader_removed_checkers = max ( $removed_red_checkers, $removed_yellow_checkers );


        $N = self::getGameStateValue ( "board_size" );

        switch ( $N )
        {
            case 8:
                $leader_removed_checkers_percent = $leader_removed_checkers / 12 * 100;
                break;

            case 10:
                $leader_removed_checkers_percent = $leader_removed_checkers / 20 * 100;
                break;
        }
            
        return $leader_removed_checkers_percent;    
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

//
// GET SELECTABLE STACKS 
//
//
function getSelectableOrigins( $player_id )
{
    $result = array();

    $board = self::getBoard();


    //
	//Get the board size
    //
    $N=self::getGameStateValue("board_size");
                  

    for( $x=0; $x<$N; $x++ )
    {
        for( $y=0; $y<$N; $y++ )
        {
            if (    ( $x + $y ) % 2 == 0    )
            {
                if ( $board [ $x ] [ $y ] == $player_id )
                {
                    if ( self::hasMoves ( $x, $y, $board, $player_id ) )
                    {
                        if( ! isset( $result[$x] ) )
                        $result[$x] = array();

                        $result[$x][$y] = true;
                    }
                }
            }
        }
    }

    return $result;
}






//
//  GET DESTINATIONS FOR SELECTED CHECKER 
//
//  First, determine if the player is Red or Yellow 
//
//  Then determine if origin is a single or a double 
//
//  If Red 
//      If origin single 
//          Add all empty squares in NW direction
//          Add all empty squares in NE direction
//
//      If origin double 
//          If red singleton in SW direction 
//              Add it 
//          Else 
//              Add all empty squares in SW direction
//
//          If red singleton in SE direction 
//              Add it 
//          Else 
//              Add all empty squares in SE direction
//
//
//
//
//
function getSelOrgDests( $selected_x, $selected_y, $board, $player_id )
{
    $result = array();

    $board = self::getBoard ( );

    $player_color = self::getPlayerColor( $player_id );


    //
    // CHECK IF SINGLETON 
    //
    $bullseye_id = self::getBullseyeID ( $selected_x, $selected_y );

    if ( $bullseye_id == NULL )
    {
        $is_singleton = true;
    }
    else 
    {
        $is_singleton = false;
    }


    if ( $player_color == "ff0000")     // RED PLAYER 
    {
        if ( $is_singleton )            // RED SINGLETON
        {
            //
            //  ADD ALL NW EMPTY SQUARES 
            //
            $investigate_x = $selected_x;
            $investigate_y = $selected_y;

            while ( true )
            {
                $investigate_x -= 1;
                $investigate_y += 1;

                if (    self::isUnoccupiedSquare ( $investigate_x, $investigate_y, $board )    )      // UNOCCUPIED SQUARE 
                {
                    //
                    // ADD TO RESULT
                    //
                    if(    ! isset( $result [ $investigate_x ] )    )
                    $result [ $investigate_x ] = array();

                    $result [ $investigate_x ] [ $investigate_y ] = true;
                }
                else 
                {
                    break;
                }
            }

            //
            //  ADD ALL NE EMPTY SQUARES 
            //
            $investigate_x = $selected_x;
            $investigate_y = $selected_y;

            while ( true )
            {
                $investigate_x += 1;
                $investigate_y += 1;

                if (    self::isUnoccupiedSquare ( $investigate_x, $investigate_y, $board )    )
                {
                    //
                    // ADD TO RESULT
                    //
                    if(    ! isset( $result [ $investigate_x ] )    )
                    $result [ $investigate_x ] = array();

                    $result [ $investigate_x ] [ $investigate_y ] = true;
                }
                else 
                {
                    break;
                }
            }
        }
        else                        // RED DOUBLE 
        {
            //
            // CHECK FOR SW RED SINGLETON 
            //
            $investigate_x = $selected_x - 1;
            $investigate_y = $selected_y - 1;

            if (    self::isSingleton ( $investigate_x, $investigate_y, $board, $player_id )    ) // SW RED SINGLETON
            {
                //
                // ADD TO RESULT
                //
                if(    ! isset( $result [ $investigate_x ] )    )
                $result [ $investigate_x ] = array();

                $result [ $investigate_x ] [ $investigate_y ] = true;
            }
            else // NOT SW RED SINGLETON
            {
                //
                //  ADD ALL SW EMPTY SQUARES 
                //
                $investigate_x = $selected_x;
                $investigate_y = $selected_y;

                while ( true )
                {
                    $investigate_x -= 1;
                    $investigate_y -= 1;

                    if (    self::isUnoccupiedSquare ( $investigate_x, $investigate_y, $board )    )
                    {
                        //
                        // ADD TO RESULT
                        //
                        if(    ! isset( $result [ $investigate_x ] )    )
                        $result [ $investigate_x ] = array();

                        $result [ $investigate_x ] [ $investigate_y ] = true;
                    }
                    else 
                    {
                        break;
                    }
                }
            }

            //
            // CHECK FOR SE RED SINGLETON 
            //
            $investigate_x = $selected_x + 1;
            $investigate_y = $selected_y - 1;

            if (    self::isSingleton ( $investigate_x, $investigate_y, $board, $player_id )    ) // SE RED SINGLETON
            {
                //
                // ADD TO RESULT
                //
                if(    ! isset( $result [ $investigate_x ] )    )
                $result [ $investigate_x ] = array();

                $result [ $investigate_x ] [ $investigate_y ] = true;
            }
            else // NOT SE RED SINGLETON
            {
                //
                //  ADD ALL SE EMPTY SQUARES 
                //
                $investigate_x = $selected_x;
                $investigate_y = $selected_y;

                while ( true )
                {
                    $investigate_x += 1;
                    $investigate_y -= 1;

                    if (    self::isUnoccupiedSquare ( $investigate_x, $investigate_y, $board )    )
                    {
                        //
                        // ADD TO RESULT
                        //
                        if(    ! isset( $result [ $investigate_x ] )    )
                        $result [ $investigate_x ] = array();

                        $result [ $investigate_x ] [ $investigate_y ] = true;
                    }
                    else 
                    {
                        break;
                    }
                }
            }
        }
    }
    else // YELLOW PLAYER 
    {
        if ( $is_singleton )            // YELLOW SINGLETON
        {
            //
            //  ADD ALL SW EMPTY SQUARES 
            //
            $investigate_x = $selected_x;
            $investigate_y = $selected_y;

            while ( true )
            {
                $investigate_x -= 1;
                $investigate_y -= 1;

                if (    self::isUnoccupiedSquare ( $investigate_x, $investigate_y, $board )    )      // UNOCCUPIED SQUARE 
                {
                    //
                    // ADD TO RESULT
                    //
                    if(    ! isset( $result [ $investigate_x ] )    )
                    $result [ $investigate_x ] = array();

                    $result [ $investigate_x ] [ $investigate_y ] = true;
                }
                else 
                {
                    break;
                }
            }

            //
            //  ADD ALL SE EMPTY SQUARES 
            //
            $investigate_x = $selected_x;
            $investigate_y = $selected_y;

            while ( true )
            {
                $investigate_x += 1;
                $investigate_y -= 1;

                if (    self::isUnoccupiedSquare ( $investigate_x, $investigate_y, $board )    )
                {
                    //
                    // ADD TO RESULT
                    //
                    if(    ! isset( $result [ $investigate_x ] )    )
                    $result [ $investigate_x ] = array();

                    $result [ $investigate_x ] [ $investigate_y ] = true;
                }
                else 
                {
                    break;
                }
            }
        }
        else                        // YELLOW DOUBLE 
        {
            //
            // CHECK FOR NW YELLOW SINGLETON 
            //
            $investigate_x = $selected_x - 1;
            $investigate_y = $selected_y + 1;

            if (    self::isSingleton ( $investigate_x, $investigate_y, $board, $player_id )    ) // NW YELLOW SINGLETON
            {
                //
                // ADD TO RESULT
                //
                if(    ! isset( $result [ $investigate_x ] )    )
                $result [ $investigate_x ] = array();

                $result [ $investigate_x ] [ $investigate_y ] = true;
            }
            else // NOT NW YELLOW SINGLETON
            {
                //
                //  ADD ALL NW EMPTY SQUARES 
                //
                $investigate_x = $selected_x;
                $investigate_y = $selected_y;

                while ( true )
                {
                    $investigate_x -= 1;
                    $investigate_y += 1;

                    if (    self::isUnoccupiedSquare ( $investigate_x, $investigate_y, $board )    )
                    {
                        //
                        // ADD TO RESULT
                        //
                        if(    ! isset( $result [ $investigate_x ] )    )
                        $result [ $investigate_x ] = array();

                        $result [ $investigate_x ] [ $investigate_y ] = true;
                    }
                    else 
                    {
                        break;
                    }
                }
            }

            //
            // CHECK FOR NE YELLOW SINGLETON 
            //
            $investigate_x = $selected_x + 1;
            $investigate_y = $selected_y + 1;

            if (    self::isSingleton ( $investigate_x, $investigate_y, $board, $player_id )    ) // NE YELLOW SINGLETON
            {
                //
                // ADD TO RESULT
                //
                if(    ! isset( $result [ $investigate_x ] )    )
                $result [ $investigate_x ] = array();

                $result [ $investigate_x ] [ $investigate_y ] = true;
            }
            else // NOT NE YELLOW SINGLETON
            {
                //
                //  ADD ALL NE EMPTY SQUARES 
                //
                $investigate_x = $selected_x;
                $investigate_y = $selected_y;

                while ( true )
                {
                    $investigate_x += 1;
                    $investigate_y += 1;

                    if (    self::isUnoccupiedSquare ( $investigate_x, $investigate_y, $board )    )
                    {
                        //
                        // ADD TO RESULT
                        //
                        if(    ! isset( $result [ $investigate_x ] )    )
                        $result [ $investigate_x ] = array();

                        $result [ $investigate_x ] [ $investigate_y ] = true;
                    }
                    else 
                    {
                        break;
                    }
                }
            }
        }
    }

    return $result;  
}





function getSelectableDestinations( $player_id )
{
    //$result = array();

    $board = self::getBoard();

    $boardSelectedCheckers = self::getBoardSelectedCheckers();  // Should just be one


    $N=self::getGameStateValue("board_size");


    for( $x=0; $x<$N; $x++ )
    {
        for( $y=0; $y<$N; $y++ )
        {

            if (    ( $x + $y ) % 2 == 0    )   // Dark square
            {
                if (    (  $board [ $x ] [ $y ] == $player_id  )
                    &&  (  $boardSelectedCheckers [ $x ] [ $y ] == 1 )    )
                {
                    return (  self::getSelOrgDests ($x, $y, $board, $player_id)  );
                }
            }
        }
    }
}



//
//  GET SELECTABLE CROWNS
//
//  SINGLETONS ARE SELECTABLE CROWNS 
//
//  RETURN LIST OF SINGLETONS - WHICH BELONG TO THE ACTIVE PLAYER AND ARE NOT MARKED AS UNCROWNED
//
function getSelectableCrowns( $player_id )
{
    $result = array();

    $board = self::getBoard();

    $bullseye_IDs = self::getBullseyeIDs ( );

    $isUncrownedCheckers = self::getIsUncrownedCheckers ( );


    $N=self::getGameStateValue("board_size");




    //
    // SHOW isUncrownedCheckers 
    //
    /*
    for( $x=0; $x<$N; $x++ )
    {
        for( $y=0; $y<$N; $y++ )
        {
            if (    ( $x + $y ) % 2 == 0    )   // DARK SQUARE
            {
                self::notifyAllPlayers( "backendMessage", clienttranslate( 'is_uncrowned: ${var_1}, ${var_2}, ${var_3} ' ), 
                array(
                    "var_1" => $x,
                    "var_2" => $y,
                    //"var_3" => 12345
                    "var_3" => $isUncrownedCheckers [ $x ] [ $y ]
                    ) );
            }
        }
    }
    */


    for( $x=0; $x<$N; $x++ )
    {
        for( $y=0; $y<$N; $y++ )
        {
            if (    ( $x + $y ) % 2 == 0    )   // DARK SQUARE
            {
                if (    ( $board [ $x ] [ $y ] == $player_id )
                    &&  ( $bullseye_IDs [ $x ] [ $y ] == NULL )
                    &&  ( $isUncrownedCheckers [ $x ] [ $y ] != 1 )    )
                {
                    /*
                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'selectable crown: ${var_1}, ${var_2} ' ), 
                    array(
                        "var_1" => $x,
                        "var_2" => $y
                        ) );
                    */

                    //
                    // ADD TO RESULT
                    //
                    if(    ! isset( $result [ $x ] )    )
                    $result [ $x ] = array();

                    $result [ $x ] [ $y ] = true;
                }
            }
        }
    }

    return $result;  
}




//
//  GET REMOVABLE IMPASSE CHECKERS
//
//  EVERY STACK HAS A REMOVABLE CHECKER
//
//  RETURN LIST OF ALL STACKS 
//
function getRemovableImpasseCheckers( $player_id )
{
    $result = array();

    $board = self::getBoard();


    $N=self::getGameStateValue("board_size");


    for( $x=0; $x<$N; $x++ )
    {
        for( $y=0; $y<$N; $y++ )
        {
            if (    ( $x + $y ) % 2 == 0    )   // DARK SQUARE
            {
                if ( $board [ $x ] [ $y ] == $player_id )
                {
                    /*
                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'removable impasse checker: ${var_1}, ${var_2} ' ), 
                    array(
                        "var_1" => $x,
                        "var_2" => $y
                        ) );
                    */

                    //
                    // ADD TO RESULT
                    //
                    if(    ! isset( $result [ $x ] )    )
                    $result [ $x ] = array();

                    $result [ $x ] [ $y ] = true;
                }
            }
        }
    }

    return $result;  
}




function getBoardSelectedCheckers ( )
{
    return self::getDoubleKeyCollectionFromDB( "SELECT board_x x, board_y y, is_origin_selected is_origin_selected FROM board", true );
}



function getIsUncrownedCheckers ( )
{
    return self::getDoubleKeyCollectionFromDB( "SELECT board_x x, board_y y, is_uncrowned is_uncrowned FROM board", true );
}



//
//  HAS MOVES
//
//
//  First, determine if the player is Red or Yellow 
//
//  Then determine if it's a single or a double 
//
//  If Red 
//      If single 
//          If NW neighbor on board but unoccupied 
//              Return true 
//          Else if NE neighbor on board but unoccupied 
//              Return true
//      Else if double 
//          If SW neighbor on board 
//              If SW neighbor unoccupied 
//                  Return true 
//              Else if SW neighbor red single 
//                  Return true 
//          Else if SE neighbor on board 
//              If SE neighbor unoccupied 
//                  Return true 
//              Else if SE neighbor red single 
//                  Return true 
//
//
//  Else if Yellow 
//      If single 
//          If SW neighbor on board but unoccupied 
//              Return true 
//          Else if SE neighbor on board but unoccupied 
//              Return true
//      Else if double 
//          If NW neighbor on board
//              If NW neighbor unoccupied
//                  Return true
//              Else if NW neighbor yellow single
//                  Return true
//          Else if NE neighbor on board
//              If NE neighbor unoccupied
//                  Return true
//              Else if NE neighbor red single
//                  Return true
//
//
//  Return false 
//
function hasMoves ( $x, $y, $board, $player_id )
{    

    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( 'hasMoves function.' ), 
    array(
        
        ) ); 
    */   
        
    //$board = self::getBoard ( );

    $N = self::getGameStateValue ( "board_size" );


    $player_color = self::getPlayerColor ( $player_id );


    // 
    //  SEE IF SINGLETON 
    //
    $bullseye_id = self::getBullseyeID ( $x, $y );

    if ( $bullseye_id == NULL )
    {
        $is_singleton = true;
    }
    else 
    {
        $is_singleton = false;
    }


    if ( $player_color == "ff0000" )    // RED
    {
        $singleton_directions = array (    array ( -1, 1 ), array ( 1, 1 )    );

        $double_directions = array (    array ( -1, -1 ), array ( 1, -1 )    );

        if ( $is_singleton )
        {
            foreach ( $singleton_directions as $singleton_direction ) 
            {
                $neighbor_x = $x + $singleton_direction [ 0 ];
                $neighbor_y = $y + $singleton_direction [ 1 ];

                if (    self::isSquareOnBoard ( $neighbor_x, $neighbor_y, $N )    )             // Neighbor square on board
                {
                    if (    ! self::isOccupiedSquare ( $neighbor_x, $neighbor_y, $board )    )  // Neighbor unoccupied
                    {
                        return true;
                    }
                }
            }
        }
        else // Double 
        {
            foreach ( $double_directions as $double_direction ) 
            {
                $neighbor_x = $x + $double_direction [ 0 ];
                $neighbor_y = $y + $double_direction [ 1 ];

                if (    self::isSquareOnBoard ( $neighbor_x, $neighbor_y, $N )    )
                {
                    if (    ! self::isOccupiedSquare ( $neighbor_x, $neighbor_y, $board )    )  // Neighbor unoccupied
                    {
                        return true;
                    }
                    else                                                                        // Neighbor occupied
                    {
                        $neighbor_id = $board [ $neighbor_x ] [ $neighbor_y ];
                    
                        $neighbor_bullseye_id = self::getBullseyeID ( $neighbor_x, $neighbor_y );

                        if ( $neighbor_bullseye_id == NULL && $neighbor_id == $player_id )      // Singleton neighbor of same color
                        {
                            return true;
                        }
                    }
                }
            }
        }
    }
    else // YELLOW 
    {
        $singleton_directions = array (    array ( -1, -1 ), array ( 1, -1 )    );

        $double_directions = array (    array ( -1, 1 ), array ( 1, 1 )    );

        if ( $is_singleton )
        {
            foreach ( $singleton_directions as $singleton_direction ) 
            {
                $neighbor_x = $x + $singleton_direction [ 0 ];
                $neighbor_y = $y + $singleton_direction [ 1 ];

                if (    self::isSquareOnBoard ( $neighbor_x, $neighbor_y, $N )    )             // Neighbor square on board
                {
                    if (    ! self::isOccupiedSquare ( $neighbor_x, $neighbor_y, $board )    )  // Neighbor unoccupied
                    {
                        return true;
                    }
                }
            }
        }
        else // Double 
        {
            foreach ( $double_directions as $double_direction ) 
            {
                $neighbor_x = $x + $double_direction [ 0 ];
                $neighbor_y = $y + $double_direction [ 1 ];

                if (    self::isSquareOnBoard ( $neighbor_x, $neighbor_y, $N )    )
                {
                    if (    ! self::isOccupiedSquare ( $neighbor_x, $neighbor_y, $board )    )  // Neighbor unoccupied
                    {
                        return true;
                    }
                    else                                                                        // Neighbor occupied
                    {
                        $neighbor_id = $board [ $neighbor_x ] [ $neighbor_y ];
                        
                        $neighbor_bullseye_id = self::getBullseyeID ( $neighbor_x, $neighbor_y );

                        if ( $neighbor_bullseye_id == NULL && $neighbor_id == $player_id )      // Singleton neighbor of same color
                        {
                            return true;
                        }
                    }
                }
            }
        }
    }


    return false;
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





function getBullseyeID ( $x, $y )
{      
    $sql = "SELECT bullseye_id bullseye_id FROM board WHERE ( board_x, board_y) = ( $x, $y )"; // should be only one

    $result = self::DbQuery( $sql );


    if ( $result->num_rows > 0 ) 
    {
        while (    $row = $result->fetch_assoc()    ) 
        {
            $bullseye_IDs [ ] = $row [ "bullseye_id" ];
        }
    } 

    $bullseye_id = $bullseye_IDs [0];

    return $bullseye_id;
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




function getUncrownedCheckerCoords ( $player_id )
{
    $uncrowned_coords = array ( );


    $sql = "SELECT board_x x, board_y y FROM board WHERE board_player = $player_id AND is_uncrowned = 1";  // should be only one

    $result = self::DbQuery( $sql );

    $row = $result->fetch_assoc ( );

    if (    isset ( $row )    )
    {
        $uncrowned_coords [ 0 ] = $row [ "x" ];
        $uncrowned_coords [ 1 ] = $row [ "y" ];
    }


    return $uncrowned_coords;
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




function isUnoccupiedSquare ( $x, $y, $board )
{
	//Get the board size
    $N=self::getGameStateValue("board_size");
     

    if (    self::isSquareOnBoard ( $x, $y, $N )    )
    {
        if ( $board [ $x ] [ $y ] == NULL )
            return true;
        else 
            return false;
    }
    else 
        return false;
}





function isSingleton ( $x, $y, $board, $player_id )
{
	//Get the board size
    $N=self::getGameStateValue("board_size");
     

    if (    self::isSquareOnBoard ( $x, $y, $N )    )
    {
        if (    $board [ $x ] [ $y ] == $player_id 
             && self::getBullseyeID ( $x, $y ) == NULL    )
        {
            return true;
        }
        else 
        {
            return false;
        }
    }
    else 
        return false;
}






function getBoard()
{
    return self::getDoubleKeyCollectionFromDB( "SELECT board_x x, board_y y, board_player player
                                                FROM board", true );
}



function getBullseyeIDs()
{
    return self::getDoubleKeyCollectionFromDB( "SELECT board_x x, board_y y, bullseye_id bullseye_id
                                                FROM board", true );
}




public function getOtherPlayerId($player_id)
{
    $sql = "SELECT player_id FROM player WHERE player_id != $player_id";
    return $this->getUniqueValueFromDB( $sql );
}


function getColorName( $color )
{
        if ($color=='ff0000')
        {
            return clienttranslate('RED');
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





//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

//
//  SELECT ORIGIN 
//
//
//  If it's a singleton or double that has moves
//      Mark as selected in database 
//      Notify frontend to highlight the selected singleton
//      State change: selectOrigin => selectDestination
//
//
//      
//
//      If it's in the home row                                         ERROR: NO NEED TO CHECK FOR DOUBLE IN THE HOME ROW.  TOP CHECKER WOULD HAVE BEEN REMOVED IMMEDIATELY IN HOME ROW
//          Remove the top checker in the database                          #
//                                                                          #
//          If there's an uncrowned checker                                 #
//              Remove the newly created singleton in the database          #
//              Add a crown to the uncrowned checker in the database        #
//              Set is_uncrowned = 0                                        #
//                                                                          #
//              Notify the frontend to ...                                  #
//                  Slide the home row crown to the uncrowned checker       #
//                  Remove the newly created home row singleton             #
//                                                                          #
//          Else (no uncrowned checker)                                     #
//              Notify frontend to remove the bore off crown                #
//
//          State change: bearOff => nextPlayer                             #
//                                                                          #
//      Else (not in home row)                                              #
//          Mark as selected in database                                    #
//          Notify frontend to highlight the selected double                #
//          State change: selectOrigin => selectDestination                 #
//
//      
//
function selectOrigin ( $x, $y )
{
    self::checkAction( 'selectOrigin' );  


    $active_player_id = self::getActivePlayerId(); 

    $board = self::getBoard();


    //
    // Check if this is a selectable origin 
    //
    if ( self::hasMoves ( $x, $y, $board, $active_player_id ) )
    {
        /*               
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'selectOrigin - hasMoves' ), 
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
        // ADVANCE TO NEXT STATE: selectOrigin => selectDestination
        //
        $this->gamestate->nextState( 'selectOrigin' );   
            
    }
    else
    {
        throw new feException( "Not a valid origin stack." );
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





//
//  SELECT DESTINATION 
//
//
//  Get selected origin coords
//
//  If origin is a singleton
//      Delete singleton from origin and set is_selected_origin to 0 in database 
//      Add singleton to destination in database
//      Notify frontend about singleton move from origin to destination
//
//      If destination in far row 
//          Set is_uncrowned = 1 in database 
//
//          If there are any other singletons of active player
//              State change: selectCrownableDestination => selectCrown 
// 
//          Else (no other singletons) 
//              State change: selectCrownableDestNoCrownsAvailable => nextPlayer 
//
//      Else (destination not in far row) 
//          State change: selectDestination => nextPlayer
//      
//          
//  Else if origin is a double
//      If destination not in home row
//          If the destination is a singleton 
//              Delete crown from origin and set is_selected_origin to 0 in database 
//              Add crown to destination 
//              Notify frontend to slide crown from origin to destination 
//
//              If base checker is in far row ( $selected_y == 0 or $selected_y == $N - 1 )
//                  Set is_uncrowned = 1 in database 
//
//              If there are any other singletons
//                  State change: selectCrownableDestination => selectCrown 
// 
//              Else (no other singletons) 
//                  State change: selectCrownableDestNoCrownsAvailable => nextPlayer 
//      
//          Else (destination empty square) 
//              Delete double from origin and set is_selected_origin to 0 in database 
//              Add double to destination 
//              Notify frontend to slide double from origin to destination
//
//      Else (destination in home row)
//          If the home row desination is a singleton   THERE WON'T BE AN UNCROWNED CHECKER IN THIS CASE BECAUSE THE HOME ROW SINGLETON WOULD HAVE ALREADY CROWNED IT
//              Delete crown from origin and set is_selected_origin to 0 in database 
//              Notify frontend to remove the origin crown 
//              
//          Else (home row destination empty square) 
//              If there's an uncrowned checker
//                  Delete base checker and crown from origin and set is_selected_origin to 0 in database 
//                  Add crown to uncrowned checker
//                  Notify frontend to...
//                      Remove origin base checker 
//                      Slide origin crown to uncrowned checker
//
//              Else (no uncrowned checker)
//                  Delete base checker and crown from origin and set is_selected_origin to 0 in database 
//                  Add singleton to destination 
//                  Notify frontend to remove origin crown and slide origin base checker from origin to destination 
//
//      State change: selectDestination => nextPlayer
//   
//
function selectDestination ( $x, $y )
{
    self::checkAction( 'selectDestination' ); 
    

    $active_player_id = self::getActivePlayerId();

    //$player_color = self::getPlayerColor ( $active_player_id );

	//Get the board size
    $N=self::getGameStateValue("board_size");

    $board = self::getBoard ( );

    $bullseye_IDs = self::getBullseyeIDs ( );


    $selected_origin = self::getSelectedOriginCoords ( );

    $selected_x = $selected_origin [ 0 ];
    $selected_y = $selected_origin [ 1 ];

    $origin_base_checker_id = self::getCheckerID ( $selected_x, $selected_y );







    //
    // UNSELECT ORIGIN IN DATABASE                          ####  BUG FIX  ####   THERE WAS A BUG WHERE AN EXTRA SELECTED ORIGIN WAS SHOWING ON THE BOARD
    //                                                      ####
    $sql = "UPDATE board SET is_origin_selected = 0      
            WHERE 1";                                      
                                                            ####
    self::DbQuery( $sql );                                  ####






    //
    // CHECK IF ORIGIN IS SINGLETON 
    //
    $origin_bullseye_id = self::getBullseyeID ( $selected_x, $selected_y );

    if ( $origin_bullseye_id == NULL )
    {
        $is_origin_singleton = true;
    }
    else 
    {
        $is_origin_singleton = false;
    }


    if ( $is_origin_singleton )    //  SINGLETON ORIGIN
    {
        //
        // REMOVE SINGLETON FROM ORIGIN AND UNSELECT ORIGIN IN DATABASE 
        //
        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL, is_origin_selected = 0 WHERE ( board_x, board_y ) = ( $selected_x, $selected_y )";  
    
        self::DbQuery( $sql );


        //
        // ADD SINGLETON TO DESTINATION IN DATABASE 
        //
        $sql = "UPDATE board SET board_player = $active_player_id, checker_id = $origin_base_checker_id WHERE ( board_x, board_y ) = ( $x, $y )";  
    
        self::DbQuery( $sql );
        

        // 
        //  NOTIFY FRONTEND ABOUT SINGLETON SLIDE 
        //
        self::notifyAllPlayers( "slidSingleton", clienttranslate( '' ), 
            array(
            'slid_singleton_id' => $origin_base_checker_id,
            'destination_x' => $x,
            'destination_y' => $y
            ) );



        // 
        // UPDATE HISTORY PANEL ABOUT SINGLETON SLIDE 
        //
        $x_from_coord = chr( ord( "A" ) + $selected_x );
        $y_from_coord = $selected_y + 1;

        $x_to_coord = chr( ord( "A" ) + $x );
        $y_to_coord = $y + 1;

        self::notifyAllPlayers( "playerHistory", clienttranslate( '${player_name} ${x_from_coord}${y_from_coord}-${x_to_coord}${y_to_coord} single' ),     
                array(
                    'player_name' => self::getActivePlayerName(),
                    'x_from_coord' => $x_from_coord,
                    'y_from_coord' => $y_from_coord,
                    'x_to_coord' => $x_to_coord,
                    'y_to_coord' => $y_to_coord
                ) );


        // 
        //  CHECK IF SINGLETON IN FAR ROW 
        //
        if ( $y == 0 || $y == $N - 1 ) 
        {
            //
            // SET is_uncrowned = 1 IN DATABASE 
            //
            $sql = "UPDATE board SET is_uncrowned = 1 WHERE ( board_x, board_y ) = ( $x, $y )";  
    
            self::DbQuery( $sql );


            /*
            self::notifyAllPlayers( "backendMessage", clienttranslate( 'SET is_uncrowned = 1: ${var_1}, ${var_2} ' ), 
                array(
                    "var_1" => $x,
                    "var_2" => $y
                    ) );
            */


            $number_of_singletons = self::getNumberOfSingletons ( $active_player_id, $board, $bullseye_IDs, $N );

            /*
            self::notifyAllPlayers( "backendMessage", clienttranslate( '$number_of_singletons: ${var_1} ' ), 
            array(
                "var_1" => $number_of_singletons
                ) );
            */

            if ( $number_of_singletons == 1  ) // ONLY ONE SINGLETON OF ACTIVE PLAYER 
            {
                //
                // Go to the next state: selectCrownableDestNoCrownsAvailable => nextPlayer
                //
                $this->gamestate->nextState( 'selectCrownableDestNoCrownsAvailable' );

                return;
            }
            else // MORE THAN ONE SINGLETON OF ACTIVE PLAYER 
            {
                //
                // Go to the next state: selectCrownableDestination => selectCrown
                //
                $this->gamestate->nextState( 'selectCrownableDestination' );

                return;
            }
        }
    }
    else //  DOUBLE ORIGIN
    {
        //
        //  CHECK IF DESTINATION NOT IN HOME ROW
        //
        if (    ! ( $y == 0 || $y == $N - 1 )    ) // DESTINATION NOT IN HOME ROW
        {
            //
            // CHECK IF DESTINATION IS OCCUPIED (BY SINGLETON AS THE ONLY POSSIBLE OCCUPIER)    ####  TRANSPOSE  ####
            //
            $destination_checker_id = self::getCheckerID ( $x, $y );

            if ( $destination_checker_id !== NULL )    // DESTINATION IS SINGLETON (NOT IN HOME ROW)
            {
                //
                // REMOVE CROWN FROM ORIGIN AND UNSELECT ORIGIN IN DATABASE 
                //
                $sql = "UPDATE board SET bullseye_id = NULL, is_origin_selected = 0 WHERE ( board_x, board_y ) = ( $selected_x, $selected_y )";  
    
                self::DbQuery( $sql );


                //
                // ADD CROWN TO DESTINATION IN DATABASE 
                //
                $sql = "UPDATE board SET bullseye_id = $origin_bullseye_id WHERE ( board_x, board_y ) = ( $x, $y )";  
    
                self::DbQuery( $sql );
        
                // 
                //  NOTIFY FRONTEND ABOUT CROWN SLIDE 
                //
                self::notifyAllPlayers( "slidCrown", clienttranslate( '' ), 
                    array(
                    'slid_crown_id' => $origin_bullseye_id,
                    'destination_x' => $x,
                    'destination_y' => $y
                    ) );


                // 
                // UPDATE HISTORY PANEL ABOUT TRANSPOSE CROWN SLIDE 
                //
                $x_from_coord = chr( ord( "A" ) + $selected_x );
                $y_from_coord = $selected_y + 1;

                $x_to_coord = chr( ord( "A" ) + $x );
                $y_to_coord = $y + 1;

                self::notifyAllPlayers( "playerHistory", clienttranslate( '${player_name} ${x_from_coord}${y_from_coord}-${x_to_coord}${y_to_coord} transpose' ),     
                    array(
                        'player_name' => self::getActivePlayerName(),
                        'x_from_coord' => $x_from_coord,
                        'y_from_coord' => $y_from_coord,
                        'x_to_coord' => $x_to_coord,
                        'y_to_coord' => $y_to_coord
                    ) );


                // 
                // SEE IF ORIGIN IN FAR ROW 
                // 
                if ( $selected_y == 0 || $selected_y == $N - 1 )
                {
                    //
                    // SET is_uncrowned = 1 FOR ORIGIN IN DATABASE 
                    //
                    $sql = "UPDATE board SET is_uncrowned = 1 WHERE ( board_x, board_y ) = ( $selected_x, $selected_y )";  
    
                    self::DbQuery( $sql );


                    $number_of_singletons = self::getNumberOfSingletons ( $active_player_id, $board, $bullseye_IDs, $N );


                    if ( $number_of_singletons == 1  ) // ONLY ONE SINGLETON OF ACTIVE PLAYER 
                    {
                        //
                        // Go to the next state: selectCrownableDestNoCrownsAvailable => nextPlayer
                        //
                        $this->gamestate->nextState( 'selectCrownableDestNoCrownsAvailable' );

                        return;
                    }
                    else // MORE THAN ONE SINGLETON OF ACTIVE PLAYER 
                    {
                        //
                        // Go to the next state: selectCrownableDestination => selectCrown
                        //
                        $this->gamestate->nextState( 'selectCrownableDestination' );

                        return;
                    }
                }
            }
            else  // DESTINATION IS EMPTY SQUARE (NOT IN HOME ROW)
            {
                //
                // REMOVE DOUBLE FROM ORIGIN AND UNSELECT ORIGIN IN DATABASE 
                //
                $sql = "UPDATE board SET board_player = NULL, checker_id = NULL, bullseye_id = NULL, is_origin_selected = 0 
                                     WHERE ( board_x, board_y ) = ( $selected_x, $selected_y )";  
    
                self::DbQuery( $sql );


                //
                // ADD DOUBLE TO DESTINATION IN DATABASE 
                //
                $sql = "UPDATE board SET board_player = $active_player_id, checker_id = $origin_base_checker_id, bullseye_id = $origin_bullseye_id 
                                 WHERE ( board_x, board_y ) = ( $x, $y )";  
    
                self::DbQuery( $sql );
        
                // 
                //  NOTIFY FRONTEND ABOUT DOUBLE SLIDE 
                //
                self::notifyAllPlayers( "slidDouble", clienttranslate( '' ), 
                     array(
                     'slid_base_checker_id' => $origin_base_checker_id,
                     'slid_crown_id' => $origin_bullseye_id,
                     'destination_x' => $x,
                     'destination_y' => $y
                     ) );


                // 
                // UPDATE HISTORY PANEL ABOUT DOUBLE SLIDE
                //
                $x_from_coord = chr( ord( "A" ) + $selected_x );
                $y_from_coord = $selected_y + 1;

                $x_to_coord = chr( ord( "A" ) + $x );
                $y_to_coord = $y + 1;

                self::notifyAllPlayers( "playerHistory", clienttranslate( '${player_name} ${x_from_coord}${y_from_coord}-${x_to_coord}${y_to_coord} double' ),     
                    array(
                        'player_name' => self::getActivePlayerName(),
                        'x_from_coord' => $x_from_coord,
                        'y_from_coord' => $y_from_coord,
                        'x_to_coord' => $x_to_coord,
                        'y_to_coord' => $y_to_coord
                    ) );
            }
        }
        else    //  DESINATION IN HOME ROW
        {
            //
            //  INCREMENT REMOVED CHECKERS FOR ACTIVE PLAYER 
            //
            //  NOTIFY FRONTEND TO UPDATE PLAYER PANEL 
            //
            $player_color = self::getPlayerColor ( $active_player_id );

            if ( $player_color == "ff0000" )    // RED PLAYER
            {
                //
                //  INCREMENT RED'S REMOVED CHECKERS 
                //
                self::incGameStateValue( "removed_red_checkers", 1 );

                //
                //  UPDATE PLAYER PANEL FOR RED
                //
                self::notifyAllPlayers("playerPanel",
                    "",
                    array(
                    "removed_checkers" => self::getGameStateValue( "removed_red_checkers" ),
                    "player_id" => $active_player_id ));
            }
            else // YELLOW PLAYER 
            {
                //
                //  INCREMENT YELLOW'S REMOVED CHECKERS 
                //
                self::incGameStateValue( "removed_yellow_checkers", 1 );

                //
                //  UPDATE PLAYER PANEL FOR YELLOW
                //
                self::notifyAllPlayers("playerPanel",
                    "",
                    array(
                    "removed_checkers" => self::getGameStateValue( "removed_yellow_checkers" ),
                    "player_id" => $active_player_id ));
            }



            //
            // CHECK IF DESTINATION IS OCCUPIED (BY SINGLETON AS THE ONLY POSSIBLE OCCUPIER)
            //
            $destination_checker_id = self::getCheckerID ( $x, $y );

            if ( $destination_checker_id !== NULL )    // HOME ROW DESTINATION IS SINGLETON          ##########   THERE WON'T BE AN UNCROWNED CHECKER    ##########
            {
                //
                // REMOVE CROWN FROM ORIGIN IN DATABASE AND SET is_origin_selected = 0
                //
                $sql = "UPDATE board SET bullseye_id = NULL, is_origin_selected = 0 WHERE ( board_x, board_y ) = ($selected_x, $selected_y)";  
    
                self::DbQuery( $sql );


                //
                // NOTIFY FRONTEND TO REMOVE ORIGIN CROWN
                //
                self::notifyAllPlayers( "transpose_boreOffCrown", clienttranslate( '' ), 
                    array(
                    'player_id' => $active_player_id,
                    'bore_off_crown_id' => $origin_bullseye_id
                    ) );


                // 
                // UPDATE HISTORY PANEL ABOUT TRANSPOSE CROWN SLIDE AND BEAR-OFF
                //
                $x_from_coord = chr( ord( "A" ) + $selected_x );
                $y_from_coord = $selected_y + 1;

                $x_to_coord = chr( ord( "A" ) + $x );
                $y_to_coord = $y + 1;

                self::notifyAllPlayers( "playerHistory", clienttranslate( '${player_name} ${x_from_coord}${y_from_coord}-${x_to_coord}${y_to_coord} transpose, bear-off' ),     
                    array(
                        'player_name' => self::getActivePlayerName(),
                        'x_from_coord' => $x_from_coord,
                        'y_from_coord' => $y_from_coord,
                        'x_to_coord' => $x_to_coord,
                        'y_to_coord' => $y_to_coord
                    ) );
            }
            else  //  HOME ROW DESTINATION IS EMPTY SQUARE 
            {
                //
                // CHECK FOR UNCROWNED ACTIVE PLAYER CHECKER 
                //
                $sql = "SELECT board_x x, board_y y FROM board WHERE board_player = $active_player_id AND is_uncrowned = 1";

                $result = self::DbQuery( $sql );

                $row = $result->fetch_assoc(); 

                if ( $result->num_rows > 0 ) // UNCROWNED CHECKER
                {
                    $uncrowned_x = $row [ "x" ];
                    $uncrowned_y = $row [ "y" ];

                    //
                    // REMOVE BASE CHECKER AND CROWN FROM ORIGIN IN DATABASE AND SET is_origin_selected = 0
                    //
                    $sql = "UPDATE board SET board_player = NULL, checker_id = NULL, bullseye_id = NULL, is_origin_selected = 0 
                            WHERE ( board_x, board_y ) = ($selected_x, $selected_y)";  
    
                    self::DbQuery( $sql );


                    //
                    // ADD REMOVED BULLSEYE ID TO UNCROWNED CHECKER IN DATABASE 
                    //
                    // SET UNCROWNED TO 0 IN DATABASE FOR ACTIVE PLAYER
                    //
                    $sql = "UPDATE board SET bullseye_id = $origin_bullseye_id, is_uncrowned = 0 WHERE ( board_x, board_y ) = ( $uncrowned_x, $uncrowned_y )";  
    
                    self::DbQuery( $sql );


                    //
                    // NOTIFY FRONTEND TO...
                    //      REMOVE ORIGIN BASE CHECKER 
                    //      SLIDE ORIGIN CROWN TO UNCROWNED CHECKER 
                    //
                    self::notifyAllPlayers( "boreOffCrown_addedCrown", clienttranslate( '' ), 
                        array(
                        'player_id' => $active_player_id,
                        'origin_base_checker_id' => $origin_base_checker_id,
                        'origin_crown_id' => $origin_bullseye_id,
                        'uncrowned_x' => $uncrowned_x,
                        'uncrowned_y' => $uncrowned_y
                        ) );

                    // 
                    // UPDATE HISTORY PANEL ABOUT SLIDE, BEAR-OFF, AND CROWN
                    //
                    $x_from_coord = chr( ord( "A" ) + $selected_x );
                    $y_from_coord = $selected_y + 1;

                    $x_to_coord = chr( ord( "A" ) + $x );
                    $y_to_coord = $y + 1;

                    self::notifyAllPlayers( "playerHistory", clienttranslate( '${player_name} ${x_from_coord}${y_from_coord}-${x_to_coord}${y_to_coord} 
                                                                               bear-off, crown' ),     
                        array(
                            'player_name' => self::getActivePlayerName(),
                            'x_from_coord' => $x_from_coord,
                            'y_from_coord' => $y_from_coord,
                            'x_to_coord' => $x_to_coord,
                            'y_to_coord' => $y_to_coord
                        ) );

                }   
                else    //  NO UNCROWNED CHECKER
                {
                    //
                    // REMOVE BASE CHECKER AND CROWN FROM ORIGIN IN DATABASE AND SET is_origin_selected = 0
                    //
                    $sql = "UPDATE board SET board_player = NULL, checker_id = NULL, bullseye_id = NULL, is_origin_selected = 0 
                            WHERE ( board_x, board_y ) = ($selected_x, $selected_y)";  
    
                    self::DbQuery( $sql );


                    //
                    // ADD ORIGIN BASE CHECKER TO HOME ROW DESTINATION IN DATABASE 
                    //
                    $sql = "UPDATE board SET board_player = $active_player_id, checker_id = $origin_base_checker_id 
                                 WHERE ( board_x, board_y ) = ( $x, $y )";  
    
                    self::DbQuery( $sql );
        

                    //
                    // NOTIFY FRONTEND TO...
                    //      REMOVE ORIGIN CROWN 
                    //      SLIDE ORIGIN BASE CHECKER FROM ORIGIN TO DESTINATION 
                    //
                    self::notifyAllPlayers( "boreOffCrown", clienttranslate( '' ), 
                        array(
                        'player_id' => $active_player_id,
                        'origin_base_checker_id' => $origin_base_checker_id,
                        'origin_crown_id' => $origin_bullseye_id,
                        'destination_x' => $x,
                        'destination_y' => $y
                        ) );


                    // 
                    // UPDATE HISTORY PANEL ABOUT SLIDE AND BEAR-OFF
                    //
                    $x_from_coord = chr( ord( "A" ) + $selected_x );
                    $y_from_coord = $selected_y + 1;

                    $x_to_coord = chr( ord( "A" ) + $x );
                    $y_to_coord = $y + 1;

                    self::notifyAllPlayers( "playerHistory", clienttranslate( '${player_name} ${x_from_coord}${y_from_coord}-${x_to_coord}${y_to_coord} 
                                                                               bear-off' ),     
                        array(
                            'player_name' => self::getActivePlayerName(),
                            'x_from_coord' => $x_from_coord,
                            'y_from_coord' => $y_from_coord,
                            'x_to_coord' => $x_to_coord,
                            'y_to_coord' => $y_to_coord
                        ) );


                }
            }
        }
    }

    //
    // Go to the next state: selectDestination => nextPlayer
    //
    $this->gamestate->nextState( 'selectDestination' );
}






//
//  SELECT CROWN
//
//  Remove selected singleton from database 
//
//  Add new crown to uncrowned checker and set is_uncrowned = 0
//
//  Notify frontend that crown was selected
//
function selectCrown ( $x, $y )
{
    self::checkAction( 'selectCrown' ); 

    $active_player_id = self::getActivePlayerId();

    $player_color = self::getPlayerColor ( $active_player_id );


    $selected_crown_id = self::getCheckerID ( $x, $y );


    //
    // REMOVE SELECTED SINGLETON FROM DATABASE 
    //
    $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ( $x, $y )";  
    
    self::DbQuery( $sql );


    //
    // ADD BULLSEYE TO UNCROWNED CHECKER AND SET is_uncrowned = 0 IN DATABASE 
    //
    $is_uncrowned_coords = self::getUncrownedCheckerCoords ( $active_player_id );

    $is_uncrowned_x = $is_uncrowned_coords [ 0 ];

    $is_uncrowned_y = $is_uncrowned_coords [ 1 ];


    if ( $player_color == "ff0000" )    // RED PLAYER
    {
        $bullseye_id = self::getGameStateValue ( "red_bullseye_id");

        self::incGameStateValue ( "red_bullseye_id", 1);
    }
    else    // YELLOW PLAYER
    {
        $bullseye_id = self::getGameStateValue ( "yellow_bullseye_id");

        self::incGameStateValue ( "yellow_bullseye_id", 1);
    }


    $sql = "UPDATE board SET bullseye_id = $bullseye_id, is_uncrowned = 0 WHERE ( board_x, board_y ) = ( $is_uncrowned_x, $is_uncrowned_y )";  
    
    self::DbQuery( $sql );


    //
    //  NOTIFY FRONTEND ABOUT CROWNING 
    //
    //  REMOVE SINGLETON AND ADD BULLSEYE
    //
    self::notifyAllPlayers( "uncrownedCrowned", clienttranslate( '' ), 
        array(
            'player_id' => $active_player_id,
            'selected_crown_id' => $selected_crown_id,
            'bullseye_id' => $bullseye_id,
            'crowned_x' => $is_uncrowned_x,
            'crowned_y' => $is_uncrowned_y
            ) );


    // 
    // UPDATE HISTORY PANEL ABOUT CROWNING
    //
    $x_from_coord = chr( ord( "A" ) + $x );
    $y_from_coord = $y + 1;

    $x_to_coord = chr( ord( "A" ) + $is_uncrowned_x );
    $y_to_coord = $is_uncrowned_y + 1;

    self::notifyAllPlayers( "playerHistory", clienttranslate( '${player_name} ${x_from_coord}${y_from_coord}-${x_to_coord}${y_to_coord} crown' ),     
        array(
            'player_name' => self::getActivePlayerName(),
            'x_from_coord' => $x_from_coord,
            'y_from_coord' => $y_from_coord,
            'x_to_coord' => $x_to_coord,
            'y_to_coord' => $y_to_coord
        ) );



    //
    // Go to the next state: selectCrown => nextPlayer
    //
    $this->gamestate->nextState( 'selectCrown' );
}
    




//
//  REMOVE IMPASSE CHECKER 
//
//  Increment removed checkers for active player color 
//
//
//  If it's a singleton
//      Remove from database
//      Set is_uncrowned = 0
//
//      Notify frontend about removed impasse singleton
//
//      State change: removeImpasseChecker => nextPlayer
//
//  Else if it's a double
//      Remove the crown in the database
//
//      If there's an uncrowned checker 
//          Remove the base checker in the database 
//          Add a crown to the uncrowned checker in the database 
//
//          Notify the frontend to ...
//              Slide the removable impasse crown to the uncrowned checker
//              Set is_uncrowned = 0
//              Remove the base checker
//
//      Else (no uncrowned checker)
//          Notify frontend to remove the removed impasse crown
//
//          If base checker is in far row ( $selected_y == 0 or $selected_y == $N - 1 )
//              Set is_uncrowned = 1 in database 
//
//              If there are any other singletons
//                  State change: selectCrownableDestination => selectCrown 
//                  return
// 
//              Else (no other singletons) 
//                  State change: selectCrownableDestNoCrownsAvailable => nextPlayer 
//                  return
//      
//      State change: removeImpasseChecker => nextPlayer
//
//
function removeImpasseChecker ( $x, $y )
{
    self::checkAction( 'removeImpasseChecker' );  
    
    $active_player_id = self::getActivePlayerId(); 

        
    $board = self::getBoard();


	//Get the board size
    $N=self::getGameStateValue("board_size");

    $checker_id = self::getCheckerID ( $x, $y );

    $bullseye_id = self::getBullseyeID ( $x, $y );

    if ( $bullseye_id == NULL )
    {
        $is_singleton = true;
    }
    else 
    {
        $is_singleton = false;
    }


    //
    //  INCREMENT REMOVED CHECKERS FOR ACTIVE PLAYER 
    //
    //  NOTIFY FRONTEND TO UPDATE PLAYER PANEL 
    //
    $player_color = self::getPlayerColor ( $active_player_id );

    if ( $player_color == "ff0000" )    // RED PLAYER
    {
        //
        //  INCREMENT RED'S REMOVED CHECKERS 
        //
        self::incGameStateValue( "removed_red_checkers", 1 );

        //
        //  UPDATE PLAYER PANEL FOR RED
        //
        self::notifyAllPlayers("playerPanel",
            "",
            array(
            "removed_checkers" => self::getGameStateValue( "removed_red_checkers" ),
            "player_id" => $active_player_id ));
    }
    else // YELLOW PLAYER 
    {
        //
        //  INCREMENT YELLOW'S REMOVED CHECKERS 
        //
        self::incGameStateValue( "removed_yellow_checkers", 1 );

        //
        //  UPDATE PLAYER PANEL FOR YELLOW
        //
        self::notifyAllPlayers("playerPanel",
            "",
            array(
            "removed_checkers" => self::getGameStateValue( "removed_yellow_checkers" ),
            "player_id" => $active_player_id ));
    }



    if ( $is_singleton )    // SINGLETON
    {
        //
        //  REMOVE IMPASSE SINGLETON FROM DATABASE 
        //
        //  SET is_uncrowned = 0
        //
        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL, is_uncrowned = 0 WHERE ( board_x, board_y ) = ( $x, $y )";  
    
        self::DbQuery( $sql );

        
        //
        // NOTIFY FRONTEND ABOUT REMOVED IMPASSE SINGLETON
        //
        self::notifyAllPlayers( "impasseSingletonRemoved", clienttranslate( '' ), 
            array(
            'player_id' => $active_player_id,
            'removed_impasse_singleton_id' => $checker_id
            ) );
    

        // 
        // UPDATE HISTORY PANEL ABOUT REMOVED IMPASSE SINGLETON 
        //
        $x_removed_impasse_singleton_display_coord = chr( ord( "A" ) + $x );
        $y_removed_impasse_singleton_display_coord = $y + 1;

        self::notifyAllPlayers( "playerHistory", clienttranslate( '${player_name} ${x_removed_impasse_singleton_display_coord}${y_removed_impasse_singleton_display_coord} impasse' ),     
                array(
                    'player_name' => self::getActivePlayerName(),
                    'x_removed_impasse_singleton_display_coord' => $x_removed_impasse_singleton_display_coord,
                    'y_removed_impasse_singleton_display_coord' => $y_removed_impasse_singleton_display_coord
                ) );


        //
        // Go to the next state - removeImpasseChecker => nextPlayer
        //
        $this->gamestate->nextState( 'removeImpasseChecker' );           
    }
    else // DOUBLE 
    {
        // 
        // REMOVE CROWN FROM DATABASE 
        //
        $sql = "UPDATE board SET bullseye_id = NULL WHERE ( board_x, board_y) = ($x, $y)";  

        self::DbQuery( $sql );


        //
        // CHECK FOR UNCROWNED ACTIVE PLAYER CHECKER 
        //
        $sql = "SELECT board_x x, board_y y FROM board WHERE board_player = $active_player_id AND is_uncrowned = 1";

        $result = self::DbQuery( $sql );

        $row = $result->fetch_assoc(); 

        if ( $result->num_rows > 0 ) // UNCROWNED CHECKER
        {
            $uncrowned_x = $row [ "x" ];
            $uncrowned_y = $row [ "y" ];

            /*
            self::notifyAllPlayers( "backendMessage", clienttranslate( '$uncrowned_x, $uncrowned_y: ${var_1}, ${var_2} ' ), 
            array(
                    "var_1" => $uncrowned_x,
                    "var_2" => $uncrowned_y
            ) );
            */

            //
            // REMOVE BASE CHECKER FROM $x,$y IN DATABASE
            //
            $sql = "UPDATE board SET board_player = NULL, checker_id = NULL WHERE ( board_x, board_y ) = ($x, $y)";  
    
            self::DbQuery( $sql );


            //
            // ADD REMOVED BULLSEYE ID TO UNCROWNED CHECKER IN DATABASE 
            //
            // SET UNCROWNED TO 0 IN DATABASE FOR RED
            //
            $sql = "UPDATE board SET bullseye_id = $bullseye_id, is_uncrowned = 0 WHERE ( board_x, board_y ) = ( $uncrowned_x, $uncrowned_y )";  
    
            self::DbQuery( $sql );


            //
            // NOTIFY FRONTEND ABOUT REMOVED BASE CHECKER AND ADDED CROWN
            //
            self::notifyAllPlayers( "removedImpasseCrown_addedCrown", clienttranslate( '' ), 
                array(
                'player_id' => $active_player_id,
                'removed_base_checker_id' => $checker_id,
                'crown_id' => $bullseye_id,
                'uncrowned_x' => $uncrowned_x,
                'uncrowned_y' => $uncrowned_y
                ) );



            // 
            // UPDATE HISTORY PANEL ABOUT CROWNING
            //
            $x_from_coord = chr( ord( "A" ) + $x );
            $y_from_coord = $y + 1;

            $x_to_coord = chr( ord( "A" ) + $uncrowned_x );
            $y_to_coord = $uncrowned_y + 1;

            self::notifyAllPlayers( "playerHistory", clienttranslate( '${player_name} ${x_from_coord}${y_from_coord}-${x_to_coord}${y_to_coord} impasse/crown' ),     
                array(
                    'player_name' => self::getActivePlayerName(),
                    'x_from_coord' => $x_from_coord,
                    'y_from_coord' => $y_from_coord,
                    'x_to_coord' => $x_to_coord,
                    'y_to_coord' => $y_to_coord
                ) );

        } 
        else // NO UNCROWNED ACTIVE PLAYER CHECKER 
        {
            //
            // NOTIFY FRONTEND TO REMOVE IMPASSE CROWN 
            //
            self::notifyAllPlayers( "removedImpasseCrown", clienttranslate( '' ), 
                array(
                'player_id' => $active_player_id,
                'removed_impasse_crown_id' => $bullseye_id
                ) );


            // 
            // UPDATE HISTORY PANEL ABOUT REMOVED IMPASSE CROWN 
            //
            $x_removed_impasse_crown_display_coord = chr( ord( "A" ) + $x );
            $y_removed_impasse_crown_display_coord = $y + 1;

            self::notifyAllPlayers( "playerHistory", clienttranslate( '${player_name} ${x_removed_impasse_crown_display_coord}${y_removed_impasse_crown_display_coord} impasse' ),     
                array(
                    'player_name' => self::getActivePlayerName(),
                    'x_removed_impasse_crown_display_coord' => $x_removed_impasse_crown_display_coord,
                    'y_removed_impasse_crown_display_coord' => $y_removed_impasse_crown_display_coord
                    ) );


            // 
            // SEE IF IMPASSE SQUARE IN RED'S FAR ROW OR YELLOW'S FAR ROW
            // 
            if (    ( $player_color == "ff0000" && $y == $N - 1 ) || ( $player_color == "ffff00" && $y == 0 )    )
            {
                //
                // SET is_uncrowned = 1 FOR IMPASSE CHECKER IN DATABASE 
                //
                $sql = "UPDATE board SET is_uncrowned = 1 WHERE ( board_x, board_y ) = ( $x, $y )";  
    
                self::DbQuery( $sql );


                $bullseye_IDs = self::getBullseyeIDs ( );

                $number_of_singletons = self::getNumberOfSingletons ( $active_player_id, $board, $bullseye_IDs, $N );


                if ( $number_of_singletons == 1  ) // ONLY ONE SINGLETON OF ACTIVE PLAYER 
                {
                    //
                    // Go to the next state: removeImpasseChecker_createUncrowned_noCrownsAvailable => nextPlayer
                    //
                    $this->gamestate->nextState( 'removeImpasseChecker_createUncrowned_noCrownsAvailable' );

                    return;
                }
                else // MORE THAN ONE SINGLETON OF ACTIVE PLAYER 
                {
                    //
                    // Go to the next state: removeImpasseChecker_createUncrowned => selectCrown
                    //
                    $this->gamestate->nextState( 'removeImpasseChecker_createUncrowned' );

                    return;
                }
            }
        }

        //
        // Go to the next state - removeImpasseChecker => nextPlayer
        //
        $this->gamestate->nextState( 'removeImpasseChecker' );               
    }
}








function getNumberOfSingletons ( $player_id, $board, $bullseye_IDs, $N )
{
    $number_of_singletons = 0;

    for ( $i = 0; $i < $N; $i++ )
    {
        for ( $j = 0; $j < $N; $j++ )
        {
            if (    ( $i + $j ) % 2 == 0    )
            {
                if ( $board [ $i ] [ $j ] == $player_id && $bullseye_IDs [ $i ] [ $j ] == NULL )  // SINGLETON
                {
                    ++$number_of_singletons;
                }
            }
        }
    }

    return $number_of_singletons;
}




    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

function argSelectOrigin()
{
    return array(
        'selectableOrigins' => self::getSelectableOrigins ( self::getActivePlayerId() )
    );
}

function argSelectDestination()
{
    return array(
        'selectableDestinations' => self::getSelectableDestinations ( self::getActivePlayerId() )
    );
}

function argSelectCrown()
{
    return array(
        'selectableCrowns' => self::getSelectableCrowns ( self::getActivePlayerId() )
    );
}

function argRemoveImpasseChecker()
{
    return array(
        'removableImpasseCheckers' => self::getRemovableImpasseCheckers ( self::getActivePlayerId() )
    );
}


//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

//
//  If active player removed all his checkers 
//      Active player wins 
//      End game 
//
//  Switch to next active player 
//
//  If new active player has moves available 
//      Switch state: movesAvailable => selectOrigin
//
//  Else 
//      Switch state: noMovesAvailable => removeImpasseChecker
//
//
function stNextPlayer()
{
    $N = self::getGameStateValue("board_size");


    $active_player_id = self::getActivePlayerId();

    $active_player_color = self::getPlayerColor ( $active_player_id );


    // 
    //  END OF GAME CHECK 
    //
    if ( $active_player_color == "ff0000" )     // RED ACTIVE PLAYER
    {
        $removed_red_checkers = self::getGameStateValue( "removed_red_checkers" );

        if (    $N == 8 && $removed_red_checkers == 12 
             || $N == 10 && $removed_red_checkers == 20    )
        {
            $sql = "UPDATE player
                    SET player_score = 1 WHERE player_id = $active_player_id";
                    self::DbQuery( $sql );


            $newScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );

            self::notifyAllPlayers( "newScores", "", array(
                "scores" => $newScores
            ) );

            $this->gamestate->nextState( 'endGame' );

            return;
        }
    }
    else                                        // YELLOW ACTIVE PLAYER 
    {
        $removed_yellow_checkers = self::getGameStateValue( "removed_yellow_checkers" );

        if (    $N == 8 && $removed_yellow_checkers == 12 
             || $N == 10 && $removed_yellow_checkers == 20    )
        {
            $sql = "UPDATE player
                    SET player_score = 1 WHERE player_id = $active_player_id";
                    self::DbQuery( $sql );


            $newScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );

            self::notifyAllPlayers( "newScores", "", array(
                "scores" => $newScores
            ) );

            $this->gamestate->nextState( 'endGame' );

            return;
        }
    }



    //
    //  SWITCH ACTIVE PLAYER ID TO NEXT PLAYER 
    //
    $active_player_id = self::activeNextPlayer();



    //  
    //  SEE IF ACTIVE PLAYER HAS ANY MOVES AVAILABLE 
    //
    $has_any_moves = false;

    $board = self::getBoard();


    for( $x=0; $x<$N; $x++ )
    {
        for( $y=0; $y<$N; $y++ )
        {
            if (    ( $x + $y ) % 2 == 0    )
            {
                if ( $board [ $x ] [ $y ] == $active_player_id )
                {
                    if ( self::hasMoves ( $x, $y, $board, $active_player_id ) )
                    {
                        $has_any_moves = true;

                        break 2;
                    }
                }
            }
        }
    }



    //
    //  IF ACTIVE PLAYER HAS ANY MOVES, GO TO STATE SELECT ORIGIN 
    //
    //  IF NOT, GO TO STATE REMOVE IMPASSE CHECKER
    //
    self::giveExtraTime( $active_player_id );


    if ( $has_any_moves )
    {
        //
        // Game state: movesAvailable => selectOrigin 
        //
        $this->gamestate->nextState( 'movesAvailable' );
    }
    else 
    {
        //
        // Game state: noMovesAvailable => removeImpasseChecker 
        //
        $this->gamestate->nextState( 'noMovesAvailable' );
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


    function initializeBoard ( $red_player_id, $yellow_player_id )
    {
        $N=self::getGameStateValue("board_size");

        $sql = "INSERT INTO board (board_x, board_y, board_player, checker_id, bullseye_id, is_origin_selected, is_uncrowned) VALUES ";

        $sql_values = array();

        switch ( $N )
        {
            case 8:
                // 
                //  RED CHECKERS
                //
                //      Bottom row left to right and proceeding upwards
                //
                $red_checker_id = self::getGameStateValue( "red_checker_id" );          // Square 0,0
                $sql_values[] = "(0, 0, $red_player_id, $red_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "red_checker_id", 1 );

                $red_checker_id = self::getGameStateValue( "red_checker_id" );          // Square 4,0
                $sql_values[] = "(4, 0, $red_player_id, $red_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "red_checker_id", 1 );

                $red_checker_id = self::getGameStateValue( "red_checker_id" );          // Square 3,1
                $sql_values[] = "(3, 1, $red_player_id, $red_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "red_checker_id", 1 );

                $red_checker_id = self::getGameStateValue( "red_checker_id" );          // Square 7,1
                $sql_values[] = "(7, 1, $red_player_id, $red_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "red_checker_id", 1 );


                $red_checker_id = self::getGameStateValue( "red_checker_id" );          // Square 2,6
                $red_bullseye_id = self::getGameStateValue( "red_bullseye_id" );          
                $sql_values[] = "(2, 6, $red_player_id, $red_checker_id, $red_bullseye_id, 0, 0)";                   
                self::incGameStateValue( "red_checker_id", 1 );
                self::incGameStateValue( "red_bullseye_id", 1 );

                $red_checker_id = self::getGameStateValue( "red_checker_id" );          // Square 6,6
                $red_bullseye_id = self::getGameStateValue( "red_bullseye_id" );          
                $sql_values[] = "(6, 6, $red_player_id, $red_checker_id, $red_bullseye_id, 0, 0)";                   
                self::incGameStateValue( "red_checker_id", 1 );
                self::incGameStateValue( "red_bullseye_id", 1 );

                $red_checker_id = self::getGameStateValue( "red_checker_id" );          // Square 1,7
                $red_bullseye_id = self::getGameStateValue( "red_bullseye_id" );          
                $sql_values[] = "(1, 7, $red_player_id, $red_checker_id, $red_bullseye_id, 0, 0)";                   
                self::incGameStateValue( "red_checker_id", 1 );
                self::incGameStateValue( "red_bullseye_id", 1 );

                $red_checker_id = self::getGameStateValue( "red_checker_id" );          // Square 5,7
                $red_bullseye_id = self::getGameStateValue( "red_bullseye_id" );          
                $sql_values[] = "(5, 7, $red_player_id, $red_checker_id, $red_bullseye_id, 0, 0)";                   
                self::incGameStateValue( "red_checker_id", 1 );
                self::incGameStateValue( "red_bullseye_id", 1 );


                // 
                //  YELLOW CHECKERS
                //
                //      Bottom row left to right and proceeding upwards
                //
                $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );          // Square 2,0
                $yellow_bullseye_id = self::getGameStateValue( "yellow_bullseye_id" );          
                $sql_values[] = "(2, 0, $yellow_player_id, $yellow_checker_id, $yellow_bullseye_id, 0, 0)";                   
                self::incGameStateValue( "yellow_checker_id", 1 );
                self::incGameStateValue( "yellow_bullseye_id", 1 );

                $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );          // Square 6,0
                $yellow_bullseye_id = self::getGameStateValue( "yellow_bullseye_id" );          
                $sql_values[] = "(6, 0, $yellow_player_id, $yellow_checker_id, $yellow_bullseye_id, 0, 0)";                   
                self::incGameStateValue( "yellow_checker_id", 1 );
                self::incGameStateValue( "yellow_bullseye_id", 1 );

                $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );          // Square 1,1
                $yellow_bullseye_id = self::getGameStateValue( "yellow_bullseye_id" );          
                $sql_values[] = "(1, 1, $yellow_player_id, $yellow_checker_id, $yellow_bullseye_id, 0, 0)";                   
                self::incGameStateValue( "yellow_checker_id", 1 );
                self::incGameStateValue( "yellow_bullseye_id", 1 );

                $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );          // Square 5,1
                $yellow_bullseye_id = self::getGameStateValue( "yellow_bullseye_id" );          
                $sql_values[] = "(5, 1, $yellow_player_id, $yellow_checker_id, $yellow_bullseye_id, 0, 0)";                   
                self::incGameStateValue( "yellow_checker_id", 1 );
                self::incGameStateValue( "yellow_bullseye_id", 1 );


                $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );          // Square 0,6
                $sql_values[] = "(0, 6, $yellow_player_id, $yellow_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "yellow_checker_id", 1 );

                $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );          // Square 4,6
                $sql_values[] = "(4, 6, $yellow_player_id, $yellow_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "yellow_checker_id", 1 );

                $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );          // Square 3,7
                $sql_values[] = "(3, 7, $yellow_player_id, $yellow_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "yellow_checker_id", 1 );

                $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );          // Square 7,7
                $sql_values[] = "(7, 7, $yellow_player_id, $yellow_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "yellow_checker_id", 1 );

                break;


            case 10:
                // 
                //  RED CHECKERS
                //
                //      Bottom row left to right and proceeding upwards
                //
                $red_checker_id = self::getGameStateValue( "red_checker_id" );          // Square 2,0
                $sql_values[] = "(2, 0, $red_player_id, $red_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "red_checker_id", 1 );

                $red_checker_id = self::getGameStateValue( "red_checker_id" );          // Square 6,0
                $sql_values[] = "(6, 0, $red_player_id, $red_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "red_checker_id", 1 );

                $red_checker_id = self::getGameStateValue( "red_checker_id" );          // Square 1,1
                $sql_values[] = "(1, 1, $red_player_id, $red_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "red_checker_id", 1 );

                $red_checker_id = self::getGameStateValue( "red_checker_id" );          // Square 5,1
                $sql_values[] = "(5, 1, $red_player_id, $red_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "red_checker_id", 1 );

                $red_checker_id = self::getGameStateValue( "red_checker_id" );          // Square 9,1
                $sql_values[] = "(9, 1, $red_player_id, $red_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "red_checker_id", 1 );

                $red_checker_id = self::getGameStateValue( "red_checker_id" );          // Square 0,2
                $sql_values[] = "(0, 2, $red_player_id, $red_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "red_checker_id", 1 );

                $red_checker_id = self::getGameStateValue( "red_checker_id" );          // Square 4,2
                $sql_values[] = "(4, 2, $red_player_id, $red_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "red_checker_id", 1 );

                $red_checker_id = self::getGameStateValue( "red_checker_id" );          // Square 8,2
                $sql_values[] = "(8, 2, $red_player_id, $red_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "red_checker_id", 1 );


                $red_checker_id = self::getGameStateValue( "red_checker_id" );          // Square 3,7
                $red_bullseye_id = self::getGameStateValue( "red_bullseye_id" );          
                $sql_values[] = "(3, 7, $red_player_id, $red_checker_id, $red_bullseye_id, 0, 0)";                   
                self::incGameStateValue( "red_checker_id", 1 );
                self::incGameStateValue( "red_bullseye_id", 1 );

                $red_checker_id = self::getGameStateValue( "red_checker_id" );          // Square 7,7
                $red_bullseye_id = self::getGameStateValue( "red_bullseye_id" );          
                $sql_values[] = "(7, 7, $red_player_id, $red_checker_id, $red_bullseye_id, 0, 0)";                   
                self::incGameStateValue( "red_checker_id", 1 );
                self::incGameStateValue( "red_bullseye_id", 1 );

                $red_checker_id = self::getGameStateValue( "red_checker_id" );          // Square 2,8
                $red_bullseye_id = self::getGameStateValue( "red_bullseye_id" );          
                $sql_values[] = "(2, 8, $red_player_id, $red_checker_id, $red_bullseye_id, 0, 0)";                   
                self::incGameStateValue( "red_checker_id", 1 );
                self::incGameStateValue( "red_bullseye_id", 1 );

                $red_checker_id = self::getGameStateValue( "red_checker_id" );          // Square 6,8
                $red_bullseye_id = self::getGameStateValue( "red_bullseye_id" );          
                $sql_values[] = "(6, 8, $red_player_id, $red_checker_id, $red_bullseye_id, 0, 0)";                   
                self::incGameStateValue( "red_checker_id", 1 );
                self::incGameStateValue( "red_bullseye_id", 1 );

                $red_checker_id = self::getGameStateValue( "red_checker_id" );          // Square 1,9
                $red_bullseye_id = self::getGameStateValue( "red_bullseye_id" );          
                $sql_values[] = "(1, 9, $red_player_id, $red_checker_id, $red_bullseye_id, 0, 0)";                   
                self::incGameStateValue( "red_checker_id", 1 );
                self::incGameStateValue( "red_bullseye_id", 1 );

                $red_checker_id = self::getGameStateValue( "red_checker_id" );          // Square 5,9
                $red_bullseye_id = self::getGameStateValue( "red_bullseye_id" );          
                $sql_values[] = "(5, 9, $red_player_id, $red_checker_id, $red_bullseye_id, 0, 0)";                   
                self::incGameStateValue( "red_checker_id", 1 );
                self::incGameStateValue( "red_bullseye_id", 1 );


                // 
                //  YELLOW CHECKERS
                //
                //      Bottom row left to right and proceeding upwards
                //
                $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );          // Square 4,0
                $yellow_bullseye_id = self::getGameStateValue( "yellow_bullseye_id" );          
                $sql_values[] = "(4, 0, $yellow_player_id, $yellow_checker_id, $yellow_bullseye_id, 0, 0)";                   
                self::incGameStateValue( "yellow_checker_id", 1 );
                self::incGameStateValue( "yellow_bullseye_id", 1 );

                $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );          // Square 8,0
                $yellow_bullseye_id = self::getGameStateValue( "yellow_bullseye_id" );          
                $sql_values[] = "(8, 0, $yellow_player_id, $yellow_checker_id, $yellow_bullseye_id, 0, 0)";                   
                self::incGameStateValue( "yellow_checker_id", 1 );
                self::incGameStateValue( "yellow_bullseye_id", 1 );

                $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );          // Square 3,1
                $yellow_bullseye_id = self::getGameStateValue( "yellow_bullseye_id" );          
                $sql_values[] = "(3, 1, $yellow_player_id, $yellow_checker_id, $yellow_bullseye_id, 0, 0)";                   
                self::incGameStateValue( "yellow_checker_id", 1 );
                self::incGameStateValue( "yellow_bullseye_id", 1 );

                $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );          // Square 7,1
                $yellow_bullseye_id = self::getGameStateValue( "yellow_bullseye_id" );          
                $sql_values[] = "(7, 1, $yellow_player_id, $yellow_checker_id, $yellow_bullseye_id, 0, 0)";                   
                self::incGameStateValue( "yellow_checker_id", 1 );
                self::incGameStateValue( "yellow_bullseye_id", 1 );

                $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );          // Square 2,2
                $yellow_bullseye_id = self::getGameStateValue( "yellow_bullseye_id" );          
                $sql_values[] = "(2, 2, $yellow_player_id, $yellow_checker_id, $yellow_bullseye_id, 0, 0)";                   
                self::incGameStateValue( "yellow_checker_id", 1 );
                self::incGameStateValue( "yellow_bullseye_id", 1 );

                $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );          // Square 6,2
                $yellow_bullseye_id = self::getGameStateValue( "yellow_bullseye_id" );          
                $sql_values[] = "(6, 2, $yellow_player_id, $yellow_checker_id, $yellow_bullseye_id, 0, 0)";                   
                self::incGameStateValue( "yellow_checker_id", 1 );
                self::incGameStateValue( "yellow_bullseye_id", 1 );


                $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );          // Square 1,7
                $sql_values[] = "(1, 7, $yellow_player_id, $yellow_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "yellow_checker_id", 1 );

                $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );          // Square 5,7
                $sql_values[] = "(5, 7, $yellow_player_id, $yellow_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "yellow_checker_id", 1 );

                $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );          // Square 9,7
                $sql_values[] = "(9, 7, $yellow_player_id, $yellow_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "yellow_checker_id", 1 );

                $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );          // Square 0,8
                $sql_values[] = "(0, 8, $yellow_player_id, $yellow_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "yellow_checker_id", 1 );

                $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );          // Square 4,8
                $sql_values[] = "(4, 8, $yellow_player_id, $yellow_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "yellow_checker_id", 1 );

                $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );          // Square 8,8
                $sql_values[] = "(8, 8, $yellow_player_id, $yellow_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "yellow_checker_id", 1 );

                $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );          // Square 3,9
                $sql_values[] = "(3, 9, $yellow_player_id, $yellow_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "yellow_checker_id", 1 );

                $yellow_checker_id = self::getGameStateValue( "yellow_checker_id" );          // Square 7,9
                $sql_values[] = "(7, 9, $yellow_player_id, $yellow_checker_id, NULL, 0, 0)";                   
                self::incGameStateValue( "yellow_checker_id", 1 );

                break;
        }

        
        $sql .= implode( $sql_values, ',' );
        self::DbQuery( $sql );



        //
        //  Load middle rows with NULL values 
        //
        switch ( $N )
        {
            case 8:
                $y_begin_row = 2;
                $y_end_row = 5;

                break;

            case 10:
                $y_begin_row = 3;
                $y_end_row = 6;

                break;
        }

        $sql = "INSERT INTO board (board_x, board_y, board_player, checker_id, bullseye_id, is_origin_selected, is_uncrowned) VALUES ";

        $sql_values = array();

        for ( $x = 0; $x < $N; $x++ )
        {
            for ( $y = $y_begin_row; $y <= $y_end_row; $y++ )
            {
                if (    ( $x + $y ) % 2 == 0    )
                {
                    $sql_values[] = "($x, $y, NULL, NULL, NULL, 0, 0)";                   
                }
            }
        }

        $sql .= implode( $sql_values, ',' );
        self::DbQuery( $sql );


        //
        //  10x10 Lower left corner and upper right corner NULL 
        //
        if ( $N == 10 )
        {
            $sql = "INSERT INTO board (board_x, board_y, board_player, checker_id, bullseye_id, is_origin_selected, is_uncrowned) VALUES ";

            $sql_values = array();

            $sql_values[] = "(0, 0, NULL, NULL, NULL, 0, 0)";                   

            $sql_values[] = "(9, 9, NULL, NULL, NULL, 0, 0)";                   

            $sql .= implode( $sql_values, ',' );
            self::DbQuery( $sql );
        }
    }

}
