<?php
/*
 * ----------------------------------------------------------------------------
 * "THE BEER-WARE LICENSE" (Revision 42):
 * <benjamin.woester@googlemail.com> wrote this file. As long as you retain
 * this notice you can do whatever you want with this stuff. If we meet some
 * day, and you think this stuff is worth it, you can buy me a beer in return.
 * Benjamin Wöster
 * ----------------------------------------------------------------------------
 */
/**
 * @author     Benjamin Wöster <benjamin.woester@googlemail.com>
 * @package    libIwParsers
 * @subpackage parsers
 */


///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

/**
 * Base function class for parsers
 *
 * This class holds the functions that are to be needed by several parsersbases
 */
class ParserFunctionC
{

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression class matching thousand seperator
     *
     * IceWars gives users the possibility to specify their own seperators.
     * In this method, we define those seperators we support for the thousand
     * seperator. It's meant to be used within other regular expressions.
     *
     * For the moment, we support: ".", "'", "k", '"', '`', '´' and " " as seperators.
     *
     * @done by Mac: get supported thousand seperators from config
     */
    private function getRegExpThousandSeperator()
    {
        $res = "[";
        $supportedThousandSeperators = ConfigC::get('lib.aThousandSeperators');
        foreach ($supportedThousandSeperators as $sep) {
            if (stripos("\".'", $sep) !== false) {
                $res .= "\\" . $sep;
            } else if ($sep == " ") {
                $res .= "[:blank:]";
            } else {
                $res .= $sep;
            }
        }
        $res .= "]";

        return $res;
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression class matching comma separator
     *
     * IceWars gives users the possibility to specify their own separators.
     * In this method, we define those separators we support for the thousand
     * separator. It's meant to be used within other regular expressions.
     *
     */
    private function getRegExpCommaSeperator()
    {
        return "[\.\,´`]";
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression class matching floating point seperator
     *
     * IceWars gives users the possibility to specify their own separators.
     * In this method, we define those separators we support for the floating
     * point separator. It's meant to be used within other regular expressions.
     *
     * For the moment, we support: "." and ",".
     */
    private function getRegExpFloatingPointSeperator()
    {
        return "[\.,´`]{1}";
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression class matching a bracket string
     *
     * researches/ships and buildings are often shown as a bracket string
     * like (Area 42 (unterirdischer Forschungskomplex))
     * or (Tarnung von großen Sachen mittels einer Decke) (Verstehen der Zusammenhänge)
     */
    protected function getRegExpBracketString()
    {
        return "(?:\((?:(?:[^\n\(\)]+)(?:\((?:[^\n\(\)]*)\)(?:[^\n\(\)]*))*)\))(?:\s\((?:(?:[^\n\(\)]+)(?:\((?:[^\n\(\)]*)\)(?:[^\n\(\)]*))*)\))*";
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching building points
     *
     * Building points may be separated by thousand separator. Building points
     * start and end with digits. If there are three digits at the right, there
     * may be a thousand separator left of them.
     *
     * The following regExp is used:
     * (?<![\d]|[\.])(\d+|\d{1,3}([\.]\d{3})*)(?![\d\w]|[\.])
     *
     * Explanation:
     * 1. no digit or dot before the regExp   (?<![\d]|[\.])
     * 2. a dezimal value                     (\d+|\d{1,3}([\.]\d{3})*)
     *  2.1 a dezimal value is either a       \d+
     *      repetition of decimals...
     *  2.2 ... OR ...                        |
     *  2.3 a sequence of one to three digits \d{1,3}
     *  2.4 that might be followed by a       ([\.]\d{3})*
     *      repetition of one dot and three
     *      digits multiple times
     * 3. no digits, characters or dots       (?![\d\w]|[\.])
     *    behind the regExp
     *
     * @TODO: use a back reference to always match the same separator. Something
     *                 like: \d{1,3}(([\.])\d{3})?(\1\d{3})*
     *                     ^    ^         ^
     *                     |    |         |
     *                 Start    End       Reuse
     *                 This won't match 123.456'789 any more but only decimals that always
     *                 use the same separator (123.456.789 or 123'456'789).
     */
    protected function getRegExpDecimalNumber()
    {
        $retVal = '(?:';
        $reThousandSeperator = $this->getRegExpThousandSeperator();

        $retVal .= '(?:(?<=\s)|(?<=^)|(?<=\())'; //the decimal number shall start in a whitesace or start of line or a braket
        $retVal .= '(?:\-)?'; //value can be negative
        $retVal .= '(?:\d{1,3}'; //1) a sequence of one to three digits
        $retVal .= '(?:' . $reThousandSeperator . '\d{3})*'; //1.1) that might be followed by a repetition of one thousandSeperator and three digits multiple times
        $retVal .= '|'; //  ... OR ...
        $retVal .= '\d+)'; //2) a decimal value is either a repetition of decimals...
        $retVal .= '(?=\s|$|\)|\%)'; //the decimal number shall end in a whitesace or end of line or a braket
        $retVal .= ')';

        return $retVal;
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching doubles
     */
    protected function getRegExpFloatingDouble()
    {

        $reThousandSeperator = $this->getRegExpThousandSeperator();
        $reCommaSeperator    = $this->getRegExpCommaSeperator();

        $retVal  = '(?:';
        $retVal .=  '(?:(?<=\s)|(?<=^)|(?<=\())'; //the decimal number shall start in a whitesace or start of line or a bracket
        $retVal .=  '(?:\-|\+)?';                 //value can be negative (or explicit positive)
        $retVal .=  '(?:\d{1,3}';                 //1) a sequence of one to three digits
        $retVal .=   '(?:';
        $retVal .=    $reThousandSeperator;
        $retVal .=    '\d{3})*'; //1.1) that might be followed by a repetition of one thousandSeperator and three digits multiple times
        $retVal .=    '|'; //  ... OR ...
        $retVal .=   '\d+)'; //2) a decimal value is either a repetition of decimals...
        $retVal .=   '(?:';
        $retVal .=   $reCommaSeperator;
        $retVal .=   '\d{2}'; //there can be a comma separated part of 2 digits
        $retVal .=  ')?'; //but needn't to be
        $retVal .= '(?=\s|$|\)|\*|\%)'; //the decimal number shall end in a whitesace or end of line or a bracket or a * or a %
        $retVal .= ')';

        return $retVal;
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching a unsigned double
     */
    protected function getRegExpUnsignedDouble()
    {
        $retVal              = '';
        $reThousandSeperator = $this->getRegExpThousandSeperator();
        $reCommaSeperator    = $this->getRegExpCommaSeperator();

        $retVal .= '(?:(?<=\s)|(?<=^)|(?<=\+)|(?<=\())'; //the decimal number shall start in a whitespace or start of line or a bracket
        $retVal .= '(?:\d{1,3}'; //1) a sequence of one to three digits
        $retVal .=   '(?:';
        $retVal .=    $reThousandSeperator;
        $retVal .=    '\d{3})*'; //1.1) that might be followed by a repetition of one thousandSeperator and three digits multiple times
        $retVal .= '|'; //  ... OR ...
        $retVal .= '\d+)'; //2) a decimal value is either a repetition of decimals...
        $retVal .=   '(?:';
        $retVal .=   $reCommaSeperator;
        $retVal .=   '\d{2}'; //there can be a comma separated part of 2 digits
        $retVal .= ')?'; //but needn't to be
        $retVal .= '(?=\s|$|\)|\+)'; //the decimal number shall end in a whitesace or end of line or a bracket

        return $retVal;
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching user names
     *
     * Taking a look at IceWars registration page, we can see a user name
     * can be at least 30 characters in length.
     *
     * From the german IW forum:
     * "Anmeldung in IW:
     *  Name enthält unerlaubte Zeichen. Erlaubt sind a-z0-9.-_ +*()={} und keine
     *  Leerzeichen am Anfang/Ende oder mehrfache Leerzeichen. Mindestens 1
     *  Zeichen, maximal 30."
     * (http://www.icewars-forum.de/index.php?topic=23486.msg514075#msg514075)
     *
     * From the highscore, we can see that also upper case letters are allowed.
     */
    protected function getRegExpUserName()
    {
        $retVal = '(?:';

        $retVal .= '(?:(?<=\s)|(?<=^)|(?<=\>))'; //the user name shall start in a whitesace or start of line or >
        $retVal .= '(?!\s\s)'; //the user name must not contain multiple spaces

        $retVal .= '[a-zA-Z0-9\.\-_\+\*\(\)=\{\}]'; //first character must not be a space
        $retVal .= '(?:'; //probably, there will be more characters
        $retVal .= '[a-zA-Z0-9\.\-_\+\*\(\)=\{\} ]{0,28}'; //one to thirty characters
        $retVal .= '[a-zA-Z0-9\.\-_\+\*\(\)=\{\}]'; //last character must not be a space
        $retVal .= ')?';

        $retVal .= '(?=\s|$|\.|\<)'; //the username shall end in a whitesace or end of line or a dot (see messages) or <
        $retVal .= ')';

        return $retVal;
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching user names
     *
     * This function does not contain look behind and look before to increase speed
     */
    protected function getRegExpLowUserName()
    {
        $retVal = '';

        $retVal .= '[a-zA-Z0-9\.\-_\+\*\(\)=\{\}]'; //first character must not be a space
        $retVal .= '(?:'; //probably, there will be more characters
        $retVal .= '[a-zA-Z0-9\.\-_\+\*\(\)=\{\} ]{0,28}'; //one to thirty characters
        $retVal .= '[a-zA-Z0-9\.\-_\+\*\(\)=\{\}]'; //last character must not be a space
        $retVal .= ')?';

        return $retVal;
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching user rank
     *
     * This is for the german parsers. Alliance members can have the following
     * ranks: 'Gründer', 'HC', 'interner HC', 'Memberverwalter' or 'Member'.
     *
     * @fix: In the ally member list, 'Memberverwalter' is called
     *       'Mitgliederverwalter'.
     * @fix: In the ally member list, 'Member' is called
     *       'Mitglieder'.
     */
    protected function getRegExpUserRank_de()
    {
        return '(?:Hasenpriester|(?<!interner\s)HC|interner HC|Mitgliederverwalter|Mitglieder)';
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching text
     */
    protected function getRegExpText()
    {
        return '(?:.*)';
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching text (only a single line!)
     */
    protected function getRegExpSingleLineText()
    {
        return '(?:[^\n]*)';
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching text (only a single line!)
     */
    protected function getRegExpSingleLineText3()
    {
        return '(?:[^\n\t]{3,})';
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching a staatsform
     *
     * This is for the german parsers. A Staatsform can be:
     * 'Monarchie', 'Diplomatie', 'Kommunismus', 'Demokratie', 'unzivilisierte Barbarei' or 'Barbarismus'.
     */
    protected function getRegExpStaatsform_de()
    {
        return '(?:Monarchie|Diktatur|Kommunismus|Demokratie|Barbarismus|unzivilisierte\sBarbarei)';
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching building points
     *
     * Building points may be separated by thousand separator. Building points
     * start and end with digits. If at the right are three digits, there may
     * be a thousand separator left of them.
     *
     * Building points are decimal numbers.
     */
    protected function getRegExpBuildingPoints()
    {
        return $this->getRegExpDecimalNumber();
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching research points
     *
     * Research points may be seperated by thousand seperator. Research points
     * start and end with digits. If at the right are three digits, there may
     * be a thousand seperator left of them.
     *
     * Research points are decimal numbers.
     */
    protected function getRegExpResearchPoints()
    {
        return $this->getRegExpDecimalNumber();
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching total points
     *
     * Total points may be separated by thousand separator. Total points
     * start and end with digits. If at the right are three digits, there may
     * be a thousand separator left of them.
     *
     * Total points are decimal numbers.
     */
    protected function getRegExpTotalPoints()
    {
        return $this->getRegExpDecimalNumber();
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching points per day
     *
     * Points per day may be separated by thousand separator and may be separated
     * by floating point separator. Total points start and end with digits.
     * If at the right are three digits (left of the floating point separator),
     * there may be a thousand separator left of them.
     *
     * In fact, they are like the other points that additionally may be followed
     * by a floating point separator and two digits.
     */
    protected function getRegExpPointsPerDay()
    {
        return $this->getRegExpDecimalNumber() . '(?:' . $this->getRegExpFloatingPointSeperator() . '\d{2}' . ')?';
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching an kolo type
     *
     * Object types include:
     * - artifact stations
     * - battle stations
     * - colonies
     * - robot mining stations
     */
    protected function getRegExpKoloTypes()
    {
        return '(?:Kolonie|KB|RB|AB|SB|Kampfbasis|Sammelbasis|Artefaktsammelbasis|Artefaktbasis)';
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching an object type
     *
     * Object types include:
     * - no Object at all
     * - artifact stations
     * - battle stations
     * - colonies
     * - robot mining stations
     * - space stations
     */
    protected function getRegExpObjectTypes()
    {
        return '(?:Kolonie|---|Kampfbasis|KB|Sammelbasis|Artefaktsammelbasis|Artefaktbasis|RB|AB|SB|Raumstation)';
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching a planet type
     *
     */
    protected function getRegExpPlanetTypes()
    {
        return '(?:Steinklumpen|S|Nichts|N|Eisplanet|E|Gasgigant|G|Asteroid|A|Elektrosturm|Ionensturm|Raumverzerrung|grav.\sAnomalie)';
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching a date
     */
    protected function getRegExpKoloCoords()
    {
        $retVal = '(?:';

        $retVal .= '\d{1,2}';
        $retVal .= '\:';
        $retVal .= '\d{1,3}';
        $retVal .= '\:';
        $retVal .= '\d{1,2}';

        $retVal .= ')';

        return $retVal;
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching a date
     *
     * @TODO support the other date formats available in IW (Settings -> Administration -> Time formats)
     * @TODO don't match wrong dates like 51.45.2208 or 30.02.2009
     */
    protected function getRegExpDate()
    {
        $retVal = '';

        $retVal .= '(?:(?<=\s)|(?<=^))'; //the date shall start in a whitesace or start of line
        $retVal .= '\d{2}\.\d{2}\.\d{4}';
        $retVal .= '(?=\s|$)'; //the date shall end in a whitesace or end of line

        return $retVal;
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching a date
     *
     * @TODO don't match wrong dates like 51.45.2208 or 30.02.2009
     */
    protected function getRegExpDateTime()
    {
        $retVal = '';

        $retVal .= '(?:\b)'; //the date shall start in a word boundary
        $retVal .= '(?:';
        $retVal .= '\d{1,2}\D{0,2}[\s\.](?:\d{1,2}|\D+)[\s\.]\d{4}\s\d{1,2}\:\d{1,2}(?:\:(?:\d{1,2}))?';
        $retVal .= '|';
        $retVal .= '(?:\d{4})[\-\.](?:\d{1,2})[\-\.](?:\d{1,2})\s(?:\d{1,2})\:(?:\d{1,2})(?:\:(?:\d{1,2}))?';
        $retVal .= '|';
        $retVal .= '(?:\D+)\s(?:\d{1,2})\D{0,2}\,?\s(?:\d{4})\,?\s(?:\d{1,2})\:(?:\d{1,2})(?:\:(?:\d{1,2})|)(?:\s(?:am|pm))?';
        $retVal .= ')';
        $retVal .= '(?=\b)'; //the date shall end in a word boundary

        return $retVal;
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression patter matching possible flotten actions
     */
    protected function getRegExpShipActions()
    {
        $retVal = '';
        $retVal .= '(?:';
        $retVal .= 'Übergabe\s\(tr\sSchiffe\)|';
        $retVal .= 'Übergabe|';
        $retVal .= 'Transport|';
        $retVal .= 'Stationieren\s\&\sVerteidigen|';
        $retVal .= 'Stationieren|';
//      $retVal .= 'Ressourcenhandel\s\(ok\)|';
        $retVal .= 'Ressourcenhandel|';
        $retVal .= 'Ressourcen\sabholen|';
        $retVal .= 'Angriff|';
        $retVal .= 'Sondierung\s\(Geologie\)\s\(Scout\)|';
        $retVal .= 'Sondierung\s\(Geologie\)|';
        $retVal .= 'Sondierung\s\(Gebäude\)\s\(Scout\)|';
        $retVal .= 'Sondierung\s\(Gebäude\/Ress\)|';
        $retVal .= 'Sondierung\s\(Schiff\)\s\(Scout\)|';
        $retVal .= 'Sondierung\s\(Schiffe\/Def\/Ress\)|';
        $retVal .= 'Kolonisation|';
        $retVal .= 'Saveflug|';
        $retVal .= 'Basisaufbau\s\(Kampf\)|';
        $retVal .= 'Basisaufbau\s\(Ressourcen\)|';
        $retVal .= 'Basisaufbau\s\(Artefakte\)|';
        $retVal .= 'Massdriverpaket|';
//      $retVal .= 'Rückkehr(?=(?:\n+Stationieren)|)|';
        $retVal .= 'Rückkehr';
        $retVal .= ')';

        return $retVal;
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression patter matching possible flotten texts, after arrival
     */
    protected function getRegExpShipTexts()
    {
        // Texte für angekommende Flüge (zufällig)
        $retVal = '';

        $retVal .= '(?:';
        $retVal .= 'Lädt\sRess\sein\sund\saus|';
        $retVal .= 'Surft\sim\sBordnetz|';
        $retVal .= 'Schaut\sder\sfeschen\sPilotin\shinterher|';
        $retVal .= 'Hört\sMusik|';
        $retVal .= 'Erforscht\sgrade\sseine\sNase|';
        $retVal .= 'Faselt\swas\svon\sWurzelzwergen|';
        $retVal .= 'Im\sLandeanflug|';
        $retVal .= 'Sabbert\sdie\sInstrumente\svoll|';
        $retVal .= 'Versucht\sdie\srichtigen\sKnöpfe\sfür\sdie\sLandung\szu\sfinden|';
        $retVal .= 'Faselt\swirres\sZeug\sins\sInterkom|';
        $retVal .= 'Pfeift\sder\sfeschen\sPilotin\shinterher\sund\smacht\skomische\sAndeutungen|';
        $retVal .= 'Wartet\sauf\sWeihnachten|';
        $retVal .= 'Erklaert\sdie\sInfinitesimalrechnung|';
        $retVal .= 'Quatscht\smit\sder\sBodenkontrolle|';
        $retVal .= 'Wurzelzwergen,\süberall\sWurzelzwergen|';
        $retVal .= 'Liegt\sbesoffen\sin\sder\sEcke';
        $retVal .= ')';

        return $retVal;
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression patter matching possible areas
     */
    protected function getRegExpAreas()
    {
        $retVal = '';

        $retVal .= '(?:';
        $retVal .= 'Beobachtung|';
        $retVal .= 'Bevölkerung|';
        $retVal .= 'blubbernde\sGallertmasse|';
        $retVal .= 'Brause|';
        $retVal .= 'Bomber|';
        $retVal .= 'Chemie|';
        $retVal .= 'Dreadnoughts|';
        $retVal .= 'Ethik|';
        $retVal .= 'Evolution|';
        $retVal .= 'Forschung|';
        $retVal .= 'Freizeit|';
        $retVal .= 'Förderungsanlagen|';
        $retVal .= 'Imperiale\sHilfsgüter|';
        $retVal .= 'Industrie|';
        $retVal .= 'Informatik|';
        $retVal .= 'Jäger|';
        $retVal .= 'Kolonisation|';
        $retVal .= 'Korvetten|';
        $retVal .= 'Kreuzer|';
        $retVal .= 'Lager\s&\sBunker|';
        $retVal .= 'Militär|';
        $retVal .= 'orbitale\sVerteidigung|';
        $retVal .= 'orbitale\sDef|';
        $retVal .= 'planetare\sVerteidigung|';
        $retVal .= 'planetare\sDef|';
        $retVal .= 'Physik|';
        $retVal .= 'Prototypen|';
        $retVal .= 'Raumfahrt|';
        $retVal .= 'Schlachtschiffe|';
        $retVal .= 'Sondenverteidigung|';
        $retVal .= 'Sonden|';
        $retVal .= 'Spezielle\sAktionen|';
        $retVal .= 'Spezielle\sSchiffe|';
        $retVal .= 'Unbekannt|';
        $retVal .= 'Unifragen|';
        $retVal .= 'Verteidigung|';
        $retVal .= 'Wirtschaft\s\&\sVerwaltung|';
        $retVal .= 'Wirtschaft|';
        $retVal .= 'Zerstörer|';
        $retVal .= 'Zivile\sSchiffe';
        $retVal .= ')';

        return $retVal;
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression patter matching possible defence buildings
     */
    protected function getRegExpDefence()
    {
        $retVal = '';

        $retVal .= '(?:';
        $retVal .= 'SDI\sRaketensystem|';
        $retVal .= 'SDI\sAtomraketen|';
        $retVal .= 'SDI\sPlasmalaser|';
        $retVal .= 'SDI\sGravitonbeam|';
        $retVal .= 'Stopfentenwerfer|';
        $retVal .= 'Raketensatellit|';
        $retVal .= 'Gausskanonensatellit|';
        $retVal .= 'LaserSat|';
        $retVal .= 'PulslaserSat|';
        $retVal .= 'SD01\sGatling|';
        $retVal .= 'SD02\sPulslaser|';
        $retVal .= 'SDI\sStellarkonverter|';
        $retVal .= 'Fusiontorpedowerfer\s\(Sat\)|';
        $retVal .= 'MassdriverSat|';
        $retVal .= ')';

        return $retVal;
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching a mixed duration
     */
    protected function getRegExpMixedDuration()
    {
        $retVal = '';

        $retVal .= '(?:(?<=\s)|(?<=^))'; //the date shall start in a whitesace or start of line
        $retVal .= '(?:';
        $retVal .= '(?:\d+\s(?:Tag|Tage|day|days)\s+)?';
        $retVal .= '(?:\d{1,2})\:(?:\d{1,2})(?:\:(?:\d{1,2}))?';
        $retVal .= ')';
        $retVal .= '(?=\s|$)'; //the date shall end in a whitesace or end of line

        return $retVal;
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching a mixed time
     */
    protected function getRegExpMixedTime()
    {
        $retVal = '';

        $retVal .= '(?:(?<=\s)|(?<=^))'; //the date shall start in a whitesace or start of line
        $retVal .= '(?:';
        $retVal .= '(?:\d+\s(?:Tag|Tage|day|days)\s+)?';
        $retVal .= '(?:\d{1,2})\:(?:\d{1,2})(?:\:(?:\d{1,2}))?(?:\s(?:am|pm))?';
        $retVal .= ')';
        $retVal .= '(?=\s|$)'; //the date shall end in a whitesace or end of line

        return $retVal;
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching a resource
     *
     * by Mac: \w+ replaced with explicit resource names 06.2010
     */
    protected function getRegExpResource()
    {
        $retVal = '';

        $retVal .= '(?:(?<=\s)|(?<=^))'; //shall start in a whitesace or start of line

        $retVal .= '(?:Eisen|Eis|Wasser|Stahl|Energie|VV4A|FP|Forschungspunkte|chem\.\sElemente|Bevölkerung|Credits)';

        $retVal .= '(?=\:|,|\s|$)'; //shall end in a whitesace or : or , or end of line

        return $retVal;
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching a resource
     */
    protected function getRegExpSchiffe()
    {
        $retVal = '';

        $retVal .= '(?:(?<=\s)|(?<=^))'; //shall start in a whitesace or start of line
        $retVal .= '[\wäöü]+[\-[:blank:]\(\d\wäöü]+[[:blank:]\d\wäöü]+[\)\wäöü]*';
        $retVal .= '(?=\s|$)'; //shall end in a whitesace or end of line

        return $retVal;
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching a resource
     */
    protected function getRegExpBuildings()
    {
        $retVal = '';

        $retVal .= '(?:(?<=\s)|(?<=^))'; //shall start in a whitesace or start of line
        $retVal .= '[\wäöü]+[\-[:blank:]\(\d\wäöü]+[[:blank:]\d\wäöü]+[\)\wäöü]*';
        $retVal .= '(?=\s|$)'; //shall end in a whitesace or end of line

        return $retVal;
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression pattern matching a user title
     *
     * As I don't know anything about allowed characters, I allow anything.
     * Multiple spaces are not allowed.
     */
    protected function getRegExpUserTitle()
    {
        $retVal = '';

        $retVal .= '(?:(?<=\s)|(?<=^))'; //the user name shall start in a whitesace or start of line
        $retVal .= '(?!  )';             //the user name must not contain multiple spaces

        $retVal .= '[^ ]';               //first character must not be a space
        $retVal .= '(?:';                //propably, there will be more characters
        $retVal .= '.+';                 //characters
        $retVal .= '[^ ]';               //last character must not be a space
        $retVal .= ')?';

        $retVal .= '(?=\s|$)';           //the username shall end in a whitesace or end of line

        return $retVal;
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression patter matching possible flotten capabilities
     */
    protected function getRegExpPlanetaryProblems()
    {
        $reDuration = $this->getRegExpMixedDuration();

        $retVal = '';

        $retVal .= '(?:';
        $retVal .= 'Bev.{1,3}lkerungsmangel|';
        $retVal .= 'Scannerabschaltung\swegen\sChemiemangel|';
        $retVal .= 'Werften\ssind\sruntergefallen\s\*n.{1,3}l\*|';
        $retVal .= 'Werften\ssind\swieder\soben\sin\s' . $reDuration . '|';
        $retVal .= 'Energiemangel|';
        $retVal .= 'Forschungsausfall\sdurch\sEnergiemangel|';
        $retVal .= 'Wassermangel|';
        $retVal .= ')';

        return $retVal;
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * returns a regular expression patter matching possible flotten capabilities
     */
    protected function getRegExpShipCapabilities()
    {
        $retVal = '';

        $retVal .= '(?:';
        $retVal .= '.{1,3}bergebbar|';
        $retVal .= '.{1,3}bergebbar\san\seigene\sPlaneten|';
        $retVal .= 'Stationierbar|';
        $retVal .= 'Transport|';
        $retVal .= 'Angreifen\s\/\sVerteidigen|';
        $retVal .= 'Pl.{1,3}ndern|';
        $retVal .= 'Sondieren|';
        $retVal .= 'Kolonisieren|';
        $retVal .= 'Kampfbasis\saufbauen|';
        $retVal .= 'Ressbasis\saufbauen|';
        $retVal .= 'Artefaktbasis\saufbauen|';
        $retVal .= 'Bombardieren|';
        $retVal .= 'Tarnbar|';
        $retVal .= 'Terraformer';
        $retVal .= ')';

        return $retVal;
    }

}