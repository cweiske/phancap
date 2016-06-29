<?php
/**
 * Part of phancap
 *
 * PHP version 5
 *
 * @category  Tools
 * @package   Options
 * @author    Christian Weiske <cweiske@cweiske.de>
 * @copyright 2014 Christian Weiske
 * @license   http://www.gnu.org/licenses/agpl.html GNU AGPL v3
 * @link      http://cweiske.de/phancap.htm
 */
namespace phancap;

/**
 * Options a user can give to the API
 *
 * @category  Tools
 * @package   Options
 * @author    Christian Weiske <cweiske@cweiske.de>
 * @copyright 2014 Christian Weiske
 * @license   http://www.gnu.org/licenses/agpl.html GNU AGPL v3
 * @version   Release: @package_version@
 * @link      http://cweiske.de/phancap.htm
 */
class Options
{
    /**
     * Available options and their configuration
     *
     * @var array
     */
    public $options = array(
        /**
         * Browser settings
         */
        'url' => array(
            'title'     => 'Website URL',
            'default'   => null,
            'type'      => 'url',
            'required'  => true,
        ),
        'bwidth' => array(
            'title'   => 'Browser width',
            'default' => 1024,
            'type'    => 'int',
            'min'     => 16,
            'max'     => 2048,
        ),
        'bheight' => array(
            'title'   => 'Browser height',
            'default' => null,
            'type'    => 'int',
            'min'     => 16,
            'max'     => 8192,
        ),
        /**
         * Screenshot settings
         */
        'swidth' => array(
            'title'   => 'Screenshot width',
            'default' => null,
            'type'    => 'int',
            'min'     => 16,
            'max'     => 8192,
        ),
        'sheight' => array(
            'title'   => 'Screenshot height',
            'default' => null,
            'type'    => 'int',
            'min'     => 16,
            'max'     => 8192,
        ),
        'sformat' => array(
            'title'   => 'Screenshot format',
            'default' => 'png',
            'type'    => array('png', 'jpg', 'pdf'),
        ),
        'smode' => array(
            'title'   => 'Screenshot mode',
            'default' => 'screen',
            'type'    => array('screen', 'page'),
        ),
        'smaxage' => array(
            'title'   => 'Maximum age for a screenshot',
            'default' => null,
            'type'    => 'age',
            'min'     => null,
        ),
        /**
         * Authentication
         */
        'atimestamp' => array(
            'title'   => 'Timestamp the request has been generated',
            'default' => null,
            'type'    => 'skip',
        ),
        'atoken' => array(
            'title'   => 'Access token (user name)',
            'default' => null,
            'type'    => 'skip',
        ),
        'asignature' => array(
            'title'   => 'Access signature',
            'default' => null,
            'type'    => 'skip',
        ),
    );

    /**
     * Actual values we use after parsing the GET parameters
     *
     * @var array
     */
    public $values = array();

    /**
     * @var Config
     */
    protected $config;


    /**
     * Parses an array of options, validates them and writes them into
     * $this->values.
     *
     * @param array $arValues Array of options, e.g. $_GET
     *
     * @return void
     * @throws \InvalidArgumentException When required parameters are missing
     *         or parameter values are invalid.
     */
    public function parse($arValues)
    {
        foreach ($this->options as $name => $arOption) {
            $this->values[$name] = $arOption['default'];
            if (!isset($arValues[$name])) {
                if (isset($arOption['required'])) {
                    throw new \InvalidArgumentException(
                        $name . ' parameter missing'
                    );
                }
                continue;
            }

            if ($arValues[$name] === ''
                && !isset($arOption['required'])
            ) {
                //allow empty value; default value will be used
            } else if ($arOption['type'] == 'url') {
                $this->values[$name] = $this->validateUrl($arValues[$name]);
            } else if ($arOption['type'] == 'int') {
                $this->values[$name] = $this->validateInt(
                    $arValues[$name], $arOption['min'], $arOption['max']
                );
            } else if (gettype($arOption['type']) == 'array') {
                $this->values[$name] = $this->validateArray(
                    $arValues[$name], $arOption['type']
                );
            } else if ($arOption['type'] == 'age') {
                $this->values[$name] = $this->clamp(
                    static::validateAge($arValues[$name]),
                    $arOption['min'], null,
                    true
                );
            } else if ($arOption['type'] != 'skip') {
                throw new \InvalidArgumentException(
                    'Unsupported option type: ' . $arOption['type']
                );
            }
            unset($arValues[$name]);
        }

        if (count($arValues) > 0) {
            throw new \InvalidArgumentException(
                'Unsupported parameter: ' . implode(', ', array_keys($arValues))
            );
        }

        $this->calcPageSize();
    }

    /**
     * Calculate the browser size and screenshot size from the given options
     *
     * @return void
     */
    protected function calcPageSize()
    {
        if ($this->values['swidth'] === null) {
            $this->values['swidth'] = $this->values['bwidth'];
        }
        if ($this->values['smode'] == 'page') {
            return;
        }

        if ($this->values['sheight'] !== null) {
            $this->values['bheight'] = intval(
                $this->values['bwidth'] / $this->values['swidth']
                * $this->values['sheight']
            );
        } else if ($this->values['bheight'] !== null) {
            $this->values['sheight'] = intval(
                $this->values['swidth'] / $this->values['bwidth']
                * $this->values['bheight']
            );
        } else {
            //no height set. use 4:3
            $this->values['sheight'] = $this->values['swidth'] / 4 * 3;
            $this->values['bheight'] = $this->values['bwidth'] / 4 * 3;
        }
    }

    /**
     * Makes sure a value is between $min and $max (inclusive)
     *
     * @param integer $value  Value to check
     * @param integer $min    Minimum allowed value
     * @param integer $max    Maximum allowed value
     * @param boolean $silent When silent, invalid values are corrected.
     *                        An exception is thrown otherwise.
     *
     * @return integer Corrected value
     * @throws \InvalidArgumentException When not silent and value outside range
     */
    protected function clamp($value, $min, $max, $silent = false)
    {
        if ($min !== null && $value < $min) {
            if ($silent) {
                $value = $min;
            } else {
                throw new \InvalidArgumentException(
                    'Value must be at least ' . $min
                );
            }
        }
        if ($max !== null && $value > $max) {
            if ($silent) {
                $value = $max;
            } else {
                throw new \InvalidArgumentException(
                    'Value may be up to ' . $min
                );
            }
        }
        return $value;
    }

    /**
     * Validates an age is numeric. If it is not numeric, it's interpreted as
     * a ISO 8601 duration specification.
     *
     * @param string $value Age in seconds
     *
     * @return integer Age in seconds
     * @throws \InvalidArgumentException
     * @link   http://en.wikipedia.org/wiki/Iso8601#Durations
     */
    public static function validateAge($value)
    {
        if (!is_numeric($value)) {
            //convert short notation to seconds
            $value = 'P' . ltrim(strtoupper($value), 'P');
            try {
                $interval = new \DateInterval($value);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException(
                    'Invalid age: ' . $value
                );
            }
            $value = 86400 * (
                $interval->y * 365
                + $interval->m * 30
                + $interval->d
            ) + $interval->h * 3600
                + $interval->m * 60
                + $interval->s;
        }
        return $value;
    }

    /**
     * Check that a given value exists in an array
     *
     * @param string $value   Value to check
     * @param array  $options Array of allowed values
     *
     * @return string Value
     * @throws \InvalidArgumentException If the value does not exist in $options
     */
    protected function validateArray($value, $options)
    {
        if (array_search($value, $options) === false) {
            throw new \InvalidArgumentException(
                'Invalid value ' . $value . '.'
                . ' Allowed: ' . implode(', ', $options)
            );
        }
        return $value;
    }

    /**
     * Validate that a value is numeric and between $min and $max (inclusive)
     *
     * @param string  $value Value to check
     * @param integer $min   Minimum allowed value
     * @param integer $max   Maximum allowed value
     *
     * @return integer Value as integer
     * @throws \InvalidArgumentException When outside range or not numeric
     */
    protected function validateInt($value, $min, $max)
    {
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException(
                'Value must be a number'
            );
        }
        $value = (int) $value;
        return $this->clamp($value, $min, $max);
    }

    /**
     * Validate (and fix) an URL
     *
     * @param string $url URL
     *
     * @return string Fixed URL
     * @throws \InvalidArgumentException
     */
    protected function validateUrl($url)
    {
        $parts = parse_url($url);
        if ($parts === false) {
            throw new \InvalidArgumentException('Invalid URL');
        }
        if (!isset($parts['scheme'])) {
            $url = 'http://' . $url;
            $parts = parse_url($url);
        } else if ($parts['scheme'] != 'http' && $parts['scheme'] != 'https') {
            throw new \InvalidArgumentException('Unsupported protocol');
        }
        if (!isset($parts['host'])) {
            throw new \InvalidArgumentException('URL host missing');
        }
        return $url;
    }

    /**
     * Set phancap configuration
     *
     * @param Config $config Phancap configuration
     *
     * @return void
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
        $this->options['smaxage']['default'] = $this->config->screenshotMaxAge;
        $this->options['smaxage']['min']     = $this->config->screenshotMinAge;
    }
}
?>
