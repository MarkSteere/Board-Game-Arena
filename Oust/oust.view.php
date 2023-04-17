<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Oust implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * oust.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in oust_oust.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */

require_once( APP_BASE_PATH."view/common/game.view.php" );

class view_oust_oust extends game_view
{
    function getGameName() {
        return "oust";
    }
    
  	function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        //$players = $this->game->loadPlayersBasicInfos();
        //$players_nbr = count( $players );

        /*********** Place your code below:  ************/

      $this->page->begin_block( "oust_oust", "square" );
        
      //Get the size of the board regarding the choosen option
      $N=$this->game->getGameStateValue("board_size");
        
      //Say it to the .tpl to choose the right board image
      $this->tpl['BOARD_SIZE'] = $N;
      

      switch($N)
      {
        case 6:                 // board size 6
            $hor_scale = 60.6;                                       
            $ver_scale = 52.5;

            $hor_offset = 43;
            $ver_offset = 49.5;
       
            for( $u=0; $u<11; $u++ )                                 
                {                                                       
                for( $v=0; $v<11; $v++ )
                {
                    if (    (  $u < 6 && $v >= 5 - $u  )             // Cell on board
                         || (  $u >= 6 && $v <= 15 - $u  )    )
                    {
                        $this->page->insert_block( "square", array(
                            'U' => $u,
                            'V' => $v,
                            'LEFT' =>  ( $u + ($v - 5) / 2 ) * $hor_scale + $hor_offset,                    
                            'TOP' =>   ( 10 - $v ) * $ver_scale + $ver_offset          // Turned $v right side up to match screen coordinates
                        ) );
                    }            
                }  
            }  

            break;

        case 7:                 // board size 7
            $hor_scale = 52;                                       
            $ver_scale = 45;

            $hor_offset = 38.25;
            $ver_offset = 46.67;

            for( $u=0; $u<13; $u++ )                                 
                {                                                       
                for( $v=0; $v<13; $v++ )
                {
                    if (    (  $u < 7 && $v >= 6 - $u  )             // Cell on board
                         || (  $u >= 7 && $v <= 18 - $u  )    )
                    {
                        $this->page->insert_block( "square", array(
                            'U' => $u,
                            'V' => $v,
                            'LEFT' =>  ( $u + ($v - 6) / 2 ) * $hor_scale + $hor_offset,                    
                            'TOP' =>   ( 12 - $v ) * $ver_scale + $ver_offset          // Turned $v right side up to match screen coordinates
                        ) );
                    }            
                }  
            }  

            break;

        case 8:                 // board size 8
            $hor_scale = 45;                                       
            $ver_scale = 39;

            $hor_offset = 38.25;
            $ver_offset = 47;

            for( $u=0; $u<15; $u++ )                                 
                {                                                       
                for( $v=0; $v<15; $v++ )
                {
                    if (    (  $u < 8 && $v >= 7 - $u  )             // Cell on board
                         || (  $u >= 8 && $v <= 21 - $u  )    )
                    {
                        $this->page->insert_block( "square", array(
                            'U' => $u,
                            'V' => $v,
                            'LEFT' =>  ( $u + ($v - 7) / 2 ) * $hor_scale + $hor_offset,                    
                            'TOP' =>   ( 14 - $v ) * $ver_scale + $ver_offset          // Turned $v right side up to match screen coordinates
                        ) );
                    }            
                }  
            }  

            break;
       }
      

        /*********** Do not change anything below this line  ************/
  	}
}
  

