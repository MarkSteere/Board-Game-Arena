<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Gopher implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * gopher.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in gopher_gopher.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
require_once( APP_BASE_PATH."view/common/game.view.php" );
  
class view_gopher_gopher extends game_view
{
    function getGameName() {
        return "gopher";
    }

  	function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        
        $this->page->begin_block( "gopher_gopher", "square" );
        
        $hor_scale = 61.70;
        $ver_scale = 53.44;

        for( $x=0; $x<=10; $x++ )
        {
            for( $y=0; $y<=10; $y++ )
            {
                if ( ( ( $x <= 5 ) && ( $y <= $x + 5 ) ) ||  // ( $x, $y ) on the board
                     ( ( $x >= 6 ) && ( $y >= $x - 5) ) )
                {               
                    $this->page->insert_block( "square", array(
                        'X' => $x,
                        'Y' => $y,
                        'LEFT' => round ( ( $x + ( 10 - $y ) / 2 ) * $hor_scale - 118.5 ),
                        'TOP' => round ( $y * $ver_scale + 27 )
                    ) );
                }
            }        
        }     
  	}
}
  

