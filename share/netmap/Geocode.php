<?php

/*****************************************************************************
 *
 * Copyright (C) 2009 NagVis Project
 *
 * License:
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2 as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *
 *****************************************************************************/

class Geocode
{
	/**
	 * @param  string $address
	 * @return array
	 */
	public function resolve($address)
	{
		$locations = array();

		$request_url = 'http://maps.google.com/maps/geo?output=xml&key=' . $this->getKey()
			. '&sensor=false&oe=utf8&q=' . urlencode($address);
		if (($xml = @simplexml_load_file($request_url)) === FALSE)
			throw new Exception('Cannot connect to Google Maps');

		switch ($xml->Response->Status->code)
		{
			case "200":
				foreach ($xml->Response->Placemark as $placemark)
				{
					$coordinates = explode(',', (string)$placemark->Point->coordinates);
					$locations[] = array(
						'point' =>  ($coordinates[1] . ',' . $coordinates[0]),
						'address' =>(string)$placemark->address
					);
				}
				break;

			case "500":
				throw new Exception('Google Maps server error');

			case "602":
				throw new Exception('Could not resolve the address');

			case "610":
				throw new Exception('Invalid Google Maps key');

			case "620":
				throw new Exception('Geocoding requests are sent too frequently,'
					. ' please repeat the action again after some time');

			default:
				throw new Exception('Unexpected Google Maps error');
		}

		return $locations;
	}

	public function getKey()
	{
		if (($key = file_get_contents('GoogleMaps.key')) !== FALSE)
			return trim($key);
		else
			throw new Exception('Could not read GoogleMaps.key');
	}

	public function saveKey($key)
	{
		if (file_put_contents('GoogleMaps.key', $key) !== FALSE)
			return;
		else
			throw new Exception('Could not write GoogleMaps.key');
	}
}

?>
