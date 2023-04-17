<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * Cephalopod implementation : © <Your name here> <Your email address here>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * cephalopod.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class Cephalopod extends Table
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
            "black_die_base_id" => 20,                   // Base value. Must be modified to include dice value information.
            "green_die_base_id" => 21,                   // Base value. Must be modified to include dice value information.
            "total_black_dice"=> 22,                // Total number of black dice on the board
            "total_green_dice"=> 23,                // Total number of green dice on the board
            "last_move_x"=> 30,                     // Last move indicator: u
            "last_move_y"=> 31,                     // Last move indicator: v
            "last_move_id"=> 32,                    // Last move indicator: id
            "board_size" => 101,                    // Size of board
        ) );        
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "cephalopod";
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
        self::setGameStateValue( "black_die_base_id", 10000 ); // Add dice value.  E.g., 16,000 has dice value 6.
        self::setGameStateValue( "green_die_base_id", 20000 );
        self::setGameStateValue( "total_black_dice", 0 );
        self::setGameStateValue( "total_green_dice", 0 );

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
            /*
            if( $color == '000000' )
                $black_player_id = $player_id;
            else
                $green_player_id = $player_id;
            */

        }

        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );

        //self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();

        
        $N=self::getGameStateValue("board_size");

        //  
        //
        //  FILL BOARD WITH NULL VALUES
        //
        //
        $sql = "INSERT INTO board (board_x, board_y, board_player, die_id) VALUES ";

        $sql_values = array();

        for ( $x = 0; $x < 5; $x++ ) 
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
        $die_id = self::getGameStateValue ( "black_die_base_id" ) + 6000; // Black die value 6
        self::incGameStateValue ( "black_die_base_id", 1 );

        $sql = "UPDATE board SET board_player = $black_player_id, die_id = $die_id
                WHERE ( board_x, board_y) = (0, 2)";  
    
        self::DbQuery( $sql );

        self::incGameStateValue( "total_black_dice", 1 );


        $die_id = self::getGameStateValue ( "black_die_base_id" ) + 5000; // Black die value 5
        self::incGameStateValue ( "black_die_base_id", 1 );

        $sql = "UPDATE board SET board_player = $black_player_id, die_id = $die_id
                WHERE ( board_x, board_y) = (1, 1)";  
    
        self::DbQuery( $sql );

        self::incGameStateValue( "total_black_dice", 1 );


        $die_id = self::getGameStateValue ( "black_die_base_id" ) + 4000; // Black die value 4
        self::incGameStateValue ( "black_die_base_id", 1 );

        $sql = "UPDATE board SET board_player = $black_player_id, die_id = $die_id
                WHERE ( board_x, board_y) = (2, 0)";  
    
        self::DbQuery( $sql );

        self::incGameStateValue( "total_black_dice", 1 );


        $die_id = self::getGameStateValue ( "black_die_base_id" ) + 1000; // Black die value 1
        self::incGameStateValue ( "black_die_base_id", 1 );

        $sql = "UPDATE board SET board_player = $black_player_id, die_id = $die_id
                WHERE ( board_x, board_y) = (3, 2)";  
    
        self::DbQuery( $sql );

        self::incGameStateValue( "total_black_dice", 1 );


        $die_id = self::getGameStateValue ( "black_die_base_id" ) + 2000; // Black die value 2
        self::incGameStateValue ( "black_die_base_id", 1 );

        $sql = "UPDATE board SET board_player = $black_player_id, die_id = $die_id
                WHERE ( board_x, board_y) = (4, 1)";  
    
        self::DbQuery( $sql );

        self::incGameStateValue( "total_black_dice", 1 );


        //
        //  GREEN 
        //
        $die_id = self::getGameStateValue ( "green_die_base_id" ) + 3000; // Green die value 3
        self::incGameStateValue ( "green_die_base_id", 1 );

        $sql = "UPDATE board SET board_player = $green_player_id, die_id = $die_id
                WHERE ( board_x, board_y) = (0, 0)";  
    
        self::DbQuery( $sql );

        self::incGameStateValue( "total_green_dice", 1 );


        $die_id = self::getGameStateValue ( "green_die_base_id" ) + 1000; // Green die value 1
        self::incGameStateValue ( "green_die_base_id", 1 );

        $sql = "UPDATE board SET board_player = $green_player_id, die_id = $die_id
                WHERE ( board_x, board_y) = (2, 1)";  
    
        self::DbQuery( $sql );

        self::incGameStateValue( "total_green_dice", 1 );


        $die_id = self::getGameStateValue ( "green_die_base_id" ) + 2000; // Green die value 2
        self::incGameStateValue ( "green_die_base_id", 1 );

        $sql = "UPDATE board SET board_player = $green_player_id, die_id = $die_id
                WHERE ( board_x, board_y) = (3, 0)";  
    
        self::DbQuery( $sql );

        self::incGameStateValue( "total_green_dice", 1 );
        */



        //
        //
        //
        // SHOW WHATS IN THE DATABASE AT THIS POINT ##########################################################################################
        //
        //
        //
        /*
        $sql = "SELECT board_x x, board_y y, die_id die_id
                FROM board WHERE 1";
        //$sql = "SELECT board_x x, board_y y, die_id die_id
        //        FROM board WHERE board_player = $active_player_id";

        $result = self::DbQuery( $sql );

        if ( $result->num_rows > 0 ) 
        {
             // output data of each row
            while (    $row = $result->fetch_assoc()    ) 
            {
                self::notifyAllPlayers( "backendMessage", clienttranslate( '${var_1}' ), 
                    array(
                        "var_1" => $row["x"]." ".$row["y"]." ". $row["die_id"]
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
  
        // TODO: Gather all information about current game situation (visible by player $current_player_id).
  
        //
        // Get board occupied cells
        // 
        $sql = "SELECT board_x x, board_y y, board_player player, die_id die_id
                    FROM board
                    WHERE board_player IS NOT NULL";
        $result['board'] = self::getObjectListFromDB( $sql );


		//
        // Get  board size 
        //
        $N=self::getGameStateValue("board_size");
		$result['board_size'] = $N;


        //
        // Placed dice
        //
        $activePlayerId = self::getActivePlayerId();
        $otherPlayerId = self::getOtherPlayerId($activePlayerId);

        $active_player_color = self::getPlayerColor ( $activePlayerId );


        $placedDice = array();

        if (    $active_player_color == "000000"    )
        {
            $placedDice [$activePlayerId] = self::getGameStateValue( "total_black_dice" );
            $placedDice [$otherPlayerId] = self::getGameStateValue( "total_green_dice" );
        }
        else
        {
            $placedDice [$activePlayerId] = self::getGameStateValue( "total_green_dice" );
            $placedDice [$otherPlayerId] = self::getGameStateValue( "total_black_dice" );
        }
        
        $result['placedDice'] = $placedDice;



        
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
        $total_black_dice = self::getGameStateValue ( "total_black_dice");

        $total_green_dice = self::getGameStateValue ( "total_green_dice");

        $total_dice = $total_black_dice + $total_green_dice;


        $N = self::getGameStateValue ( "board_size" );

        switch ( $N )
        {
            case 3:
                $number_of_squares = 15;
                break;

            case 5:
                $number_of_squares = 25;
                break;
        }
            
        return $total_dice / $number_of_squares * 100;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

//
// Actually returns an array of arrays.  Selected square, selectable dice, selected dice
//
function getTripleSetOfArgs ( )
{                       
    $triple_set_of_args = array ( );


    $selected_square_coords = array ( );
    $selected_dice_coords = array ( );
    $selectable_dice_coords = array ( );


    $selected_square_coords = self::getSelectedSquareCoords ( );
    $selected_dice_coords = self::getSelectedDiceCoords ( );
    $selectable_dice_coords = self::getSelectableDiceCoords ( );


    $triple_set_of_args [ ] = $selected_square_coords;  // add onto 
    $triple_set_of_args [ ] = $selected_dice_coords;    // add onto 
    $triple_set_of_args [ ] = $selectable_dice_coords;  // add onto 


    return $triple_set_of_args;
}




function getSelectedDiceIDs ( )
{      
    $selected_dice_IDs = array ( );


    $sql = "SELECT die_id die_id FROM board WHERE is_die_selected = 1";

    $result = self::DbQuery( $sql );


    if ( $result->num_rows > 0 ) 
    {
        while (    $row = $result->fetch_assoc()    ) 
        {
            $selected_dice_IDs [ ] = $row [ "die_id" ];
        }
    } 


    return $selected_dice_IDs;
}





function getSelectedDiceCoords ( )
{      
    $board_selected_dice_coords = array ( );

    $selected_dice_coords = array ( );


    $board_selected_dice_coords = self::getBoardSelectedDice ( );


    $N=self::getGameStateValue("board_size");

    for( $x=0; $x<5; $x++ )
    {
        for( $y=0; $y<$N; $y++ )
        {
           if ( $board_selected_dice_coords [ $x ] [ $y ] == 1 )
           {
               if(    ! isset ( $selected_dice_coords [ $x ] )    )
               {
                    $selected_dice_coords [ $x ] = array ( ) ;
               }

               $selected_dice_coords [ $x ] [ $y ] = 1;

               /*
               self::notifyAllPlayers( "backendMessage", clienttranslate( 'selected dice coords: ${var_1}, ${var_2}' ), 
                   array(
                       "var_1" => $x,
                       "var_2" => $y
                   ) );
               */
           }
        }
    }

    return $selected_dice_coords;
}







//
//
//  ONLY RETURN DICE COORDS THAT 
//      1. ARE NOT ALREADY SELECTED
//      2. CAN COMBINE WITH SELECTED DICE
//

function getSelectableDiceCoords ( )
{     
    $board_die_IDs = array ( );

    $selected_square = array ( );

    $selected_dice_IDs = array ( );


    $selectable_dice_IDs = array ( );

    $selectable_dice_coords = array ( );


    $raw_possible_combinations = array ( );


    $board_die_IDs = self::getBoardDieIDs ( );

    $selected_square_coords = self::getSelectedSquareCoords ( );

    $raw_possible_combinations = self::getRawPossibleCombinations ( $selected_square_coords [ 0 ], $selected_square_coords [ 1 ] );

    //
    //  RAW SELECTABLE DICE IDS, REGARDLESS IF ALREADY SELECTED
    //
    foreach ( $raw_possible_combinations as $raw_possible_combination )
    {
        foreach ( $raw_possible_combination as $selectable_die_id )
        {
            if (    ! in_array ( $selectable_die_id, $selectable_dice_IDs )    )
            {
                $selectable_dice_IDs [ ] = $selectable_die_id; 
            }
        }
    }

    //
    // Get sum of selected dice 
    //
    $sum_of_selected_dice = 0;

    $selected_dice_IDs = self::getSelectedDiceIDs ( );


    foreach ( $selected_dice_IDs as $selected_die_id )
    {
        $sum_of_selected_dice += self::getPipValue ( $selected_die_id );
    }

    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( '$sum_of_selected_dice: ${var_1}' ), 
        array(
            "var_1" => $sum_of_selected_dice
            ) );
    */


    //
    // SELECTABLE DICE COOORDS
    //
    // ONLY RETURN COORDS 
    //      1. NOT ALREADY SELECTED
    //      2. CAN COMBINE WITH SELECTED DICE
    //
    $selected_dice_coords = self::getSelectedDiceCoords ( );


    $N=self::getGameStateValue("board_size");


    for( $x=0; $x<5; $x++ )
    {
        for( $y=0; $y<$N; $y++ )
        {
            if (    isset ( $board_die_IDs [ $x ] [ $y ] )    )                                 // Die is on the board at x, y
            {
                if (    in_array ( $board_die_IDs [ $x ] [ $y ], $selectable_dice_IDs )    )    // It's in the raw selectable dice array
                {
                    $die_id = self::getDieID ( $x, $y );                                        // Selectable die ID at x, y

                    /*
                    self::notifyAllPlayers( "backendMessage", clienttranslate( '$die_id: ${var_1}' ), 
                        array(
                            "var_1" => $die_id
                            ) );
                    */

                    if (    ! isset ( $selected_dice_coords [ $x ] [ $y ] )                         // If not already selected...
                                   && self::valueLowEnough ( $die_id, $sum_of_selected_dice )    )  // and pip value is low enough 
                    {
                        if(    ! isset ( $selectable_dice_coords [ $x ] )    )                      // Add to result array
                        {
                            $selectable_dice_coords [ $x ] = array ( ) ;
                        }

                        $selectable_dice_coords [ $x ] [ $y ] = true;

                        /*
                        self::notifyAllPlayers( "backendMessage", clienttranslate( 'selectable dice coords: ${var_1}, ${var_2}' ), 
                        array(
                            "var_1" => $x,
                            "var_2" => $y
                            ) );
                        */

                    }
                }
            }
        }
    }

    return $selectable_dice_coords;
}






function valueLowEnough ( $die_id, $sum_of_selected_dice )
{
    if (    self::getPipValue ( $die_id ) + $sum_of_selected_dice <= 6 )
    {
        return true;
    }
    else 
    {
        return false;
    }
}






function getDieID ( $x, $y )
{      
    $sql = "SELECT die_id die_id FROM board WHERE ( board_x, board_y) = ($x, $y)"; // should be only one

    $result = self::DbQuery( $sql );


    if ( $result->num_rows > 0 ) 
    {
        while (    $row = $result->fetch_assoc()    ) 
        {
            $dice_IDs [ ] = $row [ "die_id" ];
        }
    } 

    $die_id = $dice_IDs [0];

    return $die_id;
}







function getSelectableSquareCoords ( )
//function getSelectableSquares ( $active_player_id )
{                       
    /*
    self::notifyAllPlayers( "backendMessage", clienttranslate( '## getSelectableSquareCoords ( ) ##' ), 
    array(
    ) );
    */

    $board = self::getBoard ( );

    //$board_dice = self::getBoardDieIDs ( );


    $selectable_square_coords = array ( );

	//Get the board size
    $N = self::getGameStateValue ("board_size");

    for ( $x = 0; $x < 5; $x++ )                   
    {
        for ( $y = 0; $y < $N; $y++ ) 
        {
            if (    ! isset ( $board [ $x ] [ $y ] )    )
            {
                    if (    ! isset ( $selectable_square_coords [ $x ] )    )
                    {
                        $selectable_square_coords [ $x ] = array ( );
                    }

                    $selectable_square_coords [ $x ] [ $y ] = true;
            }
        }
    }
    
    return $selectable_square_coords;
}




function getBoardSelectedSquares() // should be just 1
{
    return self::getDoubleKeyCollectionFromDB( "SELECT board_x x, board_y y, is_square_selected is_square_selected
                                                FROM board", true );
}



function getBoardSelectedDice() 
{
    return self::getDoubleKeyCollectionFromDB( "SELECT board_x x, board_y y, is_die_selected is_die_selected
                                                FROM board", true );
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
            return clienttranslate('BLACK');
        if ($color=='00a400')
            return clienttranslate('GREEN');
} 


function getPlayerColor ( $player_id ) 
{
    $players = self::loadPlayersBasicInfos();

    if (isset ( $players [ $player_id ] ) )
        return $players[ $player_id ][ 'player_color' ];
    else
        return null;
}





//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

//
//                  ##############################################################################################
//  SELECT SQUARE   ##############################################################################################
//                  ##############################################################################################
//
//
//  If there are no possible combinations of dice adjacent to the clicked square, either because there are less 
//  than 2 adjacent dice, or because the adjacent dice can't be combined to form a sum of 6 or less, place a 1, 
//  completing the move.
//
//  Otherwise, if there is exactly one way of combining adjacent dice, place a die showing their sum, completing 
//  the move.
//
//  Otherwise, there must be more than one way to combine adjacent dice, so change state to select die, providing 
//  array of selectable dice, which array will then be passed to frontend, where the selectable dice will be 
//  highlighted.
//
function selectSquare ( $x, $y )
{
    self::checkAction( 'selectSquare' );  
    
    $active_player_id = self::getActivePlayerId ( ); 
    $inactive_player_id = self::getOtherPlayerId ( $active_player_id ); 

    $active_player_color = self::getPlayerColor ( $active_player_id );
        


    $raw_possible_combinations = array ( );


    //
    // Check if this is a selectable square
    //
    $board = self::getBoard ( );  // HAD LEFT THIS LINE OUT.  BUG.  ERROR.  CAUSED A DIE TO BE ADDED TO A SQUARE THAT ALREADY HAD A DIE ON IT. ###################################

    if (    ! isset ( $board [ $x ] [ $y ] )    )
    {




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






        $raw_possible_combinations = self::getRawPossibleCombinations ( $x, $y );

        $ways_of_combining_neighbors = count ( $raw_possible_combinations );

        //
        // If no neighboring dice combinations are possible, add a 1 to the board 
        //
        if ( $ways_of_combining_neighbors == 0 )                                                    // 0 POSSIBLE NEIGHBOR COMBINATIONS
        {
            //
            // Place die 
            //
            //      Update globals and database 
            //
            if (    $active_player_color == "000000"    )
            {
                $die_id_value = self::getGameStateValue( "black_die_base_id" );
                $die_id_value += 1000;                                         // Pip value = 1

                self::incGameStateValue( "black_die_base_id", 1 );

                self::incGameStateValue( "total_black_dice", 1 );
            }
            else 
            {
                $die_id_value = self::getGameStateValue( "green_die_base_id" );
                $die_id_value += 1000;                                         // Pip value = 1

                self::incGameStateValue( "green_die_base_id", 1 );

                self::incGameStateValue( "total_green_dice", 1 );
            }

            $sql = "UPDATE board SET board_player = $active_player_id, die_id = $die_id_value
                    WHERE ( board_x, board_y) = ($x, $y)";  
    
            self::DbQuery( $sql );


            //
            // Notify about placed die 
            //
            self::notifyAllPlayers( "diePlaced", clienttranslate( '' ), 
                array(
                'x' => $x,
                'y' => $y,
                'player_id' => $active_player_id,
                'die_id' => $die_id_value
                ) );


            self::updatePlayerPanelAndHistory ( $x, $y );



            //Update players info
	        self::reloadPlayersBasicInfos();

            //
            // Go to the next state
            //
            $this->gamestate->nextState( 'selectDeterminingSquare' );
        }        
        else if ( $ways_of_combining_neighbors == 1 )                                                // selectSquare () ... 1 POSSIBLE NEIGHBOR COMBINATION 
        {
            foreach ( $raw_possible_combinations as $raw_possible_combination )
            {
                $combinable_die_id_0 = $raw_possible_combination [ 0 ];
                $combinable_die_id_1 = $raw_possible_combination [ 1 ];

                $pip_value_0 = self::getPipValue ( $combinable_die_id_0 );
                $pip_value_1 = self::getPipValue ( $combinable_die_id_1 );


                $pip_sum = $pip_value_0 + $pip_value_1;

                /*
                self::notifyAllPlayers( "backendMessage", clienttranslate( '$pip_sum: ${var_1}' ), 
                array(
                    "var_1" => $pip_sum
                    ) );
                */
            }

            //
            //  PLACE DIE 
            //
            //      Update globals and database 
            //
            if (    $active_player_color == "000000"    )
            {
                $die_id_value = self::getGameStateValue( "black_die_base_id" );
                $die_id_value += $pip_sum * 1000;                                              // Put pip sum in die_id_value

                self::incGameStateValue( "black_die_base_id", 1 );

                self::incGameStateValue( "total_black_dice", 1 );
            }
            else 
            {
                $die_id_value = self::getGameStateValue( "green_die_base_id" );
                $die_id_value += $pip_sum * 1000;                                              // Put pip sum in die_id_value

                self::incGameStateValue( "green_die_base_id", 1 );

                self::incGameStateValue( "total_green_dice", 1 );
            }

            $sql = "UPDATE board SET board_player = $active_player_id, die_id = $die_id_value
                    WHERE ( board_x, board_y) = ($x, $y)";  
    
            self::DbQuery( $sql );


            //
            // Notify about placed die 
            //
            self::notifyAllPlayers( "diePlaced", clienttranslate( '' ), 
                array(
                'x' => $x,
                'y' => $y,
                'player_id' => $active_player_id,
                'die_id' => $die_id_value
                ) );



            //
            //  REMOVE THE 2 DICE 
            //
            //      Update globals and database 
            //
            foreach ( $raw_possible_combinations as $raw_possible_combination )
            {
                foreach ( $raw_possible_combination as $removable_die_id )  // SHOULD BE ONLY TWO REMOVABLE DICE HERE
                {            
                    if ( $removable_die_id >= 10000 && $removable_die_id < 20000 )  // Black die
                    {
                        self::incGameStateValue( "total_black_dice", -1 );
                    }
                    else 
                    {
                        self::incGameStateValue( "total_green_dice", -1 );
                    }

                    $sql = "UPDATE board SET board_player = NULL, die_id = NULL
                            WHERE die_id = $removable_die_id";  
    
                    self::DbQuery( $sql );


                    //
                    // Notify about removed $removable_die_id 
                    //
                    if ( $active_player_color == "000000" )     // Black player
                    {
                        if ( $removable_die_id >= 10000 && $removable_die_id < 20000 )  // Black die
                        {
                            self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                                array(
                            'player_id' => $active_player_id,
                            'die_id' => $removable_die_id
                            ) );
                        }
                        else 
                        {
                            self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                                array(
                            'player_id' => $inactive_player_id,
                            'die_id' => $removable_die_id
                            ) );
                        }
                    }
                    else                                        // Green player
                    {
                        if ( $removable_die_id >= 10000 && $removable_die_id < 20000 )  // Black die
                        {
                            self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                                array(
                            'player_id' => $inactive_player_id,
                            'die_id' => $removable_die_id
                            ) );
                        }
                        else 
                        {
                            self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                            array(
                            'player_id' => $active_player_id,
                            'die_id' => $removable_die_id
                            ) );
                        }
                    }
                }
            }            


            self::updatePlayerPanelAndHistory ( $x, $y );


            //Update players info
	        self::reloadPlayersBasicInfos();


            //
            // Go to the next state
            //
            $this->gamestate->nextState( 'selectDeterminingSquare' );
        }
        else                                                                                        // 3 TO 11 POSSIBLE NEIGHBOR COMBINATIONS ... selectSquare () 
        {
            //
            // UNSELECT PREVIOUS SELECTED SQUARE IN DATABASE
            //
            // AND SELECT THIS SQUARE IN DATABASE
            //
            $sql = "UPDATE board SET is_square_selected = 0 WHERE is_square_selected = 1";  
            self::DbQuery( $sql );

            $sql = "UPDATE board SET is_square_selected = 1 WHERE ( board_x, board_y) = ($x, $y)";  // CLICKED SQUARE
            self::DbQuery( $sql );

            /*
            self::notifyAllPlayers( "backendMessage", clienttranslate( 'SET is_square_selectd: ${var_1}, ${var_2}' ), 
            array(
                "var_1" => $x,
                "var_2" => $y
                ) );
            */

            //
            // Notify about selected square 
            //
            self::notifyAllPlayers( "squareSelected", clienttranslate( '' ), 
                array(
                'x' => $x,
                'y' => $y
                ) );


            //Update players info
	        self::reloadPlayersBasicInfos();


            //
            // Go to the next state
            //
            $this->gamestate->nextState( 'selectNonDeterminingSquare' );
        }
    }
    else
        throw new feException( "You have not selected a selectable square." );
}





//
//                  ##############################################################################################
//  SELECT DIE      ##############################################################################################
//                  ##############################################################################################
//
//  If no dice are selected
//
//      If there's no way of combining the clicked die with another die adjacent to the selected square
//          Place a 1 in the selected square, completing your turn.
//
//      If there's only one way of combining the clicked die with another die adjacent to the selected square
//          Place a die showing the combined sum, completing your turn.  
//
//      If there's more than one way of combining the clicked die with another die adjacent to the selected square
//          Select the die and continue.
//
//  If one die is already selected 
//
//      If you click a die which precludes the selection of additional selectable dice, because the sum of the 
//      clicked die, plus the selected die, plus any additional selectable dice would exceed 6 
//          Place a die showing the combined sum, completing your turn.  
//          
//      If you click a die which does not preclude the selection of additional selectable dice
//          Select the clicked die and SHOW THE FINISH BUTTON.
//
//      If you press the FINISH button
//          Place a die showing the combined sum of the selected dice, completing your turn.  
//          
//
//  If two or three dice are already selected 
//
//      If you click a die which precludes the selection of additional selectable dice, because the sum of the 
//      clicked die, plus the selected die, plus any additional selectable dice would exceed 6 
//          Place a die showing the combined sum, completing your turn.  
//      
//      If you click a die which does not preclude the selection of additional selectable dice
//          Select the clicked die and CONTINUE TO SHOW THE FINISH BUTTON.
//
//      If you press the FINISH button
//          Place a die showing the combined sum of the selected dice, completing your turn.  
//      
//
//
function selectDie ( $x, $y )                                                                                                                                       // selectDie
{
    self::checkAction( 'selectDie' );  

    $active_player_id = self::getActivePlayerId ( ); 
    $inactive_player_id = self::getOtherPlayerId ( $active_player_id ); 

    $active_player_color = self::getPlayerColor ( $active_player_id );

        
    $selectable_dice_IDs = array ( );

        
    $board = self::getBoard();

    $is_selectable_die = false;

    //
    //
    //  MAKE SURE THE CLICKED DIE IS A SELECTABLE DIE ######################################################################################################
    //
    //
    $selectable_dice_coords = self::getSelectableDiceCoords ( );

    //Get the board size
    $N = self::getGameStateValue ("board_size");

    for( $x_value=0; $x_value<5; $x_value++ )
    {
        for( $y_value=0; $y_value<$N; $y_value++ )
        {
            if (    isset ( $selectable_dice_coords [ $x_value ] [ $y_value ] )    )
            {
                /*
                self::notifyAllPlayers( "backendMessage", clienttranslate( 'Yes, isset $selectable_dice_coords [ ${var_1} ] [ ${var_2} ]' ), 
                array(
                    "var_1" => $x_value,
                    "var_2" => $y_value
                    ) );
                */

                if ( $x_value == $x && $y_value == $y )
                {
                    $is_selectable_die = true;

                    break 2;
                }
            }
            /*
            else 
            {
                self::notifyAllPlayers( "backendMessage", clienttranslate( 'No, not isset $selectable_dice_coords [ ${var_1} ] [ ${var_2} ]' ), 
                array(
                    "var_1" => $x_value,
                    "var_2" => $y_value
                    ) );
            }
            */
        }
    }

    //
    //
    //  IF THE CLICKED DIE IS A SELECTABLE DIE, PROCEED ##########################################################################################################
    //
    //
    if ( $is_selectable_die ) 
    {
        //                                                                                                                                                    ####################  
        //
        // Find out the die_id of the clicked die 
        //
        //
        $clicked_die_id = self::getDieID ($x, $y );

        //
        //
        // Find the coordinates of the selected square 
        //
        //           
        $selected_square_coords = self::getSelectedSquareCoords ( );

        $selected_square_x = $selected_square_coords [ 0 ];
        $selected_square_y = $selected_square_coords [ 1 ];
            

        //
        // Find out how many selected dice there are on the board 
        //
        $number_of_selected_dice = count (    self::getSelectedDiceIDs ( )    );

        if ( $number_of_selected_dice == 0 ) //                                                                                                  0 SELECTED DICE  ####################  
        {   
            //
            //
            // Find out how many raw combinations there are with clicked die
            //
            // If only one, complete the move 
            //
            $combs_with_clicked_die = 0; 

            $raw_possible_combinations = self::getRawPossibleCombinations ( $selected_square_x, $selected_square_y );

            foreach ( $raw_possible_combinations as $raw_possible_combination )
            {
                foreach ( $raw_possible_combination as $removable_die_id )
                {
                    if ( $removable_die_id == $clicked_die_id )

                    ++$combs_with_clicked_die;
                }
            }

            /*
            self::notifyAllPlayers( "backendMessage", clienttranslate( '# possible combs: ${var_1}' ), 
            array(
                "var_1" => $combs_with_clicked_die,
                ) );
            */

            //
            //  If there's only one possible combination that includes the clicked die, make that move.
            //
            //  Such a combination will necessarily be a combination of 2 dice.  See explanation above.
            //
            $includes_clicked_die = false;

            $removable_die_IDs = array ( );

            if ( $combs_with_clicked_die == 1 )  //                                                                         1 POSSIBLE COMBINATION WITH THE CLICKED DIE.  MAKE THE MOVE.     
            {
                //
                // Find the die_id of each of the 2 dice 
                //
                foreach ( $raw_possible_combinations as $raw_possible_combination )
                {
                    $removable_die_IDs = array ( );
                    //reset ( $removable_die_IDs );

                    foreach ( $raw_possible_combination as $removable_die_id )
                    {
                        $removable_die_IDs [ ] = $removable_die_id;

                        if ( $removable_die_id == $clicked_die_id )
                        {
                            $includes_clicked_die = true;
                        }
                    }

                    if ( $includes_clicked_die )
                    {
                        break;
                    }
                }

                /*
                foreach ( $removable_die_IDs as $removable_die_id )
                {
                    self::notifyAllPlayers( "backendMessage", clienttranslate( '$removable_die_id: ${var_1}' ), 
                    array(
                        "var_1" => $removable_die_id,
                        ) );
                }
                */

 
                //
                //  PLACE DIE ... selectDie ()
                //
                //      Update globals and database 
                //
                $pip_value_0 = self::getPipValue ( $removable_die_IDs [ 0 ] );
                $pip_value_1 = self::getPipValue ( $removable_die_IDs [ 1 ] );

                $pip_sum = $pip_value_0 + $pip_value_1;

                /*
                self::notifyAllPlayers( "backendMessage", clienttranslate( '$pip_sum: ${var_1}' ), 
                array(
                    "var_1" => $pip_sum
                    ) );
                */

                if (    $active_player_color == "000000"    )
                {
                    $die_id_value = self::getGameStateValue( "black_die_base_id" );
                    $die_id_value += $pip_sum * 1000;                                              // Put pip sum in die_id_value

                    self::incGameStateValue( "black_die_base_id", 1 );
    
                    self::incGameStateValue( "total_black_dice", 1 );
                }
                else 
                {
                    $die_id_value = self::getGameStateValue( "green_die_base_id" );
                    $die_id_value += $pip_sum * 1000;                                              // Put pip sum in die_id_value

                    self::incGameStateValue( "green_die_base_id", 1 );

                    self::incGameStateValue( "total_green_dice", 1 );
                }

                $sql = "UPDATE board SET board_player = $active_player_id, die_id = $die_id_value
                        WHERE ( board_x, board_y) = ($selected_square_x, $selected_square_y)";  
    
                self::DbQuery( $sql );


                //
                // Notify about placed die 
                //
                self::notifyAllPlayers( "diePlaced", clienttranslate( '' ), 
                    array(
                    'x' => $selected_square_x,
                    'y' => $selected_square_y,
                    'player_id' => $active_player_id,
                    'die_id' => $die_id_value
                    ) );



                //
                //  REMOVE THE 2 DICE 
                //
                //      Update globals and database 
                //
                foreach ( $removable_die_IDs as $removable_die_id )
                {
                    if ( $removable_die_id >= 10000 && $removable_die_id < 20000 )  // Black die
                    {
                        self::incGameStateValue( "total_black_dice", -1 );
                    }
                    else 
                    {
                        self::incGameStateValue( "total_green_dice", -1 );
                    }

                    $sql = "UPDATE board SET board_player = NULL, die_id = NULL
                            WHERE die_id = $removable_die_id";  
    
                    self::DbQuery( $sql );


                    //
                    // Notify about removed $removable_die_id 
                    //
                    if ( $active_player_color == "000000" )     // Black player
                    {
                        if ( $removable_die_id >= 10000 && $removable_die_id < 20000 )  // Black die
                        {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                            array(
                        'player_id' => $active_player_id,
                        'die_id' => $removable_die_id
                        ) );
                    }
                    else 
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                            array(
                        'player_id' => $inactive_player_id,
                        'die_id' => $removable_die_id
                        ) );
                    }
                    }
                    else                                        // Green player
                    {
                        if ( $removable_die_id >= 10000 && $removable_die_id < 20000 )  // Black die
                        {
                            self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                                array(
                            'player_id' => $inactive_player_id,
                            'die_id' => $removable_die_id
                            ) );
                        }
                        else 
                        {
                            self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                            array(
                            'player_id' => $active_player_id,
                            'die_id' => $removable_die_id
                            ) );
                        }
                    }
                }            


                self::updatePlayerPanelAndHistory ( $selected_square_x, $selected_square_y );


                //Update players info
	            self::reloadPlayersBasicInfos();

                //
                // Go to the next state
                //
                $this->gamestate->nextState( 'selectDeterminingDie' );
            }
            else  //                                                                                                        MORE THAN ONE POSSIBLE COMBINATION WITH THE CLICKED DIE.  
            {
                //
                // SELECT THE CLICKED DIE 
                //
                $sql = "UPDATE board SET is_die_selected = 1 WHERE ( board_x, board_y) = ($x, $y)";  // Select clicked die in database
                self::DbQuery( $sql );

                //
                // Notify about selected die 
                //
                self::notifyAllPlayers( "dieSelected", clienttranslate( '' ), 
                    array(
                    'x' => $x,
                    'y' => $y
                    ) );


                //Update players info
	            self::reloadPlayersBasicInfos();

                //
                // Go to the next state
                //
                $this->gamestate->nextState( 'selectFirstDie' );
            }           
        }
        else if ( $number_of_selected_dice == 1 )   //                                                                                             1 SELECTED DIE  ####################
        {                                           //                                                                                                             ####################
            // If clicked die precludes the selection of additional adjacent dice, because the sum of the 
            // clicked die, plus the selected die, plus any additional adjacent dice would exceed 6 
            // Place a die showing the combined sum, completing your turn.  


            //
            //
            // Find out how many raw combinations there are with clicked die AND the selected die
            //
            // If only one, complete the move 
            //
            $selected_dice_IDs = self::getSelectedDiceIDs ( );
            $selected_die_id = $selected_dice_IDs [ 0 ]; // should be only 1

            $combs_with_clicked_die = 0; 
            $combs_with_selected_die = 0; 
            $combs_with_clicked_die_and_selected_die = 0; 

            $raw_possible_combinations = self::getRawPossibleCombinations ( $selected_square_x, $selected_square_y );

            foreach ( $raw_possible_combinations as $raw_possible_combination )
            {
                $combs_with_clicked_die = 0; 
                $combs_with_selected_die = 0; 

                foreach ( $raw_possible_combination as $removable_die_id )
                {
                    if ( $removable_die_id == $clicked_die_id )
                    {
                        $combs_with_clicked_die = 1;
                    }

                    if ( $removable_die_id == $selected_die_id )
                    {
                        $combs_with_selected_die = 1;
                    }
                }

                if ( $combs_with_clicked_die == 1 && $combs_with_selected_die == 1 )
                {
                    ++$combs_with_clicked_die_and_selected_die;
                }
            }

            /*
            self::notifyAllPlayers( "backendMessage", clienttranslate( '$combs_with_clicked_die_and_selected_die: ${var_1}' ), 
            array(
                "var_1" => $combs_with_clicked_die_and_selected_die
                ) );
            */

            if ( $combs_with_clicked_die_and_selected_die == 1 )    //                                                              COMBINATIONS WITH CLICKED DIE AND SELECTED DIE = 1
            {
                //
                //  Place die showing combined pip sum, completing turn
                //
                $clicked_die_pip_value = self::getPipValue ( $clicked_die_id );

                $selected_die_pip_value = self::getPipValue ( $selected_die_id );

                $pip_sum = $clicked_die_pip_value + $selected_die_pip_value;


                if (    $active_player_color == "000000"    )
                {
                    $die_id_value = self::getGameStateValue( "black_die_base_id" );
                    $die_id_value += $pip_sum * 1000;                                              // Put pip sum in die_id_value

                    self::incGameStateValue( "black_die_base_id", 1 );
    
                    self::incGameStateValue( "total_black_dice", 1 );
                }
                else 
                {
                    $die_id_value = self::getGameStateValue( "green_die_base_id" );
                    $die_id_value += $pip_sum * 1000;                                              // Put pip sum in die_id_value

                    self::incGameStateValue( "green_die_base_id", 1 );

                    self::incGameStateValue( "total_green_dice", 1 );
                }

                $sql = "UPDATE board SET board_player = $active_player_id, die_id = $die_id_value
                        WHERE ( board_x, board_y) = ($selected_square_x, $selected_square_y)";  
    
                self::DbQuery( $sql );



                //
                // UNSELECT THE SELECTED DIE
                //
                $sql = "UPDATE board SET is_die_selected = NULL WHERE 1";  
                self::DbQuery( $sql );




                //
                // Notify about placed die 
                //
                self::notifyAllPlayers( "diePlaced", clienttranslate( '' ), 
                    array(
                    'x' => $selected_square_x,
                    'y' => $selected_square_y,
                    'player_id' => $active_player_id,
                    'die_id' => $die_id_value
                    ) );


                
                //
                //  REMOVE THE CLICKED DIE AND THE SELECTED DIE
                //
                //      Update globals and database 
                //

                //
                // REMOVE THE CLICKED DIE 
                //
                if ( $clicked_die_id >= 10000 && $clicked_die_id < 20000 )  // Black die
                {
                    self::incGameStateValue( "total_black_dice", -1 );
                }
                else 
                {
                    self::incGameStateValue( "total_green_dice", -1 );
                }

                $sql = "UPDATE board SET board_player = NULL, die_id = NULL
                            WHERE die_id = $clicked_die_id";  
    
                self::DbQuery( $sql );


                //
                // REMOVE THE SELECTED DIE 
                //
                if ( $selected_die_id >= 10000 && $selected_die_id < 20000 )  // Black die
                {
                    self::incGameStateValue( "total_black_dice", -1 );
                }
                else 
                {
                    self::incGameStateValue( "total_green_dice", -1 );
                }

                $sql = "UPDATE board SET board_player = NULL, die_id = NULL
                            WHERE die_id = $selected_die_id";  
    
                self::DbQuery( $sql );


                //
                // NOTIFY ABOUT REMOVED $clicked_die_id 
                //
                if ( $active_player_color == "000000" )     // Black player
                {
                    if ( $clicked_die_id >= 10000 && $clicked_die_id < 20000 )  // Black die
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                        array(
                        'player_id' => $active_player_id,
                        'die_id' => $clicked_die_id
                        ) );
                    }
                    else 
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                            array(
                        'player_id' => $inactive_player_id,
                        'die_id' => $clicked_die_id
                        ) );
                    }
                }
                else                                        // Green player
                {
                    if ( $clicked_die_id >= 10000 && $clicked_die_id < 20000 )  // Black die
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                            array(
                        'player_id' => $inactive_player_id,
                        'die_id' => $clicked_die_id
                        ) );
                    }
                    else 
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                        array(
                            'player_id' => $active_player_id,
                            'die_id' => $clicked_die_id
                            ) );
                    }
                }


                //
                // NOTIFY ABOUT REMOVED $selected_die_id 
                //
                if ( $active_player_color == "000000" )     // Black player
                {
                    if ( $selected_die_id >= 10000 && $selected_die_id < 20000 )  // Black die
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                        array(
                        'player_id' => $active_player_id,
                        'die_id' => $selected_die_id
                        ) );
                    }
                    else 
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                            array(
                        'player_id' => $inactive_player_id,
                        'die_id' => $selected_die_id
                        ) );
                    }
                }
                else                                        // Green player
                {
                    if ( $selected_die_id >= 10000 && $selected_die_id < 20000 )  // Black die
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                            array(
                        'player_id' => $inactive_player_id,
                        'die_id' => $selected_die_id
                        ) );
                    }
                    else 
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                        array(
                            'player_id' => $active_player_id,
                            'die_id' => $selected_die_id
                            ) );
                    }
                }


                self::updatePlayerPanelAndHistory ( $selected_square_x, $selected_square_y );


                //Update players info
	            self::reloadPlayersBasicInfos();

                //
                // Go to the next state
                //
                $this->gamestate->nextState( 'selectDeterminingDie' );
            }
            else //                                                                                                   COMBINATIONS WITH CLICKED DIE AND SELECTED DIE > 1
            {
                //
                // SELECT THE CLICKED DIE ...        AND PUT UP THE FINAL BUTTON          ################################
                // ######################                #######################          ################################
                //
                //

                $sql = "UPDATE board SET is_die_selected = 1 WHERE ( board_x, board_y) = ($x, $y)";  // Select clicked die in database
                self::DbQuery( $sql );

                //
                // Notify about selected die 
                //
                self::notifyAllPlayers( "dieSelected", clienttranslate( '' ), 
                    array(
                    'x' => $x,
                    'y' => $y
                    ) );


                //Update players info
	            self::reloadPlayersBasicInfos();

                //
                // Go to the next state
                //
                $this->gamestate->nextState( 'selectPossibleFinalDie' );
            }
        }
        else if ( $number_of_selected_dice == 2 )   //                                                                                             2 SELECTED DICE  ####################
        {                                           //                                                                                                              ####################
            // If clicked die precludes the selection of additional adjacent dice, because the sum of the 
            // clicked die, plus the 2 selected dice, plus any other adjacent die would exceed 6 
            // Place a die showing the combined sum, completing your turn.  


            //
            //
            // Find out how many raw combinations there are with clicked die AND the 2 selected dice
            //
            // If only one, complete the move 
            //
            $selected_dice_IDs = self::getSelectedDiceIDs ( );

            $selected_die_id_0 = $selected_dice_IDs [ 0 ]; // should be 2 selected dice 
            $selected_die_id_1 = $selected_dice_IDs [ 1 ]; 

            $combs_with_clicked_die = 0; 
            $combs_with_selected_die_0 = 0; 
            $combs_with_selected_die_1 = 0; 

            $combs_with_clicked_die_and_both_selected_dice = 0; 

            $raw_possible_combinations = self::getRawPossibleCombinations ( $selected_square_x, $selected_square_y );

            foreach ( $raw_possible_combinations as $raw_possible_combination )
            {
                $combs_with_clicked_die = 0; 
                $combs_with_selected_die_0 = 0; 
                $combs_with_selected_die_1 = 0; 


                foreach ( $raw_possible_combination as $removable_die_id )
                {
                    if ( $removable_die_id == $clicked_die_id )
                    {
                        $combs_with_clicked_die = 1;
                    }

                    if ( $removable_die_id == $selected_die_id_0 )
                    {
                        $combs_with_selected_die_0 = 1;
                    }

                    if ( $removable_die_id == $selected_die_id_1 )
                    {
                        $combs_with_selected_die_1 = 1;
                    }
                }

                if ( $combs_with_clicked_die == 1 && $combs_with_selected_die_0 == 1 && $combs_with_selected_die_1 == 1 )
                {
                    ++$combs_with_clicked_die_and_both_selected_dice;
                }
            }



            if ( $combs_with_clicked_die_and_both_selected_dice == 1 )    //                                               COMBINATIONS WITH CLICKED DIE AND BOTH SELECTED DICE = 1
            {
                //
                //
                //  PLACE DIE SHOWING SUM OF CLICKED DIE AND BOTH SELECTED DICE
                //
                //
                $clicked_die_pip_value = self::getPipValue ( $clicked_die_id );

                $selected_die_0_pip_value = self::getPipValue ( $selected_die_id_0 );

                $selected_die_1_pip_value = self::getPipValue ( $selected_die_id_1 );

                $pip_sum = $clicked_die_pip_value + $selected_die_0_pip_value + $selected_die_1_pip_value;


                if (    $active_player_color == "000000"    )
                {
                    $die_id_value = self::getGameStateValue( "black_die_base_id" );
                    $die_id_value += $pip_sum * 1000;                                              // Put pip sum in die_id_value

                    self::incGameStateValue( "black_die_base_id", 1 );
    
                    self::incGameStateValue( "total_black_dice", 1 );
                }
                else 
                {
                    $die_id_value = self::getGameStateValue( "green_die_base_id" );
                    $die_id_value += $pip_sum * 1000;                                              // Put pip sum in die_id_value

                    self::incGameStateValue( "green_die_base_id", 1 );

                    self::incGameStateValue( "total_green_dice", 1 );
                }

                $sql = "UPDATE board SET board_player = $active_player_id, die_id = $die_id_value
                        WHERE ( board_x, board_y) = ($selected_square_x, $selected_square_y)";  
    
                self::DbQuery( $sql );


                //
                // Notify about placed die 
                //
                self::notifyAllPlayers( "diePlaced", clienttranslate( '' ), 
                    array(
                    'x' => $selected_square_x,
                    'y' => $selected_square_y,
                    'player_id' => $active_player_id,
                    'die_id' => $die_id_value
                    ) );


                //
                // UNSELECT BOTH OF THE SELECTED DICE
                //
                $sql = "UPDATE board SET is_die_selected = NULL WHERE 1";  
                self::DbQuery( $sql );


                //
                //  REMOVE THE CLICKED DIE AND BOTH OF THE SELECTED DICE
                //
                //      Update globals and database 
                //

                //
                // REMOVE CLICKED DIE 
                //
                if ( $clicked_die_id >= 10000 && $clicked_die_id < 20000 )  // Black die
                {
                    self::incGameStateValue( "total_black_dice", -1 );
                }
                else 
                {
                    self::incGameStateValue( "total_green_dice", -1 );
                }

                $sql = "UPDATE board SET board_player = NULL, die_id = NULL
                            WHERE die_id = $clicked_die_id";  
    
                self::DbQuery( $sql );



                //
                // REMOVE BOTH OF THE SELECTED DICE 
                //

                //
                // REMOVE SELECTED DIE 0    
                //
                if ( $selected_die_id_0 >= 10000 && $selected_die_id_0 < 20000 )  // Black die
                {
                    self::incGameStateValue( "total_black_dice", -1 );
                }
                else 
                {
                        self::incGameStateValue( "total_green_dice", -1 );
                }

                $sql = "UPDATE board SET board_player = NULL, die_id = NULL
                            WHERE die_id = $selected_die_id_0";  
    
                self::DbQuery( $sql );


                //
                // REMOVE SELECTED DIE 1    
                //
                if ( $selected_die_id_1 >= 10000 && $selected_die_id_1 < 20000 )  // Black die
                {
                    self::incGameStateValue( "total_black_dice", -1 );
                }
                else 
                {
                    self::incGameStateValue( "total_green_dice", -1 );
                }

                $sql = "UPDATE board SET board_player = NULL, die_id = NULL
                            WHERE die_id = $selected_die_id_1";  
    
                self::DbQuery( $sql );



                //
                // NOTIFY ABOUT REMOVED $clicked_die_id 
                //
                if ( $active_player_color == "000000" )     // Black player
                {
                    if ( $clicked_die_id >= 10000 && $clicked_die_id < 20000 )  // Black die
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                        array(
                        'player_id' => $active_player_id,
                        'die_id' => $clicked_die_id
                        ) );
                    }
                    else 
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                            array(
                        'player_id' => $inactive_player_id,
                        'die_id' => $clicked_die_id
                        ) );
                    }
                }
                else                                        // Green player
                {
                    if ( $clicked_die_id >= 10000 && $clicked_die_id < 20000 )  // Black die
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                            array(
                        'player_id' => $inactive_player_id,
                        'die_id' => $clicked_die_id
                        ) );
                    }
                    else 
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                        array(
                            'player_id' => $active_player_id,
                            'die_id' => $clicked_die_id
                            ) );
                    }
                }


                //
                // NOTIFY ABOUT BOTH REMOVED $selected_die_id_0 AND $selected_die_id_1
                //

                //
                // $selected_die_id_0
                //
                if ( $active_player_color == "000000" )     // Black player
                {
                    if ( $selected_die_id_0 >= 10000 && $selected_die_id_0 < 20000 )  // Black die
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                        array(
                        'player_id' => $active_player_id,
                        'die_id' => $selected_die_id_0
                        ) );
                    }
                    else 
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                            array(
                        'player_id' => $inactive_player_id,
                        'die_id' => $selected_die_id_0
                        ) );
                    }
                }
                else                                        // Green player
                {
                    if ( $selected_die_id_0 >= 10000 && $selected_die_id_0 < 20000 )  // Black die
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                            array(
                        'player_id' => $inactive_player_id,
                        'die_id' => $selected_die_id_0
                        ) );
                    }
                    else 
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                        array(
                            'player_id' => $active_player_id,
                            'die_id' => $selected_die_id_0
                            ) );
                    }
                }



                //
                // $selected_die_id_1
                //
                if ( $active_player_color == "000000" )     // Black player
                {
                    if ( $selected_die_id_1 >= 10000 && $selected_die_id_1 < 20000 )  // Black die
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                        array(
                        'player_id' => $active_player_id,
                        'die_id' => $selected_die_id_1
                        ) );
                    }
                    else 
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                            array(
                        'player_id' => $inactive_player_id,
                        'die_id' => $selected_die_id_1
                        ) );
                    }
                }
                else                                        // Green player
                {
                    if ( $selected_die_id_1 >= 10000 && $selected_die_id_1 < 20000 )  // Black die
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                            array(
                        'player_id' => $inactive_player_id,
                        'die_id' => $selected_die_id_1
                        ) );
                    }
                    else 
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                        array(
                            'player_id' => $active_player_id,
                            'die_id' => $selected_die_id_1
                            ) );
                    }
                }


                self::updatePlayerPanelAndHistory ( $selected_square_x, $selected_square_y );


                //Update players info
	            self::reloadPlayersBasicInfos();

                //
                // Go to the next state
                //
                $this->gamestate->nextState( 'selectDeterminingDie' );
            }
            else //                                                                                                   COMBINATIONS WITH CLICKED DIE AND BOTH SELECTED DICE > 1
            {
                //
                // SELECT THE CLICKED DIE ...        AND PUT UP THE FINAL BUTTON          ################################
                // ######################                #######################          ################################
                //
                //

                $sql = "UPDATE board SET is_die_selected = 1 WHERE ( board_x, board_y) = ($x, $y)";  // Select clicked die in database
                self::DbQuery( $sql );

                //
                // Notify about selected die 
                //
                self::notifyAllPlayers( "dieSelected", clienttranslate( '' ), 
                    array(
                    'x' => $x,
                    'y' => $y
                    ) );


                //Update players info
	            self::reloadPlayersBasicInfos();

                //
                // Go to the next state
                //
                $this->gamestate->nextState( 'selectPossibleFinalDie' );
            }
        }
        else                     //                                                                                                              3 SELECTED DICE  ####################
        {                        //                                                                                                                               ####################
            //
            //
            // WHEN THERE'S 1 CLICKED DIE AND 3 SELECTED DICE, MAKE THE MOVE, COMPLETING THE TURN
            //
            //
            //
            //
            //  PLACE DIE SHOWING SUM OF CLICKED DIE AND ALL 3 SELECTED DICE
            //
            //
            $clicked_die_pip_value = self::getPipValue ( $clicked_die_id );


            $selected_dice_IDs = self::getSelectedDiceIDs ( );

            $selected_dice_IDs_pip_sum = 0;

            foreach ( $selected_dice_IDs as $selected_die_id )
            {
                $selected_dice_IDs_pip_sum += self::getPipValue ( $selected_die_id );
            }


            $total_pip_sum = $clicked_die_pip_value + $selected_dice_IDs_pip_sum;


            if (    $active_player_color == "000000"    )
            {
                $die_id_value = self::getGameStateValue( "black_die_base_id" );
                $die_id_value += $total_pip_sum * 1000;                                              // Put pip sum in die_id_value

                self::incGameStateValue( "black_die_base_id", 1 );
    
                self::incGameStateValue( "total_black_dice", 1 );
            }
            else 
            {
                $die_id_value = self::getGameStateValue( "green_die_base_id" );
                $die_id_value += $total_pip_sum * 1000;                                              // Put pip sum in die_id_value

                self::incGameStateValue( "green_die_base_id", 1 );

                self::incGameStateValue( "total_green_dice", 1 );
            }

            $sql = "UPDATE board SET board_player = $active_player_id, die_id = $die_id_value
                    WHERE ( board_x, board_y) = ($selected_square_x, $selected_square_y)";  
    
            self::DbQuery( $sql );


            //
            // Notify about placed die 
            //
            self::notifyAllPlayers( "diePlaced", clienttranslate( '' ), 
                array(
                'x' => $selected_square_x,
                'y' => $selected_square_y,
                'player_id' => $active_player_id,
                'die_id' => $die_id_value
                ) );



            //
            // UNSELECT ALL 3 OF THE SELECTED DICE
            //
            $sql = "UPDATE board SET is_die_selected = NULL WHERE 1";  
            self::DbQuery( $sql );



            //
            //  REMOVE THE CLICKED DIE AND ALL 3 OF THE SELECTED DICE
            //
            //      Update globals and database 
            //

            //
            // REMOVE CLICKED DIE 
            //
            if ( $clicked_die_id >= 10000 && $clicked_die_id < 20000 )  // Black die
            {
                self::incGameStateValue( "total_black_dice", -1 );
            }
            else 
            {
                self::incGameStateValue( "total_green_dice", -1 );
            }

            $sql = "UPDATE board SET board_player = NULL, die_id = NULL
                        WHERE die_id = $clicked_die_id";  
    
            self::DbQuery( $sql );



            //
            //  REMOVE ALL 3 OF THE SELECTED DICE
            //
            foreach ( $selected_dice_IDs as $selected_die_id )
            {
                //
                // REMOVE THE SELECTED DIE 
                //
                if ( $selected_die_id >= 10000 && $selected_die_id < 20000 )  // Black die
                {
                    self::incGameStateValue( "total_black_dice", -1 );
                }
                else 
                {
                    self::incGameStateValue( "total_green_dice", -1 );
                }

                $sql = "UPDATE board SET board_player = NULL, die_id = NULL
                            WHERE die_id = $selected_die_id";  
            
                self::DbQuery( $sql );
            }



            //
            // NOTIFY ABOUT REMOVED $clicked_die_id 
            //
            if ( $active_player_color == "000000" )     // Black player
            {
                if ( $clicked_die_id >= 10000 && $clicked_die_id < 20000 )  // Black die
                {
                    self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                    array(
                    'player_id' => $active_player_id,
                    'die_id' => $clicked_die_id
                    ) );
                }
                else 
                {
                    self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                        array(
                    'player_id' => $inactive_player_id,
                    'die_id' => $clicked_die_id
                    ) );
                }
            }
            else                                        // Green player
            {
                if ( $clicked_die_id >= 10000 && $clicked_die_id < 20000 )  // Black die
                {
                    self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                        array(
                    'player_id' => $inactive_player_id,
                    'die_id' => $clicked_die_id
                    ) );
                }
                else 
                {
                    self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                    array(
                        'player_id' => $active_player_id,
                        'die_id' => $clicked_die_id
                        ) );
                }
            }



            //
            //  NOTIFY ABOUT ALL 3 REMOVED SELECTED DICE
            //
            foreach ( $selected_dice_IDs as $selected_die_id )
            {
                //
                // NOTIFY ABOUT REMOVED $selected_die_id 
                //
                if ( $active_player_color == "000000" )     // Black player
                {
                    if ( $selected_die_id >= 10000 && $selected_die_id < 20000 )  // Black die
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                        array(
                        'player_id' => $active_player_id,
                        'die_id' => $selected_die_id
                        ) );
                    }
                    else 
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                            array(
                        'player_id' => $inactive_player_id,
                        'die_id' => $selected_die_id
                        ) );
                    }
                }
                else                                        // Green player
                {                
                    if ( $selected_die_id >= 10000 && $selected_die_id < 20000 )  // Black die
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                            array(
                        'player_id' => $inactive_player_id,
                        'die_id' => $selected_die_id
                        ) );
                    }
                    else 
                    {
                        self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                        array(
                            'player_id' => $active_player_id,
                            'die_id' => $selected_die_id
                            ) );
                    }
                }
            }


            self::updatePlayerPanelAndHistory ( $selected_square_x, $selected_square_y );


            //Update players info
	        self::reloadPlayersBasicInfos();

	        //
            // Complete this turn
            //
	        $this->gamestate->nextState( 'finalizeThisCombination' );
        }
    }
    else
        throw new feException( "That is not a selectable die." );
}





//
//  Finalize the combination of selected dice
//
function finalizeThisCombination()
{   
    self::checkAction( 'finalize' );  

    $active_player_id = self::getActivePlayerId ( ); 
    $inactive_player_id = self::getOtherPlayerId ( $active_player_id ); 

    $active_player_color = self::getPlayerColor ( $active_player_id );

        
    //
    //
    // Find the coordinates of the selected square 
    //
    //           
    $selected_square_coords = self::getSelectedSquareCoords ( );

    $selected_square_x = $selected_square_coords [ 0 ];
    $selected_square_y = $selected_square_coords [ 1 ];
            

    //
    //  COMBINE THE SELECTED CHECKERS
    //      Place a new checker 
    //      Remove the selected checkers
    //
    $selected_dice_IDs = self::getSelectedDiceIDs ( );

    //
    // Get sum of pip values
    //
    $pip_sum = 0;

    foreach ( $selected_dice_IDs as $selected_die_id )
    {
        $pip_sum += self::getPipValue ( $selected_die_id );
    }


    if (    $active_player_color == "000000"    )
    {
        $die_id_value = self::getGameStateValue( "black_die_base_id" );
        $die_id_value += $pip_sum * 1000;                                              // Put pip sum in die_id_value

        self::incGameStateValue( "black_die_base_id", 1 );
    
        self::incGameStateValue( "total_black_dice", 1 );
    }
    else 
    {
        $die_id_value = self::getGameStateValue( "green_die_base_id" );
        $die_id_value += $pip_sum * 1000;                                              // Put pip sum in die_id_value

        self::incGameStateValue( "green_die_base_id", 1 );

        self::incGameStateValue( "total_green_dice", 1 );
    }

    $sql = "UPDATE board SET board_player = $active_player_id, die_id = $die_id_value
            WHERE ( board_x, board_y) = ($selected_square_x, $selected_square_y)";  
    
    self::DbQuery( $sql );


    //
    // Notify about placed die 
    //
    self::notifyAllPlayers( "diePlaced", clienttranslate( '' ), 
        array(
        'x' => $selected_square_x,
        'y' => $selected_square_y,
        'player_id' => $active_player_id,
        'die_id' => $die_id_value
        ) );



    //
    // UNSELECT ALL OF THE SELECTED DIE
    //
        $sql = "UPDATE board SET is_die_selected = NULL WHERE 1";  
        self::DbQuery( $sql );



    //
    //  REMOVE THE SELECTED DICE
    //
    foreach ( $selected_dice_IDs as $selected_die_id )
    {
        //
        // REMOVE THE SELECTED DIE 
        //
        if ( $selected_die_id >= 10000 && $selected_die_id < 20000 )  // Black die
        {
            self::incGameStateValue( "total_black_dice", -1 );
        }
        else 
        {
            self::incGameStateValue( "total_green_dice", -1 );
        }

        $sql = "UPDATE board SET board_player = NULL, die_id = NULL
                    WHERE die_id = $selected_die_id";  
    
        self::DbQuery( $sql );
    }



    //
    //  NOTIFY ABOUT REMOVED SELECTED DICE
    //
    foreach ( $selected_dice_IDs as $selected_die_id )
    {
        //
        // NOTIFY ABOUT REMOVED $selected_die_id 
        //
        if ( $active_player_color == "000000" )     // Black player
        {
            if ( $selected_die_id >= 10000 && $selected_die_id < 20000 )  // Black die
            {
                self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                array(
                'player_id' => $active_player_id,
                'die_id' => $selected_die_id
                ) );
            }
            else 
            {
                self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                    array(
                'player_id' => $inactive_player_id,
                'die_id' => $selected_die_id
                ) );
            }
        }
        else                                        // Green player
        {
            if ( $selected_die_id >= 10000 && $selected_die_id < 20000 )  // Black die
            {
                self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                    array(
                'player_id' => $inactive_player_id,
                'die_id' => $selected_die_id
                ) );
            }
            else 
            {
                self::notifyAllPlayers( "dieRemoved", clienttranslate( '' ), 
                array(
                    'player_id' => $active_player_id,
                    'die_id' => $selected_die_id
                    ) );
            }
        }
    }


    self::updatePlayerPanelAndHistory ( $selected_square_x, $selected_square_y );


    //Update players info
	self::reloadPlayersBasicInfos();

	//
    // Complete this turn
    //

	$this->gamestate->nextState( 'finalizeThisCombination' );
}








function updatePlayerPanelAndHistory ( $selected_square_x, $selected_square_y )
{
    //
    //
    // UPDATE PLAYER PANEL 
    //
    //      Display the players' number of placed dice
    //

    $active_player_id = self::getActivePlayerId(); 

    $inactive_player_id = self::getOtherPlayerId($active_player_id); 


    $active_player_color = self::getPlayerColor ( $active_player_id );

    $inactive_player_color = self::getPlayerColor ( $inactive_player_id );


    $total_black_dice = self::getGameStateValue ( "total_black_dice");
    $total_black_dice_str = "{$total_black_dice}";

    $total_green_dice = self::getGameStateValue ( "total_green_dice");
    $total_green_dice_str = "{$total_green_dice}";

    if (    $active_player_color == "000000"   ) // Black player is active
    {
        self::notifyAllPlayers("playerPanel",
            "",
            array(
                "placed_dice" => $total_black_dice_str,
                "player_id" => $active_player_id ));

        self::notifyAllPlayers("playerPanel",
            "",
            array(
                "placed_dice" => $total_green_dice_str,
                "player_id" => self::getOtherPlayerId($active_player_id) ));
    }
    else 
    {
        self::notifyAllPlayers("playerPanel",
            "",
            array(
                "placed_dice" => $total_green_dice_str,
                "player_id" => $active_player_id ));

       self::notifyAllPlayers("playerPanel",
            "",
            array(
                "placed_dice" => $total_black_dice_str,
                "player_id" => self::getOtherPlayerId($active_player_id) ));
    }

    //
    // UPDATE HISTORY PANEL
    //
    //      Show coordinates where player last placed a die
    //
    $x_display_coord = chr( ord( "A" ) + $selected_square_x );
    $y_display_coord = $selected_square_y + 1;

    self::notifyAllPlayers( "diePlacedHistory", clienttranslate( '${active_player_name} 
       ${x_display_coord}${y_display_coord}' ),     
       array(
            'active_player_name' => self::getActivePlayerName(),
            'x_display_coord' => $x_display_coord,
            'y_display_coord' => $y_display_coord
        ) );
}






function getSelectedSquareCoords ( )
{
    $selected_square = array ( );


    $sql = "SELECT board_x x, board_y y FROM board WHERE is_square_selected = 1";  // should be only one

    $result = self::DbQuery( $sql );

    $row = $result->fetch_assoc ( );


    $selected_square [ 0 ] = $row [ "x" ];
    $selected_square [ 1 ] = $row [ "y" ];


    return $selected_square;
}





//
// Raw possible combinations 
//
// Doesn't test whether neighboring dice are selected 
//
function getRawPossibleCombinations ( $x, $y )
{
    $raw_possible_combinations = array ( );

    $board_die_IDs = array ( );

    $die_IDs_neighbors = array ( );



    $board_die_IDs = self::getBoardDieIDs ( );

    $N=self::getGameStateValue("board_size");

    $directions = array (    array ( -1, 0 ), array ( 0, 1 ), array ( 1, 0 ), array ( 0, -1 )    );

    foreach( $directions as $direction )
    {
        $neighbor_x = $x + $direction [ 0 ];
        $neighbor_y = $y + $direction [ 1 ];

        if (    self::isSquareOnBoard ( $neighbor_x, $neighbor_y, $N )    )
        {
            $die_id_neighbor = $board_die_IDs [ $neighbor_x ] [ $neighbor_y ];

            if ( $die_id_neighbor )
            {
                $die_IDs_neighbors [ ] = $die_id_neighbor;
            }
        }
    }


    $number_of_neighbors = count ( $die_IDs_neighbors );

    if ( $number_of_neighbors == 2 )                                                                    // 2 NEIGHBORS
    {
        $pip_value_0 = self::getPipValue ( $die_IDs_neighbors [ 0 ] );
        $pip_value_1 = self::getPipValue ( $die_IDs_neighbors [ 1 ] );

        if ( $pip_value_0 + $pip_value_1 <= 6 )                                                         // 1 combinations of 2 dice
        {
            $raw_possible_combinations [ ] = array ( $die_IDs_neighbors [ 0 ], $die_IDs_neighbors [ 1 ] );
        }
    }
    else if ( $number_of_neighbors == 3 )                                                               // 3 NEIGHBORS
    {
        $pip_value_0 = self::getPipValue ( $die_IDs_neighbors [ 0 ] );
        $pip_value_1 = self::getPipValue ( $die_IDs_neighbors [ 1 ] );
        $pip_value_2 = self::getPipValue ( $die_IDs_neighbors [ 2 ] );

        if ( $pip_value_0 + $pip_value_1 <= 6 )                                                         // 3 combinations of 2 dice
        {
            $raw_possible_combinations [ ] = array ( $die_IDs_neighbors [ 0 ], $die_IDs_neighbors [ 1 ] );
        }

        if ( $pip_value_0 + $pip_value_2 <= 6 )
        {
            $raw_possible_combinations [ ] = array ( $die_IDs_neighbors [ 0 ], $die_IDs_neighbors [ 2 ] );
        }

        if ( $pip_value_1 + $pip_value_2 <= 6 )
        {
            $raw_possible_combinations [ ] = array ( $die_IDs_neighbors [ 1 ], $die_IDs_neighbors [ 2 ] );
        }

        if ( $pip_value_0 + $pip_value_1 + $pip_value_2 <= 6 )                                          // 1 combinations of 3 dice
        {
            $raw_possible_combinations [ ] = array ( $die_IDs_neighbors [ 0 ], $die_IDs_neighbors [ 1 ], $die_IDs_neighbors [ 2 ] );
        }
    }
    else if ( $number_of_neighbors == 4 )                                                               // 4 NEIGHBORS
    {
        $pip_value_0 = self::getPipValue ( $die_IDs_neighbors [ 0 ] );
        $pip_value_1 = self::getPipValue ( $die_IDs_neighbors [ 1 ] );
        $pip_value_2 = self::getPipValue ( $die_IDs_neighbors [ 2 ] );
        $pip_value_3 = self::getPipValue ( $die_IDs_neighbors [ 3 ] );

        if ( $pip_value_0 + $pip_value_1 <= 6 )                                                         // 6 combinations of 2 dice
        {
            $raw_possible_combinations [ ] = array ( $die_IDs_neighbors [ 0 ], $die_IDs_neighbors [ 1 ] );
        }

        if ( $pip_value_0 + $pip_value_2 <= 6 )
        {
            $raw_possible_combinations [ ] = array ( $die_IDs_neighbors [ 0 ], $die_IDs_neighbors [ 2 ] );
        }

        if ( $pip_value_0 + $pip_value_3 <= 6 )
        {
            $raw_possible_combinations [ ] = array ( $die_IDs_neighbors [ 0 ], $die_IDs_neighbors [ 3 ] );
        }

        if ( $pip_value_1 + $pip_value_2 <= 6 )
        {
            $raw_possible_combinations [ ] = array ( $die_IDs_neighbors [ 1 ], $die_IDs_neighbors [ 2 ] );
        }

        if ( $pip_value_1 + $pip_value_3 <= 6 )
        {
            $raw_possible_combinations [ ] = array ( $die_IDs_neighbors [ 1 ], $die_IDs_neighbors [ 3 ] );
        }

        if ( $pip_value_2 + $pip_value_3 <= 6 )
        {
            $raw_possible_combinations [ ] = array ( $die_IDs_neighbors [ 2 ], $die_IDs_neighbors [ 3 ] );
        }

        if ( $pip_value_0 + $pip_value_1 + $pip_value_2  <= 6 )                                              // 4 combinations of 3 dice
        {
            $raw_possible_combinations [ ] = array ( $die_IDs_neighbors [ 0 ], $die_IDs_neighbors [ 1 ], $die_IDs_neighbors [ 2 ] );
        }

        if ( $pip_value_0 + $pip_value_1 + $pip_value_3 <= 6 )  
        {
            $raw_possible_combinations [ ] = array ( $die_IDs_neighbors [ 0 ], $die_IDs_neighbors [ 1 ], $die_IDs_neighbors [ 3 ] );
        }

        if ( $pip_value_0 + $pip_value_2 + $pip_value_3 <= 6 )  
        {
            $raw_possible_combinations [ ] = array ( $die_IDs_neighbors [ 0 ], $die_IDs_neighbors [ 2 ], $die_IDs_neighbors [ 3 ] );
        }

        if ( $pip_value_1 + $pip_value_2 + $pip_value_3 <= 6 )  
        {
            $raw_possible_combinations [ ] = array ( $die_IDs_neighbors [ 1 ], $die_IDs_neighbors [ 2 ], $die_IDs_neighbors [ 3 ] );
        }

        if ( $pip_value_0 +$pip_value_1 + $pip_value_2 + $pip_value_3 <= 6 )                                 // 1 combinations of 4 dice
        {
            $raw_possible_combinations [ ] = array ( $die_IDs_neighbors [ 0 ], $die_IDs_neighbors [ 1 ], $die_IDs_neighbors [ 2 ], $die_IDs_neighbors [ 3 ] );
        }
    }

    return $raw_possible_combinations;
}





function getPipValue ( $die_id )
{
    return floor (    ( $die_id % 10000 ) / 1000    );
}



function isSquareOnBoard ( $x, $y, $N )
{
    if (    ( $x >= 0 && $x < 5 ) && ( $y >= 0 && $y < $N )    )
        return true;
}


function getBoardDieIDs()
{
    return self::getDoubleKeyCollectionFromDB( "SELECT board_x x, board_y y, die_id FROM board", true );
}



function unselectAll ( )
{
    self::checkAction( 'unselectAll' );  
    
    //$active_player_id = self::getActivePlayerId(); 
        
    //$board = self::getBoard();

    $sql = "UPDATE board SET is_square_selected = 0 WHERE 1";  

    self::DbQuery( $sql );


    $sql = "UPDATE board SET is_die_selected = 0 WHERE 1";  

    self::DbQuery( $sql );

    //
    // Notify
    //
    self::notifyAllPlayers( "allUnselected", clienttranslate( '' ), 
        array(
        ) );


    //Update players info
    self::reloadPlayersBasicInfos();

    //
    // Go to the next state
    //
    $this->gamestate->nextState( 'unselectAll' );
}



    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

function argSelectSquare()
{
    return array(
        'selectableSquares' => self::getSelectableSquareCoords ( )
    );
}


function argSelectDie()
{
    return array(
        'tripleSetOfArgs' => self::getTripleSetOfArgs (  ) // SELECTED SQUARE, SELECTED DICE, SELECTABLE DICE
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

    $active_player_color = self::getPlayerColor ( $active_player_id );
    $inactive_player_color = self::getPlayerColor ( $inactive_player_id );

    //Get the board size
    $N = self::getGameStateValue("board_size");
            

    if ( $N == 3 )
        $number_of_squares = 15;
    else    // $N = 5
        $number_of_squares = 25;


    //
    // See who has the most checkers in a filled board and end the game.
    //
    $total_black_dice = self::getGameStateValue ( "total_black_dice" );

    $total_green_dice = self::getGameStateValue ( "total_green_dice" );

    if ( $total_black_dice + $total_green_dice == $number_of_squares )
    {
        if ( $total_black_dice > $total_green_dice )
        {
            if ( $inactive_player_color == "000000" )
            {
                $sql = "UPDATE player
                        SET player_score = 1 WHERE player_id = $inactive_player_id";

                self::DbQuery( $sql );
            }
            else 
            {
                $sql = "UPDATE player
                        SET player_score = 1 WHERE player_id = $active_player_id";

                self::DbQuery( $sql );
            }
        }
        else // $total_black_dice < $total_green_dice
        {
            if ( $active_player_color == "000000" )
            {
                $sql = "UPDATE player
                        SET player_score = 1 WHERE player_id = $inactive_player_id";

                self::DbQuery( $sql );
            }
            else 
            {
                $sql = "UPDATE player
                        SET player_score = 1 WHERE player_id = $active_player_id";

                self::DbQuery( $sql );
            }
        }



        $newScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );

        self::notifyAllPlayers( "newScores", "", array(
            "scores" => $newScores
        ) );

        //Update players info
	    //self::reloadPlayersBasicInfos();

        //
        // Go to the next state
        //
        $this->gamestate->nextState( 'endGame' );
    }
    else 
    {
        //Update players info
	    self::reloadPlayersBasicInfos();

        //
        // Go to the next state
        //
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
