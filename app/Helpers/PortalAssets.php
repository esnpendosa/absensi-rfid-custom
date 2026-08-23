<?php

namespace App\Helpers;

class PortalAssets
{
    public static function getLogo(): string
    {
        return asset('images/logo-smk.png');
    }

    public static function getBuilding(): string
    {
        return asset('images/hero-building-clean.png');
    }

    public static function getCard1(): string
    {
        return asset('images/cards/art-01.png');
    }

    public static function getCard2(): string
    {
        return asset('images/cards/art-02.png');
    }

    public static function getCard3(): string
    {
        return asset('images/cards/art-03.png');
    }

    public static function getCard4(): string
    {
        return asset('images/cards/art-04.png');
    }

    public static function getCard5(): string
    {
        return asset('images/cards/art-05.png');
    }
}