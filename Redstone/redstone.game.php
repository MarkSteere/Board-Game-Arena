<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * Redstone implementation : © <Mark Steere> <marksteere@gmail.com>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * redstone.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class Redstone extends Table
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
            //"end_of_game" => 10,                    // End of game variable
            "move_number" => 11,                    // Counter of the number of moves (used to detect first move)
            "black_stone_id" => 20,                 // Initial black stone ID
            "white_stone_id" => 21,                 // Initial white stone ID
            "red_stone_id" => 22,                   // Initial red stone ID
            "total_black_stones"=> 23,              // Total number of black stones
            "total_white_stones"=> 24,              // Total number of white stones
            "last_move_x"=> 30,                     // Last move indicator: x
            "last_move_y"=> 31,                     // Last move indicator: y
            "last_move_id"=> 32,                    // Last move indicator: id
            "board_size" => 101,                    // The size of the board
        ) );        
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "redstone";
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
        // INITIALIZE GLOBAL VARIABLES
        //
        self::setGameStateValue( "black_stone_id", 10000 );
        self::setGameStateValue( "white_stone_id", 20000 );
        self::setGameStateValue( "red_stone_id", 30000 );
        self::setGameStateValue( "total_black_stones", 0 );
        self::setGameStateValue( "total_white_stones", 0 );

        self::setGameStateValue( "move_number", 0 );

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
            // FOR TEMPORARY BOARD FILL
            //
            if( $color == '000000' )
                $black_player_id = $player_id;
            else
                $white_player_id = $player_id;


        }

        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );

        //self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();



        //
        // Initialize the board with NULL values for all cells 
        //
        $N=self::getGameStateValue("board_size");


        $sql = "INSERT INTO board (board_x, board_y, board_player, stone_id) VALUES ";

        $sql_values = array();



        //
        // INITIALIZE STATS
        //
        self::initStat( "player", "turns_number", 0);




        //
        //  
        //
        //  TEMPORARY FILL BOARD WITH STONES 
        //
        //
        //
        /*
        for ( $x = 0; $x < $N; $x++ )         
        {
            for ( $y = 0; $y < $N; $y++ )  
            {
                if (    ( $x + $y ) % 2 == 0    )
                {
                    $player_id_value = $black_player_id;

                    $stone_id_value = self::getGameStateValue( "black_stone_id" );
                    self::incGameStateValue( "black_stone_id", 1 );


                    self::incGameStateValue( "total_black_stones", 1 );

                    $sql_values[] = "($x, $y, $player_id_value, $stone_id_value)";                   
                }
                else                                                                    // RED STONES
                {
                    $player_id_value = 0;                             

                    $stone_id_value = self::getGameStateValue( "red_stone_id" );
                    self::incGameStateValue( "red_stone_id", 1 );

                    $sql_values[] = "($x, $y, $player_id_value, $stone_id_value)";                   
                }
                
                //else                                                                    // WHITE STONES
                //{
                //    $player_id_value = $white_player_id;                             
                //
                //    $stone_id_value = self::getGameStateValue( "white_stone_id" );
                //    self::incGameStateValue( "white_stone_id", 1 );
                //
                //    self::incGameStateValue( "total_white_stones", 1 );
                //
                //    $sql_values[] = "($x, $y, $player_id_value, $stone_id_value)";                   
                //}
                
            }
        }

        $sql .= implode( $sql_values, ',' );
        self::DbQuery( $sql ); 
        */



        //  
        //
        //  FILL BOARD WITH NULL VALUES
        //
        //
        for ( $x = 0; $x < $N; $x++ ) // Start on bottom row.  $v = 0
        {
            for ( $y = 0; $y < $N; $y++ )  
            {
                $sql_values[] = "($x, $y, NULL, NULL)";                   
            }
        }

        $sql .= implode( $sql_values, ',' );
        self::DbQuery( $sql );



        //  
        //
        //  ADD IN SOME TEMPORARY VALUES FOR addDieOnBoard TESTING
        //
        //
        //
        //  BLACK 
        //
        //
        /*
        $stone_id = self::getGameStateValue ( "black_stone_id" ); 

        self::incGameStateValue ( "black_stone_id", 1 );
        self::incGameStateValue( "total_black_stones", 1 );
        $sql = "UPDATE board SET board_player = $black_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 3, 0 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "black_stone_id" ); 
        self::incGameStateValue ( "black_stone_id", 1 );
        self::incGameStateValue( "total_black_stones", 1 );
        $sql = "UPDATE board SET board_player = $black_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 8, 0 )";      
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "black_stone_id" ); 
        self::incGameStateValue ( "black_stone_id", 1 );
        self::incGameStateValue( "total_black_stones", 1 );
        $sql = "UPDATE board SET board_player = $black_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 3, 1 )";      
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "black_stone_id" ); 
        self::incGameStateValue ( "black_stone_id", 1 );
        self::incGameStateValue( "total_black_stones", 1 );
        $sql = "UPDATE board SET board_player = $black_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 8, 1 )";      
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "black_stone_id" ); 
        self::incGameStateValue ( "black_stone_id", 1 );
        self::incGameStateValue( "total_black_stones", 1 );
        $sql = "UPDATE board SET board_player = $black_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 3, 2 )";      
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "black_stone_id" ); 
        self::incGameStateValue ( "black_stone_id", 1 );
        self::incGameStateValue( "total_black_stones", 1 );
        $sql = "UPDATE board SET board_player = $black_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 8, 3 )";      
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "black_stone_id" ); 
        self::incGameStateValue ( "black_stone_id", 1 );
        self::incGameStateValue( "total_black_stones", 1 );
        $sql = "UPDATE board SET board_player = $black_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 7, 5 )";      
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "black_stone_id" ); 
        self::incGameStateValue ( "black_stone_id", 1 );
        self::incGameStateValue( "total_black_stones", 1 );
        $sql = "UPDATE board SET board_player = $black_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 8, 5 )";      
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "black_stone_id" ); 
        self::incGameStateValue ( "black_stone_id", 1 );
        self::incGameStateValue( "total_black_stones", 1 );
        $sql = "UPDATE board SET board_player = $black_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 0, 7 )";      
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "black_stone_id" ); 
        self::incGameStateValue ( "black_stone_id", 1 );
        self::incGameStateValue( "total_black_stones", 1 );
        $sql = "UPDATE board SET board_player = $black_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 1, 7 )";      
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "black_stone_id" ); 
        self::incGameStateValue ( "black_stone_id", 1 );
        self::incGameStateValue( "total_black_stones", 1 );
        $sql = "UPDATE board SET board_player = $black_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 2, 7 )";      
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "black_stone_id" ); 
        self::incGameStateValue ( "black_stone_id", 1 );
        self::incGameStateValue( "total_black_stones", 1 );
        $sql = "UPDATE board SET board_player = $black_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 3, 7 )";      
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "black_stone_id" ); 
        self::incGameStateValue ( "black_stone_id", 1 );
        self::incGameStateValue( "total_black_stones", 1 );
        $sql = "UPDATE board SET board_player = $black_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 5, 7 )";      
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "black_stone_id" ); 
        self::incGameStateValue ( "black_stone_id", 1 );
        self::incGameStateValue( "total_black_stones", 1 );
        $sql = "UPDATE board SET board_player = $black_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 6, 7 )";      
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "black_stone_id" ); 
        self::incGameStateValue ( "black_stone_id", 1 );
        self::incGameStateValue( "total_black_stones", 1 );
        $sql = "UPDATE board SET board_player = $black_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 7, 7 )";      
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "black_stone_id" ); 
        self::incGameStateValue ( "black_stone_id", 1 );
        self::incGameStateValue( "total_black_stones", 1 );
        $sql = "UPDATE board SET board_player = $black_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 8, 7 )";      
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "black_stone_id" ); 
        self::incGameStateValue ( "black_stone_id", 1 );
        self::incGameStateValue( "total_black_stones", 1 );
        $sql = "UPDATE board SET board_player = $black_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 0, 8 )";      
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "black_stone_id" ); 
        self::incGameStateValue ( "black_stone_id", 1 );
        self::incGameStateValue( "total_black_stones", 1 );
        $sql = "UPDATE board SET board_player = $black_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 8, 8 )";      
        self::DbQuery( $sql );



        //
        //  WHITE 
        //
        //
        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 1, 0 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 7, 0 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 0, 1 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 1, 1 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 2, 1 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 7, 1 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 0, 2 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 2, 2 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 1, 3 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 7, 4 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 8, 4 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 0, 6 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 2, 6 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 3, 6 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 6, 6 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 7, 6 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 8, 6 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 1, 8 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 2, 8 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 3, 8 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 4, 8 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 5, 8 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 6, 8 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "white_stone_id" ); 
        self::incGameStateValue ( "white_stone_id", 1 );
        self::incGameStateValue( "total_white_stones", 1 );
        $sql = "UPDATE board SET board_player = $white_player_id, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 7, 8 )";     
        self::DbQuery( $sql );


        //
        //  RED 
        //
        //
        $stone_id = self::getGameStateValue ( "red_stone_id" ); 
        self::incGameStateValue ( "red_stone_id", 1 );
        $sql = "UPDATE board SET board_player = 0, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 2, 0 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "red_stone_id" ); 
        self::incGameStateValue ( "red_stone_id", 1 );
        $sql = "UPDATE board SET board_player = 0, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 7, 2 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "red_stone_id" ); 
        self::incGameStateValue ( "red_stone_id", 1 );
        $sql = "UPDATE board SET board_player = 0, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 2, 3 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "red_stone_id" ); 
        self::incGameStateValue ( "red_stone_id", 1 );
        $sql = "UPDATE board SET board_player = 0, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 1, 6 )";     
        self::DbQuery( $sql );


        $stone_id = self::getGameStateValue ( "red_stone_id" ); 
        self::incGameStateValue ( "red_stone_id", 1 );
        $sql = "UPDATE board SET board_player = 0, stone_id = $stone_id
                WHERE ( board_x, board_y) = ( 5, 6 )";     
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
    
        //$current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
    
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );
  
        //
        // Get board occupied cells
        // 
        $sql = "SELECT board_x x, board_y y, board_player player, stone_id stone_id
                    FROM board
                    WHERE board_player IS NOT NULL";
        $result['board'] = self::getObjectListFromDB( $sql );


		//Get the board size
		$result['board_size'] = self::getGameStateValue("board_size");
  
		//Get the move number
		$result['move_number'] = self::getGameStateValue("move_number");
  
        //
        // Placed stones
        //
        $activePlayerId = self::getActivePlayerId();
        $otherPlayerId = self::getOtherPlayerId($activePlayerId);

        $active_player_color = self::getPlayerColor ( $activePlayerId );


        $placedStones = array();

        if ( $active_player_color == "000000" )
        {
            $placedStones[$activePlayerId] = self::getGameStateValue( "total_black_stones" );
            $placedStones[$otherPlayerId] = self::getGameStateValue( "total_white_stones" );
        }
        else
        {
            $placedStones[$activePlayerId] = self::getGameStateValue( "total_white_stones" );
            $placedStones[$otherPlayerId] = self::getGameStateValue( "total_black_stones" );
        }
        
        $result['placedStones'] = $placedStones;





        //
        // LAST MOVE INDICATOR 
        //
        $last_move = array ( );

        $last_move [ 0 ] = self::getGameStateValue( "last_move_x" );

        $last_move [ 1 ] = self::getGameStateValue( "last_move_y" );

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
        $total_black_stones = self::getGameStateValue ( "total_black_stones");

        $total_white_stones = self::getGameStateValue ( "total_white_stones");

        $total_stones = $total_black_stones + $total_white_stones;


        $N = self::getGameStateValue ( "board_size" );

        switch ( $N )
        {
            case 9:
                $board_size = 81;
                break;

            case 11:
                $board_size = 121;
                break;

            case 13:
                $board_size = 169;
                break;

            case 15:
                $board_size = 225;
                break;

            case 19:
                $board_size = 361;
                break;
        }
            
        return min ( $total_stones / $board_size * 200, 100 );
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    


//  Michael Amundsen contribution
//
//  You can place a red stone if, after placement, it's adjacent to a bounded group. Or place a nonred stone if, after placement there are no bounded groups on the board.
//
//      Nice concept but computationally intensive
//
//
// getAllSelectableSquares ( $active_player_id )  //  Combined array of nonCapturingSelectableSquares, capturingSelectableSquares, and nonCapturingOrCapturingSelectableSquares
// ########################################
//
//  HIGHLIGHTING COLORS
//
//      GREEN   non-capturing placemnt
//      YELLOW  non-capturing or capturing placement
//      RED     capturing placement
//
//
//  GAME LOGIC 
//  ##########
// 
//  Give each active stone and each inactive stone on the board a group ID.  I.e., all stones that are part of the same group have the same group ID. (Active means the player on turn.)
//
//      group_IDs_active [i][j] = active_group_ID
//      group_IDs_inactive [i][j] = inactive_group_ID
//
//  To accomplish this, make one pass through the board for the active player and one pass for the inactive player.  Start with the
//  bottom row and visit each square from left to right.  Then repeat, moving upward through the rows.
//
//  If the current square is occupied 
//      If its left neighboring square is occupied
//          Copy the left neighbor's group ID to the current square
//          If the bottom neighboring square is ALSO occupied
//              Replace all occurrences of the current square's group ID with the bottom neighbor's group ID 
//      Else if its bottom neighboring square is occupied
//          Copy the bottom neighbor's group ID
//      Else 
//          Give the current square a fresh group ID   
// 
//  Make arrays of liberty coordinates for each group
//      active_groups_liberties [ group_ID ] = liberties_array [][]
//      inactive_groups_liberties [ group_ID ] = liberties_array [][]
//
//  Make lists of vulnerable groups.  I.e., groups with only one liberty
//      vulnerable_group_IDs_active [] = j, k, l,...
//      ivulnerable_group_IDs_inactive [] = m, n, o,...
//
//  For each unoccupied square 
//      If the square is adjacent to a vulnerable (only one liberty) inactive (player not on turn) group 
//          The square is a capturing placement.
//      Else if the square is adjacent to a vulnerable active group
//          If the square is ALSO adjacent to an invulnerable active group OR an unoccupied square
//              The square is a capturing or non-capturing placement
//          Else the square is a capturing placement
//      Else if the square is adjacent to an invulnerable active group OR an unoccupied square 
//          The square is a non-capturing placement 
//          
//              
//
function getAllSelectableSquares ( $active_player_id )
{                       
    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( '###### getSelectableCells ( ) ######' ), 
    array(
    ) );
    */

    $all_selectable_squares = array ( );

    $non_capturing_selectable_squares = array ( );
    
    $capturing_selectable_squares = array ( );

    $non_capturing_or_capturing_selectable_squares = array ( );
    

    $inactive_player_id = self::getOtherPlayerId ( $active_player_id );

    $board = self::getBoard ();

    //
    // TWO DIMENSIONAL ARRAY TO RECORD THE GROUP ID FOR EACH OCCUPIED SQUARE
    //
    //      ONE FOR EACH PLAYER
    //
    $group_IDs_active = array (); 
    $group_IDs_inactive = array (); 


	//Get the board size
    $N = self::getGameStateValue ("board_size");

    //
    // Set fresh group IDs to 0
    //
    $fresh_group_ID_active = 0;
    $fresh_group_ID_inactive = 0;

    //
    // Fill in $group_IDs_active
    //
    for ( $y = 0; $y < $N; $y++ )                   
    {
        for ( $x = 0; $x < $N; $x++ )  
        {
            if (    $board [ $x ] [ $y ] == $active_player_id    )                                      // Current square is occupied by active player
            {
                if (    self::getOccupant ( $x - 1, $y, $board, $N ) == $active_player_id    )          // If square to left of current square is occupied by active player...
                {
                    $group_IDs_active [ $x ] [ $y ] = $group_IDs_active [ $x - 1 ] [ $y ];              // ...copy its group ID to current square

                    if (    self::getOccupant ( $x, $y - 1, $board, $N ) == $active_player_id    )      // If square below current square is ALSO occupied by active player...
                    {
                        $current_group_ID = $group_IDs_active [ $x ] [ $y ];                            // ...replace all occurrences of current group ID with lower right group ID
                        $below_group_ID = $group_IDs_active [ $x ] [ $y - 1 ];

                        foreach ($group_IDs_active as &$group_IDs_row)         
                        {
                            foreach ($group_IDs_row as &$group_ID)         
                            {
                                if ( $group_ID == $current_group_ID )
                                {
                                     $group_ID = $below_group_ID;
                                }
                            }
                        }
                        unset($group_IDs_row);  // break the reference with the last element    
                        unset($group_ID);       // break the reference with the last element    
                    }
                }
                else if (    self::getOccupant ( $x, $y - 1, $board, $N ) == $active_player_id    )     // If square below current square is occupied by active player...
                {
                    $group_IDs_active [ $x ] [ $y ] = $group_IDs_active [ $x ] [ $y - 1 ];              // ...copy its group ID to current square
                }
                else
                {
                    $group_IDs_active [ $x ] [ $y ] = $fresh_group_ID_active++;                         // Give the current square a fresh group ID
                }
            }
        }
    }

    

    //
    // Fill in $group_IDs_inactive
    //
    for ( $y = 0; $y < $N; $y++ )                   
    {
        for ( $x = 0; $x < $N; $x++ )  
        {
            if (    $board [ $x ] [ $y ] == $inactive_player_id    )                                      // Current square is occupied by active player
            {
                if (    self::getOccupant ( $x - 1, $y, $board, $N ) == $inactive_player_id    )          // If square to left of current square is occupied by active player...
                {
                    $group_IDs_inactive [ $x ] [ $y ] = $group_IDs_inactive [ $x - 1 ] [ $y ];              // ...copy its group ID to current square

                    if (    self::getOccupant ( $x, $y - 1, $board, $N ) == $inactive_player_id    )      // If square below current square is ALSO occupied by active player...
                    {
                        $current_group_ID = $group_IDs_inactive [ $x ] [ $y ];                            // ...replace all occurrences of current group ID with lower right group ID
                        $below_group_ID = $group_IDs_inactive [ $x ] [ $y - 1 ];

                        foreach ($group_IDs_inactive as &$group_IDs_row)         
                        {
                            foreach ($group_IDs_row as &$group_ID)         
                            {
                                if ( $group_ID == $current_group_ID )
                                {
                                     $group_ID = $below_group_ID;
                                }
                            }
                        }
                        unset($group_IDs_row);  // break the reference with the last element    
                        unset($group_ID);       // break the reference with the last element    
                    }
                }
                else if (    self::getOccupant ( $x, $y - 1, $board, $N ) == $inactive_player_id    )     // If square below current square is occupied by active player...
                {
                    $group_IDs_inactive [ $x ] [ $y ] = $group_IDs_inactive [ $x ] [ $y - 1 ];              // ...copy its group ID to current square
                }
                else
                {
                    $group_IDs_inactive [ $x ] [ $y ] = $fresh_group_ID_inactive++;                         // Give the current square a fresh group ID
                }
            }
        }
    }

    


    // ################################################################################################
    // ################################################################################################
    //
    // TESTING
    //
    //      SHOW EVERY OCCUPIED CELL GROUP ID 
    //
    //          FIRST FOR ACTIVE PLAYER 
    //
    /*
    for ( $y = 0; $y < $N; $y++ )                   // Start on bottom row.  $y = 0
    {
        for ( $x = 0; $x < $N; $x++ )  
        {
            if (    $board [ $x ] [ $y ] == $active_player_id    ) 
            {
                    self::notifyAllPlayers( "backendMessage", clienttranslate( '${var_1}, ${var_2} active player' ), 
                    array(
                            "var_1" => $x,
                            "var_2" => $y
                    ) );

                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'group id = ${var_1}' ), 
                    array(
                            "var_1" => $group_IDs_active [ $x ] [ $y ]
                    ) );
            }
        }
    }
    */

    //
    //          THEN FOR INACTIVE PLAYER 
    //
    /*
    for ( $y = 0; $y < $N; $y++ )                   // Start on bottom row.  $y = 0
    {
        for ( $x = 0; $x < $N; $x++ )  
        {
            if (    $board [ $x ] [ $y ] == $inactive_player_id    ) 
            {
                    self::notifyAllPlayers( "backendMessage", clienttranslate( '${var_1}, ${var_2} inactive player' ), 
                    array(
                            "var_1" => $x,
                            "var_2" => $y
                    ) );

                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'group id = ${var_1}' ), 
                    array(
                            "var_1" => $group_IDs_inactive [ $x ] [ $y ]
                    ) );
            }
        }
    }
    */

    //
    //
    // LIST OF ARRAYS OF LIBERTIES FOR EACH GROUP ID 
    //
    //
    $active_groups_liberties = array ( );
    $inactive_groups_liberties = array ( ); 

    //
    //
    // ACTIVE GROUPS LIBERTIES 
    //
    //
    $directions = array (    array ( -1, 0 ), array ( 0, 1 ), array ( 1, 0 ), array ( 0, -1 )    );

    for ( $y = 0; $y < $N; $y++ )                   // Start on bottom row.  $y = 0
    {
        for ( $x = 0; $x < $N; $x++ )  
        {
            if ( $board [ $x ] [ $y ] == NULL )                                                             // Unoccupied square
            {
                foreach ( $directions as $direction )                                                       // Check surrounding squares for group IDs
                {
                    $neighbor_x = $x + $direction [ 0 ];
                    $neighbor_y = $y + $direction [ 1 ];

                    if (    isset ( $group_IDs_active [ $neighbor_x ] [ $neighbor_y ] )    )                // Neighboring group
                    {
                        $neighboring_group_ID = $group_IDs_active [ $neighbor_x ] [ $neighbor_y ];          // Neighboring group ID
                    
                        if (    ! isset ( $active_groups_liberties [ $neighboring_group_ID ] )    )         // If group ID not in groups liberties array, add it 
                        {
                            $active_groups_liberties [ $neighboring_group_ID ] = array ( );
                        }
                        
                        if (    ! isset ( $active_groups_liberties [ $neighboring_group_ID ] [ $x ] )    )  // Add this unoccupied square...
                        {                                                                                   // ...to groups liberties array [ $neighboring_group_ID ]
                            $active_groups_liberties [ $neighboring_group_ID ] [ $x ] = array ( ) ;
                        }

                        $active_groups_liberties [ $neighboring_group_ID ] [ $x ] [ $y ] = 1;
                    }
                }
            }
        }
    }



    //
    //
    // INACTIVE GROUPS LIBERTIES 
    //
    //
    $directions = array (    array ( -1, 0 ), array ( 0, 1 ), array ( 1, 0 ), array ( 0, -1 )    );

    for ( $y = 0; $y < $N; $y++ )                   // Start on bottom row.  $y = 0
    {
        for ( $x = 0; $x < $N; $x++ )  
        {
            if ( $board [ $x ] [ $y ] == NULL )                                                             // Unoccupied square
            {
                foreach ( $directions as $direction )                                                       // Check surrounding squares for group IDs
                {
                    $neighbor_x = $x + $direction [ 0 ];
                    $neighbor_y = $y + $direction [ 1 ];

                    if (    isset ( $group_IDs_inactive [ $neighbor_x ] [ $neighbor_y ] )    )                // Neighboring group
                    {
                        $neighboring_group_ID = $group_IDs_inactive [ $neighbor_x ] [ $neighbor_y ];          // Neighboring group ID
                    
                        if (    ! isset ( $inactive_groups_liberties [ $neighboring_group_ID ] )    )         // If group ID not in groups liberties array, add it 
                        {
                            $inactive_groups_liberties [ $neighboring_group_ID ] = array ( );
                        }
                        
                        if (    ! isset ( $inactive_groups_liberties [ $neighboring_group_ID ] [ $x ] )    )  // Add this unoccupied square...
                        {                                                                                   // ...to groups liberties array [ $neighboring_group_ID ]
                            $inactive_groups_liberties [ $neighboring_group_ID ] [ $x ] = array ( ) ;
                        }

                        $inactive_groups_liberties [ $neighboring_group_ID ] [ $x ] [ $y ] = 1;
                    }
                }
            }
        }
    }



    // ################################################################################################
    // ################################################################################################
    //
    // TESTING
    //
    //      SHOW EVERY GROUP IDs LIBERTIES
    //
    //          FIRST FOR ACTIVE PLAYER 
    //
    /*
    for ( $group_ID = 0; $group_ID < $fresh_group_ID_active; $group_ID++ )
    {
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'active group_ID = ${var_1}' ), 
        array(
                "var_1" => $group_ID
        ) );

        for ( $y = 0; $y < $N; $y++ )                   
        {
            for ( $x = 0; $x < $N; $x++ )  
            {
                if (    isset ( $active_groups_liberties [ $group_ID ] [ $x ] [ $y ] )    ) 
                {
                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'liberty_x liberty_y ${var_1}, ${var_2}' ), 
                    array(
                            "var_1" => $x,
                            "var_2" => $y
                    ) );
                }
            }
        }
    }
    */


    //
    //          THEN FOR INACTIVE PLAYER 
    //
    /*
    for ( $group_ID = 0; $group_ID < $fresh_group_ID_inactive; $group_ID++ )
    {
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'inactive group_ID = ${var_1}' ), 
        array(
                "var_1" => $group_ID
        ) );

        for ( $y = 0; $y < $N; $y++ )                   
        {
            for ( $x = 0; $x < $N; $x++ )  
            {
                if (    isset ( $inactive_groups_liberties [ $group_ID ] [ $x ] [ $y ] )    ) 
                {
                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'liberty_x liberty_y ${var_1}, ${var_2}' ), 
                    array(
                            "var_1" => $x,
                            "var_2" => $y
                    ) );
                }
            }
        }
    }
    */

    //
    // GROUPS WITH EXACTLY ONE LIBERTY 
    //
    $vulnerable_group_IDs_active = array ( );
    $vulnerable_group_IDs_inactive = array ( );

    //
    // ACTIVE GROUPS
    //
    for ( $group_ID = 0; $group_ID < $fresh_group_ID_active; $group_ID++ )
    {
        /*
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'active group_ID = ${var_1}' ), 
        array(
                "var_1" => $group_ID
        ) );
        */
        $count_liberties_active = 0;

        if (    isset ( $active_groups_liberties [ $group_ID ] )    ) 
        {
            foreach ( $active_groups_liberties [ $group_ID ] as $row ) 
            {
                $count_liberties_active += count ( $row );
            }
        }

        if ( $count_liberties_active == 1 )
        {
            $vulnerable_group_IDs_active [ ] = $group_ID;
        }
    }


    //
    // INACTIVE GROUPS
    //
    for ( $group_ID = 0; $group_ID < $fresh_group_ID_inactive; $group_ID++ )
    {
        /*
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'inactive group_ID = ${var_1}' ), 
        array(
                "var_1" => $group_ID
        ) );
        */
        $count_liberties_inactive = 0;

        if (    isset ( $inactive_groups_liberties [ $group_ID ] )    ) 
        {
            foreach ( $inactive_groups_liberties [ $group_ID ] as $row ) 
            {
                $count_liberties_inactive += count ( $row );
            }
        }

        if ( $count_liberties_inactive == 1 )
        {
            $vulnerable_group_IDs_inactive [ ] = $group_ID;
        }
    }



    // ################################################################################################
    // ################################################################################################
    //
    // TESTING
    //
    //      SHOW EVERY VULNERABLE (HAS ONE LIBERTY) GROUP ID
    //
    //          FIRST FOR ACTIVE PLAYER 
    //
    /*
    foreach ( $vulnerable_group_IDs_active as $group_ID )
    {
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'vulnerable active group_ID = ${var_1}' ), 
        array(
                "var_1" => $group_ID
        ) );
    }
    */

    //
    //          THEN FOR INACTIVE PLAYER 
    //
    /*
    foreach ( $vulnerable_group_IDs_inactive as $group_ID )
    {
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'vulnerable inactive group_ID = ${var_1}' ), 
        array(
                "var_1" => $group_ID
        ) );
    }
    */



    //  For each unoccupied square 
    //      If the square is adjacent to a vulnerable (only one liberty) inactive (player not on turn) group 
    //          The square is a capturing placement.
    //      Else if the square is adjacent to a vulnerable active group
    //          If the square is ALSO adjacent to an invulnerable active group OR an unoccupied square
    //              The square is a capturing or non-capturing placement
    //          Else the square is a capturing placement
    //      Else if the square is adjacent to an invulnerable active group OR an unoccupied square 
    //          The square is a non-capturing placement 
    //          

    for ( $y = 0; $y < $N; $y++ )                   
    {
        for ( $x = 0; $x < $N; $x++ )  
        {
            $is_vulnerable_active_neighbor = false;
            $is_vulnerable_inactive_neighbor = false;
            $is_invulnerable_active_neighbor = false;
            $is_unoccupied_neighbor = false;

            if ( $board [ $x ] [ $y ] == NULL )                                                                                 // UNOCCUPIED SQUARE
            {
                foreach ( $directions as $direction )                                                                           // CHECK SURROUNDING SQUARES FOR GROUP iD
                {
                    $neighbor_x = $x + $direction [ 0 ];
                    $neighbor_y = $y + $direction [ 1 ];

                    if (    self::isSquareOnBoard ( $neighbor_x, $neighbor_y, $N )    )
                    {
                        if (    isset ( $group_IDs_inactive [ $neighbor_x ] [ $neighbor_y ] )    )                                      // INACTIVE NEIGHBOR
                        {
                            if (     in_array ( $group_IDs_inactive [ $neighbor_x ] [ $neighbor_y ], $vulnerable_group_IDs_inactive )    )  // Vulnerable inactive neighbor...
                            {  
                                if (     ! isset ( $capturing_selectable_squares [ $x ] )    )                                                  // ...Capturing placement.  Break.
                                {
                                    $capturing_selectable_squares [ $x ] = array ( );
                                }

                                $is_vulnerable_inactive_neighbor = true;
                                
                                //$capturing_selectable_squares [ $x ] [ $y ] = 1;                                                       

                                //break;
                            }
                        }
                        else if (    isset ( $group_IDs_active [ $neighbor_x ] [ $neighbor_y ] )    )                                   // ACTIVE NEIGHBOR
                        {
                            if (    in_array ( $group_IDs_active [ $neighbor_x ] [ $neighbor_y ], $vulnerable_group_IDs_active )    )       // Vulnerable active neighbor
                            {
                                $is_vulnerable_active_neighbor = true;                                                                          // $is_vulnerable_active_neighbor = true; 
                            }
                            else                                                                                                            // Invulnerable active neighbor
                            {
                                $is_invulnerable_active_neighbor = true;                                                                       // $is_invulnerable_active_neighbor = true; 
                            }
                        }
                        else if ( $board [ $neighbor_x ] [ $neighbor_y ] == NULL  )                                                     // UNOCCUPIED NEIGHBOR
                        {
                            $is_unoccupied_neighbor = true;
                        }
                    }
                }

                //
                // REMAINING GAME LOGIC 
                //
                if ( $is_vulnerable_inactive_neighbor )                                                                                 // VULNERABLE INACTIVE NEIGHBOR
                {
                     $capturing_selectable_squares [ $x ] [ $y ] = 1;                                                                       // Capturing placement
                }
                else if ( $is_vulnerable_active_neighbor )                                                                              // VULNERABLE ACTIVE NEIGHBOR
                {
                    if ( $is_invulnerable_active_neighbor || $is_unoccupied_neighbor )                                                      // Also invulnerable active neighbor...
                    {                                                                                                                       //    ...or unoccupied neighbor
                        if (     ! isset ( $non_capturing_or_capturing_selectable_squares [ $x ] )    )                                         // Non-capturing or capturing placement
                        {
                            $non_capturing_or_capturing_selectable_squares [ $x ] = array ( );
                        }

                        $non_capturing_or_capturing_selectable_squares [ $x ] [ $y ] = 1;                                                       
                    }
                    else                                                                                                                    // Not also invulnerable active neighbor...
                    {                                                                                                                       //    ...or unoccupied neighbor
                        if (     ! isset ( $capturing_selectable_squares [ $x ] )    )                                                          // Capturing placement
                        { 
                            $capturing_selectable_squares [ $x ] = array ( );
                        }

                        $capturing_selectable_squares [ $x ] [ $y ] = 1;                                                       
                    }
                }
                else                                                                                                                    // NOT VULNERABLE ACTIVE OR INACTIVE NEIGHBOR
                {
                    if ( $is_invulnerable_active_neighbor || $is_unoccupied_neighbor )                                                      // Invulnerable active neighbor...
                    {                                                                                                                       //    ...or unoccupied neighbor
                        if (     ! isset ( $non_capturing_selectable_squares [ $x ] )    )                                                      // Nonapturing placement. 
                        {
                            $non_capturing_selectable_squares [ $x ] = array ( );
                        }

                        $non_capturing_selectable_squares [ $x ] [ $y ] = 1;                                                       
                   }
                }
            }
        }
    }

    $all_selectable_squares [ ] = $non_capturing_selectable_squares;

    $all_selectable_squares [ ] = $capturing_selectable_squares;

    $all_selectable_squares [ ] = $non_capturing_or_capturing_selectable_squares;


    
    return $all_selectable_squares;
}




function getSelectedSquareCoords ( )
{
    $selected_square = array ( );


    $sql = "SELECT board_x x, board_y y FROM board WHERE is_square_selected = 1";  // should be only one

    $result = self::DbQuery( $sql );

    $row = $result->fetch_assoc ( );

    if (    isset ( $row )    )
    {
        $selected_square [ 0 ] = $row [ "x" ];
        $selected_square [ 1 ] = $row [ "y" ];
    }


    return $selected_square;
}






function getOccupant ( $x, $y, $board, $N )
{                       
    if ( $x >= 0 && $x < $N )                                                       
    {
        if ( $y >= 0 && $y < $N )    
        {
            return $board [ $x ] [ $y ];
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




function isUnoccupiedSquare ( $x, $y, $board )
{
	//Get the board size
    $N=self::getGameStateValue("board_size");
     

    if (    self::isSquareOnBoard ( $x, $y, $N )    )
    {
        if ( $board [ $x ] [ $y ] == NULL )
            return true;
        else 
            return false;
    }
    else 
        return false;
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

        'placedStones' => $player_panel_str_0
	) );

	self::notifyAllPlayers( "swapColors", clienttranslate( '${player_name} is now playing <span style="color:#${player_color};">${player_colorname}</span>.' ), array(
		'i18n' => array( 'player_colorname' ),
		'player_id' => $player[1]['id'],
		'player_name' => $player[1]['name'],
		'player_color' => $player[0]['color'],
		'player_colorname' => self::getColorName($player[0]['color']),

        'placedStones' => $player_panel_str_1
	) );


	//Update player info
	self::reloadPlayersBasicInfos();

}






//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 
//
// IF THERE'S A CHOICE BETWEEN MAKING A NON-CAPTURING PLACEMENT AND A CAPTURING PLACEMENT 
//
//      SELECT THE SQUARE AND PUT UP THE CHOICE BUTTONS 
//
//  ELSE
//
//      PLACE THE APPROPRIATE STONE ( BLACK, WHITE, OR RED ) COMPLETING THE TURN
//
function selectSquare ( $x, $y )
{   
    self::checkAction( 'selectSquare' );  

    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( 'selectSquare ${var_1}, ${var_2}' ), 
    array(
        "var_1" => $x,
        "var_2" => $y
    ) );
    */

    // 
    // Don't rely on frontend to say what kind of square was clicked.
    //
    // Figure it out here
    //
    $all_selectable_squares = array ( );

    $all_selectable_squares = self::getAllSelectableSquares (    self::getActivePlayerId ( )    );


    $non_capturing_selectable_squares = $all_selectable_squares [ 0 ];

    $capturing_selectable_squares = $all_selectable_squares [ 1 ];

    $non_capturing_or_capturing_selectable_squares = $all_selectable_squares [ 2 ];


    $N = self::getGameStateValue("board_size");



    if (    isset ( $non_capturing_selectable_squares [ $x ] [ $y ] )    )
    {
        //
        // PLACE NON-CAPTURING STONE
        //
        self::placeNonCapturingStone ( $x, $y );


        //
        // Go to the next state
        //
        // Check if it's the first move
        //
	    if ( self::getGameStateValue ( "move_number" ) == 0 ) // 0 since the variable is incremented when the move has been validated
        {
            //Increment number of moves
	        self::incGameStateValue( "move_number", 1 );
				
	    	// Go to the choice state
	    	$this->gamestate->nextState( 'firstMoveChoice' );
	    }
        else  // Not the first move
        {
            $this->gamestate->nextState( 'placeStone' );
        }
    }
    else if (    isset ( $capturing_selectable_squares [ $x ] [ $y ] )   )
    {
        //
        // PLACE CAPTURING STONE
        //
        self::placeCapturingStone ( $x, $y );




        $this->gamestate->nextState( 'placeStone' );

    }
    else if (    isset ( $non_capturing_or_capturing_selectable_squares [ $x ] [ $y ] )    )
    {
        /*
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'Non-capturing or capturing square' ), 
        array(
        ) );
        */

        //
        //  MARK SELECTED SQUARE IN DATABASE 
        //
        $sql = "UPDATE board SET is_square_selected = 0 WHERE is_square_selected = 1";  
        self::DbQuery( $sql );

        $sql = "UPDATE board SET is_square_selected = 1 WHERE ( board_x, board_y) = ($x, $y)";  // CLICKED SQUARE
        self::DbQuery( $sql );


        $this->gamestate->nextState( 'selectNonCapturingOrCapturingSquare' );
    }
    else
    {
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'Not a legal placement.' ), 
        array(
        ) );

        //throw new feException( "Point is not selectable." );
    }
}




function placeNonCapturingStone ( $x, $y )
{    
    //$board = self::getBoard();

    $active_player_id = self::getActivePlayerId(); 

    $active_player_color = self::getPlayerColor ( $active_player_id );





    //
    // TURNS NUMBER STATISTIC
    //
    self::incStat( 1, "turns_number", $active_player_id);




    //
    // LAST MOVE INDICATOR
    //
    self::setGameStateValue ("last_move_x", $x);
    self::setGameStateValue ("last_move_y", $y);

    self::incGameStateValue ("last_move_id", 1);

    //
    // Notify about last move 
    //
    self::notifyAllPlayers( "lastMoveIndicator", clienttranslate( '' ), 
        array(
        'x' => $x,
        'y' => $y,
        'id' => self::getGameStateValue ("last_move_id")
        ) );






    //
    // PLACE BLACK OR WHITE STONE 
    //
    //      UPDATE GLOBALS AND DATABASE
    //
    if ( $active_player_color == "000000" )
    {
        $stone_id_value = self::getGameStateValue( "black_stone_id" );
        self::incGameStateValue( "black_stone_id", 1 );

        self::incGameStateValue( "total_black_stones", 1 );
    }
    else 
    {
        $stone_id_value = self::getGameStateValue( "white_stone_id" );
        self::incGameStateValue( "white_stone_id", 1 );

        self::incGameStateValue( "total_white_stones", 1 );
    }


    $sql = "UPDATE board SET board_player = $active_player_id, stone_id = $stone_id_value
            WHERE ( board_x, board_y) = ($x, $y)";  
    
    self::DbQuery( $sql );


    //
    // Notify about placed stone 
    //
    self::notifyAllPlayers( "stonePlaced", clienttranslate( '' ), 
        array(
        'x' => $x,
        'y' => $y,
        'player_id' => $active_player_id,
        'stone_id' => $stone_id_value
        ) );



    //
    // UPDATE PLAYER PANEL 
    //
    //      DISPLAY PLAYER'S NUMBER OF PLACED STONES
    //
    $total_black_stones = self::getGameStateValue ( "total_black_stones");
    $total_black_stones_str = "{$total_black_stones}";

    $total_white_stones = self::getGameStateValue ( "total_white_stones");
    $total_white_stones_str = "{$total_white_stones}";

    if (    $active_player_color == "000000"   ) // Black player is active
    {
        self::notifyAllPlayers("playerPanel",
            "",
            array(
            "placed_stones" => $total_black_stones_str,
            "player_id" => $active_player_id ));

        self::notifyAllPlayers("playerPanel",
            "",
            array(
            "placed_stones" => $total_white_stones_str,
            "player_id" => self::getOtherPlayerId($active_player_id) ));
    }
    else 
    {
        self::notifyAllPlayers("playerPanel",
            "",
            array(
            "placed_stones" => $total_white_stones_str,
            "player_id" => $active_player_id ));

        self::notifyAllPlayers("playerPanel",
            "",
            array(
            "placed_stones" => $total_black_stones_str,
            "player_id" => self::getOtherPlayerId($active_player_id) ));
    }


    $x_display_coord = chr( ord( "A" ) + $x );
    $y_display_coord = $y + 1;

    self::notifyAllPlayers( "stonePlacedHistory", clienttranslate( '${active_player_name} 
        ${x_display_coord}${y_display_coord}' ),     
        array(
            'active_player_name' => self::getActivePlayerName(),
            'x_display_coord' => $x_display_coord,
            'y_display_coord' => $y_display_coord
        ) );
}



function placeCapturingStone ( $x, $y )
{    
    $active_player_id = self::getActivePlayerId(); 

    $active_player_color = self::getPlayerColor ( $active_player_id );

    $inactive_player_id = self::getOtherPlayerId ( $active_player_id );

    $inactive_player_color = self::getPlayerColor ( $inactive_player_id );


    $board = self::getBoard ();




    //
    // TURNS NUMBER STATISTIC
    //
    self::incStat( 1, "turns_number", $active_player_id);



    //
    // LAST MOVE INDICATOR
    //
    self::setGameStateValue ("last_move_x", $x);
    self::setGameStateValue ("last_move_y", $y);

    self::incGameStateValue ("last_move_id", 1);

    //
    // Notify about last move 
    //
    self::notifyAllPlayers( "lastMoveIndicator", clienttranslate( '' ), 
        array(
        'x' => $x,
        'y' => $y,
        'id' => self::getGameStateValue ("last_move_id")
        ) );





    //
    // FIND OUT WHICH GROUPS WILL BE BOUNDED BY THE RED STONE 
    //

    //
    // TWO DIMENSIONAL ARRAY TO RECORD THE GROUP ID FOR EACH OCCUPIED SQUARE
    //
    //      ONE FOR EACH PLAYER
    //
    $group_IDs_active = array (); 
    $group_IDs_inactive = array (); 


	//Get the board size
    $N = self::getGameStateValue ("board_size");

    //
    // Set fresh group IDs to 0
    //
    $fresh_group_ID_active = 0;
    $fresh_group_ID_inactive = 0;

    //
    // Fill in $group_IDs_inactive
    //
    for ( $y_value = 0; $y_value < $N; $y_value++ )                   
    {
        for ( $x_value = 0; $x_value < $N; $x_value++ )  
        {
            if (    $board [ $x_value ] [ $y_value ] == $inactive_player_id    )                                      // Current square is occupied by active player
            {
                if (    self::getOccupant ( $x_value - 1, $y_value, $board, $N ) == $inactive_player_id    )          // If square to left of current square is occupied by active player...
                {
                    $group_IDs_inactive [ $x_value ] [ $y_value ] = $group_IDs_inactive [ $x_value - 1 ] [ $y_value ];              // ...copy its group ID to current square

                    if (    self::getOccupant ( $x_value, $y_value - 1, $board, $N ) == $inactive_player_id    )      // If square below current square is ALSO occupied by active player...
                    {
                        $current_group_ID = $group_IDs_inactive [ $x_value ] [ $y_value ];                            // ...replace all occurrences of current group ID with lower right group ID
                        $below_group_ID = $group_IDs_inactive [ $x_value ] [ $y_value - 1 ];

                        foreach ($group_IDs_inactive as &$group_IDs_row)         
                        {
                            foreach ($group_IDs_row as &$group_ID)         
                            {
                                if ( $group_ID == $current_group_ID )
                                {
                                     $group_ID = $below_group_ID;
                                }
                            }
                        }
                        unset($group_IDs_row);  // break the reference with the last element    
                        unset($group_ID);       // break the reference with the last element    
                    }
                }
                else if (    self::getOccupant ( $x_value, $y_value - 1, $board, $N ) == $inactive_player_id    )     // If square below current square is occupied by active player...
                {
                    $group_IDs_inactive [ $x_value ] [ $y_value ] = $group_IDs_inactive [ $x_value ] [ $y_value - 1 ];              // ...copy its group ID to current square
                }
                else
                {
                    $group_IDs_inactive [ $x_value ] [ $y_value ] = $fresh_group_ID_inactive++;                         // Give the current square a fresh group ID
                }
            }
        }
    }




    //
    // TEST 
    //  SHOW GROUP IDs FOR INACTIVE PLAYER 
    //
    /*
    for ( $y_value = 0; $y_value < $N; $y_value++ )                   // Start on bottom row.  $y = 0
    {
        for ( $x_value = 0; $x_value < $N; $x_value++ )  
        {
            if (    $board [ $x_value ] [ $y_value ] == $inactive_player_id    ) 
            {
                    self::notifyAllPlayers( "backendMessage", clienttranslate( '${var_1}, ${var_2} inactive player' ), 
                    array(
                            "var_1" => $x_value,
                            "var_2" => $y_value
                    ) );

                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'group id = ${var_1}' ), 
                    array(
                            "var_1" => $group_IDs_inactive [ $x_value ] [ $y_value ]
                    ) );
            }
        }
    }
    */


   

    //
    // Fill in $group_IDs_active
    //
    for ( $y_value = 0; $y_value < $N; $y_value++ )                   
    {
        for ( $x_value = 0; $x_value < $N; $x_value++ )  
        {
            if (    $board [ $x_value ] [ $y_value ] == $active_player_id    )                                      // Current square is occupied by active player
            {
                if (    self::getOccupant ( $x_value - 1, $y_value, $board, $N ) == $active_player_id    )          // If square to left of current square is occupied by active player...
                {
                    $group_IDs_active [ $x_value ] [ $y_value ] = $group_IDs_active [ $x_value - 1 ] [ $y_value ];              // ...copy its group ID to current square

                    if (    self::getOccupant ( $x_value, $y_value - 1, $board, $N ) == $active_player_id    )      // If square below current square is ALSO occupied by active player...
                    {
                        $current_group_ID = $group_IDs_active [ $x_value ] [ $y_value ];                            // ...replace all occurrences of current group ID with lower right group ID
                        $below_group_ID = $group_IDs_active [ $x_value ] [ $y_value - 1 ];

                        foreach ($group_IDs_active as &$group_IDs_row)         
                        {
                            foreach ($group_IDs_row as &$group_ID)         
                            {
                                if ( $group_ID == $current_group_ID )
                                {
                                     $group_ID = $below_group_ID;
                                }
                            }
                        }
                        unset($group_IDs_row);  // break the reference with the last element    
                        unset($group_ID);       // break the reference with the last element    
                    }
                }
                else if (    self::getOccupant ( $x_value, $y_value - 1, $board, $N ) == $active_player_id    )     // If square below current square is occupied by active player...
                {
                    $group_IDs_active [ $x_value ] [ $y_value ] = $group_IDs_active [ $x_value ] [ $y_value - 1 ];              // ...copy its group ID to current square
                }
                else
                {
                    $group_IDs_active [ $x_value ] [ $y_value ] = $fresh_group_ID_active++;                         // Give the current square a fresh group ID
                }
            }
        }
    }

    

    //
    //
    // LIST OF ARRAYS OF LIBERTIES FOR EACH GROUP ID 
    //
    //
    $inactive_groups_liberties = array ( ); 
    $active_groups_liberties = array ( );

    $directions = array (    array ( -1, 0 ), array ( 0, 1 ), array ( 1, 0 ), array ( 0, -1 )    );

    //
    //
    // INACTIVE GROUPS LIBERTIES 
    //
    //
    for ( $y_value = 0; $y_value < $N; $y_value++ )                   // Start on bottom row.  $y = 0
    {
        for ( $x_value = 0; $x_value < $N; $x_value++ )  
        {
            if ( $board [ $x_value ] [ $y_value ] == NULL )                                                             // Unoccupied square
            {
                foreach ( $directions as $direction )                                                       // Check surrounding squares for group IDs
                {
                    $neighbor_x = $x_value + $direction [ 0 ];
                    $neighbor_y = $y_value + $direction [ 1 ];

                    if (    isset ( $group_IDs_inactive [ $neighbor_x ] [ $neighbor_y ] )    )                // Neighboring group
                    {
                        $neighboring_group_ID = $group_IDs_inactive [ $neighbor_x ] [ $neighbor_y ];          // Neighboring group ID
                    
                        if (    ! isset ( $inactive_groups_liberties [ $neighboring_group_ID ] )    )         // If group ID not in groups liberties array, add it 
                        {
                            $inactive_groups_liberties [ $neighboring_group_ID ] = array ( );
                        }
                        
                        if (    ! isset ( $inactive_groups_liberties [ $neighboring_group_ID ] [ $x_value ] )    )  // Add this unoccupied square...
                        {                                                                                           // ...to groups liberties array [ $neighboring_group_ID ]
                            $inactive_groups_liberties [ $neighboring_group_ID ] [ $x_value ] = array ( ) ;
                        }

                        /*
                        self::notifyAllPlayers( "backendMessage", clienttranslate( '$inact_grps_libs [${var_1}] [${var_2}] [${var_3}] ' ), 
                        array(
                            "var_1" => $neighboring_group_ID,
                            "var_2" => $x_value,
                            "var_3" => $y_value
                        ) );
                        */

                        $inactive_groups_liberties [ $neighboring_group_ID ] [ $x_value ] [ $y_value ] = 1;
                    }
                }
            }
        }
    }



    //
    //
    // ACTIVE GROUPS LIBERTIES 
    //
    //
    for ( $y_value = 0; $y_value < $N; $y_value++ )                   // Start on bottom row.  $y = 0
    {
        for ( $x_value = 0; $x_value < $N; $x_value++ )  
        {
            if ( $board [ $x_value ] [ $y_value ] == NULL )                                                             // Unoccupied square
            {
                foreach ( $directions as $direction )                                                       // Check surrounding squares for group IDs
                {
                    $neighbor_x = $x_value + $direction [ 0 ];
                    $neighbor_y = $y_value + $direction [ 1 ];

                    if (    isset ( $group_IDs_active [ $neighbor_x ] [ $neighbor_y ] )    )                // Neighboring group
                    {
                        $neighboring_group_ID = $group_IDs_active [ $neighbor_x ] [ $neighbor_y ];          // Neighboring group ID
                    
                        if (    ! isset ( $active_groups_liberties [ $neighboring_group_ID ] )    )         // If group ID not in groups liberties array, add it 
                        {
                            $active_groups_liberties [ $neighboring_group_ID ] = array ( );
                        }
                        
                        if (    ! isset ( $active_groups_liberties [ $neighboring_group_ID ] [ $x_value ] )    )  // Add this unoccupied square...
                        {                                                                                   // ...to groups liberties array [ $neighboring_group_ID ]
                            $active_groups_liberties [ $neighboring_group_ID ] [ $x_value ] = array ( ) ;
                        }

                        /*
                        self::notifyAllPlayers( "backendMessage", clienttranslate( '$act_grps_libs [${var_1}] [${var_2}] [${var_3}] ' ), 
                        array(
                            "var_1" => $neighboring_group_ID,
                            "var_2" => $x_value,
                            "var_3" => $y_value
                        ) );
                        */

                        $active_groups_liberties [ $neighboring_group_ID ] [ $x_value ] [ $y_value ] = 1;
                    }
                }
            }
        }
    }



    //
    // GROUPS WITH EXACTLY ONE LIBERTY 
    //
    $vulnerable_group_IDs_active = array ( );
    $vulnerable_group_IDs_inactive = array ( );

    //
    // INACTIVE GROUPS
    //
    for ( $group_ID = 0; $group_ID < $fresh_group_ID_inactive; $group_ID++ )
    {
        $count_liberties_inactive = 0;

        if (    isset ( $inactive_groups_liberties [ $group_ID ] )    ) 
        {
            foreach ( $inactive_groups_liberties [ $group_ID ] as $row ) 
            {
                $count_liberties_inactive += count ( $row );
            }
        }

        if ( $count_liberties_inactive == 1 )
        {
            $vulnerable_group_IDs_inactive [ ] = $group_ID;
        }
    }

    /*
    foreach ( $vulnerable_group_IDs_inactive as $vulnerable_group_ID_inactive  )
    {
        self::notifyAllPlayers( "backendMessage", clienttranslate( '$vulnerable_group_ID_inactive = ${var_1}' ), 
        array(
            "var_1" => $vulnerable_group_ID_inactive
        ) );
    }
    */

    //
    // ACTIVE GROUPS
    //
    for ( $group_ID = 0; $group_ID < $fresh_group_ID_active; $group_ID++ )
    {
        $count_liberties_active = 0;

        if (    isset ( $active_groups_liberties [ $group_ID ] )    ) 
        {
            foreach ( $active_groups_liberties [ $group_ID ] as $row ) 
            {
                $count_liberties_active += count ( $row );
            }
        }

        if ( $count_liberties_active == 1 )
        {
            $vulnerable_group_IDs_active [ ] = $group_ID;
        }
    }

    /*
    foreach ( $vulnerable_group_IDs_active as $vulnerable_group_ID_active  )
    {
        self::notifyAllPlayers( "backendMessage", clienttranslate( '$vulnerable_group_ID_active = ${var_1}' ), 
        array(
            "var_1" => $vulnerable_group_ID_active
        ) );
    }
    */

           
    $removable_group_IDs_inactive = array ();
    $removable_group_IDs_active = array ();

    $removable_stone_IDs_inactive = array ();
    $removable_stone_IDs_active = array ();

    
    //
    //  MAKE LIST OF REMOVABLE GROUPS 
    //
    foreach ( $directions as $direction )                                                                           // CHECK SURROUNDING SQUARES FOR GROUP iD
    {
        $neighbor_x = $x + $direction [ 0 ];
        $neighbor_y = $y + $direction [ 1 ];

        if (    self::isSquareOnBoard ( $neighbor_x, $neighbor_y, $N )    )
        {
            if (    isset ( $group_IDs_inactive [ $neighbor_x ] [ $neighbor_y ] )    )                                          // INACTIVE NEIGHBOR
            {
                if (     in_array ( $group_IDs_inactive [ $neighbor_x ] [ $neighbor_y ], $vulnerable_group_IDs_inactive )    )  // Vulnerable inactive neighbor...
                {  

                    /*
                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'vuln inact grp ID ${var_1}' ), 
                    array(
                            "var_1" => $group_IDs_inactive [ $neighbor_x ] [ $neighbor_y ]
                    ) );
                    */


                    //
                    // 
                    //  ADD GROUP ID TO $removable_group_IDs_inactive 
                    //
                    //  BUT ONLY IF NOT ALREADY IN IT 
                    //
                    //  WAS BUG.  GROUP IDs WERE BEING COUNTED TWICE AND REMOVED STONE COUNT DOUBLED, F***ING UP THE STONE COUNT 
                    //
                    //
                    if (  !  in_array ( $group_IDs_inactive [ $neighbor_x ] [ $neighbor_y ], $removable_group_IDs_inactive )    )
                    {
                        $removable_group_IDs_inactive [ ] = $group_IDs_inactive [ $neighbor_x ] [ $neighbor_y ];
                    }
                }
            }
            else if (    isset ( $group_IDs_active [ $neighbor_x ] [ $neighbor_y ] )    )                                       // ACTIVE NEIGHBOR
            {
                if (     in_array ( $group_IDs_active [ $neighbor_x ] [ $neighbor_y ], $vulnerable_group_IDs_active )    )      // Vulnerable active neighbor...
                {  

                    /*
                    self::notifyAllPlayers( "backendMessage", clienttranslate( 'vuln act grp ID ${var_1}' ), 
                    array(
                            "var_1" => $group_IDs_active [ $neighbor_x ] [ $neighbor_y ]
                    ) );
                    */

                    //
                    // 
                    //  ADD GROUP ID TO $removable_group_IDs_inactive 
                    //
                    //  BUT ONLY IF NOT ALREADY IN IT 
                    //
                    //  WAS BUG.  GROUP IDs WERE BEING COUNTED TWICE AND REMOVED STONE COUNT DOUBLED, F***ING UP THE STONE COUNT 
                    //
                    //
                    if (  !  in_array ( $group_IDs_active [ $neighbor_x ] [ $neighbor_y ], $removable_group_IDs_active )    )
                    {
                        $removable_group_IDs_active [ ] = $group_IDs_active [ $neighbor_x ] [ $neighbor_y ];
                    }
                }
            }
        }
    }


    //
    //  MAKE LISTS OF REMOVABLE STONES 
    //
    //
    //      Inactive stones
    // 
    foreach ( $removable_group_IDs_inactive as $removable_group_ID_inactive )
    {
        for ( $y_value = 0; $y_value < $N; $y_value++)
        {
            for ( $x_value = 0; $x_value < $N; $x_value++)
            {
                if (    isset ( $group_IDs_inactive [ $x_value ] [ $y_value ] )    )
                {
                    $group_ID = $group_IDs_inactive [ $x_value ] [ $y_value ];

                    if ( $group_ID == $removable_group_ID_inactive )
                    {
                        $stone_ID = self::getStoneID ( $x_value, $y_value );

                        $removable_stone_IDs_inactive [ ] = $stone_ID;
                    }
                }
            }
        }
    }

    /*
    foreach ( $removable_stone_IDs_inactive as $removable_stone_ID_inactive )
    {
        self::notifyAllPlayers( "backendMessage", clienttranslate( '$removable_stone_ID_inactive = ${var_1}' ), 
        array(
            "var_1" => $removable_stone_ID_inactive
        ) );
    }
    */

    //
    //      Active stones
    // 
    foreach ( $removable_group_IDs_active as $removable_group_ID_active )
    {
        for ( $y_value = 0; $y_value < $N; $y_value++)
        {
            for ( $x_value = 0; $x_value < $N; $x_value++)
            {
                if (    isset ( $group_IDs_active [ $x_value ] [ $y_value ] )    )
                {
                    $group_ID = $group_IDs_active [ $x_value ] [ $y_value ];

                    if ( $group_ID == $removable_group_ID_active )
                    {
                        $stone_ID = self::getStoneID ( $x_value, $y_value );

                        $removable_stone_IDs_active [ ] = $stone_ID;
                    }
                }
            }
        }
    }

    /*
    foreach ( $removable_stone_IDs_active as $removable_stone_ID_active )
    {
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'AA $removable_stone_ID_active = ${var_1}' ), 
        array(
            "var_1" => $removable_stone_ID_active
        ) );
    }
    */

    //
    // PLACE RED STONE 
    //
    //      UPDATE GLOBALS AND DATABASE
    //
    $stone_id_value = self::getGameStateValue( "red_stone_id" );
    self::incGameStateValue( "red_stone_id", 1 );

    //
    // player_id = 0 for red stone
    //
    $sql = "UPDATE board SET board_player = 0, stone_id = $stone_id_value 
            WHERE ( board_x, board_y) = ($x, $y)";  
    
    self::DbQuery( $sql );










    //
    //  REMOVE REMOVABLE CHECKERS FROM DATABASE 
    //
    //  AND UPDATE GLOBALS
    //
    //      INACTIVE STONES 
    //
    foreach ( $removable_stone_IDs_inactive as $removable_stone_ID_inactive )
    {
        $sql = "UPDATE board SET board_player = NULL, stone_id = NULL WHERE stone_id = $removable_stone_ID_inactive";    
                                
        self::DbQuery( $sql );
    }

    //
    //      ACTIVE STONES 
    //
    foreach ( $removable_stone_IDs_active as $removable_stone_ID_active )
    {
        $sql = "UPDATE board SET board_player = NULL, stone_id = NULL WHERE stone_id = $removable_stone_ID_active";    
                                
        self::DbQuery( $sql );
    }

    // 
    // UPDATE GLOBALS
    //
    $count_removable_stones_active = count ( $removable_stone_IDs_active );
    $count_removable_stones_inactive = count ( $removable_stone_IDs_inactive );


    if ( $active_player_color == "000000" )
    {
        /*
        self::notifyAllPlayers( "backendMessage", clienttranslate( '$active_player_color = ${var_1}' ), 
        array(
            "var_1" => $active_player_color
        ) );

        self::notifyAllPlayers( "backendMessage", clienttranslate( 'cnt rm st act, inact = ${var_1}, ${var_2}' ), 
        array(
            "var_1" => $count_removable_stones_active,
            "var_2" => $count_removable_stones_inactive
        ) );
        */
        self::incGameStateValue ( "total_black_stones", -$count_removable_stones_active );

        self::incGameStateValue ( "total_white_stones", -$count_removable_stones_inactive );
    }
    else 
    {
        /*
        self::notifyAllPlayers( "backendMessage", clienttranslate( '$active_player_color = ${var_1}' ), 
        array(
            "var_1" => $active_player_color
        ) );

        self::notifyAllPlayers( "backendMessage", clienttranslate( 'cnt rm st act, inact = ${var_1}, ${var_2}' ), 
        array(
            "var_1" => $count_removable_stones_active,
            "var_2" => $count_removable_stones_inactive
        ) );
        */
        self::incGameStateValue ( "total_black_stones", -$count_removable_stones_inactive );

        self::incGameStateValue ( "total_white_stones", -$count_removable_stones_active );
    }








    //
    // NOTIFY ABOUT PLACED RED STONE 
    //
    self::notifyAllPlayers( "stonePlaced", clienttranslate( '' ), 
        array(
        'x' => $x,
        'y' => $y,
        'player_id' => $active_player_id,
        'stone_id' => $stone_id_value
        ) );

    //
    // NOTIFY HISTORY PANEL ABOUT PLACED RED STONE
    //
    $x_display_coord = chr( ord( "A" ) + $x );
    $y_display_coord = $y + 1;

    self::notifyAllPlayers( "stonePlacedHistory", clienttranslate( '${active_player_name} 
        ${x_display_coord}${y_display_coord} *' ),     
        array(
            'active_player_name' => self::getActivePlayerName(),
            'x_display_coord' => $x_display_coord,
            'y_display_coord' => $y_display_coord
        ) );


    //
    // NOTIFY ABOUT REMOVED STONES
    //
    //
    //      INACTIVE STONES 
    //
    self::notifyAllPlayers( "stonesRemoved", clienttranslate( '' ), 
        array(
        'player_id' => $inactive_player_id,
        'removable_stone_IDs' => $removable_stone_IDs_inactive
        ) );

    //
    //      ACTIVE STONES 
    //
    self::notifyAllPlayers( "stonesRemoved", clienttranslate( '' ), 
        array(
        'player_id' => $active_player_id,
        'removable_stone_IDs' => $removable_stone_IDs_active
        ) );












    //
    // UPDATE PLAYER PANEL 
    //
    //      DISPLAY PLAYER'S NUMBER OF PLACED STONES
    //
    $total_black_stones = self::getGameStateValue ( "total_black_stones");
    $total_black_stones_str = "{$total_black_stones}";

    $total_white_stones = self::getGameStateValue ( "total_white_stones");
    $total_white_stones_str = "{$total_white_stones}";

    if (    $active_player_color == "000000"   ) // Black player is active
    {
        self::notifyAllPlayers("playerPanel",
            "",
            array(
            "placed_stones" => $total_black_stones_str,
            "player_id" => $active_player_id ));

        self::notifyAllPlayers("playerPanel",
            "",
            array(
            "placed_stones" => $total_white_stones_str,
            "player_id" => self::getOtherPlayerId($active_player_id) ));
    }
    else 
    {
        self::notifyAllPlayers("playerPanel",
            "",
            array(
            "placed_stones" => $total_white_stones_str,
            "player_id" => $active_player_id ));

        self::notifyAllPlayers("playerPanel",
            "",
            array(
            "placed_stones" => $total_black_stones_str,
            "player_id" => self::getOtherPlayerId($active_player_id) ));
    }

















    //  ###############################################
    //  ###############################################
    //
    //  END OF GAME CHECK.  WILL HAPPEN AFTER A CAPTURE.
    //
    //  ###############################################
    //  ###############################################
    //
 
    //$total_black_stones = self::getGameStateValue ( "total_black_stones");
    //$total_white_stones = self::getGameStateValue ( "total_white_stones");

    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( '$total_black_stones = ${var_1}' ), 
    array(
        "var_1" => $total_black_stones
    ) );

    self::notifyAllPlayers( "backendMessage", clienttranslate( '$total_blue_checkers = ${var_1}' ), 
    array(
         "var_1" => $total_blue_checkers
    ) );
    */
    if ( $total_white_stones == 0 && $total_black_stones == 0 )                     // ACTIVE PLAYER WINS
    {
        $sql = "UPDATE player SET player_score = 1 WHERE player_id = $active_player_id";

        self::DbQuery( $sql );


        $newScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );

        self::notifyAllPlayers( "newScores", "", array(
            "scores" => $newScores
        ) );

        /*
        self::notifyAllPlayers( "backendMessage", clienttranslate( 'Active player won.  Go to endGame.' ), 
         array(
        ) );
        */

        $this->gamestate->nextState( 'endGame' );

    }
    else if ( $total_white_stones == 0 )                                            //  BLACK WINS
    {
        if ( $inactive_player_color == "000000") // Black
        {
            $sql = "UPDATE player SET player_score = 1 WHERE player_id = $inactive_player_id";
        }
        else 
        {
             $sql = "UPDATE player SET player_score = 1 WHERE player_id = $active_player_id";
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
    else if ( $total_black_stones == 0 )                                      // WHITE WINS
    {
        if ( $inactive_player_color == "6464ad") // white
        {
            $sql = "UPDATE player SET player_score = 1 WHERE player_id = $inactive_player_id";
        }
        else 
        {
            $sql = "UPDATE player SET player_score = 1 WHERE player_id = $active_player_id";
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
}



function getStoneID ( $x, $y )
{      
    $sql = "SELECT stone_id stone_id FROM board WHERE ( board_x, board_y) = ( $x, $y )"; // should be only one

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






function unselectSelectedSquare ( )
{    
    //
    //  UNSELECT SQUARE IN DATABASE 
    //
    $sql = "UPDATE board SET is_square_selected = 0 WHERE 1";  
    self::DbQuery( $sql );


    //
    // NOTIFY ABOUT UNSELECT SELECTED SQUARE 
    //

    // No, not necessary







    //
    // Go to the next state
    //
    $this->gamestate->nextState( 'unselectSelectedSquare' );

}


//
//  GET SELECTED SQUARE FROM DATABASE 
//
//  PLACE BLACK OR WHITE STONE
//
function nonCaptureOnSelectedSquare()
{
    self::checkAction( 'chooseNoncapturing' );  


    $selected_square = self::getSelectedSquareCoords ( );

    $x = $selected_square [ 0 ];
    $y = $selected_square [ 1 ];


    //
    //  UNSELECT SQUARE IN DATABASE 
    //
    $sql = "UPDATE board SET is_square_selected = 0 WHERE 1";  
    self::DbQuery( $sql );


    //
    // PLACE NON-CAPTURING STONE
    //
    self::placeNonCapturingStone ( $x, $y );

    //
    // Go to the next state
    //
    $this->gamestate->nextState( 'placeStone' );
}



//
//  GET SELECTED SQUARE FROM DATABASE 
//
//  PLACE RED STONE
//
function captureOnSelectedSquare()
{
    self::checkAction( 'chooseCapturing' );  


    $selected_square = self::getSelectedSquareCoords ( );

    $x = $selected_square [ 0 ];
    $y = $selected_square [ 1 ];


    //
    //  UNSELECT SQUARE IN DATABASE 
    //
    $sql = "UPDATE board SET is_square_selected = 0 WHERE 1";  
    self::DbQuery( $sql );


    //
    // PLACE CAPTURING STONE
    //
    self::placeCapturingStone ( $x, $y );

    //
    // Go to the next state
    //
    $this->gamestate->nextState( 'placeStone' );
}





function getBoardStoneIDs()
{
    return self::getDoubleKeyCollectionFromDB( "SELECT board_x x, board_y y, stone_id
                                                FROM board", true );
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






    //
    // TURNS NUMBER STATISTIC - SWITCH STATS
    //
    self::setStat( 1, "turns_number", $active_player_id);

    self::setStat( 0, "turns_number", $inactive_player_id);









    //Update players info
	self::reloadPlayersBasicInfos();

	//Let's the other player play
	$this->gamestate->nextState( 'nextTurn' );
}



    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

function argSelectSquare ( )
{
    return array(                                      // nonCapturingSelectableSquares, capturingSelectableSquares, and nonCapturingOrCapturingSelectableSquares
        'allSelectableSquares' => self::getAllSelectableSquares ( self::getActivePlayerId() )  
    );
}

function argChooseCaptureOrNonCapture ( )
{
    return array(                                      // selectedSquare
        'selectedSquare' => self::getSelectedSquareCoords ( )  
    );
}



//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

function stNextPlayer()
{   
    // Activate next player
    $active_player_id = self::activeNextPlayer();

    self::giveExtraTime( $active_player_id );                           // Active player can play. Give him some extra time.
    $this->gamestate->nextState( 'nextTurn' );
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
