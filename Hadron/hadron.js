/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Hadron implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * hadron.js
 *
 * Hadron user interface script
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
    return declare("bgagame.hadron", ebg.core.gamegui, {
        constructor: function(){
            console.log('hadron constructor');
              
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
            
            //
            // Setting up player boards
            //
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
            // Setting up board with tiles
            //   
            for( var i in gamedatas.board )
            {
                var square = gamedatas.board[i];

                if( square.player !== null )
                {
                    //console.log( " " + square.x + " " + square.y + " " +  square.player + " " +  square.tile_id );

                    this.addTileOnBoard ( square.x, square.y, square.player, square.tile_id, gamedatas.board_size );
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

                this.addLastMoveIndicator ( last_move_x, last_move_y, last_move_id, gamedatas.board_size );
                   
                //console.log( "Show last move indicator" );
            }
            else {
                //console.log( "Don't show last move indicator" );
            }



            //
            // Display player panel data
            //
            for (var keyPlayerId in gamedatas.placedTiles) 
            {
                //console.log( "placedTiles" + gamedatas.placedTiles[keyPlayerId] );

                placed_tiles = gamedatas.placedTiles[keyPlayerId];

                dojo.byId("placed-tiles_p"+keyPlayerId).innerHTML = placed_tiles;
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
            // ON CLICKED SQUARE 
            //            
            dojo.query( '.clickable_square_'+gamedatas.board_size ).connect( 'onclick', this, 'onClickedSquare' );        
            

 
             // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
        },
       


        //
        //  ADD TILE ON BOARD 
        //
        addTileOnBoard: function( x, y, player, tile_id, N )
        {   
            //console.log( 'tile_id = ' + tile_id );

            if ( N == 5 )
            {
                //console.log( 'Board size = 5' );

                tileHorOffset = 0;
                tileVerOffset = 0; 
            }
            else    // N == 7
            {
                //console.log( 'Board size = 7' );

                tileHorOffset = 0;
                tileVerOffset = 0; 
            }


            let backgroundPosition = 'background-position: ';

            backgroundPosition += tileHorOffset + "px " + tileVerOffset + "px";

            
            if ( tile_id < 20000 )                                          // RED TILE
            {
                //console.log( 'tile_id = ' + tile_id );
                //console.log( 'tileHorOffset = ' + tileHorOffset );
                //console.log( 'tileVerOffset = ' + tileVerOffset );


                dojo.place( this.format_block( 'jstpl_red_tile', {
                    tile_id: tile_id,
                    bkgPos: backgroundPosition
                } ) , 'tiles' );        
            }
            else                                                            // BLUE TILE
            {
                dojo.place( this.format_block( 'jstpl_blue_tile', {
                    tile_id: tile_id,
                    bkgPos: backgroundPosition
                } ) , 'tiles' );
            }
            

            this.placeOnObject( tile_id, 'overall_player_board_'+player );
            this.slideToObject( tile_id, 'placement_square_'+x+'_'+y ).play(); 

        },



        addOverlay ()
        {
            let backgroundPosition = 'background-position: 0px 0px';

            dojo.place( this.format_block( 'jstpl_billboard_content', {
                bkgPos: backgroundPosition
            } ) , 'overlay_billboard_1' );           
        },





        addLastMoveIndicator: function( last_move_x, last_move_y, last_move_id, N )
        {  
            //console.log( 'addLastMoveIndicator: function(  )' );

            if ( last_move_id == 99999 ) // NO TILES PLACED YET
            {
                //console.log( 'last_move_id == 99999' );

                return;
            }


            //
            //  REMOVE PREVIOUS LAST MOVE INDICATOR
            //
            //N_str = '' + N;

            last_move_indicator_str = 'last_move_indicator' + '_' + N;

            //console.log( last_move_indicator_str );

            dojo.query( '.' + last_move_indicator_str ).removeClass( last_move_indicator_str );


            let backgroundPositionLMI = 'background-position: 0px 0px';   

            let last_move_id_str = last_move_id.toString ( );

            //console.log( 'last_move_x, last_move_y, last_move_id_str: ' + last_move_x + ', ' + last_move_y + ', ' + last_move_id_str );
              
            dojo.place( this.format_block( 'jstpl_last_move_indicator', {
                last_move_id: last_move_id_str,
                bkgPos: backgroundPositionLMI
            } ) , 'tiles' );

            this.placeOnObject( last_move_id_str, 'placement_square_'+last_move_x+'_'+last_move_y ); 
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

                    this.highlightSelectableSquares ( args.args.selectableSquares ); 
    
                    break;

                case 'firstMoveChoice':
                
                    this.highlightSelectableSquares ( args.args.selectableSquares ); 
                            
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
					case 'firstMoveChoice':
						this.addActionButton( 'makeChoice_button', _('Switch colors'), 'onMakeChoice' ); 
                    break;
                }
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods
        
        //
        //
        // HIGHLIGHT SELECTABLE SQUARES 
        //
        //
        highlightSelectableSquares: function( selectableSquares ) 
        {
            console.log( 'highlightSelectableSquares' );


            //
            // Remove old selectable squares and add new selectable squares
            //
            dojo.query( '.selectableSquare' ).removeClass( 'selectableSquare' );

            N = this.gamedatas.board_size;
            
            if (!this.isSpectator)
            {
                for( var x in selectableSquares )
                {
                    for( var y in selectableSquares [ x ] )
                    {        
                        console.log( 'Adding selectableSquare class' );

                        dojo.addClass( 'clickable_square_'+x+'_'+y, 'selectableSquare' );
                    }            
                }
            }
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
            // Note: "clicked_square_x_y"
            var coords = evt.currentTarget.id.split('_');
            var x_clicked_square = coords[2];               // Raw coordinates
            var y_clicked_square = coords[3];               // (0,0) is lower left cell for both players


            if (   dojo.hasClass( 'clickable_square_'+x_clicked_square+'_'+y_clicked_square, 'selectableSquare'   )  )          // Select square                                 
            {            
                if  (   this.checkAction( 'selectSquare' ) )
                {                
                    //console.log("selectSquare ajax call");    

                    this.ajaxcall( "/hadron/hadron/selectSquare.html", {   // Place checker
                        x:x_clicked_square,
                        y:y_clicked_square
                    }, this, function( result ) {} );
                }
            } 
        },
     


		//If the player has decided to switch colors
        onMakeChoice: function ()
        {
			this.ajaxcall( '/hadron/hadron/makeChoice.html', { lock:true }, this, function( result ) {} );
		},

        
                
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your hadron.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            dojo.subscribe( 'tilePlaced', this, "notif_tilePlaced" );

            dojo.subscribe( 'tilePlacedHistory', this, "notif_tilePlacedHistory" );

            dojo.subscribe( 'lastMoveIndicator', this, "notif_lastMoveIndicator" );

            dojo.subscribe( 'backendMessage', this, "notif_backendMessage" );

            dojo.subscribe( 'playerPanel', this, 'notif_playerPanel');

            dojo.subscribe( 'newScores', this, "notif_newScores" );


            //Swapping colors notif
            dojo.subscribe( 'swapColors', this, "notif_swapColors" );
            this.notifqueue.setSynchronous( 'swapColors', 10 );
                   
        },  
        
        // TODO: from this point and below, you can write your game notifications handling methods
        

        notif_tilePlaced: function( notif )
        {            
            // Remove current possible moves (makes the board more clear)
            dojo.query( '.selectableSquare' ).removeClass( 'selectableSquare' );        
        
            this.addTileOnBoard( notif.args.x, notif.args.y, notif.args.player_id, notif.args.tile_id )

            //console.log("addTileOnBoard"+" "+notif.args.x+" "+notif.args.y+" "+notif.args.player_id+" "+notif.args.tile_id);
        },
        

        
        notif_tilePlacedHistory: function ( notif )
        {
            //console.log("notif_checkerPlacedHistory");
        },



        notif_lastMoveIndicator: function( notif )
        {
            //console.log("notif_lastMoveIndicator");

            // Remove current possible moves (makes the board more clear)
            dojo.query( '.selectableSquare' ).removeClass( 'selectableSquare' );        
        
            if (this.prefs[103].value == 1)
            {
                this.addLastMoveIndicator ( notif.args.x, notif.args.y, notif.args.id, this.gamedatas.board_size );
                   
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
            var placed_tiles = notif.args.placed_tiles;
            var player_id = notif.args.player_id;
            dojo.byId("placed-tiles_p"+player_id).innerHTML = placed_tiles;
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
			//Change/rafraichit les couleurs des joueurs
			this.gamedatas.players[ notif.args.player_id ].color = notif.args.player_color;
			dojo.query($('player_name_'+notif.args.player_id)).style('color', '#'+notif.args.player_color);
			
			//Notify his new color to the player
			if(notif.args.player_id==this.player_id)
			{
				this.showMessage( _('You are now playing <span style="color:#${player_color}">${player_color_name}</span>').replace('${player_color}', 
                    notif.args.player_color).replace('${player_color_name}', _( notif.args.player_colorname ) ), 'info' );
			}


            if (notif.args.player_color == "dc0000") {
                dojo.query($('icon-placed-tiles_'+notif.args.player_id)).removeClass('icon_placed_tiles_2b7aff');
                dojo.query($('icon-placed-tiles_'+notif.args.player_id)).addClass('icon_placed_tiles_dc0000');
            } else {
                dojo.query($('icon-placed-tiles_'+notif.args.player_id)).removeClass('icon_placed_tiles_dc0000');
                dojo.query($('icon-placed-tiles_'+notif.args.player_id)).addClass('icon_placed_tiles_2b7aff');
            }
            
            
            for (var player_id in this.gamedatas.players) {
                document.querySelector("#player_name_" + player_id + " a").style.color = "#" + this.gamedatas.players[player_id].color;
            }


            dojo.byId("placed-tiles_p"+notif.args.player_id).innerHTML = notif.args.placed_tiles;
        }
   });             
});
