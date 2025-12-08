<?php

namespace CentralTickets;

class Configurations
{
    private static array $cache = [];

    /**
     * Get a configuration setting
     *
     * @param string $key The setting key
     * @param mixed $default Default value if the setting does not exist
     * @return mixed The setting value or default if not found
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        global $wpdb;

        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT setting_value FROM {$wpdb->prefix}git_settings WHERE setting_key = %s",
                $key
            )
        );

        if ($result !== null) {
            $value = git_unserialize($result);
            self::$cache[$key] = $value;
        } else {
            $value = $default;
            self::$cache[$key] = $default;
        }

        return $value;
    }

    public static function get_map(string $key, mixed $default = null)
    {
        $references = explode('.', $key);
        $map = self::get($references[0]) ?? null;
        foreach (array_slice($references, 1) as $ref) {
            if (is_array($map) && isset($map[$ref])) {
                $map = $map[$ref];
            } else {
                return $default;
            }
        }
        return $map;
    }

    /**
     * Set a configuration setting
     *
     * @param string $key The setting key
     * @param mixed $value The value to set
     * @return int|false Number of rows affected or false on error
     */
    public static function set(string $key, mixed $value)
    {
        global $wpdb;

        $string_value = git_serialize($value);

        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, setting_key FROM {$wpdb->prefix}git_settings WHERE setting_key = %s",
                $key
            )
        );

        if ($result === null) {
            $result = $wpdb->query(
                $wpdb->prepare(
                    "INSERT INTO {$wpdb->prefix}git_settings (setting_key, setting_value) VALUES (%s, %s)",
                    $key,
                    $string_value
                )
            );
        } else {
            $result = $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->prefix}git_settings SET setting_value = %s WHERE setting_key = %s",
                    $string_value,
                    $key
                )
            );
        }

        self::$cache[$key] = $value;

        return $result;
    }

    public static function get_all()
    {
        global $wpdb;

        $results = $wpdb->get_results("SELECT setting_key, setting_value FROM {$wpdb->prefix}git_settings", ARRAY_A);
        $result = [];

        foreach ($results as $row) {
            $result[$row['setting_key']] = git_unserialize($row['setting_value']);
        }

        return $result;
    }
}
