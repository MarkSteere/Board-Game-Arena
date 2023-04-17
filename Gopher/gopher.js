/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Gopher implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * gopher.js
 *
 * Gopher user interface script
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
    return declare("bgagame.gopher", ebg.core.gamegui, {
        constructor: function(){
            console.log('gopher constructor');
              
            // Here, you can init the global variables of your user interface
            // this.myGlobalValue = 0;

            this.numberRedMoves = 0;
            this.numberBlueMoves = 0;

            //this.currentRedMove = 1;
            //this.currentBlueMove = 1;
        },
        
        /*
            setup:
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
        setup: function( gamedatas )
        {
            console.log( "Starting game setup" );
            
            // Setting up player boards
            /*
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];
                         
                // TODO: Setting up players boards if needed
            }
            */
            
            // TODO: Set up your game interface here, according to "gamedatas"
            for( var i in gamedatas.board )
            {
                var square = gamedatas.board[i];
                
                if( square.player !== null )
                {
                    this.addDiscOnBoard( square.x, square.y, square.player );
                }
            }
            
            dojo.query( '.square' ).connect( 'onclick', this, 'onPlayDisc' );                        
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();
            
            // This was in reversi.js
            //this.ensureSpecificImageLoading( ['../common/point.png'] ); 

            console.log( "Ending game setup" );
        },
       

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName );
            
            switch( stateName )
            {
            
            case 'playerTurn':
                this.updatePossibleMoves( args.args.possibleMoves );
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
            //console.log( 'onUpdateActionButtons: '+stateName );
                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName )
                {
                }
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods
        
        addDiscOnBoard: function( x, y, player )
        {       
            var horOffset = 0;
            var vertOffset = 0;

            //var testHorOffset = -1;
            //var testVertOffset = -1;

            let backgroundPosition = 'background-position: ';
            

            var color = this.gamedatas.players[ player ].color;



            if ( color == "ff0000" )
            {
                horOffset =  -( ( ( this.numberRedMoves % 36 ) % 9 ) * 55 ) - 1;
                vertOffset = -( Math.trunc ( ( this.numberRedMoves % 36 ) / 9) * 55 ) - 1;
            }
            else
            {
                horOffset =  -( ( ( this.numberBlueMoves % 36 ) % 9 ) * 55 ) - 1;
                vertOffset = -( ( Math.trunc ( ( this.numberBlueMoves % 36 ) / 9) + 4 ) * 55 ) - 1;
            }
            

            backgroundPosition += horOffset + "px " + vertOffset + "px";
            
            dojo.place( this.format_block( 'jstpl_disc', {
                xy: x+''+y,
                color: color,
                bkgPos: backgroundPosition
            } ) , 'discs' );

            
            this.placeOnObject( 'disc_'+x+''+y, 'overall_player_board_'+player );
            this.slideToObject( 'disc_'+x+''+y, 'square_'+x+'_'+y ).play();


            if ( color == "ff0000" )
            {
                ++this.numberRedMoves;
            }
            else
            {
                ++this.numberBlueMoves;
            }
            
       },   

        
        updatePossibleMoves: function( possibleMoves )
        {
            // Remove current possible moves
            dojo.query( '.possibleMove' ).removeClass( 'possibleMove' );

            for( var x in possibleMoves )
            {
                for( var y in possibleMoves[ x ] )
                {
                    // x,y is a possible move
                    dojo.addClass( 'square_'+x+'_'+y, 'possibleMove' );
                }            
            }
                        
            // this.addTooltipToClass( 'possibleMove', '', _('Place a disc here') );
        },


        ///////////////////////////////////////////////////
        //// Player's action
        
        /*
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        */
        
        onPlayDisc: function( evt )
        {
            // Stop this event propagation
            evt.preventDefault();
            dojo.stopEvent( evt );

            // Get the clicked square x and y
            // Note: square id format is "square_X_Y"
            var coords = evt.currentTarget.id.split('_');
            var x = coords[1];
            var y = coords[2];

            console.log("onPlayDisc"+" "+x+" "+y);

            if( ! dojo.hasClass( 'square_'+x+'_'+y, 'possibleMove' ) )
            {
                // This is not a possible move => the click does nothing
                return ;
            }
            
            if( this.checkAction( 'playDisc' ) )    // Check that this action is possible at this moment
            {            
                this.ajaxcall( "/gopher/gopher/playDisc.html", {
                    x:x,
                    y:y
                }, this, function( result ) {} );
            }            
        },

        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your gopher.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            // TODO: here, associate your game notifications with local methods
            
            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            
            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            // 
            console.log( 'notifications subscriptions setup' );
            
            dojo.subscribe( 'playDisc', this, "notif_playDisc" );
            this.notifqueue.setSynchronous( 'playDisc', 500 );
            
            dojo.subscribe( 'newScores', this, "notif_newScores" );
        },  
        
        // TODO: from this point and below, you can write your game notifications handling methods
        // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
        
        notif_playDisc: function( notif )
        {
            // Remove current possible moves (makes the board more clear)
            dojo.query( '.possibleMove' ).removeClass( 'possibleMove' );        
        
            this.addDiscOnBoard( notif.args.x, notif.args.y, notif.args.player_id );

            console.log("notif_playDisc"+" "+notif.args.x+" "+notif.args.y);
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
