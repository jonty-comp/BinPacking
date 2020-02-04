<?php

namespace BinPacking;

use BinPacking\Algorithms\{BestAreaFit, BestLongSideFit, BottomLeft, BestShortSideFit};
use BinPacking\Helpers\RectangleHelper;

class RectangleBinPack
{
    /**
     * Width of the bin to pack into
     *
     * @var int
     */
    public $binWidth;
    
    /**
     * Height of the bin to pack into
     *
     * @var int
     */
    public $binHeight;

    /**
     * Allow 90 degree rotation or not
     *
     * @var bool
     */
    public $allowFlip;

    /**
     * Used rectangles array
     *
     * @var Rectangle[]
     */
    public $usedRectangles;
    
    /**
     * Used rectangles array
     *
     * @var Rectangle[]
     */
    public $freeRectangles;

    /**
     * Array of rectangles unable to pack in the bin
     *
     * @var Rectangle[]
     */
    public $cantPack = [];

    /**
     * Bottom border of the bin that cannot be used
     */
    public $bottomBorder;

    /**
     * Left border of thebin that cannot be used
     */
    public $leftBorder;

    /**
     * Chosen packing method
     *
     * @var string
     */
    public $method = BestAreaFit::class;

    /**
     * Construct the bin for packing into
     *
     * @param int $width  Width of the bin
     * @param int $height Height of the bin
     * @param boolean $flip   Allow rotation of the items to pack
     */
    public function __construct(int $width, int $height, bool $flip = true, $method)
    {
        $this->binWidth = $width;
        $this->binHeight = $height;
        $this->allowFlip = $flip;

        $this->bottomBorder = 0;
        $this->leftBorder = 0;

        $this->usedRectangles = [];
        $this->freeRectangles = [];

        switch ($method) {
            case 'RectBottomLeftRule':
                $this->method = BottomLeft::class;
                break;

            case 'RectBestAreaFit':
                $this->method = BestAreaFit::class;
                break;

            case 'RectBestLongSideFit':
                $this->method = BestLongSideFit::class;
                break;

            case 'RectBestShortSideFit':
                $this->method = BestShortSideFit::class;
                break;

            default:
                throw new \InvalidArgumentException("Method {$method} not recognised.");
        }
    }

    /**
     * Initialize the free bins to pack into
     *
     * @return void
     */
    public function init() : RectangleBinPack
    {
        // Create free rectangle
        $initialFree = new Rectangle($this->binWidth - $this->leftBorder, $this->binHeight - $this->bottomBorder);
        $initialFree->setPosition($this->leftBorder, $this->bottomBorder);

        $this->freeRectangles = [$initialFree];
        return $this;
    }

    /**
     * Set the bottom border of the sheet (that cannot be but)
     *
     * @param integer $bottomBorder
     * @return RectangleBinPack
     */
    public function setBottomBorder(int $bottomBorder) : RectangleBinPack
    {
        $this->bottomBorder = $bottomBorder;
        return $this;
    }

    /**
     * Set the left border of the sheet that cannot be cut
     *
     * @param integer $leftBorder
     * @return RectangleBinPack
     */
    public function setLeftBorder(int $leftBorder) : RectangleBinPack
    {
        $this->leftBorder = $leftBorder;
        return $this;
    }

    /**
     * Get the width of the bin
     *
     * @return integer
     */
    public function getBinWidth() : int
    {
        return $this->binWidth;
    }

    /**
     * Get the height of the bin
     *
     * @return integer
     */
    public function getBinHeight() : int
    {
        return $this->binHeight;
    }

    /**
     * Get whether the rectangles can be flipped or not
     *
     * @return boolean
     */
    public function isFlipAllowed() : bool
    {
        return $this->allowFlip;
    }

    /**
     * Get the array of rectangles unable to pack
     *
     * @return Rectangle[]
     */
    public function getCantPack() : array
    {
        return $this->cantPack;
    }

    /**
     * Get the rectangles that are "used" aka been placed in the bin
     *
     * @return Rectangle[]
     */
    public function getUsedRectangles() : array
    {
        return $this->usedRectangles;
    }

    /**
     * Get the rectangles that have not been used
     *
     * @return Rectangle[]
     */
    public function getFreeRectangles() : array
    {
        return $this->freeRectangles;
    }

    /**
     * Get the percentage of the area of the bin used
     *
     * @return float
     */
    public function getUsage() : float
    {
        $usedSurfaceArea = 0;
        foreach ($this->usedRectangles as $usedRect) {
            $usedSurfaceArea += $usedRect->width * $usedRect->height;
        }

        return $usedSurfaceArea / ($this->binWidth * $this->binHeight);
    }

    /**
     * Insert a rectangle for a space to be found
     *
     * @param Rectangle $rect
     * @return Rectangle
     */
    public function insert(Rectangle $rect) : ?Rectangle
    {
        $newNode = null;

        $score1 = RectangleHelper::MAXINT;
        $score2 = RectangleHelper::MAXINT;

        $newNode = $this->method::findNewPosition($this, $rect, $score1, $score2);
        
        if (!$newNode) {
            return $newNode;
        }

        $this->placeRect($newNode);

        return $newNode;
    }

    /**
     * Insert multiple rectangles at once (trying to find the best fit)
     *
     * @param Rectangle[] $toPack
     * @return Rectangle[]
     */
    public function insertMany(array $toPack) : array
    {
        $packed = [];

        while (count($toPack) > 0) {
            $bestScore1 = RectangleHelper::MAXINT;
            $bestScore2 = RectangleHelper::MAXINT;
            $bestRectIndex = -1;
            $bestNode = null;
            
            
            $count = count($toPack);
            for ($i = 0; $i < $count; ++$i) {
                $score1 = RectangleHelper::MAXINT;
                $score2 = RectangleHelper::MAXINT;
                $newNode = $this->scoreRect(
                    $toPack[$i],
                    $score1,
                    $score2
                );

                if ($score1 < $bestScore1 || ($score1 == $bestScore1 && $score2 < $bestScore2)) {
                    $bestScore1 = $score1;
                    $bestScore2 = $score2;
                    $bestNode = $newNode;
                    $bestRectIndex = $i;
                }
            }

            // Can't fit the rectangle
            if ($bestRectIndex == -1) {
                $this->cantPack = $toPack;
                $toPack = [];
            } else {
                $this->placeRect($bestNode);
                $packed[] = $bestNode;
                unset($toPack[$bestRectIndex]);
                $toPack = array_values($toPack);
            }
        }

        return $packed;
    }

    /**
     * Place the rectangle in the bin
     *
     * @param Rectangle $node
     * @return void
     */
    public function placeRect(Rectangle $node)
    {
        $numRectsToProcess = count($this->freeRectangles);
        for ($i = 0; $i < $numRectsToProcess; ++$i) {
            if ($this->splitFreeNode($this->freeRectangles[$i], $node)) {
                unset($this->freeRectangles[$i]);
                $this->freeRectangles = array_values($this->freeRectangles);
                --$i;
                --$numRectsToProcess;
            }
        }

        $this->pruneFreeList();

        $this->usedRectangles[] = $node;
    }

    /**
     * Attempt to get a "score" for how well the rectangle is placed (based on the algorithm used)
     *
     * @param int $width
     * @param int $height
     * @param int $score1
     * @param int $score2
     * @return Rectangle|null
     */
    public function scoreRect(Rectangle $rect, int &$score1, int &$score2) : ?Rectangle
    {
        $score1 = RectangleHelper::MAXINT;
        $score2 = RectangleHelper::MAXINT;
        
        $newNode = $this->method::findNewPosition($this, $rect, $score1, $score2);
        
        if (!$newNode) {
            $score1 = RectangleHelper::MAXINT;
            $score2 = RectangleHelper::MAXINT;
        }

        return $newNode;
    }

    /**
     * Remove the "used" node from the free node, then split the free node into 2 further free nodes
     *
     * @param Rectangle $freeNode
     * @param Rectangle $usedNode
     * @return boolean
     */
    public function splitFreeNode(Rectangle $freeNode, Rectangle $usedNode) : bool
    {
        $usedX = $usedNode->xPos;
        $usedY = $usedNode->yPos;
        $usedW = $usedNode->width;
        $usedH = $usedNode->height;

        $freeX = $freeNode->xPos;
        $freeY = $freeNode->yPos;
        $freeW = $freeNode->width;
        $freeH = $freeNode->height;

        // Test with SAT if the rectangles even intersect
        if ($usedX >= ($freeX + $freeW)
            || ($usedX + $usedW) <= $freeX
            || $usedY >= ($freeY + $freeH)
            || ($usedY + $usedH) <= $freeY) {
            return false;
        }

        if ($usedX < ($freeX + $freeW)
            && ($usedX + $usedW) > $freeX) {
            // New node at the top side of the used node.
            if ($usedY > $freeY
                && $usedY < ($freeY + $freeH)) {
                $newNode = clone $freeNode;
                $newNode->setHeight($usedY - $newNode->yPos);
                $this->freeRectangles[] = $newNode;
            }

            // New node at the bottom side of the used node.
            if (($usedY + $usedH) < ($freeY + $freeH)) {
                $newNode = clone $freeNode;
                $newNode->setY($usedY + $usedH);
                $newNode->setHeight(
                    ($freeY + $freeH) - ($usedY + $usedH)
                );
                $this->freeRectangles[] = $newNode;
            }
        }

        if ($usedY < ($freeY + $freeH)
            && ($usedY + $usedH) > $freeY) {
            // New node at the left side of the used node.
            if ($usedX > $freeX
                && $usedX < ($freeX + $freeW)) {
                $newNode = clone $freeNode;
                $newNode->setWidth($usedX - $newNode->xPos);
                $this->freeRectangles[] = $newNode;
            }

            // New node at the right side of the used node.
            if (($usedX + $usedW) < ($freeX + $freeW)) {
                $newNode = clone $freeNode;
                $newNode->setX($usedX + $usedW);
                $newNode->setWidth(
                    ($freeX + $freeW) - ($usedX + $usedW)
                );
                $this->freeRectangles[] = $newNode;
            }
        }

        // Check if the used node has a window
        if ($usedNode instanceof WindowedRectangle) {
            $newNode = clone $usedNode->getWindow();
            $newNode->setX($usedX + $usedNode->getLeftBorder() + WindowedRectangle::INNERBORDER);
            $newNode->setY($usedY + $usedNode->getBottomBorder() + WindowedRectangle::INNERBORDER);

            $this->freeRectangles[] = $newNode;
        }

        return true;
    }

    /**
     * Remove any free rectangles that lie within another free rectangle
     *
     * @return void
     */
    public function pruneFreeList() : void
    {
        $count = count($this->freeRectangles);
        for ($i = 0; $i < $count; ++$i) {
            for ($j = $i + 1; $j < $count; ++$j) {
                if (RectangleHelper::isContainedIn($this->freeRectangles[$i], $this->freeRectangles[$j])) {
                    unset($this->freeRectangles[$i]);
                    $count--;
                    $this->freeRectangles = array_values($this->freeRectangles);
                    --$i;
                    break;
                }

                if (RectangleHelper::isContainedIn($this->freeRectangles[$j], $this->freeRectangles[$i])) {
                    unset($this->freeRectangles[$j]);
                    $count--;
                    $this->freeRectangles = array_values($this->freeRectangles);
                    --$j;
                    break;
                }
            }
        }
    }
}
