<?php
namespace CentralTickets;

class MetaManager
{
    public const TICKET = 'ticket';

    public const TRANSPORT = 'transport';

    private static array $cache = [];

    public static function get_meta(string $meta_type, int $meta_id, string $meta_key)
    {
        global $wpdb;

        // Build cache key
        $cache_key = "{$meta_type}_{$meta_id}_{$meta_key}";

        // Check if value exists in cache
        if (isset(self::$cache[$cache_key])) {
            return self::$cache[$cache_key];
        }

        $table_name = $wpdb->prefix . 'git_meta';

        // Query database
        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT meta_value FROM $table_name WHERE meta_type = %s AND meta_id = %d AND meta_key = %s",
                $meta_type,
                $meta_id,
                $meta_key
            )
        );

        // If result found, parse it and cache
        if ($result !== null) {
            $result_parsed = git_unserialize($result);
            self::$cache[$cache_key] = $result_parsed;
            return $result_parsed;
        }

        return null;
    }

    public static function get_map_meta(string $meta_type, int $meta_id, string $meta_key)
    {
        $references = explode('.', $meta_key);
        $map = self::get_meta($meta_type, $meta_id, $references[0]) ?? null;
        foreach (array_slice($references, 1) as $ref) {
            if (is_array($map) && isset($map[$ref])) {
                $map = $map[$ref];
            } else {
                return null;
            }
        }
        return $map;
    }

    public static function get_metadata(string $meta_type, int $meta_id)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'git_meta';

        // Query all metadata for the entity
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_key, meta_value FROM $table_name WHERE meta_type = %s AND meta_id = %d",
                $meta_type,
                $meta_id,
            )
        );

        $metadata = [];

        foreach ($results as $meta) {
            self::$cache["{$meta_type}_{$meta_id}_{$meta->meta_key}"] = git_unserialize($meta->meta_value);
            $metadata[$meta->meta_key] = self::$cache["{$meta_type}_{$meta_id}_{$meta->meta_key}"];
        }

        return $metadata;
    }

    /**
     * Saves multiple metadata values for an entity
     * 
     * Allows saving multiple metadata at once using an associative array.
     * Internally calls set_meta() for each key-value pair.
     * 
     * @param string $meta_type The entity type (e.g., 'ticket', 'transport')
     * @param int    $meta_id   The entity ID
     * @param array  $metadata  Associative array [meta_key => meta_value]
     * 
     * @return void
     * 
     * @example
     * MetaManager::set_metadata('transport', 123, [
     *     'capacity' => 45,
     *     'features' => ['wifi', 'ac', 'bathroom'],
     *     'status' => 'active',
     *     'last_maintenance' => '2024-01-15'
     * ]);
     * 
     * @see MetaManager::set_meta() For saving individual metadata
     */
    public static function set_metadata(string $meta_type, int $meta_id, array $metadata)
    {
        foreach ($metadata as $key => $value) {
            self::set_meta($meta_type, $meta_id, $key, $value);
        }
    }

    public static function set_meta(string $meta_type, int $meta_id, string $meta_key, mixed $meta_value)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'git_meta';
        $cache_key = "{$meta_type}_{$meta_id}_{$meta_key}";

        // Check if metadata already exists
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id FROM $table_name WHERE meta_type = %s AND meta_id = %d AND meta_key = %s",
                $meta_type,
                $meta_id,
                $meta_key
            )
        );

        if ($result === null) {
            // Create new record
            $wpdb->insert($table_name, [
                'meta_type' => $meta_type,
                'meta_id' => $meta_id,
                'meta_key' => $meta_key,
                'meta_value' => git_serialize($meta_value)
            ], [
                '%s', // meta_type
                '%d', // meta_id
                '%s', // meta_key
                '%s'  // meta_value
            ]);
        } else {
            // Update existing record
            $wpdb->update(
                $table_name,
                ['meta_value' => git_serialize($meta_value)],
                ['id' => $result->id],
                ['%s'], // meta_value format
                ['%d']  // id format
            );
        }

        // Update cache
        self::$cache[$cache_key] = $meta_value;
    }

    /**
     * Clears the complete metadata cache
     * 
     * Useful for freeing memory or forcing reload from database.
     * 
     * @return void
     * 
     * @example
     * MetaManager::clear_cache();
     */
    public static function clear_cache(): void
    {
        self::$cache = [];
    }

    /**
     * Clears cache for a specific entity
     * 
     * Removes from cache all metadata for a specific entity.
     * 
     * @param string $meta_type The entity type
     * @param int    $meta_id   The entity ID
     * 
     * @return void
     * 
     * @example
     * MetaManager::clear_entity_cache('transport', 123);
     */
    public static function clear_entity_cache(string $meta_type, int $meta_id): void
    {
        $pattern = "{$meta_type}_{$meta_id}_";

        foreach (array_keys(self::$cache) as $cache_key) {
            if (str_starts_with($cache_key, $pattern)) {
                unset(self::$cache[$cache_key]);
            }
        }
    }

    public static function delete_meta(string $meta_type, int $meta_id, string $meta_key): bool
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'git_meta';
        $cache_key = "{$meta_type}_{$meta_id}_{$meta_key}";

        $result = $wpdb->delete(
            $table_name,
            [
                'meta_type' => $meta_type,
                'meta_id' => $meta_id,
                'meta_key' => $meta_key
            ],
            ['%s', '%d', '%s']
        );

        // Clear cache
        unset(self::$cache[$cache_key]);

        return $result !== false;
    }

    public static function delete_all_meta(string $meta_type, int $meta_id): bool
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'git_meta';

        $result = $wpdb->delete(
            $table_name,
            [
                'meta_type' => $meta_type,
                'meta_id' => $meta_id
            ],
            ['%s', '%d']
        );

        // Clear entity cache
        self::clear_entity_cache($meta_type, $meta_id);

        return $result !== false;
    }

    public static function get_stats(): array
    {
        global $wpdb;

        return $wpdb->get_results(
            "SELECT meta_type, COUNT(*) as total 
             FROM {$wpdb->prefix}git_meta 
             GROUP BY meta_type",
            ARRAY_A
        );
    }
}