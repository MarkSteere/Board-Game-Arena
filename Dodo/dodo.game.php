<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * Dodo implementation : © <Mark Steere> <marksteere@gmail.com>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * dodo.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class Dodo extends Table
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
            "normal_or_misere" => 100, //The size of the board
  ) );
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "dodo";
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
 
        if ( self::getGameStateValue("normal_or_misere") == 2 )
        {
            self::notifyAllPlayers( "backendMessage", clienttranslate( 'MISERE GOAL - If you cant move, you lose.  For normal goal, see settings.' ), 
            array(
                
                ) );
        }
        else
        {
            self::notifyAllPlayers( "backendMessage", clienttranslate( 'NORMAL GOAL - If you cant move, you win.  For misere goal, see settings.' ), 
            array(
                
                ) );
        }

        //
        // Create players
        // 
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
            
            if( $color == 'ff0000' )
                $red_player_id = $player_id;
            else
                $blue_player_id = $player_id;
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );

        $sql = "UPDATE player
                SET player_score = 0 WHERE 1";
                
        self::DbQuery( $sql );

        self::reloadPlayersBasicInfos();
        


        //
        // Initialize the board
        //
        $red_checker_index = 0;
        $blue_checker_index = 0;

        $checker_id = 0;

        $sql = "INSERT INTO board (board_u, board_v, board_player, checker_index, checker_id, is_selected) VALUES ";
        $sql_values = array();

        for( $u=0; $u<=6; $u++ )
        {
            for( $v=0; $v<=6; $v++ )
            {

                if (    (  ($u >= 0 && $u <= 3) && ($v >= 3 - $u && $v <= 6)  ) ||  // Cell is on the board
                        (  ($u >= 4 && $u <= 6) && ($v >= 0 && $v <= 9 - $u)  )    )
                {
                    $player_id_value = "NULL";
                    $checker_index_value = "NULL";

                    if  (   ( $u==3 && ( $v>=0 && $v<=1 ) )  
                    ||  ( $u==4 && ( $v>=0 && $v<=2 ) )
                    ||  ( $u==5 && ( $v>=0 && $v<=3 ) )
                    ||  ( $u==6 && ( $v>=0 && $v<=3 ) )
                    )
                    { 
                        $player_id_value = $red_player_id;
                        $checker_index_value = $red_checker_index++;  // Normal values for red checker indices

                        $checker_id_value = $checker_id++;
                    }

                    else if (   ( $u==0 && ( $v>=3 && $v<=6 ) )  
                        ||  ( $u==1 && ( $v>=3 && $v<=6 ) )                
                        ||  ( $u==2 && ( $v>=4 && $v<=6 ) )                
                        ||  ( $u==3 && ( $v>=5 && $v<=6 ) )                
                        )
                    {
                        $player_id_value = $blue_player_id;
                        $checker_index_value = 100 + $blue_checker_index++; // Add 100 to blue checker indices

                        $checker_id_value = $checker_id++;
                    }

                    $sql_values[] = "($u, $v, $player_id_value, $checker_index_value, $checker_id_value, 0)";
                    
                }
            }
        }

        $sql .= implode( $sql_values, ',' );
        self::DbQuery( $sql );

        $this->activeNextPlayer();
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
    
        //
        // Get players information
        // 
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );
  
        //
        // Get board information
        // 
        $sql = "SELECT board_u u, board_v v, board_player player, checker_index ch_index, checker_id ch_id, is_selected is_sel
                FROM board
                WHERE board_player IS NOT NULL";
        $result['board'] = self::getObjectListFromDB( $sql );
  
		$result['normal_or_misere'] = self::getGameStateValue("normal_or_misere");

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
        $active_player_id = self::getActivePlayerId();

        $table = $this->getNextPlayerTable();
        $inactive_player_id = $table[$active_player_id];

        $active_player_count = 0;
        $active_player_selectable_checkers = self::getSelectableCheckers ( $active_player_id );

        $inactive_player_count = 0;
        $inactive_player_selectable_checkers = self::getSelectableCheckers ( $inactive_player_id );

        for( $u=0; $u<=6; $u++ )
        {
            for( $v=0; $v<=6; $v++ )
            {
                if (    (  ($u >= 0 && $u <= 3) && ($v >= 3 - $u && $v <= 6)  ) ||  // Cell is on the board
                        (  ($u >= 4 && $u <= 6) && ($v >= 0 && $v <= 9 - $u)  )    )
                {
                    if ( ($active_player_selectable_checkers [$u][$v] ?? false) == true )
                        ++$active_player_count;

                    if ( ($inactive_player_selectable_checkers [$u][$v] ?? false) == true )
                        ++$inactive_player_count;
                }
            }
        }


        $lesser_count = min ( $active_player_count, $inactive_player_count );

        /*
        self::notifyAllPlayers( "backendMessage", clienttranslate( '$active_player_count ${var_1}' ), 
            array(
            'var_1' => $active_player_count,
            ) );
        self::notifyAllPlayers( "backendMessage", clienttranslate( '$inactive_player_count ${var_1}' ), 
            array(
                'var_1' => $inactive_player_count,
            ) );
        self::notifyAllPlayers( "backendMessage", clienttranslate( '$lesser_count ${var_1}' ), 
            array(
                'var_1' => $lesser_count,
            ) );
        */
            

        /*
        self::notifyAllPlayers( "backendMessage", clienttranslate( '$lesser_count ${var_1}' ), 
        array(
            'var_1' => $lesser_count,
            ) );
        */


        if ($lesser_count >= 7)
            return 0;
        else if ($lesser_count == 6)
            return 17;
        else if ($lesser_count == 5)
            return 33;
        else if ($lesser_count == 4)
            return 50;
        else if ($lesser_count == 3)
            return 67;
        else if ($lesser_count == 2)
            return 83;
        else if ($lesser_count <= 1)
            return 100;

    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

/*
function getActivePlayerColor() 
{
    $player_id = self::getActivePlayerId();
    $players = self::loadPlayersBasicInfos();
    if (isset($players[$player_id]))
        return $players[$player_id]['player_color'];
    else
        return null;
}
*/
function getActivePlayerColor ( $active_player_id ) 
{
    $players = self::loadPlayersBasicInfos();

    if (isset ( $players [ $active_player_id ] ) )
        return $players[ $active_player_id ][ 'player_color' ];
    else
        return null;
}


function hasMoves ( $u, $v, $board, $active_player_id )
{    
    $active_player_color = self::getActivePlayerColor( $active_player_id );

    if (  $active_player_color == 'ff0000' )
    {
        $directions = array( array( -1, 0 ), array( -1, 1 ), array( 0, 1 ) );
    }
    else
    {
        $directions = array( array( 1, 0 ), array( 1, -1 ), array( 0, -1 ) );
    }

    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( 'hasMoves $u, $v: ${var_1}, ${var_2}' ), 
    array(
        'var_1' => $u,
        'var_2' => $v
        ) );
    */

    foreach( $directions as $direction )
    {
        $neighbor_u = $u + $direction[0];
        $neighbor_v = $v + $direction[1];


        if (    (  ($neighbor_u >= 0 && $neighbor_u <= 3) && ($neighbor_v >= 3 - $neighbor_u && $neighbor_v <= 6)  ) ||  // Cell is on the board
                (  ($neighbor_u >= 4 && $neighbor_u <= 6) && ($neighbor_v >= 0 && $neighbor_v <= 9 - $neighbor_u)  )    )
        {
            if ( $board [ $neighbor_u ] [ $neighbor_v ] == null )
                return true;       
        }
    }

    return false;
}


function getSelectableCheckers( $active_player_id )
{
    $result = array();

    $board = self::getBoard();

    for( $u=0; $u<=6; $u++ )
    {
        for( $v=0; $v<=6; $v++ )
        {

            if (    (  ($u >= 0 && $u <= 3) && ($v >= 3 - $u && $v <= 6)  ) ||  // Cell is on the board
                    (  ($u >= 4 && $u <= 6) && ($v >= 0 && $v <= 9 - $u)  )    )
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



function getSelCheckerDestinations( $u, $v, $board, $active_player_id )
{
    $result = array();

    $active_player_color = self::getActivePlayerColor( $active_player_id );

    if (  $active_player_color == 'ff0000' )
        $directions = array( array( -1, 0 ), array( -1, 1 ), array( 0, 1 ) );
    else
        $directions = array( array( 1, 0 ), array( 1, -1 ), array( 0, -1 ) );

    foreach( $directions as $direction )
    {
        $neighbor_u = $u + $direction[0];
        $neighbor_v = $v + $direction[1];

        if (    (  ($neighbor_u >= 0 && $neighbor_u <= 3) && ($neighbor_v >= 3 - $neighbor_u && $neighbor_v <= 6)  ) ||  // Cell is on the board
                (  ($neighbor_u >= 4 && $neighbor_u <= 6) && ($neighbor_v >= 0 && $neighbor_v <= 9 - $neighbor_u)  )    )
        {
            if ( $board [ $neighbor_u ] [ $neighbor_v ] == null )
            {
                if( ! isset( $result[$neighbor_u] ) )
                $result[$neighbor_u] = array();

                $result[$neighbor_u][$neighbor_v] = true;
            }    
        }
    }

    return $result;  
}



function getSelectableDestinations( $active_player_id )
{
    //$result = array();

    $board = self::getBoard();

    $boardSelectedCheckers = self::getBoardSelectedCheckers();  // Should just be one

    for( $u=0; $u<=6; $u++ )
    {
        for( $v=0; $v<=6; $v++ )
        {

            if (    (  ($u >= 0 && $u <= 3) && ($v >= 3 - $u && $v <= 6)  ) ||  // Cell is on the board
                    (  ($u >= 4 && $u <= 6) && ($v >= 0 && $v <= 9 - $u)  )    )
            {

                if (    (  $board [ $u ] [ $v ] == $active_player_id  )
                    &&  (  $boardSelectedCheckers [ $u ] [ $v ] == 1 )    )
                {

                    return (  self::getSelCheckerDestinations ($u, $v, $board, $active_player_id)  );

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
    return self::getDoubleKeyCollectionFromDB( "SELECT board_u u, board_v v, board_player player
                                                FROM board", true );
}


function getBoardSelectedCheckers()
{
    return self::getDoubleKeyCollectionFromDB( "SELECT board_u u, board_v v, is_selected is_sel
                                                FROM board", true );
}



    
/*
        In this space, you can put any utility methods useful for your game logic
*/



//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

function selectChecker( $u, $v )
{

    self::checkAction( 'selectChecker' );  
    
    $active_player_id = self::getActivePlayerId(); 
        
    $board = self::getBoard();

    // Check if this is a selectable checker
    if ( self::hasMoves ( $u, $v, $board, $active_player_id ) )
    {
                        
        $sql = "UPDATE board SET is_selected = 1
                WHERE ( board_u, board_v) = ($u, $v)";  

        self::DbQuery( $sql );

        // 
        // Determine oriented display coordinates for history panel
        //
        $active_player_color = self::getActivePlayerColor( $active_player_id );

        if (  $active_player_color == 'ff0000' )
        {
            $u_oriented = $u;
            $v_oriented = $v;                
                
            $u_oriented_display_coord = chr( ord( "A" ) + $u_oriented );
            $v_oriented_display_coord = $v_oriented + 1;
        }
        else
        {
            $u_oriented = 6 - $u;
            $v_oriented = 6 - $v;                
                
            $u_oriented_display_coord = chr( ord( "A" ) + 6 - $u_oriented );
            $v_oriented_display_coord = $v_oriented + 1;
        }

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
    // Find checker id of selected checker
    //
    $sql = "SELECT board_u, board_v, checker_index, checker_id from board WHERE is_selected = 1 ";       
    $result = self::DbQuery( $sql );
    $row = $result -> fetch_assoc();

    $u_old = $row ["board_u"];
    $v_old = $row ["board_v"];
    $checker_index_old = $row ["checker_index"];

    $checker_id = $row ["checker_id"];


    //
    // Move the checker
    //
    // Delete old checker
    $sql = "UPDATE board SET board_player = NULL, checker_index = NULL, checker_id = NULL, is_selected = 0
            WHERE ( board_u, board_v) = ($u_old, $v_old)";  
    self::DbQuery( $sql );

    // Add new checker
    $sql = "UPDATE board SET board_player = $active_player_id, checker_index = $checker_index_old, checker_id = $checker_id, is_selected = 0
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
    ) );




    $table = $this->getNextPlayerTable();
    $inactive_player_id = $table[$active_player_id];


    $active_player_color = self::getActivePlayerColor( $active_player_id );


    if (  $active_player_color == 'ff0000' )
    {
        $u_active_old_oriented = $u_old;
        $v_active_old_oriented = $v_old;                
            
        $u_active_old_oriented_display_coord = chr( ord( "A" ) + $u_active_old_oriented );
        $v_active_old_oriented_display_coord = $v_active_old_oriented + 1;

        $u_active_new_oriented = $u;
        $v_active_new_oriented = $v;                
            
        $u_active_new_oriented_display_coord = chr( ord( "A" ) + $u_active_new_oriented );
        $v_active_new_oriented_display_coord = $v_active_new_oriented + 1;


        $u_inactive_old_oriented = 6 - $u_old;
        $v_inactive_old_oriented = 6 - $v_old;                
            
        $u_inactive_old_oriented_display_coord = chr( ord( "A" ) + $u_inactive_old_oriented );
        $v_inactive_old_oriented_display_coord = $v_inactive_old_oriented + 1;

        $u_inactive_new_oriented = 6 - $u;
        $v_inactive_new_oriented = 6 - $v;                
            
        $u_inactive_new_oriented_display_coord = chr( ord( "A" ) + $u_inactive_new_oriented );
        $v_inactive_new_oriented_display_coord = $v_inactive_new_oriented + 1;
    }
    else
    {
        $u_active_old_oriented = 6 - $u_old;
        $v_active_old_oriented = 6 - $v_old;                
            
        $u_active_old_oriented_display_coord = chr( ord( "A" ) + $u_active_old_oriented );
        $v_active_old_oriented_display_coord = $v_active_old_oriented + 1;

        $u_active_new_oriented = 6 - $u;
        $v_active_new_oriented = 6 - $v;                
            
        $u_active_new_oriented_display_coord = chr( ord( "A" ) + $u_active_new_oriented );
        $v_active_new_oriented_display_coord = $v_active_new_oriented + 1;


        $u_inactive_old_oriented = $u_old;
        $v_inactive_old_oriented = $v_old;                
            
        $u_inactive_old_oriented_display_coord = chr( ord( "A" ) + $u_inactive_old_oriented );
        $v_inactive_old_oriented_display_coord = $v_inactive_old_oriented + 1;

        $u_inactive_new_oriented = $u;
        $v_inactive_new_oriented = $v;                
            
        $u_inactive_new_oriented_display_coord = chr( ord( "A" ) + $u_inactive_new_oriented );
        $v_inactive_new_oriented_display_coord = $v_inactive_new_oriented + 1;
    }




    self::notifyPlayer( $active_player_id, "destinationSelectedHistory", clienttranslate( '${active_player_name} 
    ${u_active_old_oriented_display_coord}${v_active_old_oriented_display_coord}-${u_active_new_oriented_display_coord}${v_active_new_oriented_display_coord}' ), 

        array(
        'active_player_id' => $active_player_id,
        'active_player_name' => self::getActivePlayerName(),

        'u_active_old_oriented_display_coord' => $u_active_old_oriented_display_coord,
        'v_active_old_oriented_display_coord' => $v_active_old_oriented_display_coord,

        'u_active_new_oriented_display_coord' => $u_active_new_oriented_display_coord,
        'v_active_new_oriented_display_coord' => $v_active_new_oriented_display_coord

    ) );

    

    self::notifyPlayer( $inactive_player_id, "destinationSelectedHistory", clienttranslate( '${active_player_name} 
    ${u_inactive_old_oriented_display_coord}${v_inactive_old_oriented_display_coord}-${u_inactive_new_oriented_display_coord}${v_inactive_new_oriented_display_coord}' ), 

        array(
        'inactive_player_id' => $inactive_player_id,
        'active_player_name' => self::getActivePlayerName(),

        //'inactive_player_name' => self::getPlayerNameById($inactive_player_id),

        'u_inactive_old_oriented_display_coord' => $u_inactive_old_oriented_display_coord,
        'v_inactive_old_oriented_display_coord' => $v_inactive_old_oriented_display_coord,

        'u_inactive_new_oriented_display_coord' => $u_inactive_new_oriented_display_coord,
        'v_inactive_new_oriented_display_coord' => $v_inactive_new_oriented_display_coord

    ) );


    $this->gamestate->nextState( 'selectDestination' );
    
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

function stNextPlayer()
{
    // Active next player
    $active_player_id = self::activeNextPlayer();


    // Can this player play?
    $selectableCheckers = self::getSelectableCheckers( $active_player_id );

    
    if( count( $selectableCheckers ) == 0 )
    {

        

		$normal_or_misere=self::getGameStateValue("normal_or_misere");





        if ( $normal_or_misere == 1 )
        {
            $sql = "UPDATE player
                    SET player_score = 1 WHERE player_id = $active_player_id";
                    self::DbQuery( $sql );
        }
        else
        {
            $sql = "UPDATE player
                    SET player_score = 1 WHERE player_id != $active_player_id";
                    self::DbQuery( $sql );
        }




        $newScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );

        self::notifyAllPlayers( "newScores", "", array(
            "scores" => $newScores
        ) );

        $this->gamestate->nextState( 'endGame' );
    }
    else
    {
        // This player can play. Give him some extra time
        self::giveExtraTime( $active_player_id );
        $this->gamestate->nextState( 'nextTurn' );
    }
    
}


/*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
    
    /*
    
    Example for game state "MyGameState":

    function stMyGameState()
    {
        // Do some stuff ...
        
        // (very often) go to another gamestate
        $this->gamestate->nextState( 'some_gamestate_transition' );
    }    
    */

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

    /*
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
    */
    
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
