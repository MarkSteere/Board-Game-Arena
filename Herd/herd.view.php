<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Herd implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * herd.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in herd_herd.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
  require_once( APP_BASE_PATH."view/common/game.view.php" );
  
  class view_herd_herd extends game_view
  {
    function getGameName() {
        return "herd";
    }    
  	function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        /*********** Place your code below:  ************/

  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        /*********** Place your code below:  ************/



        $this->page->begin_block( "herd_herd", "clickable_cell" );

        $this->page->begin_block( "herd_herd", "placement_cell" );


        $this->page->begin_block( "herd_herd", "dice_square" );


        
        //Get the size of the board regarding the choosen option
        $N=$this->game->getGameStateValue("board_size");
        
        //Say it to the .tpl to choose the right board image
        $this->tpl['BOARD_SIZE'] = $N;
        

        //
        //  CLICKABLE CELLS 
        //
        switch($N)
        {
            case 4:                 // board size 4 - large
                $hor_scale = 93.6; 
                //$hor_scale = 93.5; 
                
                $ver_scale = 81.1;
                //$ver_scale = 81;
                //$ver_scale = 80.9;
                //$ver_scale = 80.8;

                $hor_offset = 49.6;
                //$hor_offset = 49.7;
                $ver_offset = 50;

                for( $u=0; $u<7; $u++ )                                 
                {                                                       
                    for( $v=0; $v<7; $v++ )
                    {
                        if (    (  $u < 4 && $v >= 3 - $u  )             // Cell on board
                             || (  $u >= 4 && $v <= 9 - $u  )    )
                        {
                            $this->page->insert_block( "clickable_cell", array(
                                'U' => $u,
                                'V' => $v,
                                'LEFT' =>  ( $u + ($v - 3) / 2 ) * $hor_scale + $hor_offset,                    
                                'TOP' =>   ( 6 - $v ) * $ver_scale + $ver_offset          // Turned $v right side up to match screen coordinates
                            ) );
                        }            
                    }  
                }  

                break;

            case 5:                 // board size 5 - medium
                $hor_scale = 72.6;                                       
                //$hor_scale = 72.8;                                       
                //$hor_scale = 73;                                       
                //$hor_scale = 69.75;    
                
                $ver_scale = 62.9;  
                //$ver_scale = 63;  
                //$ver_scale = 60.4;  

                $hor_offset = 49.7;
                $ver_offset = 53;

                for( $u=0; $u<9; $u++ )                                 
                {                                                       
                    for( $v=0; $v<9; $v++ )
                    {
                        if (    (  $u < 5 && $v >= 4 - $u  )             // Cell on board
                             || (  $u >= 5 && $v <= 12 - $u  )    )
                        {
                            $this->page->insert_block( "clickable_cell", array(
                                'U' => $u,
                                'V' => $v,
                                'LEFT' =>  ( $u + ($v - 4) / 2 ) * $hor_scale + $hor_offset,                    
                                'TOP' =>   ( 8 - $v ) * $ver_scale + $ver_offset          // Turned $v right side up to match screen coordinates
                            ) );
                        }            
                    }  
                }  

                break;

                case 6:                 // board size 6 - large
                    //$hor_scale = 60.58;                                       
                    //$hor_scale = 60.5;                                       
                    $hor_scale = 60.6;     
                    
                    $ver_scale = 52.4;
                    //$ver_scale = 52.5;

                    $hor_offset = 43;
                    $ver_offset = 50;
       
                    for( $u=0; $u<11; $u++ )                                 
                    {                                                       
                        for( $v=0; $v<11; $v++ )
                        {
                            if (    (  $u < 6 && $v >= 5 - $u  )             // Cell on board
                                 || (  $u >= 6 && $v <= 15 - $u  )    )
                            {
                                $this->page->insert_block( "clickable_cell", array(
                                    'U' => $u,
                                    'V' => $v,
                                    'LEFT' =>  ( $u + ($v - 5) / 2 ) * $hor_scale + $hor_offset,                    
                                    'TOP' =>   ( 10 - $v ) * $ver_scale + $ver_offset          // Turned $v right side up to match screen coordinates
                                ) );
                            }            
                        }  
                    }  

                    break;
   		}


        //
        //  PLACEMENT CELLS 
        //
        switch($N)
        {
            case 4:                 // board size 4 - large
                $hor_scale = 93.6; 
                
                $ver_scale = 81.1;

                $hor_offset = 48.6;
                //$hor_offset = 49.1;
                //$hor_offset = 49.6;

                $ver_offset = 50;

                for( $u=0; $u<7; $u++ )                                 
                {                                                       
                    for( $v=0; $v<7; $v++ )
                    {
                        if (    (  $u < 4 && $v >= 3 - $u  )             // Cell on board
                             || (  $u >= 4 && $v <= 9 - $u  )    )
                        {
                            $this->page->insert_block( "placement_cell", array(
                                'U' => $u,
                                'V' => $v,
                                'LEFT' =>  ( $u + ($v - 3) / 2 ) * $hor_scale + $hor_offset,                    
                                'TOP' =>   ( 6 - $v ) * $ver_scale + $ver_offset          // Turned $v right side up to match screen coordinates
                            ) );
                        }            
                    }  
                }  

                break;

            case 5:                 // board size 5 - medium
                $hor_scale = 72.6;                                       
                //$hor_scale = 72.8;                                       
                //$hor_scale = 73;                                       
                //$hor_scale = 69.75;    
                
                $ver_scale = 62.9;  
                //$ver_scale = 63;  
                //$ver_scale = 60.4;  

                $hor_offset = 49.7;
                $ver_offset = 53;

                for( $u=0; $u<9; $u++ )                                 
                {                                                       
                    for( $v=0; $v<9; $v++ )
                    {
                        if (    (  $u < 5 && $v >= 4 - $u  )             // Cell on board
                             || (  $u >= 5 && $v <= 12 - $u  )    )
                        {
                            $this->page->insert_block( "placement_cell", array(
                                'U' => $u,
                                'V' => $v,
                                'LEFT' =>  ( $u + ($v - 4) / 2 ) * $hor_scale + $hor_offset,                    
                                'TOP' =>   ( 8 - $v ) * $ver_scale + $ver_offset          // Turned $v right side up to match screen coordinates
                            ) );
                        }            
                    }  
                }  

                break;

                case 6:                 // board size 6 - large
                    //$hor_scale = 60.58;                                       
                    //$hor_scale = 60.5;                                       
                    $hor_scale = 60.6;     
                    
                    $ver_scale = 52.4;
                    //$ver_scale = 52.5;

                    $hor_offset = 43;
                    $ver_offset = 50;
       
                    for( $u=0; $u<11; $u++ )                                 
                    {                                                       
                        for( $v=0; $v<11; $v++ )
                        {
                            if (    (  $u < 6 && $v >= 5 - $u  )             // Cell on board
                                 || (  $u >= 6 && $v <= 15 - $u  )    )
                            {
                                $this->page->insert_block( "placement_cell", array(
                                    'U' => $u,
                                    'V' => $v,
                                    'LEFT' =>  ( $u + ($v - 5) / 2 ) * $hor_scale + $hor_offset,                    
                                    'TOP' =>   ( 10 - $v ) * $ver_scale + $ver_offset          // Turned $v right side up to match screen coordinates
                                ) );
                            }            
                        }  
                    }  

                    break;
   		}



        //
        //  DICE SQUARES 
        //
        for( $x=0; $x<2; $x++ )     
        {
            $this->page->insert_block( "dice_square", array(
                'X' => $x,
                'LEFT' => 10 + $x * 90,                    
                'TOP' => 30          
            ) );
        }


        /*********** Do not change anything below this line  ************/
  	}
  }
  
