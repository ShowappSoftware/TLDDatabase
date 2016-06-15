<?php
/**
 * TLDDatabase: Abstraction for Public Suffix List in PHP.
 *
 * @link      https://github.com/layershifter/TLDDatabase
 *
 * @copyright Copyright (c) 2016, Alexander Fedyashov
 * @license   https://raw.githubusercontent.com/layershifter/TLDDatabase/master/LICENSE Apache 2.0 License
 */

namespace LayerShifter\TLDDatabase\Http;

use LayerShifter\TLDDatabase\Exceptions\HttpException;

/**
 * cURL adapter for fetching Public Suffix List.
 */
final class CurlAdapter implements AdapterInterface
{

    /**
     * @const int Number of seconds for HTTP timeout.
     */
    const TIMEOUT = 60;

    /**
     * @var string URL of Public Suffix List file.
     */
    private $url;

    /**
     * @inheritdoc
     */
    public function __construct($url)
    {
        if (!is_string($url)) {
            throw new HttpException('Invalid input URL, url must be type of string');
        }

        $this->url = $url;
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($curl, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, self::TIMEOUT);

        $responseContent = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $errorMessage = curl_error($curl);
        $errorNumber = curl_errno($curl);

        curl_close($curl);

        if ($errorNumber !== 0) {
            throw new HttpException(sprintf('Get cURL error while fetching PSL file: %s', $errorMessage));
        }

        if ($responseCode !== 200) {
            throw new HttpException(
                sprintf('Get invalid HTTP response code "%d" while fetching PSL file', $responseCode)
            );
        }

        return preg_split('/[\n\r]+/', $responseContent);
    }
}