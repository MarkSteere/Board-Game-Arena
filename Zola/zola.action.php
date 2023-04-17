<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Zola implementation : © <Mark SteereMark Steere> <marksteere@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * zola.action.php
 *
 * Zola main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/zola/zola/myAction.html", ...)
 *
 */
  
  
  class action_zola extends APP_GameAction
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
            $this->view = "zola_zola";
            self::trace( "Complete reinitialization of board game" );
      }
  	} 
  	
  	// TODO: defines your action entry points there


    public function selectChecker()
    {
        self::setAjaxMode();     
        $x = self::getArg( "x", AT_posint, true );
        $y = self::getArg( "y", AT_posint, true );
        $result = $this->game->selectChecker( $x, $y );
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
        $x = self::getArg( "x", AT_posint, true );
        $y = self::getArg( "y", AT_posint, true );
        $result = $this->game->selectDestination( $x, $y );
        self::ajaxResponse( );
    }


   
    public function makeChoice()
    {
        self::setAjaxMode();
        $this->game->chooseFirstMove( );
        self::ajaxResponse( );
    }


}
  

