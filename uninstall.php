<?php
// Vérifie si le script est appelé par WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Liste des custom post types personnalisés
$post_types = get_post_types(['_builtin' => false], 'names');

// Suppression des options associées à chaque custom post type
foreach ($post_types as $post_type) {
    delete_option("page_for_{$post_type}");
}