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
    );

    public $values = array();


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
            } else {
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
        if ($value < $min) {
            throw new \InvalidArgumentException(
                'Value must be at least ' . $min
            );
        }
        if ($value > $max) {
            throw new \InvalidArgumentException(
                'Value may be up to ' . $min
            );
        }
        return $value;
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
}
?>
