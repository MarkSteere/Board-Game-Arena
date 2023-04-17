/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Impasse implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * impasse.js
 *
 * Impasse user interface script
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
    return declare("bgagame.impasse", ebg.core.gamegui, {
        constructor: function(){
            console.log('impasse constructor');
              
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
            // Display player panel data
            //
            var N = gamedatas.board_size;
            var total_checkers;

            if (  N == 8  )
                total_checkers = 12;
            else    // N = 10
                total_checkers = 20;

            for (var keyPlayerId in gamedatas.removedCheckers) 
            {
                //console.log( "removedCheckers" + gamedatas.removedCheckers[keyPlayerId] );

                remaining_checkers = total_checkers - gamedatas.removedCheckers[keyPlayerId];

                dojo.byId("remaining-checkers_p"+keyPlayerId).innerHTML = remaining_checkers;
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
            // Setting up player boards
            //   
            for( var i in gamedatas.board )
            {
                var square = gamedatas.board[i];

                if( square.player !== null )
                {
                    //console.log( " " + square.x + " " + square.y + " " +  square.player + " " +  square.checker_id + " " +  square.bullseye_id );

                    this.addStackOnBoard ( square.x, square.y, square.player, square.checker_id, square.bullseye_id );
                }            
            }



            //
            // LAST MOVE INDICATOR
            //
            /*
            if (this.prefs[103].value == 1)
            {
                last_move_x = this.gamedatas.last_move [ 0 ];

                last_move_y = this.gamedatas.last_move [ 1 ];

                last_move_id = this.gamedatas.last_move [ 2 ];

                this.addLastMoveIndicator ( last_move_x, last_move_y, last_move_id );
                   
                //console.log( "Show last move indicator" );
            }
            else {
                //console.log( "Don't show last move indicator" );
            }
            */


            //
            // ON CLICKED SQUARE 
            //            
            dojo.query( '.clickable_square_'+gamedatas.board_size ).connect( 'onclick', this, 'onClickedSquare' );        
            

 
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
        },
       


        //
        //  ADD STACK ON BOARD 
        //      SINGLETON 
        //      CROWN 
        //
        addStackOnBoard: function( x, y, player, checker_id, bullseye_id )
        {   
            //console.log( 'checker_id = ' + checker_id );

            N = this.gamedatas.board_size;

            if ( N == 8 )
            {
                //console.log( 'Board size = 8' );

                checkerHorOffset = 0;
                checkerVerOffset = 0; 

                bullseyeHorOffset = 0;
                bullseyeVerOffset = 0; 
            }
            else    // N == 10
            {
                //console.log( 'Board size = 10' );

                checkerHorOffset = 0;
                checkerVerOffset = 0; 

                bullseyeHorOffset = 0;
                bullseyeVerOffset = 0; 
            }



            let backgroundPosition = 'background-position: ';

            backgroundPosition += checkerHorOffset + "px " + checkerVerOffset + "px";

            
            if ( checker_id < 20000 )                                       // RED CHECKER
            {
                //console.log( 'checker_id = ' + checker_id );
                //console.log( 'checkerHorOffset = ' + checkerHorOffset );
                //console.log( 'checkerVerOffset = ' + checkerVerOffset );

                //console.log( 'bullseye_id = ' + bullseye_id );
                //console.log( 'bullseyeHorOffset = ' + bullseyeHorOffset );
                //console.log( 'bullseyeVerOffset = ' + bullseyeVerOffset );

                dojo.place( this.format_block( 'jstpl_red_checker', {
                    checker_id: checker_id,
                    bkgPos: backgroundPosition
                } ) , 'checkers' );        
            }
            else                                                            // YELLOW CHECKER
            {
                dojo.place( this.format_block( 'jstpl_yellow_checker', {
                    checker_id: checker_id,
                    bkgPos: backgroundPosition
                } ) , 'checkers' );
            }


           
            if ( bullseye_id != null )                                       // BULLSEYE
            {
                dojo.place( this.format_block( 'jstpl_bullseye', {
                    bullseye_id: bullseye_id,
                    bkgPos: backgroundPosition
                } ) , 'checkers' );        
            }


           
            if (this.isSpectator)
            {
                var x_oriented = x;
                var y_oriented = y;
            }
            else
            {
                var current_player_id = this.player_id;   
                var current_player = this.gamedatas.players [ current_player_id ];               
                var current_color = current_player.color;

                //console.log( 'current_player_id = ' + current_player_id );
                //console.log( 'current_color = ' + current_color );

                if ( current_color == "ff0000" )
                {
                    var x_oriented = x;
                    var y_oriented = y;
                }
                else
                {
                    var x_oriented = N - x - 1;
                    var y_oriented = N - y - 1;
                }
            }
            

            this.placeOnObject( checker_id, 'overall_player_board_'+player );
            this.slideToObject( checker_id, 'placement_square_'+x_oriented+'_'+y_oriented ).play(); 

   
            if ( bullseye_id != null )                                       
            {
                this.placeOnObject( bullseye_id, 'overall_player_board_'+player );
                this.slideToObject( bullseye_id, 'placement_square_'+x_oriented+'_'+y_oriented ).play(); 
            }


        },






        //
        //  ADD CROWN ON BOARD 
        //
        addCrownOnBoard: function( x, y, player, bullseye_id )
        {   
            //console.log( 'bullseye_id = ' + bullseye_id );

            N = this.gamedatas.board_size;

            if ( N == 8 )
            {
                //console.log( 'Board size = 8' );

                bullseyeHorOffset = 0;
                bullseyeVerOffset = 0; 
            }
            else    // N == 10
            {
                //console.log( 'Board size = 10' );

                bullseyeHorOffset = 0;
                bullseyeVerOffset = 0; 
            }



            let backgroundPosition = 'background-position: ';

            backgroundPosition += bullseyeHorOffset + "px " + bullseyeVerOffset + "px";

            
            dojo.place( this.format_block( 'jstpl_bullseye', {
                bullseye_id: bullseye_id,
                bkgPos: backgroundPosition
            } ) , 'checkers' );        


           
            if (this.isSpectator)
            {
                var x_oriented = x;
                var y_oriented = y;
            }
            else
            {
                var current_player_id = this.player_id;   
                var current_player = this.gamedatas.players [ current_player_id ];               
                var current_color = current_player.color;

                //console.log( 'current_player_id = ' + current_player_id );
                //console.log( 'current_color = ' + current_color );

                if ( current_color == "ff0000" )
                {
                    var x_oriented = x;
                    var y_oriented = y;
                }
                else
                {
                    var x_oriented = N - x - 1;
                    var y_oriented = N - y - 1;
                }
            }
            
            this.placeOnObject( bullseye_id, 'overall_player_board_'+player );
            this.slideToObject( bullseye_id, 'placement_square_'+x_oriented+'_'+y_oriented ).play(); 

        },







        addOverlay ()
        {
            let backgroundPosition = 'background-position: 0px 0px';


            if (this.isSpectator)
            {
                dojo.place( this.format_block( 'jstpl_billboard_content', {
                    bkgPos: backgroundPosition
                } ) , 'overlay_billboard_1' );           
            }
            else
            {
                var current_player_id = this.player_id;   
                var current_player = this.gamedatas.players [ current_player_id ];               
                var current_color = current_player.color;

                //console.log( 'Player color' + current_color);

                if ( current_color == "ff0000" )
                {
                    dojo.place( this.format_block( 'jstpl_billboard_content', {
                        bkgPos: backgroundPosition
                    } ) , 'overlay_billboard_1' );
                }
                else if ( current_color == "ffff00" )
                {
                    dojo.place( this.format_block( 'jstpl_reverse_billboard_content', {
                        bkgPos: backgroundPosition
                    } ) , 'overlay_billboard_1' );
                }
            }
        },

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
                case 'selectOrigin':

                    this.highlightSelectableOrigins ( args.args.selectableOrigins ); 
    
                    break;

                case 'selectDestination':
                
                    this.highlightSelectedOrigin ();
                
                    this.highlightSelectableDestinations ( args.args.selectableDestinations );
        
                    break;
                   
                case 'selectCrown':
                
                    this.highlightSelectableCrowns ( args.args.selectableCrowns );
                        
                    break;
                   
                case 'removeImpasseChecker':
                
                    this.highlightRemovableImpasseCheckers ( args.args.removableImpasseCheckers );
                        
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
        // HIGHLIGHT SELECTABLE ORIGINS 
        //
        //
        highlightSelectableOrigins: function( selectableOrigins ) 
        {
            console.log( 'highlightSelectableOrigins' );


            //
            // Remove old selectable origins and add new selectable origins
            //
            dojo.query( '.selectableOrigin' ).removeClass( 'selectableOrigin' );

            N = this.gamedatas.board_size;
            
            if (!this.isSpectator)
            {
                var current_player_id = this.player_id;   
                var current_player = this.gamedatas.players [ current_player_id ];               
                var current_color = current_player.color;

        
                for( var x in selectableOrigins )
                {
                    for( var y in selectableOrigins [ x ] )
                    {
                        if ( current_color == "ff0000" )
                        {
                            var x_oriented = x;
                            var y_oriented = y;
                        }
                        else
                        {
                            var x_oriented = N - x - 1;
                            var y_oriented = N - y - 1;
                        }
        
                        console.log( 'Adding selectableOrigin class' );

                        dojo.addClass( 'clickable_square_'+x_oriented+'_'+y_oriented, 'selectableOrigin' );
                    }            
                }
            }
        },


        highlightSelectedOrigin: function ()
        {
            //console.log( 'highlightSelectedOrigin' );

            
            if ( ! this.isSpectator )
            {
                var current_player_id = this.player_id;   
                var current_player = this.gamedatas.players [ current_player_id ];               
                var current_color = current_player.color;


                N = this.gamedatas.board_size;
            

                for( var i in this.gamedatas.board )
                {
                    var square = this.gamedatas.board[i];

                    if( square.is_origin_selected == 1 )
                    {
                        console.log( 'square.is_origin_selected == 1');

                        var x = square.x;
                        var y = square.y;

                        if ( current_color == "ff0000" )
                        {
                            var x_oriented = x;
                            var y_oriented = y;
                        }
                        else
                        {
                            var x_oriented = N - x - 1;
                            var y_oriented = N - y - 1;
                        }

                        dojo.addClass( 'clickable_square_'+x_oriented+'_'+y_oriented, 'selectedOrigin' );

                    }            
                }
            }

            //return;
        },


        //
        //
        // HIGHLIGHT SELECTABLE DESTINATIONS  
        //
        //
        highlightSelectableDestinations: function( selectableDestinations ) 
        {
            console.log( 'highlightSelectableDestinations' );

            N = this.gamedatas.board_size;
            
            if (!this.isSpectator)
            {
                var current_player_id = this.player_id;   
                var current_player = this.gamedatas.players [ current_player_id ];               
                var current_color = current_player.color;

        
                for( var x in selectableDestinations )
                {
                    for( var y in selectableDestinations [ x ] )
                    {
                        if ( current_color == "ff0000" )
                        {
                            var x_oriented = x;
                            var y_oriented = y;
                        }
                        else
                        {
                            var x_oriented = N - x - 1;
                            var y_oriented = N - y - 1;
                        }
        
                        console.log( 'Adding selectableDestination class' );

                        dojo.addClass( 'clickable_square_'+x_oriented+'_'+y_oriented, 'selectableDestination' );
                    }            
                }
            }
        },



        //
        //
        // HIGHLIGHT SELECTABLE CROWNS  
        //
        //
        highlightSelectableCrowns: function( selectableCrowns ) 
        {
            console.log( 'highlightSelectableCrowns' );

            N = this.gamedatas.board_size;
            
            if (!this.isSpectator)
            {
                var current_player_id = this.player_id;   
                var current_player = this.gamedatas.players [ current_player_id ];               
                var current_color = current_player.color;

        
                for( var x in selectableCrowns )
                {
                    for( var y in selectableCrowns [ x ] )
                    {
                        if ( current_color == "ff0000" )
                        {
                            var x_oriented = x;
                            var y_oriented = y;
                        }
                        else
                        {
                            var x_oriented = N - x - 1;
                            var y_oriented = N - y - 1;
                        }
        
                        console.log( 'Adding selectableCrown class' );

                        dojo.addClass( 'clickable_square_'+x_oriented+'_'+y_oriented, 'selectableCrown' );
                    }            
                }
            }
        },



        //
        //
        // HIGHLIGHT REMOVABLE IMPASSE CHECKERS  
        //
        //
        highlightRemovableImpasseCheckers: function( removableImpasseCheckers ) 
        {
            //
            // Remove old selectable origins and add new selectable origins
            //
            dojo.query( '.selectableOrigin' ).removeClass( 'selectableOrigin' );

            console.log( 'highlightRemovableImpasseCheckers' );

            N = this.gamedatas.board_size;
            
            if (!this.isSpectator)
            {
                var current_player_id = this.player_id;   
                var current_player = this.gamedatas.players [ current_player_id ];               
                var current_color = current_player.color;

        
                for( var x in removableImpasseCheckers )
                {
                    for( var y in removableImpasseCheckers [ x ] )
                    {
                        if ( current_color == "ff0000" )
                        {
                            var x_oriented = x;
                            var y_oriented = y;
                        }
                        else
                        {
                            var x_oriented = N - x - 1;
                            var y_oriented = N - y - 1;
                        }
        
                        console.log( 'Adding removableImpasseChecker class' );

                        dojo.addClass( 'clickable_square_'+x_oriented+'_'+y_oriented, 'removableImpasseChecker' );
                    }            
                }
            }
        },



        ///////////////////////////////////////////////////
        //// Player's action
        
        onClickedSquare: function( evt )
        {
            console.log("onClickedSquare");   
                    
            // Stop this event propagation
            evt.preventDefault();
            dojo.stopEvent( evt );
    
            // Get the clicked square x and y
            // Note: square id format is "square_X_Y"
            var coords = evt.currentTarget.id.split('_');
            var x_clicked_square = coords[2];               // Raw coordinates
            var y_clicked_square = coords[3];               // (0,0) is lower left square for both players


            N = this.gamedatas.board_size;


            var current_player_id = this.player_id;   
            var current_player = this.gamedatas.players [ current_player_id ];               
            var current_color = current_player.color;


            if ( current_color == "ff0000" )
            {
                var x_oriented = x_clicked_square;
                var y_oriented = y_clicked_square;
            }
            else
            {
                var x_oriented = N - x_clicked_square - 1;
                var y_oriented = N - y_clicked_square - 1;
            }


            if (   dojo.hasClass( 'clickable_square_'+x_clicked_square+'_'+y_clicked_square, 'selectableOrigin'   )  )      // Select origin                                 
            {            
                if  (    this.checkAction( 'selectOrigin' )    )
                {                
                    //console.log("checkAction selectOrigin ok");    

                    this.ajaxcall( "/impasse/impasse/selectOrigin.html", {   // SELECT ORIGIN
                        x:x_oriented,
                        y:y_oriented
                    }, this, function( result ) {} );
                }
            } 
            

            else if (     (  this.gamedatas.gamestate.name == "selectDestination"  )
                      &&  (  ! dojo.hasClass('clickable_square_'+x_clicked_square+'_'+y_clicked_square, 'selectableDestination')  )    )    // UNSELECT CHECKER                           
            {    
                if (   this.checkAction( 'unselectOrigin' ) )
                {        
                        //console.log("checkAction unselectOrigin ok - unselecting origin");    
                   
                        this.ajaxcall( "/impasse/impasse/unselectOrigin.html", {     // UNSELECT ORIGIN
                    }, this, function( result ) {} );
                }
            } 


            else if (   dojo.hasClass( 'clickable_square_'+x_clicked_square+'_'+y_clicked_square, 'selectableDestination' )   )           // SELECT DESTINATION
            {  

                //console.log("This square is a possible destination.");  

                if (   this.checkAction ( 'selectDestination' )   )
                {       
                    //console.log("checkAction selectDestination ok - selecting destination.");  
                    
                    this.ajaxcall( "/impasse/impasse/selectDestination.html", {     
                        x:x_oriented,
                        y:y_oriented
                    }, this, function( result ) {} );
                }
            } 


            else if (   dojo.hasClass( 'clickable_square_'+x_clicked_square+'_'+y_clicked_square, 'selectableCrown' )   )           // SELECT CROWN
            {  

                //console.log("This square is a possible crown.");  

                if (   this.checkAction ( 'selectCrown' )   )
                {       
                    //console.log("checkAction selectCrown ok - selecting crown ");  
                    
                    this.ajaxcall( "/impasse/impasse/selectCrown.html", {     
                        x:x_oriented,
                        y:y_oriented
                    }, this, function( result ) {} );
                }
            } 


            else if (   dojo.hasClass( 'clickable_square_'+x_clicked_square+'_'+y_clicked_square, 'removableImpasseChecker' )   )           // SELECT CROWN
            {  

                //console.log("Remove impasse checker.");  

                if (   this.checkAction ( 'removeImpasseChecker' )   )
                {       
                    //console.log("checkAction removeImpasseChecker ok");  
                    
                    this.ajaxcall( "/impasse/impasse/removeImpasseChecker.html", {     
                        x:x_oriented,
                        y:y_oriented
                    }, this, function( result ) {} );
                }
            } 


        },
        


        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your impasse.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            dojo.subscribe( 'originSelected', this, "notif_originSelected" );
            
            dojo.subscribe( 'originUnselected', this, "notif_originUnselected" );

            dojo.subscribe( 'slidSingleton', this, "notif_slidSingleton" );

            dojo.subscribe( 'slidCrown', this, "notif_slidCrown" );

            dojo.subscribe( 'slidDouble', this, "notif_slidDouble" );

            dojo.subscribe( 'boreOffCrown', this, "notif_boreOffCrown" );

            dojo.subscribe( 'transpose_boreOffCrown', this, "notif_transpose_boreOffCrown" );

            dojo.subscribe( 'boreOffCrown_addedCrown', this, "notif_boreOffCrown_addedCrown" );

            dojo.subscribe( 'uncrownedCrowned', this, "notif_uncrownedCrowned" );

            dojo.subscribe( 'impasseSingletonRemoved', this, "notif_impasseSingletonRemoved" );

            dojo.subscribe( 'removedImpasseCrown', this, "notif_removedImpasseCrown" );

            dojo.subscribe( 'removedImpasseCrown_addedCrown', this, "notif_removedImpasseCrown_addedCrown" );

            dojo.subscribe( 'playerPanel', this, "notif_playerPanel" );

            dojo.subscribe( 'playerHistory', this, "notif_playerHistory" );

            dojo.subscribe( 'backendMessage', this, "notif_backendMessage" );

            dojo.subscribe( 'newScores', this, "notif_newScores" );

        },  
        
        // TODO: from this point and below, you can write your game notifications handling methods
        
        notif_originSelected: function( notif )
        {            
            if ( ! this.isSpectator )
            {
                var current_player_id = this.player_id;   
                var current_player = this.gamedatas.players [ current_player_id ];               
                var current_color = current_player.color;

                
                N = this.gamedatas.board_size;
            

                if ( current_color == "ff0000" )
                {
                    var x_oriented = notif.args.x;
                    var y_oriented = notif.args.y;
                }
                else
                {
                    var x_oriented = N - notif.args.x - 1;
                    var y_oriented = N - notif.args.y - 1;
                }


                //console.log("notif_originSelected"+" "+x+" "+y);


                dojo.query( '.selectableOrigin' ).removeClass( 'selectableOrigin' );   
            
                dojo.addClass( 'clickable_square_'+x_oriented+'_'+y_oriented, 'selectedOrigin' );

            }
       
        },

        
        notif_originUnselected: function( notif )
        {
            console.log("notif_originUnselected");

            dojo.query( '.selectedOrigin' ).removeClass( 'selectedOrigin' );      
            dojo.query( '.selectableDestination' ).removeClass( 'selectableDestination' );      
        },
        

        notif_slidSingleton: function( notif )
        {
            console.log("notif_slidSingleton");

            dojo.query( '.selectedOrigin' ).removeClass( 'selectedOrigin' );      
            dojo.query( '.selectableDestination' ).removeClass( 'selectableDestination' );      


            N = this.gamedatas.board_size;

            
            if (this.isSpectator)
            {
                var current_color = "ff0000";
            }
            else
            {
                var current_player_id = this.player_id;   
                var current_player = this.gamedatas.players [ current_player_id ];               
                var current_color = current_player.color;
            }


            if ( current_color == "ff0000" )
            {
                var destination_x_oriented = notif.args.destination_x;
                var destination_y_oriented = notif.args.destination_y;
            }
            else
            {
                var destination_x_oriented = N - notif.args.destination_x - 1;
                var destination_y_oriented = N - notif.args.destination_y - 1;
            }
            
            //console.log("destination_x_oriented, destination_y_oriented"+" "+destination_x_oriented+" "+destination_y_oriented);

            this.slideToObject( ''+notif.args.slid_singleton_id, 'placement_square_'+destination_x_oriented+'_'+destination_y_oriented ).play();             
        },



        notif_slidCrown: function( notif )
        {
            console.log("notif_slidCrown");

            dojo.query( '.selectedOrigin' ).removeClass( 'selectedOrigin' );      
            dojo.query( '.selectableDestination' ).removeClass( 'selectableDestination' );      


            N = this.gamedatas.board_size;

            
            if (this.isSpectator)
            {
                var current_color = "ff0000";
            }
            else
            {
                var current_player_id = this.player_id;   
                var current_player = this.gamedatas.players [ current_player_id ];               
                var current_color = current_player.color;
            }


            if ( current_color == "ff0000" )
            {
                var destination_x_oriented = notif.args.destination_x;
                var destination_y_oriented = notif.args.destination_y;
            }
            else
            {
                var destination_x_oriented = N - notif.args.destination_x - 1;
                var destination_y_oriented = N - notif.args.destination_y - 1;
            }
            
            //console.log("destination_x_oriented, destination_y_oriented"+" "+destination_x_oriented+" "+destination_y_oriented);

            this.slideToObject( ''+notif.args.slid_crown_id, 'placement_square_'+destination_x_oriented+'_'+destination_y_oriented ).play();             
        },



        notif_slidDouble: function( notif )
        {
            console.log("notif_slidDouble");

            dojo.query( '.selectedOrigin' ).removeClass( 'selectedOrigin' );      
            dojo.query( '.selectableDestination' ).removeClass( 'selectableDestination' );      


            N = this.gamedatas.board_size;

            
            if (this.isSpectator)
            {
                var current_color = "ff0000";
            }
            else
            {
                var current_player_id = this.player_id;   
                var current_player = this.gamedatas.players [ current_player_id ];               
                var current_color = current_player.color;
            }


            if ( current_color == "ff0000" )
            {
                var destination_x_oriented = notif.args.destination_x;
                var destination_y_oriented = notif.args.destination_y;
            }
            else
            {
                var destination_x_oriented = N - notif.args.destination_x - 1;
                var destination_y_oriented = N - notif.args.destination_y - 1;
            }
            
            //console.log("destination_x_oriented, destination_y_oriented"+" "+destination_x_oriented+" "+destination_y_oriented);

            this.slideToObject( ''+notif.args.slid_base_checker_id, 'placement_square_'+destination_x_oriented+'_'+destination_y_oriented ).play();             

            this.slideToObject( ''+notif.args.slid_crown_id, 'placement_square_'+destination_x_oriented+'_'+destination_y_oriented ).play();             
        },



        notif_boreOffCrown: function( notif )
        {
            //console.log("notif_boreOffCrown");

            dojo.query( '.selectedOrigin' ).removeClass( 'selectedOrigin' );      
            dojo.query( '.selectableDestination' ).removeClass( 'selectableDestination' );     
            
            N = this.gamedatas.board_size;

            
            if (this.isSpectator)
            {
                var current_color = "ff0000";
            }
            else
            {
                var current_player_id = this.player_id;   
                var current_player = this.gamedatas.players [ current_player_id ];               
                var current_color = current_player.color;
            }


            if ( current_color == "ff0000" )
            {
                var destination_x_oriented = notif.args.destination_x;
                var destination_y_oriented = notif.args.destination_y;
            }
            else
            {
                var destination_x_oriented = N - notif.args.destination_x - 1;
                var destination_y_oriented = N - notif.args.destination_y - 1;
            }


            //console.log ( "bore_off_crown_id = " + bore_off_crown_id );

            this.slideToObject( ''+notif.args.origin_base_checker_id, 'placement_square_'+destination_x_oriented+'_'+destination_y_oriented ).play();    
            
            this.slideToObjectAndDestroy( ''+notif.args.origin_crown_id, 'overall_player_board_'+notif.args.player_id, 1000, 0 ); 
        },



        notif_transpose_boreOffCrown: function( notif )
        {
            //console.log("notif_boreOffCrown");

            dojo.query( '.selectedOrigin' ).removeClass( 'selectedOrigin' );      
            dojo.query( '.selectableDestination' ).removeClass( 'selectableDestination' );     
            

            bore_off_crown_id = notif.args.bore_off_crown_id;

            //console.log ( "bore_off_crown_id = " + bore_off_crown_id );

            this.slideToObjectAndDestroy( ''+bore_off_crown_id, 'overall_player_board_'+notif.args.player_id, 1000, 0 ); 
        },



        //
        // SLIDE BORNE OFF BULLSEYE TO UNCROWNED SINGLETON 
        //      
        notif_boreOffCrown_addedCrown: function( notif )
        {            
            dojo.query( '.selectedOrigin' ).removeClass( 'selectedOrigin' );      
            dojo.query( '.selectableDestination' ).removeClass( 'selectableDestination' );     
            
            N = this.gamedatas.board_size;
            

            if (this.isSpectator)
            {
                var current_color = "ff0000";
            }
            else
            {
                var current_player_id = this.player_id;   
                var current_player = this.gamedatas.players [ current_player_id ];               
                var current_color = current_player.color;
            }


            if ( current_color == "ff0000" )
            {
                var destination_x_oriented = notif.args.destination_x;
                var destination_y_oriented = notif.args.destination_y;
                
                var uncrowned_x_oriented = notif.args.uncrowned_x;
                var uncrowned_y_oriented = notif.args.uncrowned_y;
            }
            else
            {
                var destination_x_oriented = N - notif.args.destination_x - 1;
                var destination_y_oriented = N - notif.args.destination_y - 1;
                
                var uncrowned_x_oriented = N - notif.args.uncrowned_x - 1;
                var uncrowned_y_oriented = N - notif.args.uncrowned_y - 1;
            }

            //console.log("uncrowned_x, uncrowned_y"+" "+notif.args.uncrowned_x+" "+notif.args.uncrowned_y);

            this.slideToObjectAndDestroy( ''+notif.args.origin_base_checker_id, 'overall_player_board_'+notif.args.player_id, 1000, 0 ); 

            this.slideToObject( ''+notif.args.origin_crown_id, 'placement_square_'+uncrowned_x_oriented+'_'+uncrowned_y_oriented ).play();             
        },




        //
        //  UNCROWNED CROWNED
        //
        //  REMOVED SELECTED CROWN AND ADD CROWN TO UNCROWNED SINGLETON 
        //
        //      SLIDE OFF AND DESTROY SELECTED CROWN 
        //      ADD BULLSEYE TO UNCROWNED CROWN
        //      
        notif_uncrownedCrowned: function( notif )
        {            
            dojo.query( '.selectableCrown' ).removeClass( 'selectableCrown' );      


            N = this.gamedatas.board_size;
            
            bullseye_id = notif.args.bullseye_id;


            if (this.isSpectator)
            {
                var current_color = "ff0000";
            }
            else
            {
                var current_player_id = this.player_id;   
                var current_player = this.gamedatas.players [ current_player_id ];               
                var current_color = current_player.color;
            }


            if ( current_color == "ff0000" )
            {
                var crowned_x_oriented = notif.args.crowned_x;
                var crowned_y_oriented = notif.args.crowned_y;
            }
            else
            {
                var crowned_x_oriented = N - notif.args.crowned_x - 1;
                var crowned_y_oriented = N - notif.args.crowned_y - 1;
            }

            //console.log("crowned_x, crowned_y"+" "+notif.args.crowned_x+" "+notif.args.crowned_y);

            //
            //  REMOVE SELECTED CROWN 
            //
            this.slideToObjectAndDestroy( ''+notif.args.selected_crown_id, 'overall_player_board_'+notif.args.player_id, 1000, 0 ); 


            //
            //  ADD BULLSEYE ONTO UNCROWNED CROWNED SINGLETON 
            //
            //console.log( 'bullseye_id = ' + bullseye_id );

            if ( N == 8 )
            {
                //console.log( 'Board size = 8' );

                bullseyeHorOffset = 0;
                bullseyeVerOffset = 0; 
            }
            else    // N == 10
            {
                //console.log( 'Board size = 10' );

                bullseyeHorOffset = 0;
                bullseyeVerOffset = 0; 
            }


            let backgroundPosition = 'background-position: ';

            backgroundPosition += bullseyeHorOffset + "px " + bullseyeVerOffset + "px";

            
            dojo.place( this.format_block( 'jstpl_bullseye', {
                bullseye_id: bullseye_id,
                bkgPos: backgroundPosition
            } ) , 'checkers' );        

                      
            this.placeOnObject( bullseye_id, 'overall_player_board_'+notif.args.player_id );
            this.slideToObject( bullseye_id, 'placement_square_'+crowned_x_oriented+'_'+crowned_y_oriented ).play(); 

        },


        notif_impasseSingletonRemoved: function( notif )
        {
            //console.log("notif_impasseSingletonRemoved");

            dojo.query( '.removableImpasseChecker' ).removeClass( 'removableImpasseChecker' );      

            //console.log ( "removed_impasse_singleton_id = " + notif.args.removed_impasse_singleton_id );

            this.slideToObjectAndDestroy( ''+notif.args.removed_impasse_singleton_id, 'overall_player_board_'+notif.args.player_id, 1000, 0 ); 
        },


        notif_removedImpasseCrown: function( notif )
        {
            //console.log("notif_removedImpasseCrown");

            dojo.query( '.removableImpasseChecker' ).removeClass( 'removableImpasseChecker' );                  

            removed_impasse_crown_id = notif.args.removed_impasse_crown_id;

            //console.log ( "removed_impasse_crown_id = " + removed_impasse_crown_id );

            this.slideToObjectAndDestroy( ''+removed_impasse_crown_id, 'overall_player_board_'+notif.args.player_id, 1000, 0 ); 
        },



        //
        //  REMOVED IMPASSE CROWN AND USED REMAINING SINGLETON TO CROWN UNCROWNED CHECKER 
        //
        //      SLIDE BULLSEYE FROM REMOVED IMPASSE SQUARE TO UNCROWNED CHECKER SQUARE 
        //      SLIDE OFF AND DESTROY BASE CHECKER FROM IMPASSE SQUARE
        //      
        notif_removedImpasseCrown_addedCrown: function( notif )
        {            
            dojo.query( '.removableImpasseChecker' ).removeClass( 'removableImpasseChecker' );      

            N = this.gamedatas.board_size;
            

            if (this.isSpectator)
            {
                var current_color = "ff0000";
            }
            else
            {
                var current_player_id = this.player_id;   
                var current_player = this.gamedatas.players [ current_player_id ];               
                var current_color = current_player.color;
            }


            if ( current_color == "ff0000" )
            {
                var uncrowned_x_oriented = notif.args.uncrowned_x;
                var uncrowned_y_oriented = notif.args.uncrowned_y;
            }
            else
            {
                var uncrowned_x_oriented = N - notif.args.uncrowned_x - 1;
                var uncrowned_y_oriented = N - notif.args.uncrowned_y - 1;
            }

            //console.log("uncrowned_x, uncrowned_y"+" "+notif.args.uncrowned_x+" "+notif.args.uncrowned_y);

            this.slideToObject( ''+notif.args.crown_id, 'placement_square_'+uncrowned_x_oriented+'_'+uncrowned_y_oriented ).play();             

            this.slideToObjectAndDestroy( ''+notif.args.removed_base_checker_id, 'overall_player_board_'+notif.args.player_id, 1000, 0 ); 

        },


        // 
        //  DISPLAY REMAINING NUMBER OF CHECKERS 
        //
        notif_playerPanel: function ( notif ) {

            //console.log("Update player panel");

            var N = this.gamedatas.board_size;
            var total_checkers;

            if (  N == 8  )
                total_checkers = 12;
            else    // N = 10
                total_checkers = 20;

            var remaining_checkers = total_checkers - notif.args.removed_checkers;

            var player_id = notif.args.player_id;
            dojo.byId("remaining-checkers_p"+player_id).innerHTML = remaining_checkers;
        },


        // 
        //  DISPLAY REMAINING NUMBER OF CHECKERS 
        //
        notif_playerHistory: function ( notif ) {

            //console.log("notif_playerHistory");

        },


        notif_backendMessage: function( notif )
        {
            //console.log("Inside notif_backendMessage");
        },


        notif_newScores: function( notif )
        {
            for( var player_id in notif.args.scores )
            {
                var newScore = notif.args.scores[ player_id ];
                this.scoreCtrl[ player_id ].toValue( newScore );
            }
        },

   });             
});
