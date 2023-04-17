<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Dodo implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * dodo.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in dodo_dodo.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
  require_once( APP_BASE_PATH."view/common/game.view.php" );
  
  class view_dodo_dodo extends game_view
  {
    function getGameName() {
        return "dodo";
    }    
  	function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        
        $this->page->begin_block( "dodo_dodo", "square" );
        
        $hor_scale = 95.2627;
        $ver_scale = 110;

        for( $u=0; $u<=6; $u++ )
        {
            for( $v=0; $v<=6; $v++ )
            {
                if (    (  ($u >= 0 && $u <= 3) && ($v >= 3 - $u && $v <= 6)  ) ||  // Cell is on the board
                        (  ($u >= 4 && $u <= 6) && ($v >= 0 && $v <= 9 - $u)  )    )
                {               
                    $this->page->insert_block( "square", array(
                        'U' => $u,
                        'V' => $v,
                        'LEFT' =>  (  $u + $v - 3 ) * $hor_scale + 24.2,
                        'TOP' =>  ( 6 + $u - $v ) / 2 * $ver_scale + 46.2 
                    ) );
                }
            }        
        }  
  	}
}
  

