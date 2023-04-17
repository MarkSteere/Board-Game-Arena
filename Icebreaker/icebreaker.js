/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Icebreaker implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * icebreaker.js
 *
 * Icebreaker user interface script
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
    return declare("bgagame.icebreaker", ebg.core.gamegui, {
        constructor: function(){
            console.log('icebreaker constructor');
              
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
            // Player panel
            //
            for ( var player_id in gamedatas.players )
            {
                // Setting up player_panel
                var player_board_div = $('player_board_'+player_id);
                //alert(player_board_div.id);


                dojo.place(
                    this.format_block(
                        'jstpl_player_board',
                        {
                            player_id : player_id,
                        }
                    )
                    , player_board_div
                );
            }


            //
            // Display player panel
            //
            var N = gamedatas.board_size;
            var majority_icebergs;

            if (  N == 5  )
                majority_icebergs = 28;
            else if (  N == 6  )
                majority_icebergs = 43;
            else if (  N == 7  )
               $majority_icebergs = 61;
            else    // N = 8
                majority_icebergs = 82;

            for (var keyPlayerId in gamedatas.capturedCheckers) 
            {
                //console.log( "capturedCheckers" + gamedatas.capturedCheckers[keyPlayerId] );

                dojo.byId("captured-checkers_p"+keyPlayerId).innerHTML = gamedatas.capturedCheckers[keyPlayerId] + " / " + majority_icebergs;
            }

            
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
            
            dojo.query( '.square'+gamedatas.board_size ).connect( 'onclick', this, 'onClickedSquare' );        
            
            // TODO: Set up your game interface here, according to "gamedatas"


 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
        },

       
         addOverlay ()
        {
            let backgroundPosition = 'background-position: 0px 0px';
            
            dojo.place( this.format_block( 'jstpl_billboard_content', {
                bkgPos: backgroundPosition
            } ) , 'overlay_billboard_1' );
            

            //console.log( "Adding overlay" );
        },


        //
        // Add checker on board
        //
        addCheckerOnBoard: function( u, v, player, checker_id )
        {   

            //console.log( 'checker_id = ' + checker_id );
            //console.log( " " + u + " " + v + " " +  player + " " +  checker_id );

            let backgroundPosition = 'background-position: ';

            if ( checker_id <= 999 )                                // Iceberg 
            {
                if ( this.gamedatas.board_size == 5 )
                {
                    horOffset = -134;
                    verOffset = 0;
                }
                else if ( this.gamedatas.board_size == 6 )
                {
                    horOffset = -110;
                    verOffset = -1;
                }
                else if ( this.gamedatas.board_size == 7 )
                {
                    horOffset = -92;
                    verOffset = 0;
                }
                else                   // board size = 8
                {
                    horOffset = -78 - 1;
                    verOffset = -1;
                }
               
                //console.log( 'checker_id = ' + checker_id );
                //console.log( 'horOffset = ' + horOffset );
                //console.log( 'vertOffset = ' + vertOffset );
            }
            else if ( checker_id >= 1000 && checker_id <= 1999 )       // Red checker 
            {
                if ( this.gamedatas.board_size == 5 )
                {
                    horOffset = 0;
                    verOffset = 0;
                }
                else if ( this.gamedatas.board_size == 6 )
                {
                    horOffset = 0;
                    verOffset = -1;
                }
                else if ( this.gamedatas.board_size == 7 )
                {
                    horOffset = -1;
                    verOffset = -1;
                }
                else                   // board size = 8
                {
                    horOffset = 0;
                    verOffset = -1;
                }
               
                //console.log( 'checker_id = ' + checker_id );
                //console.log( 'horOffset = ' + horOffset );
                //console.log( 'vertOffset = ' + vertOffset );
            }
            else                                                    // Black checker 
            {
                if ( this.gamedatas.board_size == 5 )
                {
                    horOffset = -67;
                    verOffset = 0;
                }
                else if ( this.gamedatas.board_size == 6 )
                {
                    horOffset = -55;
                    verOffset = -1;
                }
                else if ( this.gamedatas.board_size == 7 )
                {
                    horOffset = -46 - 1;
                    verOffset = -1;
                }
                else                   // board size = 8
                {
                    horOffset = -39 - 1;
                    verOffset = -1;
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



            if ( player == 0 )
                this.placeOnObject( checker_id, 'square_'+u+'_'+v );
            else
            {
                this.placeOnObject( checker_id, 'overall_player_board_'+player );
                this.slideToObject( checker_id, 'square_'+u+'_'+v ).play(); 
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
                case 'selectChecker':

                    this.highlightSelectableCheckers ( args.args.selectableCheckers );
    
                    break;
               
                case 'selectDestination':
                
                    //this.highlightSelectedChecker ();
                                        
                    this.highlightSelectableDestinations ( args.args.selectableDestinations );
            
                    break;
                       
                case 'firstMoveChoice':
                
                    this.highlightSelectableCheckers ( args.args.selectableCheckers );
                            
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
        
        highlightSelectableCheckers: function( selectableCheckers )
        {
            //
            // Remove old selectable checkers and add new selectable checkers
            //
            dojo.query( '.selectableChecker' ).removeClass( 'selectableChecker' );


            if (!this.isSpectator)
            {
                var current_player_id = this.player_id;   
                //var current_player = this.gamedatas.players [ current_player_id ];               
                //var current_color = current_player.color;

        
                for( var u in selectableCheckers )
                {
                    for( var v in selectableCheckers [ u ] )
                    {        
                        //console.log( 'Adding selectableChecker class' );

                        dojo.addClass( 'square_'+u+'_'+v, 'selectableChecker' );
                    }            
                }
            }

                      
            // this.addTooltipToClass( 'selectableChecker', '', _('Select this checker') );
        },


        highlightSelectableDestinations: function( selectableDestinations )
        {
            //console.log( 'Entered highlightSelectableDestinations' );

            //
            // Remove old selectable destinations and add new selectable destinations
            //
            dojo.query( '.selectableDestination' ).removeClass( 'selectableDestination' );

            
            if (!this.isSpectator)
            {
                for( var u in selectableDestinations )
                {
                    for( var v in selectableDestinations [ u ] )
                    {
                        //console.log( 'dojo.addClass selectableDestination to ' + x + ' ' + y );
            
                        dojo.addClass( 'square_'+u+'_'+v, 'selectableDestination' );
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
            console.log("onClickedSquare");  
            
            // Stop this event propagation
            evt.preventDefault();
            dojo.stopEvent( evt );
    
            // Get the clicked square x and y
            // Note: square id format is "square_X_Y"
            var coords = evt.currentTarget.id.split('_');
            var u_clicked_square = coords[1];               // Raw coordinates
            var v_clicked_square = coords[2];               // (0,0) is lower left square for both players


            if (   dojo.hasClass( 'square_'+u_clicked_square+'_'+v_clicked_square, 'selectableChecker'   )  )                   // SELECT CHECKER                                 
            {            
                if  (   this.checkAction( 'selectChecker' ) )
                {                
                    //console.log("selectChecker ajax call");    

                    this.ajaxcall( "/icebreaker/icebreaker/selectChecker.html", {   // Select checker
                        u:u_clicked_square,
                        v:v_clicked_square
                    }, this, function( result ) {} );
                }
            } 
            else if (     (  this.gamedatas.gamestate.name == "selectDestination"  )
                      &&  (  ! dojo.hasClass('square_'+u_clicked_square+'_'+v_clicked_square, 'selectableDestination')  )    )    // UNSELECT CHECKER                           
            {    
                if (   this.checkAction( 'selectDestination' ) )
                {        
                        //console.log("checkAction selectDestination ok - unselecting destination");    
                   
                    this.ajaxcall( "/icebreaker/icebreaker/unselectChecker.html", {     // Unselect checker
                    }, this, function( result ) {} );
                }
            } 
            else if (   dojo.hasClass( 'square_'+u_clicked_square+'_'+v_clicked_square, 'selectableDestination' )   )           // SELECT DESTINATION
            {  

                //console.log("This square is a possible destination.");  

                if (   this.checkAction ( 'selectDestination' )   )
                {       
                    //console.log("checkAction selectDestination ok - selecting destination - MOVE CHECKER");    
                    this.ajaxcall( "/icebreaker/icebreaker/selectDestination.html", {     // Move checker
                        u:u_clicked_square,
                        v:v_clicked_square
                    }, this, function( result ) {} );
                }
            } 
        },
     

		//If the player has decided to switch colors
        onMakeChoice: function ()
        {
			this.ajaxcall( '/icebreaker/icebreaker/makeChoice.html', { lock:true }, this, function( result ) {} );
		},


        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your icebreaker.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'Setting up notification subscriptions.' );
            
            dojo.subscribe( 'checkerSelected', this, "notif_checkerSelected" );

            dojo.subscribe( 'checkerUnselected', this, "notif_checkerUnselected" );

            dojo.subscribe( 'destinationSelected', this, "notif_destinationSelected" );

            dojo.subscribe( 'newScores', this, "notif_newScores" );

            dojo.subscribe( 'destinationSelectedHistory', this, "notif_destinationSelectedHistory" );

            dojo.subscribe( 'backendMessage', this, "notif_backendMessage" );

            dojo.subscribe( 'playerPanel', this, 'notif_playerPanel');


            //Swapping colors notif
            dojo.subscribe( 'swapColors', this, "notif_swapColors" );
            this.notifqueue.setSynchronous( 'swapColors', 10 );
                   
        },  
        
        // TODO: from this point and below, you can write your game notifications handling methods
        
        notif_checkerSelected: function( notif )
        {            
            if (!this.isSpectator)
            {
                var u = notif.args.u;
                var v = notif.args.v;

                //console.log("notif_checkerSelected"+" "+u+" "+v);

                dojo.addClass( 'square_'+u+'_'+v, 'selectedChecker' );

                dojo.query( '.selectableChecker' ).removeClass( 'selectableChecker' );      
            }       
        },
        

        notif_checkerUnselected: function( notif )
        {
            console.log("notif_checkerUnselected");

            dojo.query( '.selectedChecker' ).removeClass( 'selectedChecker' );      
            dojo.query( '.selectableDestination' ).removeClass( 'selectableDestination' );      
        },
        

        notif_destinationSelected: function( notif )
        {
            //console.log("notif_destinationSelected. SLIDE TO OBJECT");
            
            //var current_player_id = this.player_id;   

            dojo.query( '.selectedChecker' ).removeClass( 'selectedChecker' );      
            dojo.query( '.selectableDestination' ).removeClass( 'selectableDestination' );  

            this.slideToObject( ''+notif.args.checker_id, 'square_'+notif.args.u_new+'_'+notif.args.v_new ).play();    
            
            console.log("notif.args.captured_checker_id = " + notif.args.captured_checker_id);

            if ( notif.args.captured_checker_id !== null )
                this.slideToObjectAndDestroy( ''+notif.args.captured_checker_id, 'overall_player_board_'+notif.args.capturing_player_id, 1000, 0 ); 
        },

        
        notif_newScores: function( notif )
        {
            for( var player_id in notif.args.scores )
            {
                var newScore = notif.args.scores[ player_id ];
                this.scoreCtrl[ player_id ].toValue( newScore );
            }
        },

        notif_destinationSelectedHistory: function( notif )
        {
            console.log("notif_destinationSelectedHistory");

        },


        notif_backendMessage: function( notif )
        {
            //console.log("Inside notif_backendMessage");
        },


        notif_playerPanel: function ( notif ) {
            var captured_checkers = notif.args.captured_checkers;
            var player_id = notif.args.player_id;
            dojo.byId("captured-checkers_p"+player_id).innerHTML = captured_checkers;
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

            
            for (var player_id in this.gamedatas.players) {
                document.querySelector("#player_name_" + player_id + " a").style.color = "#" + this.gamedatas.players[player_id].color;
            }


            //dojo.byId("captured-checkers_p"+notif.args.player_id).innerHTML = 99;
            dojo.byId("captured-checkers_p"+notif.args.player_id).innerHTML = notif.args.capturedCheckers;

	    }
   });             
});
