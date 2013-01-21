<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\config;

use snb\core\KernelInterface;


/**
 * Converts a set of config settings in a PHP class that can be loaded more efficiently.
 */
class ConfigStoreCompiler
{
    protected $settings;
    protected $environment;
    protected $path;
    protected $source;
    protected $hash;


    public function __construct(KernelInterface $kernel)
    {
        $this->settings = array();
        $this->path = $kernel->getPackagePath('app');
        $this->source = '';
        $this->setEnvironment('dev');
    }


    public function setValues($settings)
    {
        $this->settings = $settings;
    }

    public function setEnvironment($env)
    {
        $this->environment = $env;
        $this->hash = md5($env);
    }

    public function setCachePath($path)
    {
        $this->path = $path;
    }


    public function compile()
    {
        foreach ($this->settings as $name => $value)
        {
            $this->repr($name);
            $this->raw(' => ');
            $this->repr($value);
            $this->raw(",\n");
        }

        $date = date('d M Y H:i:s');
        $output = <<<END
<?php
/**
 * This file is Generated from the config yml files. Do not edit
 * Generated on {$date} for environment '{$this->environment}'
 */

namespace snb\config;
use snb\config\ConfigStoreInterface;

class ConfigStore implements ConfigStoreInterface
{
	public function getSettings()
	{
		return array({$this->source});
	}
}

END;

        // Write the file to the cache folder
        // We attempt to create the cache folder first
        // (using code from Twig (See copyright below) ∞to create and write the file - the file is written to a tmp file
        // and then copied over the original)
        $file = $this->path;
        $dir = dirname($file);
        if (!is_dir($dir)) {
            if (false === @mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf("Unable to create the cache directory (%s).", $dir));
            }
        } elseif (!is_writable($dir)) {
            throw new \RuntimeException(sprintf("Unable to write in the cache directory (%s).", $dir));
        }

        $tmpFile = tempnam(dirname($file), basename($file));
        if (false !== @file_put_contents($tmpFile, $output)) {
            // rename does not work on Win32 before 5.2.6
            if (@rename($tmpFile, $file) || (@copy($tmpFile, $file) && unlink($tmpFile))) {
                @chmod($file, 0666 & ~umask());

                return;
            }
        }

        // obviously failed to create the file, so fail
        throw new \RuntimeException(sprintf('Failed to write cache file "%s".', $file));
    }


    /**
     * The Following code taken from Twig
     * (c) 2009 Fabien Potencier
     * (c) 2009 Armin Ronacher
     *
     * For the full copyright and license information, please view the LICENSE
     * file that was distributed with this source code.
     */

    /**
     * Returns a PHP representation of a given value.
	 * @param $value
	 */
	public function repr($value)
    {
        if (is_int($value) || is_float($value)) {
            if (false !== $locale = setlocale(LC_NUMERIC, 0)) {
                setlocale(LC_NUMERIC, 'C');
            }

            $this->raw($value);

            if (false !== $locale) {
                setlocale(LC_NUMERIC, $locale);
            }
        } elseif (null === $value) {
            $this->raw('null');
        } elseif (is_bool($value)) {
            $this->raw($value ? 'true' : 'false');
        } elseif (is_array($value)) {
            $this->raw('array(');
            $i = 0;
            foreach ($value as $key => $subValue) {
                if ($i++) {
                    $this->raw(', ');
                }
                $this->repr($key);
                $this->raw(' => ');
                $this->repr($subValue);
            }
            $this->raw(')');
        } else {
            $this->string($value);
        }
    }

    /**
     * Adds a quoted string to the compiled code.
     *
     * @param string $value The string
	 * @param $value
	 */
	public function string($value)
    {
        $this->source .= sprintf('"%s"', addcslashes($value, "\0\t\"\$\\"));
    }

    /**
     * Adds a raw string to the compiled code.
     * @param string $string The string
	 */
	public function raw($string)
    {
        $this->source .= $string;
    }

}