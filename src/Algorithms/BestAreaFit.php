<?php

namespace BinPacking\Algorithms;

use BinPacking\{RectangleBinPack, Rectangle};
use BinPacking\Helpers\RectangleHelper;

class BestAreaFit
{
    public static function findNewPosition(
        RectangleBinPack $bin,
        Rectangle $rectangle,
        int &$bestAreaFit,
        int &$bestShortSideFit
    ) : ?Rectangle {
        $bestNode = null;
        $bestAreaFit = RectangleHelper::MAXINT;
        $bestShortSideFit = RectangleHelper::MAXINT;
        
        $rectWidth = $rectangle->width;
        $rectHeight = $rectangle->height;
        $flipAllowed = $bin->isFlipAllowed();
        
        foreach ($bin->getFreeRectangles() as $freeRect) {
            $rotate = false;
            $freeWidth = $freeRect->width;
            $freeHeight = $freeRect->height;

            $areaFit = ($freeWidth * $freeHeight) - ($rectWidth * $rectHeight);

            if ($freeWidth >= $rectWidth && $freeHeight >= $rectHeight) {
                $leftoverHoriz = $freeWidth - $rectWidth;
                $leftoverVert = $freeHeight - $rectHeight;
                $shortSideFit = min($leftoverHoriz, $leftoverVert);

                if ($areaFit < $bestAreaFit || ($areaFit === $bestAreaFit && $shortSideFit < $bestShortSideFit)) {
                    $bestNode = [$rectWidth, $rectHeight, $freeRect->xPos, $freeRect->yPos];

                    $bestShortSideFit = $shortSideFit;
                    $bestAreaFit = $areaFit;
                }
            }

            if ($flipAllowed && $freeWidth >= $rectHeight && $freeHeight >= $rectWidth) {
                $leftoverHoriz = $freeWidth - $rectHeight;
                $leftoverVert = $freeHeight - $rectWidth;
                $shortSideFit = min($leftoverHoriz, $leftoverVert);

                if ($areaFit < $bestAreaFit || ($areaFit === $bestAreaFit && $shortSideFit < $bestShortSideFit)) {
                    $bestNode = [$rectWidth, $rectHeight, $freeRect->xPos, $freeRect->yPos];
                    $rotate = true;
                             
                    $bestShortSideFit = $shortSideFit;
                    $bestAreaFit = $areaFit;
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
