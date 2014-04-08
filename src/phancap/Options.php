<?php
namespace phancap;

class Options
{
    public static $options = array(
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
     */
    public function parse($arValues)
    {
        foreach (static::$options as $name => $arOption) {
            $this->values[$name] = $arOption['default'];
            if (!isset($arValues[$name])) {
                if (isset($arOption['required'])) {
                    throw new \InvalidArgumentException(
                        $name . ' parameter missing'
                    );
                }
                continue;
            }

            if ($arOption['type'] == 'url') {
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
     *
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

    protected function validateUrl($url)
    {
        $parts = parse_url($url);
        if ($parts === false) {
            throw new \InvalidArgumentException('Invalid URL');
        }
        if (!isset($parts['scheme'])) {
            throw new \InvalidArgumentException('URL scheme missing');
        }
        if (!isset($parts['host'])) {
            throw new \InvalidArgumentException('URL host missing');
        }
        return $url;
    }

    public function setConfig(Config $config)
    {
        $this->config = $config;
        static::$options['smaxage']['default'] = $this->config->screenshotMaxAge;
        static::$options['smaxage']['min']     = $this->config->screenshotMinAge;
    }
}
?>
