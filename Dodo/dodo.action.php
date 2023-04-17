<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Dodo implementation : © <Mark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * dodo.action.php
 *
 * Dodo main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/dodo/dodo/myAction.html", ...)
 *
 */
  
  
  class action_dodo extends APP_GameAction
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
            $this->view = "dodo_dodo";
            self::trace( "Complete reinitialization of board game" );
        }
  	} 
  	

    public function selectChecker()
    {
        self::setAjaxMode();     
        $u = self::getArg( "u", AT_posint, true );
        $v = self::getArg( "v", AT_posint, true );
        $result = $this->game->selectChecker( $u, $v );
        self::ajaxResponse( );
    }

  
    public function unselectChecker()
    {
        self::setAjaxMode();     
        $result = $this->game->unselectChecker();
        self::ajaxResponse( );
    }
  

    public function selectDestination()
    {
        self::setAjaxMode();     
        $u = self::getArg( "u", AT_posint, true );
        $v = self::getArg( "v", AT_posint, true );
        $result = $this->game->selectDestination( $u, $v );
        self::ajaxResponse( );
    }
   
}
  

