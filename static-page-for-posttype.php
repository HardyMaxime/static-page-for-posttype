<?php
/*
Plugin Name: Static Page for Post Type
Plugin URI: https://maximehardy.me/
Description: Ce plugin ajoute des fonctionnalités personnalisées à votre site.
Version: 1.2
Author: Maxime HARDY
Author URI: https://maximehardy.me/
*/

if (!defined('ABSPATH')) exit; // Sécurité : Empêche l'accès direct

class Static_Page_For_Post_Type {

    public function __construct() {
        add_action('admin_init', [$this, 'clbs_register_reading_settings']);
        add_action('template_redirect', [$this, 'clbs_redirect_custom_post_type_archives']);
        add_filter('display_post_states', [$this, 'clbs_add_post_state_label'], 10, 2);
    }

    /**
     * Enregistrer les options dans les réglages de lecture
     */
    public function clbs_register_reading_settings(): void
    {
        $post_types = get_post_types(['_builtin' => false], 'objects');

        foreach ($post_types as $post_type) {
            if(!$post_type->publicly_queryable) continue;
            register_setting('reading', "page_for_{$post_type->name}");

            add_settings_field(
                "page_for_{$post_type->name}",
                "Page pour les {$post_type->labels->name}",
                function() use ($post_type) {
                    $pages = get_pages();
                    $selected = get_option("page_for_{$post_type->name}");

                    echo '<select name="page_for_' . $post_type->name . '">';
                    echo '<option value="">— Sélectionner une page —</option>';
                    foreach ($pages as $page) {
                        $is_selected = selected($selected, $page->ID, false);
                        echo "<option value='{$page->ID}' {$is_selected}>{$page->post_title}</option>";
                    }
                    echo '</select>';
                },
                'reading',
                'default'
            );
        }
    }

    /**
     * Rediriger les archives des custom post types vers la page sélectionnée
     */
    public function clbs_redirect_custom_post_type_archives(): void
    {
        if (is_post_type_archive()) {
            $post_types = $this->get_post_types();
            foreach ($post_types as $post_type) {
                if (is_post_type_archive($post_type)) {
                    $page_id = get_option("page_for_{$post_type}");
                    if ($page_id) {
                        $redirect_url = get_permalink($page_id);
                        if ($redirect_url && !is_page($page_id)) { // Évite la boucle de redirection
                            wp_redirect($redirect_url, 301);
                            exit;
                        }
                    }
                }
            }
        }
    }

    /**
     * Ajouter un label dans la liste des pages de l'administration
     */
    public function clbs_add_post_state_label($post_states, $post): array
    {
        $post_types = get_post_types(['_builtin' => false], 'objects');

        foreach ($post_types as $post_type) {
            $assigned_page_id = get_option("page_for_{$post_type->name}");

            if ($post->ID == $assigned_page_id) {
                $post_states["page_for_{$post_type->name}"] = "Page des {$post_type->labels->name}";
            }
        }

        return $post_states;
    }

    /**
     * Récupérer les post types
     */
    function get_post_types(): array
    {
        $post_types = get_post_types(['_builtin' => false], 'names');
        // Exclure les post types commençant par 'acf-'
        $post_types = array_filter($post_types, function($post_type) {
            return (!str_starts_with($post_type, 'acf-'));
        });
        return $post_types;
    }
}

// Initialisation de la classe
new Static_Page_For_Post_Type();
