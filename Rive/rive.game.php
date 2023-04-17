<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * Rive implementation : © <Mark Steere> <marksteere@gmail.com>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * rive.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class Rive extends Table
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
            "move_number" => 11,                // Counter of the number of moves (used to detect first move)
            "red_checker_id" => 20,             // Initial red checker ID
            "blue_checker_id" => 21,            // Initial blue checker ID
            "total_red_checkers"=> 22,          // Total number of red checkers
            "total_blue_checkers"=> 23,         // Total number of blue checkers
            "fresh_group_ID"=> 30,              // Fresh group ID
            "group_combining_checker_u"=> 40,   // u coordinate of checker that reduced the number of groups 
            "group_combining_checker_v"=> 41,   // v coordinate of checker that reduced the number of groups
            "pare_down_group_size_current"=> 50,// Combined group is currently this size
            "pare_down_group_size_goal"=> 51,   // Combined group must be pared down to this size
            "board_size" => 101,                // Size of board
        ) );        
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "rive";
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
        self::setGameStateValue( "red_checker_id", 1000 );
        self::setGameStateValue( "blue_checker_id", 2000 );
        self::setGameStateValue( "total_red_checkers", 0 );
        self::setGameStateValue( "total_blue_checkers", 0 );



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
            /*
            if( $color == 'ff0000' )
                $red_player_id = $player_id;
            else
                $blue_player_id = $player_id;
            */

        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );

        $sql = "UPDATE player
                SET player_score = 0 WHERE 1";
                
        self::DbQuery( $sql );

        //self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();
        

        //
        // Initialize the board with NULL values for all cells 
        //
        $N_option = self::getGameStateValue ("board_size");
    
        if (  $N_option == 3  )         // $N_option = 3 - board size 3 - small
            $N = 3;
        else if (  $N_option == 5  )    // $N_option = 5 - board size 3x3x5 - medium
            $N = 5;
        else                            // $N_option = 40 - board size 4 - large    ... option 40 not typo
            $N = 4;


        $sql = "INSERT INTO board (board_u, board_v, board_player, checker_id) VALUES ";

        $sql_values = array();


        switch ($N)
        {
            case 3:  // size 3 - small board

                for ($u = 0; $u < 5; $u++)
                {
                    for( $v = 0; $v < 5; $v++ )
                    {
                        if (    self::isCellOnBoard($u, $v, 3)    )
                            $sql_values[] = "($u, $v, NULL, NULL)";                   
                    }
                }

                $sql .= implode( $sql_values, ',' );
                self::DbQuery( $sql );

                break;

            case 5:  // 3x3x5 - medium board

                for ($u = 0; $u < 7; $u++)
                {
                    for( $v = 0; $v < 5; $v++ )
                    {
                        if (    self::isCellOnBoard($u, $v, 5)    )
                            $sql_values[] = "($u, $v, NULL, NULL)";                   
                    }
                }

                $sql .= implode( $sql_values, ',' );
                self::DbQuery( $sql );

                break;

            case 4:  // size 4 - large board

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

        }
        

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

         switch ( $N )
         {
		    case 3:
                $result['board_size'] = 3; // size 3 - small
                break;

		    case 5:
                $result['board_size'] = 5; // size 3x3x5 - medium
                break;

		    case 40:
                $result['board_size'] = 4; // size 4 - large
                break;
         }




        //
        // Placed checkers
        //
        $activePlayerId = self::getActivePlayerId();

        $otherPlayerId = self::getOtherPlayerId($activePlayerId);


        $active_player_color = self::getPlayerColor ( $activePlayerId );


        $placedCheckers = array();

        if (    $active_player_color == "ff0000"    )
        {
            $placedCheckers[$activePlayerId] = self::getGameStateValue( "total_red_checkers" );
            $placedCheckers[$otherPlayerId] = self::getGameStateValue( "total_blue_checkers" );
        }
        else
        {
            $placedCheckers[$activePlayerId] = self::getGameStateValue( "total_blue_checkers" );
            $placedCheckers[$otherPlayerId] = self::getGameStateValue( "total_red_checkers" );
        }
        
        $result['placedCheckers'] = $placedCheckers;
        






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
        $total_red_checkers = self::getGameStateValue ( "total_red_checkers");

        $total_blue_checkers = self::getGameStateValue ( "total_blue_checkers");

        $total_checkers = $total_red_checkers + $total_blue_checkers;



        $N_option = self::getGameStateValue ( "board_size" );
            
        if (  $N_option == 3  )         // $N_option = 3 - board size 3 - small
            $board_size = 19;
        else if (  $N_option == 5  )    // $N_option = 5 - board size 3x3x5 - medium
            $board_size = 29;
        else                            // $N_option = 40 - board size 4 - large    ... option 40 not typo
            $board_size = 37;


        return min ( $total_checkers / $board_size * 300, 100 );
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    


function isSelectableCell( $u, $v, $board)
{
    //$board = self::getBoard();

	//Get the board size
    $N=self::getGameStateValue("board_size");
     
    switch ($N)
    {
        case 3:                                             // small board
            if (    self::isCellOnBoard($u, $v, 3)    )
            {
                if ( $board [ $u ] [ $v ] == NULL )
                    return true;
                else 
                    return false;
            }
            else 
                return false;

        case 5:                                             // medium board
            if (    self::isCellOnBoard($u, $v, 5)    )
            {
                if ( $board [ $u ] [ $v ] == NULL )
                    return true;
                else 
                    return false;
            }
            else 
                return false;

        case 4:                                             // large board
            if (    self::isCellOnBoard($u, $v, 4)    )
            {
                if ( $board [ $u ] [ $v ] == NULL )
                    return true;
                else 
                    return false;
            }
            else 
                return false;
    }
}





//
// Give each stone on the board a group ID.  I.e., all stones that are part of the same group have the same group ID.  
// To accomplish this, make one pass through the board.  Start with the bottom row and visit each cell from left to 
// right.  Then, moving upward, visit every cell in every other row.  
//
//  Indexed array group_IDs [][]
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
function getSelectableCells ( )
{                       
    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( '###### getSelectableCells ( ) ######' ), 
    array(
    ) );
    */

    $board = self::getBoard ();

    //
    // Each occupied cell will have a group ID for the group it's part of
    //
    $group_IDs = array (); 

    $legal_moves = array ();

	//Get the board size
    $N_option = self::getGameStateValue ("board_size");

    if (  $N_option == 3  )         // $N_option = 3 - board size 3 - small
        $N = 3;
    else if (  $N_option == 5  )    // $N_option = 5 - board size 3x3x5 - medium
        $N = 5;
    else                            // $N_option = 40 - board size 4 - large    ... option 40 not typo
        $N = 4;



    // Set fresh_group_ID to 0
    self::setGameStateValue ( "fresh_group_ID", 0 );

     
    switch ($N)
    {
        case 3:                         // size 3 board - small
            //
            // Fill in group_IDs 
            //
            for ( $v = 0; $v < 5; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (2 - $v, 0); $u <= min (6 - $v, 4); $u++ )  // Left to right within hexagonal board
                {
                    if (    isset ( $board [ $u ] [ $v ] )    ) // Current cell is occupied
                    {
                        if (    self::isCellOccupied ( $u - 1, $v, $board, 3 )    )         // If cell to left of current cell is occupied...
                        {
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u - 1 ] [ $v ];        // ...copy its group ID to current cell

                            if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 3 )    ) // If cell to lower right of current cell is ALSO occupied...
                            {
                                $current_group_ID = $group_IDs [ $u ] [ $v ];               // ...replace all occurrences of current group ID with lower right group ID
                                $LR_group_ID = $group_IDs [ $u + 1 ] [ $v - 1 ];
                                
                                foreach ($group_IDs as &$group_IDs_row)         
                                {
                                    foreach ($group_IDs_row as &$group_ID)         
                                    {
                                        if ( $group_ID == $current_group_ID )
                                             $group_ID = $LR_group_ID;
                                    }
                                }
                                unset($group_IDs_row); // break the reference with the last element    
                                unset($group_ID); // break the reference with the last element    
                            }
                        }
                        else if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 3 )    )    // If cell to lower right of current cell is occupied...
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u + 1 ] [ $v - 1 ];        // ...copy its group ID to current cell

                        else if (    self::isCellOccupied ( $u, $v - 1, $board, 3 )    )        // If cell to lower left of current cell is occupied...
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u ] [ $v - 1 ];            // ...copy its group ID to current cell

                        else
                        {
                            $group_IDs [ $u ] [ $v ] = self::getGameStateValue ( "fresh_group_ID" );        // Give the current cell a fresh group ID
                            
                            self::incGameStateValue ( "fresh_group_ID", 1 );
                        }

                        /*
                        self::notifyAllPlayers( "backendMessage", clienttranslate( '$group_IDs [ ${var_1} ] [ ${var_2} ] = ${var_3}' ), 
                        array(
                                "var_1" => $u,
                                "var_2" => $v,
                                "var_3" => $group_IDs [ $u ] [ $v ]

                        ) );
                        */
                    }
                }
            }


            //
            // For each group ID, record the size of its group 
            //
            $group_ID_populations = array (); // One dimensional array records each group ID and the size of the group of checkers with that ID

            foreach ($group_IDs as $group_IDs_row)         
            {
                foreach ($group_IDs_row as $group_ID)         
                {
                    if (    isset ( $group_ID_populations [ $group_ID ] )    )
                        ++$group_ID_populations [ $group_ID ];
                    else
                        $group_ID_populations [ $group_ID ] = 1;
                }
            }



            //
            // For each occupied cell, record the size of the group that checker is part of 
            //
            $checkers_group_sizes = array ();

            for ( $u = 0; $u < 5; $u++ )
            {
                for ( $v = 0; $v < 5; $v++ )
                {
                    if (    isset ( $group_IDs [ $u ][ $v ] )    )
                    {
                        $checkers_group_sizes [ $u ] [ $v ] = $group_ID_populations [    $group_IDs [ $u ] [ $v ]    ];
                    }
                }
            }



            //
            // For each unoccupied cell, record its biggest neighboring group 
            //
            $biggest_neighboring_groups = array ();

            $directions = array( array( -1, 0 ), array( -1, 1 ), array( 0, 1 ), 
                          array( 1, 0 ), array( 1, -1), array( 0, -1 ) );


            for ( $v = 0; $v < 5; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (2 - $v, 0); $u <= min (6 - $v, 4); $u++ )  // Left to right within hexagonal board
                {
                    if ( $board [ $u ] [ $v ] == NULL ) // Current cell is unoccupied
                    {
                        $biggest_neighboring_group = 0;
       
                        foreach( $directions as $direction )
                        {
                            $neighbor_u = $u + $direction[0];
                            $neighbor_v = $v + $direction[1];

                            if (    self::isCellOccupied ( $neighbor_u, $neighbor_v, $board, $N )    )
                            {
                                if (    $checkers_group_sizes [ $neighbor_u ] [ $neighbor_v ] > $biggest_neighboring_group   )
                                {
                                    $biggest_neighboring_group = $checkers_group_sizes [ $neighbor_u ] [ $neighbor_v ];
                                }
                            }
                        }

                        $biggest_neighboring_groups [ $u ] [ $v ] = $biggest_neighboring_group;

                        /*
                        self::notifyAllPlayers( "backendMessage", clienttranslate( '$biggest_neighboring_groups [ ${var_1} ] [ ${var_2} ] = ${var_3}' ), 
                        array(
                                "var_1" => $u,
                                "var_2" => $v,
                                "var_3" => $biggest_neighboring_groups [ $u ] [ $v ]

                        ) );
                        */
                    }
                }
            }


            //
            // Find the smallest of biggest neighboring groups
            //
            $smallest_biggest_neighboring_group = 1000;

            foreach ($biggest_neighboring_groups as $biggest_neighboring_groups_row)         
            {
                foreach ($biggest_neighboring_groups_row as $biggest_neighboring_group)         
                {
                    //self::notifyAllPlayers( "backendMessage", clienttranslate( '$biggest_neighboring_group = ${var_1}' ), 
                    //array(
                    //        "var_1" => $biggest_neighboring_group
                    //) );

                    if ( $biggest_neighboring_group < $smallest_biggest_neighboring_group )
                        $smallest_biggest_neighboring_group = $biggest_neighboring_group;                
                }
            }

            //self::notifyAllPlayers( "backendMessage", clienttranslate( '$smallest_biggest_neighboring_group = ${var_1}' ), 
            //array(
            //        "var_1" => $smallest_biggest_neighboring_group
            //) );

            /*
            $testarray = array ();

            $testarray [1][2] = 100;
            $testarray [3][4] = 101;
            $testarray [5][6] = 102;

            foreach ($testarray as $subarray)
            {
                foreach ($subarray as $element)
                {
                    self::notifyAllPlayers( "backendMessage", clienttranslate( '$element = ${var_1}' ), 
                    array(
                    "var_1" => $element
                ) );
                }
            }
            */


            //
            // Legal moves are unoccupied cells whose largest neighboring group is as small as possible
            //
            $legal_moves = array ();


            for ( $v = 0; $v < 5; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (2 - $v, 0); $u <= min (6 - $v, 4); $u++ )  // Left to right within hexagonal board
                {
                    if ( $board [ $u ] [ $v ] == NULL ) // Current cell is unoccupied
                    {
                        if ( $biggest_neighboring_groups [ $u ] [ $v ] == $smallest_biggest_neighboring_group )
                        {
                            if(    ! isset ( $legal_moves [ $u ] )    )
                                $legal_moves [ $u ] = array();

                             $legal_moves [ $u ] [ $v ] = true;
                        }
                    }
                }
            }

            break;

 
        case 5:                         // size 3x3x5 board - medium
            //
            // Fill in group_IDs 
            //
            for ( $v = 0; $v < 5; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (2 - $v, 0); $u <= min (8 - $v, 6); $u++ )  // Left to right within hexagonal board
                {
                    if (    isset ( $board [ $u ] [ $v ] )    ) // Current cell is occupied
                    {
                        if (    self::isCellOccupied ( $u - 1, $v, $board, 5 )    )         // If cell to left of current cell is occupied...
                        {
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u - 1 ] [ $v ];        // ...copy its group ID to current cell

                            if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 5 )    ) // If cell to lower right of current cell is ALSO occupied...
                            {
                                $current_group_ID = $group_IDs [ $u ] [ $v ];               // ...replace all occurrences of current group ID with lower right group ID
                                $LR_group_ID = $group_IDs [ $u + 1 ] [ $v - 1 ];
                                
                                foreach ($group_IDs as &$group_IDs_row)         
                                {
                                    foreach ($group_IDs_row as &$group_ID)         
                                    {
                                        if ( $group_ID == $current_group_ID )
                                             $group_ID = $LR_group_ID;
                                    }
                                }
                                unset($group_IDs_row); // break the reference with the last element    
                                unset($group_ID); // break the reference with the last element    
                            }
                        }
                        else if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 5 )    )    // If cell to lower right of current cell is occupied...
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u + 1 ] [ $v - 1 ];        // ...copy its group ID to current cell

                        else if (    self::isCellOccupied ( $u, $v - 1, $board, 5 )    )        // If cell to lower left of current cell is occupied...
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u ] [ $v - 1 ];            // ...copy its group ID to current cell

                        else
                        {
                            $group_IDs [ $u ] [ $v ] = self::getGameStateValue ( "fresh_group_ID" );        // Give the current cell a fresh group ID
                            
                            self::incGameStateValue ( "fresh_group_ID", 1 );
                        }

                        /*
                        self::notifyAllPlayers( "backendMessage", clienttranslate( '$group_IDs [ ${var_1} ] [ ${var_2} ] = ${var_3}' ), 
                        array(
                                "var_1" => $u,
                                "var_2" => $v,
                                "var_3" => $group_IDs [ $u ] [ $v ]

                        ) );
                        */
                    }
                }
            }


            //
            // For each group ID, record the size of its group 
            //
            $group_ID_populations = array (); // One dimensional array records each group ID and the size of the group of checkers with that ID

            foreach ($group_IDs as $group_IDs_row)         
            {
                foreach ($group_IDs_row as $group_ID)         
                {
                    if (    isset ( $group_ID_populations [ $group_ID ] )    )
                        ++$group_ID_populations [ $group_ID ];
                    else
                        $group_ID_populations [ $group_ID ] = 1;
                }
            }



            //
            // For each occupied cell, record the size of the group that checker is part of 
            //
            $checkers_group_sizes = array ();

            for ( $u = 0; $u < 7; $u++ )
            {
                for ( $v = 0; $v < 5; $v++ )
                {
                    if (    isset ( $group_IDs [ $u ][ $v ] )    )
                    {
                        $checkers_group_sizes [ $u ] [ $v ] = $group_ID_populations [    $group_IDs [ $u ] [ $v ]    ];
                    }
                }
            }



            //
            // For each unoccupied cell, record its biggest neighboring group 
            //
            $biggest_neighboring_groups = array ();

            $directions = array( array( -1, 0 ), array( -1, 1 ), array( 0, 1 ), 
                          array( 1, 0 ), array( 1, -1), array( 0, -1 ) );


            for ( $v = 0; $v < 5; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (2 - $v, 0); $u <= min (8 - $v, 6); $u++ )  // Left to right within hexagonal board
                {
                    if ( $board [ $u ] [ $v ] == NULL ) // Current cell is unoccupied
                    {
                        $biggest_neighboring_group = 0;
       
                        foreach( $directions as $direction )
                        {
                            $neighbor_u = $u + $direction[0];
                            $neighbor_v = $v + $direction[1];

                            if (    self::isCellOccupied ( $neighbor_u, $neighbor_v, $board, $N )    )
                            {
                                if (    $checkers_group_sizes [ $neighbor_u ] [ $neighbor_v ] > $biggest_neighboring_group   )
                                {
                                    $biggest_neighboring_group = $checkers_group_sizes [ $neighbor_u ] [ $neighbor_v ];
                                }
                            }
                        }

                        $biggest_neighboring_groups [ $u ] [ $v ] = $biggest_neighboring_group;

                        /*
                        self::notifyAllPlayers( "backendMessage", clienttranslate( '$biggest_neighboring_groups [ ${var_1} ] [ ${var_2} ] = ${var_3}' ), 
                        array(
                                "var_1" => $u,
                                "var_2" => $v,
                                "var_3" => $biggest_neighboring_groups [ $u ] [ $v ]

                        ) );
                        */
                    }
                }
            }


            //
            // Find the smallest of biggest neighboring groups
            //
            $smallest_biggest_neighboring_group = 1000;

            foreach ($biggest_neighboring_groups as $biggest_neighboring_groups_row)         
            {
                foreach ($biggest_neighboring_groups_row as $biggest_neighboring_group)         
                {
                    //self::notifyAllPlayers( "backendMessage", clienttranslate( '$biggest_neighboring_group = ${var_1}' ), 
                    //array(
                    //        "var_1" => $biggest_neighboring_group
                    //) );

                    if ( $biggest_neighboring_group < $smallest_biggest_neighboring_group )
                        $smallest_biggest_neighboring_group = $biggest_neighboring_group;                
                }
            }

            //self::notifyAllPlayers( "backendMessage", clienttranslate( '$smallest_biggest_neighboring_group = ${var_1}' ), 
            //array(
            //        "var_1" => $smallest_biggest_neighboring_group
            //) );

            //
            // Legal moves are unoccupied cells whose largest neighboring group is as small as possible
            //
            $legal_moves = array ();


            for ( $v = 0; $v < 5; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (2 - $v, 0); $u <= min (8 - $v, 6); $u++ )  // Left to right within hexagonal board
                {
                    if ( $board [ $u ] [ $v ] == NULL ) // Current cell is unoccupied
                    {
                        if ( $biggest_neighboring_groups [ $u ] [ $v ] == $smallest_biggest_neighboring_group )
                             $legal_moves [ $u ] [ $v ] = true;
                    }
                }
            }

            break;


        case 4:                         // size 4 board - large
            //
            // Fill in group_IDs 
            //
            for ( $v = 0; $v < 7; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (3 - $v, 0); $u <= min (9 - $v, 6); $u++ )  // Left to right within hexagonal board
                {
                    if (    isset ( $board [ $u ] [ $v ] )    ) // Current cell is occupied
                    {
                        if (    self::isCellOccupied ( $u - 1, $v, $board, 4 )    )         // If cell to left of current cell is occupied...
                        {
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u - 1 ] [ $v ];        // ...copy its group ID to current cell

                            if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 4 )    ) // If cell to lower right of current cell is ALSO occupied...
                            {
                                $current_group_ID = $group_IDs [ $u ] [ $v ];               // ...replace all occurrences of current group ID with lower right group ID
                                $LR_group_ID = $group_IDs [ $u + 1 ] [ $v - 1 ];
                                
                                foreach ($group_IDs as &$group_IDs_row)         
                                {
                                    foreach ($group_IDs_row as &$group_ID)         
                                    {
                                        if ( $group_ID == $current_group_ID )
                                             $group_ID = $LR_group_ID;
                                    }
                                }
                                unset($group_IDs_row); // break the reference with the last element    
                                unset($group_ID); // break the reference with the last element    
                            }
                        }
                        else if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 4 )    )    // If cell to lower right of current cell is occupied...
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u + 1 ] [ $v - 1 ];        // ...copy its group ID to current cell

                        else if (    self::isCellOccupied ( $u, $v - 1, $board, 4 )    )        // If cell to lower left of current cell is occupied...
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u ] [ $v - 1 ];            // ...copy its group ID to current cell

                        else
                        {
                            $group_IDs [ $u ] [ $v ] = self::getGameStateValue ( "fresh_group_ID" );        // Give the current cell a fresh group ID
                            
                            self::incGameStateValue ( "fresh_group_ID", 1 );
                        }

                    }
                }
            }


            //
            // For each group ID, record the size of its group 
            //
            $group_ID_populations = array (); // One dimensional array records each group ID and the size of the group of checkers with that ID

            foreach ($group_IDs as $group_IDs_row)         
            {
                foreach ($group_IDs_row as $group_ID)         
                {
                    if (    isset ( $group_ID_populations [ $group_ID ] )    )
                        ++$group_ID_populations [ $group_ID ];
                    else
                        $group_ID_populations [ $group_ID ] = 1;
                }
            }



            //
            // For each occupied cell, record the size of the group that checker is part of 
            //
            $checkers_group_sizes = array ();

            for ( $u = 0; $u < 7; $u++ )
            {
                for ( $v = 0; $v < 7; $v++ )
                {
                    if (    isset ( $group_IDs [ $u ][ $v ] )    )
                    {
                        $checkers_group_sizes [ $u ] [ $v ] = $group_ID_populations [    $group_IDs [ $u ] [ $v ]    ];
                    }
                }
            }



            //
            // For each unoccupied cell, record its biggest neighboring group 
            //
            $biggest_neighboring_groups = array ();

            $directions = array( array( -1, 0 ), array( -1, 1 ), array( 0, 1 ), 
                          array( 1, 0 ), array( 1, -1), array( 0, -1 ) );


            for ( $v = 0; $v < 7; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (3 - $v, 0); $u <= min (9 - $v, 6); $u++ )  // Left to right within hexagonal board
                {
                    if ( $board [ $u ] [ $v ] == NULL ) // Current cell is unoccupied
                    {
                        $biggest_neighboring_group = 0;
       
                        foreach( $directions as $direction )
                        {
                            $neighbor_u = $u + $direction[0];
                            $neighbor_v = $v + $direction[1];

                            if (    self::isCellOccupied ( $neighbor_u, $neighbor_v, $board, $N )    )
                            {
                                if (    $checkers_group_sizes [ $neighbor_u ] [ $neighbor_v ] > $biggest_neighboring_group   )
                                {
                                    $biggest_neighboring_group = $checkers_group_sizes [ $neighbor_u ] [ $neighbor_v ];
                                }
                            }
                        }

                        $biggest_neighboring_groups [ $u ] [ $v ] = $biggest_neighboring_group;

                    }
                }
            }


            //
            // Find the smallest of biggest neighboring groups
            //
            $smallest_biggest_neighboring_group = 1000;

            foreach ($biggest_neighboring_groups as $biggest_neighboring_groups_row)         
            {
                foreach ($biggest_neighboring_groups_row as $biggest_neighboring_group)         
                {
                    //self::notifyAllPlayers( "backendMessage", clienttranslate( '$biggest_neighboring_group = ${var_1}' ), 
                    //array(
                    //        "var_1" => $biggest_neighboring_group
                    //) );

                    if ( $biggest_neighboring_group < $smallest_biggest_neighboring_group )
                        $smallest_biggest_neighboring_group = $biggest_neighboring_group;                
                }
            }


            //
            // Legal moves are unoccupied cells whose largest neighboring group is as small as possible
            //
            $legal_moves = array ();


            for ( $v = 0; $v < 7; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (3 - $v, 0); $u <= min (9 - $v, 6); $u++ )  // Left to right within hexagonal board
                {
                    if ( $board [ $u ] [ $v ] == NULL ) // Current cell is unoccupied
                    {
                        if ( $biggest_neighboring_groups [ $u ] [ $v ] == $smallest_biggest_neighboring_group )
                        {
                            if(    ! isset ( $legal_moves [ $u ] )    )
                                $legal_moves [ $u ] = array();

                             $legal_moves [ $u ] [ $v ] = true;
                        }
                    }
                }
            }

            break;

    }
     
    return $legal_moves;
}





function evaluateAdjacentGroups ( $u_value, $v_value )
{

    $board = self::getBoard ();

    //
    // Each occupied cell will have a group ID for the group it's part of
    //
    $group_IDs = array (); 


	//Get the board size
    $N_option = self::getGameStateValue ("board_size");

    if (  $N_option == 3  )         // $N_option = 3 - board size 3 - small
        $N = 3;
    else if (  $N_option == 5  )    // $N_option = 5 - board size 3x3x5 - medium
        $N = 5;
    else                            // $N_option = 40 - board size 4 - large    ... option 40 not typo
        $N = 4;

    // Set fresh_group_ID to 0
    self::setGameStateValue ( "fresh_group_ID", 0 );


    switch ($N)
    {
        case 3:                     // size 3 board - small
            //
            // Fill in group_IDs 
            //
            for ( $v = 0; $v < 5; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (2 - $v, 0); $u <= min (6 - $v, 4); $u++ )  // Left to right within hexagonal board
                {
                    if (    isset ( $board [ $u ] [ $v ] )    ) // Current cell is occupied
                    {
                        if (    self::isCellOccupied ( $u - 1, $v, $board, 3 )    )         // If cell to left of current cell is occupied...
                        {
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u - 1 ] [ $v ];        // ...copy its group ID to current cell

                            if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 3 )    ) // If cell to lower right of current cell is ALSO occupied...
                            {
                                $current_group_ID = $group_IDs [ $u ] [ $v ];               // ...replace all occurrences of current group ID with lower right group ID
                                $LR_group_ID = $group_IDs [ $u + 1 ] [ $v - 1 ];
                                
                                foreach ($group_IDs as &$group_IDs_row)         
                                {
                                    foreach ($group_IDs_row as &$group_ID)         
                                    {
                                        if ( $group_ID == $current_group_ID )
                                             $group_ID = $LR_group_ID;
                                    }
                                }
                                unset($group_IDs_row); // break the reference with the last element    
                                unset($group_ID); // break the reference with the last element    
                            }
                        }
                        else if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 3 )    )    // If cell to lower right of current cell is occupied...
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u + 1 ] [ $v - 1 ];        // ...copy its group ID to current cell

                        else if (    self::isCellOccupied ( $u, $v - 1, $board, 3 )    )        // If cell to lower left of current cell is occupied...
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u ] [ $v - 1 ];            // ...copy its group ID to current cell

                        else
                        {
                            $group_IDs [ $u ] [ $v ] = self::getGameStateValue ( "fresh_group_ID" );        // Give the current cell a fresh group ID
                            
                            self::incGameStateValue ( "fresh_group_ID", 1 );
                        }

                        /*
                        self::notifyAllPlayers( "backendMessage", clienttranslate( '$group_IDs [ ${var_1} ] [ ${var_2} ] = ${var_3}' ), 
                        array(
                                "var_1" => $u,
                                "var_2" => $v,
                                "var_3" => $group_IDs [ $u ] [ $v ]

                        ) );
                        */
                    }
                }
            }


            //
            // For each group ID, record the size of its group 
            //
            $group_ID_populations = array (); // One dimensional array records each group ID and the size of the group of checkers with that ID

            foreach ($group_IDs as $group_IDs_row)         
            {
                foreach ($group_IDs_row as $group_ID)         
                {
                    if (    isset ( $group_ID_populations [ $group_ID ] )    )
                        ++$group_ID_populations [ $group_ID ];
                    else
                        $group_ID_populations [ $group_ID ] = 1;
                }
            }



            //
            // For each occupied cell, record the size of the group that checker is part of 
            //
            $checkers_group_sizes = array ();

            for ( $u = 0; $u < 5; $u++ )
            {
                for ( $v = 0; $v < 5; $v++ )
                {
                    if (    isset ( $group_IDs [ $u ][ $v ] )    )
                    {
                        $checkers_group_sizes [ $u ] [ $v ] = $group_ID_populations [    $group_IDs [ $u ] [ $v ]    ];
                    }
                }
            }


            //
            // Determine 
            //      Biggest neighboring group of ( $u_value, $u_value )
            //      Sum of neighboring group sizes
            //
            $visited_neighboring_group_IDs = array ( );
            
            $biggest_neighboring_group = 0;
            $sum_of_neighboring_groups = 0;

            $directions = array( array( -1, 0 ), array( -1, 1 ), array( 0, 1 ), 
                          array( 1, 0 ), array( 1, -1), array( 0, -1 ) );


            foreach( $directions as $direction )
            {
                $neighbor_u = $u_value + $direction[0];
                $neighbor_v = $v_value + $direction[1];

                if (    self::isCellOccupied ( $neighbor_u, $neighbor_v, $board, $N )    )
                {
                    //
                    // In calculating sum of group sizes 
                    //      Don't count any group more than once.
                    //      I.e., different neighbors might be part of the same group.
                    //                    
                    if (    !in_array ( $group_IDs [ $neighbor_u ][ $neighbor_v ], $visited_neighboring_group_IDs )    )
                    {
                        $visited_neighboring_group_IDs [ ] = $group_IDs [ $neighbor_u ][ $neighbor_v ]; // Add the neighboring group ID into the array

                        $checkers_group_size = $checkers_group_sizes [ $neighbor_u ] [ $neighbor_v ];

                        $sum_of_neighboring_groups += $checkers_group_size;

                        if (    $checkers_group_size > $biggest_neighboring_group   )
                        {
                            $biggest_neighboring_group = $checkers_group_size;
                        }
                    }                    
                }
            }


            /*
            self::notifyAllPlayers( "backendMessage", clienttranslate( 'biggest: ${var_1}  sum: ${var_2}' ), 
            array(
                "var_1" => $biggest_neighboring_group,
                "var_2" => $sum_of_neighboring_groups
            ) );
            */


            self::setGameStateValue( "group_combining_checker_u", $u_value );
            self::setGameStateValue( "group_combining_checker_v", $v_value );

            self::setGameStateValue( "pare_down_group_size_current", $sum_of_neighboring_groups + 1 );
            self::setGameStateValue( "pare_down_group_size_goal", $biggest_neighboring_group + 1 );

            break;


        case 5:                     // size 3x3x5 board - medium
            //
            // Fill in group_IDs 
            //
            for ( $v = 0; $v < 5; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (2 - $v, 0); $u <= min (8 - $v, 6); $u++ )  // Left to right within hexagonal board
                {
                    if (    isset ( $board [ $u ] [ $v ] )    ) // Current cell is occupied
                    {
                        if (    self::isCellOccupied ( $u - 1, $v, $board, 5 )    )         // If cell to left of current cell is occupied...
                        {
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u - 1 ] [ $v ];        // ...copy its group ID to current cell

                            if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 5 )    ) // If cell to lower right of current cell is ALSO occupied...
                            {
                                $current_group_ID = $group_IDs [ $u ] [ $v ];               // ...replace all occurrences of current group ID with lower right group ID
                                $LR_group_ID = $group_IDs [ $u + 1 ] [ $v - 1 ];
                                
                                foreach ($group_IDs as &$group_IDs_row)         
                                {
                                    foreach ($group_IDs_row as &$group_ID)         
                                    {
                                        if ( $group_ID == $current_group_ID )
                                             $group_ID = $LR_group_ID;
                                    }
                                }
                                unset($group_IDs_row); // break the reference with the last element    
                                unset($group_ID); // break the reference with the last element    
                            }
                        }
                        else if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 5 )    )    // If cell to lower right of current cell is occupied...
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u + 1 ] [ $v - 1 ];        // ...copy its group ID to current cell

                        else if (    self::isCellOccupied ( $u, $v - 1, $board, 5 )    )        // If cell to lower left of current cell is occupied...
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u ] [ $v - 1 ];            // ...copy its group ID to current cell

                        else
                        {
                            $group_IDs [ $u ] [ $v ] = self::getGameStateValue ( "fresh_group_ID" );        // Give the current cell a fresh group ID
                            
                            self::incGameStateValue ( "fresh_group_ID", 1 );
                        }
                    }
                }
            }


            //
            // For each group ID, record the size of its group 
            //
            $group_ID_populations = array (); // One dimensional array records each group ID and the size of the group of checkers with that ID

            foreach ($group_IDs as $group_IDs_row)         
            {
                foreach ($group_IDs_row as $group_ID)         
                {
                    if (    isset ( $group_ID_populations [ $group_ID ] )    )
                        ++$group_ID_populations [ $group_ID ];
                    else
                        $group_ID_populations [ $group_ID ] = 1;
                }
            }



            //
            // For each occupied cell, record the size of the group that checker is part of 
            //
            $checkers_group_sizes = array ();

            for ( $u = 0; $u < 7; $u++ )
            {
                for ( $v = 0; $v < 5; $v++ )
                {
                    if (    isset ( $group_IDs [ $u ][ $v ] )    )
                    {
                        $checkers_group_sizes [ $u ] [ $v ] = $group_ID_populations [    $group_IDs [ $u ] [ $v ]    ];
                    }
                }
            }

            //
            // Determine 
            //      Biggest neighboring group of ( $u_value, $u_value )
            //      Sum of neighboring group sizes
            //
            $visited_neighboring_group_IDs = array ( );
            
            $biggest_neighboring_group = 0;
            $sum_of_neighboring_groups = 0;

            $directions = array( array( -1, 0 ), array( -1, 1 ), array( 0, 1 ), 
                          array( 1, 0 ), array( 1, -1), array( 0, -1 ) );


            foreach( $directions as $direction )
            {
                $neighbor_u = $u_value + $direction[0];
                $neighbor_v = $v_value + $direction[1];

                if (    self::isCellOccupied ( $neighbor_u, $neighbor_v, $board, $N )    )
                {
                    //
                    // In calculating sum of group sizes 
                    //      Don't count any group more than once.
                    //      I.e., different neighbors might be part of the same group.
                    //                    
                    if (    !in_array ( $group_IDs [ $neighbor_u ][ $neighbor_v ], $visited_neighboring_group_IDs )    )
                    {
                        $visited_neighboring_group_IDs [ ] = $group_IDs [ $neighbor_u ][ $neighbor_v ]; // Add the neighboring group ID into the array

                        $checkers_group_size = $checkers_group_sizes [ $neighbor_u ] [ $neighbor_v ];

                        $sum_of_neighboring_groups += $checkers_group_size;

                        if (    $checkers_group_size > $biggest_neighboring_group   )
                        {
                            $biggest_neighboring_group = $checkers_group_size;
                        }
                    }                    
                }
            }


            self::setGameStateValue( "group_combining_checker_u", $u_value );
            self::setGameStateValue( "group_combining_checker_v", $v_value );

            self::setGameStateValue( "pare_down_group_size_current", $sum_of_neighboring_groups + 1 );
            self::setGameStateValue( "pare_down_group_size_goal", $biggest_neighboring_group + 1 );

            break;


        case 4:                     // size 4 board - large
            //
            // Fill in group_IDs 
            //
            for ( $v = 0; $v < 7; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (3 - $v, 0); $u <= min (9 - $v, 6); $u++ )  // Left to right within hexagonal board
                {
                    if (    isset ( $board [ $u ] [ $v ] )    ) // Current cell is occupied
                    {
                        if (    self::isCellOccupied ( $u - 1, $v, $board, 4 )    )         // If cell to left of current cell is occupied...
                        {
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u - 1 ] [ $v ];        // ...copy its group ID to current cell

                            if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 4 )    ) // If cell to lower right of current cell is ALSO occupied...
                            {
                                $current_group_ID = $group_IDs [ $u ] [ $v ];               // ...replace all occurrences of current group ID with lower right group ID
                                $LR_group_ID = $group_IDs [ $u + 1 ] [ $v - 1 ];
                                
                                foreach ($group_IDs as &$group_IDs_row)         
                                {
                                    foreach ($group_IDs_row as &$group_ID)         
                                    {
                                        if ( $group_ID == $current_group_ID )
                                             $group_ID = $LR_group_ID;
                                    }
                                }
                                unset($group_IDs_row); // break the reference with the last element    
                                unset($group_ID); // break the reference with the last element    
                            }
                        }
                        else if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 4 )    )    // If cell to lower right of current cell is occupied...
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u + 1 ] [ $v - 1 ];        // ...copy its group ID to current cell

                        else if (    self::isCellOccupied ( $u, $v - 1, $board, 4 )    )        // If cell to lower left of current cell is occupied...
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u ] [ $v - 1 ];            // ...copy its group ID to current cell

                        else
                        {
                            $group_IDs [ $u ] [ $v ] = self::getGameStateValue ( "fresh_group_ID" );        // Give the current cell a fresh group ID
                            
                            self::incGameStateValue ( "fresh_group_ID", 1 );
                        }

                    }
                }
            }


            //
            // For each group ID, record the size of its group 
            //
            $group_ID_populations = array (); // One dimensional array records each group ID and the size of the group of checkers with that ID

            foreach ($group_IDs as $group_IDs_row)         
            {
                foreach ($group_IDs_row as $group_ID)         
                {
                    if (    isset ( $group_ID_populations [ $group_ID ] )    )
                        ++$group_ID_populations [ $group_ID ];
                    else
                        $group_ID_populations [ $group_ID ] = 1;
                }
            }



            //
            // For each occupied cell, record the size of the group that checker is part of 
            //
            $checkers_group_sizes = array ();

            for ( $u = 0; $u < 7; $u++ )
            {
                for ( $v = 0; $v < 7; $v++ )
                {
                    if (    isset ( $group_IDs [ $u ][ $v ] )    )
                    {
                        $checkers_group_sizes [ $u ] [ $v ] = $group_ID_populations [    $group_IDs [ $u ] [ $v ]    ];
                    }
                }
            }


            //
            // Determine 
            //      Biggest neighboring group of ( $u_value, $u_value )
            //      Sum of neighboring group sizes
            //
            $visited_neighboring_group_IDs = array ( );
            
            $biggest_neighboring_group = 0;
            $sum_of_neighboring_groups = 0;

            $directions = array( array( -1, 0 ), array( -1, 1 ), array( 0, 1 ), 
                          array( 1, 0 ), array( 1, -1), array( 0, -1 ) );


            foreach( $directions as $direction )
            {
                $neighbor_u = $u_value + $direction[0];
                $neighbor_v = $v_value + $direction[1];

                if (    self::isCellOccupied ( $neighbor_u, $neighbor_v, $board, $N )    )
                {
                    //
                    // In calculating sum of group sizes 
                    //      Don't count any group more than once.
                    //      I.e., different neighbors might be part of the same group.
                    //                    
                    if (    !in_array ( $group_IDs [ $neighbor_u ][ $neighbor_v ], $visited_neighboring_group_IDs )    )
                    {
                        $visited_neighboring_group_IDs [ ] = $group_IDs [ $neighbor_u ][ $neighbor_v ]; // Add the neighboring group ID into the array

                        $checkers_group_size = $checkers_group_sizes [ $neighbor_u ] [ $neighbor_v ];

                        $sum_of_neighboring_groups += $checkers_group_size;

                        if (    $checkers_group_size > $biggest_neighboring_group   )
                        {
                            $biggest_neighboring_group = $checkers_group_size;
                        }
                    }                    
                }
            }


            self::setGameStateValue( "group_combining_checker_u", $u_value );
            self::setGameStateValue( "group_combining_checker_v", $v_value );

            self::setGameStateValue( "pare_down_group_size_current", $sum_of_neighboring_groups + 1 );
            self::setGameStateValue( "pare_down_group_size_goal", $biggest_neighboring_group + 1 );

            break;
    }
}




//
// To see if this placement combines groups, compare the number of groups before and after the placement
//
function doesCombineGroups ( $u, $v )
{
    $board = self::getBoard ();

    $position_after_placement = $board;
    $position_after_placement  [ $u ] [ $v ] = true;


    $i = self::countOfGroups ( $board );
    $j = self::countOfGroups ( $position_after_placement );

    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( '${var_1} ${var_2}' ), 
    array(
            "var_1" => $i,
            "var_2" => $j
    ) );
    */

    if ( $j < $i )
        return true;
    else 
        return false;
}




function countOfGroups ( $board )
{
    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( '###### countOfGroups ######' ), 
    array(
    ) );
    */

    //
    // Each occupied cell will have a group ID for the group it's part of
    //
    $group_IDs = array (); // Each occupied cell will have a group ID for the group it's part of


	// Get the board size
    $N_option = self::getGameStateValue ("board_size");

    if (  $N_option == 3  )         // $N_option = 3 - board size 3 - small
        $N = 3;
    else if (  $N_option == 5  )    // $N_option = 5 - board size 3x3x5 - medium
        $N = 5;
    else                            // $N_option = 40 - board size 4 - large    ... option 40 not typo
        $N = 4;

    // Set fresh_group_ID to 0
    self::setGameStateValue ( "fresh_group_ID", 0 );

     
    switch ($N)
    {
        case 3:
            //
            // Fill in group_IDs 
            //
            for ( $v = 0; $v < 5; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (2 - $v, 0); $u <= min (6 - $v, 4); $u++ )  // Left to right within hexagonal board
                {
                    if (    isset ( $board [ $u ] [ $v ] )    ) // THIS RETURNS TRUE
                    {
                        if (    self::isCellOccupied ( $u - 1, $v, $board, 3 )    )         // If cell to left of current cell is occupied...
                        {                                                                   // ...copy its group ID to current cell
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u - 1 ] [ $v ];        

                            if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 3 )    ) // If cell to lower right of current cell is ALSO occupied...
                            {                                                               // ...replace all occurrences of current group ID with lower right group ID
                                $current_group_ID = $group_IDs [ $u ] [ $v ];               
                                $LR_group_ID = $group_IDs [ $u + 1 ] [ $v - 1 ];
                                
                                foreach ($group_IDs as &$group_IDs_row)         
                                {
                                    foreach ($group_IDs_row as &$group_ID)         
                                    {
                                        if ( $group_ID == $current_group_ID )
                                             $group_ID = $LR_group_ID;
                                    }
                                }
                                unset($group_IDs_row); // break the reference with the last element    
                                unset($group_ID); // break the reference with the last element    
                            }
                        }
                        else if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 3 )    )            // If cell to lower right of current cell is occupied...
                        {                                                                               // ...copy its group ID to current cell
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u + 1 ] [ $v - 1 ];        
                        }
                        else if (    self::isCellOccupied ( $u, $v - 1, $board, 3 )    )        // If cell to lower left of current cell is occupied...
                        {                                                                       // ...copy its group ID to current cell
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u ] [ $v - 1 ];            
                        }

                        else
                        {
                            $group_IDs [ $u ] [ $v ] = self::getGameStateValue ( "fresh_group_ID" );        // Give the current cell a fresh group ID
                            
                            self::incGameStateValue ( "fresh_group_ID", 1 );
                        }
                    }
                }
            }


            //
            // Simple list of group IDs
            //
            $group_ID_values = array (); 

            foreach ($group_IDs as $group_IDs_row)         
            {
                foreach ($group_IDs_row as $group_ID)         
                {
                    if (!in_array($group_ID, $group_ID_values))
                    {
                        $group_ID_values[] = $group_ID; 
                    }                
                }
            }

            return count ( $group_ID_values );


        case 5:
            //
            // Fill in group_IDs 
            //
            for ( $v = 0; $v < 5; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (2 - $v, 0); $u <= min (8 - $v, 6); $u++ )  // Left to right within hexagonal board
                {
                    if (    isset ( $board [ $u ] [ $v ] )    ) // Current cell is occupied
                    {
                        if (    self::isCellOccupied ( $u - 1, $v, $board, 5 )    )         // If cell to left of current cell is occupied...
                        {
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u - 1 ] [ $v ];        // ...copy its group ID to current cell

                            if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 5 )    ) // If cell to lower right of current cell is ALSO occupied...
                            {
                                $current_group_ID = $group_IDs [ $u ] [ $v ];               // ...replace all occurrences of current group ID with lower right group ID
                                $LR_group_ID = $group_IDs [ $u + 1 ] [ $v - 1 ];
                                
                                foreach ($group_IDs as &$group_IDs_row)         
                                {
                                    foreach ($group_IDs_row as &$group_ID)         
                                    {
                                        if ( $group_ID == $current_group_ID )
                                             $group_ID = $LR_group_ID;
                                    }
                                }
                                unset($group_IDs_row); // break the reference with the last element    
                                unset($group_ID); // break the reference with the last element    
                            }
                        }
                        else if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 5 )    )    // If cell to lower right of current cell is occupied...
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u + 1 ] [ $v - 1 ];        // ...copy its group ID to current cell

                        else if (    self::isCellOccupied ( $u, $v - 1, $board, 5 )    )        // If cell to lower left of current cell is occupied...
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u ] [ $v - 1 ];            // ...copy its group ID to current cell

                        else
                        {
                            $group_IDs [ $u ] [ $v ] = self::getGameStateValue ( "fresh_group_ID" );        // Give the current cell a fresh group ID
                            
                            self::incGameStateValue ( "fresh_group_ID", 1 );
                        }
                    }
                }
            }


            //
            // Simple list of group IDs
            //
            $group_ID_values = array (); 

            foreach ($group_IDs as $group_IDs_row)         
            {
                foreach ($group_IDs_row as $group_ID)         
                {
                    if (!in_array($group_ID, $group_ID_values))
                    {
                        $group_ID_values[] = $group_ID; 
                    }                
                }
            }

            return count ( $group_ID_values );


        case 4:
            //
            // Fill in group_IDs 
            //
            for ( $v = 0; $v < 7; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (3 - $v, 0); $u <= min (9 - $v, 6); $u++ )  // Left to right within hexagonal board
                {
                    if (    isset ( $board [ $u ] [ $v ] )    ) // THIS RETURNS TRUE
                    {
                        if (    self::isCellOccupied ( $u - 1, $v, $board, 4 )    )         // If cell to left of current cell is occupied...
                        {                                                                   // ...copy its group ID to current cell
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u - 1 ] [ $v ];        

                            if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 4 )    ) // If cell to lower right of current cell is ALSO occupied...
                            {                                                               // ...replace all occurrences of current group ID with lower right group ID
                                $current_group_ID = $group_IDs [ $u ] [ $v ];               
                                $LR_group_ID = $group_IDs [ $u + 1 ] [ $v - 1 ];
                                
                                foreach ($group_IDs as &$group_IDs_row)         
                                {
                                    foreach ($group_IDs_row as &$group_ID)         
                                    {
                                        if ( $group_ID == $current_group_ID )
                                             $group_ID = $LR_group_ID;
                                    }
                                }
                                unset($group_IDs_row); // break the reference with the last element    
                                unset($group_ID); // break the reference with the last element    
                            }
                        }
                        else if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 4 )    )            // If cell to lower right of current cell is occupied...
                        {                                                                               // ...copy its group ID to current cell
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u + 1 ] [ $v - 1 ];        
                        }
                        else if (    self::isCellOccupied ( $u, $v - 1, $board, 4 )    )        // If cell to lower left of current cell is occupied...
                        {                                                                       // ...copy its group ID to current cell
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u ] [ $v - 1 ];            
                        }

                        else
                        {
                            $group_IDs [ $u ] [ $v ] = self::getGameStateValue ( "fresh_group_ID" );        // Give the current cell a fresh group ID
                            
                            self::incGameStateValue ( "fresh_group_ID", 1 );
                        }
                    }
                }
            }


            //
            // Simple list of group IDs
            //
            $group_ID_values = array (); 

            foreach ($group_IDs as $group_IDs_row)         
            {
                foreach ($group_IDs_row as $group_ID)         
                {
                    if (!in_array($group_ID, $group_ID_values))
                    {
                        $group_ID_values[] = $group_ID; 
                    }                
                }
            }

            return count ( $group_ID_values );



    }
}




//
// * Fill in group_IDs.  Each occupied cell has the ID of the group its in.  
// * Find out the ID of the checker that joined the subgroups, global variable (group_combining_checker_u, group_combining_checker_v).
// * Create group_to_pare, having only that group.
// * For each checker in group_to_pare...
//      - Create group_to_pare_copy 
//      - Remove the checker 
//      - See if the removal of the checker divided the group into 2 or 3 subgroups
//      - If not, add that checker to $removable_checkers array 
//
function getRemovableCheckers ( )
{
    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( '### getRemovableCheckers ( ) ###' ), 
    array(
    ) );
    */

    $removable_checkers = array ( );

    $board = self::getBoard ();

    //
    // Each occupied cell will have a group ID for the group it's part of
    //
    $group_IDs = array (); 

    //
    // Subset of $group_IDs which only has the group to be pared down
    //
    $group_to_pare = array (); 

    //
    // Copy of $group_to_pare, used to check for removable checkers
    //
    $group_to_pare_copy = array (); 


	//Get the board size
    $N_option = self::getGameStateValue ("board_size");

    if (  $N_option == 3  )         // $N_option = 3 - board size 3 - small
        $N = 3;
    else if (  $N_option == 5  )    // $N_option = 5 - board size 3x3x5 - medium
        $N = 5;
    else                            // $N_option = 40 - board size 4 - large    ... option 40 not typo
        $N = 4;

    // Set fresh_group_ID to 0
    self::setGameStateValue ( "fresh_group_ID", 0 );

     
    switch ($N)
    {
        case 3:
            //
            // Fill in group_IDs 
            //
            for ( $v = 0; $v < 5; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (2 - $v, 0); $u <= min (6 - $v, 4); $u++ )  // Left to right within hexagonal board
                {
                    if (    isset ( $board [ $u ] [ $v ] )    ) // Current cell is occupied
                    {
                        if (    self::isCellOccupied ( $u - 1, $v, $board, 3 )    )         // If cell to left of current cell is occupied...
                        {
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u - 1 ] [ $v ];        // ...copy its group ID to current cell

                            if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 3 )    ) // If cell to lower right of current cell is ALSO occupied...
                            {
                                $current_group_ID = $group_IDs [ $u ] [ $v ];               // ...replace all occurrences of current group ID with lower right group ID
                                $LR_group_ID = $group_IDs [ $u + 1 ] [ $v - 1 ];
                                
                                foreach ($group_IDs as &$group_IDs_row)         
                                {
                                    foreach ($group_IDs_row as &$group_ID)         
                                    {
                                        if ( $group_ID == $current_group_ID )
                                             $group_ID = $LR_group_ID;
                                    }
                                }
                                unset($group_IDs_row); // break the reference with the last element    
                                unset($group_ID); // break the reference with the last element    
                            }
                        }
                        else if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 3 )    )    // If cell to lower right of current cell is occupied...
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u + 1 ] [ $v - 1 ];        // ...copy its group ID to current cell

                        else if (    self::isCellOccupied ( $u, $v - 1, $board, 3 )    )        // If cell to lower left of current cell is occupied...
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u ] [ $v - 1 ];            // ...copy its group ID to current cell

                        else
                        {
                            $group_IDs [ $u ] [ $v ] = self::getGameStateValue ( "fresh_group_ID" );        // Give the current cell a fresh group ID
                            
                            self::incGameStateValue ( "fresh_group_ID", 1 );
                        }

                        /*
                        self::notifyAllPlayers( "backendMessage", clienttranslate( '$group_IDs [ ${var_1} ] [ ${var_2} ] = ${var_3}' ), 
                        array(
                                "var_1" => $u,
                                "var_2" => $v,
                                "var_3" => $group_IDs [ $u ] [ $v ]

                        ) );
                        */
                    }
                }
            }


            $group_combining_checker_u = self::getGameStateValue("group_combining_checker_u");
            $group_combining_checker_v = self::getGameStateValue("group_combining_checker_v");

            $group_to_pare_ID = $group_IDs [ $group_combining_checker_u ] [ $group_combining_checker_v ];

            //
            // Fill in $group_to_pare from group_IDs using only the group with the ID of the combining checker 
            //
            for ( $v = 0; $v < 5; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (2 - $v, 0); $u <= min (6 - $v, 4); $u++ )  // Left to right within hexagonal board
                {
                    if (     isset ( $group_IDs [ $u ] [ $v ] )    )
                    {
                        if ( $group_IDs [ $u ] [ $v ] == $group_to_pare_ID )
                        {
                            /*
                            self::notifyAllPlayers( "backendMessage", clienttranslate( 'group to pare checker: ${var_1}, ${var_2}' ), 
                            array(
                                "var_1" => $u,
                                "var_2" => $v
                            ) );
                            */


                            $group_to_pare [ $u ] [ $v ] = $group_IDs [ $u ] [ $v ];
                        }
                        else 
                        {
                            $group_to_pare [ $u ] [ $v ] = NULL;
                        }
                    }
                    else 
                    {
                        $group_to_pare [ $u ] [ $v ] = NULL;
                    }
                }
            }

            
            /*
            for ( $v = 0; $v < 5; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (2 - $v, 0); $u <= min (6 - $v, 4); $u++ )  // Left to right within hexagonal board
                {
                        self::notifyAllPlayers( "backendMessage", clienttranslate( '$group_to_pare [ ${var_1} ] [ ${var_2} ] = ${var_3}' ), 
                        array(
                            "var_1" => $u,
                            "var_2" => $v,
                            "var_3" => $group_to_pare [ $u ] [ $v ] ?? 999
                        ) );
                }
            }
            */

            //
            // For each checker in $group_to_pare, make a copy of $group_to_pare WITHOUT that checker, and see if 
            // $group_to_pare_copy was split into 2 or 3 groups by that checker's removal.  If it's still one solid group, 
            // add ( $u, $v ) to $removable_checkers.
            //
            for ( $v = 0; $v < 5; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (2 - $v, 0); $u <= min (6 - $v, 4); $u++ )  // Left to right within hexagonal board
                {
                    
                    if (    isset ( $group_to_pare [ $u ] [ $v ] )    )
                    {
                
                        $group_to_pare_copy = $group_to_pare;

                        $group_to_pare_copy  [ $u ] [ $v ] = NULL;


                        /*
                        for ( $v_value = 0; $v_value < 5; $v_value++ ) // Start on bottom row.  $v = 0
                        {
                            for( $u_value = max (2 - $v_value, 0); $u_value <= min (6 - $v_value, 4); $u_value++ )  // Left to right within hexagonal board
                            {
                                self::notifyAllPlayers( "backendMessage", clienttranslate( '$group_to_pare_copy [ ${var_1} ] [ ${var_2} ] = ${var_3}' ), 
                                array(
                                    "var_1" => $u_value,
                                    "var_2" => $v_value,
                                    "var_3" => $group_to_pare_copy [ $u_value ] [ $v_value ] ?? 999
                                ) );
                            }
                        }

                        self::notifyAllPlayers( "backendMessage", clienttranslate( 'count of groups in group to pare copy = ${var_1}' ), 
                        array(
                                "var_1" => self::countOfGroups ( $group_to_pare_copy )
                        ) );
                        */
 
                        if (    self::countOfGroups ( $group_to_pare_copy ) == 1    )
                        {
                            /*
                            self::notifyAllPlayers( "backendMessage", clienttranslate( '$removable checker: ${var_1}, ${var_2}' ), 
                            array(
                                "var_1" => $u,
                                "var_2" => $v
                            ) );
                            */

                            $removableCheckers [ $u ] [ $v ] = true;
                        }
                        else 
                        {
                            /*
                            self::notifyAllPlayers( "backendMessage", clienttranslate( 'count of groups in group to pare copy = ${var_1}' ), 
                            array(
                                "var_1" => self::countOfGroups ( $group_to_pare_copy )
                            ) );
                            */                       
                        }
                    }
                }
            }

            return $removableCheckers;


        case 5:
            //
            // Fill in group_IDs 
            //
            for ( $v = 0; $v < 5; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (2 - $v, 0); $u <= min (8 - $v, 6); $u++ )  // Left to right within hexagonal board
                {
                    if (    isset ( $board [ $u ] [ $v ] )    ) // Current cell is occupied
                    {
                        if (    self::isCellOccupied ( $u - 1, $v, $board, 5 )    )         // If cell to left of current cell is occupied...
                        {
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u - 1 ] [ $v ];        // ...copy its group ID to current cell

                            if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 5 )    ) // If cell to lower right of current cell is ALSO occupied...
                            {
                                $current_group_ID = $group_IDs [ $u ] [ $v ];               // ...replace all occurrences of current group ID with lower right group ID
                                $LR_group_ID = $group_IDs [ $u + 1 ] [ $v - 1 ];
                                
                                foreach ($group_IDs as &$group_IDs_row)         
                                {
                                    foreach ($group_IDs_row as &$group_ID)         
                                    {
                                        if ( $group_ID == $current_group_ID )
                                             $group_ID = $LR_group_ID;
                                    }
                                }
                                unset($group_IDs_row); // break the reference with the last element    
                                unset($group_ID); // break the reference with the last element    
                            }
                        }
                        else if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 5 )    )    // If cell to lower right of current cell is occupied...
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u + 1 ] [ $v - 1 ];        // ...copy its group ID to current cell

                        else if (    self::isCellOccupied ( $u, $v - 1, $board, 5 )    )        // If cell to lower left of current cell is occupied...
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u ] [ $v - 1 ];            // ...copy its group ID to current cell

                        else
                        {
                            $group_IDs [ $u ] [ $v ] = self::getGameStateValue ( "fresh_group_ID" );        // Give the current cell a fresh group ID
                            
                            self::incGameStateValue ( "fresh_group_ID", 1 );
                        }

                        /*
                        self::notifyAllPlayers( "backendMessage", clienttranslate( '$group_IDs [ ${var_1} ] [ ${var_2} ] = ${var_3}' ), 
                        array(
                                "var_1" => $u,
                                "var_2" => $v,
                                "var_3" => $group_IDs [ $u ] [ $v ]

                        ) );
                        */
                    }
                }
            }


            $group_combining_checker_u = self::getGameStateValue("group_combining_checker_u");
            $group_combining_checker_v = self::getGameStateValue("group_combining_checker_v");

            $group_to_pare_ID = $group_IDs [ $group_combining_checker_u ] [ $group_combining_checker_v ];

            //
            // Fill in $group_to_pare from group_IDs using only the group with the ID of the combining checker 
            //
            for ( $v = 0; $v < 5; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (2 - $v, 0); $u <= min (8 - $v, 6); $u++ )  // Left to right within hexagonal board
                {
                    if (     isset ( $group_IDs [ $u ] [ $v ] )    )
                    {
                        if ( $group_IDs [ $u ] [ $v ] == $group_to_pare_ID )
                        {
                            /*
                            self::notifyAllPlayers( "backendMessage", clienttranslate( 'group to pare checker: ${var_1}, ${var_2}' ), 
                            array(
                                "var_1" => $u,
                                "var_2" => $v
                            ) );
                            */


                            $group_to_pare [ $u ] [ $v ] = $group_IDs [ $u ] [ $v ];
                        }
                        else 
                        {
                            $group_to_pare [ $u ] [ $v ] = NULL;
                        }
                    }
                    else 
                    {
                        $group_to_pare [ $u ] [ $v ] = NULL;
                    }
                }
            }

            
            /*
            for ( $v = 0; $v < 5; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (2 - $v, 0); $u <= min (6 - $v, 4); $u++ )  // Left to right within hexagonal board
                {
                        self::notifyAllPlayers( "backendMessage", clienttranslate( '$group_to_pare [ ${var_1} ] [ ${var_2} ] = ${var_3}' ), 
                        array(
                            "var_1" => $u,
                            "var_2" => $v,
                            "var_3" => $group_to_pare [ $u ] [ $v ] ?? 999
                        ) );
                }
            }
            */

            //
            // For each checker in $group_to_pare, make a copy of $group_to_pare WITHOUT that checker, and see if 
            // $group_to_pare_copy was split into 2 or 3 groups by that checker's removal.  If it's still one solid group, 
            // add ( $u, $v ) to $removable_checkers.
            //
            for ( $v = 0; $v < 5; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (2 - $v, 0); $u <= min (8 - $v, 6); $u++ )  // Left to right within hexagonal board
                {
                    
                    if (    isset ( $group_to_pare [ $u ] [ $v ] )    )
                    {
                
                        $group_to_pare_copy = $group_to_pare;

                        $group_to_pare_copy  [ $u ] [ $v ] = NULL;


                        /*
                        for ( $v_value = 0; $v_value < 5; $v_value++ ) // Start on bottom row.  $v = 0
                        {
                            for( $u_value = max (2 - $v_value, 0); $u_value <= min (6 - $v_value, 4); $u_value++ )  // Left to right within hexagonal board
                            {
                                self::notifyAllPlayers( "backendMessage", clienttranslate( '$group_to_pare_copy [ ${var_1} ] [ ${var_2} ] = ${var_3}' ), 
                                array(
                                    "var_1" => $u_value,
                                    "var_2" => $v_value,
                                    "var_3" => $group_to_pare_copy [ $u_value ] [ $v_value ] ?? 999
                                ) );
                            }
                        }

                        self::notifyAllPlayers( "backendMessage", clienttranslate( 'count of groups in group to pare copy = ${var_1}' ), 
                        array(
                                "var_1" => self::countOfGroups ( $group_to_pare_copy )
                        ) );
                        */
 
                        if (    self::countOfGroups ( $group_to_pare_copy ) == 1    )
                        {
                            /*
                            self::notifyAllPlayers( "backendMessage", clienttranslate( '$removable checker: ${var_1}, ${var_2}' ), 
                            array(
                                "var_1" => $u,
                                "var_2" => $v
                            ) );
                            */

                            $removableCheckers [ $u ] [ $v ] = true;
                        }
                        else 
                        {
                            /*
                            self::notifyAllPlayers( "backendMessage", clienttranslate( 'count of groups in group to pare copy = ${var_1}' ), 
                            array(
                                "var_1" => self::countOfGroups ( $group_to_pare_copy )
                            ) );
                            */                       
                        }
                    }
                }
            }

            return $removableCheckers;


        case 4:                         // size 4 board - large
            //
            // Fill in group_IDs 
            //
            for ( $v = 0; $v < 7; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (3 - $v, 0); $u <= min (9 - $v, 6); $u++ )  // Left to right within hexagonal board
                {
                    if (    isset ( $board [ $u ] [ $v ] )    ) // Current cell is occupied
                    {
                        if (    self::isCellOccupied ( $u - 1, $v, $board, 4 )    )         // If cell to left of current cell is occupied...
                        {
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u - 1 ] [ $v ];        // ...copy its group ID to current cell

                            if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 4 )    ) // If cell to lower right of current cell is ALSO occupied...
                            {
                                $current_group_ID = $group_IDs [ $u ] [ $v ];               // ...replace all occurrences of current group ID with lower right group ID
                                $LR_group_ID = $group_IDs [ $u + 1 ] [ $v - 1 ];
                                
                                foreach ($group_IDs as &$group_IDs_row)         
                                {
                                    foreach ($group_IDs_row as &$group_ID)         
                                    {
                                        if ( $group_ID == $current_group_ID )
                                             $group_ID = $LR_group_ID;
                                    }
                                }
                                unset($group_IDs_row); // break the reference with the last element    
                                unset($group_ID); // break the reference with the last element    
                            }
                        }
                        else if (    self::isCellOccupied ( $u + 1, $v - 1, $board, 4 )    )    // If cell to lower right of current cell is occupied...
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u + 1 ] [ $v - 1 ];        // ...copy its group ID to current cell

                        else if (    self::isCellOccupied ( $u, $v - 1, $board, 4 )    )        // If cell to lower left of current cell is occupied...
                            $group_IDs [ $u ] [ $v ] = $group_IDs [ $u ] [ $v - 1 ];            // ...copy its group ID to current cell

                        else
                        {
                            $group_IDs [ $u ] [ $v ] = self::getGameStateValue ( "fresh_group_ID" );        // Give the current cell a fresh group ID
                            
                            self::incGameStateValue ( "fresh_group_ID", 1 );
                        }

                     }
                }
            }


            $group_combining_checker_u = self::getGameStateValue("group_combining_checker_u");
            $group_combining_checker_v = self::getGameStateValue("group_combining_checker_v");

            $group_to_pare_ID = $group_IDs [ $group_combining_checker_u ] [ $group_combining_checker_v ];

            //
            // Fill in $group_to_pare from group_IDs using only the group with the ID of the combining checker 
            //
            for ( $v = 0; $v < 7; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (3 - $v, 0); $u <= min (9 - $v, 6); $u++ )  // Left to right within hexagonal board
                {
                    if (     isset ( $group_IDs [ $u ] [ $v ] )    )
                    {
                        if ( $group_IDs [ $u ] [ $v ] == $group_to_pare_ID )
                        {
                            $group_to_pare [ $u ] [ $v ] = $group_IDs [ $u ] [ $v ];
                        }
                        else 
                        {
                            $group_to_pare [ $u ] [ $v ] = NULL;
                        }
                    }
                    else 
                    {
                        $group_to_pare [ $u ] [ $v ] = NULL;
                    }
                }
            }


            //
            // For each checker in $group_to_pare, make a copy of $group_to_pare WITHOUT that checker, and see if 
            // $group_to_pare_copy was split into 2 or 3 groups by that checker's removal.  If it's still one solid group, 
            // add ( $u, $v ) to $removable_checkers.
            //
            for ( $v = 0; $v < 7; $v++ ) // Start on bottom row.  $v = 0
            {
                for( $u = max (3 - $v, 0); $u <= min (9 - $v, 6); $u++ )  // Left to right within hexagonal board
                {
                    
                    if (    isset ( $group_to_pare [ $u ] [ $v ] )    )
                    {
                
                        $group_to_pare_copy = $group_to_pare;

                        $group_to_pare_copy  [ $u ] [ $v ] = NULL;

 
                        if (    self::countOfGroups ( $group_to_pare_copy ) == 1    )
                        {
                            $removableCheckers [ $u ] [ $v ] = true;
                        }
                        else 
                        {
                        }
                    }
                }
            }

            return $removableCheckers;

    }
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
        if ($color=='ff0000')
            return clienttranslate('RED');
        if ($color=='0000ff')
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
        case 3:  // size 3 - small board
            if (    (  $u >= 0 && $u < 3 && $v >= 2 - $u && $v < 5 )             // Cell on board
                 || (  $u >= 3 && $u < 5 && $v >= 0 && $v <= 6 - $u  )    )
                 return true;
            else 
                 return false;

        case 5: // 3x3x5 - medium board
            if (    (  $u >= 0 && $u < 3 && $v >= 2 - $u && $v < 5 )             // Cell on board
                 || (  ($u==3 || $u==4) && $v >= 0 && $v <= 4  )
                 || (  $u >= 5 && $u < 7 && $v >= 0 && $v <= 8 - $u  )    )
                 return true;
            else 
                 return false;

        case 4:  // size 4 - large board
            if (    (  $u >= 0 && $u < 4 && $v >= 3 - $u && $v < 7 )             // Cell on board
                 || (  $u >= 4 && $u < 7 && $v >= 0 && $v <= 9 - $u  )    )
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
    /*
    $N=self::getGameStateValue("board_size");

    if (  $N == 3  )
        $total_cells = 19;
    else    // $N = 5
        $total_cells = 29;
    */

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


        $were_groups_combined = false;


        $legal_moves = self::getSelectableCells ();

        if (    $legal_moves [ $u ] [ $v ] == true    ) 
        {
            //
            // If this placement combines groups, note such in global variables
            //
            $were_groups_combined = self::doesCombineGroups ( $u, $v );  

            if ( $were_groups_combined )
            {
                //
                // Set global variables 
                //      group_combining_checker_u
                //      group_combining_checker_v
                //      pare_down_group_size_current
                //      pare_down_group_size_goal
                //
                self::evaluateAdjacentGroups ( $u, $v );
            }


            $active_player_id = self::getActivePlayerId(); 
            
            $active_player_color = self::getPlayerColor ( $active_player_id );


            if (    $active_player_color == "ff0000"    )
            {
                $checker_id_value = self::getGameStateValue( "red_checker_id" );
                self::incGameStateValue( "red_checker_id", 1 );

                self::incGameStateValue( "total_red_checkers", 1 );
            }
            else 
            {
                $checker_id_value = self::getGameStateValue( "blue_checker_id" );
                self::incGameStateValue( "blue_checker_id", 1 );

                self::incGameStateValue( "total_blue_checkers", 1 );
            }


            $sql = "UPDATE board SET board_player = $active_player_id, checker_id = $checker_id_value
                    WHERE ( board_u, board_v) = ($u, $v)";  
    
            self::DbQuery( $sql );
    
    
            //
            // Notify
            //
            self::notifyAllPlayers( "checkerPlaced", clienttranslate( '' ), 
                array(
                'u' => $u,
                'v' => $v,
                'player_id' => $active_player_id,
                'checker_id' => $checker_id_value
                ) );

    
            //
            // Update the playerPanel to display the players' number of placed checkers
            //
            $total_red_checkers = self::getGameStateValue ( "total_red_checkers");
            $total_red_checkers_str = "{$total_red_checkers}";

            $total_blue_checkers = self::getGameStateValue ( "total_blue_checkers");
            $total_blue_checkers_str = "{$total_blue_checkers}";

            if (    $active_player_color == "ff0000"   )
            {
                self::notifyAllPlayers("playerPanel",
                    "",
                    array(
                    "placed_checkers" => $total_red_checkers_str,
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
                    "placed_checkers" => $total_red_checkers_str,
                    "player_id" => self::getOtherPlayerId($active_player_id) ));
            }


            $u_display_coord = chr( ord( "A" ) + $u );
            $v_display_coord = $v + 1;

            self::notifyAllPlayers( "checkerPlacedHistory", clienttranslate( '${active_player_name} 
                +${u_display_coord}${v_display_coord}' ),     
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
		    if( self::getGameStateValue( "move_number" )==0 ) //0 since the variable is incremented when the move has been validated
            {
			    //Increment number of moves
			    self::incGameStateValue( "move_number", 1 );
				
			    // Go to the choice state
			    $this->gamestate->nextState( 'firstMoveChoice' );
		    }
            else 
            {
                if ( $were_groups_combined )
                    $this->gamestate->nextState( 'placeGroupingChecker' );
                else 
                    $this->gamestate->nextState( 'placeNonGroupingChecker' );
            }

        }
        else
            throw new feException( "Cell is not selectable." );
    }
    







    
    function removeChecker ($u, $v)
    {
        self::checkAction( 'removeChecker' );  
    
        /*
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'removeChecker' ), 
        array(            
            ) );
        */

        $board = self::getBoard();

        $active_player_id = self::getActivePlayerId(); 
        $active_player_color = self::getPlayerColor ( $active_player_id );

        $inactive_player_id = self::getOtherPlayerId ( $active_player_id );


        $legal_moves = self::getRemovableCheckers ();


        if (    $legal_moves [ $u ] [ $v ] == true    ) 
        {

            //
            // Find ID of checker to be removed
            //
            $sql = "SELECT board_u, board_v, checker_id from board WHERE ( board_u, board_v) = ($u, $v) ";       
            $result = self::DbQuery( $sql );
            $row = $result -> fetch_assoc();
        
            $removed_checker_id = $row ["checker_id"];
    

            //
            // Delete the checker to be removed 
            //
            $sql = "UPDATE board SET board_player = NULL, checker_id = NULL
                    WHERE ( board_u, board_v) = ($u, $v)";  
            self::DbQuery( $sql );


            //
            // Notify players about removed checker.  
            //
            // Checker should fly to panel of its own color.
            //
            $active_player_color = self::getPlayerColor ( $active_player_id );
                    
            if ( $active_player_color == "ff0000" )
            {
                if ( $removed_checker_id < 2000 ) // Checker is red
                {
                    $panel_player_id = $active_player_id;
                }
                else 
                {
                    $panel_player_id = $inactive_player_id;
                }
            }
            else 
            {
                if ( $removed_checker_id < 2000 ) // Checker is red
                {
                    $panel_player_id = $inactive_player_id;
                }
                else 
                {
                    $panel_player_id = $active_player_id;
                }
            }

            self::notifyAllPlayers( "checkerRemoved", clienttranslate( '' ), 
                array(
                'removed_checker_id' => $removed_checker_id,
                'player_id' => $panel_player_id
            ) );


            //
            // Decrement total checkers of appropriate color 
            //
            if ( $removed_checker_id < 2000 )
                self::incGameStateValue( "total_red_checkers", -1 );
            else 
                self::incGameStateValue( "total_blue_checkers", -1 );

            //
            // Update the playerPanel to display the players' number of placed checkers
            //
            $total_red_checkers = self::getGameStateValue ( "total_red_checkers");
            $total_red_checkers_str = "{$total_red_checkers}";

            $total_blue_checkers = self::getGameStateValue ( "total_blue_checkers");
            $total_blue_checkers_str = "{$total_blue_checkers}";

            if (    $active_player_color == "ff0000"   )
            {
                self::notifyAllPlayers("playerPanel",
                    "",
                    array(
                    "placed_checkers" => $total_red_checkers_str,
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
                    "placed_checkers" => $total_red_checkers_str,
                    "player_id" => self::getOtherPlayerId($active_player_id) ));
            }


            $u_display_coord = chr( ord( "A" ) + $u );
            $v_display_coord = $v + 1;

            self::notifyAllPlayers( "checkerRemovedHistory", clienttranslate( '${active_player_name} 
                -${u_display_coord}${v_display_coord}' ),     
                array(
                    'active_player_name' => self::getActivePlayerName(),
                    'u_display_coord' => $u_display_coord,
                    'v_display_coord' => $v_display_coord
                ) );



            //
            // Decerement combined group size 
            //
            self::incGameStateValue ( "pare_down_group_size_current", -1);


            //
            // Determine the next state
            //
            $pare_down_group_size_current = self::getGameStateValue ( "pare_down_group_size_current");
            $pare_down_group_size_goal = self::getGameStateValue ( "pare_down_group_size_goal");

            if ( $pare_down_group_size_current == $pare_down_group_size_goal )
            {
                $this->gamestate->nextState( 'removeLastChecker' );
            }
            else 
            {
                $this->gamestate->nextState( 'removeNotLastChecker' );
            }
        }
        else
            throw new feException( "Checker is not removable." );
    
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


    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

function argPlaceChecker()
{
    return array(
        'selectableCells' => self::getSelectableCells ( )
    );
}

function argRemoveChecker ( )
{
    return array(
        'removableCheckers' => self::getRemovableCheckers ( )
    );
}



//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

function stNextPlayer()
{   
    // Activate next player
    $active_player_id = self::activeNextPlayer();

	$inactive_player_id = self::getOtherPlayerId ( $active_player_id );

    $inactive_player_color = self::getPlayerColor ( $inactive_player_id );

	//Get the board size
    $N_option = self::getGameStateValue( "board_size" );
            
    if (  $N_option == 3  )         // board size 3 - small
        $board_size = 19;
    else if (  $N_option == 5  )    // board size 3x3x5 - medium
        $board_size = 29;
    else                            // board size 4 - large ... ($N_option = 40)
        $board_size = 37;           
            

    $total_red_checkers = self::getGameStateValue ( "total_red_checkers");
    $total_blue_checkers = self::getGameStateValue ( "total_blue_checkers");

    $total_checkers = $total_red_checkers + $total_blue_checkers;

    
    //
    // If the board is filled with checkers, end the game
    //
    if (    $total_checkers == $board_size    )
    {
        if ( $total_red_checkers > $total_blue_checkers ) // Red wins
        {
            if ( $inactive_player_color == "ff0000") // Red
            {
                $sql = "UPDATE player
                        SET player_score = 1 WHERE player_id = $inactive_player_id";
            }
            else 
            {
                $sql = "UPDATE player
                        SET player_score = 1 WHERE player_id = $active_player_id";
            }
        }
        else // Blue wins
        {
            if ( $inactive_player_color == "0000ff") // Blue
            {
                $sql = "UPDATE player
                        SET player_score = 1 WHERE player_id = $inactive_player_id";
            }
            else 
            {
                $sql = "UPDATE player
                        SET player_score = 1 WHERE player_id = $active_player_id";
            }
        }

        self::DbQuery( $sql );
    

        $newScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );

        self::notifyAllPlayers( "newScores", "", array(
            "scores" => $newScores
        ) );

        $this->gamestate->nextState( 'endGame' );
    }
    else 
    {
        self::giveExtraTime( $active_player_id );   
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
