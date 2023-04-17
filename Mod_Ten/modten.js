/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * ModTen implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * modten.js
 *
 * ModTen user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/stock"
],
function (dojo, declare) {
    return declare("bgagame.modten", ebg.core.gamegui, {
        constructor: function(){
            console.log('modten constructor');
              
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;

            this.playerHand = null;
            this.cardwidth = 72;
            this.cardheight = 100;
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
            console.log( "start setup" );

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
                        }
                    )
                    , player_board_div
                );
            }

            
            //
            // Display player panel
            //
            for (var keyPlayerId in gamedatas.n_played_cards) 
            {
                //console.log( "n_played_cards" + gamedatas.n_played_cards[keyPlayerId] );

                dojo.byId("n_played_cards_p"+keyPlayerId).innerHTML = gamedatas.n_played_cards[keyPlayerId];
            }

            

            // Player hand
            this.playerHand = new ebg.stock();
            this.playerHand.create( this, $('myhand'), this.cardwidth, this.cardheight );
            this.playerHand.image_items_per_row = 9;
            dojo.connect( this.playerHand, 'onChangeSelection', this, 'onPlayerHandSelectionChanged' );
            
            //console.log( "number of players = " + this.gamedatas.number_of_players);

            // Create cards types:
            for( var suit=1;suit<=4;suit++ )
            {
                for( var value=1;value<=9;value++ )
                {
                    // Build card type id
                    var card_type_id = this.getCardUniqueId( suit, value );
                    this.playerHand.addItemType( card_type_id, card_type_id, g_gamethemeurl+'img/Mod_Ten_cards.png', card_type_id );
                }
            }
            
            // Cards in player's hand
            for( var i in this.gamedatas.hand )
            {
                var card = this.gamedatas.hand[i];
                var suit = card.type;
                var value = card.type_arg;
                this.playerHand.addToStockWithId( this.getCardUniqueId( suit, value ), card.id );
            }


            //
            //  MOD TEN AND TRICK SUIT 
            //
            var mod_ten = this.gamedatas.mod_ten;

            var mod_ten_id = this.gamedatas.mod_ten_id;

            var active_player_id = this.gamedatas.active_player_id;

            this.addModTenOnBoard ( mod_ten, mod_ten_id, active_player_id );


            if ( mod_ten > 0 )
            {
                var trick_suit = this.gamedatas.trick_suit;

                var trick_suit_id = this.gamedatas.trick_suit_id;

                this.addTrickSuitOnBoard ( trick_suit, trick_suit_id, active_player_id );
            }


            //
            //  MOVE LISTS 
            //
            for ( var key_player_id in gamedatas.move_lists)
            {
                this.updateMoveString ( gamedatas.move_lists[key_player_id], key_player_id );
            }

           
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();
            
            //this.ensureSpecificImageLoading( ['../common/point.png'] );  
        },
       

        //
        //  ADD MOD TEN ON BOARD 
        //
        addModTenOnBoard: function( mod_ten, mod_ten_id, player_id )
        {   
            //console.log('addModTenOnBoard ' + mod_ten );

            var horOffset;
            var verOffset;

            //var rank_id = 1000 + mod_ten;

            horOffset = 0 - ( mod_ten * 72 );

            verOffset = 0;


            let backgroundPosition = 'background-position: ';

            backgroundPosition += horOffset + "px " + verOffset + "px";   


            let mod_ten_id_str = mod_ten_id.toString ( );


            dojo.place( this.format_block( 'jstpl_rank', {
                rank_id: mod_ten_id_str,
                bkgPos: backgroundPosition
            } ) , 'images' );        


            this.placeOnObject( mod_ten_id_str, 'overall_player_board_'+player_id );
            this.slideToObject( mod_ten_id_str, 'rank_container_id', 500, 0 ).play(); 
        },




        //
        //  ADD TRICK SUIT ON BOARD 
        //
        addTrickSuitOnBoard: function( trick_suit, trick_suit_id, player_id )
        {   
            //console.log( "addTrickSuitOnBoard " + trick_suit );

            var horOffset;
            var verOffset;

            switch ( trick_suit )
            {
                case '0':

                    break;

                case '1':
                case '2':
                case '3':
                case '4':
                    
                    horOffset = 0 - (    ( trick_suit - 1 ) * 72    );

                    verOffset = 0;


                    let backgroundPosition = 'background-position: ';

                    backgroundPosition += horOffset + "px " + verOffset + "px";   


                    let trick_suit_id_str = trick_suit_id.toString ( );

                    dojo.place( this.format_block( 'jstpl_trick_suit', {
                        trick_suit_id: trick_suit_id_str,
                        bkgPos: backgroundPosition
                    } ) , 'images' );        


                    this.placeOnObject( trick_suit_id_str, 'overall_player_board_'+player_id );
                    this.slideToObject( trick_suit_id_str, 'trick_suit_container_id', 500, 0 ).play(); 

                    break;
            }
        },



        //
        //  UPDATE MOVE STRING 
        //
        updateMoveString: function( move_string, player_id )
        {   
            document.getElementById ( "played_cards_list_" + player_id ).innerHTML = move_string; 
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
            
            /* Example:
            
            case 'myGameState':
            
                // Show some HTML block at this game state
                dojo.style( 'my_html_block_id', 'display', 'block' );
                
                break;
           */
           
           
            case 'dummmy':
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
        
        /*
        
            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.
        
        */
        // Get card unique identifier based on its color and value
        getCardUniqueId: function( suit, value )
        {
            return (suit-1)*9+(value-1);
        },

        


        removeCard: function( player_id, card_id )
        {
            if( player_id == this.player_id )
            {
                // You played a card. If it exists in your hand, move card from there and remove
                // corresponding item
                
                if( $('myhand_item_'+card_id) )
                {
                    this.playerHand.removeFromStockById( card_id );
                }
            }
        },




        ///////////////////////////////////////////////////
        //// Player's action
        
        onPlayerHandSelectionChanged: function(  )
        {
            var items = this.playerHand.getSelectedItems();

            if( items.length > 0 )
            {
                if( this.checkAction( 'playCard', true ) )
                {
                    // Can play a card
                    
                    var card_id = items[0].id;
                    
                    this.ajaxcall( "/modten/modten/playCard.html", { 
                            id: card_id,
                            lock: true 
                            }, this, function( result ) {  }, function( is_error) { } );                        

                    this.playerHand.unselectAll();
                }
                else
                {
                    this.playerHand.unselectAll();
                }                
            }
        },
        

        

        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your modten.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            //dojo.subscribe( 'newHand', this, "notif_newHand" );

            dojo.subscribe( 'removeCard', this, "notif_removeCard" );


            dojo.subscribe( 'modTenAdded', this, "notif_modTenAdded" );
            dojo.subscribe( 'modTenRemoved', this, "notif_modTenRemoved" );

            dojo.subscribe( 'trickSuitAdded', this, "notif_trickSuitAdded" );
            dojo.subscribe( 'trickSuitRemoved', this, "notif_trickSuitRemoved" );

            dojo.subscribe( 'moveStringUpdated', this, "notif_moveStringUpdated" );


            dojo.subscribe( 'newScores', this, "notif_newScores" );

            dojo.subscribe( 'backendMessage', this, "notif_backendMessage" );

            dojo.subscribe( 'playerPanel', this, 'notif_playerPanel');
        },  
        
        // TODO: from this point and below, you can write your game notifications handling methods
        
        notif_removeCard: function( notif )
        {
            // Play a card on the table
            this.removeCard( notif.args.player_id, notif.args.card_id );
        },


        notif_modTenAdded: function( notif )
        {
            //console.log('');

            this.addModTenOnBoard( ''+notif.args.mod_ten, notif.args.mod_ten_id, notif.args.player_id  ); 
        },


        notif_modTenRemoved: function( notif )
        {
            //console.log('');

            this.slideToObjectAndDestroy( ''+notif.args.mod_ten_id, 'overall_player_board_'+notif.args.player_id, 500, 0 ); 
        },


        notif_trickSuitAdded: function( notif )
        {
            //console.log('');

            this.addTrickSuitOnBoard( ''+notif.args.trick_suit, notif.args.trick_suit_id, notif.args.player_id  ); 
        },


        notif_trickSuitRemoved: function( notif )
        {
            //console.log('');

            this.slideToObjectAndDestroy( ''+notif.args.trick_suit_id, 'overall_player_board_'+notif.args.player_id, 500, 0 ); 
        },


        notif_moveStringUpdated: function( notif )
        {
            //console.log( '' + notif.args.player_id + ' ' + notif.args.move_string );

            this.updateMoveString( ''+notif.args.move_string, notif.args.player_id  ); 
        },


        notif_newScores: function( notif )
        {
            // Update players' scores
            
            for( var player_id in notif.args.newScores )
            {
                this.scoreCtrl[ player_id ].toValue( notif.args.newScores[ player_id ] );
            }
        },


        notif_backendMessage: function( notif )
        {
            // console.log('Inside notif_backendMessage');
        },


        notif_playerPanel: function ( notif ) {
            var n_played_cards = notif.args.n_played_cards;
            var player_id = notif.args.player_id;
            dojo.byId("n_played_cards_p"+player_id).innerHTML = n_played_cards;
        }
   });             
});
