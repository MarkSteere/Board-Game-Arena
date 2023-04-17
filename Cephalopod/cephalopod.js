/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Cephalopod implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * cephalopod.js
 *
 * Cephalopod user interface script
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
    return declare("bgagame.cephalopod", ebg.core.gamegui, {
        constructor: function(){
            console.log('cephalopod constructor');
              
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
            for (var keyPlayerId in gamedatas.placedDice) 
            {
                //console.log( "placedDice" + gamedatas.placedCheckers [ keyPlayerId ] );

                dojo.byId("placed-dice_p"+keyPlayerId).innerHTML = gamedatas.placedDice[keyPlayerId];
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
                    //console.log( "setup addDieOnBoard" + " " + square.x + " " + square.y + " " +  square.player + " " +  square.die_id );

                    this.addDieOnBoard ( square.x, square.y, square.player, square.die_id );
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
            dojo.query( '.square' ).connect( 'onclick', this, 'onClickedSquare' ); 
            
 
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
        // Add die on board
        //
        addDieOnBoard: function( x, y, player_id, die_id )
        {   
            //console.log( 'die_id = ' + die_id );

            pip_value = this.getPipValue ( die_id );


            //console.log( 'die_id = ' + die_id );
            //console.log ( "ADOB" + " " + x + " " + y + " " +  player_id + " " +  die_id );
            //console.log( 'pip value = ' + pip_value );



            let backgroundPosition = 'background-position: ';

            if ( die_id >= 10000 && die_id < 20000 )        // Black die 
            {
                verOffset = -1;
            }
            else                                            // Green die
            {
                verOffset = -95;
            }

            horOffset = -1 - (    ( pip_value - 1 ) * 94   );

            //console.log( 'horOffset = ' + horOffset );
            //console.log( 'verOffset = ' + verOffset );


            backgroundPosition += horOffset + "px " + verOffset + "px";   
                
            dojo.place( this.format_block( 'jstpl_die', {
                die_id: die_id,
                bkgPos: backgroundPosition
            } ) , 'dice' );


            die_id = String(die_id); // Repair by VictoriaLa


            this.placeOnObject( die_id, 'overall_player_board_'+player_id );
            this.slideToObject( die_id, 'square_'+x+'_'+y ).play(); 
        },



        addLastMoveIndicator: function( last_move_x, last_move_y, last_move_id )
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
            } ) , 'dice' );

            this.placeOnObject( last_move_id_str, 'square_'+last_move_x+'_'+last_move_y );      
        },




        getPipValue: function( die_id )
        {
            return Math.floor (    ( die_id % 10000 ) / 1000    );
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
                    
                case 'selectDie':
                
                    this.highlightSelectableDice ( args.args.tripleSetOfArgs );
                            
                    break;       
                    
                case 'finalizeThisCombination':
                
                    this.highlightSelectableDice ( args.args.tripleSetOfArgs );
                            
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
                    case 'finalizeThisCombination':
						this.addActionButton( 'finalizeThisCombination_button', _('Finish'), 'onFinalizeThisCombination' ); 
                    break;
                }
            }
        },        


        ///////////////////////////////////////////////////
        //// Utility methods
        
        highlightSelectableSquares: function( selectableSquares )
        {
            //
            // Remove old selectable squares and add new selectable squares
            //
            dojo.query( '.selectableSquare' ).removeClass( 'selectableSquare' );


            if (!this.isSpectator)
            {        
                for( var x in selectableSquares )
                {
                    for( var y in selectableSquares [ x ] )
                    {        
                        //console.log( 'Adding selectableSquare class to '+ x + ' ' + y);

                        dojo.addClass( 'square_'+x+'_'+y, 'selectableSquare' );
                    }            
                }
            }
                      
            // this.addTooltipToClass( 'selectableSquare', '', _('Select this square') );
        },


        highlightSelectedSquare: function( selectedSquares )
        {
            //
            // Remove old selected squares (should be just one) and add new selected squares
            //
            dojo.query( '.selectedSquares' ).removeClass( 'selectedSquares' );


            if (!this.isSpectator)
            {        
                for( var x in selectedSquares )
                {
                    for( var y in selectedSquares [ x ] )
                    {        
                        //console.log( 'Adding selectedSquare class to '+ x + ' ' + y);

                        dojo.addClass( 'square_'+x+'_'+y, 'selectedSquare' );
                    }            
                }
            }
                      
            // this.addTooltipToClass( 'selectedSquare', '', _('Selected square') );
        },


        highlightSelectableDice: function( tripleSetOfArgs )
        {
            //
            // Remove old selectable squares and add new selectable squares
            //
            dojo.query( '.selectableDie' ).removeClass( 'selectableDie' );


            if (!this.isSpectator)
            {  
                //
                // 1. HIGHLIGHT SELECTED SQUARE 
                //
                selected_square = tripleSetOfArgs [ 0 ];

                x = selected_square [ 0 ];
                y = selected_square [ 1 ];

                //console.log( 'x, y: ' + x + ' ' + y );

                dojo.addClass( 'square_' + x+ '_' + y, 'selectedSquare' );


                //
                // 2. HIGHLIGHT SELECTED DICE 
                //
                selected_dice = tripleSetOfArgs [ 1 ];

                for( var x in selected_dice )
                {
                    for( var y in selected_dice [ x ] )
                    {        
                        //console.log( 'Adding selected_die class to ' + x + ' ' + y );

                        dojo.addClass( 'square_'+x+'_'+y, 'selectedDie' );
                    }            
                }


                //
                // 3. HIGHLIGHT SELECTABLE DICE 
                //
                selectable_dice = tripleSetOfArgs [ 2 ];

                for( var x in selectable_dice )
                {
                    for( var y in selectable_dice [ x ] )
                    {        
                        //console.log( 'Adding selectableDie class to ' + x + ' ' + y );

                        dojo.addClass( 'square_'+x+'_'+y, 'selectableDie' );
                    }            
                }
            }
                      
            // this.addTooltipToClass( 'selectableSquare', '', _('Select this die') );
        },


        highlightSelectedDice: function( selectedDice )
        {
            //
            // Remove old selectable squares and add new selectable squares
            //
            dojo.query( '.selectedDie' ).removeClass( 'selectedDie' );


            if (!this.isSpectator)
            {        
                for( var x in selectedDice )
                {
                    for( var y in selectedDice [ x ] )
                    {        
                        //console.log( 'Adding selectedDie class to '+ x + ' ' + y);

                        dojo.addClass( 'square_'+x+'_'+y, 'selectedDie' );
                    }            
                }
            }
                      
            // this.addTooltipToClass( 'selectedDie', '', _('Selected die') );
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
            // Stop this event propagation
            evt.preventDefault();
            dojo.stopEvent( evt );
    
            // Get the clicked square u and v
            // Note: square id format is "square_U_V"
            var coords = evt.currentTarget.id.split('_');
            var x = coords[1];
            var y = coords[2];

                
            console.log("onClickedSquare "+" "+x+" "+y);
    
            if (   dojo.hasClass( 'square_'+x+'_'+y, 'selectableSquare'   )  )                      // SELECTABLE SQUARE                               
            {            
                //console.log("Has selectableSquare class");    

                if  (   this.checkAction( 'selectSquare' ) )
                {                
                    //console.log("AJAX selectSquare");    

                    this.ajaxcall( "/cephalopod/cephalopod/selectSquare.html", {   // Select square
                        x:x,
                        y:y
                    }, this, function( result ) {} );
                }
            } 
            else if (   dojo.hasClass( 'square_'+x+'_'+y, 'selectableDie' )   )                     // SELECTABLE DIE
            {  
                //console.log("Has selectableSquare class");    

                if (   this.checkAction ( 'selectDie' )   )
                {       
                    //console.log("AJAX selectDie");   
                    
                    this.ajaxcall( "/cephalopod/cephalopod/selectDie.html", {     // Select die
                        x:x,
                        y:y
                    }, this, function( result ) {} );
                }
            } 
            else                                                                                       // NEITHER SELECTABLE SQUARE NOR SELECTABLE DIE                           
            {                                                                                          // UNSELECT ALL
                if (   this.checkAction( 'unselectAll' ) )
                {        
                        //console.log("AJAX unselectAll");    
                   
                        this.ajaxcall( "/cephalopod/cephalopod/unselectAll.html", {     // Unselect checker
                    }, this, function( result ) {} );
                }
            } 
        },



        //If the player has decided to finalize this combination
        onFinalizeThisCombination: function ()
        {
			this.ajaxcall( '/cephalopod/cephalopod/finalizeThisCombination.html', { lock:true }, this, function( result ) {} );
		},

       


        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your cephalopod.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );

            dojo.subscribe( 'squareSelected', this, "notif_squareSelected" );
            
            dojo.subscribe( 'diePlaced', this, "notif_diePlaced" );

            dojo.subscribe( 'dieRemoved', this, "notif_dieRemoved" );

            dojo.subscribe( 'dieSelected', this, "notif_dieSelected" );

            dojo.subscribe( 'allUnselected', this, "notif_allUnselected" );

            dojo.subscribe( 'checkerPlacedHistory', this, "notif_checkerPlacedHistory" );

            dojo.subscribe( 'lastMoveIndicator', this, "notif_lastMoveIndicator" );

            dojo.subscribe( 'playerPanel', this, 'notif_playerPanel');

            dojo.subscribe( 'newScores', this, "notif_newScores" );
            
            dojo.subscribe( 'backendMessage', this, "notif_backendMessage" );
        },  
        

        // TODO: from this point and below, you can write your game notifications handling methods
        
        notif_squareSelected: function( notif )
        {            
            var x = notif.args.x;
            var y = notif.args.y;

            console.log( "notif_squareSelected"+" "+x+" "+y );

            dojo.query ( '.selectableSquare' ).removeClass( 'selectableSquare' );             
            dojo.query( '.selectedSquare' ).removeClass( 'selectedSquare' );      

            dojo.addClass ( 'square_'+x+'_'+y, 'selectedSquare' );
        },

        
        notif_diePlaced: function( notif )
        {            
            console.log( "notif addDieOnBoard" + " " + notif.args.x + " " + notif.args.y + " " + notif.args.player_id + " " + notif.args.die_id );

            // Remove current possible moves (makes the board more clear)
            dojo.query( '.selectableSquare' ).removeClass( 'selectableSquare' );        
            dojo.query( '.selectedSquare' ).removeClass( 'selectedSquare' );      
            dojo.query( '.selectedDie' ).removeClass( 'selectedDie' );        
        
            this.addDieOnBoard ( notif.args.x, notif.args.y, notif.args.player_id, notif.args.die_id );
        },
        

        notif_dieRemoved: function( notif )
        {
            console.log("notif_dieRemoved");
            
            dojo.query( '.selectableSquare' ).removeClass( 'selectableSquare' );      
            dojo.query( '.selectedSquare' ).removeClass( 'selectedSquare' );      
            dojo.query( '.selectableDie' ).removeClass( 'selectableDie' );      
            dojo.query( '.selectedDie' ).removeClass( 'selectedDie' );        

            this.slideToObjectAndDestroy( ''+notif.args.die_id, 'overall_player_board_'+notif.args.player_id, 1000, 0 ); 
        },

        
        notif_dieSelected: function( notif )
        {            
            var x = notif.args.x;
            var y = notif.args.y;

            console.log( "notif_dieSelected"+" "+x+" "+y );

            dojo.addClass ( 'square_'+x+'_'+y, 'selectedDie' );

            dojo.query ( '.selectableDie' ).removeClass( 'selectableDie' );             
        },

        
        notif_allUnselected: function( notif )
        {            
            var x = notif.args.x;
            var y = notif.args.y;

            //console.log( "notif_squareSelected"+" "+x+" "+y );

            dojo.query ( '.selectableSquare' ).removeClass( 'selectableSquare' );      
            dojo.query ( '.selectedSquare' ).removeClass( 'selectedSquare' );
            dojo.query ( '.selectableDie' ).removeClass( 'selectableDie' );      
            dojo.query ( '.selectedDie' ).removeClass( 'selectedDie' );             
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




      
        notif_checkerPlacedHistory: function ( notif )
        {
            //console.log("notif_checkerPlacedHistory");
        },


        notif_playerPanel: function ( notif ) 
        {
            var player_id = notif.args.player_id;
            var placed_dice = notif.args.placed_dice;

            //console.log("player_id = " + player_id);
            //console.log("placed_dice = " + placed_dice);

            dojo.byId("placed-dice_p"+player_id).innerHTML = placed_dice;
        },


        notif_newScores: function( notif )
        {
            for( var player_id in notif.args.scores )
            {
                var newScore = notif.args.scores[ player_id ];
                this.scoreCtrl[ player_id ].toValue( newScore );
            }
        },

        
        notif_backendMessage: function( notif )
        {
            //console.log("Inside notif_backendMessage");
        }
   });             
});
