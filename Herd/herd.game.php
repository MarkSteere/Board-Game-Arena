<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * Herd implementation : © <Mark Steere> <marksteere@gmail.com>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * herd.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class Herd extends Table
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
            "move_number" => 11,                    // Counter of the number of moves (used to detect first move)
            "black_stone_id" => 20,                 
            "white_stone_id" => 21,                 
            "number_of_black_stones" => 26,         
            "number_of_white_stones" => 27,  
            "rolled_die_0" => 30,
            "rolled_die_1" => 31,
            "rolled_die_0_id" => 32,
            "rolled_die_1_id" => 33,
            "placed_stones_this_turn" => 40,
            "number_of_stones_to_place" => 41,
            "removed_stones_this_turn" => 42,
            "number_of_stones_to_remove" => 43,
            "board_size" => 101                  
        ) );        
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "herd";
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
        self::setGameStateValue( "black_stone_id", 10000 );
        self::setGameStateValue( "white_stone_id", 20000 );
        self::setGameStateValue( "rolled_die_0_id", 30000 );
        self::setGameStateValue( "rolled_die_1_id", 40000 );
        self::setGameStateValue( "number_of_black_stones", 0 );
        self::setGameStateValue( "number_of_white_stones", 0 );

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
            // FOR TEST BOARD FILL
            //
            if( $color == '000000' )    // BLACK PLAYER
                $black_player_id = $player_id;
            else
                $white_player_id = $player_id;

        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );

        self::reloadPlayersBasicInfos();
        
        /************ Start the game initialization *****/

        
        // Initialize player stats
        $this->initStat('player', 'dice_total', 0);

        
        //
        // Load the board with NULL values 
        //
        self::initializeBoard ( );

        self::rollDice ( );



        //
        //  BALANCING RULE 
        //
        //  FOR FIRST ROLL, SET THE NUMBER OF DICE TO PLACE TO THE  *** LOWER ***  DIE VALUE, NOT THE HIGHER 
        //
        $rolled_die_0 = self::getGameStateValue ( "rolled_die_0");

        $rolled_die_1 = self::getGameStateValue ( "rolled_die_1");

        $lower_die_value = min ( $rolled_die_0, $rolled_die_1 );

        self::setGameStateValue ( "number_of_stones_to_place", $lower_die_value );


        //
        //  TEMPORARY - ADD BLACK AND WHITE STONES TO TEST GAME 
        //
        /*
        $black_stone_id = self::getGameStateValue( "black_stone_id" );    
        self::incGameStateValue( "black_stone_id", 1 );
        self::incGameStateValue( "number_of_black_stones", 1 );
                
        $sql = "UPDATE board SET board_player = $black_player_id, stone_id = $black_stone_id WHERE ( board_u, board_v ) = ( 3, 3 )";  
    
        self::DbQuery( $sql );

   
        $white_stone_id = self::getGameStateValue( "white_stone_id" );    
        self::incGameStateValue( "white_stone_id", 1 );
        self::incGameStateValue( "number_of_white_stones", 1 );
                
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $white_stone_id WHERE ( board_u, board_v ) = ( 5, 2 )";  
    
        self::DbQuery( $sql );
   
       

        $white_stone_id = self::getGameStateValue( "white_stone_id" );    
        self::incGameStateValue( "white_stone_id", 1 );
        self::incGameStateValue( "number_of_white_stones", 1 );
                
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $white_stone_id WHERE ( board_u, board_v ) = ( 0, 4 )";  
    
        self::DbQuery( $sql );
   
        $white_stone_id = self::getGameStateValue( "white_stone_id" );    
        self::incGameStateValue( "white_stone_id", 1 );
        self::incGameStateValue( "number_of_white_stones", 1 );
                
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $white_stone_id WHERE ( board_u, board_v ) = ( 1, 3 )";  
    
        self::DbQuery( $sql );
   
        $white_stone_id = self::getGameStateValue( "white_stone_id" );    
        self::incGameStateValue( "white_stone_id", 1 );
        self::incGameStateValue( "number_of_white_stones", 1 );
                
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $white_stone_id WHERE ( board_u, board_v ) = ( 1, 2 )";  
    
        self::DbQuery( $sql );
        */
       

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();



        //
        // NOTIFY FRONTEND TO UPDATE PLAYER PANEL FOR ACTIVE PLAYER
        //
        //      0 STONES / NUMBER OF STONES TO PLACE
        //
//      #################################################################################################  NOT WORKING FOR SOME REASON - NOTIF NOT GETTING CALLED

        /*
        self::notifyAllPlayers( "backendMessage", clienttranslate( '${var_1}, ${var_2}, ${var_3}, ${var_4} ' ), 
            array(
                "var_1" => self::getActivePlayerId(),
                "var_2" => 0,
                "var_3" => 0,
                "var_4" => $lower_die_value
            ) );

//      #################################################################################################  NOT WORKING FOR SOME REASON - NOTIF NOT GETTING CALLED

        self::notifyAllPlayers( "activePlayerPanelAddedStones", clienttranslate( '' ), 
            array(
            'active_player_id' => self::getActivePlayerId(),
            'active_player_number_of_stones' => 0,
            'placed_stones_this_turn' => 0,
            'number_of_stones_to_place' => $lower_die_value
            ) );
        */

//      #################################################################################################  NOT WORKING FOR SOME REASON - NOTIF NOT GETTING CALLED



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

    
        $inactive_player_id = self::getOtherPlayerId($active_player_id);

        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );
  

        //
        // Get board occupied cells
        // 
        $sql = "SELECT board_u u, board_v v, board_player player, stone_id stone_id
                FROM board WHERE board_player IS NOT NULL";

        $result['board'] = self::getObjectListFromDB( $sql );


        //
        // NUMBER OF STONES ON BOARD
        //
        $activePlayerId = self::getActivePlayerId();
        $otherPlayerId = self::getOtherPlayerId($activePlayerId);

        //
        //  NUMBER OF STONES ARRAY 
        //      ACTIVE PLAYER NUMBER OF STONES, INACTIVE PLAYER NUMBER OF STONES 
        //
        $numberOfStones = array();

        if ( $active_player_color == "000000" )     // BLACK PLAYER
        {
            $numberOfStones[$active_player_id] = self::getGameStateValue( "number_of_black_stones" );
            $numberOfStones[$inactive_player_id] = self::getGameStateValue( "number_of_white_stones" );
        }
        else                                        // WHITE PLAYER
        {
            $numberOfStones[$active_player_id] = self::getGameStateValue( "number_of_white_stones" );
            $numberOfStones[$inactive_player_id] = self::getGameStateValue( "number_of_black_stones" );
        }
        
        $result['numberOfStones'] = $numberOfStones;


        $result['placed_stones_this_turn'] = self::getGameStateValue ( "placed_stones_this_turn" );

        $result['number_of_stones_to_place'] = self::getGameStateValue ( "number_of_stones_to_place" );

        $result['removed_stones_this_turn'] = self::getGameStateValue ( "removed_stones_this_turn" );

        $result['number_of_stones_to_remove'] = self::getGameStateValue ( "number_of_stones_to_remove" );



        $rolled_die_0 = self::getGameStateValue ( "rolled_die_0" );
        $rolled_die_1 = self::getGameStateValue ( "rolled_die_1" );

        $rolled_dice = array ( $rolled_die_0, $rolled_die_1 );

        $result['rolled_dice'] = $rolled_dice;


        $rolled_die_0_id = self::getGameStateValue ( "rolled_die_0_id" );
        $rolled_die_1_id = self::getGameStateValue ( "rolled_die_1_id" );

        $rolled_dice_IDs = array ( $rolled_die_0_id, $rolled_die_1_id );

        $result['rolled_dice_IDs'] = $rolled_dice_IDs;


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
        $number_of_black_stones = self::getGameStateValue ( "number_of_black_stones");

        $number_of_white_stones = self::getGameStateValue ( "number_of_white_stones");

        $total_number_of_stones = $number_of_black_stones + $number_of_white_stones;


        $N = self::getGameStateValue ( "board_size" );

        switch ( $N )
        {
            case 4:
                $board_area = 37;
                break;

            case 5:
                $board_area = 61;
                break;

            case 6:
                $board_area = 91;
                break;
        }
            
        return ( $total_number_of_stones / $board_area * 100 );    
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    


//
//  getSelectableCells ( $active_player_id ) 
//  ########################################
//
//  Return array of unoccupied cells which are not surrounded by enemy stones
//              
//
function getSelectableCells ( $active_player_id )
{                       
    $legal_moves = array();

    $board = self::getBoard();


    //
	//Get the board size
    //
    $N=self::getGameStateValue("board_size");
                  

    //
    // VISIT ALL CELLS
    //
    for ( $v = 0; $v < 2*$N-1; $v++ )                   // Start on bottom row.  $v = 0
    {
        $u_left_limit = self::uLeftLimit ( $v, $N );    // Left to right limits of row in hexagonal board
        $u_right_limit = self::uRightLimit ( $v, $N );

        for (    $u = $u_left_limit; $u <= $u_right_limit; ++$u    )  
        {
            if (    $board [ $u ] [ $v ] == NULL    )                  // CURRENT CELL IS UNOCCUPIED
            {
                if (    self::isLegalMove ( $u, $v, $board, $N, $active_player_id )    )      
                {
                    if(    ! isset ( $legal_moves [ $u ] )    )
                        $legal_moves [ $u ] = array();

                    $legal_moves [ $u ] [ $v ] = true;
                }
            }
        }
    }

    return $legal_moves;
}



//
//  getRemovableStones ( $active_player_id ) 
//  ########################################
//
//  Return array of enemy occupied cells
//              
//
function getRemovableStones ( $active_player_id )
{                       
    $removable_stones = array();

    $board = self::getBoard();

    $inactive_player_id = self::getOtherPlayerId ( $active_player_id );

    //
	//Get the board size
    //
    $N=self::getGameStateValue("board_size");
                  

    //
    // VISIT ALL CELLS
    //
    for ( $v = 0; $v < 2*$N-1; $v++ )                   // Start on bottom row.  $v = 0
    {
        $u_left_limit = self::uLeftLimit ( $v, $N );    // Left to right limits of row in hexagonal board
        $u_right_limit = self::uRightLimit ( $v, $N );

        for (    $u = $u_left_limit; $u <= $u_right_limit; ++$u    )  
        {
            if (    $board [ $u ] [ $v ] == $inactive_player_id    )                  // CURRENT CELL IS ENEMY OCCUPIED
            {
                if(    ! isset ( $removable_stones [ $u ] )    )
                    $removable_stones [ $u ] = array();

                $removable_stones [ $u ] [ $v ] = true;
            }
        }
    }

    return $removable_stones;
}



//
//  IS LEGAL MOVE?
//
//  Check if cell unoccupied  
//
//  If occupied                 ####  BUG FIX  ####  This was missing before.  Sometimes stones were being added to the same cell twice when quickly adding stones.
//      Return false
//
//  Check all neighboring cells 
//
//  If any of them are unoccupied or friendly occupied
//      Return true 
//  Else (only surrounded by walls and enemy stones)
//      Return false 
//
function isLegalMove ( $u, $v, $board, $N, $active_player_id )
{     
    //
    // First, check if cell is occupied                  ####  BUG FIX  ####
    //
    if ( $board [ $u ] [ $v ] !== NULL)
    {
        return false;
    }


    $directions = array( array( -1, 0 ), array( -1, 1 ), array( 0, 1 ), 
                         array( 1, 0 ), array( 1, -1), array( 0, -1 ) );

    
    foreach( $directions as $direction )     
    {
        $neighbor_u = $u + $direction[0];
        $neighbor_v = $v + $direction[1];

        if (    self::isCellOnBoard ( $neighbor_u, $neighbor_v, $N )    )
        {
            if ( $board [ $neighbor_u ] [ $neighbor_v ] == NULL                 // NEIGHBOR IS UNOCCUPIED OR FRIENDLY OCCUPIED
              || $board [ $neighbor_u ] [ $neighbor_v ] == $active_player_id )
            {
                return true;
            }
        }
    }

    return false;
}



/*
function getOccupant ( $u, $v, $board, $N )
{                       
    if (    $v >= 0 && $v < 2*$N - 1    )                                                       // Row is on the board
    {
        if (    $u >= self::uLeftLimit ( $v, $N ) && $u <= self::uRightLimit ( $v, $N )    )    // $u is in the row
        {
            return $board [ $u ] [ $v ];
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
*/


function isCellOnBoard ($u, $v, $N)
{    
    switch ($N)
    {
        case 4:  // small board
            if (    (  $u >= 0 && $u < 4 && $v >= 3 - $u && $v < 7 )             // Cell on board
                 || (  $u >= 4 && $u < 7 && $v >= 0 && $v <= 9 - $u  )    )
                 return true;
            else 
                 return false;

        case 5: // medium board
            if (    (  $u >= 0 && $u < 5 && $v >= 4 - $u && $v < 9 )             // Cell on board
                 || (  $u >= 5 && $u < 9 && $v >= 0 && $v <= 12 - $u  )    )
                 return true;
            else 
                 return false;

        case 6:  // large board
            if (    (  $u >= 0 && $u < 6 && $v >= 5 - $u && $v < 11 )             // Cell on board
                 || (  $u >= 6 && $u < 11 && $v >= 0 && $v <= 15 - $u  )    )
                 return true;
            else 
                 return false;
    }
}


function uLeftLimit ( $v, $N )
{                       
    return max ( $N - 1 - $v, 0 );
}



function uRightLimit ( $v, $N )
{                       
    return min ( 3*$N - 3 - $v, 2*$N - 2 );
}


function getBoard()
{
    return self::getDoubleKeyCollectionFromDB( "SELECT board_u u, board_v v, board_player player
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
            return clienttranslate('WHITE');
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




function getStoneID ( $u, $v )
{      
    $sql = "SELECT stone_id stone_id FROM board WHERE ( board_u, board_v) = ( $u, $v )"; // should be only one

    $result = self::DbQuery( $sql );


    if ( $result->num_rows > 0 ) 
    {
        while (    $row = $result->fetch_assoc()    ) 
        {
            $stone_IDs [ ] = $row [ "stone_id" ];
        }
    } 

    $stone_id = $stone_IDs [0];

    return $stone_id;
}



/*
function isCellOccupied ($u, $v, $board, $N)
{    
    if (    self::isCellOnBoard ($u, $v, $N)    )
    {
        if (    isset ( $board [ $u ] [ $v ] )    )
            return true;
        else
            return false;
    }
    else 
	    return false;
}
*/






//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

//
//  PLACE STONE 
//
//
//  If it's legal move
//      Update globals 
//      Add to database
//      Notify frontend to add a stone
//
//      If it captures enemy stone 
//          Remove enemy stone 
//          Increase number of stones to add
//
//      State change: 
//          placeStone => placeStone 
//          placeLastStone => nextTurn
//      
function placeStone ( $u, $v )
{
    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( 'function placeStone ( $u, $v )' ), 
        array(
         ) );
    */


    self::checkAction( 'placeStone' );  
    
    $active_player_id = self::getActivePlayerId(); 

    $inactive_player_id = self::getOtherPlayerId ( $active_player_id );


    $active_player_color = self::getPlayerColor ( $active_player_id );

    $inactive_player_color = self::getPlayerColor ( $inactive_player_id );

        
    $board = self::getBoard();

	//Get the board size
    $N=self::getGameStateValue("board_size");



    //
    // CHECK IF THIS IS A SELECTABLE CELL 
    //
    if (    self::isLegalMove ( $u, $v, $board, $N, $active_player_id )    )      
    {
        //
        //  UPDATE GLOBALS 
        //      STONES PLACED THIS TURN 
        //      BLACK OR WHITE STONE ID 
        //      NUMBER OF BLACK OR WHITE STONES
        //
        self::incGameStateValue( "placed_stones_this_turn", 1 );


        if (    $active_player_color == "000000"    )                       // BLACK PLAYER
        {
            $stone_id_value = self::getGameStateValue( "black_stone_id" );
            self::incGameStateValue( "black_stone_id", 1 );

            self::incGameStateValue( "number_of_black_stones", 1 );
        }
        else                                                                // WHITE PLAYER
        {
            $stone_id_value = self::getGameStateValue( "white_stone_id" );
            self::incGameStateValue( "white_stone_id", 1 );

            self::incGameStateValue( "number_of_white_stones", 1 );
        }

        $sql = "UPDATE board SET board_player = $active_player_id, stone_id = $stone_id_value
                WHERE ( board_u, board_v) = ($u, $v)";  
    
        self::DbQuery( $sql );



        // UPDATE STATS ABOUT PIP USAGE
        $this->incStat(1, 'dice_total', $active_player_id);



        //
        // NOTIFY FRONTEND ABOUT PLACED STONE 
        //
        self::notifyAllPlayers( "stonePlaced", clienttranslate( '' ), 
            array(
            'u' => $u,
            'v' => $v,
            'player_id' => $active_player_id,
            'stone_id' => $stone_id_value,
            'N' => $N
            ) );



        //
        // NOTIFY FRONTEND TO UPDATE PLAYER PANEL FOR ACTIVE PLAYER
        //
        //      PLACED STONES / NUMBER OF STONES TO PLACE
        //
        if (    $active_player_color == "000000"    )                       // BLACK PLAYER
        {
            $active_player_number_of_stones = self::getGameStateValue( "number_of_black_stones" );
        }
        else                                                                // WHITE PLAYER
        {
            $active_player_number_of_stones = self::getGameStateValue( "number_of_white_stones" );
        }

        $placed_stones_this_turn = self::getGameStateValue ( "placed_stones_this_turn" );

        $number_of_stones_to_place = self::getGameStateValue ( "number_of_stones_to_place" );

        self::notifyAllPlayers( "activePlayerPanelAddedStones", clienttranslate( '' ), 
            array(
            'active_player_id' => $active_player_id,
            'active_player_number_of_stones' => $active_player_number_of_stones,
            'placed_stones_this_turn' => $placed_stones_this_turn,
            'number_of_stones_to_place' => $number_of_stones_to_place
            ) );




        //
        //  CHECK FOR ENEMY STONE CAPTURE 
        //
        //      Check each neighboring cell for enemy stone  
        //
        //      If neighboring stone 
        //          If it's captured 
        //              Update globals 
        //                  Increment number_of_stones_to_place
        //                  Decrement number_of_COLOR_stones
        //              Remove it from database
        //              Increase number of stones to add
        //
        //              Notify frontend about removed enemy stone
        //
        $board = self::getBoard();                                              // GET NEW COPY OF BOARD AFTER ADDING STONE ABOVE

        $directions = array( array( -1, 0 ), array( -1, 1 ), array( 0, 1 ), 
                             array( 1, 0 ), array( 1, -1), array( 0, -1 ) );

        foreach( $directions as $direction )     
        {
            $neighbor_u = $u + $direction[0];
            $neighbor_v = $v + $direction[1];

            if (    self::isCellOnBoard ( $neighbor_u, $neighbor_v, $N )    )
            {
                if ( $board [ $neighbor_u ] [ $neighbor_v ] == $inactive_player_id )  //  ENEMY NEIGHBOR
                {
                    $sub_directions = array( array( -1, 0 ), array( -1, 1 ), array( 0, 1 ), 
                                             array( 1, 0 ), array( 1, -1), array( 0, -1 ) );

                    foreach( $sub_directions as $sub_direction )     
                    {
                        $sub_neighbor_u = $neighbor_u + $sub_direction[0];
                        $sub_neighbor_v = $neighbor_v + $sub_direction[1];

                        if (    self::isCellOnBoard ( $sub_neighbor_u, $sub_neighbor_v, $N )    )
                        {
                            if ( $board [ $sub_neighbor_u ] [ $sub_neighbor_v ] == NULL                 //  ENEMY NEIGHBOR HAS EMPTY OR ENEMY NEIGHBOR
                              || $board [ $sub_neighbor_u ] [ $sub_neighbor_v ] == $inactive_player_id )  
                            {
                                continue 2;
                            }
                        }
                    }

                    //
                    //  ENEMY NEIGHBOR IS CAPTURED 
                    //
                    //      Decrement number_of_COLOR_stones (enemy stones)
                    //      Increment number_of_stones_to_place 
                    //      Remove enemy neighbor from database
                    //
                    if (    $active_player_color == "000000"    )                       // BLACK PLAYER
                    {
                        $active_player_number_of_stones = self::incGameStateValue( "number_of_white_stones", -1 );
                    }
                    else                                                                // WHITE PLAYER
                    {
                        $active_player_number_of_stones = self::incGameStateValue( "number_of_black_stones", -1 );
                    }


                    //
                    //  INCREASE NUMBER OF STONES TO PLACE 
                    //
                    self::incGameStateValue( "number_of_stones_to_place", 1 );


                    $remove_stone_id = self::getStoneID ( $neighbor_u, $neighbor_v ); // GET REMOVED STONE ID BEFORE REMOVING IT


                    $sql = "UPDATE board SET board_player = NULL, stone_id = NULL WHERE ( board_u, board_v) = ($neighbor_u, $neighbor_v)";  
    
                    self::DbQuery( $sql );


                    // UPDATE PIP USAGE TO ACCOUNT FOR EXTRA REMOVAL AND PLACEMENT
                    $this->incStat(-2, 'dice_total', $active_player_id);


                    //
                    // NOTIFY FRONTEND ABOUT REMOVED STONE 
                    //
                    self::notifyAllPlayers( "stoneRemoved", clienttranslate( '' ), 
                        array(
                        'player_id' => $inactive_player_id,
                        'removed_stone_id' => $remove_stone_id
                        ) );


                    //
                    // NOTIFY FRONTEND TO UPDATE PLAYER PANEL FOR ACTIVE PLAYER - TO ACCOUNT FOR INCREASED NUMBER OF STONES TO ADD
                    //
                    //      PLACED STONES / NUMBER OF STONES TO PLACE
                    //
                    if (    $active_player_color == "000000"    )                       // BLACK PLAYER
                    {
                        $active_player_number_of_stones = self::getGameStateValue( "number_of_black_stones" );

                        $inactive_player_number_of_stones = self::getGameStateValue( "number_of_white_stones" );
                    }
                    else                                                                // WHITE PLAYER
                    {
                        $active_player_number_of_stones = self::getGameStateValue( "number_of_white_stones" );

                        $inactive_player_number_of_stones = self::getGameStateValue( "number_of_black_stones" );
                    }

                    $placed_stones_this_turn = self::getGameStateValue ( "placed_stones_this_turn" );

                    $number_of_stones_to_place = self::getGameStateValue ( "number_of_stones_to_place" );

                    // 
                    //  ACTIVE PLAYER PANEL 
                    //
                    self::notifyAllPlayers( "activePlayerPanelAddedStones", clienttranslate( '' ), 
                       array(
                       'active_player_id' => $active_player_id,
                       'active_player_number_of_stones' => $active_player_number_of_stones,
                       'placed_stones_this_turn' => $placed_stones_this_turn,
                       'number_of_stones_to_place' => $number_of_stones_to_place
                       ) );

                    // 
                    //  INACTIVE PLAYER PANEL 
                    //
                    self::notifyAllPlayers("playerPanelNumberOfStones",
                        "",
                        array(
                        "number_of_stones" => $inactive_player_number_of_stones,
                        "player_id" => $inactive_player_id ));

                }
                else 
                {
                    continue;
                }
            }
        }



        //
        //  STATE CHANGE 
        //
        //  If board is completely filled with stones, end the game
        //
        //  If number of placed stones < number of stones to be placed AND there are still legal placements available
        //      ( placeStoneFirstTurn or placeStone ) => placeStone
        //  Else 
        //      ( placeStoneFirstTurn or placeStone ) => nextPlayer
        // 

        //
        //  CHECK FOR END GAME 
        //
        $number_of_black_stones = self::getGameStateValue( "number_of_black_stones" );

        $number_of_white_stones = self::getGameStateValue( "number_of_white_stones" );

        $total_number_of_stones = $number_of_black_stones + $number_of_white_stones;

        $board_filled = false;

        switch ( $N )
        {
            case 4:
                if ( $total_number_of_stones == 37 )
                {
                    $board_filled = true;                    
                }

                break;

            case 5:
                if ( $total_number_of_stones == 61 )
                {
                    $board_filled = true;                    
                }

                break;

            case 6:
                if ( $total_number_of_stones == 91 )
                {
                    $board_filled = true;                    
                }

                break;
        }


        if ( $board_filled )
        {
            if ( $active_player_color == "000000" )     // ACTIVE PLAYER IS BLACK
            {
                if ( $number_of_black_stones > $number_of_white_stones )
                {
                    $sql = "UPDATE player SET player_score = 1 WHERE player_id = $active_player_id";
                }
                else 
                {
                    $sql = "UPDATE player SET player_score = 1 WHERE player_id != $active_player_id";
                }
            }
            else                                        // ACTIVE PLAYER IS WHITE
            {
                if ( $number_of_white_stones > $number_of_black_stones )
                {
                    $sql = "UPDATE player SET player_score = 1 WHERE player_id = $active_player_id";
                }
                else 
                {
                    $sql = "UPDATE player SET player_score = 1 WHERE player_id != $active_player_id";
                }
            }
                                 
            self::DbQuery( $sql );


            $newScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );

            self::notifyAllPlayers( "newScores", "", array(
                "scores" => $newScores
            ) );

            $this->gamestate->nextState( 'endGame' );
        }





        $legal_placements_count = 0;

        $legal_placements_remaining = self::getSelectableCells ( $active_player_id );

        foreach ( $legal_placements_remaining as $legal_placements_remaining_row)
        {
            $legal_placements_count += count( $legal_placements_remaining_row );
        }


	    if (    self::getGameStateValue( "placed_stones_this_turn" ) < self::getGameStateValue( "number_of_stones_to_place" )    
              &&  $legal_placements_count > 0    ) 
        {



			$this->gamestate->nextState( 'placeStone' );
		}
        else  
        {
            // 
            //  ZERO OUT GLOBAL DATA 
            //
            self::setGameStateValue ( "placed_stones_this_turn", 0 );
            self::setGameStateValue ( "number_of_stones_to_place", 0 );


            //
            //  UPDATE ACTIVE PLAYER PANEL WITH ONLY NUMBER OF STONES - NO DATA ABOUT ADDED STONES
            //
            if ( $active_player_color == "000000" )         // BLACK PLAYER IS ACTIVE
            {
                self::notifyAllPlayers("playerPanelNumberOfStones",
                    "",
                    array(
                    "number_of_stones" => self::getGameStateValue ( "number_of_black_stones"),
                    "player_id" => $active_player_id ));
            }
            else                                            // WHITE PLAYER IS ACTIVE 
            {
                self::notifyAllPlayers("playerPanelNumberOfStones",
                    "",
                    array(
                    "number_of_stones" => self::getGameStateValue ( "number_of_white_stones"),
                    "player_id" => $active_player_id ));
            }


            $this->gamestate->nextState( 'placeLastStone' );
        }
    }
    else
        throw new feException( "Not a legal placement." );

}




//
//  REMOVE STONE 
//
//
//  If it's an enemy stone
//      Update globals 
//          Increment number of stones removed
//          Decriment number of enemy stones 
//      Remove stone from database
//      Notify frontend to remove the stone
//
//      State change: 
//          removeStone => removeStone 
//  
//          If it's the last removed stone (either from dice limit or no more enemy stones on board)
//              If there are legal placements available 
//                  removeLastStone => placeStone 
//              Else (no legal placements available)
//                  removeLastStone_noPlacementsAvailable => nextPlayer
//      
function removeStone ( $u, $v )
{
    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( 'function removeStone ( $u, $v )' ), 
        array(
         ) );
    */


    self::checkAction( 'removeStone' );  
    
    $active_player_id = self::getActivePlayerId(); 

    $inactive_player_id = self::getOtherPlayerId ( $active_player_id );


    $active_player_color = self::getPlayerColor ( $active_player_id );

    $inactive_player_color = self::getPlayerColor ( $inactive_player_id );

        
    $board = self::getBoard();

	//Get the board size
    $N=self::getGameStateValue("board_size");



    //
    // CHECK IF THIS IS AN ENEMY STONE
    //
    $remove_stone_player_id = $board [ $u ] [ $v ];

    if (  $remove_stone_player_id == $inactive_player_id   )      
    {
        //
        //  REMOVE STONE 
        //
        //      Update globals 
        //          Increment removed_stones_this_turn
        //          Decrement number_of_COLOR_stones 
        //
        //      Remove stone from database
        //
        //      Notify frontend about removed stone
        //
        self::incGameStateValue( "removed_stones_this_turn", 1 );           // INCREMENT STONES REMOVED THIS TURN


        if (    $inactive_player_color == "000000"    )                     // BLACK REMOVED STONE
        {
            self::incGameStateValue( "number_of_black_stones", -1 );            // DECREMENT NUMBER OF BLACK STONES
        }
        else                                                                // WHITE REMOVED STONE
        {
            self::incGameStateValue( "number_of_white_stones", -1 );            // DECREMENT NUMBER OF WHITE STONES
        }

        //
        //  REMOVE STONE FROM DATABASE 
        //
        $remove_stone_id = self::getStoneID ( $u, $v ); // GET REMOVED STONE ID BEFORE REMOVING IT

        $sql = "UPDATE board SET board_player = NULL, stone_id = NULL       
                WHERE ( board_u, board_v) = ($u, $v)";  
    
        self::DbQuery( $sql );


        // UPDATE STATS ABOUT PIP USAGE
        $this->incStat(1, 'dice_total', $active_player_id);


        //
        // NOTIFY FRONTEND ABOUT REMOVED STONE 
        //
        self::notifyAllPlayers( "stoneRemoved", clienttranslate( '' ), 
            array(
            'player_id' => $remove_stone_player_id, // INACTIVE PLAYER ID
            'removed_stone_id' => $remove_stone_id
            ) );



        //
        // NOTIFY FRONTEND TO UPDATE PLAYER PANEL FOR ACTIVE PLAYER
        //
        //      REMOVED STONES / NUMBER OF STONES TO REMOVE
        //
        //      AND... UPDATE INACTIVE PLAYER PANEL TO SHOW REDUCED NUMBER OF STONES
        //
        if (    $active_player_color == "000000"    )                       // BLACK PLAYER
        {
            $active_player_number_of_stones = self::getGameStateValue( "number_of_black_stones" );

            $inactive_player_number_of_stones = self::getGameStateValue( "number_of_white_stones" );
        }
        else                                                                // WHITE PLAYER
        {
            $active_player_number_of_stones = self::getGameStateValue( "number_of_white_stones" );

            $inactive_player_number_of_stones = self::getGameStateValue( "number_of_black_stones" );
        }

        $removed_stones_this_turn = self::getGameStateValue ( "removed_stones_this_turn" );

        $number_of_stones_to_remove = self::getGameStateValue ( "number_of_stones_to_remove" );

        // 
        //  ACTIVE PLAYER PANEL 
        //
        self::notifyAllPlayers( "activePlayerPanelRemovedStones", clienttranslate( '' ), 
            array(
            'active_player_id' => $active_player_id,
            'active_player_number_of_stones' => $active_player_number_of_stones,
            'removed_stones_this_turn' => $removed_stones_this_turn,
            'number_of_stones_to_remove' => $number_of_stones_to_remove
            ) );

        // 
        //  INACTIVE PLAYER PANEL 
        //
        self::notifyAllPlayers("playerPanelNumberOfStones",
            "",
            array(
            "number_of_stones" => $inactive_player_number_of_stones,
            "player_id" => $inactive_player_id ));




        //
        //  STATE CHANGE 
        //
        //  If number of removed stones < number of stones to be removed 
        //  AND there are still enemy stones on the board
        //      removeStone => removeStone
        //  Else 
        //      removeStone => nextPlayer
        //   
        /*
        $enemy_stone_count = 0;                                                             ####  BUGGY  ####
                                                                                            ####
        $enemy_stones_remaining = self::getRemovableStones ( $active_player_id );           ####  CLICKING FAST ON ENEMY STONES CAUSES THIS CHECK TO FAIL  ####
                                                                                            ####
        foreach ( $enemy_stones_remaining as $enemy_stones_remaining_row)                   ####
        {                                                                                   ####
            $enemy_stone_count += count( $enemy_stones_remaining_row );                     ####
        }                                                                                   ####
                                                                                            ####
	    if (    self::getGameStateValue( "removed_stones_this_turn" ) < self::getGameStateValue( "number_of_stones_to_remove" )    
              &&  $enemy_stone_count > 0    )                                               ####
        {                                                                                   ####
			$this->gamestate->nextState( 'removeStone' );                                   ####
		}                                                                                   ####
        */
	    if (    self::getGameStateValue( "removed_stones_this_turn" ) < self::getGameStateValue( "number_of_stones_to_remove" )    
              &&  $inactive_player_number_of_stones > 0    ) 
        {
			$this->gamestate->nextState( 'removeStone' );                                   
		}

        else  
        {

            // 
            //  ZERO OUT GLOBAL DATA 
            //
            self::setGameStateValue ( "removed_stones_this_turn", 0 );
            self::setGameStateValue ( "number_of_stones_to_remove", 0 );



            //
            //  UPDATE ACTIVE PLAYER PANEL WITH ONLY NUMBER OF STONES - NO DATA ABOUT REMOVED STONES
            //
            if ( $active_player_color == "000000" )         // BLACK PLAYER IS ACTIVE
            {
                self::notifyAllPlayers("playerPanelNumberOfStones",
                    "",
                    array(
                    "number_of_stones" => self::getGameStateValue ( "number_of_black_stones"),
                    "player_id" => $active_player_id ));
            }
            else                                            // WHITE PLAYER IS ACTIVE 
            {
                self::notifyAllPlayers("playerPanelNumberOfStones",
                    "",
                    array(
                    "number_of_stones" => self::getGameStateValue ( "number_of_white_stones"),
                    "player_id" => $active_player_id ));
            }



            //
            //  IF NO PLACEMENTS AVAILABLE, JUMP TO NEXT PLAYER TURN 
            //
            $selectable_cells = self::getSelectableCells ( $active_player_id );

            if (     count ( $selectable_cells ) > 0    )
            {
                //
                //  UPDATE ACTIVE PLAYER PANEL WITH NUMBER OF STONES TO ADD - ZERO SO FAR
                //
                if ( $active_player_color == "000000" )         // BLACK PLAYER IS ACTIVE
                {
                    $active_player_number_of_stones = self::getGameStateValue ("number_of_black_stones");
                }
                else                                            // WHITE PLAYER IS ACTIVE 
                {
                    $active_player_number_of_stones = self::getGameStateValue ("number_of_white_stones");
                }


                $number_of_stones_to_place = self::getGameStateValue ("number_of_stones_to_place");

                self::notifyAllPlayers("activePlayerPanelAddedStones",
                    "",
                    array(
                        'active_player_id' => $active_player_id,
                        'active_player_number_of_stones' => $active_player_number_of_stones,
                        'placed_stones_this_turn' => 0,
                        'number_of_stones_to_place' => $number_of_stones_to_place
                        ) );



                $this->gamestate->nextState( 'removeLastStone' );
            }
            else 
            {
                $this->gamestate->nextState( 'removeLastStone_noPlacementsAvailable' );
            }
        }


    }
    else
        throw new feException( "Not a removable stone." );


}




    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

function argPlaceStone()
{
    return array(
        'selectableCells' => self::getSelectableCells ( self::getActivePlayerId() )
    );
}


function argRemoveStone()
{
    return array(
        'removableStones' => self::getRemovableStones ( self::getActivePlayerId() )
    );
}



//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

function stNextPlayer()
{
    $active_player_id = self::getActivePlayerId ( );


    $inactive_player_id = self::getOtherPlayerId ( $active_player_id );


    //
    //  REMOVE DICE FROM LAST TURN - NOTIFY FRONTEND
    //
    $rolled_die_0_id = self::getGameStateValue ( "rolled_die_0_id");
    $rolled_die_1_id = self::getGameStateValue ( "rolled_die_1_id");

    self::notifyAllPlayers( "diceRemoved", clienttranslate( '' ), 
        array(
        'die_0_id' => $rolled_die_0_id,
        'die_1_id' => $rolled_die_1_id,
        'player_id' => $inactive_player_id
        ) );
 

    //
    //  ROLL DICE FOR NEXT TURN 
    //
    self::rollDice ( );


    $rolled_die_0 = self::getGameStateValue ( "rolled_die_0");

    $rolled_die_1 = self::getGameStateValue ( "rolled_die_1");

    $lower_die_value = min ( $rolled_die_0, $rolled_die_1 );

    $higher_die_value = max ( $rolled_die_0, $rolled_die_1 );


    self::setGameStateValue ( "number_of_stones_to_remove", $lower_die_value );

    self::setGameStateValue ( "removed_stones_this_turn", 0 );


    self::setGameStateValue ( "number_of_stones_to_place", $higher_die_value );

    self::setGameStateValue ( "placed_stones_this_turn", 0 );


    $rolled_die_0_id = self::getGameStateValue ( "rolled_die_0_id");

    $rolled_die_1_id = self::getGameStateValue ( "rolled_die_1_id");


    //
    //  SHOW DICE FOR NEXT TURN - FRONTEND
    //
    self::notifyAllPlayers( "diceRolled", clienttranslate( '' ), 
        array(
        'die_0' => $rolled_die_0,
        'die_1' => $rolled_die_1,
        'die_0_id' => $rolled_die_0_id,
        'die_1_id' => $rolled_die_1_id,
        'player_id' => $inactive_player_id
        ) );
 


    // 
    //  ACTIVATE NEXT PLAYER 
    //
    $active_player_id = self::activeNextPlayer();

    $active_player_color = self::getPlayerColor ( $active_player_id );



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
    // NOTIFY FRONTEND TO UPDATE PLAYER PANEL FOR ACTIVE PLAYER 
    //
    //      REMOVED STONES / NUMBER OF STONES TO REMOVE
    //
    if (    $active_player_color == "000000"    )                       // BLACK PLAYER
    {
        $active_player_number_of_stones = self::getGameStateValue( "number_of_black_stones" );

        //$inactive_player_number_of_stones = self::getGameStateValue( "number_of_white_stones" );
    }
    else                                                                // WHITE PLAYER
    {
        $active_player_number_of_stones = self::getGameStateValue( "number_of_white_stones" );

        //$inactive_player_number_of_stones = self::getGameStateValue( "number_of_black_stones" );
    }

    $removed_stones_this_turn = self::getGameStateValue ( "removed_stones_this_turn" );

    $number_of_stones_to_remove = self::getGameStateValue ( "number_of_stones_to_remove" );

    // 
    //  ACTIVE PLAYER PANEL 
    //



    /*
            self::notifyAllPlayers( "backendMessage", clienttranslate( '${var_1}, ${var_2}, ${var_3}, ${var_4}} ' ), 
                array(
                    "var_1" => $active_player_id,
                    "var_2" => $active_player_number_of_stones,
                    "var_3" => $removed_stones_this_turn,
                    "var_4" => $number_of_stones_to_remove
                ) );
    */




    self::notifyAllPlayers( "activePlayerPanelRemovedStones", clienttranslate( '' ), 
        array(
        'active_player_id' => $active_player_id,
        'active_player_number_of_stones' => $active_player_number_of_stones,
        'removed_stones_this_turn' => $removed_stones_this_turn,
        'number_of_stones_to_remove' => $number_of_stones_to_remove
        ) );


    // 
    //  NEXT STATE 
    //
    self::giveExtraTime( $active_player_id );

    /*
    $this->gamestate->nextState( 'nextTurn' );
    */

    $removable_stones_count = 0;

    $removable_stones = self::getRemovableStones ( $active_player_id );

    foreach ( $removable_stones as $removable_stones_row)
    {
        $removable_stones_count += count ( $removable_stones_row );
    }

    if ( $removable_stones_count == 0 )
    {
        self::giveExtraTime( $active_player_id );  
        
        $this->gamestate->nextState( 'nextTurn_noEnemyStonesToRemove' );
    }
    else 
    {
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
    }    




    function initializeBoard( )
    {
        //
        // Initialize the board with NULL values for all cells 
        //
        $sql = "INSERT INTO board (board_u, board_v, board_player, stone_id) VALUES ";

        $sql_values = array();


        $N = self::getGameStateValue ("board_size");

        switch ($N)
        {
            case 4:  // small board

                for ($u = 0; $u < 7; $u++)
                {
                    for( $v = 0; $v < 7; $v++ )
                    {
                        if (    self::isCellOnBoard($u, $v, 4)    )
                            $sql_values[] = "($u, $v, NULL, NULL)";                   
                    }
                }

                $sql .= implode( $sql_values, ',' );
                self::DbQuery( $sql );

                break;

            case 5:  // medium board

                for ($u = 0; $u < 9; $u++)
                {
                    for( $v = 0; $v < 9; $v++ )
                    {
                        if (    self::isCellOnBoard($u, $v, 5)    )
                            $sql_values[] = "($u, $v, NULL, NULL)";                   
                    }
                }

                $sql .= implode( $sql_values, ',' );
                self::DbQuery( $sql );

                break;

            case 6:  // large board

                for ($u = 0; $u < 11; $u++)
                {
                    for( $v = 0; $v < 11; $v++ )
                    {
                        if (    self::isCellOnBoard($u, $v, 6)    )
                            $sql_values[] = "($u, $v, NULL, NULL)";                   
                    }
                }

                $sql .= implode( $sql_values, ',' );
                self::DbQuery( $sql );

                break;
        }
    }    



    function rollDice( )
    {
        $die_0 = bga_rand ( 1, 6 );

        $die_1 = bga_rand ( 1, 6 );

        self::setGameStateValue( "rolled_die_0", $die_0 );

        self::setGameStateValue( "rolled_die_1", $die_1 );


        self::incGameStateValue( "rolled_die_0_id", 1 );

        self::incGameStateValue( "rolled_die_1_id", 1 );
    }

}




        //
        //  UPDATE PLAYER PANEL WITH NUMBER OF STONES 
        //
        /*
        if ( $active_player_color == "000000" )         // BLACK PLAYER IS ACTIVE
        {
            self::notifyAllPlayers("playerPanel_number_of_stones",
                "",
                array(
                "number_of_stones" => self::getGameStateValue ( "number_of_black_stones"),
                "player_id" => $active_player_id ));

            self::notifyAllPlayers("playerPanel_number_of_stones",
                "",
                array(
                "number_of_stones" => self::getGameStateValue ( "number_of_white_stones"),
                "player_id" => $inactive_player_id ));
        }
        else                                            // WHITE PLAYER IS ACTIVE 
        {
            self::notifyAllPlayers("playerPanel_number_of_stones",
                "",
                array(
                "number_of_stones" => self::getGameStateValue ( "number_of_white_stones"),
                "player_id" => $active_player_id ));

            self::notifyAllPlayers("playerPanel_number_of_stones",
                "",
                array(
                "number_of_stones" => self::getGameStateValue ( "number_of_black_stones"),
                "player_id" => $inactive_player_id ));
        }
        */





        
            /*
            self::notifyAllPlayers( "backendMessage", clienttranslate( '${var_1}, ${var_2}, ${var_3}, ${var_4}}, ${var_5} ' ), 
                array(
                    "var_1" => $x,
                    "var_2" => $y,
                    "var_3" => $neighbor_x,
                    "var_4" => $neighbor_y,
                    "var_5" => $tile_id
                ) );
            */
