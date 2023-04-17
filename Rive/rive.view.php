<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Rive implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * rive.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in rive_rive.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */

require_once( APP_BASE_PATH."view/common/game.view.php" );

class view_rive_rive extends game_view
{
    function getGameName() {
        return "rive";
    }
    
  	function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        //$players = $this->game->loadPlayersBasicInfos();
        //$players_nbr = count( $players );

        /*********** Place your code below:  ************/

        $this->page->begin_block( "rive_rive", "square" );
        
        //Get the size of the board regarding the choosen option
        $N_option = $this->game->getGameStateValue("board_size");

        switch ( $N_option )
        {
		    case 3:
                $N = 3; // size 3 - small
                break;

		    case 5:
                $N = 5; // size 3x3x5 - medium
                break;

		    case 40:
                $N = 4; // size 4 - large
                break;
        }


        
      //Say it to the .tpl to choose the right board image
      $this->tpl['BOARD_SIZE'] = $N;
      

      switch($N)
      {
        case 3:                 // board size 3 - small
            $hor_scale = 130;                                       
            $ver_scale = 112.5;

            $hor_offset = 52;
            $ver_offset = 77;
       
            for( $u=0; $u<5; $u++ )                                 
                {                                                       
                for( $v=0; $v<5; $v++ )
                {
                    if (    (  $u < 3 && $v >= 2 - $u  )             // Cell on board
                         || (  $u >= 3 && $v <= 6 - $u  )    )
                    {
                        $this->page->insert_block( "square", array(
                            'U' => $u,
                            'V' => $v,
                            'LEFT' =>  ( $u + ($v - 2) / 2 ) * $hor_scale + $hor_offset,                    
                            'TOP' =>   ( 4 - $v ) * $ver_scale + $ver_offset          // Turned $v right side up to match screen coordinates
                        ) );
                    }            
                }  
            }  

            break;

        case 5:                 // board size 3x3x5 - medium
            $hor_scale = 93.5;                                       
            $ver_scale = 80.8;

            $hor_offset = 49.7;
            $ver_offset = 36.6;

            for( $u=0; $u<7; $u++ )                                 
                {                                                       
                for( $v=0; $v<5; $v++ )
                {
                    if (    (  $u < 3 && $v >= 2 - $u  )             // Cell on board
                         || (  ($u==3 || $u==4) && $v <= 4  )
                         || (  $u >= 5 && $v <= 8 - $u  )    )
                    {
                        $this->page->insert_block( "square", array(
                            'U' => $u,
                            'V' => $v,
                            'LEFT' =>  ( $u + ($v - 2) / 2 ) * $hor_scale + $hor_offset,                    
                            'TOP' =>   ( 4 - $v ) * $ver_scale + $ver_offset          // Turned $v right side up to match screen coordinates
                        ) );
                    }            
                }  
            }  

            break;

        case 4:                 // board size 4 - large
            $hor_scale = 93.5;                                       
            $ver_scale = 80.8;

            $hor_offset = 49.7;
            $ver_offset = 80;

            for( $u=0; $u<7; $u++ )                                 
                {                                                       
                for( $v=0; $v<7; $v++ )
                {
                    if (    (  $u < 4 && $v >= 3 - $u  )             // Cell on board
                         || (  $u >= 4 && $v <= 9 - $u  )    )
                    {
                        $this->page->insert_block( "square", array(
                            'U' => $u,
                            'V' => $v,
                            'LEFT' =>  ( $u + ($v - 3) / 2 ) * $hor_scale + $hor_offset,                    
                            'TOP' =>   ( 6 - $v ) * $ver_scale + $ver_offset          // Turned $v right side up to match screen coordinates
                        ) );
                    }            
                }  
            }  

            break;
       }
      

        /*********** Do not change anything below this line  ************/
  	}
}
  

