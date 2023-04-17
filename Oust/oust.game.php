<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * Oust implementation : © <Mark Steere> <marksteere@gmail.com>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * oust.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class Oust extends Table
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
            "brown_checker_id" => 20,               // Initial brown checker ID
            "blue_checker_id" => 21,                // Initial blue checker ID
            "total_brown_checkers"=> 22,            // Total number of brown checkers
            "total_blue_checkers"=> 23,             // Total number of blue checkers
            "last_move_u"=> 30,                     // Last move indicator: u
            "last_move_v"=> 31,                     // Last move indicator: v
            "last_move_id"=> 32,                    // Last move indicator: id
            "board_size" => 101,                    // Size of board
        ) );        
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "oust";
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
        self::setGameStateValue( "brown_checker_id", 1000 );
        self::setGameStateValue( "blue_checker_id", 2000 );
        self::setGameStateValue( "total_brown_checkers", 0 );
        self::setGameStateValue( "total_blue_checkers", 0 );

        self::setGameStateValue( "last_move_u", 0 );
        self::setGameStateValue( "last_move_v", 7 );
        self::setGameStateValue( "last_move_id", 99999 );



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
            // FOR TEMPORARY BOARD FILL
            //
            if( $color == '5e3200' )
                $brown_player_id = $player_id;
            else
                $blue_player_id = $player_id;










        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        
        //self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();
        
        //
        // Initialize the board with NULL values for all cells 
        //
        $N=self::getGameStateValue("board_size");


        $sql = "INSERT INTO board (board_u, board_v, board_player, checker_id) VALUES ";

        $sql_values = array();


        //
        //  
        //
        //  TEMPORARY FILL BOARD WITH CHECKERS 
        //
        //
        //
        /*
        $brown_checker_id = 1000;  // brown - 1000 to 1999  // ERROR - SEE BELOW - HAVE TO GET CHECKER IDs FROM GLOBALS
        $blue_checker_id = 2000;    // blue - 2000 to 2999

        for( $u=0; $u<2*$N-1; $u++ )
        {
            for( $v=0; $v<2*$N-1; $v++ )
            {
                if (    self::isCellOnBoard($u, $v, $N)    )
                {
                    if (    ( $u + $v ) % 2 == 0    )
                    {
                        $player_id_value = $brown_player_id;
                        $checker_id_value = $brown_checker_id++;
                    }
                    else
                    {
                        $player_id_value = $blue_player_id;                             
                        $checker_id_value = $blue_checker_id++;
                    }

                    $sql_values[] = "($u, $v, $player_id_value, $checker_id_value)";                   
                }
            }
        }

        $sql .= implode( $sql_values, ',' );
        self::DbQuery( $sql );
        */










        //  
        //
        //  TEMPORARY - PARTIALLY FILL BOARD FOR TESTING CANTPLAY CHECK
        //
        //
        /*
        for ( $v = 0; $v < 2*$N-1; $v++ ) // Start on bottom row.  $v = 0
        {
            $u_left_limit = self::uLeftLimit ( $v, $N );        // Left to right limits of row in hexagonal board
            $u_right_limit = self::uRightLimit ( $v, $N );

            for (    $u = $u_left_limit; $u <= $u_right_limit; ++$u    )  
            {
                if ( $v < $N - 2 )
                {
                    $player_id_value = $brown_player_id;

                    $checker_id_value = self::getGameStateValue( "brown_checker_id" );
                    self::incGameStateValue( "brown_checker_id", 1 );

                    self::incGameStateValue( "total_brown_checkers", 1 );

                    $sql_values[] = "($u, $v, $player_id_value, $checker_id_value)";                   
                }
                else if ( $v > $N - 1 )
                {
                    $player_id_value = $blue_player_id;                             

                    $checker_id_value = self::getGameStateValue( "blue_checker_id" );
                    self::incGameStateValue( "blue_checker_id", 1 );

                    self::incGameStateValue( "total_blue_checkers", 1 );

                    $sql_values[] = "($u, $v, $player_id_value, $checker_id_value)";                   
                }
                else 
                {
                    $sql_values[] = "($u, $v, NULL, NULL)";                   
                }
            }
        }

        $sql .= implode( $sql_values, ',' );
        self::DbQuery( $sql );


        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL
                WHERE ( board_u, board_v) = (5, 0)";  
    
        self::DbQuery( $sql );

        self::incGameStateValue( "total_brown_checkers", -1 );
        */






        //  
        //
        //  FILL BOARD WITH NULL VALUES
        //
        //
        for ( $v = 0; $v < 2*$N-1; $v++ ) // Start on bottom row.  $v = 0
        {
            $u_left_limit = self::uLeftLimit ( $v, $N );        // Left to right limits of row in hexagonal board
            $u_right_limit = self::uRightLimit ( $v, $N );

            for (    $u = $u_left_limit; $u <= $u_right_limit; ++$u    )  
            {
                $sql_values[] = "($u, $v, NULL, NULL)";                   
            }
        }

        $sql .= implode( $sql_values, ',' );
        self::DbQuery( $sql );
 
        
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
  
        // TODO: Gather all information about current game situation (visible by player $current_player_id).
  
        //
        // Get board occupied cells
        // 
        $sql = "SELECT board_u u, board_v v, board_player player, checker_id ch_id
                    FROM board
                    WHERE board_player IS NOT NULL";
        $result['board'] = self::getObjectListFromDB( $sql );


		//
        // Get  board size 
        //
        $N=self::getGameStateValue("board_size");
		$result['board_size'] = $N;


        //
        // Placed checkers
        //
        $activePlayerId = self::getActivePlayerId();
        $otherPlayerId = self::getOtherPlayerId($activePlayerId);

        $active_player_color = self::getPlayerColor ( $activePlayerId );


        $placedCheckers = array();

        if (    $active_player_color == "5e3200"    )
        {
            $placedCheckers[$activePlayerId] = self::getGameStateValue( "total_brown_checkers" );
            $placedCheckers[$otherPlayerId] = self::getGameStateValue( "total_blue_checkers" );
        }
        else
        {
            $placedCheckers[$activePlayerId] = self::getGameStateValue( "total_blue_checkers" );
            $placedCheckers[$otherPlayerId] = self::getGameStateValue( "total_brown_checkers" );
        }
        
        $result['placedCheckers'] = $placedCheckers;
        




        //
        // LAST MOVE INDICATOR 
        //
        $last_move = array ( );

        $last_move [ 0 ] = self::getGameStateValue( "last_move_u" );

        $last_move [ 1 ] = self::getGameStateValue( "last_move_v" );

        $last_move [ 2 ] = self::getGameStateValue( "last_move_id" );

        $result['last_move'] = $last_move;





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
        $total_brown_checkers = self::getGameStateValue ( "total_brown_checkers");

        $total_blue_checkers = self::getGameStateValue ( "total_blue_checkers");

        $total_checkers = $total_brown_checkers + $total_blue_checkers;


        $N = self::getGameStateValue ( "board_size" );

        switch ( $N )
        {
            case 6:
                $board_size = 91;
                break;

            case 7:
                $board_size = 127;
                break;

            case 8:
                $board_size = 169;
                break;
        }
            
        return min ( $total_checkers / $board_size * 333, 100 );
    }





//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

function isSelectableCell( $u, $v, $board)
{
    //$board = self::getBoard();

	//Get the board size
    $N=self::getGameStateValue("board_size");
     

    if (    self::isCellOnBoard($u, $v, $N)    )
    {
        if ( $board [ $u ] [ $v ] == NULL )
            return true;
        else 
            return false;
    }
    else 
        return false;
}



// getSelectableCells ( $active_player_id ) 
// ########################################
//
//
// GROUP IDs ARRAYS
//
// Give each stone on the board a group ID.  I.e., all stones that are part of the same group have the same group ID. 
//      $group_IDs_active
//      $group_IDs_inactive
//
// To accomplish this, make one pass through the board (for each of active and inactive players).  Start with the
// bottom row and visit each cell from left to right.  Then, moving upward through the rows, visit every cell in each row.  
//
//  Indexed arrays  (not key-value)
//      group_IDs_active [][] 
//      group_IDs_inactive [][] 
//
//  If the current cell is occupied 
//      If its left neighboring cell is occupied
//          Copy the left neighbor's group ID to the current cell
//          If the lower right neighboring cell is ALSO occupied
//              Replace all occurrences of the current cell's group ID with the lower right neighbor's group ID 
//      Else if its lower right neighboring cell is occupied
//          Copy the lower right neighbor's group ID
//      Else if its lower left neighboring cell is occupied
//          Copy the lower left neighbor's group ID
//      Else 
//          Give the current cell a fresh group ID
//          
// 
//
// GROUP SIZE ARRAYS
//
// Give each stone on the board a group size.  I.e., record the size of the group that each stone is part of.
// 
//      First, 
//          group_ID_populations_active         One dimensional arrays indexed by group ID record the size of the group of checkers having that group ID
//          group_ID_populations_inactive       
//
//      Then
//          group_sizes_active                  Two dimensional arrays indexed by ( u, v) record the size of the group the checker at that location is part of
//          group_sizes_inactive
//
//      Then,
//          For each candidate placement (unoccupied cell), check the surrounding adjacent cells (and check if different adjacent checkers are part of the same group)
//              
//              Combined active group size += surrounding (unique) active group size
//
//              If surrounding active group size == 1 (singleton)
//                  
//                  Check all of the singleton's surrounding cells for inactive groups
//
//                      Largest inactive group size = max (current largest inactive group size, active singleton adjacent inactive group size)
//
//  ## NEW ##   Else if surrounding active group size > 1   ## NEW ##
//
//  ## NEW ##       If any member of that active group has an adjacent inactive singleton
//
//  ## NEW ##           Largest inactive group size = max (current largest inactive group size, 1)
//                  
//              Largest inactive group size = max (current largest inactive group size, placement candidate adjacent inactive group)
//
//          Combined active group size += 1
//
//          If combined active group size = 1 (singleton)
//              Add the cell to legal_placements array 
//          Else if combined active group size > largest inactive group size
//              Add the cell to legal_placements array 
//              



function getSelectableCells ( $active_player_id )
{                       
    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( '###### getSelectableCells ( ) ######' ), 
    array(
    ) );
    */

    $inactive_player_id = self::getOtherPlayerId ( $active_player_id );

    $board = self::getBoard ();

    //
    // Two dimensional array to record the group ID for each occupied cell
    //      One array for each player.
    //
    $group_IDs_active = array (); 
    $group_IDs_inactive = array (); 

    //
    // One dimensional array to record how many of each group ID, indexed by group ID 
    //     One array for each player 
    //
    $group_ID_populations_active = array (); 
    $group_ID_populations_inactive = array (); 

    // 
    // Two dimensional array to record the size of group each occupied cell is part of 
    //      one for each player 
    //
    //    WAS NEEDED IN RIVE BUT NOT HERE IN OUST
    //
    /*
    $group_sizes_active = array (); 
    $group_sizes_inactive = array ();
    */

    $legal_moves = array ();

	//Get the board size
    $N = self::getGameStateValue ("board_size");

    //
    // Set fresh group IDs to 0
    //
    $fresh_group_ID_active = 0;
    $fresh_group_ID_inactive = 0;
    //self::setGameStateValue ( "fresh_group_ID_active", 0 );
    // self::setGameStateValue ( "fresh_group_ID_inactive", 0 );

    //
    // Fill in $group_IDs_active
    //
    for ( $v = 0; $v < 2*$N-1; $v++ )                   // Start on bottom row.  $v = 0
    {
        $u_left_limit = self::uLeftLimit ( $v, $N );    // Left to right limits of row in hexagonal board
        $u_right_limit = self::uRightLimit ( $v, $N );

        for (    $u = $u_left_limit; $u <= $u_right_limit; ++$u    )  
        {
            if (    $board [ $u ] [ $v ] == $active_player_id    )                                      // Current cell is occupied by active player
            {
                if (    self::getOccupant ( $u - 1, $v, $board, $N ) == $active_player_id    )          // If cell to left of current cell is occupied by active player...
                {
                    $group_IDs_active [ $u ] [ $v ] = $group_IDs_active [ $u - 1 ] [ $v ];              // ...copy its group ID to current cell

                    if (    self::getOccupant ( $u + 1, $v - 1, $board, $N ) == $active_player_id    )  // If cell to lower right of current cell is ALSO occupied by active player...
                    {
                        $current_group_ID = $group_IDs_active [ $u ] [ $v ];                            // ...replace all occurrences of current group ID with lower right group ID
                        $LR_group_ID = $group_IDs_active [ $u + 1 ] [ $v - 1 ];

                        foreach ($group_IDs_active as &$group_IDs_row)         
                        {
                            foreach ($group_IDs_row as &$group_ID)         
                            {
                                if ( $group_ID == $current_group_ID )
                                     $group_ID = $LR_group_ID;
                            }
                        }
                        unset($group_IDs_row);  // break the reference with the last element    
                        unset($group_ID);       // break the reference with the last element    
                    }
                }
                else if (    self::getOccupant ( $u + 1, $v - 1, $board, $N ) == $active_player_id    ) // If cell to lower right of current cell is occupied by active player...
                {
                    $group_IDs_active [ $u ] [ $v ] = $group_IDs_active [ $u + 1 ] [ $v - 1 ];          // ...copy its group ID to current cell
                }
                else if (    self::getOccupant ( $u, $v - 1, $board, $N ) == $active_player_id    )     // If cell to lower left of current cell is occupied...
                {
                    $group_IDs_active [ $u ] [ $v ] = $group_IDs_active [ $u ] [ $v - 1 ];              // ...copy its group ID to current cell
                }
                else
                {
                    $group_IDs_active [ $u ] [ $v ] = $fresh_group_ID_active++;                         // Give the current cell a fresh group ID
                }
            }
        }
    }

    
    //
    // Fill in $group_IDs_inactive
    //
    for ( $v = 0; $v < 2*$N-1; $v++ )                   // Start on bottom row.  $v = 0
    {
        $u_left_limit = self::uLeftLimit ( $v, $N );    // Left to right limits of row in hexagonal board
        $u_right_limit = self::uRightLimit ( $v, $N );

        for (    $u = $u_left_limit; $u <= $u_right_limit; ++$u    )  
        {
            if (    $board [ $u ] [ $v ] == $inactive_player_id    )                                    // Current cell is occupied by inactive player
            {
                if (    self::getOccupant ( $u - 1, $v, $board, $N ) == $inactive_player_id    )        // If cell to left of current cell is occupied by inactive player...
                {
                    $group_IDs_inactive [ $u ] [ $v ] = $group_IDs_inactive [ $u - 1 ] [ $v ];          // ...copy its group ID to current cell

                    if (    self::getOccupant ( $u + 1, $v - 1, $board, $N ) == $inactive_player_id    )// If cell to lower right of current cell is ALSO occupied by inactive player...
                    {
                        $current_group_ID = $group_IDs_inactive [ $u ] [ $v ];                          // ...replace all occurrences of current group ID with lower right group ID
                        $LR_group_ID = $group_IDs_inactive [ $u + 1 ] [ $v - 1 ];

                        foreach ($group_IDs_inactive as &$group_IDs_row)         
                        {
                            foreach ($group_IDs_row as &$group_ID)         
                            {
                                if ( $group_ID == $current_group_ID )
                                     $group_ID = $LR_group_ID;
                            }
                        }
                        unset($group_IDs_row);  // break the reference with the last element    
                        unset($group_ID);       // break the reference with the last element    
                    }
                }
                else if (    self::getOccupant ( $u + 1, $v - 1, $board, $N ) == $inactive_player_id    )   // If cell to lower right of current cell is occupied by inactive player...
                {
                    $group_IDs_inactive [ $u ] [ $v ] = $group_IDs_inactive [ $u + 1 ] [ $v - 1 ];          // ...copy its group ID to current cell
                }
                else if (    self::getOccupant ( $u, $v - 1, $board, $N ) == $inactive_player_id    )       // If cell to lower left of current cell is occupied by inactive player...
                {
                    $group_IDs_inactive [ $u ] [ $v ] = $group_IDs_inactive [ $u ] [ $v - 1 ];              // ...copy its group ID to current cell
                }
                else
                {
                    $group_IDs_inactive [ $u ] [ $v ] = $fresh_group_ID_inactive++;                         // Give the current cell a fresh group ID
                }
            }
        }
    }




    //
    // Fill in group ID populations arrays 
    //
    foreach ($group_IDs_active as $group_IDs_row)           // Active player
    {
        foreach ($group_IDs_row as $group_ID)         
        {
            if (    isset ( $group_ID_populations_active [ $group_ID ] )    )   
                ++$group_ID_populations_active [ $group_ID ];
            else
                $group_ID_populations_active [ $group_ID ] = 1;
        }
    }

    foreach ($group_IDs_inactive as $group_IDs_row)         // Inctive player
    {
        foreach ($group_IDs_row as $group_ID)         
        {
            if (    isset ( $group_ID_populations_inactive [ $group_ID ] )    )   
                ++$group_ID_populations_inactive [ $group_ID ];
            else
                $group_ID_populations_inactive [ $group_ID ] = 1;
        }
    }



    // ################################################################################################
    // ################################################################################################
    //
    // TESTING
    //
    //      SHOW EVERY OCCUPIED CELL GROUP ID AND GROUPS SIZE
    //
    //          FIRST FOR ACTIVE PLAYER 
    //
    /*
    for ( $v = 0; $v < 2*$N-1; $v++ )                   // Start on bottom row.  $v = 0
    {
        $u_left_limit = self::uLeftLimit ( $v, $N );    // Left to right limits of row in hexagonal board
        $u_right_limit = self::uRightLimit ( $v, $N );

        for (    $u = $u_left_limit; $u <= $u_right_limit; ++$u    )  
        {
            if (    $board [ $u ] [ $v ] == $active_player_id    ) 
            {
                    self::notifyAllPlayers( "backendMessage", clienttranslate( '${var_1}, ${var_2} active player' ), 
                    array(
                            "var_1" => $u,
                            "var_2" => $v
                    ) );

                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'group id = ${var_1}' ), 
                    array(
                            "var_1" => $group_IDs_active [ $u ] [ $v ]
                    ) );

                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'group size = ${var_1}' ), 
                    array(
                            "var_1" => $group_ID_populations_active [    $group_IDs_active [ $u ] [ $v ]    ]
                    ) );
            }
        }
    }

    //
    //          THEN FOR INACTIVE PLAYER 
    //
    for ( $v = 0; $v < 2*$N-1; $v++ )                   // Start on bottom row.  $v = 0
    {
        $u_left_limit = self::uLeftLimit ( $v, $N );    // Left to right limits of row in hexagonal board
        $u_right_limit = self::uRightLimit ( $v, $N );

        for (    $u = $u_left_limit; $u <= $u_right_limit; ++$u    )  
        {
            if (    $board [ $u ] [ $v ] == $inactive_player_id    ) 
            {
                    self::notifyAllPlayers( "backendMessage", clienttranslate( '${var_1}, ${var_2} inactive player' ), 
                    array(
                            "var_1" => $u,
                            "var_2" => $v
                    ) );

                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'group id = ${var_1}' ), 
                    array(
                            "var_1" => $group_IDs_inactive [ $u ] [ $v ]
                    ) );

                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'group size = ${var_1}' ), 
                    array(
                            "var_1" => $group_ID_populations_inactive [    $group_IDs_inactive [ $u ] [ $v ]    ]
                    ) );
            }
        }
    }
    */


    //
    // ############################################################
    //
    // CONSIDER EACH UNOCCUPIED CELL AS A LEGAL PLACEMENT CANDIDATE
    //
    // ############################################################
    //
    $directions = array( array( -1, 0 ), array( -1, 1 ), array( 0, 1 ), 
                  array( 1, 0 ), array( 1, -1), array( 0, -1 ) );

    $sub_directions = array( array( -1, 0 ), array( -1, 1 ), array( 0, 1 ), 
                      array( 1, 0 ), array( 1, -1), array( 0, -1 ) );


    for ( $v = 0; $v < 2*$N-1; $v++ )                                   // Start on bottom row.  $v = 0
    {
        $u_left_limit = self::uLeftLimit ( $v, $N );                    // Left to right limits of row in hexagonal board
        $u_right_limit = self::uRightLimit ( $v, $N );

        for (    $u = $u_left_limit; $u <= $u_right_limit; ++$u    )  
        {

            /*
            if ( $u == 5 && $v == 5 )
                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'HERE 5,5' ), 
                    array(
                    ) );

            if ( $u == 8 && $v == 5 )
                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'HERE 8,5' ), 
                    array(
                    ) );
            */

            $combined_group_size_active = 0;

            $largest_neighboring_group_inactive = 0;

            
            if (    ! isset ( $board [ $u ] [ $v ] )    )                   // Current cell is unoccupied and is a candidate for a legal placement
            //if (    $board [ $u ] [ $v ] == NULL    )                     // Current cell is unoccupied and is a candidate for a legal placement
            {
                $visited_neighboring_group_IDs_active = array ( );
                $visited_neighboring_group_IDs_inactive = array ( );

                foreach( $directions as $direction )                        // Visit every surrounding cell of the candidate cell
                {
                    $neighbor_u = $u + $direction[0];
                    $neighbor_v = $v + $direction[1];

                    if (    self::getOccupant ( $neighbor_u, $neighbor_v, $board, $N ) == $active_player_id    )    // Neighbor is active player
                    {
                        $group_ID_active = $group_IDs_active [ $neighbor_u ][ $neighbor_v ];

                        //
                        //      Don't visit any group more than once.
                        //      I.e., different neighbors might be part of the same group.
                        //                    
                        if (    ! in_array ( $group_ID_active, $visited_neighboring_group_IDs_active )    )
                        {
                            $visited_neighboring_group_IDs_active [ ] = $group_ID_active; // Add the neighboring active group ID into the array


                            $group_size_active = $group_ID_populations_active [ $group_ID_active ];

                            $combined_group_size_active += $group_size_active;


                            if (    $group_size_active == 1    )        // If neighbor is active singleton, check all of ITS surrounding cells for inactive groups
                            {
                                $sub_visited_neighboring_group_IDs_inactive = array ( );

                                foreach( $sub_directions as $sub_direction )
                                {
                                    $sub_neighbor_u = $neighbor_u + $sub_direction[0];
                                    $sub_neighbor_v = $neighbor_v + $sub_direction[1];

                                    if (    self::getOccupant ( $sub_neighbor_u, $sub_neighbor_v, $board, $N ) == $inactive_player_id )
                                    {
                                        $sub_group_ID_inactive = $group_IDs_inactive [ $sub_neighbor_u ][ $sub_neighbor_v ];

                                        //
                                        //      Don't count any group more than once.
                                        //      I.e., different neighbors might be part of the same group.
                                        //                    
                                        if (    ! in_array ( $sub_group_ID_inactive, $sub_visited_neighboring_group_IDs_inactive )    )
                                        {
                                            $sub_visited_neighboring_group_IDs_inactive [ ] = $sub_group_ID_inactive; // Add the neighboring inactive subgroup ID into the array


                                            $sub_group_size_inactive = $group_ID_populations_inactive [ $sub_group_ID_inactive ];

                                            $largest_neighboring_group_inactive = max ( $largest_neighboring_group_inactive, $sub_group_size_inactive );
                                            //if (    $sub_group_size_inactive > $largest_neighboring_group_inactive   )
                                            //{
                                            //    $largest_neighboring_group_inactive = $sub_group_size_inactive;
                                            //}

                                        }
                                    }
                                }
                            }
                            else //  $group_size_active > 1   Check whole active group to see if there's an adjacent inactive singleton
                            {
                                for ( $v_sub = 0; $v_sub < 2*$N-1; $v_sub++ )                         // Start on bottom row.  $v_value = 0
                                {
                                    $u_left_limit_sub = self::uLeftLimit ( $v_sub, $N );                      // Left to right limits of row in hexagonal board
                                    $u_right_limit_sub = self::uRightLimit ( $v_sub, $N );

                                    for (    $u_sub = $u_left_limit_sub; $u_sub <= $u_right_limit_sub; ++$u_sub    )  
                                    {
                                        if (    isset ( $group_IDs_active [ $u_sub ] [ $v_sub ] )    )
                                        {
                                            if (    $group_IDs_active [ $u_sub ] [ $v_sub ] == $group_ID_active    )
                                            {
                                                foreach( $sub_directions as $sub_direction )
                                                {
                                                    $sub_neighbor_u = $u_sub + $sub_direction[0];
                                                    $sub_neighbor_v = $v_sub + $sub_direction[1];
 
                                                    if (    self::getOccupant ( $sub_neighbor_u, $sub_neighbor_v, $board, $N ) == $inactive_player_id    )
                                                    {
                                                        $largest_neighboring_group_inactive = max ( $largest_neighboring_group_inactive, 1 );

                                                        //
                                                        // If there's an adjacent singleton, no need to check for more adjacent singletons.  
                                                        // Just break out of the foreach loop and the two for loops.
                                                        //
                                                        break 3;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    else if (    self::getOccupant ( $neighbor_u, $neighbor_v, $board, $N ) == $inactive_player_id    )     // Neighbor is inactive player
                    {
                        $group_ID_inactive = $group_IDs_inactive [ $neighbor_u ][ $neighbor_v ];

                        //
                        //      Don't count any group more than once.
                        //      I.e., different neighbors might be part of the same group.
                        //                    
                        if (    ! in_array ( $group_ID_inactive, $visited_neighboring_group_IDs_inactive )    )
                        {
                            $visited_neighboring_group_IDs_inactive [ ] = $group_ID_inactive; // Add the neighboring inactive group ID into the array


                            $group_size_inactive = $group_ID_populations_inactive [ $group_ID_inactive ];

                            $largest_neighboring_group_inactive = max ( $largest_neighboring_group_inactive, $group_size_inactive );
                        }
                    }
                }// foreach( $directions as $direction ) ... end


                $combined_group_size_active += 1;  // ...to account for placed checker



                if (      $combined_group_size_active == 1    // You can place a singleton anywhere
                    || (    $largest_neighboring_group_inactive > 0                                            // There is a neighboring inactive group...
                        && $combined_group_size_active > $largest_neighboring_group_inactive    )      )      // ... AND the combined active group is larger than it
                {
                    if(    ! isset ( $legal_moves [ $u ] )    )
                        $legal_moves [ $u ] = array();

                    $legal_moves [ $u ] [ $v ] = true;
                }



            }// $board [ $u ] [ $v ] == NULL ... end


            /*
            if ( $u == 5 && $v == 5 )
                    self::notifyAllPlayers( "backendMessage", clienttranslate( '5,5 comb_gr_size_act = ${var_1}, largst_n_gr_sz_inactive = ${var_2}' ), 
                    array(
                            "var_1" => $combined_group_size_active,
                            "var_2" => $largest_neighboring_group_inactive
                    ) );


            if ( $u == 8 && $v == 5 )
                    self::notifyAllPlayers( "backendMessage", clienttranslate( '8,5 comb_gr_size_act = ${var_1}, largst_n_gr_sz_inactive = ${var_2}' ), 
                    array(
                            "var_1" => $combined_group_size_active,
                            "var_2" => $largest_neighboring_group_inactive
                    ) );
            */



        }// for u ... end
    } // end for v ... end
    
    return $legal_moves;
}





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
        if ($color=='5e3200')
            return clienttranslate('BROWN');
        if ($color=='00a0b4')
            return clienttranslate('BLUE');
} 


function getPlayerColor ( $player_id ) 
{
    $players = self::loadPlayersBasicInfos();

    if (isset ( $players [ $player_id ] ) )
        return $players[ $player_id ][ 'player_color' ];
    else
        return null;
}



function isCellOnBoard ($u, $v, $N)
{    
    switch ($N)
    {
        case 6:  // size 6 board
            if (    (  $u >= 0 && $u < 6 && $v >= 5 - $u && $v < 11 )             // Cell on board
                 || (  $u >= 6 && $u < 11 && $v >= 0 && $v <= 15 - $u  )    )
                 return true;
            else 
                 return false;

        case 7:  // size 7 board
            if (    (  $u >= 0 && $u < 7 && $v >= 6 - $u && $v < 13 )             // Cell on board
                 || (  $u >= 7 && $u < 13 && $v >= 0 && $v <= 18 - $u  )    )
                 return true;
            else 
                 return false;

        case 8:  // size 8 board
            if (    (  $u >= 0 && $u < 8 && $v >= 7 - $u && $v < 15 )             // Cell on board
                 || (  $u >= 8 && $u < 15 && $v >= 0 && $v <= 21 - $u  )    )
                 return true;
            else 
                 return false;
    }
}



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



//Exchange the player colors (used for the first move rule)
function swapColors()
{
    //Get original player colors
    $player=self::getObjectListFromDB("SELECT player_color color, player_name name, player_id id FROM player");
    $color1=$player[0]['color'];
    $color2=$player[1]['color'];
    //Swap it in the db
    $sql = "UPDATE player SET player_color =
					CASE
						WHEN player_no=1 THEN '$color2'
						WHEN player_no=2 THEN '$color1'
					END";
	self::DbQuery( $sql );
		
	//
    // Notifications of new color for each player
    //

    $player_panel_str_0 = "0";
    $player_panel_str_1 = "1";

	self::notifyAllPlayers( "swapColors", clienttranslate( '${player_name} is now playing <span style="color:#${player_color};">${player_colorname}</span>.' ), array(
		'i18n' => array( 'player_colorname' ),
		'player_id' => $player[0]['id'],
		'player_name' => $player[0]['name'],
		'player_color' => $player[1]['color'],
		'player_colorname' => self::getColorName($player[1]['color']),

        'placedCheckers' => $player_panel_str_0
	) );

	self::notifyAllPlayers( "swapColors", clienttranslate( '${player_name} is now playing <span style="color:#${player_color};">${player_colorname}</span>.' ), array(
		'i18n' => array( 'player_colorname' ),
		'player_id' => $player[1]['id'],
		'player_name' => $player[1]['name'],
		'player_color' => $player[0]['color'],
		'player_colorname' => self::getColorName($player[0]['color']),

        'placedCheckers' => $player_panel_str_1
	) );


	//Update player info
	self::reloadPlayersBasicInfos();

}




//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

function placeChecker ( $u, $v )
{    
    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( 'placeChecker' ), 
    array(
            
    ) );
    */

    self::checkAction( 'placeChecker' );  

    $board = self::getBoard();

    $active_player_id = self::getActivePlayerId(); 
    $inactive_player_id = self::getOtherPlayerId ( $active_player_id );

    $active_player_color = self::getPlayerColor ( $active_player_id );
    $inactive_player_color = self::getPlayerColor ( $inactive_player_id );

    $N=self::getGameStateValue("board_size");


    $removable_checkers = array ();

    $checker_IDs  = array ();

    $removable_checker_IDs = array ();




    $legal_moves = self::getSelectableCells ( $active_player_id );

    if (    $legal_moves [ $u ] [ $v ] == true    ) 
    {

        $removable_checkers = self::getRemovableCheckers ( $u, $v );

        //$count_removable_checkers = count ( $removable_checkers );



        $count_removable_checkers = 0;

        foreach ( $removable_checkers as $removable_checkers_row ) 
        {
            $count_removable_checkers += count ( $removable_checkers_row );
        }


        /*
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'count rem checkers ${var_1}' ), 
            array(
                "var_1" => $count_removable_checkers
            ) );
        */

        //
        // Place checker 
        //
        //      Update globals and database 
        //
        if (    $active_player_color == "5e3200"    )
        {
            $checker_id_value = self::getGameStateValue( "brown_checker_id" );
            self::incGameStateValue( "brown_checker_id", 1 );

            self::incGameStateValue( "total_brown_checkers", 1 );
            self::incGameStateValue( "total_blue_checkers", -$count_removable_checkers );
        }
        else 
        {
            $checker_id_value = self::getGameStateValue( "blue_checker_id" );
            self::incGameStateValue( "blue_checker_id", 1 );

            self::incGameStateValue( "total_blue_checkers", 1 );
            self::incGameStateValue( "total_brown_checkers", -$count_removable_checkers );
        }


        $sql = "UPDATE board SET board_player = $active_player_id, checker_id = $checker_id_value
                WHERE ( board_u, board_v) = ($u, $v)";  
    
        self::DbQuery( $sql );






        //
        // LAST MOVE INDICATOR
        //
        self::setGameStateValue ("last_move_u", $u);
        self::setGameStateValue ("last_move_v", $v);

        self::incGameStateValue ("last_move_id", 1);

        //
        // Notify about last move 
        //
        self::notifyAllPlayers( "lastMoveIndicator", clienttranslate( '' ), 
            array(
            'u' => $u,
            'v' => $v,
            'id' => self::getGameStateValue ("last_move_id")
            ) );




        //
        //
        //
        // SHOW WHATS IN THE DATABASE AT THIS POINT ##########################################################################################
        //
        //
        //
        /*
        $sql = "SELECT board_u u, board_v v, checker_id ch_id
                FROM board WHERE board_player = $active_player_id";

        $result = self::DbQuery( $sql );

        if ( $result->num_rows > 0 ) 
        {
             // output data of each row
            while (    $row = $result->fetch_assoc()    ) 
            {
                self::notifyAllPlayers( "backendMessage", clienttranslate( '${var_1}' ), 
                    array(
                        "var_1" => $row["u"]." ".$row["v"]." ". $row["ch_id"]
                    ) );
            }
        }
        else 
        {
            self::notifyAllPlayers( "backendMessage", clienttranslate( '0 results' ), 
                array(
                ) );
        }
        */


        //
        // Remove checkers
        //
        //      Update database 
        //
        if ( $count_removable_checkers > 0 )
        {
            //
            // Get removable checker IDs now, before the checkers are removed from the database.
            // 
            // Use later when notifying about removed checkers 
            //
            $checker_IDs = self::getBoardCheckerIDs ();

            for ( $v_value = 0; $v_value < 2*$N-1; $v_value++ )                       // Start on bottom row.  $v = 0
            {
                $u_left_limit = self::uLeftLimit ( $v_value, $N );                    // Left to right limits of row in hexagonal board
                $u_right_limit = self::uRightLimit ( $v_value, $N );

                for (    $u_value = $u_left_limit; $u_value <= $u_right_limit; ++$u_value    )  
                {
                    if (    isset ( $removable_checkers [ $u_value ] [ $v_value ] )    )
                    {
                        $removable_checker_IDs [ ] = $checker_IDs [ $u_value ] [ $v_value ]; // Add to removed checkers array
                    }
                }
            }

            /*
            $removable_checker_IDs_str = implode ( "_", $removable_checker_IDs );

            self::notifyAllPlayers( "backendMessage", clienttranslate( '$removable_checker_IDs_str = ${var_1}' ), 
                array(
                    "var_1" => $removable_checker_IDs_str
                ) );
            */


            //
            // Now remove the removable checkers from the database 
            //
            //$sql_values = array();

            for ( $v_value = 0; $v_value < 2*$N-1; $v_value++ )                       // Start on bottom row.  $v = 0
            {
                $u_left_limit = self::uLeftLimit ( $v_value, $N );                    // Left to right limits of row in hexagonal board
                $u_right_limit = self::uRightLimit ( $v_value, $N );

                for (    $u_value = $u_left_limit; $u_value <= $u_right_limit; ++$u_value    )  
                {
                    if (    isset ( $removable_checkers [ $u_value ] [ $v_value ] )    )
                    {
                        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL
                                WHERE ( board_u, board_v) = ($u_value, $v_value)";    
                                
                        self::DbQuery( $sql );
                    }
                }
            }
        }


   
        //
        // Notify about placed checker 
        //
        self::notifyAllPlayers( "checkerPlaced", clienttranslate( '' ), 
            array(
            'u' => $u,
            'v' => $v,
            'player_id' => $active_player_id,
            'checker_id' => $checker_id_value
            ) );

    

        //
        // Notify about removed checkers
        //
        if ( $count_removable_checkers > 0 )
        {
            $removed_checker_IDs_str = implode ( "_", $removable_checker_IDs );

            self::notifyAllPlayers( "checkersRemoved", clienttranslate( '' ), 
                array(
                'u' => $u,
                'v' => $v,
                'player_id' => $inactive_player_id,
                //'player_id' => $active_player_id,
                'removed_checker_IDs_str' => $removed_checker_IDs_str
                ) );
        }



        //
        // Update the playerPanel to display the players' number of placed checkers
        //
        $total_brown_checkers = self::getGameStateValue ( "total_brown_checkers");
        $total_brown_checkers_str = "{$total_brown_checkers}";

        $total_blue_checkers = self::getGameStateValue ( "total_blue_checkers");
        $total_blue_checkers_str = "{$total_blue_checkers}";

        if (    $active_player_color == "5e3200"   ) // Brown player is active
        {
            self::notifyAllPlayers("playerPanel",
                "",
                array(
                "placed_checkers" => $total_brown_checkers_str,
                "player_id" => $active_player_id ));

            self::notifyAllPlayers("playerPanel",
                "",
                array(
                "placed_checkers" => $total_blue_checkers_str,
                "player_id" => self::getOtherPlayerId($active_player_id) ));
        }
        else 
        {
            self::notifyAllPlayers("playerPanel",
                "",
                array(
                "placed_checkers" => $total_blue_checkers_str,
                "player_id" => $active_player_id ));

            self::notifyAllPlayers("playerPanel",
                "",
                array(
                "placed_checkers" => $total_brown_checkers_str,
                "player_id" => self::getOtherPlayerId($active_player_id) ));
        }


        $u_display_coord = chr( ord( "A" ) + $u );
        $v_display_coord = $v + 1;

        self::notifyAllPlayers( "checkerPlacedHistory", clienttranslate( '${active_player_name} 
            ${u_display_coord}${v_display_coord}' ),     
            array(
                'active_player_name' => self::getActivePlayerName(),
                'u_display_coord' => $u_display_coord,
                'v_display_coord' => $v_display_coord
            ) );


        //
        // Go to the next state
        //
        // Check if it's the first move
        //
	    if ( self::getGameStateValue( "move_number" ) == 0 ) // 0 since the variable is incremented when the move has been validated
        {
			//Increment number of moves
			self::incGameStateValue( "move_number", 1 );
				
			// Go to the choice state
			$this->gamestate->nextState( 'firstMoveChoice' );
		}
        else  // Not the first move
        {
            if ( $count_removable_checkers == 0 )                           // No capture
            {
                $this->gamestate->nextState( 'placeNonCapturingChecker' );
            }
            else                                                            // CAPTURE
            {
                $total_brown_checkers = self::getGameStateValue ( "total_brown_checkers");
                $total_blue_checkers = self::getGameStateValue ( "total_blue_checkers");







                /*
                self::notifyAllPlayers( "backendMessage", clienttranslate( '$total_brown_checkers = ${var_1}' ), 
                    array(
                        "var_1" => $total_brown_checkers
                    ) );

                self::notifyAllPlayers( "backendMessage", clienttranslate( '$total_blue_checkers = ${var_1}' ), 
                    array(
                        "var_1" => $total_blue_checkers
                    ) );
                */






                if ( $total_blue_checkers == 0 )                                            //  BROWN WINS
                {
                    if ( $inactive_player_color == "5e3200") // Brown
                    {
                        $sql = "UPDATE player
                                SET player_score = 1 WHERE player_id = $inactive_player_id";
                    }
                    else 
                    {
                        $sql = "UPDATE player
                                SET player_score = 1 WHERE player_id = $active_player_id";
                    }


                    self::DbQuery( $sql );


                    $newScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );

                    self::notifyAllPlayers( "newScores", "", array(
                        "scores" => $newScores
                    ) );

                    /*
                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'AAAAA Go to endGame' ), 
                        array(
                        ) );
                    */

                    $this->gamestate->nextState( 'endGame' );
                }
                else if ( $total_brown_checkers == 0 )                                      // BLUE WINS
                {
                    if ( $inactive_player_color == "00a0b4") // Blue
                    {
                        $sql = "UPDATE player
                                SET player_score = 1 WHERE player_id = $inactive_player_id";
                    }
                    else 
                    {
                        $sql = "UPDATE player
                        SET player_score = 1 WHERE player_id = $active_player_id";
                    }

 
                    self::DbQuery( $sql );


                    $newScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );

                    self::notifyAllPlayers( "newScores", "", array(
                        "scores" => $newScores
                    ) );

                    /*
                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'BBBBB Go to endGame' ), 
                        array(
                        ) );
                    */

                    $this->gamestate->nextState( 'endGame' );
                }
                else // Capture but nobody won
                {
                    $legal_moves = self::getSelectableCells ( $active_player_id );

                    //
                    //
                    // If you killed an enemy group and you don't have any legal moves afterward, tell state machine you can't play 
                    //
                    //
                    $count_legal_moves = 0;


                    foreach ( $legal_moves as $legal_moves_row ) 
                    {
                        $count_legal_moves += count ( $legal_moves_row );
                    }

                    /*
                    self::notifyAllPlayers( "backendMessage", clienttranslate( '$count_legal_moves = ${var_1}' ), 
                        array(
                            "var_1" => $count_legal_moves
                        ) );
                    */


                    if ( $count_legal_moves == 0 )    
                    {
                        $this->gamestate->nextState( 'cantPlay' );
                    }
                    else 
                    {
                        $this->gamestate->nextState( 'placeCapturingChecker' );
                    }
                }
            }
        }
    }
    else
        throw new feException( "Cell is not selectable." );
}
    


//
// Put u,v on the board 
//      $board [u][v] = active player ID
//
//  Fill in group IDs for both players 
//      See getSelectableCells for explanation
// 
//  Indexed arrays  (not key-value)
//      group_IDs_active [][] 
//      group_IDs_inactive [][] 
//
// active_group_id = group ID of placed checker u,v
//
// Go through all the cells on the board 
// If group ID == active_group_id 
//      Check surrounding cells for adjacent inactive group IDs 
//      For each adjacent inactive group ID 
//          Go through all the cells on the board 
//          If a cell is occupied by that inactive group ID 
//              Add its location to removable_checkers 
//
function getRemovableCheckers ($u_value, $v_value)
{
    $board = self::getBoard();

    $active_player_id = self::getActivePlayerId(); 

	//Get the board size
    $N = self::getGameStateValue ("board_size");


    //
    //
    // PUT PLACED STONE (u_value,v_value) IN THE board ARRAY
    //
    //
    $board [ $u_value ] [ $v_value ] = $active_player_id;


    $inactive_player_id = self::getOtherPlayerId ( $active_player_id );

    $removable_checkers = array ( );

    //
    // Two dimensional array to record the group ID for each occupied cell
    //      One array for each player.
    //
    $group_IDs_active = array (); 
    $group_IDs_inactive = array (); 

    $group_ID_populations_active = array ();

    $fresh_group_ID_active = 0;
    $fresh_group_ID_inactive = 0;

    //  MAYBE DON'T NEED THIS ##################################################################
    // One dimensional array to record how many of each group ID, indexed by group ID 
    //     One array for each player 
    //
    //$group_ID_populations_active = array (); 
    //$group_ID_populations_inactive = array (); 


    //
    // Fill in $group_IDs_active
    //
    for ( $v = 0; $v < 2*$N-1; $v++ )                   // Start on bottom row.  $v = 0
    {
        $u_left_limit = self::uLeftLimit ( $v, $N );    // Left to right limits of row in hexagonal board
        $u_right_limit = self::uRightLimit ( $v, $N );

        for (    $u = $u_left_limit; $u <= $u_right_limit; ++$u    )  
        {
            if (    $board [ $u ] [ $v ] == $active_player_id    )                                      // Current cell is occupied by active player
            {
                if (    self::getOccupant ( $u - 1, $v, $board, $N ) == $active_player_id    )          // If cell to left of current cell is occupied by active player...
                {
                    $group_IDs_active [ $u ] [ $v ] = $group_IDs_active [ $u - 1 ] [ $v ];              // ...copy its group ID to current cell

                    if (    self::getOccupant ( $u + 1, $v - 1, $board, $N ) == $active_player_id    )  // If cell to lower right of current cell is ALSO occupied by active player...
                    {
                        $current_group_ID = $group_IDs_active [ $u ] [ $v ];                            // ...replace all occurrences of current group ID with lower right group ID
                        $LR_group_ID = $group_IDs_active [ $u + 1 ] [ $v - 1 ];

                        foreach ($group_IDs_active as &$group_IDs_row)         
                        {
                            foreach ($group_IDs_row as &$group_ID)         
                            {
                                if ( $group_ID == $current_group_ID )
                                     $group_ID = $LR_group_ID;
                            }
                        }
                        unset($group_IDs_row);  // break the reference with the last element    
                        unset($group_ID);       // break the reference with the last element    
                    }
                }
                else if (    self::getOccupant ( $u + 1, $v - 1, $board, $N ) == $active_player_id    ) // If cell to lower right of current cell is occupied by active player...
                {
                    $group_IDs_active [ $u ] [ $v ] = $group_IDs_active [ $u + 1 ] [ $v - 1 ];          // ...copy its group ID to current cell
                }
                else if (    self::getOccupant ( $u, $v - 1, $board, $N ) == $active_player_id    )     // If cell to lower left of current cell is occupied...
                {
                    $group_IDs_active [ $u ] [ $v ] = $group_IDs_active [ $u ] [ $v - 1 ];              // ...copy its group ID to current cell
                }
                else
                {
                    $group_IDs_active [ $u ] [ $v ] = $fresh_group_ID_active++;                         // Give the current cell a fresh group ID
                }
            }
        }
    }

    
    //
    // Fill in $group_IDs_inactive
    //
    for ( $v = 0; $v < 2*$N-1; $v++ )                   // Start on bottom row.  $v = 0
    {
        $u_left_limit = self::uLeftLimit ( $v, $N );    // Left to right limits of row in hexagonal board
        $u_right_limit = self::uRightLimit ( $v, $N );

        for (    $u = $u_left_limit; $u <= $u_right_limit; ++$u    )  
        {
            if (    $board [ $u ] [ $v ] == $inactive_player_id    )                                    // Current cell is occupied by inactive player
            {
                if (    self::getOccupant ( $u - 1, $v, $board, $N ) == $inactive_player_id    )        // If cell to left of current cell is occupied by inactive player...
                {
                    $group_IDs_inactive [ $u ] [ $v ] = $group_IDs_inactive [ $u - 1 ] [ $v ];          // ...copy its group ID to current cell

                    if (    self::getOccupant ( $u + 1, $v - 1, $board, $N ) == $inactive_player_id    )// If cell to lower right of current cell is ALSO occupied by inactive player...
                    {
                        $current_group_ID = $group_IDs_inactive [ $u ] [ $v ];                          // ...replace all occurrences of current group ID with lower right group ID
                        $LR_group_ID = $group_IDs_inactive [ $u + 1 ] [ $v - 1 ];

                        foreach ($group_IDs_inactive as &$group_IDs_row)         
                        {
                            foreach ($group_IDs_row as &$group_ID)         
                            {
                                if ( $group_ID == $current_group_ID )
                                     $group_ID = $LR_group_ID;
                            }
                        }
                        unset($group_IDs_row);  // break the reference with the last element    
                        unset($group_ID);       // break the reference with the last element    
                    }
                }
                else if (    self::getOccupant ( $u + 1, $v - 1, $board, $N ) == $inactive_player_id    )   // If cell to lower right of current cell is occupied by inactive player...
                {
                    $group_IDs_inactive [ $u ] [ $v ] = $group_IDs_inactive [ $u + 1 ] [ $v - 1 ];          // ...copy its group ID to current cell
                }
                else if (    self::getOccupant ( $u, $v - 1, $board, $N ) == $inactive_player_id    )       // If cell to lower left of current cell is occupied by inactive player...
                {
                    $group_IDs_inactive [ $u ] [ $v ] = $group_IDs_inactive [ $u ] [ $v - 1 ];              // ...copy its group ID to current cell
                }
                else
                {
                    $group_IDs_inactive [ $u ] [ $v ] = $fresh_group_ID_inactive++;                         // Give the current cell a fresh group ID
                }
            }
        }
    }


    //
    // Fill in group ID populations arrays 
    //
    foreach ($group_IDs_active as $group_IDs_row)           // Active player
    {
        foreach ($group_IDs_row as $group_ID)         
        {
            if (    isset ( $group_ID_populations_active [ $group_ID ] )    )   
                ++$group_ID_populations_active [ $group_ID ];
            else
                $group_ID_populations_active [ $group_ID ] = 1;
        }
    }




    //
    //  If the placed stone is part of a group >= 2 ##############################################   SINGLETONS DON'T CAPTURE ANYTHING
    //      It must be a capture
    //      Check every cell on the board 
    //      If active checker group ID == placed checker group ID
    //          Check all of its neighboring cells 
    //          If neighboring cell has inactive checker
    //              Check every cell on board for that neighboring cell's inactive group ID
    //                  Add each such cell to removable_cells
    //                  $board[][] of each such cell = NULL
    //



    $placed_checker_group_id = $group_IDs_active [ $u_value ] [ $v_value ];

    //
    //
    // IF PLACED CHECKER IS A SINGLETON 
    //      RETURN EMPTY removable_checkers BECAUSE  ............................   SINGLETONS DON'T CAPTURE ANYTHING
    //
    //
    if (    $group_ID_populations_active [ $placed_checker_group_id ] == 1    )
        return $removable_checkers;




    $directions = array( array( -1, 0 ), array( -1, 1 ), array( 0, 1 ), 
                         array( 1, 0 ), array( 1, -1), array( 0, -1 ) );

    //
    //
    // FIND ALL REMOVABLE CHECKERS 
    //
    //
    for ( $v = 0; $v < 2*$N-1; $v++ )                   // Start on bottom row.  $v = 0
    {
        $u_left_limit = self::uLeftLimit ( $v, $N );    // Left to right limits of row in hexagonal board
        $u_right_limit = self::uRightLimit ( $v, $N );

        for ( $u = $u_left_limit; $u <= $u_right_limit; ++$u )  
        {
            if (    isset ( $group_IDs_active [ $u ] [ $v ] )    )
            {
                if ( $group_IDs_active [ $u ] [ $v ] == $placed_checker_group_id )          // Current cell has same group ID as placed checker 
                {
                    //
                    // Check for adjacent inactive checkers
                    //
                    foreach( $directions as $direction )                                    // Visit every surrounding cell of the checker with the same ID as the placed checker
                    {
                        $neighbor_u = $u + $direction[0];
                        $neighbor_v = $v + $direction[1];

                        if ( self::getOccupant ( $neighbor_u, $neighbor_v, $board, $N ) == $inactive_player_id )
                        {
                            $neighbor_group_id = $group_IDs_inactive  [ $neighbor_u ] [ $neighbor_v ];

                            //
                            // Remove all inactive checkers with $neighbor_group_id
                            //
                            for ( $v_sub = 0; $v_sub < 2*$N-1; $v_sub++ )           // Start on bottom row.  $v_sub = 0
                            {
                                $u_left_limit_sub = self::uLeftLimit ( $v_sub, $N );    // Left to right limits of row in hexagonal board
                                $u_right_limit_sub = self::uRightLimit ( $v_sub, $N );

                                for ( $u_sub = $u_left_limit_sub; $u_sub <= $u_right_limit_sub; ++$u_sub )  
                                {
                                    if (    isset ( $group_IDs_inactive [ $u_sub ] [ $v_sub ] )    )
                                    {
                                        if ( $group_IDs_inactive [ $u_sub ] [ $v_sub ] == $neighbor_group_id )          // Current cell has same group ID as placed checker 
                                        {
                                            $group_IDs_inactive [ $u_sub ] [ $v_sub ] = NULL;
                                        
                                            $board [ $u_sub ] [ $v_sub ] = NULL;


                                            if(    ! isset ( $removable_checkers [ $u_sub ] )    )
                                                $removable_checkers [ $u_sub ] = array();

                                            $removable_checkers [ $u_sub ] [ $v_sub ] = true;

                                            /*
                                            self::notifyAllPlayers( "backendMessage", clienttranslate( 'active ${var_1}, ${var_2}' ), 
                                                array(
                                                    "var_1" => $u,
                                                    "var_2" => $v
                                                ) );

                                            self::notifyAllPlayers( "backendMessage", clienttranslate( 'removable ${var_1}, ${var_2}' ), 
                                                array(
                                                    "var_1" => $u_sub,
                                                    "var_2" => $v_sub
                                                ) );
                                            */
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    return $removable_checkers;
}






//If the player have choosen to exchange the colors (and take the first move)
function chooseFirstMove()
{   
    //Swap the players colors
	self::swapColors();

	//Swap tokens
	$active_player_id = self::getActivePlayerId();
    $inactive_player_id = self::getOtherPlayerId($active_player_id);


	$sql = "UPDATE board SET board_player = 1 WHERE board_player = $active_player_id"; 
	self::DbQuery($sql);
        
	$sql = "UPDATE board SET board_player = $active_player_id WHERE board_player = $inactive_player_id"; 
	self::DbQuery($sql);
        
	$sql = "UPDATE board SET board_player = $inactive_player_id WHERE board_player = 1"; 
	self::DbQuery($sql);


    //Update players info
	self::reloadPlayersBasicInfos();

	//Let's the other player play
	$this->gamestate->nextState( 'nextTurn' );
}




function getBoardCheckerIDs()
{
    return self::getDoubleKeyCollectionFromDB( "SELECT board_u u, board_v v, checker_id
                                                FROM board", true );
}



    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

function argPlaceChecker()
{
    return array(
        'selectableCells' => self::getSelectableCells ( self::getActivePlayerId() )
    );
}


//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

function stNextPlayer()
{   
    // Activate next player
    $active_player_id = self::activeNextPlayer();


    $selectableCells = self::getSelectableCells ( $active_player_id );
    
    if(    count ( $selectableCells ) == 0    )                             // Doesn't have any placements available
    {
        $this->gamestate->nextState( 'cantPlay' );
    }
    else
    {            
        self::giveExtraTime( $active_player_id );                           // Active player can play. Give him some extra time.
        $this->gamestate->nextState( 'nextTurn' );
    } 
}


function stNextPlayerFirstMove()
{	
    // Activate next player
    $player_id = self::activeNextPlayer();

    self::giveExtraTime( $player_id );
    //Go to the choice state
    $this->gamestate->nextState( 'firstMoveChoice' );
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
