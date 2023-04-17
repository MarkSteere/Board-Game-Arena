<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Oust implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * oust.action.php
 *
 * Oust main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/oust/oust/myAction.html", ...)
 *
 */

class action_oust extends APP_GameAction
{ 
    // Constructor: please do not modify
   	public function __default()
  	{
  	    if( self::isArg( 'notifwindow') )
  	    {
            $this->view = "common_notifwindow";
  	        $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
  	    }
  	    else
  	    {
            $this->view = "oust_oust";
            self::trace( "Complete reinitialization of board game" );
        }
  	} 
  	
  	// TODO: defines your action entry points there



    public function placeChecker()
    {
        self::setAjaxMode();     
        $u = self::getArg( "u", AT_posint, true );
        $v = self::getArg( "v", AT_posint, true );
        $result = $this->game->placeChecker( $u, $v );
        self::ajaxResponse( );
    }

   
    public function makeChoice()
    {
        self::setAjaxMode();
        $this->game->chooseFirstMove( );
        self::ajaxResponse( );
    }

}
