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
 * redstone.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in redstone_redstone.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
  require_once( APP_BASE_PATH."view/common/game.view.php" );
  
  class view_redstone_redstone extends game_view
  {
    function getGameName() {
        return "redstone";
    } 
    
  	function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        /*
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );
        */





        $this->page->begin_block( "redstone_redstone", "clickable_square" );




        $this->page->begin_block( "redstone_redstone", "placement_square" );




        
        //Get the size of the board regarding the choosen option
        $N=$this->game->getGameStateValue("board_size");
        
        //Say it to the .tpl to choose the right board image
        $this->tpl['BOARD_SIZE'] = $N;
        
        //Used to select the tokens/squares sizes according to the board size



        
        switch($N)
        {
            case 9:
                $clickable_sq_hor_offset = 37.7 + 1;    // Left margin minus half div diameter.  30 + 38.5 - 61.6 / 2 = 37.7
                $clickable_sq_ver_offset = 38.29;       // Top margin minus half div diameter.  30 + 41.4645 - 66.34 / 2 = 38.2945
                $clickable_sq_hor_scale = 77;       
                $clickable_sq_ver_scale = 82.929;   

                $plcmt_sq_hor_offset = 30;              // Left margin minus half div diameter.  30 + 38.5 - 77 / 2 = 30
                $plcmt_sq_ver_offset = 32.96;           // Top margin minus half div diameter.  30 + 41.4645 - 77 / 2 = 32.9645
                $plcmt_sq_hor_scale = 77; 
                $plcmt_sq_ver_scale = 82.929;
            break;

            case 11:
                $clickable_sq_hor_offset = 36;          // Left margin minus half div diameter.  30 + 30 - 48 / 2 = 36
                $clickable_sq_ver_offset = 36.462;      // Top margin minus half div diameter.  30 + 32.31 - 51.696 / 2 = 36.462
                $clickable_sq_hor_scale = 60; 
                $clickable_sq_ver_scale = 64.62;

                $plcmt_sq_hor_offset = 30;              // Left margin minus half div diameter.  30 + 30 - 60 / 2 = 30
                $plcmt_sq_ver_offset = 32.31;           // Top margin minus half div diameter.  30 + 32.31 - 60 / 2 = 32.31
                $plcmt_sq_hor_scale = 60; 
                $plcmt_sq_ver_scale = 64.62;
            break;
            
            case 13:
                $clickable_sq_hor_offset = 35.3 + 1;    // Left margin minus half div diameter.  30 + 26.5 - 42.4 / 2 = 35.3
                $clickable_sq_ver_offset = 35.71;       // Top margin minus half div diameter.  30 + 28.5405 - 45.66 / 2 = 35.7105
                $clickable_sq_hor_scale = 53; 
                $clickable_sq_ver_scale = 57.081;

                $plcmt_sq_hor_offset = 30;              // Left margin minus half div diameter.  30 + 26.5 - 53 / 2 = 30
                $plcmt_sq_ver_offset = 32.04;           // Top margin minus half div diameter.  30 + 28.5405 - 53 / 2 = 32.0405
                $plcmt_sq_hor_scale = 53; 
                $plcmt_sq_ver_scale = 57.081;
            break;
            
            case 15:
                $clickable_sq_hor_offset = 34.8;        // Left margin minus half div diameter.  30 + 24 - 38.4 / 2 = 34.8
                $clickable_sq_ver_offset = 35.1696;     // Top margin minus half div diameter.  30 + 25.848 - 41.3568 / 2 = 35.1696
                $clickable_sq_hor_scale = 48; 
                $clickable_sq_ver_scale = 51.696;

                $plcmt_sq_hor_offset = 30;              // Left margin minus half div diameter.  30 + 24 - 48 / 2 = 30
                $plcmt_sq_ver_offset = 31.848;          // Top margin minus half div diameter.  30 + 25.848 - 48 / 2 = 31.848
                $plcmt_sq_hor_scale = 48; 
                $plcmt_sq_ver_scale = 51.696;
            break;
            
            case 19:
                $clickable_sq_hor_offset = 34.4;        // Left margin minus half div diameter.  30 + 22 - 35.2 / 2 = 34.4
                $clickable_sq_ver_offset = 36.09;       // Top margin minus half div diameter.  30 + 23.694 - 37.91 / 2 = 36.094
                $clickable_sq_hor_scale = 44; 
                $clickable_sq_ver_scale = 47.388;

                $plcmt_sq_hor_offset = 30;              // Left margin minus half div diameter.  30 + 22 - 44 / 2 = 30
                $plcmt_sq_ver_offset = 31.69 + 1;       // Top margin minus half div diameter.  30 + 23.694 - 44 / 2 = 31.694
                $plcmt_sq_hor_scale = 44; 
                $plcmt_sq_ver_scale = 47.388;
            break;
		}
        
        for( $x=0; $x<$N; $x++ )
        {
            for( $y=0; $y<$N; $y++ )
            {
                switch($N)
                {
                    case 9:
                        $random_x = rand (-3, 3);
                        $random_y = rand (-4, 4);
                    break;
        
                    case 11:
                        $random_x = rand (-2, 2);
                        $random_y = rand (-3, 3);
                    break;
                        
                    case 13:
                        $random_x = rand (-2, 2);
                        $random_y = rand (-3, 3);
                    break;
                        
                    case 15:
                        $random_x = rand (-2, 2);
                        $random_y = rand (-3, 3);
                    break;
                        
                    case 19:
                        $random_x = rand (-2, 2);
                        $random_y = rand (-3, 3);
                    break;
                }


                $y_rightside_up = $N - 1 - $y;                     // Coords (0,0) in lower left
                        

                $this->page->insert_block( "clickable_square", array(
                    'X' => $x,
                    'Y' => $y,
                    'LEFT' =>  $clickable_sq_hor_offset + $x*$clickable_sq_hor_scale,
                    'TOP' => $clickable_sq_ver_offset + $y_rightside_up*$clickable_sq_ver_scale
                ) );


                $this->page->insert_block( "placement_square", array(
                    'X' => $x,
                    'Y' => $y,
                    'LEFT' =>  $plcmt_sq_hor_offset + $x*$plcmt_sq_hor_scale + $random_x,
                    'TOP' => $plcmt_sq_ver_offset + $y_rightside_up*$plcmt_sq_ver_scale + $random_y
                    //'LEFT' =>  $plcmt_sq_hor_offset + $x*$plcmt_sq_hor_scale,
                    //'TOP' => $plcmt_sq_ver_offset + $y_rightside_up*$plcmt_sq_ver_scale
                ) );
            }
        }
        




        /*********** Place your code below:  ************/


        /*
        
        // Examples: set the value of some element defined in your tpl file like this: {MY_VARIABLE_ELEMENT}

        // Display a specific number / string
        $this->tpl['MY_VARIABLE_ELEMENT'] = $number_to_display;

        // Display a string to be translated in all languages: 
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::_("A string to be translated");

        // Display some HTML content of your own:
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::raw( $some_html_code );
        
        */
        
        /*
        
        // Example: display a specific HTML block for each player in this game.
        // (note: the block is defined in your .tpl file like this:
        //      <!-- BEGIN myblock --> 
        //          ... my HTML code ...
        //      <!-- END myblock --> 
        

        $this->page->begin_block( "redstone_redstone", "myblock" );
        foreach( $players as $player )
        {
            $this->page->insert_block( "myblock", array( 
                                                    "PLAYER_NAME" => $player['player_name'],
                                                    "SOME_VARIABLE" => $some_value
                                                    ...
                                                     ) );
        }
        
        */



        /*********** Do not change anything below this line  ************/
  	}
    
  }
  

