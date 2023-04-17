/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Redstone implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * redstone.js
 *
 * Redstone user interface script
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
    return declare("bgagame.redstone", ebg.core.gamegui, {
        constructor: function(){
            console.log('redstone constructor');
              
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
            // Display player panel
            //
            for (var keyPlayerId in gamedatas.placedStones) 
            {
                //console.log( "placedStones" + gamedatas.placedStones[keyPlayerId] );

                dojo.byId("placed-stones_p"+keyPlayerId).innerHTML = gamedatas.placedStones[keyPlayerId];
            }



            // TODO: Set up your game interface here, according to "gamedatas"
            //
            // Coords overlay option
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
                    //console.log( " " + square.x + " " + square.y + " " +  square.player + " " +  square.stone_id );

                    this.addStoneOnBoard ( square.x, square.y, square.player, square.stone_id );
                }            
            }







            //
            // LAST MOVE INDICATOR
            //
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







            //
            // ON CLICKED SQUARE 
            //            
            dojo.query( '.clickable_square_'+gamedatas.board_size ).connect( 'onclick', this, 'onClickedSquare' );        
            

 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
        },
       

        //
        // Add coordinates overlay
        //
         addOverlay ()
        {
            let backgroundPosition = 'background-position: 0px 0px';
            
            dojo.place( this.format_block( 'jstpl_billboard_content', {
                bkgPos: backgroundPosition
            } ) , 'overlay_billboard_1' );
         
        },


        //
        // Add stone on board
        //
        addStoneOnBoard: function( x, y, player_id, stone_id )
        {   
            //console.log( 'stone_id = ' + stone_id );
            //console.log( " " + x + " " + y + " " +  player_id + " " +  stone_id );

            let backgroundPosition = 'background-position: ';


            if ( stone_id >= 10000 && stone_id < 20000 )        // BLACK STONE  ##################
            {
                if ( this.gamedatas.board_size == 9 )           // board size = 9
                {
                    horOffset = 0;
                    verOffset = -.7;
                }
                else if ( this.gamedatas.board_size == 11 )     // board size = 11
                {
                    horOffset = 0;
                    verOffset = 0;
                }
                else if ( this.gamedatas.board_size == 13 )     // board size = 13
                {
                    horOffset = 0;
                    verOffset = -.6;
                }
                else if ( this.gamedatas.board_size == 15 )     // board size = 15
                {
                    horOffset = 0;
                    verOffset = 0;
                }
                else                                            // board size = 19
                {
                    horOffset = 0;
                    verOffset = 0;
                }
               
                // console.log( 'x, y, stone_id: ' + x + ' ' + y + ' ' + stone_id );

                backgroundPosition += horOffset + "px " + verOffset + "px";   
                
                dojo.place( this.format_block( 'jstpl_black_or_red_stone', {
                    stone_id: stone_id,
                    bkgPos: backgroundPosition
                } ) , 'stones' );
            }
            else if ( stone_id >= 20000 && stone_id < 30000 )       // WHITE STONE  ##################
            {
                stone_img_id = stone_id % 20000;            // Lose the 20___ stone_id prefix

                stone_img_id_mod = stone_img_id % 120;      // Only 120 white stones in image

                column = stone_img_id_mod % 12;             // 12 columns of white stones in image

                row = Math.floor ( stone_img_id_mod / 10 ); // 10 rows of white stones in image


                if ( this.gamedatas.board_size == 9 )       // board size = 9
                {
                    horOffset = column * -75;
                    verOffset = row * -75 - 1;
                }
                else if ( this.gamedatas.board_size == 11 )  // board size = 11
                {
                    horOffset = column * -59;
                    verOffset = row * -59 - 1;
                }
                else if ( this.gamedatas.board_size == 13 )  // board size = 13
                {
                    horOffset = column * -52;
                    verOffset = row * -52 - 1;
                }
                else if ( this.gamedatas.board_size == 15 )  // board size = 15
                {
                    horOffset = column * -47;
                    verOffset = row * -47 - 1;
                }
                else                                        // board size = 19
                {
                    horOffset = column * -43;
                    verOffset = row * -43 - 1.3;
                }
                

                backgroundPosition += horOffset + "px " + verOffset + "px";   
                
                dojo.place( this.format_block( 'jstpl_white_stone', {
                    stone_id: stone_id,
                    bkgPos: backgroundPosition
                } ) , 'stones' );
            }
            else       // Red stone   ....   stone_id >= 30000 
            {
                if ( this.gamedatas.board_size == 9 )           // board size = 9
                {
                    horOffset = -77;
                    verOffset = -.7;
                }
                else if ( this.gamedatas.board_size == 11 )     // board size = 11
                {
                    horOffset = -60;
                    verOffset = -.6;
                }
                else if ( this.gamedatas.board_size == 13 )     // board size = 13
                {
                    horOffset = -53;
                    verOffset = -.6;
                }
                else if ( this.gamedatas.board_size == 15 )     // board size = 15
                {
                    horOffset = -48;
                    verOffset = -.6;
                }
                else                                            // board size = 19
                {
                    horOffset = -44;
                    verOffset = 0;
                }
               
                //console.log( 'x, y, stone_id: ' + x + ' ' + y + ' ' + stone_id );

                backgroundPosition += horOffset + "px " + verOffset + "px";   
                
                dojo.place( this.format_block( 'jstpl_black_or_red_stone', {
                    stone_id: stone_id,
                    bkgPos: backgroundPosition
                } ) , 'stones' );
            }


            if ( player_id == 0 )
                this.placeOnObject( stone_id, 'placementSquare_'+x+'_'+y );
            else
            {
                this.placeOnObject( stone_id, 'overall_player_board_'+player_id );
                this.slideToObject( stone_id, 'placementSquare_'+x+'_'+y ).play(); 
            }
        },





        addLastMoveIndicator: function( last_move_x, last_move_y, last_move_id )
        {  
            console.log( 'addLastMoveIndicator: function(  )' );


            if ( last_move_id == 99999 ) // NO STONES PLACED YET
            {
                //console.log( 'last_move_id == 99999' );

                return;
            }




            //
            //  REMOVE PREVIOUS LAST MOVE INDICATOR
            //
            dojo.query( '.last_move_indicator' ).removeClass( 'last_move_indicator' );




            //
            // IF NOT THE FIRST MOVE 
            //      DESTROY PREVIOUS LAST MOVE INDICATOR 
            //
            /*
            if ( last_move_id > 100000 ) 
            {
                prev_last_move_id = last_move_id - 1;

                prev_last_move_id_str = prev_last_move_id.toString ( );


                //this.destroy( prev_last_move_id_str );
                this.fadeOutAndDestroy( prev_last_move_id_str, 100 );
            }
            */

            //
            // If not first placement of last move indicator 
            //      Remove previous last move indicator 
            //
            /*
            if ( last_move_id > 100000 ) 
            {
                last_move_id_prev = last_move_id -1;
                last_move_id_prev_str = last_move_id_prev.toString ( );

                this.fadeOutAndDestroy( last_move_id_prev_str, 100 );
                //this.destroy( last_move_id_prev_str );
            }
            */

            let backgroundPositionLMI = 'background-position: 0px 0px';   

            let last_move_id_str = last_move_id.toString ( );

            //console.log( 'last_move_x, last_move_y, last_move_id_str: ' + last_move_x + ', ' + last_move_y + ', ' + last_move_id_str );
               
            dojo.place( this.format_block( 'jstpl_last_move_indicator', {
                last_move_id: last_move_id_str,
                bkgPos: backgroundPositionLMI
            } ) , 'stones' );

            this.placeOnObject( last_move_id_str, 'placementSquare_'+last_move_x+'_'+last_move_y );            
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
                case 'selectSquare':

                    this.highlightAllSelectableSquares ( args.args.allSelectableSquares ); // Non-capturing, capturing, and non-capturing or capturing squares
    
                    break;


                case 'chooseNonCaptureOrCapture':

                    this.highlightSelectedSquare ( args.args.selectedSquare ); // Non-capturing, capturing, and non-capturing or capturing squares
    
                    break;

                    
                case 'firstMoveChoice':
                
                    this.highlightAllSelectableSquares ( args.args.allSelectableSquares ); // Non-capturing, capturing, and non-capturing or capturing squares
                            
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

            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            //console.log( 'onUpdateActionButtons: '+stateName );
                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName )
                {
					case 'firstMoveChoice':
						this.addActionButton( 'makeChoice_button', _('Switch colors'), 'onMakeChoice' ); 
                    break;

					case 'chooseNonCaptureOrCapture':
						this.addActionButton( 'NonCapture_button', _('Non-capture'), 'onNonCaptureOnSelectedSquare' ); 
						this.addActionButton( 'Capture_button', _('Capture'), 'onCaptureOnSelectedSquare' ); 
                    break;
                }
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods
        
        //
        //
        // HIGHLIGHT NON-CAPTURING SELECTABLE SQUARES AND CAPTURING SELECTABLE SQUARES 
        //
        //
        highlightAllSelectableSquares: function( allSelectableSquares ) // nonCapturingSelectableSquares and capturingSelectableSquares
        {
            console.log( 'highlightAllSelectableSquares' );

            nonCapturingSelectableSquares = allSelectableSquares [ 0 ];

            capturingSelectableSquares = allSelectableSquares [ 1 ];

            nonCapturingOrCapturingSelectableSquares = allSelectableSquares [ 2 ];


            //
            // Remove old selectable squares
            //
            dojo.query( '.nonCapturingSelectableSquare' ).removeClass( 'nonCapturingSelectableSquare' );

            dojo.query( '.capturingSelectableSquare' ).removeClass( 'capturingSelectableSquare' );

            dojo.query( '.nonCapturingOrCapturingSelectableSquare' ).removeClass( 'nonCapturingOrCapturingSelectableSquare' );

            dojo.query( '.nonCapturingOrCapturingSelectedSquare' ).removeClass( 'nonCapturingOrCapturingSelectedSquare' );


            if (!this.isSpectator)
            {  
                for( var x in nonCapturingSelectableSquares )
                {
                    for( var y in nonCapturingSelectableSquares [ x ] )
                    {        
                        //console.log( 'Adding nonCapturingSelectableSquares class to ' + x + ' ' + y );
    
                        if (this.prefs[102].value == 1)
                        {
                            dojo.addClass( 'clickable_square_'+x+'_'+y, 'nonCapturingSelectableSquare' );
                        }
                        else 
                        {
                            dojo.addClass( 'clickable_square_'+x+'_'+y, 'nonCapturingSelectableSquare_noColor' );
                        }

                    }            
                }
        
                for( var x in capturingSelectableSquares )
                {
                    for( var y in capturingSelectableSquares [ x ] )
                    {        
                        //console.log( 'Adding capturingSelectableSquares class to ' + x + ' ' + y );
    
                        if (this.prefs[102].value == 1)
                        {
                            dojo.addClass( 'clickable_square_'+x+'_'+y, 'capturingSelectableSquare' );
                        }
                        else 
                        {
                            dojo.addClass( 'clickable_square_'+x+'_'+y, 'capturingSelectableSquare_noColor' );
                        }
                    }            
                }

                for( var x in nonCapturingOrCapturingSelectableSquares )
                {
                    for( var y in nonCapturingOrCapturingSelectableSquares [ x ] )
                    {        
                        //console.log( 'Adding nonCapturingOrCapturingSelectableSquare class to ' + x + ' ' + y );
    
                        if (this.prefs[102].value == 1)
                        {
                            dojo.addClass( 'clickable_square_'+x+'_'+y, 'nonCapturingOrCapturingSelectableSquare' );
                        }
                        else 
                        {
                            dojo.addClass( 'clickable_square_'+x+'_'+y, 'nonCapturingOrCapturingSelectableSquare_noColor' );
                        }
                    }            
                }
            }

                      
            // this.addTooltipToClass( 'selectableSquare', '', _('Select this square') );
        },



        highlightSelectedSquare: function( selectedSquare ) // selectedSquare
        {
            console.log( 'highlightSelectedSquare' );

            //
            // Remove old selectable squares
            //
            dojo.query( '.nonCapturingSelectableSquare' ).removeClass( 'nonCapturingSelectableSquare' );

            dojo.query( '.capturingSelectableSquare' ).removeClass( 'capturingSelectableSquare' );

            dojo.query( '.nonCapturingOrCapturingSelectableSquare' ).removeClass( 'nonCapturingOrCapturingSelectableSquare' );

            dojo.query( '.nonCapturingOrCapturingSelectedSquare' ).removeClass( 'nonCapturingOrCapturingSelectedSquare' );


            //
            // Remove old selected squares (should be just one) and add new selected squares
            //
            dojo.query( '.selectedSquare' ).removeClass( 'selectedSquare' );


            if (!this.isSpectator)
            {    
                x = selectedSquare [ 0 ];
                y = selectedSquare [ 1 ];

                //console.log( 'selectedSquare' + ' ' + x + ' ' + y );

                dojo.addClass( 'clickable_square_'+x+'_'+y, 'nonCapturingOrCapturingSelectedSquare' );
            }
                      
            // this.addTooltipToClass( 'selectedSquare', '', _('Selected square') );
        },


        /* @Override */
        format_string_recursive : function(log, args) {
            try {
                if (log && args && !args.processed) {

                    args.processed = true;
                    
                    if (!this.isSpectator){
                        args.You = this.divYou(); 
                    }

                }
            } catch (e) {
                console.error(log,args,"Exception thrown", e.stack);
            }
            return this.inherited(arguments);
        },

        divYou : function() {
            var color = this.gamedatas.players[this.player_id].color;
            var color_bg = "";
            if (this.gamedatas.players[this.player_id] && this.gamedatas.players[this.player_id].color_back) {
                color_bg = "background-color:#" + this.gamedatas.players[this.player_id].color_back + ";";
            }
            var you = "<span style=\"font-weight:bold;color:#" + color + ";" + color_bg + "\">" + __("lang_mainsite", "You") + "</span>";
            return you;
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
            var x_clicked_square = coords[2];               //                  COORDS 2 AND 3      USUALLY 1 AND 2
            var y_clicked_square = coords[3];                

            //console.log("x_clicked_square, y_clicked_square" + " " + x_clicked_square + " " + y_clicked_square);  

            //console.log('clickable_square_'+x_clicked_square+'_'+y_clicked_square);  

            //
            //  NOT CALLING 3 SEPARATE BACKEND FUNCTIONS FOR THE 3 POSSIBLE CLASSES 
            //
            //  JUST CALLING selectSquare 
            //
            //  selectSquare WILL DETERMINE WHAT TO DO.  THIS WILL PROTECT FROM ERRONEOUS, SPORADIC CALLS FROM FRONTEND
            //
            //if (    dojo.hasClass ( 'clickable_square_'+x_clicked_square+'_'+y_clicked_square )    )


            if (    dojo.hasClass ( 'clickable_square_'+x_clicked_square+'_'+y_clicked_square, 'nonCapturingSelectableSquare' )    
                 || dojo.hasClass ( 'clickable_square_'+x_clicked_square+'_'+y_clicked_square, 'nonCapturingSelectableSquare_noColor' )

                 || dojo.hasClass ( 'clickable_square_'+x_clicked_square+'_'+y_clicked_square, 'capturingSelectableSquare' ) 
                 || dojo.hasClass ( 'clickable_square_'+x_clicked_square+'_'+y_clicked_square, 'capturingSelectableSquare_noColor' ) 

                 || dojo.hasClass ( 'clickable_square_'+x_clicked_square+'_'+y_clicked_square, 'nonCapturingOrCapturingSelectableSquare' )
                 || dojo.hasClass ( 'clickable_square_'+x_clicked_square+'_'+y_clicked_square, 'nonCapturingOrCapturingSelectableSquare_noColor' )    )
            /*
            if (    dojo.hasClass ( 'nonCapturingSelectableSquare' ) || dojo.hasClass ( 'nonCapturingSelectableSquare_noColor' )
                 || dojo.hasClass ( 'capturingSelectableSquare' ) || dojo.hasClass ( 'capturingSelectableSquare_noColor' ) 
                 || dojo.hasClass ( 'nonCapturingOrCapturingSelectableSquare' ) || dojo.hasClass ( 'nonCapturingOrCapturingSelectableSquare_noColor' )    )
            */
            {                            
                if (    this.gamedatas.gamestate.name == "selectSquare" || this.gamedatas.gamestate.name == "firstMoveChoice"    ) 
                {                
                    if  (    this.checkAction ( 'selectSquare' )    )
                    {                
                        //console.log("selectSquare ajax call");    

                        this.ajaxcall( "/redstone/redstone/selectSquare.html", {   // SELECT SQUARE
                            x:x_clicked_square,
                            y:y_clicked_square
                        }, this, function( result ) {} );
                    }
                }
                /*
                else if (  this.gamedatas.gamestate.name == "chooseNonCaptureOrCapture"  ) 
                {          
                    if (   this.checkAction( 'unselectSelectedSquare' ) )
                    {        
                            console.log("AJAX unselectSelectedSquare");    
                   
                            this.ajaxcall( "/redstone/redstone/unselectSelectedSquare.html", {     // Unselect checker
                        }, this, function( result ) {} );
                    }                    
                }
                */
            } 
            else if (  this.gamedatas.gamestate.name == "chooseNonCaptureOrCapture"  ) 
            {          
                if (   this.checkAction( 'unselectSelectedSquare' ) )
                {        
                       this.ajaxcall( "/redstone/redstone/unselectSelectedSquare.html", {     // Unselect checker
                    }, this, function( result ) {} );
                }                    
                /*
                if (   this.checkAction( 'unselectSelectedSquare' ) )
                {        
                        console.log("AJAX unselectSelectedSquare");    
                   
                        this.ajaxcall( "/redstone/redstone/unselectSelectedSquare.html", {     // Unselect checker
                    }, this, function( result ) {} );
                }
                */
            } 
        },
     

        //
        //  BUTTONS 
        //  #######
        //
        //
		//  If the player has decided to switch colors
        //
        onMakeChoice: function ()
        {
			this.ajaxcall( '/redstone/redstone/makeChoice.html', { lock:true }, this, function( result ) {} );
		},

        
        onNonCaptureOnSelectedSquare: function ()
        {
			this.ajaxcall( '/redstone/redstone/nonCaptureOnSelectedSquare.html', { lock:true }, this, function( result ) {} );
		},

        
        onCaptureOnSelectedSquare: function ()
        {
			this.ajaxcall( '/redstone/redstone/captureOnSelectedSquare.html', { lock:true }, this, function( result ) {} );
		},

        
         
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your redstone.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            dojo.subscribe( 'stonePlaced', this, "notif_stonePlaced" );

            dojo.subscribe( 'stonesRemoved', this, "notif_stonesRemoved" );

            dojo.subscribe( 'stonePlacedHistory', this, "notif_stonePlacedHistory" );

            dojo.subscribe( 'unselectSelectedSquare', this, "notif_unselectSelectedSquare" );

            dojo.subscribe( 'lastMoveIndicator', this, "notif_lastMoveIndicator" );

            dojo.subscribe( 'playerPanel', this, 'notif_playerPanel');

            dojo.subscribe( 'backendMessage', this, "notif_backendMessage" );

            dojo.subscribe( 'newScores', this, "notif_newScores" );


             //Swapping colors notif
             dojo.subscribe( 'swapColors', this, "notif_swapColors" );
             this.notifqueue.setSynchronous( 'swapColors', 10 );
        },  
        
        // TODO: from this point and below, you can write your game notifications handling methods
        
        notif_stonePlaced: function( notif )
        {            
            // Remove current possible moves (makes the board more clear)
            dojo.query( '.clickable_square' ).removeClass( 'clickable_square' );        
        
            this.addStoneOnBoard ( notif.args.x, notif.args.y, notif.args.player_id, notif.args.stone_id );

            //console.log("addStoneOnBoard"+" "+notif.args.x+" "+notif.args.y+" "+notif.args.player_id+" "+notif.args.stone_id);
        },


        notif_stonesRemoved: function( notif )
        {
            //console.log("notif_stonesRemoved");

            removable_stone_IDs = notif.args.removable_stone_IDs;

            for ( i = 0; i < removable_stone_IDs.length; i++ )
            {
                //console.log ( "removable_stone_ID = " + removable_stone_IDs [ i ] );

                this.slideToObjectAndDestroy( ''+removable_stone_IDs [ i ], 'overall_player_board_'+notif.args.player_id, 1000, 0 ); 
            }
        },


        notif_unselectSelectedSquare: function( notif )
        {
            //console.log("notif_unselectSelectedSquare");

            // 
            // UNSELECT SELECTED SQUARE 
            //
            dojo.query( '.nonCapturingOrCapturingSelectedSquare' ).removeClass( 'nonCapturingOrCapturingSelectedSquare' );        
        },


        notif_stonePlacedHistory: function ( notif )
        {
            //console.log("notif_checkerPlacedHistory");
        },








        notif_lastMoveIndicator: function( notif )
        {
            //console.log("notif_lastMoveIndicator");

            if (this.prefs[103].value == 1)
            {
                this.addLastMoveIndicator ( notif.args.x, notif.args.y, notif.args.id );
                   
                //console.log( "notif_lastMoveIndicator: Show last move indicator" );
            }
            else {
                //console.log( "notif_lastMoveIndicator: Don't show last move indicator" );
            }
        },








        notif_playerPanel: function ( notif ) {
            var placed_stones = notif.args.placed_stones;
            var player_id = notif.args.player_id;
            dojo.byId("placed-stones_p"+player_id).innerHTML = placed_stones;
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



        //
        //SWAP COLORS
        //
        notif_swapColors: function ( notif )
        {
            console.log ("notif_swapColors");

			//Change/rafraichit les couleurs des joueurs
			this.gamedatas.players[ notif.args.player_id ].color = notif.args.player_color;
			dojo.query($('player_name_'+notif.args.player_id)).style('color', '#'+notif.args.player_color);
			
			//Notify his new color to the player
			if(notif.args.player_id==this.player_id)
			{
				this.showMessage( _('You are now playing <span style="color:#${player_color}">${player_color_name}</span>').replace('${player_color}', 
                    notif.args.player_color).replace('${player_color_name}', _( notif.args.player_colorname ) ), 'info' );
			}


            if (notif.args.player_color == "000000") {
                dojo.query($('icon-placed-stones_'+notif.args.player_id)).removeClass('icon_placed_stones_6464ad');
                dojo.query($('icon-placed-stones_'+notif.args.player_id)).addClass('icon_placed_stones_000000');
            } else {
                dojo.query($('icon-placed-stones_'+notif.args.player_id)).removeClass('icon_placed_stones_000000');
                dojo.query($('icon-placed-stones_'+notif.args.player_id)).addClass('icon_placed_stones_6464ad');
            }
            
            
            for (var player_id in this.gamedatas.players) {
                document.querySelector("#player_name_" + player_id + " a").style.color = "#" + this.gamedatas.players[player_id].color;
            }


            dojo.byId("placed-stones_p"+notif.args.player_id).innerHTML = notif.args.placedStones;

	    }   
    });             
});
