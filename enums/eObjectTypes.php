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
 * @subpackage enums
 */

namespace libIwParsers\enums;

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

class eObjectTypes
{
    const noObject = 'noObject';

    const Kolonie = 'Kolonie';
    const Raumstation = 'Raumstation';
    const Artefaktbasis = 'Artefaktbasis';
    const Kampfbasis = 'Kampfbasis';
    const Sammelbasis = 'Sammelbasis';

    //TODO: validate englisch constants
    const colony = 'colony';
    const spaceStation = 'spaceStation';
    const artifactStation = 'artifactStation';
    const battleStation = 'battleStation';
    const miningStation = 'miningStation';
}