<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * Icebreaker implementation : © <Mark Steere> <marksteere@gmail.com>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * icebreaker.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class Icebreaker extends Table
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
            "move_number" => 11, //Counter of the number of moves (used to detect first move)
            "reds_captured_icebergs" => 20, //Counter of the number of moves (used to detect first move)
            "blacks_captured_icebergs" => 21, //Counter of the number of moves (used to detect first move)
            "board_size" => 101, //The size of the board
            ) );        

	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "icebreaker";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {    
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

            if( $color == 'ff0000' )
                $red_player_id = $player_id;
            else
                $black_player_id = $player_id;
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );

        $sql = "UPDATE player
                SET player_score = 0 WHERE 1";
                
        self::DbQuery( $sql );

        self::reloadPlayersBasicInfos();

        $N=self::getGameStateValue("board_size");

        
        //
        // Initialize the board 
        //
        $white_checker_id = 0;      // white - 0 to 999
        $red_checker_id = 1000;        // red - 1000 to 1999
        $black_checker_id = 2000;    // black - 2000 to 2999

        $sql = "INSERT INTO board (board_u, board_v, board_player, checker_id, is_selected) VALUES ";

        $sql_values = array();

        for( $u=0; $u<2*$N-1; $u++ )
        {
            for( $v=0; $v<2*$N-1; $v++ )
            {
                if (    self::isCellOnBoard($u, $v, $N)    )
                {
                    $player_id_value = "NULL";

                    if ($u == 0)
                    {
                        if ($v == $N - 1)
                        {
                            $player_id_value = $black_player_id;
                            $checker_id_value = $black_checker_id++;
                        }
                        else if ($v == 2*$N - 2)
                        {
                            $player_id_value = $red_player_id;
                            $checker_id_value = $red_checker_id++;
                        }
                        else
                        {
                            $player_id_value = 0;  // Iceberg = 0                           
                            $checker_id_value = $white_checker_id++;
                        }
                    }
                    else if ($u == $N - 1)
                    {
                        if ($v == 0)
                        {
                            $player_id_value = $red_player_id;
                            $checker_id_value = $red_checker_id++;
                        }
                        else if ($v == 2*$N - 2)
                        {
                            $player_id_value = $black_player_id;
                            $checker_id_value = $black_checker_id++;
                        }
                        else
                        {
                            $player_id_value = 0;  // Iceberg = 0                           
                            $checker_id_value = $white_checker_id++;
                        }
                    }
                    else if ($u == 2*$N - 2)
                    {
                        if ($v == 0)
                        {
                            $player_id_value = $black_player_id;
                            $checker_id_value = $black_checker_id++;
                        }
                        else if ($v == $N - 1)
                        {
                             $player_id_value = $red_player_id;
                             $checker_id_value = $red_checker_id++;
                       }
                        else
                        {
                            $player_id_value = 0;  // Iceberg = 0                           
                            $checker_id_value = $white_checker_id++;
                        }
                    }
                    else
                    {
                        $player_id_value = 0;  // Iceberg = 0
                        $checker_id_value = $white_checker_id++;
                    }


                    $sql_values[] = "($u, $v, $player_id_value, $checker_id_value, 0)";                   
                }
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
    
        //$current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
    
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );

  
        //
        // Get board information
        // 
        $sql = "SELECT board_u u, board_v v, board_player player, checker_id ch_id, is_selected is_sel
                FROM board
                WHERE board_player IS NOT NULL";
        $result['board'] = self::getObjectListFromDB( $sql );


		//
        // Get  board size
        //
        $N=self::getGameStateValue("board_size");
		$result['board_size'] = $N;


        //
        // Captured checkers
        //
        $activePlayerId = self::getActivePlayerId();

        $otherPlayerId = self::getOtherPlayerId($activePlayerId);


        $active_player_color = self::getPlayerColor ( $activePlayerId );


        $capturedCheckers = array();

        if (    $active_player_color == "ff0000"    )
        {
            $capturedCheckers[$activePlayerId] = self::getGameStateValue( "reds_captured_icebergs" );
            $capturedCheckers[$otherPlayerId] = self::getGameStateValue( "blacks_captured_icebergs" );
        }
        else
        {
            $capturedCheckers[$activePlayerId] = self::getGameStateValue( "blacks_captured_icebergs" );
            $capturedCheckers[$otherPlayerId] = self::getGameStateValue( "reds_captured_icebergs" );
        }

        
        $result['capturedCheckers'] = $capturedCheckers;
        

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
        // TODO: compute and return the game progression

		//Get the board size
        $N=self::getGameStateValue("board_size");
            
        if (  $N == 5  )
            $majority_icebergs = 28;
        else if (  $N == 6  )
            $majority_icebergs = 43;
        else if (  $N == 7  )
            $majority_icebergs = 61;
        else    // $N = 8
            $majority_icebergs = 82;


        $most_captured_checkers = max (    self::getGameStateValue( "reds_captured_icebergs" ), 
                                           self::getGameStateValue( "blacks_captured_icebergs" )    );

        return (    $most_captured_checkers / $majority_icebergs * 100    );
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    


function hasMoves ( $u, $v, $board, $active_player_id )
{    
    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( 'hasMoves function.' ), 
    array(
        
        ) ); 
    */   

	//Get the board size
    $N=self::getGameStateValue("board_size");

    $directions = array( array( -1, 0 ), array( -1, 1 ), array( 0, 1 ), 
                         array( 1, 0 ), array( 1, -1), array( 0, -1 ) );
        
    foreach( $directions as $direction )
    {
        $neighbor_u = $u + $direction[0];
        $neighbor_v = $v + $direction[1];

        if (    self::isCellOnBoard ( $neighbor_u, $neighbor_v, $N )    )
        {
            if (    ( $board[ $neighbor_u ][ $neighbor_v ] == 0 )
                || ( $board[ $neighbor_u ][ $neighbor_v ] == NULL )    )
                        return true;
        }
    }
 
    return false;
}
            





function getSelectableCheckers( $active_player_id )
{
    $result = array();

    $board = self::getBoard();

	//Get the board size
    $N=self::getGameStateValue("board_size");
     

    for( $u=0; $u<2*$N-1; $u++ )
    {
        for( $v=0; $v<2*$N-1; $v++ )
        {
            if (    self::isCellOnBoard($u, $v, $N)    )
            {
                if ( $board [ $u ] [ $v ] == $active_player_id )
                {
                    if ( self::hasMoves ( $u, $v, $board, $active_player_id ) )
                    {
                        if( ! isset( $result[$u] ) )
                        $result[$u] = array();

                        $result[$u][$v] = true;
                    }
                }
            }
        }
    }


     return $result;
}




    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( '($u, $v) to string = ${var_1}' ), 
    array(
             "var_1" => $cells[$u."_".$v]["path_length"]
    ) );
    */


function getSelCheckerDestinationsDijkstra ( $u, $v, $board )
{
    //
    // Return array of selectable destinations.  Selectable destinations are cells which bring the specified ship ($u, $v) 
    // one cell closer to its nearest iceberg.
    //
    
    $board = self::getBoard();

    $boardSelectedCheckers = self::getBoardSelectedCheckers();  // Should just be one

    $N=self::getGameStateValue("board_size");

    //
    // cells array  
    //
    //      Has specified ship cell with path_length = 0.  All other cells (except other ships) have 
    //      path_length initially set to 1000.
    //
    //
    $cells = array ();
    
    $cell = array (   "path_length" => 0,               // Path length is 0 only for starting cell.  All other cells have path_length of 1 or more.
                      "parents" => array (),            // No parents for starting cell.
                      "branch_out_eval" => false   );   // Have all surrounding neighbors been visited?

 
    $cells [ $u."_".$v ] = $cell;   // ($u, $v) to string key

   
    //
    // Initialize all other non-ship cells with path_length 1000
    //
    for( $u_index=0; $u_index<2*$N-1; $u_index++ )
    {
        for( $v_index=0; $v_index<2*$N-1; $v_index++ )
        {
            if (    self::isCellOnBoard ($u_index, $v_index, $N)    )
            {
                if (    $board [ $u_index ] [ $v_index ] == 0    )   // Iceberg or unoccupied.  0 or NULL.  Php == operator evaluates NULL as zero.
                {
                    $cell = array (    "path_length" => 1000,               
                                       "parents" => array (),
                                       "branch_out_eval" => false,  
                                       "iceberg_backtrack" => false    );  

                    $cells [ $u_index."_".$v_index] = $cell;

                }
            }
        }
    }



    $distance = 0;

    $reached_icebergs = false;

    //
    // For all cells with path_length = $distance, evaluate surrounding cells.  
    //
    // If a surrounding cell has a higher path_length than $distance + 1, set its path_length to $distance + 1, clear its parents, and add the evaluating 
    // cell to its parents array.
    //
    // If a surrounding cell has a path_length equal to $distance + 1, just add the evaluating cell to the parents array.
    //

    //
    // Branch out portion
    //
    while ( $reached_icebergs == false ) 
    {
        for( $u_index=0; $u_index<2*$N-1; $u_index++ )
        {
            for( $v_index=0; $v_index<2*$N-1; $v_index++ )
            {
                if (    isset ( $cells [ $u_index."_".$v_index ] )    ) // cell is in the $cells array, and therefore is on the board
                {
                    if ( $cells [ $u_index."_".$v_index ] ["path_length"] == $distance )
                    {
                        $directions = array(    array( -1, 0 ), array( -1, 1 ), array( 0, 1 ), 
                                                array( 1, 0 ), array( 1, -1), array( 0, -1 )    );

                        foreach( $directions as $direction )
                        {
                            $neighbor_u = $u_index + $direction[0];
                            $neighbor_v = $v_index + $direction[1];

                            if (    isset ( $cells [ $neighbor_u."_".$neighbor_v ] )        // neighboring cell is in the $cells array, and therefore is on the board
                                        &&  $cells [ $neighbor_u."_".$neighbor_v ] ["branch_out_eval"] == false    )  // Don't visit cells that already completed branch out eval
                            {
                                if (    $board [ $neighbor_u ] [ $neighbor_v ] == 0    )      // Iceberg or unoccupied.  0 or NULL.  Php == operator evaluates NULL as zero.
                                {
                                    if (   ( $board [ $neighbor_u ] [ $neighbor_v ] == 0 ) && ( $board [ $neighbor_u ] [ $neighbor_v ] != NULL )   )  // Iceberg. 0 but not NULL.
                                    {                                                         
                                        $reached_icebergs = true;

                                        $cells [ $neighbor_u."_".$neighbor_v ] ["iceberg_backtrack"] = true;
                                    }
                                
                                    if (    $cells [ $neighbor_u."_".$neighbor_v] ["path_length"] > $distance + 1    )  // Heart of Dijkstra's algorithm
                                    {
                                        $cells [ $neighbor_u."_".$neighbor_v] ["path_length"] = $distance + 1;          // Set path_length to new, lower value

                                        unset (    $cells [ $neighbor_u."_".$neighbor_v] ["parents"]    );              // Clear parents array

                                        $cells [ $neighbor_u."_".$neighbor_v] ["parents"] [] = $u_index."_".$v_index;   // Add evaluating cell to parents array

                                    }
                                    else if (    $cells [ $neighbor_u."_".$neighbor_v] ["path_length"] == $distance + 1    )  
                                    {

                                        $cells [ $neighbor_u."_".$neighbor_v] ["parents"] [ ] = $u_index."_".$v_index;   // Add evaluating cell to parents array

                                    }
                                }
                            }
                        }

                        $cells [ $u_index."_".$v_index ] ["branch_out_eval"] = true; 
                    }
                }

            }
        }
    
        ++$distance;
        
    }


    //
    // Backtrack portion
    //
    while (    $distance > 1    ) 
    {
        for( $u_index=0; $u_index<2*$N-1; $u_index++ )
        {
            for( $v_index=0; $v_index<2*$N-1; $v_index++ )
            {
                if (    isset ( $cells [ $u_index."_".$v_index ] )    ) // cell is in the $cells array, and therefore is on the board
                {
                    if (    $cells [ $u_index."_".$v_index ] ["path_length"] == $distance
                         && $cells [ $u_index."_".$v_index ] ["iceberg_backtrack"] == true    )
                    {
                        foreach (    $cells [ $u_index."_".$v_index ] ["parents"] as $parent    )
                                     $cells [ $parent ] ["iceberg_backtrack"] = true;
                    }
                }
            }
        }

        --$distance;
    }


    //
    // Return the array of backtrack cells at distance 1
    //
    $result = array();

    for( $u_index=0; $u_index<2*$N-1; $u_index++ )
    {
        for( $v_index=0; $v_index<2*$N-1; $v_index++ )
        {
            if (    isset ( $cells [ $u_index."_".$v_index ] )    ) // cell is in the $cells array, and therefore is on the board
            {

                if (    $cells [ $u_index."_".$v_index ] ["path_length"] == 1
                     && $cells [ $u_index."_".$v_index ] ["iceberg_backtrack"] == true    )
                {
                    if(    ! isset( $result[$u_index] )    )
                        $result[$u_index] = array();

                    $result[$u_index][$v_index] = true;            
                }

            }
        }
    }

    return $result;  
 
}



function getSelectableDestinations ( $active_player_id )
{
    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( 'getSelectableDestinations' ), 
        array(
                    
        ) );
    */

    $board = self::getBoard();

    $boardSelectedCheckers = self::getBoardSelectedCheckers();  // Should just be one


	//Get the board size
    $N=self::getGameStateValue("board_size");
            


    for( $u=0; $u<2*$N-1; $u++ )
    {
        for( $v=0; $v<2*$N-1; $v++ )
        {
            if (    self::isCellOnBoard($u, $v, $N)    )
            {
                if (    (  $board [ $u ] [ $v ] == $active_player_id  )
                    &&  (  $boardSelectedCheckers [ $u ] [ $v ] == 1 )    )
                {
                    return (  self::getSelCheckerDestinationsDijkstra ($u, $v, $board)  );
                }
            }
        }
    }
}



function getBoard()
{
    return self::getDoubleKeyCollectionFromDB( "SELECT board_u u, board_v v, board_player player
                                                FROM board", true );
}


function getBoardSelectedCheckers()
{
    return self::getDoubleKeyCollectionFromDB( "SELECT board_u u, board_v v, is_selected is_sel
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
        if ($color=='000000')
            return clienttranslate('BLACK');
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
    if (    (  $u >= 0 && $u < $N && $v >= $N - 1 - $u && $v <= 2*$N - 2 )             // Cell on board  ADD U AND V  GREATER THAN ZERO  #############################################
        || (  $u >= $N && $u <= 2*$N - 2 && $v >= 0 && $v <= 3*$N - $u - 3  )    )
        return true;
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
    $N=self::getGameStateValue("board_size");

    if (  $N == 5  )
        $majority_icebergs = 28;
    else if (  $N == 6  )
        $majority_icebergs = 43;
    else if (  $N == 7  )
        $majority_icebergs = 61;
    else    // $N = 8
        $majority_icebergs = 82;

    $majority_str_0 = "0 / {$majority_icebergs}";
    $majority_str_1 = "1 / {$majority_icebergs}";


	self::notifyAllPlayers( "swapColors", clienttranslate( '${player_name} is now playing <span style="color:#${player_color};">${player_colorname}</span>.' ), array(
		'i18n' => array( 'player_colorname' ),
		'player_id' => $player[0]['id'],
		'player_name' => $player[0]['name'],
		'player_color' => $player[1]['color'],
		'player_colorname' => self::getColorName($player[1]['color']),

        'capturedCheckers' => $majority_str_0
	) );

	self::notifyAllPlayers( "swapColors", clienttranslate( '${player_name} is now playing <span style="color:#${player_color};">${player_colorname}</span>.' ), array(
		'i18n' => array( 'player_colorname' ),
		'player_id' => $player[1]['id'],
		'player_name' => $player[1]['name'],
		'player_color' => $player[0]['color'],
		'player_colorname' => self::getColorName($player[0]['color']),

        'capturedCheckers' => $majority_str_1
	) );

		
	//Update player info
	self::reloadPlayersBasicInfos();

}




//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    function selectChecker( $u, $v )
    {    
        /*
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'selectChecker' ), 
        array(
            
            ) );
        */

        self::checkAction( 'selectChecker' );  
        
        $active_player_id = self::getActivePlayerId(); 
            
        $board = self::getBoard();
    
        // Check if this is a selectable checker
        if ( self::hasMoves ( $u, $v, $board, $active_player_id ) )
        {
            /*               
            self::notifyAllPlayers( "backendMessage", clienttranslate( 'selectChecker - hasMoves' ), 
            array(
                
                ) );
            */

            $sql = "UPDATE board SET is_selected = 1
                    WHERE ( board_u, board_v) = ($u, $v)";  
    
            self::DbQuery( $sql );
    
    
            //
            // Notify
            //
            self::notifyAllPlayers( "checkerSelected", clienttranslate( '' ), 
                array(
                'u' => $u,
                'v' => $v,
                ) );
    
            //
            // Go to the next state
            //
            $this->gamestate->nextState( 'selectChecker' );
                
        }
        else
            throw new feException( "You have not selected a moveable checker." );
    }
    
    
function unselectChecker()
    {
        self::checkAction( 'unselectChecker' );  
        
        $active_player_id = self::getActivePlayerId(); 
            
        $board = self::getBoard();
    
        $sql = "UPDATE board SET is_selected = 0
                WHERE 1";  
    
        self::DbQuery( $sql );
    
        //
        // Notify
        //
        self::notifyAllPlayers( "checkerUnselected", clienttranslate( '' ), 
            array(
            ) );
    
        //
        // Go to the next state
        //
        $this->gamestate->nextState( 'unselectChecker' );
                
    }
    
    
    function selectDestination($u, $v)
    {
        self::checkAction( 'selectDestination' );  
    
        $active_player_id = self::getActivePlayerId(); 
            
    
        //
        // Find ID of moving checker
        //
        $sql = "SELECT board_u, board_v, checker_id from board WHERE is_selected = 1 ";       
        $result = self::DbQuery( $sql );
        $row = $result -> fetch_assoc();
    
        $u_old = $row ["board_u"];
        $v_old = $row ["board_v"];
    
        $checker_id = $row ["checker_id"];
    
    
        //
        // Find ID of checker on destination square, which could be NULL
        //
        $sql = "SELECT board_u, board_v, checker_id from board WHERE ( board_u, board_v) = ($u, $v) ";       
        $result = self::DbQuery( $sql );
        $row = $result -> fetch_assoc();
        
        $captured_checker_id = $row ["checker_id"];
    
    
        //
        // Move the checker
        //
        // Delete old checker
        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL, is_selected = 0
                WHERE ( board_u, board_v) = ($u_old, $v_old)";  
        self::DbQuery( $sql );
    
        // Add new checker
        $sql = "UPDATE board SET board_player = $active_player_id, checker_id = $checker_id, is_selected = 0
            WHERE ( board_u, board_v) = ($u, $v)";  
        self::DbQuery( $sql );
    
    
        //
        // Notify
        //
        self::notifyAllPlayers( "destinationSelected", clienttranslate( '' ), 
            array(
            'checker_id' => $checker_id,
            'u_new' => $u,
            'v_new' => $v,

            'captured_checker_id' => $captured_checker_id,

            'capturing_player_id' => $active_player_id,
        ) );


        $u_old_display_coord = chr( ord( "A" ) + $u_old );
        $v_old_display_coord = $v_old + 1;

        $u_new_display_coord = chr( ord( "A" ) + $u );
        $v_new_display_coord = $v + 1;

        self::notifyAllPlayers( "destinationSelectedHistory", clienttranslate( '${active_player_name} 
                ${u_old_display_coord}${v_old_display_coord}-${u_new_display_coord}${v_new_display_coord}' ),     
                array(
                    'active_player_name' => self::getActivePlayerName(),
                    'u_old_display_coord' => $u_old_display_coord,
                    'v_old_display_coord' => $v_old_display_coord,
                    'u_new_display_coord' => $u_new_display_coord,
                    'v_new_display_coord' => $v_new_display_coord,
                ) );


        //
        // Update the playerPanel to display the players' number of captured checkers
        //
        $activePlayerId = self::getActivePlayerId();

        $active_player_color = self::getPlayerColor ( $activePlayerId );


        $N=self::getGameStateValue("board_size");

        if (  $N == 5  )
            $majority_icebergs = 28;
        else if (  $N == 6  )
            $majority_icebergs = 43;
        else if (  $N == 7  )
            $majority_icebergs = 61;
        else    // $N = 8
            $majority_icebergs = 82;


        if (    $captured_checker_id != NULL    )
        {
            if (    $active_player_color == "ff0000"   )
            {
                self::incGameStateValue( "reds_captured_icebergs", 1 );

                $reds_captured_icebergs = self::getGameStateValue( "reds_captured_icebergs" );
                $blacks_captured_icebergs = self::getGameStateValue( "blacks_captured_icebergs" );

                $reds_captured_icebergs_str = "{$reds_captured_icebergs} / {$majority_icebergs}";
                $blacks_captured_icebergs_str = "{$blacks_captured_icebergs} / {$majority_icebergs}";

                self::notifyAllPlayers("playerPanel",
                    "",
                    array(
                    "captured_checkers" => $reds_captured_icebergs_str,
                    "player_id" => $activePlayerId ));

                self::notifyAllPlayers("playerPanel",
                    "",
                    array(
                    "captured_checkers" => $blacks_captured_icebergs_str,
                    "player_id" => self::getOtherPlayerId($activePlayerId) ));
            }
            else 
            {
                self::incGameStateValue( "blacks_captured_icebergs", 1 );

                $reds_captured_icebergs = self::getGameStateValue( "reds_captured_icebergs" );
                $blacks_captured_icebergs = self::getGameStateValue( "blacks_captured_icebergs" );

                $reds_captured_icebergs_str = "{$reds_captured_icebergs} / {$majority_icebergs}";
                $blacks_captured_icebergs_str = "{$blacks_captured_icebergs} / {$majority_icebergs}";

                self::notifyAllPlayers("playerPanel",
                    "",
                    array(
                    "captured_checkers" => $blacks_captured_icebergs_str,
                    "player_id" => $activePlayerId ));

                self::notifyAllPlayers("playerPanel",
                    "",
                    array(
                    "captured_checkers" => $reds_captured_icebergs_str,
                    "player_id" => self::getOtherPlayerId($activePlayerId) ));

            }
        }

        //
        //Check if it's the first move
        //
		if( self::getGameStateValue( "move_number" )==0 ) //0 since the variable is incremented when the move has been validated
        {
			//Increment number of moves
			self::incGameStateValue( "move_number", 1 );
				
			// Go to the choice state
			$this->gamestate->nextState( 'firstMoveChoice' );
		}
        else
            $this->gamestate->nextState( 'selectDestination' );

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

function argSelectChecker()
{
    return array(
        'selectableCheckers' => self::getSelectableCheckers ( self::getActivePlayerId() )
    );
}

function argSelectDestination()
{
    return array(
        'selectableDestinations' => self::getSelectableDestinations ( self::getActivePlayerId() )
    );
}


//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

function stNextPlayer()
{   
    // Activate next player
    $active_player_id = self::activeNextPlayer();

    //
    // If inactive player (player who just moved) has majority of checkers, end game.
    //
	$inactive_player_id = self::getOtherPlayerId ( $active_player_id );

    $inactive_player_color = self::getPlayerColor ( $inactive_player_id );

	//Get the board size
    $N = self::getGameStateValue("board_size");
            

    if (  $N == 5  )
        $majority_icebergs = 28;
    else if (  $N == 6  )
        $majority_icebergs = 43;
    else if (  $N == 7  )
        $majority_icebergs = 61;
    else    // $N = 8
        $majority_icebergs = 82;


    if (    $inactive_player_color == "ff0000"    )
        $captured_icebergs = self::getGameStateValue( "reds_captured_icebergs");
    else 
        $captured_icebergs = self::getGameStateValue( "blacks_captured_icebergs");

    
    if (    $captured_icebergs == $majority_icebergs    )
    {
        $sql = "UPDATE player
                SET player_score = 1 WHERE player_id = $inactive_player_id";

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
