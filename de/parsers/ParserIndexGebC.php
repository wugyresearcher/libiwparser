<?php
/*
 * ----------------------------------------------------------------------------
 * "THE BEER-WARE LICENSE" (Revision 42):
 * <MacXY@herr-der-mails.de> wrote this file. As long as you retain
 * this notice you can do whatever you want with this stuff. If we meet some
 * day, and you think this stuff is worth it, you can buy me a beer in return.
 * Mac
 * ----------------------------------------------------------------------------
 */
/**
 * @author Mac <MacXY@herr-der-mails.de>
 * @package libIwParsers
 * @subpackage parsers_de
 */

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////



require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .
              '..'              . DIRECTORY_SEPARATOR .
              '..'              . DIRECTORY_SEPARATOR .
              'ParserBaseC.php' );
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .
              '..'              . DIRECTORY_SEPARATOR .
              '..'              . DIRECTORY_SEPARATOR .
              'ParserI.php' );
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .
              '..'              . DIRECTORY_SEPARATOR .
              '..'              . DIRECTORY_SEPARATOR .
              'HelperC.php' );
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .
              '..'              . DIRECTORY_SEPARATOR .
              'parserResults'   . DIRECTORY_SEPARATOR .
              'DTOParserMsgResultC.php' );



/**
 * Parser for Mainpage
 *
 * This parser is responsible for the Gebaeudebau section on the Mainpage
 *
 * Its identifier: de_index_geb
 */
class ParserIndexGebC extends ParserMsgBaseC implements ParserMsgI
{

  /////////////////////////////////////////////////////////////////////////////

  public function __construct()
  {
    parent::__construct();

    $this->setIdentifier('de_index_geb');
    $this->setCanParseMsg('Geb');
  }

 /////////////////////////////////////////////////////////////////////////////

  /**
   * @see ParserMsgI::parseMsg()
   */
  public function parseMsg( DTOParserResultC $parserResult )
  {
    $parserResult->objResultData = new DTOParserIndexGebResultC();
    $retVal =& $parserResult->objResultData;
    $fRetVal = 0;
    
    $regExp = $this->getRegularExpression();
    $msg = $this->getMsg();
    
    $parserResult->strIdentifier = 'de_index_geb';

    $aResult = array();

    $fRetVal = preg_match_all( $regExp, $msg->strParserText, $aResult, PREG_SET_ORDER );

    if( $fRetVal !== false && $fRetVal > 0 )
    {
        $parserResult->bSuccessfullyParsed = true;

        foreach( $aResult as $result ) {

            $iCoordsPla     = PropertyValueC::ensureInteger($result['iCoordsPla']);
            $iCoordsGal     = PropertyValueC::ensureInteger($result['iCoordsGal']);
            $iCoordsSol     = PropertyValueC::ensureInteger($result['iCoordsSol']);
            $strCoords      = $iCoordsGal . ':' . $iCoordsSol . ':' . $iCoordsPla;

            if (empty($retVal->aGeb[$strCoords]->strPlanetName)) {

                $retVal->aGeb[$strCoords] = new DTOParserIndexGebResultGebC();
                $retVal->aGeb[$strCoords]->strPlanetName = PropertyValueC::ensureString($result['strPlanetName']);
                $retVal->aGeb[$strCoords]->strCoords = $strCoords;
                $retVal->aGeb[$strCoords]->aCoords = array('coords_gal' => $iCoordsGal, 'coords_sol' => $iCoordsSol, 'coords_pla' => $iCoordsPla);
                $retVal->aGeb[$strCoords]->aGebName = array();

            }

            if (!empty($result['strGebName'])) {
                $strGebName                                          = PropertyValueC::ensureString($result['strGebName']);
                $iUnixTimestamp                                      = HelperC::convertDateTimeToTimestamp($result['dtDateTime']);
                $retVal->aGeb[$strCoords]->aGebName[$iUnixTimestamp] = $strGebName;

                ksort($retVal->aGeb[$strCoords]->aGebName, SORT_NUMERIC); //sortieren nach Bauzeitende aufsteigend, ist aber nicht nötig da ohnhin immer sortiert?
            }
        }
    }
    else
    {
      $parserResult->bSuccessfullyParsed = false;
      $parserResult->aErrors[] = 'Unable to match the pattern.';
      $parserResult->aErrors[] = $msg->strParserText;
    }
  }

  /////////////////////////////////////////////////////////////////////////////

  
  /**
   */  
  private function getRegularExpression()
  {
    $rePlanetName       = $this->getRegExpSingleLineText();
    $reDateTime         = $this->getRegExpDateTime();
    $reMixedTime         = $this->getRegExpMixedTime();
    
    $regExp = '/';
    $regExp .= '(?P<strPlanetName>'.$rePlanetName.')';
    $regExp .= '\s';
    $regExp .= '\((?P<iCoordsGal>\d+)\:(?P<iCoordsSol>\d+)\:(?P<iCoordsPla>\d+)\)';
    $regExp .= '\s+';
    $regExp .= '(?:';
    $regExp .= ' (?:';
    $regExp .= '  (?P<strGebName>'.$rePlanetName.')';
    $regExp .= '  \s+bis\s';
    $regExp .= '  (?P<dtDateTime>'.$reDateTime.')';
    $regExp .= '  (?:';
    $regExp .= '   \s(?:-\s)?';
    $regExp .= '   (?P<mtMixedTime>'.$reMixedTime.')';
    $regExp .= '  )?';
    $regExp .= ' )|(?:n.{1,5}scht)';
    $regExp .= ')';
    $regExp .= '/mxs';

    return $regExp;
  }

  /////////////////////////////////////////////////////////////////////////////

  /**
   * For debugging with "The Regex Coach" which doesn't support named groups
   */
  private function getRegularExpressionWithoutNamedGroups()
  {
    $retVal = $this->getRegularExpression();
    
    $retVal = preg_replace( '/\?P<\w+>/', '', $retVal );
    
    return $retVal;
  }
  
  /////////////////////////////////////////////////////////////////////////////

}



///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
