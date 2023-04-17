<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * Hadron implementation : © <Mark Steere> <marksteere@gmail.com>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * hadron.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class Hadron extends Table
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
            "red_tile_id" => 20,                 
            "blue_tile_id" => 21,                 
            "placed_red_tiles" => 26,         
            "placed_blue_tiles" => 27,        
            "last_move_x" => 30,                    
            "last_move_y" => 31,                    
            "last_move_id" => 32,                  
            "board_size" => 101,                   
        ) );        
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "hadron";
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
        self::setGameStateValue( "red_tile_id", 10000 );
        self::setGameStateValue( "blue_tile_id", 20000 );
        self::setGameStateValue( "placed_red_tiles", 0 );
        self::setGameStateValue( "placed_blue_tiles", 0 );

        self::setGameStateValue( "last_move_x", 0 );
        self::setGameStateValue( "last_move_y", 0 );
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
            // FOR TEST BOARD FILL
            //
            if( $color == 'dc0000' )    // RED PLAYER
                $red_player_id = $player_id;
            else
                $blue_player_id = $player_id;

        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );

        //self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();
        
        //
        // Load the board with NULL values 
        //
        self::initializeBoard ( );


        //
        //  TEMPORARY - ADD RED AND BLUE TILES TO TEST GAME 
        //
        /*
        $red_tile_id = self::getGameStateValue( "red_tile_id" );    
        self::incGameStateValue( "red_tile_id", 1 );
        self::incGameStateValue( "placed_red_tiles", 1 );
                
        $sql = "UPDATE board SET board_player = $red_player_id, tile_id = $red_tile_id WHERE ( board_x, board_y ) = ( 1, 4 )";  
    
        self::DbQuery( $sql );

   
        $red_tile_id = self::getGameStateValue( "red_tile_id" );    
        self::incGameStateValue( "placed_red_tiles", 1 );
        self::incGameStateValue( "red_tile_id", 1 );
                
        $sql = "UPDATE board SET board_player = $red_player_id, tile_id = $red_tile_id WHERE ( board_x, board_y ) = ( 2, 3 )";  
    
        self::DbQuery( $sql );

   
        $red_tile_id = self::getGameStateValue( "red_tile_id" );    
        self::incGameStateValue( "placed_red_tiles", 1 );
        self::incGameStateValue( "red_tile_id", 1 );
                
        $sql = "UPDATE board SET board_player = $red_player_id, tile_id = $red_tile_id WHERE ( board_x, board_y ) = ( 2, 1 )";  
    
        self::DbQuery( $sql );

   
        $red_tile_id = self::getGameStateValue( "red_tile_id" );    
        self::incGameStateValue( "red_tile_id", 1 );
        self::incGameStateValue( "placed_red_tiles", 1 );
                
        $sql = "UPDATE board SET board_player = $red_player_id, tile_id = $red_tile_id WHERE ( board_x, board_y ) = ( 4, 1 )";  
    
        self::DbQuery( $sql );


   
        $blue_tile_id = self::getGameStateValue( "blue_tile_id" );    
        self::incGameStateValue( "blue_tile_id", 1 );
        self::incGameStateValue( "placed_blue_tiles", 1 );
                
        $sql = "UPDATE board SET board_player = $blue_player_id, tile_id = $blue_tile_id WHERE ( board_x, board_y ) = ( 0, 0 )";  
    
        self::DbQuery( $sql );


        $blue_tile_id = self::getGameStateValue( "blue_tile_id" );    
        self::incGameStateValue( "blue_tile_id", 1 );
        self::incGameStateValue( "placed_blue_tiles", 1 );
                
        $sql = "UPDATE board SET board_player = $blue_player_id, tile_id = $blue_tile_id WHERE ( board_x, board_y ) = ( 0, 3 )";  
    
        self::DbQuery( $sql );


        $blue_tile_id = self::getGameStateValue( "blue_tile_id" );    
        self::incGameStateValue( "blue_tile_id", 1 );
        self::incGameStateValue( "placed_blue_tiles", 1 );
                
        $sql = "UPDATE board SET board_player = $blue_player_id, tile_id = $blue_tile_id WHERE ( board_x, board_y ) = ( 1, 2 )";  
    
        self::DbQuery( $sql );


        $blue_tile_id = self::getGameStateValue( "blue_tile_id" );    
        self::incGameStateValue( "blue_tile_id", 1 );
        self::incGameStateValue( "placed_blue_tiles", 1 );
                
        $sql = "UPDATE board SET board_player = $blue_player_id, tile_id = $blue_tile_id WHERE ( board_x, board_y ) = ( 4, 3 )";  
    
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
        $sql = "SELECT board_x x, board_y y, board_player player, tile_id tile_id
                FROM board WHERE board_player IS NOT NULL";

        $result['board'] = self::getObjectListFromDB( $sql );


        //
        // Placed tiles
        //
        $activePlayerId = self::getActivePlayerId();
        $otherPlayerId = self::getOtherPlayerId($activePlayerId);

        $active_player_color = self::getPlayerColor ( $activePlayerId );

        $placedTiles = array();

        if ( $active_player_color == "dc0000" )     // RED PLAYER
        {
            $placedTiles[$activePlayerId] = self::getGameStateValue( "placed_red_tiles" );
            $placedTiles[$otherPlayerId] = self::getGameStateValue( "placed_blue_tiles" );
        }
        else                                        // BLUE PLAYER
        {
            $placedTiles[$activePlayerId] = self::getGameStateValue( "placed_blue_tiles" );
            $placedTiles[$otherPlayerId] = self::getGameStateValue( "placed_red_tiles" );
        }
        
        $result['placedTiles'] = $placedTiles;



        //
        // LAST MOVE INDICATOR 
        //
        $last_move = array ( );

        $last_move [ 0 ] = self::getGameStateValue( "last_move_x" );

        $last_move [ 1 ] = self::getGameStateValue( "last_move_y" );

        $last_move [ 2 ] = self::getGameStateValue( "last_move_id" );

        $result['last_move'] = $last_move;



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
        $placed_red_tiles = self::getGameStateValue ( "placed_red_tiles");

        $placed_blue_tiles = self::getGameStateValue ( "placed_blue_tiles");

        $total_placed_tiles = $placed_red_tiles + $placed_blue_tiles;


        $N = self::getGameStateValue ( "board_size" );

        switch ( $N )
        {
            case 5:
                $board_area = 25;
                break;

            case 7:
                $board_area = 49;
                break;
        }
            
        return min ( $total_placed_tiles / $board_area * 200, 100 );    //  board_area / 2 .... half the board area
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

//
// GET SELECTABLE SQUARES 
//
function getSelectableSquares ( )
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
            if ( $board [ $x ] [ $y ] == NULL ) // UNOCCUPIED SQUARE
            {
                if ( self::isSelectableSquare ( $x, $y, $board, $N ) )
                {
                    if( ! isset( $result[$x] ) )
                    {
                        $result [ $x ] = array();
                    }

                    $result [ $x ] [ $y ] = true;
                }
            }
        }
    }


    return $result;
}



function isSelectableSquare ( $x, $y, $board, $N )
{
    //
    // First, check if square is occupied                  ####  BUG FIX  ####  Tile was added to same square a second time.
    //
    if ( $board [ $x ] [ $y ] !== NULL)
    {
        return false;
    }


    $directions = array (    array ( -1, 0 ), array ( 0, 1 ), array ( 1, 0 ), array ( 0, -1 )    );


    $total_red_neighbors = 0;
    $total_blue_neighbors = 0;

    foreach ( $directions as $direction ) 
    {
        $neighbor_x = $x + $direction [ 0 ];
        $neighbor_y = $y + $direction [ 1 ];

        if (    self::isSquareOnBoard ( $neighbor_x, $neighbor_y, $N )    )     // NEIGHBOR SQUARE ON BOARD
        {
            $tile_id = self::getTileID ( $neighbor_x, $neighbor_y );

            if ( $tile_id >= 10000 && $tile_id < 20000 )
            {
                ++$total_red_neighbors;
            }
            else if ( $tile_id >= 20000 && $tile_id < 30000 )
            {
                ++$total_blue_neighbors;
            }
        }
    }

    if ( $total_red_neighbors == 0 && $total_blue_neighbors == 0 )  // 0 RED AND 0 BLUE
    {
        return true;
    }
    else if ( $total_red_neighbors == 1 && $total_blue_neighbors == 1 )  // 1 RED AND 1 BLUE
    {
        return true;
    }
    else if ( $total_red_neighbors == 2 && $total_blue_neighbors == 2 )  // 2 RED AND 2 BLUE
    {
        return true;
    }


    return false;
}



function getTileID ( $x, $y )
{      
    $sql = "SELECT tile_id tile_id FROM board WHERE ( board_x, board_y) = ( $x, $y )"; // should be only one

    $result = self::DbQuery( $sql );


    if ( $result->num_rows > 0 ) 
    {
        while (    $row = $result->fetch_assoc()    ) 
        {
            $tile_IDs [ ] = $row [ "tile_id" ];
        }
    } 

    $tile_id = $tile_IDs [0];

    return $tile_id;
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




function getBoard()
{
    return self::getDoubleKeyCollectionFromDB( "SELECT board_x x, board_y y, board_player player
                                                FROM board", true );
}


public function getOtherPlayerId($player_id)
{
    $sql = "SELECT player_id FROM player WHERE player_id != $player_id";
    return $this->getUniqueValueFromDB( $sql );
}


function getColorName( $color )
{
        if ($color=='dc0000')
        {
            return clienttranslate('RED');
        }
        else 
        {
            return clienttranslate('BLUE');
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

        'placed_tiles' => $player_panel_str_0
	) );

	self::notifyAllPlayers( "swapColors", clienttranslate( '${player_name} is now playing <span style="color:#${player_color};">${player_colorname}</span>.' ), array(
		'i18n' => array( 'player_colorname' ),
		'player_id' => $player[1]['id'],
		'player_name' => $player[1]['name'],
		'player_color' => $player[0]['color'],
		'player_colorname' => self::getColorName($player[0]['color']),

        'placed_tiles' => $player_panel_str_1
	) );


	//Update player info
	self::reloadPlayersBasicInfos();

}





//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

//
//  SELECT SQUARE 
//
//
//  If it's legal move
//      Mark as selected in database 
//      Notify frontend to add a tile
//      State change: selectSquare => nextPlayer
//      
function selectSquare ( $x, $y )
{
    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( 'function selectSquare ( $x, $y )' ), 
        array(
         ) );
    */

    self::checkAction( 'selectSquare' );  
    
    $active_player_id = self::getActivePlayerId(); 

    $player_color = self::getPlayerColor ( $active_player_id );

        
    $board = self::getBoard();

	//Get the board size
    $N=self::getGameStateValue("board_size");



    //
    // CHECK IF THIS IS A SELECTABLE SQUARE 
    //
    if (    self::isSelectableSquare ( $x, $y, $board, $N )    )
    {
        /*
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'Is selectable square' ), 
            array(
            ) );
        */

        //
        // PLACE TILE 
        //
        //     UPDATE GLOBALS 
        //
        //     UPDATE DATABSE 
        //
        if (    $player_color == "dc0000"    )  // RED PLAYER
        {
            $tile_id_value = self::getGameStateValue( "red_tile_id" );
            self::incGameStateValue( "red_tile_id", 1 );

            self::incGameStateValue( "placed_red_tiles", 1 );
        }
        else 
        {
            $tile_id_value = self::getGameStateValue( "blue_tile_id" );
            self::incGameStateValue( "blue_tile_id", 1 );

            self::incGameStateValue( "placed_blue_tiles", 1 );
        }


        $sql = "UPDATE board SET board_player = $active_player_id, tile_id = $tile_id_value
                WHERE ( board_x, board_y) = ($x, $y)";  
    
        self::DbQuery( $sql );



        //
        // NOTIFY FRONTEND ABOUT PLACED TILE 
        //
        self::notifyAllPlayers( "tilePlaced", clienttranslate( '' ), 
            array(
            'x' => $x,
            'y' => $y,
            'player_id' => $active_player_id,
            'tile_id' => $tile_id_value
            ) );
    



        //
        // NOTIFY FRONTEND ABOUT LAST MOVE INDICATOR
        //
        self::setGameStateValue ("last_move_x", $x);
        self::setGameStateValue ("last_move_y", $y);

        self::incGameStateValue ("last_move_id", 1);


        self::notifyAllPlayers( "lastMoveIndicator", clienttranslate( '' ), 
            array(
            'x' => $x,
            'y' => $y,
            'id' => self::getGameStateValue ("last_move_id")
            ) );


    
        //
        // UPDATE PLAYER PANEL TO SHOW TOTAL NUMBER OF PLACED TILES
        //
        $placed_red_tiles = self::getGameStateValue ( "placed_red_tiles");
        $placed_red_tiles_str = "{$placed_red_tiles}";

        $placed_blue_tiles = self::getGameStateValue ( "placed_blue_tiles");
        $placed_blue_tiles_str = "{$placed_blue_tiles}";


        if (    $player_color == "dc0000"   )    // RED PLAYER IS ACTIVE
        {
            self::notifyAllPlayers("playerPanel",
                "",
                array(
                "placed_tiles" => $placed_red_tiles_str,
                "player_id" => $active_player_id ));

            self::notifyAllPlayers("playerPanel",
                "",
                array(
                "placed_tiles" => $placed_blue_tiles_str,
                "player_id" => self::getOtherPlayerId($active_player_id) ));
        }
        else                                            // BLUE PLAYER IS ACTIVE 
        {
            self::notifyAllPlayers("playerPanel",
                "",
                array(
                "placed_tiles" => $placed_blue_tiles_str,
                "player_id" => $active_player_id ));

            self::notifyAllPlayers("playerPanel",
                "",
                array(
                "placed_tiles" => $placed_red_tiles_str,
                "player_id" => self::getOtherPlayerId($active_player_id) ));
        }



        //
        // UPDATE MOVE HISTORY PANEL 
        //
        $x_display_coord = chr( ord( "A" ) + $x );
        $y_display_coord = $y + 1;

        self::notifyAllPlayers( "tilePlacedHistory", clienttranslate( '${player_name} 
            ${x_display_coord}${y_display_coord}' ),     
            array(
                'player_name' => self::getActivePlayerName(),
                'x_display_coord' => $x_display_coord,
                'y_display_coord' => $y_display_coord
            ) );



        //
        // ADVANCE STATE MACHINE
        //
        //      CHECK IF FIRST MOVE
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
            $this->gamestate->nextState( 'selectSquare' );
        }
    }
    else
        throw new feException( "Not a valid square." );
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

function argSelectSquare()
{
    return array(
        'selectableSquares' => self::getSelectableSquares ( )
    );
}


//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

function stNextPlayer()
{
    // Active next player
    $active_player_id = self::activeNextPlayer();


    //self::giveExtraTime( $active_player_id );
    //$this->gamestate->nextState( 'nextTurn' );

    //
    // ARE THERE ANY LEGAL MOVES AVAILABLE?
    //
    $selectableSquares = self::getSelectableSquares ( );

    
    if( count( $selectableSquares ) == 0 )
    {

        $sql = "UPDATE player SET player_score = 1 WHERE player_id != $active_player_id";
                    
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



    function initializeBoard ( )
    {
        $N=self::getGameStateValue("board_size");


        $sql = "INSERT INTO board (board_x, board_y, board_player, tile_id ) VALUES ";

        $sql_values = array();

        for ( $x = 0; $x < $N; $x++ )
        {
            for ( $y = 0; $y <= $N; $y++ )
            {
                $sql_values[] = "($x, $y, NULL, NULL)";                   
            }
        }

        $sql .= implode( $sql_values, ',' );
        self::DbQuery( $sql );
    }

}


            /*
            self::notifyAllPlayers( "backendMessage", clienttranslate( '${var_1}, ${var_2}, ${var_3, ${var_4}}, ${var_5} ' ), 
                array(
                    "var_1" => $x,
                    "var_2" => $y,
                    "var_3" => $neighbor_x,
                    "var_4" => $neighbor_y,
                    "var_5" => $tile_id
                ) );
            */
