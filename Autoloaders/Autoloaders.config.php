<?php

/*
 * Copyright (C) 2016 Matthew McNaney <mcnaneym@appstate.edu>.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

/**
 * List of autoloaders that will be registered when Canopy starts up.
 * The array keys are file names (without the .php extension), and the values
 * are the function names within those files.
 *
 * @author Matthew McNaney <mcnaneym at appstate dot edu>
 * @package Canopy
 */
$autoloaders = array(
    'composer' => '',
    'canopy' => 'CanopyLoader',
    'legacy' => 'LegacyLoader',
    'LegacySrcLoader' => 'LegacySrcLoader',
    'Phpws2Loader' => 'Phpws2Loader'
);
