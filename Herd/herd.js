/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Herd implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * herd.js
 *
 * Herd user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter"
],
function (dojo, declare) {
    return declare("bgagame.herd", ebg.core.gamegui, {
        constructor: function(){
            console.log('herd constructor');
              
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;

        },
        
        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
        
        setup: function( gamedatas )
        {
            console.log( "Starting game setup" );
            
            // Setting up player boards
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];
                         
                //
                // Player panel
                // 
                var player_board_div = $('player_board_'+player_id);
                //alert(player_board_div.id);

                dojo.place(
                    this.format_block(
                        'jstpl_player_board',
                        {
                            player_id : player_id,
                            code_img : gamedatas.players[player_id]['color']
                        }
                    )
                    , player_board_div
                );
            }
            


            //
            //  Coordinates overlay 
            //
            if (this.prefs[100].value == 1)
            {
                this.addOverlay ();                
                   
                //console.log( "Show overlay" );
            }
            else {
                //console.log( "Don't show overlay" );
            }


 
            //
            //  PLAYER PANEL - NUMBER OF STONES ON BOARD
            //
            for (var keyPlayerId in gamedatas.numberOfStones) 
            {
                //console.log( "placedTiles" + gamedatas.placedTiles[keyPlayerId] );

                number_of_stones = gamedatas.numberOfStones[keyPlayerId];

                dojo.byId("number-of-stones_p"+keyPlayerId).innerHTML = number_of_stones;
            }


            //
            // SET UP BOARD WITH STONES
            //   
            for( var i in gamedatas.board )
            {
                var square = gamedatas.board[i];

                if( square.player !== null )
                {
                    //console.log( " " + square.u + " " + square.v + " " +  square.player + " " +  square.stone_id );

                    this.addStoneOnBoard ( square.u, square.v, square.player, square.stone_id, gamedatas.board_size );
                }            
            }


            //
            // SHOW CURRENT DICE ROLL
            //   
            var die_0 = gamedatas.rolled_dice[0];
            var die_1 = gamedatas.rolled_dice[1];

            var die_0_id = gamedatas.rolled_dice_IDs[0];
            var die_1_id = gamedatas.rolled_dice_IDs[1];

            this.showRolledDice ( die_0, die_1, die_0_id, die_1_id, gamedatas.active_player_id );
            
 
            //
            // ON CLICKED CELL 
            //            
            //console.log( '.clickable_cell_'+gamedatas.board_size );

            dojo.query( '.clickable_cell_'+gamedatas.board_size ).connect( 'onclick', this, 'onClickedCell' );        
            

 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
        },
       


        //
        // SHOW ROLLED DICE
        //
        showRolledDice: function( die_0, die_1, die_0_id, die_1_id, player_id )
        {   
            //console.log( 'rolled dice = ' + die_0 + ' ' + die_1 + ' ' + die_0_id + ' ' + die_1_id + ' ' + player_id );

            //
            //  DIE 0 
            //
            let backgroundPosition_0 = 'background-position: ';

            horOffset = -( die_0 - 1 ) * 62;
            verOffset = 0;

            backgroundPosition_0 += horOffset + "px " + verOffset + "px";   

            dojo.place( this.format_block( 'jstpl_die', {
                die_id: die_0_id,
                bkgPos: backgroundPosition_0
            } ) , 'stones' );


            //console.log( 'overall_player_board_'+player_id );

            let die_0_id_str = die_0_id.toString ( );

            this.placeOnObject( die_0_id_str, 'overall_player_board_'+player_id );
            this.slideToObject( die_0_id_str, 'dice_square_0' ).play(); 

            //
            //  DIE 1 
            //
            let backgroundPosition_1 = 'background-position: ';

            horOffset = -( die_1 - 1 ) * 62;
            verOffset = 0;

            backgroundPosition_1 += horOffset + "px " + verOffset + "px";   

            dojo.place( this.format_block( 'jstpl_die', {
                die_id: die_1_id,
                bkgPos: backgroundPosition_1
            } ) , 'stones' );


            let die_1_id_str = die_1_id.toString ( );

            this.placeOnObject( die_1_id_str, 'overall_player_board_'+player_id );
            this.slideToObject( die_1_id_str, 'dice_square_1' ).play();
        },


        //
        // ADD STONE ON BOARD
        //
        addStoneOnBoard: function( u, v, player_id, stone_id, N )
        {   

            //console.log( 'stone_id = ' + stone_id );
            //console.log( " " + u + " " + v + " " +  player_id + " " +  stone_id + " " + N );

            let backgroundPosition = 'background-position: ';

            if ( stone_id < 20000 )                         // BLACK STONE
            {
                if ( N == 4 )       // small board
                {
                    horOffset = 0;
                    verOffset = 0;
                }
                else if ( N == 5 )  // medium board
                {
                    horOffset = 0;
                    verOffset = 0;
                }
                else                // large board
                {
                    horOffset = 0;
                    verOffset = 0;
                }
               
                backgroundPosition += horOffset + "px " + verOffset + "px";   

                dojo.place( this.format_block( 'jstpl_black_stone', {
                    stone_id: stone_id,
                    bkgPos: backgroundPosition
                } ) , 'stones' );


                //console.log( 'stone_id = ' + stone_id );
                //console.log( 'horOffset = ' + horOffset );
                //console.log( 'verOffset = ' + verOffset );
            }
            else                                            // WHITE STONE
            {
                if ( N == 4 )       // small board
                {
                    horOffset = 0;
                    verOffset = 0;
                }
                else if ( N == 5 )  // medium board
                {
                    horOffset = 0;
                    verOffset = 0;
                }
                else                // large board
                {
                    horOffset = 0;
                    verOffset = 0;
                }
               
                backgroundPosition += horOffset + "px " + verOffset + "px";   

                dojo.place( this.format_block( 'jstpl_white_stone', {
                    stone_id: stone_id,
                    bkgPos: backgroundPosition
                } ) , 'stones' );

                //console.log( 'stone_id = ' + stone_id );
                //console.log( 'horOffset = ' + horOffset );
                //console.log( 'verOffset = ' + verOffset );
            }


            this.placeOnObject( stone_id, 'overall_player_board_'+player_id );
            this.slideToObject( stone_id, 'placement_cell_'+u+'_'+v ).play(); 
        },





        addOverlay ()
        {
            let backgroundPosition = 'background-position: 0px 0px';

            dojo.place( this.format_block( 'jstpl_billboard_content', {
                bkgPos: backgroundPosition
            } ) , 'overlay_billboard_1' );           
        },



        /*
        updateActivePlayerPanelAddedStones ()
        {
            console.log ('updateActivePlayerPanelAddedStones ()');

            active_player_id = this.gamedatas.active_player_id;

            //active_player_color = active_player_id.color;

            placed_stones_this_turn = this.gamedatas.placed_stones_this_turn;
            number_of_stones_to_place = this.gamedatas.number_of_stones_to_place;

            active_player_number_of_stones = this.gamedatas.numberOfStones[active_player_id];

            //console.log ('active_player_number_of_stones'+active_player_number_of_stones);
            //
            //  STRING SHOWING 
            //      NUMBER OF STONES OF COLOR 
            //      NUMBER ADDED / NUMBER TO ADD
            // 
            active_panel_str = '' + active_player_number_of_stones + '   Added ' + placed_stones_this_turn + '/' + number_of_stones_to_place;


            dojo.byId("number-of-stones_p"+active_player_id).innerHTML = active_panel_str;
        },
        */

        /*
        updateActivePlayerPanelRemovedStones ()
        {
            active_player_id = this.gamedatas.active_player_id;


            removed_stones_this_turn = this.gamedatas.removed_stones_this_turn;
            number_of_stones_to_remove = this.gamedatas.number_of_stones_to_remove;





        },
        */


        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName );
            
            switch( stateName )
            {            
                case 'placeStoneFirstTurn':

                    this.highlightSelectableCells ( args.args.selectableCells ); 

                    //this.updateActivePlayerPanelAddedStones ( );
    
                    break;

                case 'placeStone':

                    this.highlightSelectableCells ( args.args.selectableCells ); 
    
                    //this.updateActivePlayerPanelAddedStones ( );
    
                    break;

                case 'removeStone':
                
                    this.highlightRemovableStones ( args.args.removableStones ); 
                            
                    //this.updateActivePlayerPanelRemovedStones ( );
    
                    break;                                       
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );
            
            switch( stateName )
            {
            
            /* Example:
            
            case 'myGameState':
            
                // Hide the HTML block we are displaying only during this game state
                dojo.style( 'my_html_block_id', 'display', 'none' );
                
                break;
           */
           
           
            case 'dummmy':
                break;
            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName );
                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName )
                {
/*               
                 Example:
 
                 case 'myGameState':
                    
                    // Add 3 action buttons in the action status bar:
                    
                    this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' ); 
                    this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' ); 
                    this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' ); 
                    break;
*/
                }
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods

        //
        //
        // HIGHLIGHT SELECTABLE CELLS 
        //
        //
        highlightSelectableCells: function( selectableCells ) 
        {
            console.log( 'highlightSelectableCells' );

            //
            // Remove old selectable cells and add new selectable cells
            //
            dojo.query( '.selectableCell' ).removeClass( 'selectableCell' );

            // N = this.gamedatas.board_size;                                       ####  POSSIBLE BUG FIX  ####   Maybe caused freeze in hot seat mode.
            
            if (!this.isSpectator)
            {
                for( var x in selectableCells )
                {
                    for( var y in selectableCells [ x ] )
                    {        
                        //console.log( 'Adding selectableCell class' );

                        dojo.addClass( 'clickable_cell_'+x+'_'+y, 'selectableCell' );
                    }            
                }
            }
        },



        //
        //
        // HIGHLIGHT REMOVABLE STONES 
        //
        //
        highlightRemovableStones: function( removableStones ) 
        {
            console.log( 'highlightRemovableStones' );

            //
            // Remove old selectable cells and add new selectable cells
            //
            dojo.query( '.removableStone' ).removeClass( 'removableStone' );

            // N = this.gamedatas.board_size;                                       ####  POSSIBLE BUG FIX  ####   Maybe caused freeze in hot seat mode.
            
            if (!this.isSpectator)
            {
                for( var x in removableStones )
                {
                    for( var y in removableStones [ x ] )
                    {        
                        //console.log( 'Adding removableStone class' );

                        dojo.addClass( 'clickable_cell_'+x+'_'+y, 'removableStone' );
                    }            
                }
            }
        },



        ///////////////////////////////////////////////////
        //// Player's action
        
        
        onClickedCell: function( evt )
        {
            console.log('onClickedCell');  
            // Stop this event propagation
            evt.preventDefault();
            dojo.stopEvent( evt );
    
            // Get the clicked square u and v
            // Note: "clicked_square_u_v"
            var coords = evt.currentTarget.id.split('_');
            var u_clicked_cell = coords[2];               // Raw coordinates
            var v_clicked_cell = coords[3];               // (0,0) is lower left cell for both players






            if (    this.gamedatas.gamestate.name == "placeStoneFirstTurn" 
                 || this.gamedatas.gamestate.name == "placeStone"   )
            {
                if (    dojo.hasClass( 'clickable_cell_'+u_clicked_cell+'_'+v_clicked_cell, 'selectableCell' )    )    // SELECTABLE CELL                                 
                {      
                    if  (   this.checkAction( 'placeStone' ) )
                    {                
                        //console.log("placeStone ajax call");    

                        this.ajaxcall( "/herd/herd/placeStone.html", {   // Place stone
                            u:u_clicked_cell,
                            v:v_clicked_cell
                        }, this, function( result ) {} );
                   }
                } 
            }
            else if ( this.gamedatas.gamestate.name == "removeStone" )
            {
                if (   dojo.hasClass( 'clickable_cell_'+u_clicked_cell+'_'+v_clicked_cell, 'removableStone'   )  )    // REMOVE STONE                                 
                {            
                    if  (   this.checkAction( 'removeStone' ) )
                    {                
                        //console.log("removeStone ajax call");    

                        this.ajaxcall( "/herd/herd/removeStone.html", {   // Remove stone
                            u:u_clicked_cell,
                            v:v_clicked_cell
                        }, this, function( result ) {} );
                    }
                } 
            }
        },
     


        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your herd.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            dojo.subscribe( 'stoneRemoved', this, "notif_stoneRemoved" );

            dojo.subscribe( 'stonePlaced', this, "notif_stonePlaced" );

            dojo.subscribe( 'diceRemoved', this, "notif_diceRemoved" );

            dojo.subscribe( 'diceRolled', this, "notif_diceRolled" );

            dojo.subscribe( 'diceRolledHistory', this, "notif_diceRolledHistory" );

            dojo.subscribe( 'playerPanelNumberOfStones', this, "notif_playerPanelNumberOfStones" );

            dojo.subscribe( 'activePlayerPanelAddedStones', this, "notif_activePlayerPanelAddedStones" );

            dojo.subscribe( 'activePlayerPanelRemovedStones', this, "notif_activePlayerPanelRemovedStones" );

            dojo.subscribe( 'newScores', this, "notif_newScores" );

        },  
        
        // TODO: from this point and below, you can write your game notifications handling methods
        
        notif_stoneRemoved: function( notif )
        {
            //console.log('Removed stone id = ' + notif.args.removed_stone_id);

            dojo.query( '.removableStone' ).removeClass( 'removableStone' );        

            this.slideToObjectAndDestroy( ''+notif.args.removed_stone_id, 'overall_player_board_'+notif.args.player_id, 1000, 0 ); 
        },

        
        notif_stonePlaced: function( notif )
        {            
            // Remove current possible moves (makes the board more clear)
            dojo.query( '.selectableCell' ).removeClass( 'selectableCell' );        

            //console.log("addStoneOnBoard"+" "+notif.args.u+" "+notif.args.v+" "+notif.args.player_id+" "+notif.args.stone_id+" "+notif.args.N);
        
            this.addStoneOnBoard( notif.args.u, notif.args.v, notif.args.player_id, notif.args.stone_id, notif.args.N )
        },

        
        notif_diceRemoved: function( notif )
        {
            //console.log('' +  notif.args.removed_die_0_id + ' ' + notif.args.removed_die_0_id + ' ' + notif.args.player_id);

            this.slideToObjectAndDestroy( ''+notif.args.die_0_id, 'overall_player_board_'+notif.args.player_id, 1000, 0 ); 
            this.slideToObjectAndDestroy( ''+notif.args.die_1_id, 'overall_player_board_'+notif.args.player_id, 1000, 0 ); 
        },

        
        notif_diceRolled: function( notif )
        {
            //console.log(''+notif.args.die_0 + ' ' + notif.args.die_1 + ' ' + notif.args.die_0_id + ' ' + notif.args.die_1_id + ' ' + notif.args.player_id);

            this.showRolledDice( ''+notif.args.die_0, notif.args.die_1, notif.args.die_0_id, notif.args.die_1_id, notif.args.player_id  ); 
        },

        
        notif_diceRolledHistory: function ( notif )
        {
            //console.log("notif_diceRolledHistory");
        },


        notif_playerPanelNumberOfStones: function ( notif ) {
            var number_of_stones = notif.args.number_of_stones;
            var player_id = notif.args.player_id;
            dojo.byId("number-of-stones_p"+player_id).innerHTML = number_of_stones;
        },


        notif_activePlayerPanelAddedStones: function ( notif ) {

            console.log ('notif_activePlayerPanelAddedStones');

            active_player_id = notif.args.active_player_id;
            active_player_number_of_stones = notif.args.active_player_number_of_stones;
            placed_stones_this_turn = notif.args.placed_stones_this_turn;
            number_of_stones_to_place = notif.args.number_of_stones_to_place;


            //console.log ('active_player_number_of_stones'+active_player_number_of_stones);
            //
            //  STRING SHOWING 
            //      NUMBER OF STONES OF COLOR 
            //      NUMBER OF STONES ADDED / NUMBER OF STONES TO ADD
            // 
            active_panel_str = '' + active_player_number_of_stones + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;   Added ' 
                                  + placed_stones_this_turn + '/' + number_of_stones_to_place;

            console.log ('notif_activePlayerPanelAddedStones'+active_panel_str);

            dojo.byId("number-of-stones_p"+active_player_id).innerHTML = active_panel_str;
        },


        notif_activePlayerPanelRemovedStones: function ( notif ) {

            console.log ('notif_activePlayerPanelRemovedStones');

            active_player_id = notif.args.active_player_id;
            active_player_number_of_stones = notif.args.active_player_number_of_stones;
            removed_stones_this_turn = notif.args.removed_stones_this_turn;
            number_of_stones_to_remove = notif.args.number_of_stones_to_remove;


            //console.log ('active_player_number_of_stones'+active_player_number_of_stones);
            //
            //  STRING SHOWING 
            //      NUMBER OF STONES OF COLOR 
            //      NUMBER OF STONES REMOVED / NUMBER OF STONES TO REMOVE
            // 
            active_panel_str = '' + active_player_number_of_stones + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;   Removed ' 
                                  + removed_stones_this_turn + '/' + number_of_stones_to_remove;


            dojo.byId("number-of-stones_p"+active_player_id).innerHTML = active_panel_str;
        },


        notif_newScores: function( notif )
        {
            for( var player_id in notif.args.scores )
            {
                var newScore = notif.args.scores[ player_id ];
                this.scoreCtrl[ player_id ].toValue( newScore );
            }
        }

   });             
});
