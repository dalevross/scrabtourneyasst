<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This is a visual test case, testing canvas fundamental canvas functionality.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This library is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 2.1 of the License, or (at your
 * option) any later version. This library is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser
 * General Public License for more details. You should have received a copy of
 * the GNU Lesser General Public License along with this library; if not, write
 * to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 * 02111-1307 USA
 *
 * @category  Images
 * @package   Image_Canvas
 * @author    Jesper Veggerby <pear.nosey@veggerby.dk>
 * @author    Stefan Neufeind <pear.neufeind@speedpartner.de>
 * @copyright 2003-2009 The PHP Group
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version   SVN: $Id: pdf.php 292377 2009-12-20 21:26:22Z neufeind $
 * @link      http://pear.php.net/package/Image_Canvas
 */
  
// SPECIFY HERE WHERE A TRUETYPE FONT CAN BE FOUND
$testFont = 'c:/windows/fonts/Arial.ttf';

require_once 'Image/Canvas.php';

$canvas =& Image_Canvas::factory('pdf', array('page' => 'A4', 'align' => 'center', 'width' => 600, 'height' => 600));

require_once './canvas_body.php';

?>
