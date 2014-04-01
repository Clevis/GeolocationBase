<?php

namespace Clevis\Geolocation\Nominatim;

use Nette\Object;
use Nette\InvalidArgumentException;
use Clevis\Geolocation\Position;
use Clevis\Geolocation\Rectangle;
use Clevis\Geolocation\Address;
use Clevis\Geolocation\IGeocodingService;
use Clevis\Geolocation\ConnectionException;
use Clevis\Geolocation\InvalidStatusException;
use Clevis\Geolocation\InvalidResponseException;


/**
 * Client for Nominatim Geocoding API
 *
 * Nominatim.openstreetmap.org requirements:
 * - No heavy uses (an absolute maximum of 1 request/s).
 * - Provide a valid HTTP Referer or User-Agent identifying the application (stock User-Agents as set by http libraries will not do).
 * - Clearly display attribution as suitable for your medium.
 * - Data is provided under the ODbL licence which requires to share alike (although small extractions are likely to be covered by fair usage / fair dealing)."
 *
 * Own instances of nominatim are not subject to usage restrictions (except for the license, 'course).
 *
 * May be overridden for different nominatim instances (see $base_url)
 *
 * @author Jan Martinec
 */
class GeocodingClient extends Object implements IGeocodingService
{

	/** @var string  we use our own nominatim instance, instead of the public one */
	protected $baseUrl = 'http://nominatim.limes.dyndns.org:19401/';

	/** @var string  username for HTTP auth; set to empty for "no HTTP auth required"; just a simple restriction not a real security */
	protected $username = 'rekolacz';

	/** @var string  password for HTTP auth */
	protected $password = 'rekolacz';

	/** @var string  email needed in case of capacity problems (not in case of own instance) */
	protected $email = 'info@rekola.cz';

	/** @var string  User-Agent string; required! */
	protected $ua = 'ReKolaSMS http://rekola.cz/ info@rekola.cz 1.0 2014-01-07';

	protected $munge_address = true; // Nominatim has problems when ZIP cosdes are included, or city parts ("Praha 3"); this toggle attempts to strip such extraneous info

	/**
	 * allows use of other nominatims
	 */
	public function setBaseUrl($url)
	{
		$this->baseUrl = $url;
	}

	/**
	 * Get GPS position for given address
	 *
	 * @param Address|string
	 * @param array
	 * @param bool
	 * @return Position|NULL
	 */
	public function getPosition($address, $options = array(), $fullResult = FALSE)
	{
		// address
		if ($address instanceof Address)
		{
			$address = (string) $address;
		}
		elseif (!is_string($address))
		{
			throw new InvalidArgumentException('Address should be instance of Address or a string.');
		}

		// bounds
		if (!empty($options['bounds']) && $options['bounds'] instanceof Rectangle)
		{
			/** @var Rectangle $rec */
			$rec = $options['bounds'];
			$b = $rec->getLatLonBounds();
			$options['viewboxlbrt'] = "$b[0],$b[1]|$b[2],$b[3]";
			$options['bounded'] = 1;
			unset($options['bounds']);
		}

		if ($this->munge_address) {
			$address = $this->mungeAddress($address);
		}


		$result = $this->getResponse($address, $options);

		return $fullResult ? $result : $result->getPosition();
	}

	/**
	 * Get address for given GPS position
	 *
	 * @param Position
	 * @param array
	 * @param bool
	 * @return Address|NULL
	 */
	public function getAddress(Position $position, $options = array(), $fullResult = FALSE)
	{
		$result = $this->getResponse($position, $options);

		return $fullResult ? $result : $result->getAddress();
	}

	/**
	 * Get both position and address for given query
	 *
	 * @param string|Address|Position
	 * @param array
	 * @return array (Position|NULL, Address|NULL)
	 */
	public function getPositionAndAddress($query, $options = array())
	{
		if ($query instanceof Position)
		{
			/** @var GeocodingResponse $response */
			$response = $this->getAddress($query, $options, TRUE);
			if ($response)
			{
				return array($response->getPosition(), $response->getAddress());
			}
		}
		else
		{
			/** @var GeocodingResponse $response */
			$response = $this->getPosition($query, $options, TRUE);
			if ($response)
			{
				return array($response->getPosition(), $response->getAddress());
			}
		}

		return array(NULL, NULL);
	}

	/**
	 * Get a full geocoding query result
	 *
	 * @param string|Address|Position
	 * @return GeocodingResponse
	 */
	public function getResponse($query, $options)
	{
		if ($query instanceof Position)
		{
			$options['lat'] = $query->latitude;
			$options['lon'] = $query->longitude;
		}
		else
		{
			$options['lat'] = NULL;
			$options['lon'] = NULL;
			$options['q'] = ((string) $query);
		}

		return $this->query($options);
	}

	/**
	 * Executes query on OSM Nominatim API
	 *
	 * @param  string
	 * @param  string
	 * @param  array [bounds, language, region, sensor]
	 * @return \StdClass
	 */
	protected function query(array $options)
	{
		if ($options['lat'] && $options['lon'])
		{
			$method = 'reverse';
		}
		else
		{
			$method = 'search';
		}

		$options['format'] = 'json';
		$options['addressdetails'] = 1;
		$options['email'] = $this->email;

		$url = $this->baseUrl . $method . '?' . http_build_query($options);

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_HTTPHEADER, Array('Content-Type: application/json; charset=utf-8'));

		if ($this->username)
		{
			curl_setopt($curl, CURLOPT_USERPWD, $this->username . ':' . $this->password);
		}
		if ($this->ua)
		{
			curl_setopt($curl, CURLOPT_USERAGENT,$this->ua);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_URL, $url);
		$response = curl_exec($curl);
		$response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		if (!$response)
		{
			throw new ConnectionException("Unable to connect to geocoding API.");
		}
		$payload = @json_decode($response); // @ - intentionally to escalate error to exception

		if ($payload && !is_array($payload))
		{
			$payload = array($payload);
		}

		if (!$payload)
		{
			if ((is_array($payload) && (count($payload) == 0)))
			{
				throw new InvalidStatusException("Geocoding query failed (no results).");

			}
			else
			{
				throw new InvalidResponseException("Unable to parse response from geocoding API.");
			}
		}
		if ($response_code != 200)
		{
			throw new InvalidStatusException("Geocoding query failed (status: '{$payload->status}').");
		}

		return new GeocodingResponse($this, $payload, $options);
	}

	protected function mungeAddress($address) {
		$address = preg_replace('/\d{3}\W*\d{2}/','',$address); // no ZIP codes -
		$matched = 0;
		do {
			$address = preg_replace('/,([^\d]*?)([\d]+)/',',\\1',$address, -1, $matched);
		} while ($matched > 0);
		$address = preg_replace('/(Praha|Brno|Olomouc|Plzeň|Plzen|Ostrava) +\d+/',',\\1',$address, -1, $matched);
		$address = preg_replace('/,? +,?/',' ',$address);

		return $address;
	}
}
