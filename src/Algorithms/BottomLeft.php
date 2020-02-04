<?php

namespace BinPacking\Algorithms;

use BinPacking\{RectangleBinPack, Rectangle};
use BinPacking\Helpers\RectangleHelper;

class BottomLeft
{
    public static function findNewPosition(
        RectangleBinPack $bin,
        Rectangle $rectangle,
        int &$bestY,
        int &$bestX
    ) : ?Rectangle {
        $bestNode = null;
        $bestX = RectangleHelper::MAXINT;
        $bestY = RectangleHelper::MAXINT;

        $rectW = $rectangle->width;
        $rectH = $rectangle->height;
        $flipAllowed = $bin->isFlipAllowed();

        foreach ($bin->getFreeRectangles() as $freeRect) {
            $rotate = false;
            $freeW = $freeRect->width;
            $freeH = $freeRect->height;

            // Try to place the rectangle in upright (non-flipped) orientation
            if ($freeW >= $rectW && $freeH >= $rectH) {
                $topSideY = $freeRect->yPos + $rectH;
                if ($topSideY < $bestY || ($topSideY == $bestY && $freeRect->xPos < $bestX)) {
                    $bestNode = [$rectW, $rectH, $freeRect->xPos, $freeRect->yPos];
                    $bestY = $topSideY;
                    $bestX = $freeRect->xPos;
                }
            }

            if ($flipAllowed && $freeW >= $rectH && $freeH >= $rectW) {
                $topSideY = $freeRect->yPos + $rectW;
                if ($topSideY < $bestY || ($topSideY == $bestY && $freeRect->xPos < $bestX)) {
                    $bestNode = [$rectW, $rectH, $freeRect->xPos, $freeRect->yPos];
                    $rotate = true;

                    $bestY = $topSideY;
                    $bestX = $freeRect->xPos;
                }
            }
        }


        if ($bestNode) {
            $bestRect = clone $rectangle;

            $bestRect->width = $bestNode[0];
            $bestRect->height = $bestNode[1];

            $bestRect->xPos = $bestNode[2];
            $bestRect->yPos = $bestNode[3];

            if ($rotate) {
                $bestRect->rotate();
            }

            return $bestRect;
        }

        return null;
    }
}
