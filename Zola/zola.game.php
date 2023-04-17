<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * Zola implementation : © <Mark Steere> <marksteere@gmail.com>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * zola.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class Zola extends Table
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
            "board_size" => 101, //The size of the board
            ) );        
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "zola";
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







        if (empty(self::getGameStateValue("board_size"))) 
            $N = 6;
        else
            $N=self::getGameStateValue("board_size");






        /*
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'Board size ${var_1}.' ), 
        array(
            'var_1' => $N,
            ) );   
        */

        //
        // Initialize the board
        //
        $red_checker_id = 0;    // red checker range 0 to 17
        $black_checker_id = 100; // black checker range 100 to 117

        $sql = "INSERT INTO board (board_x, board_y, board_player, checker_id, is_selected) VALUES ";        // Coords from Red's perspective
        $sql_values = array();                                                                  // (0,0) lower left

        for( $x=0; $x<$N; $x++ )
        {
            for( $y=0; $y<$N; $y++ )
            {             
                if (  ($x + $y) % 2 == 0  ) // Red checker
                {
                    $sql_values[] = "($x, $y, $red_player_id, $red_checker_id, 0)";
                    $red_checker_id++;
                }
                else                        // Black checker
                {
                    $sql_values[] = "($x, $y, $black_player_id, $black_checker_id, 0)";
                    $black_checker_id++;
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
        $sql = "SELECT board_x x, board_y y, board_player player, checker_id ch_id, is_selected is_sel
                FROM board
                WHERE board_player IS NOT NULL";
        $result['board'] = self::getObjectListFromDB( $sql );


        //
        // Remaining checkers
        //
        $activePlayerId = self::getActivePlayerId();

        $otherPlayerId = self::getOtherPlayerId($activePlayerId);

        $remainingCheckers = array();
        $remainingCheckers[$activePlayerId] = self::getRemainingCheckers($activePlayerId);
        $remainingCheckers[$otherPlayerId] = self::getRemainingCheckers($otherPlayerId);
        
        $result['remainingCheckers'] = $remainingCheckers;
        





		//Get the board size
        if (empty(self::getGameStateValue("board_size"))) 
            $N = 6;
        else
            $N=self::getGameStateValue("board_size");


		$result['board_size'] = $N;





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
        $board = self::getBoard();
        $activePlayerId = self::getActivePlayerId(); 


		//Get the board size
        if (empty(self::getGameStateValue("board_size"))) 
            $N = 6;
        else
            $N=self::getGameStateValue("board_size");
            


        if ( $N == 6 )
            $initial_checker_count = 18;
        else
            $initial_checker_count = 32;


        $active_remaining_checkers = self::getRemainingCheckers($activePlayerId);
        $inactive_remaining_checkers = self::getRemainingCheckers(self::getOtherPlayerId($activePlayerId));

        $lesser_remaining_checkers = min ($active_remaining_checkers, $inactive_remaining_checkers);

        return (    ($initial_checker_count-$lesser_remaining_checkers) / $initial_checker_count * 100    );
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

function getActivePlayerColor ( $active_player_id ) 
{
    $players = self::loadPlayersBasicInfos();

    if (isset ( $players [ $active_player_id ] ) )
        return $players[ $active_player_id ][ 'player_color' ];
    else
        return null;
}


function hasMoves ( $x, $y, $board, $active_player_id )
{    
    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( 'hasMoves function.' ), 
    array(
        
        ) ); 
    */   
        



	//Get the board size
    if (empty(self::getGameStateValue("board_size"))) 
        $N = 6;
    else
        $N=self::getGameStateValue("board_size");
            


            

        
    $dist_from_ctr = pow (  abs($x+.5-$N/2), 2  ) + pow (  abs($y+.5-$N/2), 2  );
                            //
    $directions = array(    array( -1, 1 ), array( 0, 1 ), array( 1, 1 ), array( 1, 0 ), 
                            array( 1, -1 ), array( 0, -1 ), array( -1, -1 ), array( -1, 0 )     );

    //
    // Check for king moves
    //
    foreach( $directions as $direction )
    {
        $neighbor_x = $x + $direction[0];
        $neighbor_y = $y + $direction[1];

        $neighbor_dist_from_ctr = pow (  abs($neighbor_x+.5-$N/2), 2  ) + pow (  abs($neighbor_y+.5-$N/2), 2  );


        if (    (  $neighbor_x >= 0 && $neighbor_x < $N && $neighbor_y >= 0 && $neighbor_y < $N  )  // Square is on the board
            &&  (  $board [ $neighbor_x ] [ $neighbor_y ] == null  )                                // Unoccupied square
            &&  (  $neighbor_dist_from_ctr > $dist_from_ctr  )    )                                 // Neighbor farther from center
        {
            /*
            self::notifyAllPlayers( "backendMessage", clienttranslate( 'Has king moves.' ), 
            array(
                    
                ) );
            */                    
                
            return true;    
        }               
    }


    //
    // Check for queen moves
    //
    foreach( $directions as $direction )
    {
        $neighbor_x = $x + $direction[0];
        $neighbor_y = $y + $direction[1];

        while (true)
        {
            $neighbor_dist_from_ctr = pow (  abs($neighbor_x+.5-$N/2), 2  ) + pow (  abs($neighbor_y+.5-$N/2), 2  );

            if (   $neighbor_x < 0 || $neighbor_x >= $N || $neighbor_y < 0 || $neighbor_y >= $N   )     // Square is off the board
                break;

            if  (  $board [ $neighbor_x ] [ $neighbor_y ] == $active_player_id )                    // Friendly checker
                break;

            if  (  $board [ $neighbor_x ] [ $neighbor_y ] == null )                                 // Unoccupied square
            {
                $neighbor_x += $direction[0];
                $neighbor_y += $direction[1];                
                
                continue;
            }

            if  (  $neighbor_dist_from_ctr > $dist_from_ctr  )                                      // Not off the board, not friendly, not unoccupied...
                break;                                                                              // so must be enemy checker.  If not further away...
            else                                                                                    // it's a queen move.
            {
                /*
                self::notifyAllPlayers( "backendMessage", clienttranslate( 'Has queen moves.' ), 
                array(
                    
                    ) );   
                */                 
                
                return true;    
            }   
        }
    }

    return false;
}


function getSelectableCheckers( $active_player_id )
{
    $result = array();

    $board = self::getBoard();




	//Get the board size
    if (empty(self::getGameStateValue("board_size"))) 
        $N = 6;
    else
        $N=self::getGameStateValue("board_size");
            


            

    for( $x=0; $x<$N; $x++ )
    {
        for( $y=0; $y<$N; $y++ )
        {

            if ( $board [ $x ] [ $y ] == $active_player_id )
            {

                if ( self::hasMoves ( $x, $y, $board, $active_player_id ) )
                {
                    if( ! isset( $result[$x] ) )
                    $result[$x] = array();

                    $result[$x][$y] = true;
                }
            }
        }
    }

    return $result;
}



function getSelCheckerDestinations( $x, $y, $board, $active_player_id )
{
    $result = array();

    //$active_player_color = self::getActivePlayerColor( $active_player_id );




	//Get the board size
    if (empty(self::getGameStateValue("board_size"))) 
        $N = 6;
    else
        $N=self::getGameStateValue("board_size");
            


            

    $board_center = ( $N / 2 ) - .5;


    $dist_from_ctr = pow (  (abs($x-$board_center)), 2  ) + pow (  (abs($y-$board_center)), 2  );           // Distance from square center to board center
    //$dist_from_ctr = pow (  (abs($x-2.5)+.5), 2  ) + pow (  (abs($y-2.5)+.5), 2  );
                            //
    $directions = array(    array( -1, 1 ), array( 0, 1 ), array( 1, 1 ), array( 1, 0 ), 
                            array( 1, -1 ), array( 0, -1 ), array( -1, -1 ), array( -1, 0 )     );


    //
    // Check for king moves
    //
    foreach( $directions as $direction )
    {
        $neighbor_x = $x + $direction[0];
        $neighbor_y = $y + $direction[1];

        $neighbor_dist_from_ctr = pow (  (abs($neighbor_x-$board_center)), 2  ) + pow (  (abs($neighbor_y-$board_center)), 2  );


        if (    (  $neighbor_x >= 0 && $neighbor_x < $N && $neighbor_y >= 0 && $neighbor_y < $N  )  // Square is on the board
                &&  (  $board [ $neighbor_x ] [ $neighbor_y ] == null  )                                // Unoccupied square
                &&  (  $neighbor_dist_from_ctr > $dist_from_ctr  )    )                                 // Neighbor farther from center
        {
            /*
            self::notifyAllPlayers( "backendMessage", clienttranslate( 'Adding king destination to $result array' ), 
            array(
                    
                ) );
            */

            if( ! isset( $result[$neighbor_x] ) )
            $result[$neighbor_x] = array();

            $result[$neighbor_x][$neighbor_y] = true;            
        }               
    }


    //
    // Check for queen moves
    //
    foreach( $directions as $direction )
    {
        $neighbor_x = $x + $direction[0];
        $neighbor_y = $y + $direction[1];

        while (true)
        {
            $neighbor_dist_from_ctr = pow (  (abs($neighbor_x-$board_center)), 2  ) + pow (  (abs($neighbor_y-$board_center)), 2  );

            if (   $neighbor_x < 0 || $neighbor_x >= $N || $neighbor_y < 0 || $neighbor_y >= $N  )     // Square is off the board
                break;

            if  (  $board [ $neighbor_x ] [ $neighbor_y ] == $active_player_id )                    // Friendly checker
                break;

            if  (  $board [ $neighbor_x ] [ $neighbor_y ] == null )                                 // Unoccupied square
            {
                $neighbor_x += $direction[0];
                $neighbor_y += $direction[1];                
                
                continue;
            }

            if  (  $neighbor_dist_from_ctr > $dist_from_ctr  )                                      // Not off the board, not friendly, not unoccupied...
                break;                                                                              // so must be enemy checker.  If not further away...
            else                                                                                    // it's a queen move.
            {
                /*
                self::notifyAllPlayers( "backendMessage", clienttranslate( 'Adding queen destination to $result array ${var_1} ${var_2}.' ), 
                array(
                    'var_1' => $neighbor_x,
                    'var_2' => $neighbor_y,
                    ) );   
                */
                    
                if( ! isset( $result[$neighbor_x] ) )
                $result[$neighbor_x] = array();
    
                $result[$neighbor_x][$neighbor_y] = true;
                    
                break;
            }   
        }
    }

    return $result;  
}


function getSelectableDestinations( $active_player_id )
{

    $board = self::getBoard();

    $boardSelectedCheckers = self::getBoardSelectedCheckers();  // Should just be one





	//Get the board size
    if (empty(self::getGameStateValue("board_size"))) 
        $N = 6;
    else
        $N=self::getGameStateValue("board_size");
            


            


    for( $x=0; $x<$N; $x++ )
    {
        for( $y=0; $y<$N; $y++ )
        {
            if (  $x >= 0 && $x < $N && $y >= 0 && $y < $N  )  // Square is on the board
            {
                if (    (  $board [ $x ] [ $y ] == $active_player_id  )
                    &&  (  $boardSelectedCheckers [ $x ] [ $y ] == 1 )    )
                {
                    return (  self::getSelCheckerDestinations ($x, $y, $board, $active_player_id)  );
                }
            }
        }
    }
}



//
// Return an associative array of associative array, from a SQL SELECT query.
// First array level correspond to first column specified in SQL query.
// Second array level correspond to second column specified in SQL query.
// If bSingleValue = true, keep only third column on result
// 
// @param      $sql
// @param bool $bSingleValue
// 
function getBoard()
{
    return self::getDoubleKeyCollectionFromDB( "SELECT board_x x, board_y y, board_player player
                                                FROM board", true );
}


function getBoardSelectedCheckers()
{
    return self::getDoubleKeyCollectionFromDB( "SELECT board_x x, board_y y, is_selected is_sel
                                                FROM board", true );
}


function getRemainingCheckers($player_id)
{
    $board = self::getBoard();

    $checker_count = 0;    
    



	//Get the board size
    if (empty(self::getGameStateValue("board_size"))) 
        $N = 6;
    else
        $N=self::getGameStateValue("board_size");
            


            

    for ( $x = 0; $x < $N; $x++)
    {
        for ( $y = 0; $y < $N; $y++)
        {
            if ( $board [ $x ] [ $y ] == $player_id )
                ++$checker_count;
        }
    }

    return ($checker_count);
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
		
	//Notifications of new color for each player
	self::notifyAllPlayers( "swapColors", clienttranslate( '${player_name} is now playing <span style="color:#${player_color};">${player_colorname}</span>.' ), array(
		'i18n' => array( 'player_colorname' ),
		'player_id' => $player[0]['id'],
		'player_name' => $player[0]['name'],
		'player_color' => $player[1]['color'],
		'player_colorname' => self::getColorName($player[1]['color']),

        'remainingcheckers' => self::getRemainingCheckers($player[1]['id'])
	) );
	self::notifyAllPlayers( "swapColors", clienttranslate( '${player_name} is now playing <span style="color:#${player_color};">${player_colorname}</span>.' ), array(
		'i18n' => array( 'player_colorname' ),
		'player_id' => $player[1]['id'],
		'player_name' => $player[1]['name'],
		'player_color' => $player[0]['color'],
		'player_colorname' => self::getColorName($player[0]['color']),

        'remainingcheckers' => self::getRemainingCheckers($player[0]['id'])
	) );

		
	//Update player info
	self::reloadPlayersBasicInfos();
}



//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in zola.action.php)
    */

    function selectChecker( $x, $y )
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
        if ( self::hasMoves ( $x, $y, $board, $active_player_id ) )
        {
            /*               
            self::notifyAllPlayers( "backendMessage", clienttranslate( 'selectChecker - hasMoves' ), 
            array(
                
                ) );
            */

            $sql = "UPDATE board SET is_selected = 1
                    WHERE ( board_x, board_y) = ($x, $y)";  
    
            self::DbQuery( $sql );
    
    
            //
            // Notify
            //
            self::notifyAllPlayers( "checkerSelected", clienttranslate( '' ), 
                array(
                'x' => $x,
                'y' => $y,
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
    
    
    function selectDestination($x, $y)
    {
        self::checkAction( 'selectDestination' );  
    
        $active_player_id = self::getActivePlayerId(); 
            
    
        //
        // Find ID of moving checker
        //
        $sql = "SELECT board_x, board_y, checker_id from board WHERE is_selected = 1 ";       
        $result = self::DbQuery( $sql );
        $row = $result -> fetch_assoc();
    
        $x_old = $row ["board_x"];
        $y_old = $row ["board_y"];
    
        $checker_id = $row ["checker_id"];
    
    
        //
        // Find ID of checker on destination square, which could be NULL
        //
        $sql = "SELECT board_x, board_y, checker_id from board WHERE ( board_x, board_y) = ($x, $y) ";       
        $result = self::DbQuery( $sql );
        $row = $result -> fetch_assoc();
        
        $captured_checker_id = $row ["checker_id"];
    
    
        //
        // Move the checker
        //
        // Delete old checker
        $sql = "UPDATE board SET board_player = NULL, checker_id = NULL, is_selected = 0
                WHERE ( board_x, board_y) = ($x_old, $y_old)";  
        self::DbQuery( $sql );
    
        // Add new checker
        $sql = "UPDATE board SET board_player = $active_player_id, checker_id = $checker_id, is_selected = 0
            WHERE ( board_x, board_y) = ($x, $y)";  
        self::DbQuery( $sql );
    
    
        //
        // Notify
        //
        self::notifyAllPlayers( "destinationSelected", clienttranslate( '' ), 
            array(
            'checker_id' => $checker_id,
            'x_new' => $x,
            'y_new' => $y,

            'captured_checker_id' => $captured_checker_id,

            'capturing_player_id' => $active_player_id,
        ) );


        $x_old_display_coord = chr( ord( "A" ) + $x_old );
        $y_old_display_coord = $y_old + 1;

        $x_new_display_coord = chr( ord( "A" ) + $x );
        $y_new_display_coord = $y + 1;

        self::notifyAllPlayers( "destinationSelectedHistory", clienttranslate( '${active_player_name} 
                ${x_old_display_coord}${y_old_display_coord}-${x_new_display_coord}${y_new_display_coord}' ),     
                array(
                    'active_player_name' => self::getActivePlayerName(),
                    'x_old_display_coord' => $x_old_display_coord,
                    'y_old_display_coord' => $y_old_display_coord,
                    'x_new_display_coord' => $x_new_display_coord,
                    'y_new_display_coord' => $y_new_display_coord,
                ) );


        //
        // Update the playerPanel to display the players' number of remaining checkers
        //
        $activePlayerId = self::getActivePlayerId();

        $remaining_checkers = self::getRemainingCheckers($activePlayerId);
        self::notifyAllPlayers("playerPanel",
            "",
            array(
                "remaining_checkers" => $remaining_checkers,
                "player_id" => $activePlayerId));

        $remaining_checkers = self::getRemainingCheckers(self::getOtherPlayerId($activePlayerId));
        self::notifyAllPlayers("playerPanel",
            "",
            array(
                "remaining_checkers" => $remaining_checkers,
                "player_id" => self::getOtherPlayerId($activePlayerId)));


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


		$sql = "UPDATE board SET board_player = 0 WHERE board_player = $active_player_id"; 
		self::DbQuery($sql);
        
		$sql = "UPDATE board SET board_player = $active_player_id WHERE board_player = $inactive_player_id"; 
		self::DbQuery($sql);
        
		$sql = "UPDATE board SET board_player = $inactive_player_id WHERE board_player = 0"; 
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
    // Active next player
    $active_player_id = self::activeNextPlayer();

    $board = self::getBoard();




	//Get the board size
    if (empty(self::getGameStateValue("board_size"))) 
        $N = 6;
    else
        $N=self::getGameStateValue("board_size");
            


            

    //
    // Does active player have any checkers on the board?
    //
    $checker_count = 0;    
    
    for ( $x = 0; $x < $N; $x++)
    {
        for ( $y = 0; $y < $N; $y++)
        {
            if ( $board [ $x ] [ $y ] == $active_player_id )
            {
                ++$checker_count;

                break;
            }
        }

        if ( $checker_count > 0 )
            break;
    }
    
    if ( $checker_count  == 0 )
    {
        /*
        self::notifyAllPlayers( "backendMessage", clienttranslate( '$checker_count = 0.  Go to endGame.' ), 
        array(

            ) );
        */


        $sql = "UPDATE player
                SET player_score = 1 WHERE player_id != $active_player_id";
                self::DbQuery( $sql );
        
        $newScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );

        self::notifyAllPlayers( "newScores", "", array(
            "scores" => $newScores
        ) );

        $this->gamestate->nextState( 'endGame' );
    }
    else
    {
        $selectableCheckers = self::getSelectableCheckers( $active_player_id );
    
        if( count( $selectableCheckers ) == 0 )                                     // Has checkers on board, but none are selectable
        {
            $this->gamestate->nextState( 'cantPlay' );
        }
        else
        {            
            self::giveExtraTime( $active_player_id );                               // Active player can play. Give him some extra time.
            $this->gamestate->nextState( 'nextTurn' );
        } 
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
