<?php
/*
 * ----------------------------------------------------------------------------
 * "THE BEER-WARE LICENSE" (Revision 42):
 * <martin@martimeo.de> wrote this file. As long as you retain
 * this notice you can do whatever you want with this stuff. If we meet some
 * day, and you think this stuff is worth it, you can buy me a beer in return.
 * Martin Martimeo
 * ----------------------------------------------------------------------------
 */
/**
 * @author     Martin Martimeo <martin@martimeo.de>
 * @author     Mac <MacXY@herr-der-mails.de>
 * @package    libIwParsers
 * @subpackage parsers_de
 */

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

/**
 * Parses a Schiff Information
 *
 * This parser is responsible for parsing the information of a ship
 *
 * Its identifier: de_info_schiff
 */
class ParserInfoSchiffC extends ParserBaseC implements ParserI
{

    /////////////////////////////////////////////////////////////////////////////

    public function __construct()
    {
        parent::__construct();

        $this->setIdentifier('de_info_schiff');
        $this->setName('Schiffsinformation');
        $this->setRegExpCanParseText('/Schiffinfo\s+Schiffinfo|Schiffinfo.+Daten.+Kampfdaten.+Besonderheiten/s');
        $this->setRegExpBeginData('/Schiffinfo:/');
        $this->setRegExpEndData('/Besonderheiten/');
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * @see ParserI::parseText()
     */
    public function parseText(DTOParserResultC $parserResult)
    {
        $parserResult->objResultData = new DTOParserInfoSchiffResultC();
        $retVal =& $parserResult->objResultData;

        $this->stripTextToData();

        $regExp          = $this->getRegularExpression();
        $regExpRess      = $this->getRegularExpressionRess();
        $regExpAreaName  = $this->getRegularExpressionAreaName(); //! from ResearchName
        $regExpYards     = $this->getRegularExpressionYards();
        $regExpEffective = $this->getRegularExpressionEffective();

        $aResult = array();
        $fRetVal = preg_match($regExp, $this->getText(), $aResult);

        if ($fRetVal !== false && $fRetVal > 0) {
            $parserResult->bSuccessfullyParsed = true;

            $retVal->strSchiffName   = $aResult['strSchiffName'];
            $retVal->iProductionTime = HelperC::convertMixedDurationToSeconds($aResult['strTime']);
            $retVal->aResearchs      = HelperC::convertBracketStringToArray($aResult['strResearchs']);

            $treffer = array();
            if (!empty($retVal->aResearchs)) {
                preg_match_all($regExpAreaName, $retVal->aResearchs[0], $treffer, PREG_SET_ORDER);
                foreach ($treffer as $teff) {
                    $AreaName = HelperC::convertBracketStringToArray($teff['AreaName']);
                    if (isset($AreaName[0])) {
                        if ($AreaName[0] == "Korvette") {
                            $AreaName[0] = "Korvetten";
                        } else if ($AreaName[0] == "Schlachtschiff") {
                            $AreaName[0] = "Schlachtschiffe";
                        } else if ($AreaName[0] == "Dreadnought") {
                            $AreaName[0] = "Dreadnoughts";
                        }
                        $retVal->strAreaName[] = $AreaName[0];
                    }
                }
            }
            $treffer = array();
            preg_match_all($regExpYards, $aResult['strYards'], $treffer, PREG_SET_ORDER);
            foreach ($treffer as $teff) {
                if (isset($teff['werft_typ'])) {
                    $retVal->strWerftTyp = $teff['werft_typ'];
                }
            }

            if (!empty($aResult['strActions'])) {
                $retVal->aActions = preg_split('/\n+/', $aResult['strActions']);
            }

            $retVal->iGschwdSol = $aResult['iGschwdSol'];
            $retVal->iGschwdGal = $aResult['iGschwdGal'];
            if (!empty($aResult['bGalFlucht'])) {
                $retVal->bCanLeaveGalaxy = true;
            }

            $retVal->iVerbrauchBrause  = $aResult['iVerbrauchBrause'];
            $retVal->iVerbrauchEnergie = $aResult['iVerbrauchEnergie'];
            if (!empty($aResult['iKapa1'])) {
                $retVal->bIsTransporter = true;
                $retVal->iKapa1         = PropertyValueC::ensureInteger($aResult['iKapa1']);
            }
            if (!empty($aResult['iKapa2'])) {
                $retVal->bIsTransporter = true;
                $retVal->iKapa2         = PropertyValueC::ensureInteger($aResult['iKapa2']);
            }
            if (!empty($aResult['iKapaBev'])) {
                $retVal->bIsTransporter = true;
                $retVal->iKapaBev       = PropertyValueC::ensureInteger($aResult['iKapaBev']);
            }

            if (!empty($aResult['bBeTransported'])) {
                $retVal->bCanBeTransported = true;
            }

            if (!empty($aResult['iSchiffKapa1'])) {
                $retVal->bIsCarrier = true;
                $retVal->iShipKapa1 = PropertyValueC::ensureInteger($aResult['iSchiffKapa1']);
            }

            if (!empty($aResult['iSchiffKapa2'])) {
                $retVal->bIsCarrier = true;
                $retVal->iShipKapa2 = PropertyValueC::ensureInteger($aResult['iSchiffKapa2']);
            }

            if (!empty($aResult['iSchiffKapa3'])) {
                $retVal->bIsCarrier = true;
                $retVal->iShipKapa3 = PropertyValueC::ensureInteger($aResult['iSchiffKapa3']);
            }

            $retVal->strWeaponClass = PropertyValueC::ensureString($aResult['strWeaponClass']);
            $retVal->iAttack        = PropertyValueC::ensureInteger($aResult['iAngriff']);
            $retVal->iDefence       = PropertyValueC::ensureInteger($aResult['iDefence']);
            $retVal->iArmour_kin    = PropertyValueC::ensureInteger($aResult['iPanzkin']);
            $retVal->iArmour_grav   = PropertyValueC::ensureInteger($aResult['iPanzgrav']);
            $retVal->iArmour_electr = PropertyValueC::ensureInteger($aResult['iPanzelektr']);
            $retVal->iShields       = PropertyValueC::ensureInteger($aResult['iSchilde']);
            $retVal->iAccuracy      = PropertyValueC::ensureInteger($aResult['iZielgenauigkeit']);
            $retVal->iMobility      = PropertyValueC::ensureInteger($aResult['iWendigkeit']);

            if (isset($aResult['iJaeger'])) {
                $retVal->iNoEscort = PropertyValueC::ensureInteger($aResult['iJaeger']);
            }
            if (isset($aResult['fBonusAtt'])) {
                $retVal->fBonusAtt = PropertyValueC::ensureFloat($aResult['fBonusAtt']);
            }
            if (isset($aResult['fBonusDef'])) {
                $retVal->fBonusDef = PropertyValueC::ensureFloat($aResult['fBonusDef']);
            }

            $treffer = array();
            preg_match_all($regExpRess, $aResult['kosten'], $treffer, PREG_SET_ORDER);
            foreach ($treffer as $teff) {
                $retVal->aCosts[] = array(
                    'strResourceName' => PropertyValueC::ensureEnum($teff['resource_name'], 'eResources'),
                    'iResourceCount'  => PropertyValueC::ensureInteger($teff['resource_count'])
                );
            }

            $treffer = array();
            preg_match_all($regExpEffective, $aResult['effective'], $treffer, PREG_SET_ORDER);
            foreach ($treffer as $teff) {
                $retVal->aEffectivity[] = array(
                    'strAreaName' => PropertyValueC::ensureString($teff['area_name']),
                    'fEffective'  => PropertyValueC::ensureInteger($teff['effective_count'])
                );
            }
        } else {
            $parserResult->bSuccessfullyParsed = false;
            $parserResult->aErrors[]           = 'Unable to match the de_info_schiff pattern.';
        }
    }

    /////////////////////////////////////////////////////////////////////////////

    private function getRegularExpressionRess()
    {
        $reResource = $this->getRegExpResource();

        $regExpRess = '/';
        $regExpRess .= '(?P<resource_name>' . $reResource . ')\:\s(?P<resource_count>' . $this->getRegExpDecimalNumber() . ')';
        $regExpRess .= '/mx';

        return $regExpRess;
    }

    /////////////////////////////////////////////////////////////////////////////

    private function getRegularExpressionEffective()
    {
        $reResource = $this->getRegExpAreas();

        $regExpRess = '/';
        $regExpRess .= '(?P<area_name>' . $reResource . ')\s+(?P<effective_count>' . '\d+' . ')\%';
        $regExpRess .= '/mx';

        return $regExpRess;
    }

    /////////////////////////////////////////////////////////////////////////////

    private function getRegularExpressionYards()
    {
        $reWerfttyp = "(kleine|mittlere|gro.{1,3}e|Dreadnought)";

        $regExpRess = '/';
        $regExpRess .= '((?P<werft_typ>(' . $reWerfttyp . '))\s((orbitale|planetare)\s)?Werft)';
        $regExpRess .= '/mx';

        return $regExpRess;
    }

    /////////////////////////////////////////////////////////////////////////////

    private function getRegularExpressionActions()
    {
        $reActions  = $this->getRegExpShipCapabilities();
        $regExpRess = '/';
        $regExpRess .= '(?P<action>' . $reActions . ')';
        $regExpRess .= '/mx';

        return $regExpRess;
    }

    /////////////////////////////////////////////////////////////////////////////

    private function getRegularExpressionAreaName()
    {
        $reArea = $this->getRegExpBracketString();

        $regExpRess = '/';
        $regExpRess .= '(?P<AreaName>' . $reArea . ')';
        $regExpRess .= '/mxu';

        return $regExpRess;
    }

    /////////////////////////////////////////////////////////////////////////////

    private function getRegularExpression()
    {
        //! @todo Mac: Area aus dem Forschungsnamen auslesen ?

        $reSchiffName   = $this->getRegExpSingleLineText3();
        $reResearchName = $this->getRegExpBracketString();
        $reMixedTime    = $this->getRegExpMixedTime();
        $reResource     = $this->getRegExpResource();
        $reCosts        = $this->getRegExpDecimalNumber();
        $reBonus        = $this->getRegExpFloatingDouble();
        $reWerftName    = "(?:(?:kleine|mittlere|gro.{1,3}e|Dreadnought)\s(?:(?:orbitale|planetare)\s)?Werft)";
        $reActions      = '(?:.{1,3}bergebbar|Stationierbar|Transport|Angreifen\s\/\sVerteidigen|Pl.{1,3}ndern|Sondieren';
        $reActions .= '|Kolonisieren|Kampfbasis\saufbauen|Ressbasis\saufbauen|Artefaktbasis\saufbauen';
        $reActions .= '|Bombardieren|Tarnbar|Terraformer|.{1,3}bergebbar\san\seigene\sPlaneten)';

        $regExp = '/';
        $regExp .= '(?P<strSchiffName>' . $reSchiffName . ')\s*?';
        $regExp .= '\n+';
        $regExp .= '[\s\S]+?';
        $regExp .= 'Kosten\s+?(?P<kosten>(\s?' . $reResource . '\:\s' . $reCosts . ')*|.*?)';
        $regExp .= '\n+';
        $regExp .= 'Dauer\s+?';
        $regExp .= '(?P<strTime>' . $reMixedTime . ')\s*?';
        $regExp .= '\n+';
        $regExp .= 'Voraussetzungen\sForschungen\s+?';
        $regExp .= '(?P<strResearchs>' . $reResearchName . ')?\s*';
        $regExp .= '\n+';
        $regExp .= '(?:aufr.{1,3}stbar\szu\s+?';
        $regExp .= '(?P<strUpgrade>' . $reSchiffName . ')';
        $regExp .= '\n+)?';

        $regExp .= 'ben.{1,3}tigt\sWerften\s+?';
        $regExp .= '(?P<strYards>(?:' . $reWerftName . '\s*)*)';
        $regExp .= '\n+';
        $regExp .= 'm.{1,3}gliche\sAktionen\s+?';
        $regExp .= '(?P<strActions>(?:' . $reActions . '\s*)+)';
        $regExp .= '\n+';

        $regExp .= 'Daten';
        $regExp .= '\n+';
        $regExp .= 'Geschwindigkeit\sSol\s+?';
        $regExp .= '(?P<iGschwdSol>' . '\d+' . ')\s*?';
        $regExp .= '\n+';
        $regExp .= 'Geschwindigkeit\sGal\s+?';
        $regExp .= '(?P<iGschwdGal>' . '\d+' . ')\s*?';
        $regExp .= '\n+';
        $regExp .= '(?:\s*?Schiff\skann\sdie\s(?P<bGalFlucht>Galaxie\sverlassen)\s*?';
        $regExp .= '\n+|)';
        $regExp .= 'Verbrauch\schem\.\sElemente\s+?';
        $regExp .= '(?P<iVerbrauchBrause>' . '\d+' . ')\s*?';
        $regExp .= '\n+';
        $regExp .= 'Verbrauch\sEnergie\s+?';
        $regExp .= '(?P<iVerbrauchEnergie>' . '\d+' . ')\s*?';
        $regExp .= '\n+';

        $regExp .= 'Zivile\sDaten';
        $regExp .= '\n+';
        $regExp .= '(?:^kann\svon\sfolgende\sSchiffen\s(?P<bBeTransported>transportiert\swerden)\s*?(?:(?:' . $reSchiffName . '\n)+|\n+)';
        $regExp .= '^belegt\sbei\seinem\sTransport\s+?(?P<iParkingLot>' . '\d+' . ')\sEinheit\(en\)\sPlatz\n+';
        $regExp .= '|)';
        $regExp .= '(?:Ladekapazit.t\sKlasse\s1\s+?';
        $regExp .= '(?P<iKapa1>' . '\d+' . ')\s*?';
        $regExp .= '\n+|)';
        $regExp .= '(?:Ladekapazit.t\sKlasse\s2\s+?';
        $regExp .= '(?P<iKapa2>' . '\d+' . ')\s*?';
        $regExp .= '\n+|)';
        $regExp .= '(?:Ladekapazit.t\sBev.lkerung\s+?';
        $regExp .= '(?P<iKapaBev>' . '\d+' . ')\s*?';
        $regExp .= '\n+|)';
        $regExp .= '(?:Schifftransportkapazit.t\sKlasse\s1\s+?';
        $regExp .= '(?P<iSchiffKapa1>' . '\d+' . ')\s*?';
        $regExp .= '\n+';
        $regExp .= '^kann\sfolgende\sSchiffe\stransportieren\s*?(?P<Transporter41>(?:' . $reSchiffName . '\n)+)';
        $regExp .= '|)';
        $regExp .= '(?:Schifftransportkapazit.t\sKlasse\s2\s+?';
        $regExp .= '(?P<iSchiffKapa2>' . '\d+' . ')\s*?';
        $regExp .= '\n+';
        $regExp .= '^kann\sfolgende\sSchiffe\stransportieren\s*?(?P<Transporter42>(?:' . $reSchiffName . '\n)+)';
        $regExp .= '|)';
        $regExp .= '(?:Schifftransportkapazit.t\sKlasse\s3\s+?';
        $regExp .= '(?P<iSchiffKapa3>' . '\d+' . ')\s*?';
        $regExp .= '\n+';
        $regExp .= '^kann\sfolgende\sSchiffe\stransportieren\s*?(?P<Transporter43>(?:' . $reSchiffName . '\n)+)';
        $regExp .= '|)';

        $regExp .= 'Kampfdaten';
        $regExp .= '\n+';
        $regExp .= 'Angriff\s+?';
        $regExp .= '(?P<iAngriff>' . '\d+' . ')\s*?';
        $regExp .= '\n+';
        $regExp .= 'Waffenklasse\s+?';
        $regExp .= '(?P<strWeaponClass>' . '(?:keine|elektrisch|gravimetrisch|kinetisch|unbekannt)' . ')\s*?';
        $regExp .= '\n+';
        $regExp .= 'Verteidigung\s+?';
        $regExp .= '(?P<iDefence>' . '\d+' . ')\s*?';
        $regExp .= '\n+';
        $regExp .= 'Panzerung\s\(kinetisch\)\s+?';
        $regExp .= '(?P<iPanzkin>' . '\d+' . ')\s*?';
        $regExp .= '\n+';

        $regExp .= 'Panzerung\s\(elektrisch\)\s+?';
        $regExp .= '(?P<iPanzelektr>' . '\d+' . ')\s*?';
        $regExp .= '\n+';
        $regExp .= 'Panzerung\s\(gravimetrisch\)\s+?';
        $regExp .= '(?P<iPanzgrav>' . '\d+' . ')\s*?';
        $regExp .= '\n+';
        $regExp .= 'Schilde\s+?';
        $regExp .= '(?P<iSchilde>' . '\d+' . ')\s*?';
        $regExp .= '\n+';
        $regExp .= 'Wendigkeit\s+?';
        $regExp .= '(?P<iWendigkeit>' . '\d+' . ')\s*?';
        $regExp .= '\n+';
        $regExp .= 'Zielgenauigkeit\s+?';
        $regExp .= '(?P<iZielgenauigkeit>' . '\d+' . ')\s*?';
        $regExp .= '\n+';
        $regExp .= 'Effektivit.{1,3}t\sgegen\s*\n(?P<effective>(?:^' . $reSchiffName . '\s*\d+\%\n)+)';

        $regExp .= '(?:Geleitschutz';
        $regExp .= ' \n+';
        $regExp .= ' Ben.{1,3}tigte\sJ.{1,3}geranzahl\sf.{1,3}r\sBonus\s+?';
        $regExp .= ' (?P<iJaeger>' . '\d+' . ')\s*?';
        $regExp .= ' \n+';
        $regExp .= ' Geleitschutzbonus\sAngriff\s*?';
        $regExp .= ' (?P<fBonusAtt>' . $reBonus . ')';
        $regExp .= ' \n+';
        $regExp .= ' Geleitschutzbonus\sVerteidigung\s+?';
        $regExp .= ' (?P<fBonusDef>' . $reBonus . ')\s*?';
        $regExp .= ')?';

        $regExp .= '(?:Spionagef.{1,3}higkeiten';
        $regExp .= ' \n+';
        $regExp .= ')?';

        $regExp .= '(?:Bombenschaden\s+?';
        $regExp .= ' (?P<iBombenschaden>' . '\d+' . ')\s*?';
        $regExp .= '\n+)?';

        $regExp .= '/mxu';

        return $regExp;
    }

}