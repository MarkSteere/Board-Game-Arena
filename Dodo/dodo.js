/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Dodo implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * dodo.js
 *
 * Dodo user interface script
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
    return declare("bgagame.dodo", ebg.core.gamegui, {
        constructor: function(){
            //console.log('dodo constructor');
              
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
                
            //
            // Setting up player boards
            //
            
            for( var i in gamedatas.board )
            {
                var square = gamedatas.board[i];

                if( square.player !== null )
                {
                    this.addCheckerOnBoard( square.u, square.v, square.player, square.ch_index, square.ch_id );
                }
            
            }
                                

            dojo.query( '.square' ).connect( 'onclick', this, 'onClickedSquare' );        
            
                 
            this.setupNotifications();

            var normal_or_misere = gamedatas.normal_or_misere;





            if ( normal_or_misere == 2 )
            {
                this.addMisereStampOnBoard ();
            }




                
            //console.log( 'normal_or_misere = ' + normal_or_misere );
                
        },






        addMisereStampOnBoard ()
        {

            let backgroundPosition = 'background-position: 0px 0px';

            dojo.place( this.format_block( 'jstpl_billboard_content', {
                bkgPos: backgroundPosition
            } ) , 'info_billboard_1' );


            //this.placeOnObject( info_billboard_1, 'overall_player_board_'+player );
        },

        




        addCheckerOnBoard: function( u, v, player, checker_index, checker_id )
        {   

            //console.log( 'checker_index = ' + checker_index );

            let backgroundPosition = 'background-position: ';

            if ( checker_index < 100 )                                                  // Red checkers have normal checker indices values
            {
                horOffset = -( ( ( checker_index % 13 ) % 5 ) * 100 );
                vertOffset = -( ( Math.trunc ( ( checker_index % 13 ) / 5 ) ) * 100 );  
            }
            else
            {
                checker_index -= 100;                                                    // Blue checkers have 100 + checker incex
                
                horOffset = -( ( ( checker_index % 13 ) % 5 ) * 100 );
                vertOffset = -( ( Math.trunc ( ( checker_index % 13 ) / 5 ) + 3 ) * 100 );
            }

           
            backgroundPosition += horOffset + "px " + vertOffset + "px";
            
            dojo.place( this.format_block( 'jstpl_checker', {
                checker_id: checker_id,
                bkgPos: backgroundPosition
            } ) , 'checkers' );


            if (this.isSpectator)
            {
                var u_oriented = u;
                var v_oriented = v;
            }
            else
            {
                var color = this.gamedatas.players[ player ].color;
                var current_player_id = this.player_id;   
                var current_player = this.gamedatas.players [ current_player_id ];               
                var current_color = current_player.color;

                //console.log( 'current_player_id = ' + current_player_id );
                //console.log( 'current_color = ' + current_color );

                if ( current_color == "ff0000" )
                {
                    var u_oriented = u;
                    var v_oriented = v;
                }
                else
                {
                    var u_oriented = 6 - u;
                    var v_oriented = 6 - v;
                }

            }
            

            this.placeOnObject( checker_id, 'overall_player_board_'+player );
            this.slideToObject( checker_id, 'square_'+u_oriented+'_'+v_oriented ).play(); 

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
                   
                // case 'dummmy':
                // break;
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

        highlightSelectableCheckers: function( selectableCheckers )
        {
            //
            // Remove old selectable checkers and add new selectable checkers
            //
            dojo.query( '.selectableChecker' ).removeClass( 'selectableChecker' );

            
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

        
            for( var u in selectableCheckers )
            {
                for( var v in selectableCheckers [ u ] )
                {
                    if ( current_color == "ff0000" )
                    {
                        var u_oriented = u;
                        var v_oriented = v;
                    }
                    else
                    {
                        var u_oriented = 6 - u;
                        var v_oriented = 6 - v;
                    }
        
                    dojo.addClass( 'square_'+u_oriented+'_'+v_oriented, 'selectableChecker' );
                }            
            }
                        
            // this.addTooltipToClass( 'selectableChecker', '', _('Select this checker') );
        },
        


        highlightSelectedChecker: function ()
        {
            //console.log( 'highlightSelectedChecker' );

            
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


            for( var i in this.gamedatas.board )
            {
                var square = this.gamedatas.board[i];

                if( square.is_sel == 1 )
                {
                    //console.log( 'square.is_sel == 1');

                    var u = square.u;
                    var v = square.v;

                    if ( current_color == "ff0000" )
                    {
                        var u_oriented = u;
                        var v_oriented = v;
                    }
                    else
                    {
                        var u_oriented = 6 - u;
                        var v_oriented = 6 - v;
                    }

                    dojo.addClass( 'square_'+u_oriented+'_'+v_oriented, 'selectedChecker' );

                    
                    //return;
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

            
            for( var u in selectableDestinations )
            {
                for( var v in selectableDestinations [ u ] )
                {
                    if ( current_color == "ff0000" )
                    {
                        var u_oriented = u;
                        var v_oriented = v;
                    }
                    else
                    {
                        var u_oriented = 6 - u;
                        var v_oriented = 6 - v;
                    }

                    //console.log( 'dojo.addClass selectableDestination' );
        
                    dojo.addClass( 'square_'+u_oriented+'_'+v_oriented, 'selectableDestination' );
                }            
            }

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


        
        onClickedSquare: function( evt )
        {
            // Stop this event propagation
            evt.preventDefault();
            dojo.stopEvent( evt );
    
            // Get the clicked square u and v
            // Note: square id format is "square_U_V"
            var coords = evt.currentTarget.id.split('_');
            var u_oriented = coords[1];
            var v_oriented = coords[2];

            
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
                var u = u_oriented;
                var v = v_oriented;
            }
            else
            {
                var u = 6 - u_oriented;
                var v = 6 - v_oriented;
            }

    
            console.log("onClickedSquare - oriented"+" "+u_oriented+" "+v_oriented);
    
            if (   dojo.hasClass( 'square_'+u_oriented+'_'+v_oriented, 'selectableChecker'   )  )                   // SELECT CHECKER                                 
            {            
                if  (   this.checkAction( 'selectChecker' ) )
                {                
                    //console.log("checkAction selectChecker ok");    

                    this.ajaxcall( "/dodo/dodo/selectChecker.html", {   // Select checker
                        u:u,
                        v:v
                    }, this, function( result ) {} );
                }
            } 
            else if (     (  this.gamedatas.gamestate.name == "selectDestination"  )
                    &&  (  ! dojo.hasClass('square_'+u_oriented+'_'+v_oriented, 'selectableDestination')  )    )    // UNSELECT CHECKER                           
            {    
                if (   this.checkAction( 'selectDestination' ) )
                {        
                        //console.log("checkAction selectDestination ok - unselecting destination");    
                   
                        this.ajaxcall( "/dodo/dodo/unselectChecker.html", {     // Unselect checker
                    }, this, function( result ) {} );
                }
            } 
            else if (   dojo.hasClass( 'square_'+u_oriented+'_'+v_oriented, 'selectableDestination' )   )           // SELECT DESTINATION
            {  

                //console.log("This square is a possible destination.");  

                if (   this.checkAction ( 'selectDestination' )   )
                {       
                    //console.log("checkAction selectDestination ok - selecting destination - MOVE CHECKER");    
                    this.ajaxcall( "/dodo/dodo/selectDestination.html", {     // Move checker
                        u:u,
                        v:v
                    }, this, function( result ) {} );
                }
            } 
        },


        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your dodo.game.php file.
        
        */
        setupNotifications: function()
        {
            //console.log( 'Setting up notification subscriptions.' );
            
            dojo.subscribe( 'checkerSelected', this, "notif_checkerSelected" );
            
            dojo.subscribe( 'checkerUnselected', this, "notif_checkerUnselected" );

            dojo.subscribe( 'destinationSelected', this, "notif_destinationSelected" );

            dojo.subscribe( 'destinationSelectedHistory', this, "notif_destinationSelectedHistory" );

            dojo.subscribe( 'backendMessage', this, "notif_backendMessage" );


            dojo.subscribe( 'newScores', this, "notif_newScores" );
      },  
        
        notif_checkerSelected: function( notif )
        {            
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
                var u_oriented = notif.args.u;
                var v_oriented = notif.args.v;
            }
            else
            {
                var u_oriented = 6 - notif.args.u;
                var v_oriented = 6 - notif.args.v;
            }


            //console.log("notif_checkerSelected"+" "+u_oriented+" "+v_oriented);


            dojo.addClass( 'square_'+u_oriented+'_'+v_oriented, 'selectedChecker' );

            dojo.query( '.selectableChecker' ).removeClass( 'selectableChecker' );      
       
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
                var u_new_oriented = notif.args.u_new;
                var v_new_oriented = notif.args.v_new;
            }
            else
            {
                var u_new_oriented = 6 - notif.args.u_new;
                var v_new_oriented = 6 - notif.args.v_new;
            }


            dojo.query( '.selectedChecker' ).removeClass( 'selectedChecker' );      
            dojo.query( '.selectableDestination' ).removeClass( 'selectableDestination' );  

            this.slideToObject( ''+notif.args.checker_id, 'square_'+u_new_oriented+'_'+v_new_oriented ).play();             
        },
        

        notif_destinationSelectedHistory: function( notif )
        {
            console.log("notif_destinationSelectedHistory");

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
