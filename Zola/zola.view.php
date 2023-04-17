<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Zola implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * zola.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in zola_zola.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
  require_once( APP_BASE_PATH."view/common/game.view.php" );
  
  class view_zola_zola extends game_view
  {
    function getGameName() {
        return "zola";
    }    
  	function build_page( $viewArgs )
  	{		
  	  // Get players & players number
      //$players = $this->game->loadPlayersBasicInfos();
      //$players_nbr = count( $players );

      $this->page->begin_block( "zola_zola", "square" );
        
      //Get the size of the board regarding the choosen option
      $N=$this->game->getGameStateValue("board_size");
        
      //Say it to the .tpl to choose the right board image
      $this->tpl['BOARD_SIZE'] = $N;

      switch($N)
      {
        case 6:
          $hor_scale = 113;                                       // 107 + 6
          $ver_scale = 113;

          $hor_offset = 39;
          $ver_offset = 39;
          break;

        case 8:
          $hor_scale = 84;                                       // 107 + 6
          $ver_scale = 84;

          $hor_offset = 42;
          $ver_offset = 42;
          break;
      }


      for( $x=0; $x<$N; $x++ )                                 // Coords (0,0) in lower left
      {                                                       //
        for( $y=0; $y<$N; $y++ )
        {
          $y_rightside_up = $N - $y - 1;
            
           $this->page->insert_block( "square", array(
              'X' => $x,
              'Y' => $y,
              'LEFT' =>  $hor_scale * $x + $hor_offset,                    
              'TOP' =>   $ver_scale * $y_rightside_up + $ver_offset
              ) );
        }  
      }  

    }
  }
      


        
  /*
      $hor_scale = 113;                                       // 107 + 6
      $ver_scale = 113;
                                                              //
      for( $x=0; $x<6; $x++ )                                 // Coords (0,0) in lower left
      {                                                       //
        for( $y=0; $y<6; $y++ )
        {
          $y_rightside_up = 5 - $y;
            
          $this->page->insert_block( "square", array(
          'X' => $x,
          'Y' => $y,
          'LEFT' =>  $hor_scale * $x + 39,                    
          'TOP' =>   $ver_scale * $y_rightside_up + 39
          ) );
        }  
      }  
    }
  }
  */
  

