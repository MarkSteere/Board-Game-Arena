/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Oust implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * oust.js
 *
 * Oust user interface script
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
    return declare("bgagame.oust", ebg.core.gamegui, {
        constructor: function(){
            console.log('oust constructor');
              
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
            for (var keyPlayerId in gamedatas.placedCheckers) 
            {
                //console.log( "placedCheckers" + gamedatas.placedCheckers[keyPlayerId] );

                dojo.byId("placed-checkers_p"+keyPlayerId).innerHTML = gamedatas.placedCheckers[keyPlayerId];
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
                    //console.log( " " + square.u + " " + square.v + " " +  square.player + " " +  square.ch_id );

                    this.addCheckerOnBoard( square.u, square.v, square.player, square.ch_id );
                }            
            }
            




            //
            // LAST MOVE INDICATOR
            //
            if (this.prefs[103].value == 1)
            {
                last_move_u = this.gamedatas.last_move [ 0 ];

                last_move_v = this.gamedatas.last_move [ 1 ];

                last_move_id = this.gamedatas.last_move [ 2 ];

                this.addLastMoveIndicator ( last_move_u, last_move_v, last_move_id );
                   
                //console.log( "Show last move indicator" );
            }
            else {
                //console.log( "Don't show last move indicator" );
            }
 


            //
            // ON CLICKED SQUARE 
            //            
            dojo.query( '.square'+gamedatas.board_size ).connect( 'onclick', this, 'onClickedSquare' );        
          
            
 
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
        // Add checker on board
        //
        addCheckerOnBoard: function( u, v, player_id, checker_id )
        {   

            //console.log( 'checker_id = ' + checker_id );
            //console.log( " " + u + " " + v + " " +  player + " " +  checker_id );

            let backgroundPosition = 'background-position: ';

            if ( checker_id >= 1000 && checker_id < 2000 )       // Red checker  
            {
                if ( this.gamedatas.board_size == 6 )       // board size = 6
                {
                    horOffset = 0;
                    verOffset = 0;
                }
                else if ( this.gamedatas.board_size == 7 )  // board size = 7
                {
                    horOffset = 0;
                    verOffset = 0;
                }
                else                                        // board size = 8
                {
                    horOffset = 0;
                    verOffset = 0;
                }
               
                //console.log( 'checker_id = ' + checker_id );
                //console.log( 'horOffset = ' + horOffset );
                //console.log( 'vertOffset = ' + vertOffset );
            }
            else                                              // Blue checker 2000-2999
            {
                if ( this.gamedatas.board_size == 6 )       // board size = 6
                {
                    horOffset = -56;
                    verOffset = 0;
                }
                else if ( this.gamedatas.board_size == 7 )  // board size = 7
                {
                    horOffset = -48;
                    verOffset = 0;
                }
                else                                        // board size = 8
                {
                    horOffset = -42;
                    verOffset = 0;
                }
                
                //console.log( 'checker_id = ' + checker_id );
                //console.log( 'horOffset = ' + horOffset );
                //console.log( 'vertOffset = ' + vertOffset );
            }


            backgroundPosition += horOffset + "px " + verOffset + "px";   
                

            dojo.place( this.format_block( 'jstpl_checker', {
                checker_id: checker_id,
                bkgPos: backgroundPosition
            } ) , 'checkers' );


            this.placeOnObject( checker_id, 'overall_player_board_'+player_id );
            this.slideToObject( checker_id, 'square_'+u+'_'+v ).play(); 

        },




        addLastMoveIndicator: function( last_move_u, last_move_v, last_move_id )
        {  
            //console.log( 'addLastMoveIndicator: function(  )' );

            if ( last_move_id == 99999 ) // NO STONES PLACED YET
            {
                //console.log( 'last_move_id == 99999' );

                return;
            }


            //
            //  REMOVE PREVIOUS LAST MOVE INDICATOR
            //
            dojo.query( '.last_move_indicator' ).removeClass( 'last_move_indicator' );


            let backgroundPositionLMI = 'background-position: 0px 0px';   

            let last_move_id_str = last_move_id.toString ( );

            //console.log( 'last_move_u, last_move_v, last_move_id_str: ' + last_move_u + ', ' + last_move_v + ', ' + last_move_id_str );
              
            dojo.place( this.format_block( 'jstpl_last_move_indicator', {
                last_move_id: last_move_id_str,
                bkgPos: backgroundPositionLMI
            } ) , 'checkers' );

            this.placeOnObject( last_move_id_str, 'square_'+last_move_u+'_'+last_move_v );      
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
                case 'placeChecker':

                    this.highlightSelectableCells ( args.args.selectableCells );
    
                    break;
                    
                case 'firstMoveChoice':
                
                    this.highlightSelectableCells ( args.args.selectableCells );
                            
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
            //console.log( 'onUpdateActionButtons: '+stateName );
                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName )
                {
					case 'firstMoveChoice':
						this.addActionButton( 'makeChoice_button', _('Switch colors'), 'onMakeChoice' ); 
                    break;
                }
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods
        
        highlightSelectableCells: function( selectableCells )
        {
            //
            // Remove old selectable checkers and add new selectable checkers
            //
            dojo.query( '.selectableCell' ).removeClass( 'selectableCell' );


            if (!this.isSpectator)
            {
                //var current_player_id = this.player_id;   
                //var current_player = this.gamedatas.players [ current_player_id ];               
                //var current_color = current_player.color;

        
                for( var u in selectableCells )
                {
                    for( var v in selectableCells [ u ] )
                    {        
                        //console.log( 'Adding selectableCell class to '+ u + ' ' + v);

                        dojo.addClass( 'square_'+u+'_'+v, 'selectableCell' );
                    }            
                }
            }

                      
            // this.addTooltipToClass( 'selectableChecker', '', _('Select this checker') );
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
            //console.log("onClickedSquare");  
            
            // Stop this event propagation
            evt.preventDefault();
            dojo.stopEvent( evt );
    
            // Get the clicked square x and y
            // Note: square id format is "square_X_Y"
            var coords = evt.currentTarget.id.split('_');
            var u_clicked_square = coords[1];               // Raw coordinates
            var v_clicked_square = coords[2];               // (0,0) is lower left cell for both players


            if (   dojo.hasClass( 'square_'+u_clicked_square+'_'+v_clicked_square, 'selectableCell'   )  )          // Select cell                                 
            {            
                if  (   this.checkAction( 'placeChecker' ) )
                {                
                    //console.log("placeChecker ajax call");    

                    this.ajaxcall( "/oust/oust/placeChecker.html", {   // Place checker
                        u:u_clicked_square,
                        v:v_clicked_square
                    }, this, function( result ) {} );
                }
            } 
        },
     

		//If the player has decided to switch colors
        onMakeChoice: function ()
        {
			this.ajaxcall( '/oust/oust/makeChoice.html', { lock:true }, this, function( result ) {} );
		},

        
        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your oust.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            dojo.subscribe( 'checkerPlaced', this, "notif_checkerPlaced" );

            dojo.subscribe( 'checkersRemoved', this, "notif_checkersRemoved" );

            dojo.subscribe( 'newScores', this, "notif_newScores" );

            dojo.subscribe( 'checkerPlacedHistory', this, "notif_checkerPlacedHistory" );

            dojo.subscribe( 'lastMoveIndicator', this, "notif_lastMoveIndicator" );

            dojo.subscribe( 'backendMessage', this, "notif_backendMessage" );

            dojo.subscribe( 'playerPanel', this, 'notif_playerPanel');


            //Swapping colors notif
            dojo.subscribe( 'swapColors', this, "notif_swapColors" );
            this.notifqueue.setSynchronous( 'swapColors', 10 );
                   
        },  
        
        // TODO: from this point and below, you can write your game notifications handling methods
        
        notif_checkerPlaced: function( notif )
        {            
            // Remove current possible moves (makes the board more clear)
            dojo.query( '.selectableCell' ).removeClass( 'selectableCell' );        
        
            this.addCheckerOnBoard( notif.args.u, notif.args.v, notif.args.player_id, notif.args.checker_id )

            //console.log("addCheckerOnBoard"+" "+notif.args.u+" "+notif.args.v+" "+notif.args.player_id+" "+notif.args.checker_id);
        },
        

        notif_checkersRemoved: function( notif )
        {
            console.log("notif_checkersRemoved");


            console.log ( "removed_checker_IDs_str " + notif.args.removed_checker_IDs_str );


            removed_checker_IDs = notif.args.removed_checker_IDs_str.split ( "_");



            for ( i = 0; i < removed_checker_IDs.length; i++ )
            {
                console.log ( "removed_checker_ID = " + removed_checker_IDs [ i ] );

                this.slideToObjectAndDestroy( ''+removed_checker_IDs [ i ], 'overall_player_board_'+notif.args.player_id, 1000, 0 ); 
            }
        },

        
        notif_newScores: function( notif )
        {
            for( var player_id in notif.args.scores )
            {
                var newScore = notif.args.scores[ player_id ];
                this.scoreCtrl[ player_id ].toValue( newScore );
            }
        },


        notif_checkerPlacedHistory: function ( notif )
        {
            //console.log("notif_checkerPlacedHistory");
        },









        notif_lastMoveIndicator: function( notif )
        {
            //console.log("notif_lastMoveIndicator");

            if (this.prefs[103].value == 1)
            {
                this.addLastMoveIndicator ( notif.args.u, notif.args.v, notif.args.id );
                   
                //console.log( "notif_lastMoveIndicator: Show last move indicator" );
            }
            else {
                //console.log( "notif_lastMoveIndicator: Don't show last move indicator" );
            }
        },






        notif_backendMessage: function( notif )
        {
            //console.log("Inside notif_backendMessage");
        },


        notif_playerPanel: function ( notif ) {
            var placed_checkers = notif.args.placed_checkers;
            var player_id = notif.args.player_id;
            dojo.byId("placed-checkers_p"+player_id).innerHTML = placed_checkers;
        },


        //
        //SWAP COLORS
        //
        notif_swapColors: function ( notif )
        {

			//Change/rafraichit les couleurs des joueurs
			this.gamedatas.players[ notif.args.player_id ].color = notif.args.player_color;
			dojo.query($('player_name_'+notif.args.player_id)).style('color', '#'+notif.args.player_color);
			
			//Notify his new color to the player
			if(notif.args.player_id==this.player_id)
			{
				this.showMessage( _('You are now playing <span style="color:#${player_color}">${player_color_name}</span>').replace('${player_color}', 
                    notif.args.player_color).replace('${player_color_name}', _( notif.args.player_colorname ) ), 'info' );
			}


            if (notif.args.player_color == "5e3200") {
                dojo.query($('icon-placed-checkers_'+notif.args.player_id)).removeClass('icon_placed_checkers_00a0b4');
                dojo.query($('icon-placed-checkers_'+notif.args.player_id)).addClass('icon_placed_checkers_5e3200');
            } else {
                dojo.query($('icon-placed-checkers_'+notif.args.player_id)).removeClass('icon_placed_checkers_5e3200');
                dojo.query($('icon-placed-checkers_'+notif.args.player_id)).addClass('icon_placed_checkers_00a0b4');
            }
            
            
            for (var player_id in this.gamedatas.players) {
                document.querySelector("#player_name_" + player_id + " a").style.color = "#" + this.gamedatas.players[player_id].color;
            }


            //dojo.byId("captured-checkers_p"+notif.args.player_id).innerHTML = 99;
            dojo.byId("placed-checkers_p"+notif.args.player_id).innerHTML = notif.args.placedCheckers;

	    }
   });             
});
