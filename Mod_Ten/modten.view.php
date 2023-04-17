<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * ModTen implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * modten.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in modten_modten.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
require_once( APP_BASE_PATH."view/common/game.view.php" );
  
class view_modten_modten extends game_view
{
    protected function getGameName()
    {
        // Used for translations and stuff. Please do not modify.
        return "modten";
    }
    
  	function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        /*********** Place your code below:  ************/

        //Say it to the .tpl to choose the right board image
        $this->tpl['N_PLAYERS'] = $players_nbr;


        //
        //  HAND SIZE 
        //
        switch ( $players_nbr )
        {
            case 2:
                $hand_size =  16;
                break;

            case 3:
                $hand_size =  10;
                break;

            case 4:
                $hand_size =  18;
                break;

            case 5:
                $hand_size =  12;
                break;

            case 6:
                $hand_size =  10;
                break;
        }
        
        $this->tpl['HAND_SIZE'] = $hand_size;
      
      
        $this->page->begin_block( "modten_modten", "player" );
        
        
        //
        // Arrange players so that I am on top
        //
        $player_to_pos = $this->game->getPlayersToPosition();

        foreach( $player_to_pos as $player_id => $pos )
        {
            $this->page->insert_block( "player", array( "PLAYER_ID" => $player_id,
                                                        "PLAYER_NAME" => $players[$player_id]['player_name'],
                                                        "PLAYER_COLOR" => $players[$player_id]['player_color'],
                                                        "PLAYER_LIST_POSITION" => $pos ) );
        }

        /*********** Do not change anything below this line  ************/
  	}
}
