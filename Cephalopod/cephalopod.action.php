<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Cephalopod implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * cephalopod.action.php
 *
 * Cephalopod main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/cephalopod/cephalopod/myAction.html", ...)
 *
*/
  
    class action_cephalopod extends APP_GameAction
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
            $this->view = "cephalopod_cephalopod";
            self::trace( "Complete reinitialization of board game" );
        }
  	} 
  	
  	public function selectSquare()
    {
        self::setAjaxMode();     
        $x = self::getArg( "x", AT_posint, true );
        $y = self::getArg( "y", AT_posint, true );
        $result = $this->game->selectSquare ( $x, $y );
        self::ajaxResponse ( );
    }

  
  	public function selectDie()
    {
        self::setAjaxMode();     
        $x = self::getArg( "x", AT_posint, true );
        $y = self::getArg( "y", AT_posint, true );
        $result = $this->game->selectDie ( $x, $y );
        self::ajaxResponse ( );
    }

  
    public function unselectAll()
    {
        self::setAjaxMode();     
        $result = $this->game->unselectAll ( );
        self::ajaxResponse ( );
    }


    public function finalizeThisCombination()
    {
        self::setAjaxMode();
        $this->game->finalizeThisCombination( );
        self::ajaxResponse( );
    }

}
  

