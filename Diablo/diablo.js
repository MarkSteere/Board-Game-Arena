/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Diablo implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * diablo.js
 *
 * Diablo user interface script
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
    return declare("bgagame.diablo", ebg.core.gamegui, {
        constructor: function(){
            console.log('diablo constructor');
              
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
                // PLAYER PANEL
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
            // DISPLAY PLAYER PANEL DATA
            //
            for (var keyPlayerId in gamedatas.numberOfCheckers) 
            {
                //console.log( "numberOfCheckers" + gamedatas.numberOfCheckers[keyPlayerId] );

                number_of_checkers = gamedatas.numberOfCheckers[keyPlayerId];

                dojo.byId("remaining-checkers_p"+keyPlayerId).innerHTML = number_of_checkers;
            }

            
            //
            //  COORDINATES OVERLAY
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
            // ADD CHECKERS ON BOARD
            //   
            for( var i in gamedatas.board )
            {
                var square = gamedatas.board[i];

                if( square.player !== null )
                {
                    //console.log( " " + square.x + " " + square.y + " " +  square.player + " " +  square.checker_id + " " +  gamedatas.board_size );

                    this.addStackOnBoard ( square.x, square.y, square.player, square.checker_id, gamedatas.board_size );
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
            // LAST MOVE INDICATOR
            //
            if (this.prefs[103].value == 1)
            {
                last_move_x = this.gamedatas.last_move [ 0 ];

                last_move_y = this.gamedatas.last_move [ 1 ];

                last_move_id = this.gamedatas.last_move [ 2 ];


                number_of_moves = this.gamedatas.number_of_moves;


                this.addLastMoveIndicator ( last_move_x, last_move_y, last_move_id, number_of_moves );
                   
                //console.log( "Show last move indicator" );
            }
            else {
                //console.log( "Don't show last move indicator" );
            }



            //
            // ON CLICKED SQUARE 
            //            
            dojo.query( '.clickable_square_'+gamedatas.board_size ).connect( 'onclick', this, 'onClickedSquare' );        
            


            
 
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
            } ) , 'checkers' );


            //console.log( 'overall_player_board_'+player_id );

            let die_0_id_str = die_0_id.toString ( );

            this.placeOnObject( die_0_id_str, 'overall_player_board_'+player_id );
            this.slideToObject( die_0_id_str, 'dice_square_0', 500, 0 ).play(); 

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
            } ) , 'checkers' );


            let die_1_id_str = die_1_id.toString ( );

            this.placeOnObject( die_1_id_str, 'overall_player_board_'+player_id );
            this.slideToObject( die_1_id_str, 'dice_square_1', 500, 0 ).play();
        },


        //
        //  ADD STACK ON BOARD 
        //
        addStackOnBoard: function( x, y, player, checker_id, N )
        {   
            //console.log( 'checker_id = ' + checker_id );

            stack_value = this.getStackValue ( checker_id );
            //console.log( '' + x + ' ' + y + ' ' + stack_value );

            var horOffset;
            var verOffset;

            switch ( N )
            {
                case '6':

                    horOffset = -1 - (    ( stack_value - 1 ) * 110    );

                    verOffset = -1;

                    break;

                case '8':

                    horOffset = -1 - (    ( stack_value - 1 ) * 80    );

                    verOffset = -1;

                    break;

                case '10':

                    horOffset = -1 - (    ( stack_value - 1 ) * 64    );

                    verOffset = -1;

                    break;
            }

            //console.log( 'N = ' + N );
            //console.log( '' + N + ' ' + horOffset );

            let backgroundPosition = 'background-position: ';

            backgroundPosition += horOffset + "px " + verOffset + "px";   


            if ( checker_id < 200000 )                                       // BLACK STACK
            {
                //console.log( 'checker_id = ' + checker_id );
                //console.log( 'horOffset = ' + horOffset );
                //console.log( 'verOffset = ' + verOffset );

                dojo.place( this.format_block( 'jstpl_black_checker', {
                    checker_id: checker_id,
                    bkgPos: backgroundPosition
                } ) , 'checkers' );        
            }
            else                                                            // YELLOW STACK
            {
                dojo.place( this.format_block( 'jstpl_yellow_checker', {
                    checker_id: checker_id,
                    bkgPos: backgroundPosition
                } ) , 'checkers' );
            }


            checker_id = String(checker_id); 


            this.placeOnObject( checker_id, 'overall_player_board_'+player );
            this.slideToObject( checker_id, 'placement_square_'+x+'_'+y, 500, 0 ).play();
        },




        addLastMoveIndicator: function( last_move_indicator_x, last_move_indicator_y, last_move_indicator_id, number_of_moves )
        //addLastMoveIndicator: function( last_move_x, last_move_y, last_move_id )
        {  
            //console.log( 'addLastMoveIndicator: function(  )' );

            if ( number_of_moves == 0 ) // NO STACKS MOVED YET
            {
                //console.log( 'number_of_moves == 0' );

                return;
            }


            //
            //  REMOVE PREVIOUS LAST MOVE INDICATOR
            //
            //dojo.query( '.last_move_indicator' ).removeClass( 'last_move_indicator' );


            let backgroundPositionLMI = 'background-position: 0px 0px';   

            let last_move_indicator_id_str = last_move_indicator_id.toString ( );

            //console.log( 'last_move_indicator_x, last_move_indicator_y, last_move_id_str: ' 
            //                + last_move_indicator_x + ', ' + last_move_indicator_y + ', ' + last_move_indicator_id_str );
              
            dojo.place( this.format_block( 'jstpl_last_move_indicator', {
                last_move_indicator_id: last_move_indicator_id_str,
                bkgPos: backgroundPositionLMI
            } ) , 'checkers' );

            this.placeOnObject( last_move_indicator_id_str, 'placement_square_'+last_move_indicator_x+'_'+last_move_indicator_y );      
        },




        getStackValue: function( checker_id )
        {
            return Math.floor (    ( checker_id % 100000 ) / 1000    );
        },



        addOverlay ()
        {
            let backgroundPosition = 'background-position: 0px 0px';

            dojo.place( this.format_block( 'jstpl_billboard_content', {
                bkgPos: backgroundPosition
            } ) , 'overlay_billboard_1' );           
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
            
                case 'selectOriginFirstTurn':

                    this.highlightSelectableOriginSquares ( args.args.selectableOriginsFirstTurn ); 

                    break;

                case 'selectOriginMoveA':
                
                    this.highlightSelectableOriginSquares ( args.args.selectableOriginsMoveA ); 
                                
                    break;                                       

                case 'selectOriginMoveB':
                
                    this.highlightSelectableOriginSquares ( args.args.selectableOriginsMoveB ); 
    
                    break;                                       

                case 'selectDestinationFirstTurn':
                
                    this.highlightSelectedOrigin_SelectableDestinationSquares ( args.args.selectedOrigin_selectableDestinationsFirstTurn ); 
    
                    break;

                case 'selectDestinationMoveA':
                
                    this.highlightSelectedOrigin_SelectableDestinationSquares ( args.args.selectedOrigin_selectableDestinationsMoveA ); 
                                
                    break;                                       

                case 'selectDestinationMoveB':
                
                    this.highlightSelectedOrigin_SelectableDestinationSquares ( args.args.selectedOrigin_selectableDestinationsMoveB ); 
                            
                    break;     
                    
                case 'removeCheckerMoveA':
               
                    this.highlightRemovableCheckers ( args.args.removableCheckersMoveA ); 
                                
                    break;                                       

                case 'removeCheckerMoveB':
                
                    this.highlightRemovableCheckers ( args.args.removableCheckersMoveB ); 
                                
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
        // HIGHLIGHT SELECTABLE ORIGIN SQUARES 
        //
        //
        highlightSelectableOriginSquares: function( selectableOriginSquares ) 
        {
            console.log( 'highlightSelectableOriginSquares' );

            //
            // Remove old selectable squares and add new selectable squares
            //
            dojo.query( '.selectableOriginSquare' ).removeClass( 'selectableOriginSquare' );

            // N = this.gamedatas.board_size;                                       ####  POSSIBLE BUG FIX  ####   Maybe caused freeze in hot seat mode.
            
            if (!this.isSpectator)
            {
                for( var x in selectableOriginSquares )
                {
                    for( var y in selectableOriginSquares [ x ] )
                    {        
                        //console.log( 'Adding selectableOriginSquare class' );

                        dojo.addClass( 'clickable_square_'+x+'_'+y, 'selectableOriginSquare' );
                    }            
                }
            }
        },



        //
        //
        // HIGHLIGHT SELECTED ORIGIN AND SELECTABLE DESTINATION SQUARES 
        //
        //
        highlightSelectedOrigin_SelectableDestinationSquares: function( selectedOrigin_selectableDestinationSquares ) 
        {
            console.log( 'highlightSelectedOrigin_SelectableDestinationSquares' );


            selectedOriginSquare = selectedOrigin_selectableDestinationSquares [0];

            selectableDestinationSquares = selectedOrigin_selectableDestinationSquares [1];


            //
            //  DISPLAY SELECTED ORIGIN 
            //
            if (!this.isSpectator)
            {
                //console.log( 'Adding selectableOriginSquare class' );

                //
                // REMOVE OLD SELECTED ORIGIN SQUARE AND ADD NEW SELECTED ORIGIN SQUARE
                //
                dojo.query( '.selectedOriginSquare' ).removeClass( 'selectedOriginSquare' );

                //console.log( 'selected origin square: ' + selectedOriginSquare [ 0 ] + ', ' + selectedOriginSquare [ 1 ] );

                dojo.addClass( 'clickable_square_'  + selectedOriginSquare [ 0 ] + '_' + selectedOriginSquare [ 1 ], 'selectedOriginSquare' );
            }



            //
            //  DISPLAY SELECTABLE DESTINATIONS
            //
            dojo.query( '.selectableDestinationSquare' ).removeClass( 'selectableDestinationSquare' );

            if (!this.isSpectator)
            {
                //
                //  REMOVE OLD SELECTABLE DESTINATION SQUARES AND ADD NEW SELECTABLE DESTINATION SQUARES 
                //
                dojo.query( '.selectableDestinationSquare' ).removeClass( 'selectableDestinationSquare' );


                for( var x in selectableDestinationSquares )
                {
                    for( var y in selectableDestinationSquares [ x ] )
                    {        
                        //console.log( 'Adding selectableOriginSquare class' );

                        //console.log( 'selectable destination squares: x, y' + x + ', ' + y );


                        dojo.addClass( 'clickable_square_' + x + '_' + y, 'selectableDestinationSquare' );
                    }            
                }
            }
        },




        //
        //
        // HIGHLIGHT REMOVABLE CHECKERS
        //
        //
        highlightRemovableCheckers: function( removableCheckers ) 
        {
            console.log( 'highlightRemovableCheckers' );

            //
            //  REMOVE OLD REMOVABLE CHECKER SQUARES 
            //
            //  ADD NEW REMOVABLE CHECKER SQUARES
            //
            dojo.query( '.removableCheckerSquare' ).removeClass( 'removableCheckerSquare' );

            
            if (!this.isSpectator)
            {
                for( var x in removableCheckers )
                {
                    for( var y in removableCheckers [ x ] )
                    {        
                        console.log( 'Adding removableCheckerSquare class' );

                        dojo.addClass( 'clickable_square_'+x+'_'+y, 'removableCheckerSquare' );
                    }            
                }
            }
        },



        ///////////////////////////////////////////////////
        //// Player's action
        
        
        onClickedSquare: function( evt )
        {
            console.log('onClickedSquare');  
            // Stop this event propagation
            evt.preventDefault();
            dojo.stopEvent( evt );
    
            // Get the clicked square x and y
            // Note: "clicked_square_x_y"
            var coords = evt.currentTarget.id.split('_');
            var x_clicked_square = coords[2];               // Raw coordinates
            var y_clicked_square = coords[3];               // (0,0) is lower left square for both players



            if (    this.gamedatas.gamestate.name == "selectOriginFirstTurn"   )
            {
                if (    dojo.hasClass( 'clickable_square_'+x_clicked_square+'_'+y_clicked_square, 'selectableOriginSquare' )    )    // SELECTABLE ORIGIN SQUARE                                 
                {      
                    if  (   this.checkAction( 'selectOriginFirstTurn' ) )
                    {                
                        //console.log("selectOriginFirstTurn ajax call");    

                        this.ajaxcall( "/diablo/diablo/selectOriginFirstTurn.html", {   // SELECT ORIGIN SQUARE
                            x:x_clicked_square,
                            y:y_clicked_square
                        }, this, function( result ) {} );
                   }
                } 
            }
            else if ( this.gamedatas.gamestate.name == "selectDestinationFirstTurn" )
            {
                if (    dojo.hasClass( 'clickable_square_'+x_clicked_square+'_'+y_clicked_square, 'selectableDestinationSquare' )    )    // SELECTABLE DESTINATION SQUARE                                 
                {      
                    if  (   this.checkAction( 'selectDestinationFirstTurn' ) )
                    {                
                        //console.log("selectDestinationFirstTurn ajax call");    
                        
                        this.ajaxcall( "/diablo/diablo/selectDestinationFirstTurn.html", {   // SELECT DESTINATION SQUARE
                            x:x_clicked_square,
                            y:y_clicked_square
                        }, this, function( result ) {} );
                   }
                } 
                else 
                {
                    this.ajaxcall( "/diablo/diablo/unselectOrigin.html", {   // UNSELECT ORIGIN SQUARE
                    }, this, function( result ) {} );
                }
            }
            else if ( this.gamedatas.gamestate.name == "selectOriginMoveA" )
            {
                if (    dojo.hasClass( 'clickable_square_'+x_clicked_square+'_'+y_clicked_square, 'selectableOriginSquare' )    )    // SELECTABLE ORIGIN SQUARE                                 
                {      
                    if  (   this.checkAction( 'selectOriginMoveA' ) )
                    {                
                        //console.log("selectOriginMoveA ajax call");    

                        this.ajaxcall( "/diablo/diablo/selectOriginMoveA.html", {   // SELECT ORIGIN SQUARE
                            x:x_clicked_square,
                            y:y_clicked_square
                        }, this, function( result ) {} );
                   }
                } 
            }
            else if ( this.gamedatas.gamestate.name == "selectDestinationMoveA" )
            {
                if (    dojo.hasClass( 'clickable_square_'+x_clicked_square+'_'+y_clicked_square, 'selectableDestinationSquare' )    )    // SELECTABLE DESTINATION SQUARE                                 
                {      
                    if  (   this.checkAction( 'selectDestinationMoveA' ) )
                    {                
                        //console.log("selectDestinationMoveA ajax call");    

                        this.ajaxcall( "/diablo/diablo/selectDestinationMoveA.html", {   // SELECT DESTINATION SQUARE
                            x:x_clicked_square,
                            y:y_clicked_square
                        }, this, function( result ) {} );
                   }
                } 
                else 
                {
                    this.ajaxcall( "/diablo/diablo/unselectOrigin.html", {   // UNSELECT ORIGIN SQUARE
                    }, this, function( result ) {} );
                }
            }
            else if ( this.gamedatas.gamestate.name == "selectOriginMoveB" )
            {
                if (    dojo.hasClass( 'clickable_square_'+x_clicked_square+'_'+y_clicked_square, 'selectableOriginSquare' )    )    // SELECTABLE ORIGIN SQUARE                                 
                {      
                    if  (   this.checkAction( 'selectOriginMoveB' ) )
                    {                
                        //console.log("selectOriginMoveB ajax call");    

                        this.ajaxcall( "/diablo/diablo/selectOriginMoveB.html", {   // SELECT ORIGIN SQUARE
                            x:x_clicked_square,
                            y:y_clicked_square
                        }, this, function( result ) {} );
                   }
                } 
            }
            else if ( this.gamedatas.gamestate.name == "selectDestinationMoveB" )
            {
                if (    dojo.hasClass( 'clickable_square_'+x_clicked_square+'_'+y_clicked_square, 'selectableDestinationSquare' )    )    // SELECTABLE DESTINATION SQUARE                                 
                {      
                    if  (   this.checkAction( 'selectDestinationMoveB' ) )
                    {                
                        //console.log("selectDestinationMoveB ajax call");    

                        this.ajaxcall( "/diablo/diablo/selectDestinationMoveB.html", {   // SELECT DESTINATION SQUARE
                            x:x_clicked_square,
                            y:y_clicked_square
                        }, this, function( result ) {} );
                   }
                } 
                else 
                {
                    this.ajaxcall( "/diablo/diablo/unselectOrigin.html", {   // UNSELECT ORIGIN SQUARE
                    }, this, function( result ) {} );
                }
            }
            else if ( this.gamedatas.gamestate.name == "removeCheckerMoveA" )
            {
                if (    dojo.hasClass( 'clickable_square_'+x_clicked_square+'_'+y_clicked_square, 'removableCheckerSquare' )    )    // SELECTABLE ORIGIN SQUARE                                 
                {      
                    if  (   this.checkAction( 'removeCheckerMoveA' ) )
                    {                
                        //console.log("removeCheckerMoveA ajax call");    

                        this.ajaxcall( "/diablo/diablo/removeCheckerMoveA.html", {   // REMOVE CHECKER
                            x:x_clicked_square,
                            y:y_clicked_square
                        }, this, function( result ) {} );
                   }
                } 
            }
            else if ( this.gamedatas.gamestate.name == "removeCheckerMoveB" )
            {
                if (    dojo.hasClass( 'clickable_square_'+x_clicked_square+'_'+y_clicked_square, 'removableCheckerSquare' )    )    // SELECTABLE ORIGIN SQUARE                                 
                {      
                    if  (   this.checkAction( 'removeCheckerMoveB' ) )
                    {                
                        //console.log("removeCheckerMoveB ajax call");    

                        this.ajaxcall( "/diablo/diablo/removeCheckerMoveB.html", {   // REMOVE CHECKER
                            x:x_clicked_square,
                            y:y_clicked_square
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
                  your diablo.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            dojo.subscribe( 'originSelected', this, "notif_originSelected" );
            
            dojo.subscribe( 'originUnselected', this, "notif_originUnselected" );

            dojo.subscribe( 'checkerRemoved', this, "notif_checkerRemoved" );

            dojo.subscribe( 'checkerPlaced', this, "notif_checkerPlaced" );

            dojo.subscribe( 'checkerSlid', this, "notif_checkerSlid" );

            dojo.subscribe( 'diceRemoved', this, "notif_diceRemoved" );

            dojo.subscribe( 'diceRolled', this, "notif_diceRolled" );

            dojo.subscribe( 'diceRolledHistory', this, "notif_diceRolledHistory" );

            dojo.subscribe( 'addLastMoveIndicator', this, "notif_addLastMoveIndicator" );

            dojo.subscribe( 'slideLastMoveIndicator', this, "notif_slideLastMoveIndicator" );

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
                //var current_player_id = this.player_id;   
                //var current_player = this.gamedatas.players [ current_player_id ];               
                //var current_color = current_player.color;

                
                //N = this.gamedatas.board_size;
            

                //console.log("notif_originSelected"+" "+x+" "+y);


                dojo.query( '.selectableOriginSquare' ).removeClass( 'selectableOriginSquare' );   
            
                dojo.addClass( 'clickable_square_'+notif.args.x+'_'+notif.args.y, 'selectedOriginSquare' );

            }
       
        },

        
        notif_originUnselected: function( notif )
        {
            console.log("notif_originUnselected");

            dojo.query( '.selectedOriginSquare' ).removeClass( 'selectedOriginSquare' );      
            dojo.query( '.selectableDestinationSquare' ).removeClass( 'selectableDestinationSquare' );      
        },
        

        notif_checkerRemoved: function( notif )
        {
            console.log("notif_checkerRemoved");


            dojo.query( '.removableCheckerSquare' ).removeClass( 'removableCheckerSquare' );   
            
            dojo.query( '.selectedOriginSquare' ).removeClass( 'selectedOriginSquare' );
            
            dojo.query( '.selectableDestinationSquare' ).removeClass( 'selectableDestinationSquare' );   
            

            this.slideToObjectAndDestroy( ''+notif.args.removed_checker_id, 'overall_player_board_'+notif.args.player_id, 500, 0 ); 
        },

        
        notif_checkerPlaced: function( notif )
        {            
            console.log("notif_checkerPlaced");

            dojo.query( '.selectedOriginSquare' ).removeClass( 'selectedOriginSquare' );      
            dojo.query( '.selectableDestinationSquare' ).removeClass( 'selectableDestinationSquare' );      

            //console.log("addStoneOnBoard"+" "+notif.args.u+" "+notif.args.v+" "+notif.args.player_id+" "+notif.args.stone_id+" "+notif.args.N);
        
            this.addStackOnBoard( notif.args.x, notif.args.y, notif.args.player_id, notif.args.placed_checker_id, notif.args.N )
        },

        
        notif_checkerSlid: function( notif )
        {
            console.log("notif_checkerSlid");

            dojo.query( '.selectedOriginSquare' ).removeClass( 'selectedOriginSquare' );      
            dojo.query( '.selectableDestinationSquare' ).removeClass( 'selectableDestinationSquare' );      

                       
            //console.log( notif.args.destination_x + " " + notif.args.destination_y );

            this.slideToObject( ''+notif.args.slid_checker_id, 'placement_square_'+notif.args.destination_x+'_'+notif.args.destination_y, 500, 0 ).play();             
        },



        notif_diceRemoved: function( notif )
        {
            //console.log('' +  notif.args.removed_die_0_id + ' ' + notif.args.removed_die_0_id + ' ' + notif.args.player_id);

            this.slideToObjectAndDestroy( ''+notif.args.die_0_id, 'overall_player_board_'+notif.args.player_id, 500, 0 ); 
            this.slideToObjectAndDestroy( ''+notif.args.die_1_id, 'overall_player_board_'+notif.args.player_id, 500, 0 ); 
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



        notif_addLastMoveIndicator: function( notif )
        {
            //console.log("notif_lastMoveIndicator");

            if (this.prefs[103].value == 1)
            {
                this.addLastMoveIndicator ( notif.args.x, notif.args.y, notif.args.id, notif.args.number_of_moves );
                   
                //console.log( "notif_addLastMoveIndicator: Show last move indicator" );
            }
            else 
            {
                //console.log( "notif_addLastMoveIndicator: Don't show last move indicator" );
            }
        },




        notif_slideLastMoveIndicator: function( notif )
        {
            //console.log("notif_lastMoveIndicator");

            if (this.prefs[103].value == 1)
            {

                let x = notif.args.x;

                let y = notif.args.y;

                let lmi_id = notif.args.id;

                //let number_of_moves = notif.args.number_of_moves;


                let lmi_id_str = lmi_id.toString ( );


                this.slideToObject( lmi_id_str, 'placement_square_'+x+'_'+y, 500, 0 ).play();



                /*
                if ( number_of_moves == 1 )
                {
                    let backgroundPositionLMI = 'background-position: 0px 0px';   

                    dojo.place( this.format_block( 'jstpl_last_move_indicator', {
                        last_move_indicator_id: lmi_id_str,
                        bkgPos: backgroundPositionLMI
                    } ) , 'checkers' );

                    this.placeOnObject( lmi_id_str, 'placement_square_'+x+'_'+y );    
                }
                else 
                {
                    this.slideToObject( lmi_id_str, 'placement_square_'+x+'_'+y, 500, 0 ).play();
                }
                */


                //this.addLastMoveIndicator ( notif.args.u, notif.args.v, notif.args.id );
                   
                //console.log( "notif_lastMoveIndicator: Show last move indicator" );
            }
            else {
                //console.log( "notif_lastMoveIndicator: Don't show last move indicator" );
            }
        },




        // 
        //  DISPLAY REMAINING NUMBER OF CHECKERS 
        //
        notif_playerPanel: function ( notif ) {

            //console.log("Update player panel");

            var player_id = notif.args.player_id;

            var number_of_checkers = notif.args.number_of_checkers;

            dojo.byId("remaining-checkers_p"+player_id).innerHTML = number_of_checkers;
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
        }

   });             
});
