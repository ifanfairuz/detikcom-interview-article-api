<?php

/**
 * Get base directory with sufix optional
 * 
 * @param string $sufix sufix folder path
 * @return string
 */
function base_dir(string $sufix = null): string
{
    return BASE_DIR . ($sufix ? "/$sufix" : "");
}

/**
 * Get app directory with sufix optional
 * 
 * @param string $sufix sufix folder path
 * @return string
 */
function app_dir(string $sufix = null): string
{
    return base_dir("app" . ($sufix ? "/$sufix" : ""));
}

/**
 * Get config directory with sufix optional
 * 
 * @param string $sufix sufix folder path
 * @return string
 */
function config_dir(string $sufix = null): string
{
    return base_dir("config" . ($sufix ? "/$sufix" : ""));
}

/**
 * Get core directory with sufix optional
 * 
 * @param string $sufix sufix folder path
 * @return string
 */
function core_dir(string $sufix = null): string
{
    return base_dir("core" . ($sufix ? "/$sufix" : ""));
}

/**
 * Get config value with query separator by .
 * 
 * @param string $sufix sufix folder path
 * @return mixed
 */
function config(string $query = "")
{
    global $__CONFIG;

    if ($query !== "") {
        // parse query
        $ins = explode('.', $query);
        return array_reduce($ins, function ($config, $in) {
            if (is_array($config) && @$config[$in]) {
                return $config[$in];
            } else {
                throw new Exception("Config not found");
                return null;
            }
        }, $__CONFIG);
    }

    return $__CONFIG;
}

/**
 * Create a pattern for a wildcard
 *
 * @param string $pattern
 * @return string
 */
function convertWildcardToPattern($pattern)
{
    $pattern = preg_quote($pattern, '#');

    // Asterisks are translated into zero-or-more regular expression wildcards
    // to make it convenient to check if the strings starts with the given
    // pattern such as "library/*", making any string check convenient.
    $pattern = str_replace('\*', '.*', $pattern);

    return '#^' . $pattern . '\z#u';
}

/**
 * define sql indetifier escape
 */
if (!defined('SQL_INDENTIFIER_ESCAPE')) define('SQL_INDENTIFIER_ESCAPE', config('db.db_connection') === 'mysql' ? '`' : '"');

/**
 * escape sql identifier
 * 
 * @param string $identifier
 * 
 * @return string
 */
function sqlEscapeIdentifier($identifier)
{
    return SQL_INDENTIFIER_ESCAPE . trim($identifier) . SQL_INDENTIFIER_ESCAPE;
}

function dd(...$args)
{
    var_dump(...$args);
    die();
}

/**
 * Formatted bytes to bytes
 * @param string $formattedBytes
 *
 * @return int|null
 */
function formatedBytestoBytes(string $formattedBytes): ?int
{
    $units = ['B', 'K', 'M', 'G', 'T', 'P'];
    $unitsExtended = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

    $number = (int)preg_replace("/[^0-9]+/", "", $formattedBytes);
    $suffix = preg_replace("/[^a-zA-Z]+/", "", $formattedBytes);

    //B or no suffix
    if (is_numeric($suffix[0])) {
        return preg_replace('/[^\d]/', '', $formattedBytes);
    }

    $exponent = array_flip($units)[$suffix] ?? null;
    if ($exponent === null) {
        $exponent = array_flip($unitsExtended)[$suffix] ?? null;
    }

    if ($exponent === null) {
        return null;
    }
    return $number * (1024 ** $exponent);
}

/**
 * Converts bytes to kb mb etc..
 * Taken from https://stackoverflow.com/questions/2510434/format-bytes-to-kilobytes-megabytes-gigabytes
 *
 * @param int $bytes
 *
 * @return string
 */
function toFormattedBytes(int $bytes): string
{
    $precision = 2;
    $base = log($bytes, 1024);
    $suffixes = array('', 'K', 'M', 'G', 'T');

    return round(1024 ** ($base - floor($base)), $precision) . $suffixes[floor($base)];
}

/**
 * cli output
 * @param string $text
 */
function cli_success(string $text)
{
    echo "\e[32m" . $text . "\n";
}

/**
 * cli output
 * @param string $text
 */
function cli_error(string $text)
{
    echo "\e[31m" . $text . "\n";
}

/**
 * cli output
 * @param string $text
 */
function cli_line(string $text)
{
    echo "\e[39m" . $text . "\n";
}
