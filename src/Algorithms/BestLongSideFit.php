<?php

namespace BinPacking\Algorithms;

use BinPacking\{RectangleBinPack, Rectangle};
use BinPacking\Helpers\RectangleHelper;

class BestLongSideFit
{
    public static function findNewPosition(
        RectangleBinPack $bin,
        Rectangle $rectangle,
        int &$bestShortSideFit,
        int &$bestLongSideFit
    ) : ?Rectangle {
        $bestNode = null;
        $bestLongSideFit = RectangleHelper::MAXINT;
        $bestShortSideFit = RectangleHelper::MAXINT;

        $rectW = $rectangle->width;
        $rectH = $rectangle->height;
        $flipAllowed = $bin->isFlipAllowed();

        foreach ($bin->getFreeRectangles() as $freeRect) {
            $rotate = false;
            $freeW = $freeRect->width;
            $freeH = $freeRect->height;

            if ($freeW >= $rectW && $freeH >= $rectH) {
                $leftoverHoriz = $freeW - $rectW;
                $leftoverVert = $freeH - $rectH;
                $shortSideFit = min($leftoverHoriz, $leftoverVert);
                $longSideFit = max($leftoverHoriz, $leftoverVert);

                if ($longSideFit < $bestLongSideFit || ($longSideFit == $bestLongSideFit && $shortSideFit < $bestShortSideFit)) {
                    $bestNode = [$rectW, $rectH, $freeRect->xPos, $freeRect->yPos];

                    $bestShortSideFit = $shortSideFit;
                    $bestLongSideFit = $longSideFit;
                }
            }

            if ($flipAllowed && $freeW >= $rectH && $freeH >= $rectW) {
                $leftoverHoriz = $freeW - $rectH;
                $leftoverVert = $freeH - $rectW;
                $shortSideFit = min($leftoverHoriz, $leftoverVert);
                $longSideFit = max($leftoverHoriz, $leftoverVert);

                if ($longSideFit < $bestLongSideFit || ($longSideFit == $bestLongSideFit && $shortSideFit < $bestShortSideFit)) {
                    $bestNode = [$rectW, $rectH, $freeRect->xPos, $freeRect->yPos];
                    $rotate = true;
                         
                    $bestShortSideFit = $shortSideFit;
                    $bestLongSideFit = $longSideFit;
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
