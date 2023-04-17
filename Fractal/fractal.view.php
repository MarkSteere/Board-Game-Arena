<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Fractal implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * fractal.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in fractal_fractal.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
require_once( APP_BASE_PATH."view/common/game.view.php" );
  
class view_fractal_fractal extends game_view
{
    function getGameName() {
        return "fractal";
    }    
    function build_page( $viewArgs )
    {		
  	    // Get players & players number
        //$players = $this->game->loadPlayersBasicInfos();
        //$players_nbr = count( $players );

        /*********** Place your code below:  ************/
 
        $this->page->begin_block( "fractal_fractal", "clickable_square" );
        
      
        //
        // PLACE LARGE CLICKABLE DIVS - ALL OF WHICH ARE ECCENTRIC
        //
        $hor_offset = 33 + 1;       /* ( board width - 5 * large tile width ) / 2 = ( 741 - 5 * 135 ) / 2 = 33  */

        $ver_offset = 58.731;   /* ( image height - board_height ) / 2 + (     board height - (   large cell height * ( 5 * .75 + .25 )   )     ) / 2 */
                                /*  = ( 741 - 640.8548 ) / 2 + (   640.8548 - ( 155.8845 * 4 )   ) / 2     */
                                /*  = 50.0726 + 8.6584 = 58.731  */

        $hor_scale = 135;       /* Large cell width = 135  */

        $ver_scale = 116.91;    /* Large cell height * .75 = 155.8845 * .75 = 116.9134   */

        $large_eccentric_tile_cells = array (
                                                array ( 0, 2 ),
                                                array ( 0, 3 ),
                                                array ( 0, 4 ),
                                                array ( 1, 4 ),
                                                array ( 2, 4 ),
                                                array ( 3, 3 ),
                                                array ( 4, 2 ),
                                                array ( 4, 1 ),
                                                array ( 4, 0 ),
                                                array ( 3, 0 ),
                                                array ( 2, 0 ),
                                                array ( 1, 1 ),
                                            );


        foreach ( $large_eccentric_tile_cells as $large_eccentric_tile_cell )
        {
            $u = $large_eccentric_tile_cell [ 0 ];
            $v = $large_eccentric_tile_cell [ 1 ];

            $this->page->insert_block( "clickable_square", array(
                'DIV_U' => 1000 + $u,
                'DIV_V' => 1000 + $v,
                'CLASS_U' => 1000 + $u,
                'CLASS_V' => 1000 + $v,
                'LEFT' =>  ( $u + ($v - 2) / 2 ) * $hor_scale + $hor_offset,                    
                'TOP' =>   ( 4 - $v ) * $ver_scale + $ver_offset          // Turned $v right side up to match screen coordinates
            ) );
        }


        //
        // PLACE MEDIUM ECCENTRIC CLICKABLE DIVS 
        //
        $hor_offset = 123 + 1;       // ( board width - 11 * medium tile width ) / 2 = ( 741 - 11 * 45.0001 ) / 2 = 122.99945 

        $ver_offset = 149.66 + .5;   // ( image height - board_height ) / 2 + (     board height - (   medium cell height * ( 11 * .75 + .25 )   )     ) / 2 
                                    //  = ( 741 - 640.8548 ) / 2 + (   640.8548 - ( 51.9616 * 8.5 )   ) / 2     
                                    //  = 50.0726 + 99.5906 = 149.6632 

        $hor_scale = 45;            // Medium cell width = 45.0001  

        $ver_scale = 38.97;         // Medium cell height * .75 = 51.9616 * .75 = 38.9712 


        $medium_eccentric_tile_cells = array (
                                                array ( 3, 5 ),
                                                array ( 3, 6 ),
                                                array ( 3, 7 ),
                                                array ( 4, 7 ),
                                                array ( 5, 7 ),
                                                array ( 6, 6 ),
                                                array ( 7, 5 ),
                                                array ( 7, 4 ),
                                                array ( 7, 3 ),
                                                array ( 6, 3 ),
                                                array ( 5, 3 ),
                                                array ( 4, 4 ),
                                            );


        foreach ( $medium_eccentric_tile_cells as $medium_eccentric_tile_cell )
        {
            $u = $medium_eccentric_tile_cell [ 0 ];
            $v = $medium_eccentric_tile_cell [ 1 ];

            $this->page->insert_block( "clickable_square", array(
                'DIV_U' => 100 + $u,
                'DIV_V' => 100 + $v,
                'CLASS_U' => 100 + $u,
                'CLASS_V' => 100 + $v,
                'LEFT' =>  ( $u + ($v - 5) / 2 ) * $hor_scale + $hor_offset,                    
                'TOP' =>   ( 10 - $v ) * $ver_scale + $ver_offset          // Turned $v right side up to match screen coordinates
            ) );
        }



        //
        // PLACE MEDIUM HEXAGONAL CLICKABLE DIVS 
        //
        $hor_offset = 123 + 1;       // ( board width - 11 * medium tile width ) / 2 = ( 741 - 11 * 45.0001 ) / 2 = 122.99945 

        $ver_offset = 149.66 + .5;   // ( image height - board_height ) / 2 + (     board height - (   medium cell height * ( 11 * .75 + .25 )   )     ) / 2 
                                    //  = ( 741 - 640.8548 ) / 2 + (   640.8548 - ( 51.9616 * 8.5 )   ) / 2     
                                    //  = 50.0726 + 99.5906 = 149.6632 

        $hor_scale = 45;            // Medium cell width = 45.0001  

        $ver_scale = 38.97;         // Medium cell height * .75 = 51.9616 * .75 = 38.9712 


        $medium_hexagonal_tile_cells = array (                      // Start with bottom row, and proceed row by row upwards
                                                array ( 6, 0 ),
                                                array ( 9, 0 ),
 
                                                array ( 4, 1 ),
                                                array ( 5, 1 ),
                                                array ( 6, 1 ),
                                                array ( 7, 1 ),
                                                array ( 8, 1 ),
                                                array ( 9, 1 ),
                                                array ( 10, 1 ),

                                                array ( 4, 2 ),
                                                array ( 5, 2 ),
                                                array ( 6, 2 ),
                                                array ( 7, 2 ),
                                                array ( 8, 2 ),
                                                array ( 9, 2 ),

                                                array ( 3, 3 ),
                                                array ( 4, 3 ),
                                                array ( 8, 3 ),
                                                array ( 9, 3 ),

                                                array ( 1, 4 ),
                                                array ( 2, 4 ),
                                                array ( 3, 4 ),
                                                array ( 8, 4 ),
                                                array ( 9, 4 ),
                                                array ( 10, 4 ),

                                                array ( 1, 5 ),
                                                array ( 2, 5 ),
                                                array ( 8, 5 ),
                                                array ( 9, 5 ),

                                                array ( 0, 6 ),
                                                array ( 1, 6 ),
                                                array ( 2, 6 ),
                                                array ( 7, 6 ),
                                                array ( 8, 6 ),
                                                array ( 9, 6 ),

                                                array ( 1, 7 ),
                                                array ( 2, 7 ),
                                                array ( 6, 7 ),
                                                array ( 7, 7 ),

                                                array ( 1, 8 ),
                                                array ( 2, 8 ),
                                                array ( 3, 8 ),
                                                array ( 4, 8 ),
                                                array ( 5, 8 ),
                                                array ( 6, 8 ),

                                                array ( 0, 9 ),
                                                array ( 1, 9 ),
                                                array ( 2, 9 ),
                                                array ( 3, 9 ),
                                                array ( 4, 9 ),
                                                array ( 5, 9 ),
                                                array ( 6, 9 ),

                                                array ( 1, 10 ),
                                                array ( 4, 10 )
 
        );


        foreach ( $medium_hexagonal_tile_cells as $medium_hexagonal_tile_cell )
        {
            $u = $medium_hexagonal_tile_cell [ 0 ];
            $v = $medium_hexagonal_tile_cell [ 1 ];

            $this->page->insert_block( "clickable_square", array(
                'DIV_U' => 100 + $u,
                'DIV_V' => 100 + $v,
                'CLASS_U' => 100,                                           // No $u because class same for all normal hexagons
                'CLASS_V' => 100,                                           // No $v because class same for all normal hexagons
                'LEFT' =>  ( $u + ($v - 5) / 2 ) * $hor_scale + $hor_offset,                    
                'TOP' =>   ( 10 - $v ) * $ver_scale + $ver_offset           // Turned $v right side up to match screen coordinates
            ) );
        }
 


        //
        // PLACE SMALL HEXAGONAL CLICKABLE DIVS - S cell s-to-s, p-to-p = 15.0001, 17.3206
        //
        $hor_offset = 288 + 1;       // ( board width - 11 * small tile width ) / 2 = ( 741 - 11 * 15.0001 ) / 2 = 287.99945

        $ver_offset = 296.89 + .5;   // ( image height - board_height ) / 2 + (     board height - (   medium cell height * ( 11 * .75 + .25 )   )     ) / 2 
                                     //  = ( 741 - 640.8548 ) / 2 + (   640.8548 - ( 17.3206 * 8.5 )   ) / 2     
                                     //  = 50.0726 + 246.81485 = 296.88745

        $hor_scale = 15;             // Small cell width = 15.0001  

        $ver_scale = 13;          // Small cell height * .75 = 17.3206 * .75 = 12.99045


        $small_hexagonal_tile_cells = array (                      // Start with bottom row, and proceed row by row upwards
                                                array ( 6, 0 ),
                                                array ( 9, 0 ),
 
                                                array ( 4, 1 ),
                                                array ( 5, 1 ),
                                                array ( 6, 1 ),
                                                array ( 7, 1 ),
                                                array ( 8, 1 ),
                                                array ( 9, 1 ),
                                                array ( 10, 1 ),

                                                array ( 4, 2 ),
                                                array ( 5, 2 ),
                                                array ( 6, 2 ),
                                                array ( 7, 2 ),
                                                array ( 8, 2 ),
                                                array ( 9, 2 ),

                                                array ( 3, 3 ),
                                                array ( 4, 3 ),
                                                array ( 5, 3 ),
                                                array ( 6, 3 ),
                                                array ( 7, 3 ),
                                                array ( 8, 3 ),
                                                array ( 9, 3 ),

                                                array ( 1, 4 ),
                                                array ( 2, 4 ),
                                                array ( 3, 4 ),
                                                array ( 4, 4 ),
                                                array ( 5, 4 ),
                                                array ( 6, 4 ),
                                                array ( 7, 4 ),
                                                array ( 8, 4 ),
                                                array ( 9, 4 ),
                                                array ( 10, 4 ),

                                                array ( 1, 5 ),
                                                array ( 2, 5 ),
                                                array ( 3, 5 ),
                                                array ( 4, 5 ),
                                                array ( 5, 5 ),
                                                array ( 6, 5 ),
                                                array ( 7, 5 ),
                                                array ( 8, 5 ),
                                                array ( 9, 5 ),

                                                array ( 0, 6 ),
                                                array ( 1, 6 ),
                                                array ( 2, 6 ),
                                                array ( 3, 6 ),
                                                array ( 4, 6 ),
                                                array ( 5, 6 ),
                                                array ( 6, 6 ),
                                                array ( 7, 6 ),
                                                array ( 8, 6 ),
                                                array ( 9, 6 ),

                                                array ( 1, 7 ),
                                                array ( 2, 7 ),
                                                array ( 3, 7 ),
                                                array ( 4, 7 ),
                                                array ( 5, 7 ),
                                                array ( 6, 7 ),
                                                array ( 7, 7 ),

                                                array ( 1, 8 ),
                                                array ( 2, 8 ),
                                                array ( 3, 8 ),
                                                array ( 4, 8 ),
                                                array ( 5, 8 ),
                                                array ( 6, 8 ),

                                                array ( 0, 9 ),
                                                array ( 1, 9 ),
                                                array ( 2, 9 ),
                                                array ( 3, 9 ),
                                                array ( 4, 9 ),
                                                array ( 5, 9 ),
                                                array ( 6, 9 ),

                                                array ( 1, 10 ),
                                                array ( 4, 10 )
 
        );


        foreach ( $small_hexagonal_tile_cells as $small_hexagonal_tile_cell )
        {
            $u = $small_hexagonal_tile_cell [ 0 ];
            $v = $small_hexagonal_tile_cell [ 1 ];

            $this->page->insert_block( "clickable_square", array(
                'DIV_U' => $u,
                'DIV_V' => $v,
                'CLASS_U' => 0,                                              // No $u because class same for all normal hexagons
                'CLASS_V' => 0,                                              // No $v because class same for all normal hexagons
                'LEFT' =>  ( $u + ($v - 5) / 2 ) * $hor_scale + $hor_offset,                    
                'TOP' =>   ( 10 - $v ) * $ver_scale + $ver_offset            // Turned $v right side up to match screen coordinates
            ) );
        }
  	}
}
  

