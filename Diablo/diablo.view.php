<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Diablo implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * diablo.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in diablo_diablo.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
require_once( APP_BASE_PATH."view/common/game.view.php" );
  
class view_diablo_diablo extends game_view
{
    protected function getGameName()
    {
        // Used for translations and stuff. Please do not modify.
        return "diablo";
    }
    
  	function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        /*********** Place your code below:  ************/




        $this->page->begin_block( "diablo_diablo", "clickable_square" );

        $this->page->begin_block( "diablo_diablo", "placement_square" );


        $this->page->begin_block( "diablo_diablo", "dice_square" );


        
        //Get the size of the board regarding the choosen option
        $N=$this->game->getGameStateValue("board_size");
        
        //Say it to the .tpl to choose the right board image
        $this->tpl['BOARD_SIZE'] = $N;
        

        switch($N)
        {
            case 6:
                $clickable_sq_hor_offset = 33 - .5;    
                $clickable_sq_ver_offset = 133 - .5;       
                $clickable_sq_hor_scale = 114;       
                $clickable_sq_ver_scale = 114;   

                $placement_sq_hor_offset = 33 - .5;    
                $placement_sq_ver_offset = 133 - .5;       
                $placement_sq_hor_scale = 114;       
                $placement_sq_ver_scale = 114;   
            break;

            case 8:
                $clickable_sq_hor_offset = 39 - .5;    
                $clickable_sq_ver_offset = 139 - .5;       
                $clickable_sq_hor_scale = 84;       
                $clickable_sq_ver_scale = 84;   

                $placement_sq_hor_offset = 39 - .5;    
                $placement_sq_ver_offset = 139 - .5;       
                $placement_sq_hor_scale = 84;       
                $placement_sq_ver_scale = 84;   
            break;

            case 10:
                $clickable_sq_hor_offset = 35 - .5;          
                $clickable_sq_ver_offset = 135 - .5;      
                $clickable_sq_hor_scale = 68; 
                $clickable_sq_ver_scale = 68;

                $placement_sq_hor_offset = 35 - .5;    
                $placement_sq_ver_offset = 135 - .5;       
                $placement_sq_hor_scale = 68;       
                $placement_sq_ver_scale = 68;   
            break;
   		}

        
        for( $x=0; $x<$N; $x++ )
        {
            for( $y=0; $y<$N; $y++ )
            {
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
                    'LEFT' =>  $placement_sq_hor_offset + $x*$placement_sq_hor_scale,
                    'TOP' => $placement_sq_ver_offset + $y_rightside_up*$placement_sq_ver_scale
                ) );
            }
        }
        

        //
        //  DICE SQUARES 
        //
        for( $x=0; $x<2; $x++ )     
        {
            $this->page->insert_block( "dice_square", array(
                'X' => $x,
                'LEFT' => 30 + $x * 100,                    
                'TOP' => 30          
            ) );
        }




        /*********** Do not change anything below this line  ************/
  	}
}
