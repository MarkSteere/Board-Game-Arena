<?php
/**
*------
* BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
* Cephalopod implementation : © <Your name here> <Your email address here>
*
* This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
* See http://en.boardgamearena.com/#!doc/Studio for more information.
* -----
*
* cephalopod.view.php
*
* This is your "view" file.
*
* The method "build_page" below is called each time the game interface is displayed to a player, ie:
* _ when the game starts
* _ when a player refreshes the game page (F5)
*
* "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
* particular, you can set here the values of variables elements defined in cephalopod_cephalopod.tpl (elements
* like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
*
* Note: if the HTML of your game interface is always the same, you don't have to place anything here.
*
*/
  
    require_once( APP_BASE_PATH."view/common/game.view.php" );
  
    class view_cephalopod_cephalopod extends game_view
    {
        function getGameName() 
        {
            return "cephalopod";
        } 
        
  	function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        //$players = $this->game->loadPlayersBasicInfos();
        //$players_nbr = count( $players );


        $this->page->begin_block( "cephalopod_cephalopod", "square" );
        

        //Get the size of the board regarding the choosen option
        $N=$this->game->getGameStateValue("board_size");
        
        //Say it to the .tpl to choose the right board image
        $this->tpl['BOARD_SIZE'] = $N;


        $x_dimension = 0;
        $y_dimension = 0;


        switch ( $N )
        {
            case 3:
                $hor_scale = 140;                                     
                $ver_scale = 140;

                $hor_offset = 33;
                $ver_offset = 33;

                $x_dimension = 5;
                $y_dimension = 3;

                break;

            case 5:
                $hor_scale = 140;                                     
                $ver_scale = 140;

                $hor_offset = 33;
                $ver_offset = 33;

                $x_dimension = 5;
                $y_dimension = 5;

                break;
        }


        for( $x = 0; $x < $x_dimension; $x++ )                                
        {                                                       
            for( $y = 0; $y < $y_dimension; $y++ )
            {
                $y_rightside_up = $y_dimension - 1 - $y;                     // Coords (0,0) in lower left
            
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
  

