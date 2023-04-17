<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * Silo implementation : © <Mark Steere> <marksteere@gmail.com>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * silo.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class Silo extends Table
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
            "board_size" => 101, //The size of the board
            ) );        
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "silo";
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
                $black_player_id = $player_id;
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
        $red_checker_id = 0;    // red checker range 0 to 8
        $black_checker_id = 100; // black checker range 100 to 108

        $sql = "INSERT INTO board (board_x, board_y, board_player, checker_id) VALUES ";        // Coords from Red's perspective
        $sql_values = array();                                                                  // (0,0) lower left


        $N=$this->getBoardSize();

        for( $x=0; $x<$N; $x++ )
        {
            for( $y=0; $y<3; $y++ )
            {
                if ($x % 2 == 0)
                {
                    $sql_values[] = "($x, $y, $red_player_id, $red_checker_id)";
                    $red_checker_id++;
                }
                else
                {
                    $sql_values[] = "($x, $y, $black_player_id, $black_checker_id)";
                    $black_checker_id++;
                }
            }
            for( $y=3; $y<3*$N; $y++ )
            {
                    $sql_values[] = "($x, $y, NULL, NULL)";
            }
        }

        $sql .= implode( $sql_values, ',' );
        self::DbQuery( $sql );


        //
        // TEST CHECKERS
        //
        /*
        $sql = "UPDATE board SET board_player = $black_player_id, checker_id = $black_checker_id WHERE board_x = 0 AND board_y = 3";        
        self::DbQuery( $sql );

        $black_checker_id++;

        $sql = "UPDATE board SET board_player = $black_player_id, checker_id = $black_checker_id WHERE board_x = 0 AND board_y = 4";        
        self::DbQuery( $sql );
        */

    
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
    
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );
  
        //
        // Get board information
        // 
        $sql = "SELECT board_x x, board_y y, board_player player, checker_id ch_id
                FROM board
                WHERE board_player IS NOT NULL";
        $result['board'] = self::getObjectListFromDB( $sql );


        $N=$this->getBoardSize();
	
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


        /*
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'start game player progression...'  ), 
        array(

            ) );
        */


        $board = self::getBoard();
        $active_player_id = self::getActivePlayerId(); 
        $active_player_color = self::getPlayerColor( $active_player_id );

        $table = $this->getNextPlayerTable();
        $inactive_player_id = $table[$active_player_id];


        $N=self::getGameStateValue("board_size");


        $red_player_id = -1;

        if (  $active_player_color == 'ff0000' )
        {
            $red_player_id = $active_player_id;

            $black_player_id = $inactive_player_id;
        }
        else
        {
            $red_player_id = $inactive_player_id;
        
            $black_player_id = $active_player_id;
        }



        
        //
        // Last occupied column for Red
        //
        /*
        $last_occupied_red_column = -1;
       
        for ( $x = 0; $x < $N; $x++ )
        {
            for ( $y = 0; $y < 3*$N; $y++ )
            {
                if ( $board [$N - 1 - $x][$y] == null )
                    break;

                if ( $board [$N - 1 - $x][$y] == $red_player_id )
                {
                    $last_occupied_red_column = $N - 1 - $x;

                    break;
                }
            }
            
            if ( $last_occupied_red_column >= 0 )
                break;
        }

        //
        // Last occupied column for Black
        //
        $last_occupied_black_column = -1;
        
        for ( $x = 0; $x < $N; $x++ )
        {
            for ( $y = 0; $y < 3*$N; $y++ )
            {
                if ( $board [$x][$y] == null )
                    break;

                if ( $board [$x][$y] == $black_player_id )
                {
                    $last_occupied_black_column = $x;

                    break;
                }
            }
            
            if ( $last_occupied_black_column >= 0 )
                break;
        }
        */
       

        //
        // Add up total checker distances from last occupied column
        //

        $red_player_distance = 0;
        $black_player_distance = 0;

        
        for ( $x = 0; $x < $N; $x++ )
        {
            for ( $y = 0; $y < 3*$N; $y++ )
            {
                if ( $board [$x][$y] == null )
                    break;

                if ( $board [$x][$y] == $red_player_id )
                {
                    //$red_player_distance += $last_occupied_red_column - $x;
                    $red_player_distance += $N - 1 - $x;
                    //$red_player_distance += 5 - $x;

                    /*
                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'Red player distance: ${var_1}'  ), 
                    array(
                        'var_1' => $red_player_distance
                    ) );
                    */
                }
                else
                {
                    //$black_player_distance += $x - $last_occupied_black_column;
                    $black_player_distance += $x;

                    /*
                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'Black player distance: ${var_1}'  ), 
                    array(
                        'var_1' => $black_player_distance,
                    ) );
                    */
                }                
            }

            //if ( $board [$x][$y] == null )
            //    break;
        }


        if ( $N == 6 )
            $starting_distance = 27;
        else
            $starting_distance =48;

        $lesser_count = min ( $red_player_distance, $black_player_distance );

        return ( $starting_distance - $lesser_count ) / $starting_distance * 100;

    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

function getBoardSize(){
    $N=self::getGameStateValue("board_size");
    return $N !== 0 ? $N : 6;
}    
    
function getMovableCheckers( $player_id )
{
    $result = array();

    $board = self::getBoard();
                                                                                // Coords Red's perspective.  (0,0) lower left

    $player_color = self::getPlayerColor( $player_id );
    
    $N=$this->getBoardSize();


    if (  $player_color == 'ff0000' )
    {
        $start_x_index = 0;
        $end_x_index = $N-2;
        //$end_x_index = 4;
    }
    else
    {
        $start_x_index = 1;
        $end_x_index = $N-1;
        //$end_x_index = 5;
    }


    for( $x=$start_x_index; $x<=$end_x_index; $x++ )
    {
        $y_movable = -1;
        
        for( $y=0; $y<3*$N; $y++ )
        //for( $y=0; $y<18; $y++ )
        {
            if ( $board [$x] [$y] == null )
                break;

            if ( $board [$x] [$y] == $player_id )
                $y_movable = $y;
        }

        if ( $y_movable > -1 ) 
        {
            if( ! isset( $result[$x] ) )
            $result[$x] = array();

            $result[$x][$y_movable] = true;
        }
    }


    return $result;
}


function getBoard()
{
    return self::getDoubleKeyCollectionFromDB( "SELECT board_x x, board_y y, board_player player
                                                FROM board", true );
}


function getPlayerColor ( $player_id ) 
{
    $players = self::loadPlayersBasicInfos();

    if (isset ( $players [ $player_id ] ) )
        return $players[ $player_id ][ 'player_color' ];
    else
        return null;
}


function isMovable ( $x, $y, $board, $player_id ) 
{
    $player_color = self::getPlayerColor( $player_id );


    $N=$this->getBoardSize();

    if ( $board [$x][$y] != $player_id )
    {
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'A'  ), 
        array(
        ) );

        return false;    
    }

    
    if ( $player_color == 'ff0000' )
    {
        if ( $x == $N - 1 )
        {
            self::notifyAllPlayers( "backendMessage", clienttranslate( 'B'  ), 
            array(
            ) );
            
            return false;
        }
    }
    else
    {
        if ( $x == 0 )
        {
            self::notifyAllPlayers( "backendMessage", clienttranslate( 'C'  ), 
            array(
            ) );
            
            return false;
        }
    }


    for ( $y_index = $y + 1; $y_index < 3*$N; $y_index++)
    {
        if ( $board [$x][$y_index] == $player_id )
        {
            self::notifyAllPlayers( "backendMessage", clienttranslate( 'D'  ), 
            array(
            ) );
            
            return false;
        }

        if ( $board [$x][$y_index] == null )
            return true;
    }


    return true;
}



//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

function moveChecker( $x, $y )
{

    self::checkAction( 'moveChecker' );  
    
    $active_player_id = self::getActivePlayerId(); 

    $table = $this->getNextPlayerTable();
    $inactive_player_id = $table[$active_player_id];

        
    $board = self::getBoard();


    $N=$this->getBoardSize();

    // Check if movable checker.  Should be top checker
    if (    !self::isMovable ( $x, $y, $board, $active_player_id )    )
    {
        throw new feException( "Checker is not movable." );
    }


    $active_player_color = self::getPlayerColor( $active_player_id );

    if (  $active_player_color == 'ff0000' )
    {
        $x_new = $x + 1;
        $red_player_id = $active_player_id;
        $black_player_id = $inactive_player_id;        
    }
    else
    {
        $x_new = $x - 1;
        $red_player_id = $inactive_player_id;
        $black_player_id = $active_player_id;        
    }

    for ($y_new = 0; $y_new < 3*$N; $y_new++)
    {
        if ( $board [$x_new][$y_new] == null )
            break;
    }

    $y_new_index = $y_new;


    for ( $y_index = $y; $y_index < 3*$N; $y_index++ )
    {

        //$checker_id = 12345;


        $sql = "SELECT checker_id from board WHERE board_x = $x AND board_y = $y_index";       
        $result = self::DbQuery( $sql );
        $row = $result -> fetch_assoc();

        $checker_id = $row ["checker_id"];

        if ( $checker_id == null )
        {
            /*
            self::notifyAllPlayers( "backendMessage", clienttranslate( 'Checker ID is null'  ), 
            array(
            ) );
            */

            break;
        }

        /*
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'Checker ID: ${var_1}'  ), 
        array(
            'var_1' => $checker_id
        ) );

        self::notifyAllPlayers( "backendMessage", clienttranslate( 'Set old position to null'  ), 
        array(
        ) );
        */

        $sql = "UPDATE board SET board_player = null, checker_id = null WHERE board_x = $x AND board_y = $y_index"; 
        self::DbQuery( $sql );


        /*
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'Move checker ${var_1} to ${var_2},${var_3}'  ), 
        array(
            'var_1' => $checker_id,
            'var_2' => $x_new,
            'var_3' => $y_new_index
        ) );
        */

        if ( $checker_id < 100 )
        {
            $sql = "UPDATE board SET board_player = $red_player_id, checker_id = $checker_id WHERE board_x = $x_new AND board_y = $y_new_index";
            self::DbQuery( $sql );
        }
        else
        {
            $sql = "UPDATE board SET board_player = $black_player_id, checker_id = $checker_id WHERE board_x = $x_new AND board_y = $y_new_index";
            self::DbQuery( $sql );
        }

        self::notifyAllPlayers( "slideChecker", clienttranslate( '' ), 
        array(
        'checker_id' => $checker_id,
        'x_new' => $x_new,
        'y_new' => $y_new_index
        ) );


        $y_new_index++;
    }



    //
    // Send moves to history panel
    //
    $x_old_display_coord = chr( ord( "A" ) + $x );
    $y_old_display_coord = $y + 1;

    $x_new_display_coord = chr( ord( "A" ) + $x_new );
    $y_new_display_coord = $y_new + 1;


    self::notifyPlayer( $active_player_id, "moveCheckerHistory", clienttranslate( '${active_player_name} 
    ${x_old_display_coord}${y_old_display_coord}-${x_new_display_coord}${y_new_display_coord}' ), 

        array(
        'active_player_id' => $active_player_id,
        'active_player_name' => self::getActivePlayerName(),

        'x_old_display_coord' => $x_old_display_coord,
        'y_old_display_coord' => $y_old_display_coord,

        'x_new_display_coord' => $x_new_display_coord,
        'y_new_display_coord' => $y_new_display_coord

    ) );

    self::notifyPlayer( $inactive_player_id, "moveCheckerHistory", clienttranslate( '${active_player_name} 
    ${x_old_display_coord}${y_old_display_coord}-${x_new_display_coord}${y_new_display_coord}' ), 

        array(
        'inactive_player_id' => $inactive_player_id,
        'active_player_name' => self::getActivePlayerName(),

        'x_old_display_coord' => $x_old_display_coord,
        'y_old_display_coord' => $y_old_display_coord,

        'x_new_display_coord' => $x_new_display_coord,
        'y_new_display_coord' => $y_new_display_coord

    ) );


    $this->gamestate->nextState( 'moveChecker' );
}

    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    function argMovableCheckers()
    {
        return array(
            'movableCheckers' => self::getMovableCheckers ( self::getActivePlayerId() )
        );
    }
    
    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

function stNextPlayer()
{
    
    //
    // Check if player won.  
    // If so, end game.
    //
    $active_player_id = self::getActivePlayerId(); 

    $active_player_color = self::getPlayerColor( $active_player_id );


    $N=$this->getBoardSize();

    $board = self::getBoard();    


    $count_has_begun = false;
        
    $contiguous_stack_count = 0;

    for ( $x = 0; $x < $N; $x++)
    {
        for ( $y = 0; $y < 3*$N; $y++ )
        {
            if ( $board [$x][$y] == $active_player_id )
            {
                $count_has_begun = true;

                $contiguous_stack_count++;

                if ( $contiguous_stack_count == $N*3/2 )
                    break;
            }

            else if ( $board [$x][$y] == null )
                break;

            else if ( $count_has_begun )  // Enemy checker (not active player and not null)
                break;
        }

        if ( $contiguous_stack_count == 9 )  
            break;

        if ( $count_has_begun )  // If count began, break.  If countiguous stack size didn't reach 9, it will not reach 9 on other squares
            break;
    }

    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( 'Final stack count = ${var_1}'  ), 
    array(
        'var_1' => $final_stack_count
    ) );
    */


    if ( $contiguous_stack_count == $N*3/2 )
    {
        $sql = "UPDATE player
        SET player_score = 1 WHERE player_id = $active_player_id";
        self::DbQuery( $sql );


        $newScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );

        self::notifyAllPlayers( "newScores", "", array(
            "scores" => $newScores
        ) );

        $this->gamestate->nextState( 'endGame' );
    }


    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( 'stNextPlayer()'  ), 
    array(
    ) );
    */


    //
    // Active next player
    //
    $active_player_id = self::activeNextPlayer();

    $board = self::getBoard();


    // 
    // Check if active player has any moves available.
    // If so, game state Next turn
    // If not, game state Can't play.
    //
    $active_player_color = self::getPlayerColor( $active_player_id );

    if (  $active_player_color == 'ff0000' )
        $x_index = $N-1;
    else
        $x_index = 0;
        
        
    $checker_count = 0;
        
    for ($y_index = 0; $y_index < 3*$N; $y_index++ )
    {
        if (  $board [$x_index][$y_index] == null )
            break;

        if (  $board [$x_index][$y_index] == $active_player_id )
            $checker_count++;
    }

    if ( $checker_count == $N*3/2 )
    {
        /*
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'stNextPlayer() - Cant play'  ), 
        array(
        ) );
        */
    

        $this->gamestate->nextState( 'cantPlay' );
    }
    else
    {
        /*
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'stNextPlayer() - nextTurn' ), 
        array(
        ) );
        */

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
