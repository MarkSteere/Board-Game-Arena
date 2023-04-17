<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Icebreaker implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * icebreaker.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in icebreaker_icebreaker.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
  require_once( APP_BASE_PATH."view/common/game.view.php" );
  
  class view_icebreaker_icebreaker extends game_view
  {
    function getGameName() {
        return "icebreaker";
    }    
  	function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        //$players = $this->game->loadPlayersBasicInfos();
        //$players_nbr = count( $players );

        /*********** Place your code below:  ************/


      $this->page->begin_block( "icebreaker_icebreaker", "square" );
        
      //Get the size of the board regarding the choosen option
      $N=$this->game->getGameStateValue("board_size");
        
      //Say it to the .tpl to choose the right board image
      $this->tpl['BOARD_SIZE'] = $N;
      
      switch($N)
      {
        case 5:
          $hor_scale = 74.95;                                       
          $ver_scale = 64.9;

          $hor_offset = 39.25;
          $ver_offset = 74;
          break;

        case 6:
          $hor_scale = 61.8894;                                       
          $ver_scale = 53.5981;

          $hor_offset = 36.1085;
          $ver_offset = 73.0097;
          break;

        case 7:
          $hor_scale = 53.26;                                       
          $ver_scale = 46.1;

          $hor_offset = 30.3;
          $ver_offset = 69;
          break;

        case 8:
          $hor_scale = 46.3013;                                       
          $ver_scale = 40.0981;

          $hor_offset = 29.3;
          $ver_offset = 69.3087;
          break;
      }


      for( $u=0; $u<2*$N-1; $u++ )                                 
      {                                                       
        for( $v=0; $v<2*$N-1; $v++ )
        {
          if (    (  $u < $N && $v >= $N - 1 - $u  )             // Cell on board
               || (  $u >= $N && $v <= 3*$N - $u - 3  )    )
          {
            $this->page->insert_block( "square", array(
                'U' => $u,
                'V' => $v,
                'LEFT' =>  ( $u + ($v - $N + 1) / 2 ) * $hor_scale + $hor_offset,                    
                'TOP' =>   ( 2 * $N - 2 - $v ) * $ver_scale + $ver_offset          // Turned $v right side up to match screen coordinates
            ) );
          }            
        }  
      }  
      

  	}
  }
  

