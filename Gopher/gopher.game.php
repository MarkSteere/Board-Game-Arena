<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * Gopher implementation : © <Mark Steere> <marksteere@gmail.com>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * gopher.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class Gopher extends Table
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
            //    "my_first_global_variable" => 10,
            //    "my_second_global_variable" => 11,
            //      ...
            //    "my_first_game_variant" => 100,
            //    "my_second_game_variant" => 101,
            //      ...
        ) );        
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "gopher";
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
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );

        $sql = "UPDATE player
                SET player_score = 0 WHERE 1";
                self::DbQuery( $sql );

        /* self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );*/
        self::reloadPlayersBasicInfos();
        
        // Init the board
        $sql = "INSERT INTO board (board_x,board_y,board_player) VALUES ";
        $sql_values = array();
        for( $x=0; $x<=10; $x++ )
        {
            for( $y=0; $y<=10; $y++ )
            {
                $disc_value = "NULL";
                    
                $sql_values[] = "('$x','$y',$disc_value)";
            }
        }

        $sql .= implode( $sql_values, ',' );
        self::DbQuery( $sql );
        
        /************ Start the game initialization *****/

        // Init global values with their initial values
        //self::setGameStateInitialValue( 'my_first_global_variable', 0 );
        
        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_score', 1 );  // Init a player statistics (for all players)  NEVERMIND

        // TODO: setup the initial game situation here
       

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
        $result = array( 'players' => array() );
    
        // Add players specific infos
        $sql = "SELECT player_id id, player_score score ";
        $sql .= "FROM player ";
        $sql .= "WHERE 1 ";
        $dbres = self::DbQuery( $sql );
        while( $player = mysql_fetch_assoc( $dbres ) )
        {
            $result['players'][ $player['id'] ] = $player;
        }
        
        // Get reversi board disc
        $result['board'] = self::getObjectListFromDB( "SELECT board_x x, board_y y, board_player player
                                                       FROM board
                                                       WHERE board_player IS NOT NULL" );
  
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

        $occupiedCells = self::getUniqueValueFromDb( "SELECT COUNT( board_x ) FROM board WHERE board_player IS NOT NULL" );
        
        return round( min ( $occupiedCells /48*100, 99 ) );
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////  

    function getFriendlyConnections ( $x, $y, $player, $board )
    {
        $friendlyConnections = 0;
    
        if( $board[ $x ][ $y ] === null ) // Empty cell
        {
            $directions = array( array( -1, 0 ), array( -1, -1 ), array( 0, -1 ), 
                                 array( 1, 0 ), array( 1, 1), array( 0, 1 ) );
        
            foreach( $directions as $direction )
            {
                $neighbor_x = $x + $direction[0];
                $neighbor_y = $y + $direction[1];

                if ( ( ( $neighbor_x >= 0 && $neighbor_x <= 5 ) && ( $neighbor_y >= 0 && $neighbor_y <= $neighbor_x + 5 ) ) ||  // Neighbor cell is on the board
                     ( ( $neighbor_x >= 6 && $neighbor_x <= 10 ) && ( $neighbor_y >= $neighbor_x - 5 && $neighbor_y <= 10 ) ) )
                {
                    if( $board[ $neighbor_x ][ $neighbor_y ] == $player )
                    {
                        ++$friendlyConnections;
                    }
                }
            }
        }
        else
        {
            $friendlyConnections = -1; // ( $x, $y ) is alreay occupied
        }

        return $friendlyConnections; 
    }
   
    function getEnemyConnections ( $x, $y, $player, $board )
    {
        $enemyConnections = 0;
    
        if( $board[ $x ][ $y ] === null ) // Empty cell
        {
            $directions = array( array( -1, 0 ), array( -1, -1 ), array( 0, -1 ), 
                                 array( 1, 0 ), array( 1, 1), array( 0, 1 ) );
        
            foreach( $directions as $direction )
            {
                $neighbor_x = $x + $direction[0];
                $neighbor_y = $y + $direction[1];

                if ( ( ( $neighbor_x >= 0 && $neighbor_x <= 5 ) && ( $neighbor_y >= 0 && $neighbor_y <= $neighbor_x + 5 ) ) ||  // Neighbor cell is on the board
                     ( ( $neighbor_x >= 6 && $neighbor_x <= 10 ) && ( $neighbor_y >= $neighbor_x - 5 && $neighbor_y <= 10 ) ) )
                 {
                    if( ( $board[ $neighbor_x ][ $neighbor_y ] != NULL ) &&
                        ( $board[ $neighbor_x ][ $neighbor_y ] != $player ) )
                    {
                        ++$enemyConnections;
                    }
                }
            }
        }
        else
        {
            $enemyConnections = -1; // ( $x, $y ) is alreay occupied
        }

        return $enemyConnections; 
    }

    // Get the complete board with a double associative array
    function getBoard()
    {
        return self::getDoubleKeyCollectionFromDB( "SELECT board_x x, board_y y, board_player player
                                                       FROM board", true );
    }

    
    function getPossibleMoves( $player_id )
    {
        $returned_enemy = -1;
        $returned_friendly = -1;
        
        $movesSoFar = 0;

        $result = array();
        
        $board = self::getBoard();

        for( $x=0; $x<=10; $x++ )
        {
            for( $y=0; $y<=10; $y++ )
            {
                if ( ( ( $x <= 5 ) && ( $y <= $x + 5 ) ) ||  // Cell is on the board
                    ( ( $x >= 6 ) && ( $y >= $x - 5 ) ) )
                {
                    if( $board[ $x ][ $y ] !== null )
                        ++$movesSoFar;
                }
            }
        }

        if ( $movesSoFar == 0 ) // If no moves have been made yet, every cell is a possible move
        {   
            for( $x=0; $x<=10; $x++ )
            {
                for( $y=0; $y<=10; $y++ )
                {
                    if ( ( ( $x <= 5 ) && ( $y <= $x + 5 ) ) ||  // Cell is on the board
                         ( ( $x >= 6 ) && ( $y >= $x - 5 ) ) )
                    {
                        if( ! isset( $result[$x] ) )
                            $result[$x] = array();

                            $result[$x][$y] = true;
                    }
                }
            }
        }
        else // Board not empty.  Find number of friendly and enemy connections.
        {
            for( $x=0; $x<=10; $x++ )
            {
                for( $y=0; $y<=10; $y++ )
               {
                    if ( ( ( $x <= 5 ) && ( $y <= $x + 5 ) ) ||  // Cell is on the board
                         ( ( $x >= 6 ) && ( $y >= $x - 5 ) ) )
                    {

                        $returned_enemy = self::getEnemyConnections ( $x, $y, $player_id, $board );
                        $returned_friendly = self::getFriendlyConnections ( $x, $y, $player_id, $board );

                        if ( ( $returned_enemy == 1 ) && ( $returned_friendly == 0 ) )
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
    

    //////////////////////////////////////////////////////////////////////////////
    //////////// Player actions
    //////////// 
    function playDisc( $x, $y )
    {
        $movesSoFar = 0;

        $x_display_coord = chr( ord( "A" ) + $x );
        $y_display_coord = 11 - $y;

        // Check that this player is active and that this action is possible at this moment
        self::checkAction( 'playDisc' );  
    
        $player_id = self::getActivePlayerId(); 
    
        // Now, check if this is a possible move

        $board = self::getBoard();

        for( $u=0; $u<=10; $u++ )
        {
            for( $v=0; $v<=10; $v++ )
            {
                if ( ( ( $u <= 5 ) && ( $v <= $u + 5 ) ) ||  // Cell is on the board
                    ( ( $u >= 6 ) && ( $v >= $u - 5 ) ) )
                {
                    if( $board[ $u ][ $v ] !== null )
                        ++$movesSoFar;
                }
            }
        }

        if ( $movesSoFar == 0 ) // If no moves have been made yet, every cell is a possible move
        {
            $sql = "UPDATE board SET board_player='$player_id'
                    WHERE ( board_x, board_y) = ('$x','$y')";  

            self::DbQuery( $sql );
        
            // Notify
            self::notifyAllPlayers( "playDisc", clienttranslate( '${player_name} plays ${x_display_coord} ${y_display_coord}.' ), array(
                'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(),
                'x' => $x,
                'y' => $y,
                'x_display_coord' => $x_display_coord,
                'y_display_coord' => $y_display_coord
            ) );

            // Then, go to the next state
            $this->gamestate->nextState( 'playDisc' );
        }
        else if ( ( ( $returned_enemy = self::getEnemyConnections ( $x, $y, $player_id, $board ) ) == 1 ) && 
                    ( ($returned_friendly = self::getFriendlyConnections ( $x, $y, $player_id, $board ) ) == 0 ) )
        {
            $sql = "UPDATE board SET board_player='$player_id'
                    WHERE ( board_x, board_y) = ('$x','$y')";  

            self::DbQuery( $sql );
        
            // Notify
            self::notifyAllPlayers( "playDisc", clienttranslate( '${player_name} plays ${x_display_coord} ${y_display_coord}.' ), array(
                'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(),
                'x' => $x,
                'y' => $y,
                'x_display_coord' => $x_display_coord,
                'y_display_coord' => $y_display_coord
            ) );

            // Then, go to the next state
            $this->gamestate->nextState( 'playDisc' );
        }
        else
            throw new feException( "Impossible move" );
    }


   
    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state arguments
    ////////////
    function argPlayerTurn()
    {
        return array(
            'possibleMoves' => self::getPossibleMoves( self::getActivePlayerId() )
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
        $player_id = self::activeNextPlayer();


        // Can this player play?
        $possibleMoves = self::getPossibleMoves( $player_id );

        //if( false )
        if( count( $possibleMoves ) == 0 )
        {
            $sql = "UPDATE player
                    SET player_score = 1 WHERE player_id <> $player_id";
                    self::DbQuery( $sql );

            $newScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );
            self::notifyAllPlayers( "newScores", "", array(
                "scores" => $newScores
            ) );

            $this->gamestate->nextState( 'endGame' );
        }
        else
        {
            // This player can play. Give him some extra time
            self::giveExtraTime( $player_id );
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
