/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Zola implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * zola.js
 *
 * Zola user interface script
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
    return declare("bgagame.zola", ebg.core.gamegui, {
        constructor: function(){
            //console.log('zola constructor');
              
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;
            //this.myId = 0;

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
            //console.log( "Starting game setup" );

            //
            // Player panel
            //
            for ( var player_id in gamedatas.players )
            {
                // Setting up players boards if needed - i.e. player_panel
                var player_board_div = $('player_board_'+player_id);
                //alert(player_board_div.id);


                //console.log(gamedatas.players[player_id]['color'] + postfix);

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


            // display player panel
            for (var keyPlayerId in gamedatas.remainingCheckers) 
            {
                dojo.byId("remaining-checkers_p"+keyPlayerId).innerHTML = gamedatas.remainingCheckers[keyPlayerId];
            }

            
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
                    //console.log( "x, y, player, checker_id" + " " + square.x + " " + square.y + " " +  square.player + " " +  square.ch_id );

                    this.addCheckerOnBoard( square.x, square.y, square.player, square.ch_id );

                }            
            }
            
            dojo.query( '.square'+gamedatas.board_size ).connect( 'onclick', this, 'onClickedSquare' );        
            

            // TODO: Set up your game interface here, according to "gamedatas"
            
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            //Avoid loading useless boards
            switch(gamedatas.board_size)
            {
				case 6:
					this.dontPreloadImage( 'Zola_backdrop_8x8.png' );
				    break;
				
				case 8:
					this.dontPreloadImage( 'Zola_backdrop.png' );
				    break;
			}
            //console.log( "Ending game setup" );
        },
       

         addOverlay ()
        {
            let backgroundPosition = 'background-position: 0px 0px';
            
            dojo.place( this.format_block( 'jstpl_billboard_content', {
                bkgPos: backgroundPosition
            } ) , 'overlay_billboard_1' );
            

            //console.log( "Adding overlay" );
        },


        addCheckerOnBoard: function( x, y, player, checker_id )
        {  

            //console.log( 'board_size = ' + this.gamedatas.board_size );

            let backgroundPosition = 'background-position: ';

            if ( checker_id < 100 )                                                  // Red checkers have normal checker indices values
            {

                if ( this.gamedatas.board_size == 6 )
                {
                    horOffset = -(    ( checker_id % 6 ) * 103    );
                    vertOffset = -(     Math.trunc (  checker_id / 6  ) * 103    ) - 1; 
                }
                else
                {
                    horOffset = -(    ( checker_id % 8 ) * 74    );
                    vertOffset = -(     Math.trunc (  checker_id / 8  ) * 74    ); 
                }

                //console.log( 'checker_id = ' + checker_id );
                //console.log( 'horOffset = ' + horOffset );
                //console.log( 'vertOffset = ' + vertOffset );

                backgroundPosition += horOffset + "px " + vertOffset + "px";
            
                dojo.place( this.format_block( 'jstpl_red_checker', {
                    checker_id: checker_id,
                    bkgPos: backgroundPosition
                } ) , 'checkers' );
        
            }
            else
            {
                if ( this.gamedatas.board_size == 6 )
                {
                    horOffset = -(    ( (checker_id-100) % 6 ) * 103    );
                    vertOffset = -(     Math.trunc (  (checker_id-100) / 6  ) * 103    ) - 1;
                    //vertOffset = -(     Math.trunc (  (checker_id-100) / 3  ) * 103    ) - 1;
            }
                else
                {
                    horOffset = -(    ( (checker_id-100) % 8 ) * 74    );
                    vertOffset = -(     Math.trunc (  (checker_id-100) / 8  ) * 74    );
                    //vertOffset = -(     Math.trunc (  (checker_id-100) / 8  ) * 74    ) - 1;
            }

                backgroundPosition += horOffset + "px " + vertOffset + "px";
            
                dojo.place( this.format_block( 'jstpl_black_checker', {
                    checker_id: checker_id,
                    bkgPos: backgroundPosition
                } ) , 'checkers' );
        
            }

           
            this.placeOnObject( checker_id, 'overall_player_board_'+player );
            this.slideToObject( checker_id, 'square_'+x+'_'+y ).play(); 
   
            //console.log( 'x_oriented, y_oriented = ' + " " + x_oriented + " " + y_oriented);
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
                
                    this.highlightSelectedChecker ();
                                        
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
            //console.log( 'Leaving state: '+stateName );
            
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

        
                for( var x in selectableCheckers )
                {
                    for( var y in selectableCheckers [ x ] )
                    {        
                        //console.log( 'Adding selectableChecker class' );

                        dojo.addClass( 'square_'+x+'_'+y, 'selectableChecker' );
                    }            
                }
            }

                      
            // this.addTooltipToClass( 'selectableChecker', '', _('Select this checker') );
        },
        

        highlightSelectedChecker: function ()
        {
            //console.log( 'highlightSelectedChecker' );

            
            if (!this.isSpectator)
            {
                for( var i in this.gamedatas.board )
                {
                    var square = this.gamedatas.board[i];

                    if( square.is_sel == 1 )
                    {
                        //console.log( 'square.is_sel == 1');

                        var x = square.x;
                        var y = square.y;


                        dojo.addClass( 'square_'+x+'_'+y, 'selectedChecker' );
                    }
                }            
            }

            return;
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
                for( var x in selectableDestinations )
                {
                    for( var y in selectableDestinations [ x ] )
                    {
                        //console.log( 'dojo.addClass selectableDestination to ' + x + ' ' + y );
            
                        dojo.addClass( 'square_'+x+'_'+y, 'selectableDestination' );
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
            var x_clicked_square = coords[1];               // Raw coordinates
            var y_clicked_square = coords[2];               // (0,0) is lower left square for both players


            if (   dojo.hasClass( 'square_'+x_clicked_square+'_'+y_clicked_square, 'selectableChecker'   )  )                   // SELECT CHECKER                                 
            {            
                if  (   this.checkAction( 'selectChecker' ) )
                {                
                    //console.log("selectChecker ajax call");    

                    this.ajaxcall( "/zola/zola/selectChecker.html", {   // Select checker
                        x:x_clicked_square,
                        y:y_clicked_square
                    }, this, function( result ) {} );
                }
            } 
            else if (     (  this.gamedatas.gamestate.name == "selectDestination"  )
            &&  (  ! dojo.hasClass('square_'+x_clicked_square+'_'+y_clicked_square, 'selectableDestination')  )    )    // UNSELECT CHECKER                           
            {    
                if (   this.checkAction( 'selectDestination' ) )
                {        
                        //console.log("checkAction selectDestination ok - unselecting destination");    
                   
                    this.ajaxcall( "/zola/zola/unselectChecker.html", {     // Unselect checker
                    }, this, function( result ) {} );
                }
            } 
            else if (   dojo.hasClass( 'square_'+x_clicked_square+'_'+y_clicked_square, 'selectableDestination' )   )           // SELECT DESTINATION
            {  

                //console.log("This square is a possible destination.");  

                if (   this.checkAction ( 'selectDestination' )   )
                {       
                    //console.log("checkAction selectDestination ok - selecting destination - MOVE CHECKER");    
                    this.ajaxcall( "/zola/zola/selectDestination.html", {     // Move checker
                        x:x_clicked_square,
                        y:y_clicked_square
                    }, this, function( result ) {} );
                }
            } 
    
        },


		//If the player has decided to switch colors
        onMakeChoice: function ()
        {
			this.ajaxcall( '/zola/zola/makeChoice.html', { lock:true }, this, function( result ) {} );
		},


        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your zola.game.php file.
        
        */
        setupNotifications: function()
        {
            //console.log( 'notifications subscriptions setup' );
            
            //console.log( 'Setting up notification subscriptions.' );
            
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
        
        notif_checkerSelected: function( notif )
        {            
            if (!this.isSpectator)
            {
                var x = notif.args.x;
                var y = notif.args.y;

                //console.log("notif_checkerSelected"+" "+x+" "+y);

                dojo.addClass( 'square_'+x+'_'+y, 'selectedChecker' );

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

            this.slideToObject( ''+notif.args.checker_id, 'square_'+notif.args.x_new+'_'+notif.args.y_new ).play();    
            
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
            var remaining_checkers = notif.args.remaining_checkers;
            var player_id = notif.args.player_id;
            dojo.byId("remaining-checkers_p"+player_id).innerHTML = remaining_checkers;
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

            if (notif.args.player_color == "ff0000") {
                dojo.query($('icon-remaining-checkers_'+notif.args.player_id)).removeClass('icon_remaining_checkers_000000');
                dojo.query($('icon-remaining-checkers_'+notif.args.player_id)).addClass('icon_remaining_checkers_ff0000');
            } else {
                dojo.query($('icon-remaining-checkers_'+notif.args.player_id)).removeClass('icon_remaining_checkers_ff0000');
                dojo.query($('icon-remaining-checkers_'+notif.args.player_id)).addClass('icon_remaining_checkers_000000');
            }
            
            for (var player_id in this.gamedatas.players) {
                document.querySelector("#player_name_" + player_id + " a").style.color = "#" + this.gamedatas.players[player_id].color;
            }


            dojo.byId("remaining-checkers_p"+notif.args.player_id).innerHTML = notif.args.remainingcheckers;

	    }
                
   });             
});
