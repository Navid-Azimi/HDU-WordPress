<?php
// Register the custom endpoint for posts
function custom_posts_endpoint($request) {
    // Get pagination parameters
    $page = $request->get_param('page') ? absint($request->get_param('page')) : 1;
    $per_page = $request->get_param('per_page') ? absint($request->get_param('per_page')) : 10;

    // Query posts
    $query_args = array(
        'post_type' => 'post',
        'posts_per_page' => $per_page,
        'paged' => $page,
        'post_status' => 'publish',
    );

    $query = new WP_Query($query_args);
    $posts = $query->posts;

    // Format the data
    $data = array_map(function ($post) {
        return array(
            'title' => $post->post_title,
            'slug' => $post->post_name,
            'excerpt' => wp_trim_words($post->post_content, 20, '...'),
            'featured_image' => get_the_post_thumbnail_url($post->ID, 'full') ?: null,
        );
    }, $posts);

    // Add pagination information
    $response = array(
        'data' => $data,
        'pagination' => array(
            'total' => (int) $query->found_posts,
            'total_pages' => (int) $query->max_num_pages,
            'current_page' => $page,
        ),
    );

    return rest_ensure_response($response);
}

// Register the endpoint
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/posts', array(
        'methods' => 'GET',
        'callback' => 'custom_posts_endpoint',
        'args' => array(
            'page' => array(
                'required' => false,
                'default' => 1,
                'validate_callback' => function ($param) {
                    return is_numeric($param);
                },
            ),
            'per_page' => array(
                'required' => false,
                'default' => 10,
                'validate_callback' => function ($param) {
                    return is_numeric($param);
                },
            ),
        ),
    ));
});




// Endpoint for a single blog post
function get_single_blog_post($request) {
    $slug = $request->get_param('slug');

    // Query the post by slug
    $post = get_page_by_path($slug, OBJECT, 'post');
    if (!$post) {
        return new WP_Error('not_found', 'Post not found', array('status' => 404));
    }

    // Prepare the response data
    $data = array(
        'title' => $post->post_title,
        'content' => apply_filters('the_content', $post->post_content),
        'featured_image' => get_the_post_thumbnail_url($post->ID, 'full') ?: null,
    );

    return rest_ensure_response($data);
}

// Register the single blog endpoint
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/posts/single', array(
        'methods' => 'GET',
        'callback' => 'get_single_blog_post',
        'args' => array(
            'slug' => array(
                'required' => true,
                'validate_callback' => function ($param) {
                    return is_string($param);
                },
            ),
        ),
    ));
});




// Endpoint for similar posts
function get_similar_posts($request) {
    $slug = $request->get_param('slug');
    $page = $request->get_param('page') ? absint($request->get_param('page')) : 1;
    $per_page = $request->get_param('per_page') ? absint($request->get_param('per_page')) : 10;

    // Query the current post by slug
    $current_post = get_page_by_path($slug, OBJECT, 'post');
    if (!$current_post) {
        return new WP_Error('not_found', 'Post not found', array('status' => 404));
    }

    // Query for similar posts (excluding the current post)
    $query_args = array(
        'post_type' => 'post',
        'posts_per_page' => $per_page,
        'paged' => $page,
        'post_status' => 'publish',
        'post__not_in' => array($current_post->ID), // Exclude the current post
    );

    $query = new WP_Query($query_args);
    $posts = $query->posts;

    // Format the data
    $data = array_map(function ($post) {
        return array(
            'title' => $post->post_title,
            'slug' => $post->post_name,
            'excerpt' => wp_trim_words($post->post_content, 20, '...'),
            'featured_image' => get_the_post_thumbnail_url($post->ID, 'full') ?: null,
        );
    }, $posts);

    // Add pagination information
    $response = array(
        'data' => $data,
        'pagination' => array(
            'total' => (int) $query->found_posts,
            'total_pages' => (int) $query->max_num_pages,
            'current_page' => $page,
        ),
    );

    return rest_ensure_response($response);
}

// Register the similar posts endpoint
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/posts/similar', array(
        'methods' => 'GET',
        'callback' => 'get_similar_posts',
        'args' => array(
            'slug' => array(
                'required' => true,
                'validate_callback' => function ($param) {
                    return is_string($param);
                },
            ),
            'page' => array(
                'required' => false,
                'default' => 1,
                'validate_callback' => function ($param) {
                    return is_numeric($param);
                },
            ),
            'per_page' => array(
                'required' => false,
                'default' => 10,
                'validate_callback' => function ($param) {
                    return is_numeric($param);
                },
            ),
        ),
    ));
});