/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Fractal implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * fractal.js
 *
 * Fractal user interface script
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
    return declare("bgagame.fractal", ebg.core.gamegui, {
        constructor: function(){
            console.log('fractal constructor');
              
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
            for (var keyPlayerId in gamedatas.placedTiles) 
            {
                //console.log( "placedTiles" + gamedatas.placedTiles[keyPlayerId] );

                dojo.byId("placed-tiles_p"+keyPlayerId).innerHTML = gamedatas.placedTiles[keyPlayerId];
            }

            
            //
            // Setting up player boards
            //   
            for( var i in gamedatas.board )
            {
                var square = gamedatas.board[i];

                if( square.player !== null )
                {
                    //console.log( " " + square.u + " " + square.v + " " +  square.player + " " +  square.tile_info );

                    this.addTileOnBoard( square.u, square.v, square.player, square.tile_info );
                }            
            }
            

            //
            // ON CLICKED SQUARE 
            //   
            
            // 
            // LARGE ECCENTRIC CELLS 
            //
            dojo.query( '.clickable_square_1000_1002' ).connect( 'onclick', this, 'onClickedSquare' );        
            dojo.query( '.clickable_square_1000_1003' ).connect( 'onclick', this, 'onClickedSquare' );        
            dojo.query( '.clickable_square_1000_1004' ).connect( 'onclick', this, 'onClickedSquare' );        
            dojo.query( '.clickable_square_1001_1004' ).connect( 'onclick', this, 'onClickedSquare' );        
            dojo.query( '.clickable_square_1002_1004' ).connect( 'onclick', this, 'onClickedSquare' );        
            dojo.query( '.clickable_square_1003_1003' ).connect( 'onclick', this, 'onClickedSquare' );        
            dojo.query( '.clickable_square_1004_1002' ).connect( 'onclick', this, 'onClickedSquare' );        
            dojo.query( '.clickable_square_1004_1001' ).connect( 'onclick', this, 'onClickedSquare' );        
            dojo.query( '.clickable_square_1004_1000' ).connect( 'onclick', this, 'onClickedSquare' );        
            dojo.query( '.clickable_square_1003_1000' ).connect( 'onclick', this, 'onClickedSquare' );        
            dojo.query( '.clickable_square_1002_1000' ).connect( 'onclick', this, 'onClickedSquare' );        
            dojo.query( '.clickable_square_1001_1001' ).connect( 'onclick', this, 'onClickedSquare' );        
          
            
            // 
            // MEDIUM ECCENTRIC CELLS 
            //
            dojo.query( '.clickable_square_103_105' ).connect( 'onclick', this, 'onClickedSquare' );        
            dojo.query( '.clickable_square_103_106' ).connect( 'onclick', this, 'onClickedSquare' );        
            dojo.query( '.clickable_square_103_107' ).connect( 'onclick', this, 'onClickedSquare' );        
            dojo.query( '.clickable_square_104_107' ).connect( 'onclick', this, 'onClickedSquare' );        
            dojo.query( '.clickable_square_105_107' ).connect( 'onclick', this, 'onClickedSquare' );        
            dojo.query( '.clickable_square_106_106' ).connect( 'onclick', this, 'onClickedSquare' );        
            dojo.query( '.clickable_square_107_105' ).connect( 'onclick', this, 'onClickedSquare' );        
            dojo.query( '.clickable_square_107_104' ).connect( 'onclick', this, 'onClickedSquare' );        
            dojo.query( '.clickable_square_107_103' ).connect( 'onclick', this, 'onClickedSquare' );        
            dojo.query( '.clickable_square_106_103' ).connect( 'onclick', this, 'onClickedSquare' );        
            dojo.query( '.clickable_square_105_103' ).connect( 'onclick', this, 'onClickedSquare' );        
            dojo.query( '.clickable_square_104_104' ).connect( 'onclick', this, 'onClickedSquare' );        
          
            // 
            // MEDIUM HEXAGONAL CELL 
            //
            dojo.query( '.clickable_square_100_100' ).connect( 'onclick', this, 'onClickedSquare' );        
            

            // 
            // SMALL HEXAGONAL CELL 
            //
            dojo.query( '.clickable_square_0_0' ).connect( 'onclick', this, 'onClickedSquare' );        
          
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
        },
       




        //
        // ADD TILE ON BOARD
        //
        //      tile_info = A+BB+X_Y,X_Y,X_Y...
        //          A:  SIZE: 1 = small tile, 2 = medium tile, 3 = large tile 
        //          BB: SINGLE NUMBER OFFSET INTO TILE TABLE.  PLAYER COLOR WILL DETERMINE WHETHER UPPER HALF OR LOWER HALF OF TABLE.
        //          X_Y = COORDINATES OF SURROUNDING TILE
        //
        addTileOnBoard: function( u, v, player_id, tile_info )
        {   
            //console.log( 'tile_id = ' + tile_id );
            //console.log( " " + u + " " + v + " " +  player + " " +  tile_id );


            tile_info_components = tile_info.split ( '+' );

            tile_size = parseInt ( tile_info_components [ 0 ] );    // convert from string to into

            tile_table_offset = parseInt ( tile_info_components [ 1 ] );    // convert from string to into

            //tile_adjacent_tiles_str = tile_info_components [ 2 ];

            //console.log ( 'u, v = ' + u + ", " + v );

            //console.log( "tile_info_components = " + tile_size + " " + tile_table_single_offset );

            //console.log( "tile_info_components = " + tile_size + " " + tile_table_single_offset + " " + tile_adjacent_tiles  );


             /*
             tile_size = Math.floor ( tile_id / 10000 );

            coords = tile_id % 10000;


            x = Math.floor ( coords / 100 );

            y = coords % 100;
            */


            let backgroundPosition = 'background-position: ';

            switch( tile_size )
            {
                case 1: // Small
               
                    console.log ( 'u, v = ' + u + ", " + v );

                    console.log( "tile_info_components = " + tile_size + " " + tile_table_offset );

                    x = tile_table_offset % 15;

                    if ( this.gamedatas.players[player_id]['color'] == '000000' )
                    {
                        y = Math.floor ( tile_table_offset / 15 ) + 5;
                    }
                    else 
                    {
                        y = Math.floor ( tile_table_offset / 15 );
                    }

                    console.log ( 'x, y = ' + x + ", " + y );


                    horOffset = -x * 15.96 - 1;        
                    //horOffset = -x * 16 - 1;        
                    //horOffset = -x * 15.9 - 1;        
                    //horOffset = -x * 15.8 - 1;        
                    //horOffset = -x * 16.2 - 1;        
                    //horOffset = -x * 15 - .5;        


                    verOffset = -y * 18.45 - .75;       
                    //verOffset = -y * 18.4 - .75;       
                    //verOffset = -y * 18.3 - .5;       
 

                    backgroundPosition += horOffset + "px " + verOffset + "px";   
                

                    //
                    // Just to give tile_id a unique value for dojo place. 
                    // 
                    // Will never be clicking on, moving, or removing a tile after it's placed. 
                    //
                    tile_id = 100 * u + v;



                    dojo.place( this.format_block( 'jstpl_tile_S', {
                        tile_id: tile_id,
                        bkgPos: backgroundPosition
                    } ) , 'tiles' );



                
                    //this.placeOnObject( tile_id, 'overall_player_board_'+player_id );
                    //this.slideToObject( tile_id, 'clickable_square_'+u+'_'+v ).play(); 



                
                    break;
  


                case 2: // Medium

                    //console.log ( 'u, v = ' + u + ", " + v );

                    //console.log( "tile_info_components = " + tile_size + " " + tile_table_offset );

                    x = tile_table_offset % 11;

                    if ( this.gamedatas.players[player_id]['color'] == '000000' )
                    {
                        y = Math.floor ( tile_table_offset / 11 ) + 6;
                    }
                    else 
                    {
                        y = Math.floor ( tile_table_offset / 11 );
                    }

                    //console.log ( 'x, y = ' + x + ", " + y );





                    horOffset = -x * 45.99 - 1;     
                    //horOffset = -x * 45.99 - .95;     
                    //horOffset = -x * 45.99 - .9;     
                    //horOffset = -x * 46 - .885;     
                    //horOffset = -x * 45.99 - .885;     
                    //horOffset = -x * 46.01 - .885;     



                    //horOffset = -x * 46.01 - .88;     
                    //horOffset = -x * 46.01 - .89;     
                    //horOffset = -x * 46.01 - .90;     
                    //horOffset = -x * 46.01 - .93;     
                    //horOffset = -x * 46.01 - .87;     
                    //horOffset = -x * 46.01 - 1;     
                    //horOffset = -x * 46.01 - .75;     
                    //horOffset = -x * 46.01 - .5;     
                    


                    //horOffset = -x * 46.02 - .5;        
                    //horOffset = -x * 46 - .5;        
                    //horOffset = -x* 45 - .5;        
                    //horOffset = -x* 45 - 1.5;        

                    verOffset = -y * 53.1 - .5;       
                    //verOffset = -y * 53.08 - .5;       
                    //verOffset = -y * 53.06 - .5;       
                    //verOffset = -y * 53.03 - .25;       
                    //verOffset = -y * 53 + .25;       
                    //verOffset = -y * 51.96 + .75;       
 

                    backgroundPosition += horOffset + "px " + verOffset + "px";   
                

                    //
                    // Just to give tile_id a unique value for dojo place. 
                    // 
                    // Will never be clicking on, moving, or removing a tile after it's placed. 
                    //
                    tile_id = 1000 * u + v;



                    dojo.place( this.format_block( 'jstpl_tile_M', {
                        tile_id: tile_id,
                        bkgPos: backgroundPosition
                    } ) , 'tiles' );



                
                   // this.placeOnObject( tile_id, 'overall_player_board_'+player_id );
                   // this.slideToObject( tile_id, 'clickable_square_'+u+'_'+v ).play(); 



                
                    break;
  


                case 3: // Large

                    x = tile_table_offset;

                    if ( this.gamedatas.players[player_id]['color'] == '000000' )
                    {
                        y = 1;
                    }
                    else 
                    {
                        y = 0;
                    }

                    horOffset = -x* 136.05 - 1.5;        // 

                    verOffset = -y * 157.5 + .75;       // 
 

                    //console.log ( 'x, y = ' + x + ", " + y );


                    backgroundPosition += horOffset + "px " + verOffset + "px";   
                

                    //
                    // Just to give tile_id a unique value for dojo place. 
                    // 
                    // Will never be clicking on, moving, or removing a tile after it's placed. 
                    //
                    tile_id = 10000 * u + v;



                    dojo.place( this.format_block( 'jstpl_tile_L', {
                        tile_id: tile_id,
                        bkgPos: backgroundPosition
                    } ) , 'tiles' );



                
                    //this.placeOnObject( tile_id, 'overall_player_board_'+player_id );
                    //this.slideToObject( tile_id, 'clickable_square_'+u+'_'+v ).play(); 




                    break;
            }



  
            this.placeOnObject( tile_id, 'overall_player_board_'+player_id );
            this.slideToObject( tile_id, 'clickable_square_'+u+'_'+v ).play(); 



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
                case 'placeTile':

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


        highlightSelectableCells: function( selectableCells )
        {
            //
            // Remove old selectable checkers and add new selectable checkers
            //
            dojo.query( '.selectable_cell' ).removeClass( 'selectable_cell' );


            if (!this.isSpectator)
            {
                //var current_player_id = this.player_id;   
                //var current_player = this.gamedatas.players [ current_player_id ];               
                //var current_color = current_player.color;

        
                for( var u in selectableCells )
                {
                    for( var v in selectableCells [ u ] )
                    {        
                        //console.log( 'Adding selectable_cell class to '+ u + ' ' + v);

                        dojo.addClass( 'clickable_square_'+u+'_'+v, 'selectable_cell' );
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


            var u_clicked_square_str = coords[2];               
            var v_clicked_square_str = coords[3];               


            var u_clicked_square = parseInt ( u_clicked_square_str );   
            
            var v_clicked_square = parseInt ( v_clicked_square_str );               

            //console.log("evt.currentTarget.id = " + evt.currentTarget.id );    
            //console.log("u, v = " + u_clicked_square, + " " + v_clicked_square );    

            if (   dojo.hasClass( 'clickable_square_'+u_clicked_square+'_'+v_clicked_square, 'selectable_cell'   )  )          // Select cell                                 
            {  
                if  (   this.checkAction( 'placeTile' ) )
                {                
                    //console.log("placeTile ajax call");    

                    this.ajaxcall( "/fractal/fractal/placeTile.html", {   // Place checker
                        u:u_clicked_square,
                        v:v_clicked_square
                    }, this, function( result ) {} );
                }
            } 
        },
     

		//If the player has decided to switch colors
        onMakeChoice: function ()
        {
			this.ajaxcall( '/fractal/fractal/makeChoice.html', { lock:true }, this, function( result ) {} );
		},

        
        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your fractal.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            dojo.subscribe( 'tilePlaced', this, "notif_tilePlaced" );

            dojo.subscribe( 'tilePlacedHistory', this, "notif_tilePlacedHistory" );

            dojo.subscribe( 'newScores', this, "notif_newScores" );

            dojo.subscribe( 'playerPanel', this, 'notif_playerPanel');

            dojo.subscribe( 'backendMessage', this, "notif_backendMessage" );


            //Swapping colors notif
            dojo.subscribe( 'swapColors', this, "notif_swapColors" );
            this.notifqueue.setSynchronous( 'swapColors', 10 );
                   
        },  
        
        notif_tilePlaced: function( notif )
        {      
            // Remove current possible moves (makes the board more clear)
            dojo.query( '.selectable_cell' ).removeClass( 'selectable_cell' );        
        
            this.addTileOnBoard( notif.args.u, notif.args.v, notif.args.player_id, notif.args.tile_info )

            //console.log("addCheckerOnBoard"+" "+notif.args.u+" "+notif.args.v+" "+notif.args.player_id+" "+notif.args.checker_id);
        },
        
        
        notif_newScores: function( notif )
        {
            for( var player_id in notif.args.scores )
            {
                var newScore = notif.args.scores[ player_id ];
                this.scoreCtrl[ player_id ].toValue( newScore );
            }
        },


        notif_tilePlacedHistory: function ( notif )
        {
            //console.log("notif_checkerPlacedHistory");
        },


        notif_playerPanel: function ( notif ) {
            var placed_tiles = notif.args.placed_tiles;
            var player_id = notif.args.player_id;
            dojo.byId("placed-tiles_p"+player_id).innerHTML = placed_tiles;
        },


        notif_backendMessage: function( notif )
        {
            //console.log("Inside notif_backendMessage");
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


            if (notif.args.player_color == "000000") {
                dojo.query($('icon-placed-tiles_'+notif.args.player_id)).removeClass('icon_placed_tiles_00a400');
                dojo.query($('icon-placed-tiles_'+notif.args.player_id)).addClass('icon_placed_tiles_000000');
            } else {
                dojo.query($('icon-placed-tiles_'+notif.args.player_id)).removeClass('icon_placed_tiles_000000');
                dojo.query($('icon-placed-tiles_'+notif.args.player_id)).addClass('icon_placed_tiles_00a400');
            }
            
            
            for (var player_id in this.gamedatas.players) {
                document.querySelector("#player_name_" + player_id + " a").style.color = "#" + this.gamedatas.players[player_id].color;
            }


            dojo.byId("placed-tiles_p"+notif.args.player_id).innerHTML = notif.args.placed_tiles;
        }
   });             
});
