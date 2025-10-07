<?php
namespace CentralTickets\Admin;

use CentralTickets\Components\Displayer;
use CentralTickets\Components\TextComponent;

final class AdminRouter
{
    private static $routes = [];

    public static function add_route(
        string $slug,
        string $title,
        string $classname,
        ?string $parent = null,
    ) {
        if (!is_subclass_of($classname, Displayer::class)) {
            return;
        }
        self::$routes[$slug] = [
            'title' => $title,
            'parent' => $parent,
            'content' => $classname
        ];
    }

    /**
     * Renderizar contenido basado en la ruta solicitada
     */
    public static function render_content(string $page_slug)
    {
        // Determinar qué contenido renderizar
        $target_slug = self::resolve_target_slug($page_slug);
        
        if (!$target_slug) {
            echo '<div class="notice notice-error"><p>Página no encontrada</p></div>';
            return;
        }

        // Renderizar tabs si es necesario
        $route = self::$routes[$target_slug];
        if ($route['parent'] !== null) {
            $siblings = self::get_childs($route['parent']);
            if (count($siblings) > 1) {
                echo self::get_tabpane($siblings, $target_slug);
            }
        } else {
            // Es página principal, verificar si tiene hijos para mostrar tabs
            $children = self::get_childs($target_slug);
            if (count($children) > 1) {
                // Si hay un slug específico en GET, usarlo; si no, el primero
                $active_child = $_GET['slug'] ?? array_key_first($children);
                if (isset($children[$active_child])) {
                    echo self::get_tabpane($children, $active_child);
                    $target_slug = $active_child; // Renderizar el hijo activo
                }
            }
        }

        // Renderizar el contenido
        if (isset(self::$routes[$target_slug])) {
            (new self::$routes[$target_slug]['content'])->display();
        }
    }

    /**
     * Resolver qué slug debe renderizarse basado en los parámetros GET
     */
    private static function resolve_target_slug(string $page_slug): ?string
    {
        $requested_slug = $_GET['slug'] ?? '';
        
        // Si hay un slug específico solicitado y existe, usarlo
        if ($requested_slug && isset(self::$routes[$requested_slug])) {
            return $requested_slug;
        }

        // Si la página principal existe, usarla
        if (isset(self::$routes[$page_slug])) {
            // Si tiene hijos, devolver el primer hijo
            $children = self::get_childs($page_slug);
            if (!empty($children)) {
                return array_key_first($children);
            }
            return $page_slug;
        }

        // Si la página no existe pero tiene hijos, devolver el primer hijo
        $children = self::get_childs($page_slug);
        if (!empty($children)) {
            return array_key_first($children);
        }

        return null;
    }

    /**
     * Generar panel de tabs
     */
    private static function get_tabpane(array $childs, string $slug_active): string
    {
        $html = '<div class="nav-tab-wrapper">';
        foreach ($childs as $slug => $child) {
            $link = new TextComponent('a', $child['title']);
            $link->set_attribute('href', self::get_url($slug));
            $link->class_list->add('nav-tab');
            if ($slug === $slug_active) {
                $link->class_list->add('nav-tab-active');
            }
            $html .= $link->compact();
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * Obtener hijos de una ruta
     */
    private static function get_childs(string $slug_parent): array
    {
        $childs = [];
        foreach (self::$routes as $slug => $route) {
            if ($route['parent'] === $slug_parent) {
                $childs[$slug] = $route;
            }
        }
        return $childs;
    }

    /**
     * Generar URL para una ruta
     */
    public static function get_url(string $slug): string
    {
        if (!isset(self::$routes[$slug])) {
            return '';
        }

        $route = self::$routes[$slug];

        // Si no tiene padre, es página principal
        if ($route['parent'] === null) {
            return add_query_arg([
                'page' => $slug
            ], admin_url('admin.php'));
        }

        // Tiene padre, buscar la página raíz
        $root_page = self::find_root_page($slug);

        return add_query_arg([
            'page' => $root_page,
            'slug' => $slug
        ], admin_url('admin.php'));
    }

    /**
     * Obtener URL por nombre de clase
     */
    public static function get_url_by_classname(string $classname): string
    {
        foreach (self::$routes as $slug => $route) {
            if ($route['content'] === $classname) {
                return self::get_url($slug);
            }
        }
        return '';
    }

    /**
     * Obtener slug por nombre de clase
     */
    public static function get_slug_by_classname(string $classname): ?string
    {
        foreach (self::$routes as $slug => $route) {
            if ($route['content'] === $classname) {
                return $slug;
            }
        }
        return null;
    }

    /**
     * Verificar si una clase está registrada
     */
    public static function classname_exists(string $classname): bool
    {
        foreach (self::$routes as $route) {
            if ($route['content'] === $classname) {
                return true;
            }
        }
        return false;
    }

    /**
     * Encontrar la página raíz (sin padre)
     */
    private static function find_root_page(string $slug): string
    {
        $current = $slug;

        while (isset(self::$routes[$current]) && self::$routes[$current]['parent'] !== null) {
            $current = self::$routes[$current]['parent'];
        }

        return $current;
    }

    /**
     * Obtener la ruta actual basada en los parámetros GET
     */
    public static function get_current_route(): ?string
    {
        $page = $_GET['page'] ?? '';
        $slug = $_GET['slug'] ?? '';

        // Si hay slug específico, devolverlo
        if ($slug && isset(self::$routes[$slug])) {
            return $slug;
        }

        // Si no hay slug, devolver la página
        if ($page && isset(self::$routes[$page])) {
            return $page;
        }

        return null;
    }

    /**
     * Crear enlace a una clase específica
     */
    public static function link_to_class(string $classname, string $text, array $attributes = []): string
    {
        $url = self::get_url_by_classname($classname);
        
        if (!$url) {
            return $text; // Si no encuentra la URL, devolver solo el texto
        }

        $link = new TextComponent('a', $text);
        $link->set_attribute('href', $url);
        
        foreach ($attributes as $attr => $value) {
            $link->set_attribute($attr, $value);
        }

        return $link->compact();
    }

    /**
     * Verificar si estamos en una clase específica
     */
    public static function is_current_class(string $classname): bool
    {
        $current_route = self::get_current_route();
        if (!$current_route) {
            return false;
        }

        return self::$routes[$current_route]['content'] === $classname;
    }

    /**
     * Obtener breadcrumbs para la ruta actual
     */
    public static function get_breadcrumbs(): array
    {
        $current_route = self::get_current_route();
        if (!$current_route) {
            return [];
        }

        $breadcrumbs = [];
        $route = self::$routes[$current_route];

        // Construir cadena de padres
        $parents = [];
        $current = $current_route;
        
        while ($current && isset(self::$routes[$current])) {
            array_unshift($parents, $current);
            $current = self::$routes[$current]['parent'];
        }

        // Convertir a breadcrumbs
        foreach ($parents as $slug) {
            $breadcrumbs[] = [
                'title' => self::$routes[$slug]['title'],
                'url' => self::get_url($slug),
                'slug' => $slug,
                'is_current' => $slug === $current_route
            ];
        }

        return $breadcrumbs;
    }

    /**
     * Debug: Mostrar información de rutas
     */
    public static function debug_routes(): void
    {
        echo '<pre style="background: #f0f0f0; padding: 10px; margin: 10px 0;">';
        echo "=== RUTAS REGISTRADAS ===\n";
        foreach (self::$routes as $slug => $route) {
            echo "Slug: {$slug}\n";
            echo "  Título: {$route['title']}\n";
            echo "  Padre: " . ($route['parent'] ?? 'ninguno') . "\n";
            echo "  Clase: {$route['content']}\n";
            echo "  URL: " . self::get_url($slug) . "\n\n";
        }
        
        echo "=== ESTADO ACTUAL ===\n";
        echo "GET[page]: " . ($_GET['page'] ?? 'no definido') . "\n";
        echo "GET[slug]: " . ($_GET['slug'] ?? 'no definido') . "\n";
        echo "Ruta actual: " . (self::get_current_route() ?? 'no encontrada') . "\n";
        echo '</pre>';
    }

    /**
     * Obtener todas las rutas registradas
     */
    public static function get_routes(): array
    {
        return self::$routes;
    }
}