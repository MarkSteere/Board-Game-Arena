<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Impasse implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * impasse.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in impasse_impasse.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
  require_once( APP_BASE_PATH."view/common/game.view.php" );
  
  class view_impasse_impasse extends game_view
  {
    function getGameName() {
        return "impasse";
    }    
  	function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        /*********** Place your code below:  ************/



        $this->page->begin_block( "impasse_impasse", "clickable_square" );

        $this->page->begin_block( "impasse_impasse", "placement_square" );


        
        //Get the size of the board regarding the choosen option
        $N=$this->game->getGameStateValue("board_size");
        
        //Say it to the .tpl to choose the right board image
        $this->tpl['BOARD_SIZE'] = $N;
        

        switch($N)
        {
            case 8:
                $clickable_sq_hor_offset = 35 - .5;    
                $clickable_sq_ver_offset = 35 - .5;       
                $clickable_sq_hor_scale = 85;       
                $clickable_sq_ver_scale = 85;   

                $placement_sq_hor_offset = 35 - .5;    
                $placement_sq_ver_offset = 35 - .5;       
                $placement_sq_hor_scale = 85;       
                $placement_sq_ver_scale = 85;   
            break;

            case 10:
                $clickable_sq_hor_offset = 35 - .5;          
                $clickable_sq_ver_offset = 35 - .5;      
                $clickable_sq_hor_scale = 68; 
                $clickable_sq_ver_scale = 68;

                $placement_sq_hor_offset = 35 - .5;    
                $placement_sq_ver_offset = 35 - .5;       
                $placement_sq_hor_scale = 68;       
                $placement_sq_ver_scale = 68;   
            break;
   		}

        
        for( $x=0; $x<$N; $x++ )
        {
            for( $y=0; $y<$N; $y++ )
            {
                $y_rightside_up = $N - 1 - $y;                     // Coords (0,0) in lower left

                if (    ( $x + $y ) % 2 == 0    )
                {                        
                    $this->page->insert_block( "clickable_square", array(
                        'X' => $x,
                        'Y' => $y,
                        'LEFT' =>  $clickable_sq_hor_offset + $x*$clickable_sq_hor_scale,
                        'TOP' => $clickable_sq_ver_offset + $y_rightside_up*$clickable_sq_ver_scale
                    ) );

                    $this->page->insert_block( "placement_square", array(
                        'X' => $x,
                        'Y' => $y,
                        'LEFT' =>  $placement_sq_hor_offset + $x*$placement_sq_hor_scale,
                        'TOP' => $placement_sq_ver_offset + $y_rightside_up*$placement_sq_ver_scale
                    ) );
                }
            }
        }
        


        /*********** Do not change anything below this line  ************/
  	}
  }
  

