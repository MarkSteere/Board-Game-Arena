/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Silo implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * silo.js
 *
 * Silo user interface script
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
    return declare("bgagame.silo", ebg.core.gamegui, {
        constructor: function(){
            //console.log('silo constructor');
              
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
            //console.log( "Starting game setup" );
            
            // Setting up player boards

            // TODO: Setting up players boards if needed
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

            //dojo.query( '.square' ).connect( 'onclick', this, 'onClickedSquare' );        
            dojo.query( '.square'+gamedatas.board_size ).connect( 'onclick', this, 'onClickedSquare' );        
            
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            //console.log( "Ending game setup" );
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
                else if ( current_color == "000000" )
                {
                    dojo.place( this.format_block( 'jstpl_reverse_billboard_content', {
                        bkgPos: backgroundPosition
                    } ) , 'overlay_billboard_1' );
                }
            }



            /*
            if (!this.isSpectator)
            {
                let backgroundPosition = 'background-position: 0px 0px';
                
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
                else if ( current_color == "000000" )
                {
                    dojo.place( this.format_block( 'jstpl_reverse_billboard_content', {
                        bkgPos: backgroundPosition
                    } ) , 'overlay_billboard_1' );
                }

                //console.log( "Adding overlay" );
            }
            */

        },

        addCheckerOnBoard: function( x, y, player, checker_id )
        {   
            //console.log( 'checker_index = ' + checker_index );

            N = this.gamedatas.board_size;

            let backgroundPosition = 'background-position: ';

            if ( checker_id < 100 )                                                  // Red checkers have normal checker indices values
            {





                /*
                horOffset = -(    ( checker_id % 3 ) * 107    );
                vertOffset = -(     Math.trunc (  checker_id / 3  ) * 31    ); 
                */

                if ( N == 6 )
                {
                    horOffset = -(    ( checker_id % 3 ) * 107    );
                    vertOffset = -(     Math.trunc (  checker_id / 3  ) * 31    ); 
                }
                else
                {
                    //console.log( 'Board size = 8' );


                    horOffset = -(    ( checker_id % 4 ) * 78    );
                    vertOffset = -(     Math.trunc (  checker_id / 3  ) * 22    ); 
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
                
                
                
                
                /*
                horOffset = -(    ( (checker_id-100) % 3 ) * 107    );
                vertOffset = -(     Math.trunc (  (checker_id-100) / 3  ) * 31    ); 
                */

                if ( N == 6 )
                {
                    horOffset = -(    ( (checker_id-100) % 3 ) * 107    );
                    vertOffset = -(     Math.trunc (  (checker_id-100) / 3  ) * 31    ); 
                }
                else
                {
                    horOffset = -(    ( (checker_id-100) % 4 ) * 78    );
                    vertOffset = -(     Math.trunc (  (checker_id-100) / 3  ) * 22    ); 
                }

                



                //console.log( 'checker_id = ' + checker_id );
                //console.log( 'horOffset = ' + horOffset );
                //console.log( 'vertOffset = ' + vertOffset );

                backgroundPosition += horOffset + "px " + vertOffset + "px";
            
                dojo.place( this.format_block( 'jstpl_black_checker', {
                    checker_id: checker_id,
                    bkgPos: backgroundPosition
                } ) , 'checkers' );
            }

           
            if (this.isSpectator)
            {
                var x_oriented = x;
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
                }
                else
                {
                    var x_oriented = N - x -1;
                }

            }
            

            this.placeOnObject( checker_id, 'overall_player_board_'+player );
            this.slideToObject( checker_id, 'square_'+x_oriented+'_'+y ).play(); 
   
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
            
                case 'playerTurn':
            
                this.highlightMovableCheckers ( args.args.movableCheckers );
                
                    break;
          
                case 'dummmy':
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
        
        highlightMovableCheckers: function( movableCheckers )
        {
            
            //console.log( 'Entering highlightMovableCheckers function' );

            //
            // Remove old selectable checkers and add new selectable checkers
            //
            dojo.query( '.movableChecker' ).removeClass( 'movableChecker' );

            N = this.gamedatas.board_size;
            
            if (!this.isSpectator)
            {
                var current_player_id = this.player_id;   
                var current_player = this.gamedatas.players [ current_player_id ];               
                var current_color = current_player.color;

        
                for( var x in movableCheckers )
                {
                    for( var y in movableCheckers [ x ] )
                    {
                        if ( current_color == "ff0000" )
                        {
                            var x_oriented = x;
                        }
                        else
                        {
                            var x_oriented = N - x - 1;
                        }
        
                        console.log( 'Adding movableChecker class' );

                        dojo.addClass( 'square_'+x_oriented+'_'+y, 'movableChecker' );
                    }            
                }
            }
                        
            // this.addTooltipToClass( 'selectableChecker', '', _('Select this checker') );
        },
        /*
        
            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.
        
        */


        ///////////////////////////////////////////////////
        //// Player's action
        
        
        onClickedSquare: function( evt )
        {
            // Stop this event propagation
            evt.preventDefault();
            dojo.stopEvent( evt );
    
            // Get the clicked square x and y
            // Note: square id format is "square_X_Y"
            var coords = evt.currentTarget.id.split('_');
            var x_clicked_square = coords[1];               // Raw coordinates
            var y_clicked_square = coords[2];               // (0,0) is lower left square for both players


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
                var x_oriented = x_clicked_square;
            }
            else
            {
                var x_oriented = N - x_clicked_square - 1;
            }
            if (   dojo.hasClass( 'square_'+x_clicked_square+'_'+y_clicked_square, 'movableChecker'   )  )      // Move checker                                 
            {            
                if  (   this.checkAction( 'moveChecker' ) )
                {                
                    console.log("checkAction moveChecker ok");    

                    this.ajaxcall( "/silo/silo/moveChecker.html", {   // Move checker
                        x:x_oriented,
                        y:y_clicked_square
                    }, this, function( result ) {} );
                }
            } 
    
        },
        


        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your silo.game.php file.
        
        */
        setupNotifications: function()
        {
            //console.log( 'notifications subscriptions setup' );
            
           
            dojo.subscribe( 'slideChecker', this, "notif_slideChecker" );

            dojo.subscribe( 'backendMessage', this, "notif_backendMessage" );

            dojo.subscribe( 'moveCheckerHistory', this, "notif_moveCheckerHistory" );

            dojo.subscribe( 'newScores', this, "notif_newScores" );

        },  
        


        notif_slideChecker: function( notif )
        {
            //console.log("notif_destinationSelected. SLIDE TO OBJECT");
            
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


            N = this.gamedatas.board_size;


            var y_new_oriented = notif.args.y_new;

            if ( current_color == "ff0000" )
            {
                var x_new_oriented = notif.args.x_new;
            }
            else
            {
                var x_new_oriented = N - notif.args.x_new - 1;
            }


            dojo.query( '.movableChecker' ).removeClass( 'movableChecker' );      

            this.slideToObject( ''+notif.args.checker_id, 'square_'+x_new_oriented+'_'+y_new_oriented ).play();             
        },
        

        notif_moveCheckerHistory: function( notif )
        {
            console.log("notif_moveCheckeredHistory");

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
