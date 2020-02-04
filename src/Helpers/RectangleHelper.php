<?php

namespace BinPacking\Helpers;

use BinPacking\Rectangle;

class RectangleHelper
{
    public const MAXINT = 99999999;

    /**
     * Check if the first rectangle is within the second rectangle
     *
     * @param Rectangle $rectA
     * @param Rectangle $rectB
     * @return bool
     */
    public static function isContainedIn(Rectangle $rectA, Rectangle $rectB) : bool
    {
        return $rectA->xPos >= $rectB->xPos && $rectA->yPos >= $rectB->yPos
            && $rectA->xPos + $rectA->width <= $rectB->xPos + $rectB->width
            && $rectA->yPos + $rectA->height <= $rectB->yPos + $rectB->height;
    }
}
