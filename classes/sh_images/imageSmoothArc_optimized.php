<?php

/* 
 * 
 * This file has two parts with different copyrights. 
 * See below for the class, and at the bottom of the file, after the lines filled with stars
 * for the copyrights of everything below that lines.
 * 
 */

/**
 * @author Brice PARENT (Websailors) for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if( !defined( 'SH_MARKER' ) ) {
    header( 'location: directCallForbidden.php' );
}

/**
 * Class that serves images, and asks to build them if possible.
 */
class sh_shape {
    const ARC_ALL_CORNERS = 'ALL';
    const ARC_NORTH_EST = 'NE';
    const ARC_SOUTH_EST = 'SE';
    const ARC_SOUTH_WEST = 'SW';
    const ARC_NORTH_WEST = 'NW';
    
    /**
     * Rounds the selected corner(s) of the image $image.
     * @param resource $image The GD resource of the image to remove corners from
     * @param str $arcPosition Any of the following constants : ARC_ALL_CORNERS, ARC_NORTH_EST,
     * ARC_SOUTH_EST, ARC_SOUTH_WEST, ARC_NORTH_WEST, depending of which corner we want to make round.
     * @return resource the resource identifier.
     */
    public static function arc($image,$arcPosition = self::ARC_ALL_CORNERS){
        if($arcPosition == self::ARC_ALL_CORNERS){
            $image = self::arc($image, self::ARC_NORTH_EST);
            $image = self::arc($image, self::ARC_SOUTH_EST);
            $image = self::arc($image, self::ARC_SOUTH_WEST);
            $image = self::arc($image, self::ARC_NORTH_WEST);
            return $image;
        }
        
        
        
        return $image;
    }
}


/*
 * *************************************************************************
 * THE LICENCE IS NO MORE THE SAME AFTER THAT POINT
 * *************************************************************************
 */


/*
    
    Copyright (c) 2006-2008 Ulrich Mierendorff

    Permission is hereby granted, free of charge, to any person obtaining a
    copy of this software and associated documentation files (the "Software"),
    to deal in the Software without restriction, including without limitation
    the rights to use, copy, modify, merge, publish, distribute, sublicense,
    and/or sell copies of the Software, and to permit persons to whom the
    Software is furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
    THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
    FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
    DEALINGS IN THE SOFTWARE.

*/

function imageSmoothArcDrawSegment (&$img, $cx, $cy, $a, $b, $color, $start, $stop, $seg)
{
    // Originally written from scratch by Ulrich Mierendorff, 06/2006
    // Rewritten and improved, 04/2007, 07/2007
    // Optimized circle version: 03/2008
    
    // Please do not use THIS function directly. Scroll down to imageSmoothArc(...).
    
    $fillColor = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], $color[3] );
    switch ($seg)
    {
        case 0: $xp = +1; $yp = -1; $xa = 1; $ya = -1; break;
        case 1: $xp = -1; $yp = -1; $xa = 0; $ya = -1; break;
        case 2: $xp = -1; $yp = +1; $xa = 0; $ya = 0; break;
        case 3: $xp = +1; $yp = +1; $xa = 1; $ya = 0; break;
    }
    for ( $x = 0; $x <= $a; $x += 1 ) {
        $y = $b * sqrt( 1 - ($x*$x)/($a*$a) );
        $error = $y - (int)($y);
        $y = (int)($y);
        $diffColor = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error );
        imageSetPixel($img, $cx+$xp*$x+$xa, $cy+$yp*($y+1)+$ya, $diffColor);
        imageLine($img, $cx+$xp*$x+$xa, $cy+$yp*$y+$ya , $cx+$xp*$x+$xa, $cy+$ya, $fillColor);
    }
    for ( $y = 0; $y < $b; $y += 1 ) {
        $x = $a * sqrt( 1 - ($y*$y)/($b*$b) );
        $error = $x - (int)($x);
        $x = (int)($x);
        $diffColor = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error );
        imageSetPixel($img, $cx+$xp*($x+1)+$xa, $cy+$yp*$y+$ya, $diffColor);
    }
}


function imageSmoothArc ( &$img, $cx, $cy, $w, $h, $color, $start, $stop)
{
    // Originally written from scratch by Ulrich Mierendorff, 06/2006
    // Rewritten and improved, 04/2007, 07/2007
    // Optimized circle version: 03/2008
    // compared to old version:
    // + Support for transparency added
    // + Improved quality of edges & antialiasing
    
    // note: This function does not represent the fastest way to draw elliptical
    // arcs. It was written without reading any papers on that subject. Better
    // algorithms may be twice as fast or even more.
    
    // Parameters:
    // $cx      - Center of ellipse, X-coord
    // $cy      - Center of ellipse, Y-coord
    // $w       - Width of ellipse ($w >= 2)
    // $h       - Height of ellipse ($h >= 2 )
    // $color   - Color of ellipse as a four component array with RGBA
    // $start   - Starting angle of the arc: 0, PI/2, PI, PI/2*3, 2*PI,... (0,90°,180°,270°,360°,...)
    // $stop    - Stop     angle of the arc: 0, PI/2, PI, PI/2*3, 2*PI,... (0,90°,180°,270°,360°,...)
    // $start _can_ be greater than $stop!
    // If any value is not in the given range, results are undefined!
    
    // This script does not use any special algorithms, everything is completely
    // written from scratch; see http://de.wikipedia.org/wiki/Ellipse for formulas.
    
    while ($start < 0)
        $start += 2*M_PI;
    while ($stop < 0)
        $stop += 2*M_PI;
    
    while ($start > 2*M_PI)
        $start -= 2*M_PI;
    
    while ($stop > 2*M_PI)
        $stop -= 2*M_PI;
    
    
    if ($start > $stop)
    {
        imageSmoothArc ( &$img, $cx, $cy, $w, $h, $color, $start, 2*M_PI);
        imageSmoothArc ( &$img, $cx, $cy, $w, $h, $color, 0, $stop);
        return;
    }
    
    $a = 1.0*round ($w/2);
    $b = 1.0*round ($h/2);
    $cx = 1.0*round ($cx);
    $cy = 1.0*round ($cy);
    
    for ($i=0; $i<4;$i++)
    {
        if ($start < ($i+1)*M_PI/2)
        {
            if ($start > $i*M_PI/2)
            {
                if ($stop > ($i+1)*M_PI/2)
                {
                    imageSmoothArcDrawSegment($img, $cx, $cy, $a, $b, $color, $start, ($i+1)*M_PI/2, $i);
                }
                else
                {
                    imageSmoothArcDrawSegment($img, $cx, $cy, $a, $b, $color, $start, $stop, $i);
                    break;
                }
            }
            else
            {
                if ($stop > ($i+1)*M_PI/2)
                {
                    imageSmoothArcDrawSegment($img, $cx, $cy, $a, $b, $color, $i*M_PI/2, ($i+1)*M_PI/2, $i);
                }
                else
                {
                    imageSmoothArcDrawSegment($img, $cx, $cy, $a, $b, $color, $i*M_PI/2, $stop, $i);
                    break;
                }
            }
        }
    }
}
?>
