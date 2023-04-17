<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * Fractal implementation : © <Mark Steere> <marksteere@gmail.com>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * fractal.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class Fractal extends Table
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
            "total_black_tiles"=> 22,               // Total number of black tiles
            "total_green_tiles"=> 23,               // Total number of green tiles
        ) );        
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "fractal";
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
        self::setGameStateValue( "total_black_tiles", 0 );
        self::setGameStateValue( "total_green_tiles", 0 );

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
            if( $color == '000000' )
                $black_player_id = $player_id;
            else
                $green_player_id = $player_id;


        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );

        // self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );

        self::reloadPlayersBasicInfos();
        


        // 
        // INITIALIZE DATABASE 
        // 
        //      SET ALL TILE VALUES 
        //
        self::initializeDatabase ( );









        //
        //  BUG
        //      NOT RECOGNIZING WIN FOR GREEN 
        //      SET UP BUGGY FINAL POSITION WITH GREEN TILES EXCEPT FOR LAST MOVE OF GREEN
        //
        //$sql = "UPDATE board SET board_player = $green_player_id WHERE ( board_u, board_v ) = ( 1001, 1004 )";  // 
        //self::DbQuery( $sql );

        //$sql = "UPDATE board SET board_player = $green_player_id WHERE ( board_u, board_v ) = ( 1000, 1003 )";  // 
        //self::DbQuery( $sql );



        /*
        $sql = "UPDATE board SET board_player = $green_player_id WHERE ( board_u, board_v ) = ( 1001, 1001 )";  // 
        self::DbQuery( $sql );




        $sql = "UPDATE board SET board_player = $green_player_id WHERE ( board_u, board_v ) = ( 1002, 1000 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $green_player_id WHERE ( board_u, board_v ) = ( 1004, 1001 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $green_player_id WHERE ( board_u, board_v ) = ( 1004, 1002 )";  // 
        self::DbQuery( $sql );
        */

        //$sql = "UPDATE board SET board_player = $green_player_id WHERE ( board_u, board_v ) = ( 101, 105 )";  // 
        //self::DbQuery( $sql );

        /*
        $sql = "UPDATE board SET board_player = $green_player_id WHERE ( board_u, board_v ) = ( 103, 107 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $green_player_id WHERE ( board_u, board_v ) = ( 103, 106 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $green_player_id WHERE ( board_u, board_v ) = ( 103, 105 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $green_player_id WHERE ( board_u, board_v ) = ( 103, 104 )";  // 
        self::DbQuery( $sql );
        */



        /*
        $sql = "UPDATE board SET board_player = $green_player_id WHERE ( board_u, board_v ) = ( 104, 103 )";  // 
        self::DbQuery( $sql );





        $sql = "UPDATE board SET board_player = $green_player_id WHERE ( board_u, board_v ) = ( 105, 102 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $green_player_id WHERE ( board_u, board_v ) = ( 105, 101 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $green_player_id WHERE ( board_u, board_v ) = ( 105, 103 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $green_player_id WHERE ( board_u, board_v ) = ( 106, 103 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $green_player_id WHERE ( board_u, board_v ) = ( 107, 103 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $green_player_id WHERE ( board_u, board_v ) = ( 108, 102 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $green_player_id WHERE ( board_u, board_v ) = ( 109, 101 )";  // 
        self::DbQuery( $sql );
        */






        //
        //  TEMPORARY TEST VALUES 
        //
        /*
        $sql = "UPDATE board SET board_player = $black_player_id WHERE ( board_u, board_v ) = ( 1000, 1004 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $green_player_id WHERE ( board_u, board_v ) = ( 1002, 1004 )";  // 
        self::DbQuery( $sql );
        */

        /*
        $sql = "UPDATE board SET board_player = $green_player_id WHERE ( board_u, board_v ) = ( 103, 106 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $green_player_id WHERE ( board_u, board_v ) = ( 103, 105 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $black_player_id WHERE ( board_u, board_v ) = ( 107, 105 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $black_player_id WHERE ( board_u, board_v ) = ( 107, 104 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $black_player_id WHERE ( board_u, board_v ) = ( 106, 103 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $black_player_id WHERE ( board_u, board_v ) = ( 105, 103 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $black_player_id WHERE ( board_u, board_v ) = ( 104, 104 )";  // 
        self::DbQuery( $sql );
        */


        //
        // CHANGE TILE NUMBER SO GOES LEFT TO RIGHT ACROSS THE TABLE FOR ALIGNMENT PURPOSES
        //

        // horizontal align
        /*
        $sql = "UPDATE board SET board_player = $green_player_id, tile_info = '1+45' WHERE ( board_u, board_v ) = ( 0, 6 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $green_player_id, tile_info = '1+46' WHERE ( board_u, board_v ) = ( 1, 6 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $green_player_id, tile_info = '1+47' WHERE ( board_u, board_v ) = ( 2, 6 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $green_player_id, tile_info = '1+57' WHERE ( board_u, board_v ) = ( 7, 6 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $green_player_id, tile_info = '1+58' WHERE ( board_u, board_v ) = ( 8, 6 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $green_player_id, tile_info = '1+59' WHERE ( board_u, board_v ) = ( 9, 6 )";  // 
        self::DbQuery( $sql ); 

        // vertical align
        $sql = "UPDATE board SET board_player = $green_player_id, tile_info = '1+4' WHERE ( board_u, board_v ) = ( 1, 10 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $green_player_id, tile_info = '1+19' WHERE ( board_u, board_v ) = ( 2, 8 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $black_player_id, tile_info = '1+49' WHERE ( board_u, board_v ) = ( 5, 2 )";  // 
        self::DbQuery( $sql );

        $sql = "UPDATE board SET board_player = $black_player_id, tile_info = '1+64' WHERE ( board_u, board_v ) = ( 6, 0 )";  // 
        self::DbQuery( $sql );
        */

        //
        //
        //
        // SHOW WHATS IN THE DATABASE AT THIS POINT ##########################################################################################
        //
        //
        /*
        $sql = "SELECT board_u u, board_v v, board_player board_player, tile_info tile_info FROM board WHERE 1";

        $result = self::DbQuery( $sql );

        if ( $result->num_rows > 0 ) 
        {
             // output data of each row
            while (    $row = $result->fetch_assoc()    ) 
            {
                self::notifyAllPlayers( "backendMessage", clienttranslate( '${var_1}' ), 
                    array(
                        "var_1" => $row["u"]." ".$row["v"]." ".$row["board_player"]." ". $row["tile_info"]
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
        $sql = "SELECT board_u u, board_v v, board_player player, tile_info tile_info
                    FROM board
                    WHERE board_player IS NOT NULL";
        $result['board'] = self::getObjectListFromDB( $sql );
 
        //
        // Placed tiles
        //
        $activePlayerId = self::getActivePlayerId();
        $otherPlayerId = self::getOtherPlayerId($activePlayerId);

        $active_player_color = self::getPlayerColor ( $activePlayerId );


        $placedTiles = array();

        if (    $active_player_color == "000000"    )
        {
            $placedTiles[$activePlayerId] = self::getGameStateValue( "total_black_tiles" );
            $placedTiles[$otherPlayerId] = self::getGameStateValue( "total_green_tiles" );
        }
        else
        {
            $placedTiles[$activePlayerId] = self::getGameStateValue( "total_green_tiles" );
            $placedTiles[$otherPlayerId] = self::getGameStateValue( "total_black_tiles" );
        }
        
        $result['placedTiles'] = $placedTiles;
  
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
        $total_black_tiles = self::getGameStateValue ( "total_black_tiles");

        $total_green_tiles = self::getGameStateValue ( "total_green_tiles");

        $total_tiles = $total_black_tiles + $total_green_tiles;

            
        return min ( $total_tiles / 151 * 400, 100 );  // board a quarter filled
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    


function getSelectableCells ( $active_player_id )
{                       
    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( '###### getSelectableCells ( ) ######' ), 
    array(
    ) );
    */

    //$inactive_player_id = self::getOtherPlayerId ( $active_player_id );


    $legal_moves = array ();


    $board = self::getBoard ();

    $tile_information = self::getAllTileInfo ();

    //
    // LARGE CELLS 
    //
    for ( $i = 1000; $i < 1005; $i++ )
    {
        for ( $j = 1000; $j < 1005; $j++ )
        {
            if (  isset ( $tile_information [ $i ] [ $j ] )    )
            {
                /*
                self::notifyAllPlayers( "backendMessage", clienttranslate( 'isset ( $tile_information [ $i ] [ $j ] )' ), 
                    array(
                    ) );
                */

                if ( self::getOccupant ( $i, $j, $board ) == NULL )
                {
                    $legal_moves [ $i ] [ $j ] = 1;
                    
                    /*
                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'legal move: ${var_1}, ${var_2}' ), 
                        array(
                            "var_1" => $i,
                            "var_2" => $j
                        ) );
                    */
                }
            }
        }
    }

    //
    // MEDIUM CELLS 
    //
    for ( $i = 100; $i < 111; $i++ )
    {
        for ( $j = 100; $j < 111; $j++ )
        {
            if (  isset ( $tile_information [ $i ] [ $j ] )    )
            {
                /*
                self::notifyAllPlayers( "backendMessage", clienttranslate( 'isset ( $tile_information [ $i ] [ $j ] )' ), 
                    array(
                    ) );
                */

                if ( self::getOccupant ( $i, $j, $board ) == NULL )
                {
                    $legal_moves [ $i ] [ $j ] = 1;
                    
                    /*
                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'legal move: ${var_1}, ${var_2}' ), 
                        array(
                            "var_1" => $i,
                            "var_2" => $j
                        ) );
                    */
                }
            }
        }
    }

    //
    // SMALL CELLS 
    //
    for ( $i = 0; $i < 11; $i++ )
    {
        for ( $j = 0; $j < 11; $j++ )
        {
            if (  isset ( $tile_information [ $i ] [ $j ] )    )
            {
                /*
                self::notifyAllPlayers( "backendMessage", clienttranslate( 'isset ( $tile_information [ $i ] [ $j ] )' ), 
                    array(
                    ) );
                */

                if ( self::getOccupant ( $i, $j, $board ) == NULL )
                {
                    $legal_moves [ $i ] [ $j ] = 1;
                    
                    /*
                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'legal move: ${var_1}, ${var_2}' ), 
                        array(
                            "var_1" => $i,
                            "var_2" => $j
                        ) );
                    */
                }
            }
        }
    }

    return ( $legal_moves );
}



function getOccupant ( $u, $v, $board )
{                       
    if (    isset ( $board [ $u ] [ $v ] )    )
    {
        return $board [ $u ] [ $v ];
    }
    else 
    {
        return NULL;
    }
}



function getBoard()
{
    return self::getDoubleKeyCollectionFromDB( "SELECT board_u u, board_v v, board_player player
                                                FROM board", true );
}



function getAllTileInfo()
{
    return self::getDoubleKeyCollectionFromDB( "SELECT board_u u, board_v v, tile_info tile_info
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
        else if ($color=='00a400')
        {
            return clienttranslate('GREEN');
        }
        else 
        {
            return NULL;
        }
} 


function getPlayerColor ( $player_id ) 
{
    $players = self::loadPlayersBasicInfos();

    if (isset ( $players [ $player_id ] ) )
    {
        return $players[ $player_id ][ 'player_color' ];
    }
    else
    {
        return null;
    }
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

function placeTile ( $u, $v )
{    
    self::checkAction( 'placeTile' );  

    $board = self::getBoard();

    $active_player_id = self::getActivePlayerId(); 
    $inactive_player_id = self::getOtherPlayerId ( $active_player_id );

    $active_player_color = self::getPlayerColor ( $active_player_id );
    $inactive_player_color = self::getPlayerColor ( $inactive_player_id );


    $tile_info = self::getOneTileInfo ($u, $v);


    $legal_moves = self::getSelectableCells ( $active_player_id );

    if (    $legal_moves [ $u ] [ $v ] == true    ) 
    {
        //
        // PLACE TILE 
        //
        //      Update globals and database 
        //
        if (    $active_player_color == "000000"    )
        {
            self::incGameStateValue( "total_black_tiles", 1 );
        }
        else 
        {
            self::incGameStateValue( "total_green_tiles", 1 );
        }


        $sql = "UPDATE board SET board_player = $active_player_id WHERE ( board_u, board_v) = ($u, $v)";  
    
        self::DbQuery( $sql );

        //
        // Notify about placed tile 
        //
        self::notifyAllPlayers( "tilePlaced", clienttranslate( '' ), 
            array(
            'u' => $u,
            'v' => $v,
            'player_id' => $active_player_id,
            'tile_info' => $tile_info
             ) );

        //
        // Update the playerPanel to display the players' number of placed checkers
        //
        $total_black_tiles = self::getGameStateValue ( "total_black_tiles");
        $total_black_tiles_str = "{$total_black_tiles}";

        $total_green_tiles = self::getGameStateValue ( "total_green_tiles");
        $total_green_tiles_str = "{$total_green_tiles}";

        if (    $active_player_color == "000000"   ) // Black player is active
        {
            self::notifyAllPlayers("playerPanel",
                "",
                array(
                "placed_tiles" => $total_black_tiles_str,
                "player_id" => $active_player_id ));

            self::notifyAllPlayers("playerPanel",
                "",
                array(
                "placed_tiles" => $total_green_tiles_str,
                "player_id" => self::getOtherPlayerId($active_player_id) ));
        }
        else 
        {
            self::notifyAllPlayers("playerPanel",
                "",
                array(
                "placed_tiles" => $total_green_tiles_str,
                "player_id" => $active_player_id ));

            self::notifyAllPlayers("playerPanel",
                "",
                array(
                "placed_tiles" => $total_black_tiles_str,
                "player_id" => self::getOtherPlayerId($active_player_id) ));
        }


        $u_display_coord = $u;
        $v_display_coord = $v;

        self::notifyAllPlayers( "tilePlacedHistory", clienttranslate( '${active_player_name} 
            ${u_display_coord}, ${v_display_coord}' ),     
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
            $this->gamestate->nextState( 'placeTile' );
        }
    }
    else
    {
        throw new feException( "Cell is not selectable." );
    }
}






//  didActivePlayerWin ( )
//  ######################
//
//
//  GROUP IDs ARRAY
//
//  Give each active player's stone on the board a group ID.  I.e., all stones that are part of the same group have the same group ID. 
//      $group_IDs_active
//
//  To accomplish this, visit every cell on the board.  
//
//  Indexed arrays  (not key-value)
//      group_IDs_active [][] 
//
//  If the current cell is occupied by active player
//      If current cell group ID == Null
//          Give current cell fresh group ID
//      Check each adjacent cell (adjacent cell information in tile_info for that cell).  
//          If the neighboring cell is ALSO occupied by active player
//              If neighor cell group ID == NULL 
//                  Give neighbor cell the current cell's group ID
//              Else ( neighbor cell group ID !== NULL )
//                  If the current cell and neighbor cell's group IDs are different
//                      XXXXXXXXXXXXXX Replace all occurrences of the current cell's group ID with the neighbor's group ID XXXXXXXXXXXXX
//                      CHANGE TO .... Replace all occurrences of the neighbor cell's group ID with the current cell's group ID 
//
//
//
//
//
//
//  For each group ID 
//      If the group includes at least one cell in each opposite boundary 
//          Active player wins
//
//
//
function didActivePlayerWin ( )
{   
    $active_player_id = self::getActivePlayerId(); 
    $inactive_player_id = self::getOtherPlayerId ( $active_player_id );

    $active_player_color = self::getPlayerColor ( $active_player_id );
    $inactive_player_color = self::getPlayerColor ( $inactive_player_id );


    $board = self::getBoard();

    $all_tile_info = self::getAllTileInfo ( );


    $group_IDs_active = array (); 

    $fresh_group_id = 1;


    // 
    // LARGE CELLS 
    //
    for ($u = 1000; $u < 1005; $u++)
    {
        for ($v = 1000; $v < 1005; $v++)
        {
            if (    self::isOccupiedByPlayer ( $active_player_id, $u, $v, $board )    )                         // CURRENT CELL OCCUPIED BY ACTIVE PLAYER
            {
                /*
                self::notifyAllPlayers( "backendMessage", clienttranslate( '#### LARGE: ${var_1}, ${var_2}' ), 
                    array(
                      "var_1" => $u,
                      "var_2" => $v
                    ) );
                */

                $tile_info = $all_tile_info [ $u ] [ $v ];

                $tile_info_parts = explode ( "+", $tile_info );

                $adjacent_cells_part = $tile_info_parts [ 2 ];


                if (    self::getGroupID ( $u, $v, $group_IDs_active ) == NULL    )                             // IF NO GROUP ID, GIVE FRESH GROUP ID
                {
                    $group_IDs_active [ $u ] [ $v ] = $fresh_group_id++;
                }

                $current_group_id_active = $group_IDs_active [ $u ] [ $v ];                                     // CURRENT CELL GROUP ID

                /*
                self::notifyAllPlayers( "backendMessage", clienttranslate( '#### CURRENT: ${var_1}, ${var_2}, ${var_3}' ), 
                    array(
                      "var_1" => $u,
                      "var_2" => $v,
                      "var_3" => $group_IDs_active [ $u ] [ $v ]
                    ) );
                */


                //
                // ADJACENT CELLS ARRAY 
                //
                $adjacent_cells = array ( );

                // 
                //  Fill in $adjacent_cells array
                // 
                $adjacent_cell_strings = explode ( ",", $adjacent_cells_part );

                foreach ( $adjacent_cell_strings as $adjacent_cell_string  )
                {
                    /*
                    self::notifyAllPlayers( "backendMessage", clienttranslate( '$adjacent_cell_string: ${var_1}' ), 
                        array(
                          "var_1" => $adjacent_cell_string
                        ) );
                    */

                    $adjacent_cell_coords_str = explode ( "_", $adjacent_cell_string );

                    $u_sub = (int) $adjacent_cell_coords_str [ 0 ];

                    $v_sub = (int) $adjacent_cell_coords_str [ 1 ];

                    $adjacent_cells [ ] = array ( $u_sub, $v_sub );
                }


                foreach ( $adjacent_cells as $adjacent_cell  )
                {
                    $u_adj = $adjacent_cell [ 0 ];
                    $v_adj = $adjacent_cell [ 1 ];

                    /*
                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'L adjacent: ${var_1}, ${var_2}' ), 
                        array(
                           "var_1" => $u_adj,
                           "var_2" => $v_adj
                        ) );
                    */
 
                    if (    self::isOccupiedByPlayer ( $active_player_id, $u_adj, $v_adj, $board )    )                 // NEIGHBOR CELL OCCUPIED BY ACTIVE PLAYER
                    {
                        /*
                        self::notifyAllPlayers( "backendMessage", clienttranslate( 'Neighbor cell occupied by active player' ), 
                            array(
                            ) );
                        */
 
                        $neighbor_group_id_active = self::getGroupID ( $u_adj, $v_adj, $group_IDs_active );

                        if ( $neighbor_group_id_active == NULL  )                                                       // NEIGHBOR CELL GROUP ID == NULL
                        {
                            $group_IDs_active [ $u_adj ] [ $v_adj ] = $current_group_id_active;                         // Copy current group ID into it

                            /*
                            self::notifyAllPlayers( "backendMessage", clienttranslate( 'nei gr id: ${var_1}' ), 
                            array(
                                  "var_1" => $group_IDs_active [ $u_adj ] [ $v_adj ]
                                ) );
                            */

                        }
                        else if ( $neighbor_group_id_active !== $current_group_id_active  )                             // NEIGHBOR CELL GROUP ID !== NULL
                        {                                                                                               // AND NEIGHBOR CELL GROUP ID !== CURRENT CELL GROUP ID
                            //
                            // CHANGE ALL OCCURRENCES OF NEIGHBOR GROUP ID TO CURRENT GROUP ID 
                            //

                            // 
                            // Large cells 
                            //
                            for ( $u_sub = 1000; $u_sub < 1005; $u_sub++ )
                            {
                                for ( $v_sub = 1000; $v_sub < 1005; $v_sub++ )
                                {
                                    if (    self::getGroupID ( $u_sub, $v_sub, $group_IDs_active ) == $neighbor_group_id_active     )
                                    {
                                        $group_IDs_active [ $u_sub ] [ $v_sub ] = $current_group_id_active;
                                    }
                                }
                            }

                            // 
                            // Medium cells 
                            //
                            for ( $u_sub = 100; $u_sub < 111; $u_sub++ )
                            {
                                for ( $v_sub = 100; $v_sub < 111; $v_sub++ )
                                {
                                    if (    self::getGroupID ( $u_sub, $v_sub, $group_IDs_active ) == $neighbor_group_id_active     )
                                    {
                                        $group_IDs_active [ $u_sub ] [ $v_sub ] = $current_group_id_active;
                                    }
                                }
                            }

                            // 
                            // Small cells 
                            //
                            for ( $u_sub = 0; $u_sub < 11; $u_sub++ )
                            {
                                for ( $v_sub = 0; $v_sub < 11; $v_sub++ )
                                {
                                    if (    self::getGroupID ( $u_sub, $v_sub, $group_IDs_active ) == $neighbor_group_id_active     )
                                    {
                                        $group_IDs_active [ $u_sub ] [ $v_sub ] = $current_group_id_active;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    


    // 
    // MEDIUM CELLS 
    //
    for ($u = 100; $u < 111; $u++)
    {
        for ($v = 100; $v < 111; $v++)
        {
            if (    self::isOccupiedByPlayer ( $active_player_id, $u, $v, $board )    )                         // CURRENT CELL OCCUPIED BY ACTIVE PLAYER
            {
                /*
                self::notifyAllPlayers( "backendMessage", clienttranslate( '#### MEDIUM: ${var_1}, ${var_2}' ), 
                    array(
                      "var_1" => $u,
                      "var_2" => $v
                    ) );
                */

                $tile_info = $all_tile_info [ $u ] [ $v ];

                $tile_info_parts = explode ( "+", $tile_info );

                $adjacent_cells_part = $tile_info_parts [ 2 ];


                if (    self::getGroupID ( $u, $v, $group_IDs_active ) == NULL    )                             // IF NO GROUP ID, GIVE FRESH GROUP ID
                {
                    $group_IDs_active [ $u ] [ $v ] = $fresh_group_id++;
                }

                $current_group_id_active = $group_IDs_active [ $u ] [ $v ];                                     // CURRENT CELL GROUP ID

                /*
                self::notifyAllPlayers( "backendMessage", clienttranslate( '#### CURRENT: ${var_1}, ${var_2}, ${var_3}' ), 
                    array(
                      "var_1" => $u,
                      "var_2" => $v,
                      "var_3" => $group_IDs_active [ $u ] [ $v ]
                    ) );
                */

                //
                // ADJACENT CELLS ARRAY 
                //
                $adjacent_cells = array ( );

                // 
                //  Fill in $adjacent_cells array
                // 
                $adjacent_cell_strings = explode ( ",", $adjacent_cells_part );

                foreach ( $adjacent_cell_strings as $adjacent_cell_string  )
                {
                    $adjacent_cell_coords_str = explode ( "_", $adjacent_cell_string );

                    $u_sub = (int) $adjacent_cell_coords_str [ 0 ];

                    $v_sub = (int) $adjacent_cell_coords_str [ 1 ];

                    $adjacent_cells [ ] = array ( $u_sub, $v_sub );
                }


                foreach ( $adjacent_cells as $adjacent_cell  )
                {
                    $u_adj = $adjacent_cell [ 0 ];
                    $v_adj = $adjacent_cell [ 1 ];

                    /*
                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'M adjacent: ${var_1}, ${var_2}' ), 
                        array(
                           "var_1" => $u_adj,
                           "var_2" => $v_adj
                        ) );
                    */

                    if (    self::isOccupiedByPlayer ( $active_player_id, $u_adj, $v_adj, $board )    )                 // NEIGHBOR CELL OCCUPIED BY ACTIVE PLAYER
                    { 
                        $neighbor_group_id_active = self::getGroupID ( $u_adj, $v_adj, $group_IDs_active );

                        if ( $neighbor_group_id_active == NULL  )                                                       // NEIGHBOR CELL GROUP ID == NULL
                        {
                            $group_IDs_active [ $u_adj ] [ $v_adj ] = $current_group_id_active;                         // Copy current group ID into it

                            /*
                            self::notifyAllPlayers( "backendMessage", clienttranslate( 'Nebr u, v, Gr id: ${var_1}, ${var_2}, ${var_3} ' ), 
                                array(
                                    "var_1" => $u_adj,
                                    "var_2" => $v_adj,
                                    "var_3" => $group_IDs_active [ $u_adj ] [ $v_adj ]
                                ) );
                            */

                        }
                        else if ( $neighbor_group_id_active !== $current_group_id_active  )                             // NEIGHBOR CELL GROUP ID !== NULL
                        {                                                                                               // AND NEIGHBOR CELL GROUP ID !== CURRENT CELL GROUP ID
                            /*
                            self::notifyAllPlayers( "backendMessage", clienttranslate( '## NEIGHBOR: ${var_1}, ${var_2}, ${var_3} ' ), 
                                array(
                                    "var_1" => $u_adj,
                                    "var_2" => $v_adj,
                                    "var_3" => $group_IDs_active [ $u_adj ] [ $v_adj ]
                                ) );
                            */


                            //
                            // CHANGE ALL OCCURRENCES OF CURRENT GROUP ID TO NEIGHBOR GROUP ID 
                            //

                            // 
                            // Large cells 
                            //
                            for ( $u_sub = 1000; $u_sub < 1005; $u_sub++ )
                            {
                                for ( $v_sub = 1000; $v_sub < 1005; $v_sub++ )
                                {
                                    if (    self::getGroupID ( $u_sub, $v_sub, $group_IDs_active ) == $neighbor_group_id_active     )
                                    {
                                        $group_IDs_active [ $u_sub ] [ $v_sub ] = $current_group_id_active;
                                    }
                                }
                            }

                            // 
                            // Medium cells 
                            //
                            for ( $u_sub = 100; $u_sub < 111; $u_sub++ )
                            {
                                for ( $v_sub = 100; $v_sub < 111; $v_sub++ )
                                {
                                    if (    self::getGroupID ( $u_sub, $v_sub, $group_IDs_active ) == $neighbor_group_id_active     )
                                    {
                                        $group_IDs_active [ $u_sub ] [ $v_sub ] = $current_group_id_active;
                                    }
                                }
                            }

                            // 
                            // Small cells 
                            //
                            for ( $u_sub = 0; $u_sub < 11; $u_sub++ )
                            {
                                for ( $v_sub = 0; $v_sub < 11; $v_sub++ )
                                {
                                    if (    self::getGroupID ( $u_sub, $v_sub, $group_IDs_active ) == $neighbor_group_id_active     )
                                    {
                                        $group_IDs_active [ $u_sub ] [ $v_sub ] = $current_group_id_active;
                                    }
                                }
                            }

                            /*
                            self::notifyAllPlayers( "backendMessage", clienttranslate( 'AFTER COPY: ${var_1}, ${var_2}, ${var_3} ' ), 
                                array(
                                    "var_1" => $u,
                                    "var_2" => $v,
                                    "var_3" => $group_IDs_active [ $u ] [ $v ]
                                ) );
                            */
                        }
                    }
                }
            }
        }
    }
    



    // 
    // SMALL CELLS 
    //
    for ($u = 0; $u < 11; $u++)
    {
        for ($v = 0; $v < 11; $v++)
        {
            if (    self::isOccupiedByPlayer ( $active_player_id, $u, $v, $board )    )                         // CURRENT CELL OCCUPIED BY ACTIVE PLAYER
            {
                $tile_info = $all_tile_info [ $u ] [ $v ];

                $tile_info_parts = explode ( "+", $tile_info );

                $adjacent_cells_part = $tile_info_parts [ 2 ];


                if (    self::getGroupID ( $u, $v, $group_IDs_active ) == NULL    )
                {
                    $group_IDs_active [ $u ] [ $v ] = $fresh_group_id++;
                }

                $current_group_id_active = $group_IDs_active [ $u ] [ $v ];

                //
                // ADJACENT CELLS ARRAY 
                //
                $adjacent_cells = array ( );

                // 
                //  Fill in $adjacent_cells array
                // 
                $adjacent_cell_strings = explode ( ",", $adjacent_cells_part );

                foreach ( $adjacent_cell_strings as $adjacent_cell_string  )
                {
                    $adjacent_cell_coords_str = explode ( "_", $adjacent_cell_string );

                    $u_sub = (int) $adjacent_cell_coords_str [ 0 ];

                    $v_sub = (int) $adjacent_cell_coords_str [ 1 ];

                    $adjacent_cells [ ] = array ( $u_sub, $v_sub );
                }


                foreach ( $adjacent_cells as $adjacent_cell  )
                {
                    $u_adj = $adjacent_cell [ 0 ];
                    $v_adj = $adjacent_cell [ 1 ];

                    if (    self::isOccupiedByPlayer ( $active_player_id, $u_adj, $v_adj, $board )    )                 // NEIGHBOR CELL OCCUPIED BY ACTIVE PLAYER
                    { 
                        $neighbor_group_id_active = self::getGroupID ( $u_adj, $v_adj, $group_IDs_active );

                        if ( $neighbor_group_id_active == NULL  )                                                       // NEIGHBOR CELL GROUP ID == NULL
                        {
                            $group_IDs_active [ $u_adj ] [ $v_adj ] = $current_group_id_active;                         // Copy current group ID into it
                        }
                        else if ( $neighbor_group_id_active !== $current_group_id_active  )                             // NEIGHBOR CELL GROUP ID !== NULL
                        {                                                                                               // AND NEIGHBOR CELL GROUP ID !== CURRENT CELL GROUP ID
                            //
                            // CHANGE ALL OCCURRENCES OF CURRENT GROUP ID TO NEIGHBOR GROUP ID 
                            //

                            // 
                            // Large cells 
                            //
                            for ( $u_sub = 1000; $u_sub < 1005; $u_sub++ )
                            {
                                for ( $v_sub = 1000; $v_sub < 1005; $v_sub++ )
                                {
                                    if (    self::getGroupID ( $u_sub, $v_sub, $group_IDs_active ) == $neighbor_group_id_active     )
                                    {
                                        $group_IDs_active [ $u_sub ] [ $v_sub ] = $current_group_id_active;
                                    }
                                }
                            }

                            // 
                            // Medium cells 
                            //
                            for ( $u_sub = 100; $u_sub < 111; $u_sub++ )
                            {
                                for ( $v_sub = 100; $v_sub < 111; $v_sub++ )
                                {
                                    if (    self::getGroupID ( $u_sub, $v_sub, $group_IDs_active ) == $neighbor_group_id_active     )
                                    {
                                        $group_IDs_active [ $u_sub ] [ $v_sub ] = $current_group_id_active;
                                    }
                                }
                            }

                            // 
                            // Small cells 
                            //
                            for ( $u_sub = 0; $u_sub < 11; $u_sub++ )
                            {
                                for ( $v_sub = 0; $v_sub < 11; $v_sub++ )
                                {
                                    if (    self::getGroupID ( $u_sub, $v_sub, $group_IDs_active ) == $neighbor_group_id_active     )
                                    {
                                        $group_IDs_active [ $u_sub ] [ $v_sub ] = $current_group_id_active;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    







    // 
    // SHOW GROUP IDs
    //
    /*
    for ($u = 0; $u < 1005; $u++)
    {
        for ($v = 0; $v < 1005; $v++)
        {
            if (    self::isOccupiedByPlayer ( $active_player_id, $u, $v, $board )    )                         // CURRENT CELL OCCUPIED BY ACTIVE PLAYER
            {
                self::notifyAllPlayers( "backendMessage", clienttranslate( 'u, v, Gr id: ${var_1}, ${var_2}, ${var_3} ' ), 
                    array(
                        "var_1" => $u,
                        "var_2" => $v,
                        "var_3" => $group_IDs_active [ $u ] [ $v ]
                    ) );
            }
        }
    }
    */






    if ( $active_player_color == '000000' )             // BLACK IS ACTIVE PLAYER
    {
        $northwest_group_IDs = array ( );

        $southeast_group_IDs = array ( );

        //
        // Northwest boundary 
        //
        $group_id = self::getGroupID ( 1000, 1002, $group_IDs_active );

        if ( $group_id !== NULL )
        {
            $northwest_group_IDs [ ] = $group_id;
        }

        $group_id = self::getGroupID ( 1000, 1003, $group_IDs_active );

        if ( $group_id !== NULL )
        {
            $northwest_group_IDs [ ] = $group_id;
        }

        $group_id = self::getGroupID ( 1000, 1004, $group_IDs_active );

        if ( $group_id !== NULL )
        {
            $northwest_group_IDs [ ] = $group_id;
        }

        $group_id = self::getGroupID ( 1001, 1004, $group_IDs_active );

        if ( $group_id !== NULL )
        {
            $northwest_group_IDs [ ] = $group_id;
        }


        //
        // Southeast boundary 
        //
        $group_id = self::getGroupID ( 1003, 1000, $group_IDs_active );

        if ( $group_id !== NULL )
        {
            $southeast_group_IDs [ ] = $group_id;
        }

        $group_id = self::getGroupID ( 1004, 1000, $group_IDs_active );

        if ( $group_id !== NULL )
        {
            $southeast_group_IDs [ ] = $group_id;
        }

        $group_id = self::getGroupID ( 1004, 1001, $group_IDs_active );

        if ( $group_id !== NULL )
        {
            $southeast_group_IDs [ ] = $group_id;
        }

        $group_id = self::getGroupID ( 1004, 1002, $group_IDs_active );

        if ( $group_id !== NULL )
        {
            $southeast_group_IDs [ ] = $group_id;
        }


        foreach ( $northwest_group_IDs as $northwest_group_ID )
        {
            foreach ( $southeast_group_IDs as $southeast_group_ID )
            {
                if ( $northwest_group_ID == $southeast_group_ID  )
                {
                    return true;
                }
            }
        }
    }
    else                                // GREEN IS ACTIVE PLAYER
    {
        $northeast_group_IDs = array ( );

        $southwest_group_IDs = array ( );

        //
        // Northeast boundary 
        //
        $group_id = self::getGroupID ( 1001, 1004, $group_IDs_active );

        if ( $group_id !== NULL )
        {
            $northeast_group_IDs [ ] = $group_id;
        }

        $group_id = self::getGroupID ( 1002, 1004, $group_IDs_active );

        if ( $group_id !== NULL )
        {
            $northeast_group_IDs [ ] = $group_id;
        }

        $group_id = self::getGroupID ( 1003, 1003, $group_IDs_active );

        if ( $group_id !== NULL )
        {
            $northeast_group_IDs [ ] = $group_id;
        }

        $group_id = self::getGroupID ( 1004, 1002, $group_IDs_active );

        if ( $group_id !== NULL )
        {
            $northeast_group_IDs [ ] = $group_id;
        }


        //
        // Southwest boundary 
        //
        $group_id = self::getGroupID ( 1000, 1002, $group_IDs_active );

        if ( $group_id !== NULL )
        {
            $southwest_group_IDs [ ] = $group_id;
        }

        $group_id = self::getGroupID ( 1001, 1001, $group_IDs_active );

        if ( $group_id !== NULL )
        {
            $southwest_group_IDs [ ] = $group_id;
        }

        $group_id = self::getGroupID ( 1002, 1000, $group_IDs_active );

        if ( $group_id !== NULL )
        {
            $southwest_group_IDs [ ] = $group_id;
        }

        $group_id = self::getGroupID ( 1003, 1000, $group_IDs_active );

        if ( $group_id !== NULL )
        {
            $southwest_group_IDs [ ] = $group_id;
        }


        foreach ( $northeast_group_IDs as $northeast_group_ID )
        {
            foreach ( $southwest_group_IDs as $southwest_group_ID )
            {
                if ( $northeast_group_ID == $southwest_group_ID  )
                {
                    return true;
                }
            }
        }
    }


    return false;
}




function getGroupID ( $u, $v, $group_IDs ) 
{
    if (    isset ( $group_IDs [ $u ] [ $v ] )    )
    {
        return $group_IDs [ $u ] [ $v ];
    }
    else 
    {
        return NULL;
    }
}




function isOccupiedByPlayer ( $player_id, $u, $v, $board ) 
{
    if (    isset ($board [ $u ] [ $v ])    )
    {
        if ( $board [ $u ] [ $v ] == $player_id )                                                // CURRENT CELL OCCUPIED BY ACTIVE PLAYER
        {
            return true;
        }
    }

    return false;
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





function getOneTileInfo ($u, $v)
{
    $sql = "SELECT tile_info FROM board WHERE ( board_u, board_v) = ($u, $v)";  
    return $this->getUniqueValueFromDB( $sql );
}



    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

function argPlaceTile()
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
    //
    //
    // CHECK FOR WIN                               
    //
    //
    $active_player_id = self::getActivePlayerId();


    if (    self::didActivePlayerWin ( )    )
    {
        $sql = "UPDATE player SET player_score = 1 WHERE player_id = $active_player_id";
                   
        self::DbQuery( $sql );


        $newScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );

        self::notifyAllPlayers( "newScores", "", array(
            "scores" => $newScores
            ) );

                
        //self::notifyAllPlayers( "backendMessage", clienttranslate( 'AAAAA Go to endGame' ), 
        //    array(
        //    ) );
                

        $this->gamestate->nextState( 'endGame' );
    }
    else 
    {
        $active_player_id = self::activeNextPlayer();


        self::giveExtraTime( $active_player_id );                           // Active player can play. Give him some extra time.
        $this->gamestate->nextState( 'nextTurn' );
    }




    // Activate next player
    /*
    $active_player_id = self::activeNextPlayer();


    self::giveExtraTime( $active_player_id );                           // Active player can play. Give him some extra time.
    $this->gamestate->nextState( 'nextTurn' );
    */
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

    }    



    function initializeDatabase ( )
    {
        //  
        //
        //  COMPLETELY FILL DATABASE WITH THESE tile_info VALUES.  
        //
        //      tile_info = A+BB+X_Y,X_Y,X_Y...
        //          A:  SIZE: 1 = small tile, 2 = medium tile, 3 = large tile 
        //          BB: SINGLE NUMBER OFFSET INTO TILE TABLE.  PLAYER COLOR WILL DETERMINE WHETHER UPPER HALF OR LOWER HALF OF TABLE.
        //          X_Y = COORDINATES OF SURROUNDING TILE
        //
        //  START AT CELL TO LEFT AND GO AROUND CLOCKWISE
        //

        //
        //  LARGE TILES - ALL OF WHICH ARE ECCENTRIC - STARTING AT UPPER LEFT CORNER AND MOVING CLOCKWISE
        //
        $sql = "INSERT INTO board (board_u, board_v, board_player, tile_info) VALUES ";

        $sql_values = array();

        $sql_values[] = "(1000, 1004, NULL, '3+0+1001_1004,101_110,101_109,100_109,1000_1003')";                   
        $sql_values[] = "(1001, 1004, NULL, '3+1+1000_1004,1002_1004,104_110,104_109,103_109,102_109,101_110')";                   
        $sql_values[] = "(1002, 1004, NULL, '3+2+1001_1004,1003_1003,106_109,105_109,104_110')";                   
        $sql_values[] = "(1003, 1003, NULL, '3+3+106_108,106_109,1002_1004,1004_1002,109_106,108_106,107_107')";                   
        $sql_values[] = "(1004, 1002, NULL, '3+4+109_105,109_106,1003_1003,1004_1001,110_104')";                   
        $sql_values[] = "(1004, 1001, NULL, '3+5+109_102,109_103,109_104,110_104,1004_1002,1004_1000,110_101')";                   
        $sql_values[] = "(1004, 1000, NULL, '3+6+1003_1000,109_100,109_101,110_101,1004_1001')";                   
        $sql_values[] = "(1003, 1000, NULL, '3+7+1002_1000,106_100,106_101,107_101,108_101,109_100,1004_1000')";                   
        $sql_values[] = "(1002, 1000, NULL, '3+8+1001_1001,104_101,105_101,106_100,1003_1000')";                   
        $sql_values[] = "(1001, 1001, NULL, '3+9+1000_1002,101_104,102_104,103_103,104_102,104_101')";                   // * = checked
        $sql_values[] = "(1000, 1002, NULL, '3+10+1000_1003,100_106,101_105,101_104,1001_1001')";                   
        $sql_values[] = "(1000, 1003, NULL, '3+11+1000_1004,100_109,101_108,101_107,101_106,100_106,1000_1002')";       // all checked
        
        $sql .= implode( $sql_values, ',' );
        self::DbQuery( $sql );


        //
        //  MEDIUM ECCENTRIC TILES - STARTING AT UPPER LEFT CORNER AND MOVING CLOCKWISE
        //
        $sql = "INSERT INTO board (board_u, board_v, board_player, tile_info) VALUES ";

        $sql_values = array();

        $sql_values[] = "(103, 107, NULL, '2+0+102_107,102_108,103_108,104_107,1_10,1_9,0_9,103_106')";                // *   
        $sql_values[] = "(104, 107, NULL, '2+1+103_107,103_108,104_108,105_107,4_10,4_9,3_9,2_9,1_10')";               // *    
        $sql_values[] = "(105, 107, NULL, '2+2+104_107,104_108,105_108,106_107,106_106,6_9,5_9,4_10')";                // * 
        $sql_values[] = "(106, 106, NULL, '2+3+6_8,6_9,105_107,106_107,107_106,107_105,9_6,8_6,7_7')";                 // * 
        $sql_values[] = "(107, 105, NULL, '2+4+9_5,9_6,106_106,107_106,108_105,108_104,107_104,10_4')";                // *  
        $sql_values[] = "(107, 104, NULL, '2+5+9_2,9_3,9_4,10_4,107_105,108_104,108_103,107_103,10_1')";               // *   
        $sql_values[] = "(107, 103, NULL, '2+6+106_103,9_0,9_1,10_1,107_104,108_103,108_102,107_102')";                // *  
        $sql_values[] = "(106, 103, NULL, '2+7+105_103,6_0,6_1,7_1,8_1,9_0,107_103,107_102,106_102')";                 // * 
        $sql_values[] = "(105, 103, NULL, '2+8+104_103,104_104,4_1,5_1,6_0,106_103,106_102,105_102')";                 // * 
        $sql_values[] = "(104, 104, NULL, '2+9+103_104,103_105,1_4,2_4,3_3,4_2,4_1,105_103,104_103')";                 // * 
        $sql_values[] = "(103, 105, NULL, '2+10+102_105,102_106,103_106,0_6,1_5,1_4,104_104,103_104')";                // *  
        $sql_values[] = "(103, 106, NULL, '2+11+102_106,102_107,103_107,0_9,1_8,1_7,1_6,0_6,103_105')";                // *  

        $sql .= implode( $sql_values, ',' );
        self::DbQuery( $sql );


        //
        //  MEDIUM HEXAGONAL TILES - STARTING AT BOTTOM ROW AND MOVING UPWARDS
        //
        $sql = "INSERT INTO board (board_u, board_v, board_player, tile_info) VALUES ";

        $sql_values = array();

        $sql_values[] = "(106, 100, NULL, '2+12+1002_1000,105_101,106_101,1003_1000')";                   // *
        $sql_values[] = "(109, 100, NULL, '2+13+1003_1000,108_101,109_101,1004_1000')";                   // *
        
        $sql_values[] = "(104, 101, NULL, '2+14+1001_1001,104_102,105_101,1002_1000')";                   // *
        $sql_values[] = "(105, 101, NULL, '2+15+104_101,104_102,105_102,106_101,106_100,1002_1000')";     // *             
        $sql_values[] = "(106, 101, NULL, '2+16+105_101,105_102,106_102,107_101,1003_1000,106_100')";     // *             
        $sql_values[] = "(107, 101, NULL, '2+17+106_101,106_102,107_102,108_101,1003_1000')";             // *     
        $sql_values[] = "(108, 101, NULL, '2+18+107_101,107_102,108_102,109_101,109_100,1003_1000')";     // *             
        $sql_values[] = "(109, 101, NULL, '2+19+108_101,108_102,109_102,110_101,1004_1000,109_100')";     // *             
        $sql_values[] = "(110, 101, NULL, '2+20+109_101,109_102,1004_1001,1004_1000')";                   // *

        $sql_values[] = "(104, 102, NULL, '2+21+1001_1001,103_103,104_103,105_102,105_101,104_101')";     // *             
        $sql_values[] = "(105, 102, NULL, '2+22+104_102,104_103,105_103,106_102,106_101,105_101')";       // *           
        $sql_values[] = "(106, 102, NULL, '2+23+105_102,105_103,106_103,107_102,107_101,106_101')";       // *           
        $sql_values[] = "(107, 102, NULL, '2+24+106_102,106_103,107_103,108_102,108_101,107_101')";       // *           
        $sql_values[] = "(108, 102, NULL, '2+25+107_102,107_103,108_103,109_102,109_101,108_101')";       // *            
        $sql_values[] = "(109, 102, NULL, '2+26+108_102,108_103,109_103,1004_1001,110_101,109_101')";     // *
        
        $sql_values[] = "(103, 103, NULL, '2+27+1001_1001,102_104,103_104,104_103,104_102')";             // *     
        $sql_values[] = "(104, 103, NULL, '2+28+103_103,103_104,104_104,105_103,105_102,104_102')";       // *           
        $sql_values[] = "(108, 103, NULL, '2+29+107_103,107_104,108_104,109_103,109_102,108_102')";       // *           
        $sql_values[] = "(109, 103, NULL, '2+30+108_103,108_104,109_104,1004_1001,109_102')";             // *     

        $sql_values[] = "(101, 104, NULL, '2+31+1000_1002,101_105,102_104,1001_1001')";                   // *
        $sql_values[] = "(102, 104, NULL, '2+32+101_104,101_105,102_105,103_104,103_103,1001_1001')";     // *             
        $sql_values[] = "(103, 104, NULL, '2+33+102_104,102_105,103_105,104_104,104_103,103_103')";       // *           
        $sql_values[] = "(108, 104, NULL, '2+34+107_104,107_105,108_105,109_104,109_103,108_103')";       // *           
        $sql_values[] = "(109, 104, NULL, '2+35+108_104,108_105,109_105,110_104,1004_1001,109_103')";     // *             
        $sql_values[] = "(110, 104, NULL, '2+36+109_104,109_105,1004_1002,1004_1001')";                   // *

        $sql_values[] = "(101, 105, NULL, '2+37+1000_1002,100_106,101_106,102_105,102_104,101_104')";     // *             
        $sql_values[] = "(102, 105, NULL, '2+38+101_105,101_106,102_106,103_105,103_104,102_104')";       // *           
        $sql_values[] = "(108, 105, NULL, '2+39+107_105,107_106,108_106,109_105,109_104,108_104')";       // *           
        $sql_values[] = "(109, 105, NULL, '2+40+108_105,108_106,109_106,1004_1002,110_104,109_104')";     // *             

        $sql_values[] = "(100, 106, NULL, '2+41+1000_1002,1000_1003,101_106,101_105')";                   // *
        $sql_values[] = "(101, 106, NULL, '2+42+100_106,1000_1003,101_107,102_106,102_105,101_105')";     // *             
        $sql_values[] = "(102, 106, NULL, '2+43+101_106,101_107,102_107,103_106,103_105,102_105')";       // *           
        $sql_values[] = "(107, 106, NULL, '2+44+106_106,106_107,107_107,108_106,108_105,107_105')";       // *           
        $sql_values[] = "(108, 106, NULL, '2+45+107_106,107_107,1003_1003,109_106,109_105,108_105')";     // *             
        $sql_values[] = "(109, 106, NULL, '2+46+108_106,1003_1003,1004_1002,109_105')";                   // *

        $sql_values[] = "(101, 107, NULL, '2+47+1000_1003,101_108,102_107,102_106,101_106')";             // *     
        $sql_values[] = "(102, 107, NULL, '2+48+101_107,101_108,102_108,103_107,103_106,102_106')";       // *           
        $sql_values[] = "(106, 107, NULL, '2+49+105_107,105_108,106_108,107_107,107_106,106_106')";       // *           
        $sql_values[] = "(107, 107, NULL, '2+50+106_107,106_108,1003_1003,108_106,107_106')";             // *     

        $sql_values[] = "(101, 108, NULL, '2+51+1000_1003,100_109,101_109,102_108,102_107,101_107')";     // *             
        $sql_values[] = "(102, 108, NULL, '2+52+101_108,101_109,102_109,103_108,103_107,102_107')";       // *           
        $sql_values[] = "(103, 108, NULL, '2+53+102_108,102_109,103_109,104_108,104_107,103_107')";       // *           
        $sql_values[] = "(104, 108, NULL, '2+54+103_108,103_109,104_109,105_108,105_107,104_107')";       // *           
        $sql_values[] = "(105, 108, NULL, '2+55+104_108,104_109,105_109,106_108,106_107,105_107')";       // *           
        $sql_values[] = "(106, 108, NULL, '2+56+105_108,105_109,106_109,1003_1003,107_107,106_107')";     // *             

        $sql_values[] = "(100, 109, NULL, '2+57+1000_1003,1000_1004,101_109,101_108')";                   // *
        $sql_values[] = "(101, 109, NULL, '2+58+100_109,1000_1004,101_110,102_109,102_108,101_108')";     // *             
        $sql_values[] = "(102, 109, NULL, '2+59+101_109,101_110,1001_1004,103_109,103_108,102_108')";     // *             
        $sql_values[] = "(103, 109, NULL, '2+60+102_109,1001_1004,104_109,104_108,103_108')";             // *     
        $sql_values[] = "(104, 109, NULL, '2+61+103_109,1001_1004,104_110,105_109,105_108,104_108')";     // *             
        $sql_values[] = "(105, 109, NULL, '2+62+104_109,104_110,1002_1004,106_109,106_108,105_108')";     // *             
        $sql_values[] = "(106, 109, NULL, '2+63+105_109,1002_1004,1003_1003,106_108')";                   // *

        $sql_values[] = "(101, 110, NULL, '2+64+1000_1004,1001_1004,102_109,101_109')";                   // *
        $sql_values[] = "(104, 110, NULL, '2+65+1001_1004,1002_1004,105_109,104_109')";                   // *

        $sql .= implode( $sql_values, ',' );
        self::DbQuery( $sql );


        //
        //  SMALL TILES - ALL OF WHICH ARE HEXAGONAL - STARTING AT BOTTOM ROW AND MOVING UPWARDS
        //
        $sql = "INSERT INTO board (board_u, board_v, board_player, tile_info) VALUES ";

        $sql_values = array();

        $sql_values[] = "(6, 0, NULL, '1+0+105_103,5_1,6_1,106_103')";          // *             
        $sql_values[] = "(9, 0, NULL, '1+1+106_103,8_1,9_1,107_103')";          // *        

        $sql_values[] = "(4, 1, NULL, '1+2+104_104,4_2,5_1,105_103')";          // *        
        $sql_values[] = "(5, 1, NULL, '1+3+4_1,4_2,5_2,6_1,6_0,105_103')";      // *            
        $sql_values[] = "(6, 1, NULL, '1+4+5_1,5_2,6_2,7_1,106_103,6_0')";      // *            
        $sql_values[] = "(7, 1, NULL, '1+5+6_1,6_2,7_2,8_1,106_103')";          // *        
        $sql_values[] = "(8, 1, NULL, '1+6+7_1,7_2,8_2,9_1,9_0,106_103')";      // *            
        $sql_values[] = "(9, 1, NULL, '1+7+8_1,8_2,9_2,10_1,107_103,9_0')";     // *             
        $sql_values[] = "(10, 1, NULL, '1+8+9_1,9_2,107_104,107_103')";         // *         

        $sql_values[] = "(4, 2, NULL, '1+9+104_104,3_3,4_3,5_2,5_1,4_1')";      // *            
        $sql_values[] = "(5, 2, NULL, '1+10+4_2,4_3,5_3,6_2,6_1,5_1')";         // *         
        $sql_values[] = "(6, 2, NULL, '1+11+5_2,5_3,6_3,7_2,7_1,6_1')";         // *         
        $sql_values[] = "(7, 2, NULL, '1+12+6_2,6_3,7_3,8_2,8_1,7_1')";         // *         
        $sql_values[] = "(8, 2, NULL, '1+13+7_2,7_3,8_3,9_2,9_1,8_1')";         // *         
        $sql_values[] = "(9, 2, NULL, '1+14+8_2,8_3,9_3,107_104,10_1,9_1')";    // *  
        
        $sql_values[] = "(3, 3, NULL, '1+15+104_104,2_4,3_4,4_3,4_2')";         // *         
        $sql_values[] = "(4, 3, NULL, '1+16+3_3,3_4,4_4,5_3,5_2,4_2')";         // *         
        $sql_values[] = "(5, 3, NULL, '1+17+4_3,4_4,5_4,6_3,6_2,5_2')";         // *         
        $sql_values[] = "(6, 3, NULL, '1+18+5_3,5_4,6_4,7_3,7_2,6_2')";         // *           
        $sql_values[] = "(7, 3, NULL, '1+19+6_3,6_4,7_4,8_3,8_2,7_2')";         // *           
        $sql_values[] = "(8, 3, NULL, '1+20+7_3,7_4,8_4,9_3,9_2,8_2')";         // *         
        $sql_values[] = "(9, 3, NULL, '1+21+8_3,8_4,9_4,107_104,9_2')";         // *         

        $sql_values[] = "(1, 4, NULL, '1+22+103_105,1_5,2_4,104_104')";         // *         
        $sql_values[] = "(2, 4, NULL, '1+23+1_4,1_5,2_5,3_4,3_3,104_104')";     // *             
        $sql_values[] = "(3, 4, NULL, '1+24+2_4,2_5,3_5,4_4,4_3,3_3')";         // *         
        $sql_values[] = "(4, 4, NULL, '1+25+3_4,3_5,4_5,5_4,5_3,4_3')";         // *      
        $sql_values[] = "(5, 4, NULL, '1+26+4_4,4_5,5_5,6_4,6_3,5_3')";         // *      
        $sql_values[] = "(6, 4, NULL, '1+27+5_4,5_5,6_5,7_4,7_3,6_3')";         // *     
        $sql_values[] = "(7, 4, NULL, '1+28+6_4,6_5,7_5,8_4,8_3,7_3')";         // *    
        $sql_values[] = "(8, 4, NULL, '1+29+7_4,7_5,8_5,9_4,9_3,8_3')";         // *        
        $sql_values[] = "(9, 4, NULL, '1+30+8_4,8_5,9_5,10_4,107_104,9_3')";    // *              
        $sql_values[] = "(10, 4, NULL, '1+31+9_4,9_5,107_105,107_104')";        // *          

        $sql_values[] = "(1, 5, NULL, '1+32+103_105,0_6,1_6,2_5,2_4,1_4')";     // *             
        $sql_values[] = "(2, 5, NULL, '1+33+1_5,1_6,2_6,3_5,3_4,2_4')";         // *         
        $sql_values[] = "(3, 5, NULL, '1+34+2_5,2_6,3_6,4_5,4_4,3_4')";         // *           
        $sql_values[] = "(4, 5, NULL, '1+35+3_5,3_6,4_6,5_5,5_4,4_4')";         // *          
        $sql_values[] = "(5, 5, NULL, '1+36+4_5,4_6,5_6,6_5,6_4,5_4')";         // *         
        $sql_values[] = "(6, 5, NULL, '1+37+5_5,5_6,6_6,7_5,7_4,6_4')";         // *        
        $sql_values[] = "(7, 5, NULL, '1+38+6_5,6_6,7_6,8_5,8_4,7_4')";         // *      
        $sql_values[] = "(8, 5, NULL, '1+39+7_5,7_6,8_6,9_5,9_4,8_4')";         // *         
        $sql_values[] = "(9, 5, NULL, '1+40+8_5,8_6,9_6,107_105,10_4,9_4')";    // *             

        $sql_values[] = "(0, 6, NULL, '1+41+103_105,103_106,1_6,1_5')";         // *         
        $sql_values[] = "(1, 6, NULL, '1+42+0_6,103_106,1_7,2_6,2_5,1_5')";     // *             
        $sql_values[] = "(2, 6, NULL, '1+43+1_6,1_7,2_7,3_6,3_5,2_5')";         //          
        $sql_values[] = "(3, 6, NULL, '1+44+2_6,2_7,3_7,4_6,4_5,3_5')";         //       
        $sql_values[] = "(4, 6, NULL, '1+45+3_6,3_7,4_7,5_6,5_5,4_5')";         //         
        $sql_values[] = "(5, 6, NULL, '1+46+4_6,4_7,5_7,6_6,6_5,5_5')";         //  
        $sql_values[] = "(6, 6, NULL, '1+47+5_6,5_7,6_7,7_6,7_5,6_5')";         //  
        $sql_values[] = "(7, 6, NULL, '1+48+6_6,6_7,7_7,8_6,8_5,7_5')";         //         
        $sql_values[] = "(8, 6, NULL, '1+49+7_6,7_7,106_106,9_6,9_5,8_5')";     //              
        $sql_values[] = "(9, 6, NULL, '1+50+8_6,106_106,107_105,9_5')";         //          

        $sql_values[] = "(1, 7, NULL, '1+51+103_106,1_8,2_7,2_6,1_6')";         // *         
        $sql_values[] = "(2, 7, NULL, '1+52+1_7,1_8,2_8,3_7,3_6,2_6')";         // *         
        $sql_values[] = "(3, 7, NULL, '1+53+2_7,2_8,3_8,4_7,4_6,3_6')";         // *  
        $sql_values[] = "(4, 7, NULL, '1+54+3_7,3_8,4_8,5_7,5_6,4_6')";         // * 
        $sql_values[] = "(5, 7, NULL, '1+55+4_7,4_8,5_8,6_7,6_6,5_6')";         // * 
        $sql_values[] = "(6, 7, NULL, '1+56+5_7,5_8,6_8,7_7,7_6,6_6')";         // *         
        $sql_values[] = "(7, 7, NULL, '1+57+6_7,6_8,106_106,8_6,7_6')";         // *         

        $sql_values[] = "(1, 8, NULL, '1+58+103_106,0_9,1_9,2_8,2_7,1_7')";     //              
        $sql_values[] = "(2, 8, NULL, '1+59+1_8,1_9,2_9,3_8,3_7,2_7')";         //          
        $sql_values[] = "(3, 8, NULL, '1+60+2_8,2_9,3_9,4_8,4_7,3_7')";         //          
        $sql_values[] = "(4, 8, NULL, '1+61+3_8,3_9,4_9,5_8,5_7,4_7')";         //          
        $sql_values[] = "(5, 8, NULL, '1+62+4_8,4_9,5_9,6_8,6_7,5_7')";         //          
        $sql_values[] = "(6, 8, NULL, '1+63+5_8,5_9,6_9,106_106,7_7,6_7')";     //              

        $sql_values[] = "(0, 9, NULL, '1+64+103_106,103_107,1_9,1_8')";         //          
        $sql_values[] = "(1, 9, NULL, '1+65+0_9,103_107,1_10,2_9,2_8,1_8')";    //  
        $sql_values[] = "(2, 9, NULL, '1+66+1_9,1_10,104_107,3_9,3_8,2_8')";    //  
        $sql_values[] = "(3, 9, NULL, '1+67+2_9,104_107,4_9,4_8,3_8')";         //         
        $sql_values[] = "(4, 9, NULL, '1+68+3_9,104_107,4_10,5_9,5_8,4_8')";    //               
        $sql_values[] = "(5, 9, NULL, '1+69+4_9,4_10,105_107,6_9,6_8,5_8')";    //               
        $sql_values[] = "(6, 9, NULL, '1+70+5_9,105_107,106_106,6_8')";         //          

        $sql_values[] = "(1, 10, NULL, '1+71+103_107,104_107,2_9,1_9')";        //           
        $sql_values[] = "(4, 10, NULL, '1+72+104_107,105_107,5_9,4_9')";        //           

        $sql .= implode( $sql_values, ',' );
        self::DbQuery( $sql );

    }
}
