<?php 

/**
 * Usage:
 * 
 * (new DotEnv(__DIR__ . '/.env'))->load();
 *
 * then 
 *    getenv('DB_HOST');
 * 
 * Move any secret data into .env file per server. Most items are already in aws-secrets but some items or specific
 * settings per server can be set in .env
 * 
 * .env should not be committed into git.
 */

class DotEnv
{
    /**
     * The directory where the .env file can be located.
     *
     * @var string
     */
    protected $path;


    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function load()
    {
        if (!is_readable($this->path)) {
            //throw new \RuntimeException(sprintf('%s file is not readable', $this->path));
            return;
        }

        $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {

            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // If env var does not already exist (even if empty)
            if (getenv($name) === FALSE) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
            }
        }
    }
}
