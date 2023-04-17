<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Diablo implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * diablo.action.php
 *
 * Diablo main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/diablo/diablo/myAction.html", ...)
 *
 */
  
  
  class action_diablo extends APP_GameAction
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
            $this->view = "diablo_diablo";
            self::trace( "Complete reinitialization of board game" );
      }
  	} 
  	
  	// TODO: defines your action entry points there

    public function selectOriginFirstTurn()
    {
        self::setAjaxMode();     
        $x = self::getArg( "x", AT_posint, true );
        $y = self::getArg( "y", AT_posint, true );
        $result = $this->game->selectOriginFirstTurn( $x, $y );
        self::ajaxResponse( );
    }

    public function selectOriginMoveA()
    {
        self::setAjaxMode();     
        $x = self::getArg( "x", AT_posint, true );
        $y = self::getArg( "y", AT_posint, true );
        $result = $this->game->selectOriginMoveA( $x, $y );
        self::ajaxResponse( );
    }

    public function selectOriginMoveB()
    {
        self::setAjaxMode();     
        $x = self::getArg( "x", AT_posint, true );
        $y = self::getArg( "y", AT_posint, true );
        $result = $this->game->selectOriginMoveB( $x, $y );
        self::ajaxResponse( );
    }


    public function unselectOrigin()
    {
        self::setAjaxMode();     
        $result = $this->game->unselectOrigin( );
        self::ajaxResponse( );
    }


    public function selectDestinationFirstTurn()
    {
        self::setAjaxMode();     
        $x = self::getArg( "x", AT_posint, true );
        $y = self::getArg( "y", AT_posint, true );
        $result = $this->game->selectDestinationFirstTurn( $x, $y );
        self::ajaxResponse( );
    }

    public function selectDestinationMoveA()
    {
        self::setAjaxMode();     
        $x = self::getArg( "x", AT_posint, true );
        $y = self::getArg( "y", AT_posint, true );
        $result = $this->game->selectDestinationMoveA( $x, $y );
        self::ajaxResponse( );
    }

    public function selectDestinationMoveB()
    {
        self::setAjaxMode();     
        $x = self::getArg( "x", AT_posint, true );
        $y = self::getArg( "y", AT_posint, true );
        $result = $this->game->selectDestinationMoveB( $x, $y );
        self::ajaxResponse( );
    }

    public function removeCheckerMoveA()
    {
        self::setAjaxMode();     
        $x = self::getArg( "x", AT_posint, true );
        $y = self::getArg( "y", AT_posint, true );
        $result = $this->game->removeCheckerMoveA( $x, $y );
        self::ajaxResponse( );
    }

    public function removeCheckerMoveB()
    {
        self::setAjaxMode();     
        $x = self::getArg( "x", AT_posint, true );
        $y = self::getArg( "y", AT_posint, true );
        $result = $this->game->removeCheckerMoveB( $x, $y );
        self::ajaxResponse( );
    }

  }
  

