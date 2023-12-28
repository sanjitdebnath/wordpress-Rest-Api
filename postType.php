<?php

function vietlist_getPost_data_route() {
    
    register_rest_route('vietlist/v1', '/category_data_get/', array(
        'methods' => 'GET',
        'callback' => 'vietlist_get_category_endpoint',
    ));
    
    register_rest_route('vietlist/v1', '/get_event_category/', array(
        'methods' => 'GET',
        'callback' => 'vietlist_get_event_category_endpoint',
    ));
    
    register_rest_route('vietlist/v1', '/tags_data_get/', array(
        'methods' => 'GET',
        'callback' => 'vietlist_get_tags_endpoint',
    ));
    
    register_rest_route('vietlist/v1', '/business_data_get/', array(
        'methods' => 'GET',
        'callback' => 'vietlist_get_business_data_endpoint',
    ));
    
    register_rest_route('vietlist/v1', '/business_data_set/', array(
        'methods' => 'POST',
        'callback' => 'vietlist_set_business_data_endpoint',
    ));
    
    register_rest_route('vietlist/v1', '/business_data_update/', array(
        'methods' => 'POST',
        'callback' => 'vietlist_update_business_data_endpoint',
    ));
    
    register_rest_route('vietlist/v1', '/post_data_delete/', array(
        'methods' => 'POST',
        'callback' => 'vietlist_delete_post_data_endpoint',
    ));
    
    register_rest_route('vietlist/v1', '/setbookmark/', array(
        'methods' => 'POST',
        'callback' => 'vietlist_setbookmark_endpoint',
    ));
    
    register_rest_route('vietlist/v1', '/set_event/', array(
        'methods' => 'POST',
        'callback' => 'vietlist_set_event_data_endpoint',
    ));
    
    register_rest_route('vietlist/v1', '/get_event/', array(
        'methods' => 'POST',
        'callback' => 'vietlist_get_event_data_endpoint',
    ));
    
    register_rest_route('vietlist/v1', '/update_event/', array(
        'methods' => 'POST',
        'callback' => 'vietlist_update_event_data_endpoint',
    ));
}

add_action('rest_api_init', 'vietlist_getPost_data_route');

function vietlist_get_category_endpoint(WP_REST_Request $request) {
        $headers = $request->get_headers();
        $token = str_replace('Bearer ', '', $headers['authorization'][0]);
        $user_id = $request->get_param('user_id');
        
        $args = array(
        'taxonomy'   => 'gd_placecategory',
        'hide_empty' => false,
        );
    
        $terms = get_terms($args);
        $result = [];

        if(isset($token) && !empty($token))
        {
            $user_id = get_user_id_by_token($headers);
            $bookmark = get_user_meta($user_id);
            $arr = array_map('intval', explode(',', $bookmark['bookmark'][0]));
        }
        elseif(isset($user_id) && !empty($user_id))
        {
            $bookmark = get_user_meta($user_id);
            $arr = array_map('intval', explode(',', $bookmark['bookmark'][0]));
        }
        
        foreach($terms as $term)
        {
            $category_id = $term->term_id;
            
            $url = get_term_link($term);
            $category_icon = get_term_meta($category_id, 'ct_cat_icon', true);
            $category_image = get_term_meta($category_id, 'ct_cat_default_img', true);
            $trending = get_term_meta($category_id, 'trending', true);
            
            if(empty($category_icon)){
                 $icon = site_url().'/wp-content/uploads/2020/04/restaurant-449952_1280.jpg';
            }else{
                $cat_icon_src = $category_icon['src'];
                $icon = site_url().'/wp-content/uploads/'.$cat_icon_src; 
            }
            
            if(empty($category_image)){
                 $image = site_url().'/wp-content/uploads/2020/04/restaurant-449952_1280.jpg';
            }else{
                $cat_image_src = $category_image['src'];
                $image = site_url().'/wp-content/uploads/'.$cat_image_src; 
            }
            
            if(!empty($trending))
            {
                $trending = 1;
            }
            else{
                $trending = 0;
            }
            
            $bookmark = false;
            if(isset($token) && !empty($token) or isset($user_id) && !empty($user_id))
            {
                if (in_array($category_id, $arr))
                {
                    $bookmark = true;
                }
            }
            
            array_push($result,[
                'id' => $term->term_id,
                'name' => htmlspecialchars_decode($term->name),
                'icon' => $icon,
                'image' => $image,
                'trending' => $trending,
                'bookmark' => $bookmark
                ]);
        }
        if(!empty($result))
        {
            return new WP_REST_Response(array('status' => true, 'data' => $result), 200);
        }
        else{
            return new WP_REST_Response(array('status' => true, 'data' => "Data Not Found"), 404);
        }
}

function vietlist_get_event_category_endpoint(WP_REST_Request $request) {
    
        $headers = $request->get_headers();
        $token = str_replace('Bearer ', '', $headers['authorization'][0]);
    

        $args = array(
        'taxonomy'   => 'gd_eventcategory',
        'hide_empty' => false,
        );
    
        $terms = get_terms($args);
        $result = [];
        
        foreach($terms as $term)
        {
            $category_id = $term->term_id;
            
            $url = get_term_link($term);
            $category_icon = get_term_meta($category_id, 'ct_cat_icon', true);
            $category_image = get_term_meta($category_id, 'ct_cat_default_img', true);
;
            
            if(empty($category_icon)){
                 $icon = site_url().'/wp-content/uploads/woocommerce-placeholder.png';
            }else{
                $cat_icon_src = $category_icon['src'];
                $icon = site_url().'/wp-content/uploads/'.$cat_icon_src; 
            }
            
            if(empty($category_image)){
                 $image = site_url().'/wp-content/uploads/2020/04/restaurant-449952_1280.jpg';
            }else{
                $cat_image_src = $category_image['src'];
                $image = site_url().'/wp-content/uploads/'.$cat_image_src; 
            }
            
            array_push($result,[
                'id' => $term->term_id,
                'name' => htmlspecialchars_decode($term->name),
                'icon' => $icon,
                'image' => $image,
                ]);
        }
        if(!empty($result))
        {
            return new WP_REST_Response(array('status' => true, 'data' => $result), 200);
        }
        else{
            return new WP_REST_Response(array('status' => true, 'data' => "Data Not Found"), 404);
        }
}


function vietlist_get_tags_endpoint(WP_REST_Request $request) {
    
        $args = array(
        'taxonomy'   => 'gd_place_tags',
        'hide_empty' => false,
        );
    
        $terms = get_terms($args);
        
        $result = [];
        foreach($terms as $term)
        {
            $tags_id = $term->term_id;

            array_push($result,[
                'id' => $term->term_id,
                'name' => $term->name,
                ]);
        }
        if(!empty($result))
        {
            return new WP_REST_Response(array('status' => true, 'data' => $result), 200);
        }
        else{
            return new WP_REST_Response(array('status' => true, 'data' => "Data Not Found"), 404);
        }
}

function vietlist_setbookmark_endpoint(WP_REST_Request $request) {
    $bookmark = sanitize_text_field($request->get_param('bookmark'));
    $user_id = $request->get_param('user_id');
    $headers = $request->get_headers();
    $token_validate='';
    if(!empty($headers['authorization'][0])){
        $token_validate = vietlist_validate_token($headers);
    }
    
        if($token_validate or !empty($user_id) && isset($user_id))
        {
            if($token_validate){
                 $user_id = get_user_id_by_token($headers);
            }
            
            if(!empty($bookmark))
            {
                $get_bookmark_data = get_user_meta($user_id,'bookmark',true);
                $numbersArray = explode(',', $get_bookmark_data);
                if (in_array($bookmark, $numbersArray)) {
                    $numbersArray = array_diff($numbersArray, [$bookmark]);
                    $updatedNumbersString = implode(',', $numbersArray);
                    $message = "Bookmark removed";
                }
                else{
                    $updatedNumbersString = $get_bookmark_data . ',' . $bookmark ;
                    $message = "Bookmark set";
                }
                update_user_meta($user_id, 'bookmark', $updatedNumbersString);
                return new WP_REST_Response(array('status' => true, 'message' => $message), 200);
            }
            else{
                return new WP_REST_Response(array('status' => false, 'message' => "Bookmark not found."), 404);
            }
    }
    else{
        return new WP_REST_Response(array('status' => false, 'message' => "Token validation failed."), 401);
    }
}

function uplaod_media($file)
{
    if (!empty($file) && isset($file)) {
        // if (strpos($file['type'], 'image/') === 0) {
            $upload = wp_upload_bits($file['name'], null, file_get_contents($file['tmp_name']));
            if (!$upload['error']) {
                $attachment_id = wp_usp_upload_file_by_url( $upload['url'] );
                $newUrl = "/home/vietlist24/public_html/wp-content/uploads/2023/12/" . str_replace(site_url("/wp-content/uploads/2023/12/"),"",$upload['url']);
                $newdisplayUrl = site_url("/wp-content/uploads/2023/12/") . str_replace("/home/vietlist24/public_html/wp-content/uploads/2023/12/","",get_attached_file($attachment_id));
                return $newdisplayUrl;
            } else {
                return 'Failed to upload profile picture';
            }
        // }
    }
}

function vietlist_set_business_data_endpoint(WP_REST_Request $request) {
    
    
    $headers = $request->get_headers();
    $token = str_replace('Bearer ', '', $headers['authorization'][0]);

    $post_title = !empty($request->get_param('post_title')) ? sanitize_text_field($request->get_param('post_title')) : null;
    $post_content = !empty($request->get_param('post_content')) ? sanitize_text_field($request->get_param('post_content')) : null;
    
    $search_title = !empty($request->get_param('search_title')) ? sanitize_text_field($request->get_param('search_title')) : $post_title;
    $post_status = isset($res['post_status']) ? $res['post_status'] : 'pending';
    
    $featured_image = !empty($_FILES['featured_image']) ?  uplaod_media($_FILES['featured_image']) : '';
    $verification_upload = !empty($_FILES['verification_upload']) ? uplaod_media($_FILES['verification_upload']) : '';
    $logo = !empty($_FILES['logo']) ? uplaod_media($_FILES['logo']) : '';
    $video_upload = !empty($_FILES['video_upload']) ? uplaod_media($_FILES['video_upload']) : '';

    $final_submission = !empty($request->get_param('final_submission')) ? sanitize_text_field($request->get_param('final_submission')) : 0;

    
    $token_validate = vietlist_validate_token($headers);
    if($token_validate)
    {
        
        $api_request = $request->get_params();
        // if(isset($api_request['post_category']) && !empty($api_request['post_category']))
        // {
        //     $post_category = $api_request['post_category'];
        //     print_r(gettype($post_category));
        //     die;
        //     $api_request['post_category'] = implode(",", $array);
        // }
        
        
        if(!isset($api_request['post_id']) && empty($api_request['post_id']))
        {
            $new_post = [
                'post_title' => $post_title,
                'post_content' => $post_content,
                'post_status' => $post_status,
                'post_author' => get_current_user_id(),
                'post_type' => 'gd_place',
            ];
            $post_id = wp_insert_post($new_post);
            echo $post_id;
            die;
            $api_request['post_id'] = $post_id;
        }
        
        $post_id = $api_request['post_id'];
        
        unset($api_request['post_id']);
        unset($api_request['post_title']);
        unset($api_request['post_content']);
        unset($api_request['post_status']);
        unset($api_request['final_submission']);
        
        $api_request['featured_image'] = $featured_image;
        $api_request['verification_upload'] = $verification_upload;
        $api_request['logo'] = $logo;
        $api_request['video_upload'] = $video_upload;
        
        $api_request = array_filter($api_request, function($value) {
            return $value !== '';
        });
 
        $update_query = '';
        foreach($api_request as $key => $value)
        {
            $update_query .= "`" . $key . "`" . " = " . "'" . $value ."',";
        }
        
        $length = strlen($string);
        $update_query = substr($update_query, 0, $length - 1);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'geodir_gd_place_detail';
        $query = $wpdb->prepare(
            "UPDATE $table_name 
            SET $update_query WHERE `post_id` = $post_id"
        );
        
        $result = $wpdb->query($query);
        if (is_wp_error($result)) {
            $error_message = $result->get_error_message();
        } else {
            update_post_meta($post_id, 'final_submission',0);
            if($final_submission == 1)
            {
                update_post_meta($post_id, 'final_submission',1);
            }
            
            return new WP_REST_Response(array('status' => true, 'post_id' => $post_id), 200);
        }
    }
    else{
        return new WP_REST_Response(array('status' => false, 'message' => "Token validation failed."), 401);
    }
}

function vietlist_update_business_data_endpoint(WP_REST_Request $request) {

    $headers = $request->get_headers();
    $token = str_replace('Bearer ', '', $headers['authorization'][0]);

    $post_title = !empty($request->get_param('post_title')) ? sanitize_text_field($request->get_param('post_title')) : null;
    $post_content = !empty($request->get_param('post_content')) ? sanitize_text_field($request->get_param('post_content')) : null;

    $search_title = !empty($request->get_param('search_title')) ? sanitize_text_field($request->get_param('search_title')) : $post_title;

    
    $featured_image = !empty($_FILES['featured_image']) ?  uplaod_media($_FILES['featured_image']) : '';
    $verification_upload = !empty($_FILES['verification_upload']) ? uplaod_media($_FILES['verification_upload']) : '';
    $logo = !empty($_FILES['logo']) ? uplaod_media($_FILES['logo']) : '';
    $video_upload = !empty($_FILES['video_upload']) ? uplaod_media($_FILES['video_upload']) : '';
    

    $final_submission = !empty($request->get_param('final_submission')) ? sanitize_text_field($request->get_param('final_submission')) : 0;
    


    
    $token_validate = vietlist_validate_token($headers);
    if($token_validate)
    {
        $api_request = $request->get_params();
        $post_id = $api_request['post_id'];
    
        
        if(isset($post_id) && !empty($post_id))
        {
            $post_author_id = get_post_field('post_author', $post_id);
            $user_id = get_user_id_by_token($headers);
            if ($post_author_id == $user_id) {
                $update_post = [
                    'ID' => $post_id,
                    'post_title' => $post_title,
                    'post_content' => $post_content,
                ];
                $update_post_result = wp_update_post($update_post);
            }
            else{
                return new WP_REST_Response(array('status' => false, 'message' => "Post Not Found"), 404);
            }
        }
        
        unset($api_request['post_title']);
        unset($api_request['post_content']);
        unset($api_request['post_status']);
        
        $api_request['featured_image'] = $featured_image;
        $api_request['verification_upload'] = $verification_upload;
        $api_request['logo'] = $logo;
        $api_request['video_upload'] = $video_upload;
        
        $api_request = array_filter($api_request, function($value) {
            return $value !== '';
        });
 
        $update_query = '';
        foreach($api_request as $key => $value)
        {
            $update_query .= "`" . $key . "`" . " = " . "'" . $value ."',";
        }
        
        $length = strlen($string);
        $update_query = substr($update_query, 0, $length - 1);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'geodir_gd_place_detail';
        $query = $wpdb->prepare(
            "UPDATE $table_name 
            SET $update_query WHERE `post_id` = $post_id"
        );
        $result = $wpdb->query($query);
        if (is_wp_error($result)) {
            $error_message = $result->get_error_message();
        } else {
            return new WP_REST_Response(array('status' => true, 'message' => "Post Updated"), 200);
        }
    }
    else{
        return new WP_REST_Response(array('status' => false, 'message' => "Token validation failed."), 401);
    }
}


function vietlist_delete_post_data_endpoint(WP_REST_Request $request)
{
    $headers = $request->get_headers();
    $token = str_replace('Bearer ', '', $headers['authorization'][0]);
    
    $token_validate = vietlist_validate_token($headers);
    if($token_validate)
    {
        $api_request = $request->get_params();
        $post_id = $api_request['post_id'];
        if(isset($post_id) && !empty($post_id))
        {
            $post_author_id = get_post_field('post_author', $post_id);
            $user_id = get_user_id_by_token($headers);
            if ($post_author_id == $user_id) {
                $deleted = wp_delete_post($post_id, true);
                if ($deleted) {
                    return new WP_REST_Response(array('status' => true, 'message' => 'Post has been deleted', 200));
                } else {
                    return new WP_REST_Response(array('status' => false, 'message' => 'Failed to delete post', 204));
                }
            }
            else{
                return new WP_REST_Response(array('status' => false, 'message' => "Post Not Found"), 404);
            }
        }
        else{
            return new WP_REST_Response(array('status' => false, 'message' => 'please provide post id', 204));
        }
    }
    else{
        return new WP_REST_Response(array('status' => false, 'message' => "Token validation failed."), 401);
    }
    
}

function vietlist_get_business_data_endpoint(WP_REST_Request $request)
{
    $headers = $request->get_headers();
    $token = str_replace('Bearer ', '', $headers['authorization'][0]);
    
    $token_validate = vietlist_validate_token($headers);
    if($token_validate)
    {
        $gd_place_posts = get_posts(array(
        'author' => get_current_user_id(),
        'posts_per_page' => -1,
        'post_type' => 'gd_place',
        'post_status'   => 'pending'
        ));
        
    
        $post_ids = 0;
        foreach ($gd_place_posts as $post_id) {
            $final_submission = get_post_meta($post_id->ID,'final_submission', true);
            if($final_submission == 0)
            {
             $post_ids = $post_id->ID;
            }
        }
        global $wpdb;
        $table_name = $wpdb->prefix . 'geodir_gd_place_detail';
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE `post_id` = $post_ids"
        );
        
        $results = $wpdb->get_results($query);
        if (substr($results[0]->post_category, -1) === ',') {
            $query_value = rtrim($results[0]->post_category, ',');
        }
        else{
            $query_value = $results[0]->post_category;
        }
        
        $results[0]->post_category = explode(',', $query_value);
        return new WP_REST_Response(array('status' => true, 'data' => $results[0]), 200);
        
    }
    else{
        return new WP_REST_Response(array('status' => false, 'message' => "Token validation failed."), 401);
    }
    
}

function vietlist_set_event_data_endpoint(WP_REST_Request $request) {
    $headers = $request->get_headers();
    $token = str_replace('Bearer ', '', $headers['authorization'][0]);
    $post_title = !empty($request->get_param('post_title')) ? sanitize_text_field($request->get_param('post_title')) : null;
    $post_content = !empty($request->get_param('post_content')) ? sanitize_text_field($request->get_param('post_content')) : null;
    $post_status = isset($res['post_status']) ? $res['post_status'] : 'pending';


    $featured_image = !empty($_FILES['featured_image']) ?  uplaod_media($_FILES['featured_image']) : '';
    
    $token_validate = vietlist_validate_token($headers);
    if($token_validate)
    {
        
        $api_request = $request->get_params();
        
        
        $new_event_post = [
            'post_title' => $post_title,
            'post_content' => $post_content,
            'post_status' => $post_status,
            'post_author' => get_current_user_id(),
            'post_type' => 'gd_event',
        ];
        
        $post_id = wp_insert_post($new_event_post);
        
        if($post_id)
        {
            unset($api_request['post_id']);
            unset($api_request['post_title']);
            unset($api_request['post_content']);
            unset($api_request['post_status']);
            
            if(isset($api_request['event_dates']) && !empty($api_request['event_dates']))
            {
                $convert_string_to_json = json_decode($api_request['event_dates']);
                $convert_json_to_array = json_decode(json_encode($convert_string_to_json), true);
                
                $serialized_dates_data = serialize($convert_json_to_array);
                $api_request['event_dates'] = $serialized_dates_data;
            }
            
            $api_request['featured_image'] = $featured_image;
            
            $api_request = array_filter($api_request, function($value) {
                return $value !== '';
            });
     
            $update_query = '';
            foreach($api_request as $key => $value)
            {
                $update_query .= "`" . $key . "`" . " = " . "'" . $value ."',";
            }
            
            $length = strlen($string);
            $update_query = substr($update_query, 0, $length - 1);
            
            global $wpdb;
            $table_name = $wpdb->prefix . 'geodir_gd_event_detail';
            $query = $wpdb->prepare(
                "UPDATE $table_name 
                SET $update_query WHERE `post_id` = $post_id"
            );

            $result = $wpdb->query($query);
            if (is_wp_error($result)) {
                $error_message = $result->get_error_message();
            } else {
                return new WP_REST_Response(array('status' => true, 'post_id' => $post_id), 200);
            }
        }
    }
    else{
        return new WP_REST_Response(array('status' => false, 'message' => "Token validation failed."), 401);
    }
}

function vietlist_update_event_data_endpoint(WP_REST_Request $request) {

    $headers = $request->get_headers();
    $token = str_replace('Bearer ', '', $headers['authorization'][0]);
    $post_title = !empty($request->get_param('post_title')) ? sanitize_text_field($request->get_param('post_title')) : null;
    $post_content = !empty($request->get_param('post_content')) ? sanitize_text_field($request->get_param('post_content')) : null;
    

    $search_title = !empty($request->get_param('search_title')) ? sanitize_text_field($request->get_param('search_title')) : $post_title;

    
    $featured_image = !empty($_FILES['featured_image']) ?  uplaod_media($_FILES['featured_image']) : '';
    
    $token_validate = vietlist_validate_token($headers);
    if($token_validate)
    {
        $api_request = $request->get_params();
        

        $post_id = $api_request['post_id'];
    
        
        if(isset($post_id) && !empty($post_id))
        {
            $post_author_id = get_post_field('post_author', $post_id);
            $user_id = get_user_id_by_token($headers);
            if ($post_author_id == $user_id) {
                $update_event = [
                    'ID' => $post_id,
                    'post_title' => $post_title,
                    'post_content' => $post_content,
                ];
                $update_post_result = wp_update_post($update_event);
            }
            else{
                return new WP_REST_Response(array('status' => false, 'message' => "Event Not Found"), 404);
            }
        }
        
        unset($api_request['post_title']);
        unset($api_request['post_content']);
        unset($api_request['post_status']);
        
        $api_request['featured_image'] = $featured_image;
        
        $api_request = array_filter($api_request, function($value) {
            return $value !== '';
        });
        
        // print_r($api_request);
        // die;
 
        $update_query = '';
        foreach($api_request as $key => $value)
        {
            $update_query .= "`" . $key . "`" . " = " . "'" . $value ."',";
        }
        
        $length = strlen($string);
        $update_query = substr($update_query, 0, $length - 1);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'geodir_gd_event_detail';
        $query = $wpdb->prepare(
            "UPDATE $table_name 
            SET $update_query WHERE `post_id` = $post_id"
        );
        $result = $wpdb->query($query);
        if (is_wp_error($result)) {
            $error_message = $result->get_error_message();
        } else {
            return new WP_REST_Response(array('status' => true, 'message' => "Event Updated"), 200);
        }
    }
    else{
        return new WP_REST_Response(array('status' => false, 'message' => "Token validation failed."), 401);
    }
}

function vietlist_get_event_data_endpoint(WP_REST_Request $request)
{
    $headers = $request->get_headers();
    $token = str_replace('Bearer ', '', $headers['authorization'][0]);
    
    $token_validate = vietlist_validate_token($headers);
    if($token_validate)
    {
        $gd_place_posts = get_posts(array(
        'author' => get_current_user_id(),
        'posts_per_page' => -1,
        'post_type' => 'gd_event',
        'post_status'   => 'pending'
        ));
        
    
        $data = new stdClass();
        foreach ($gd_place_posts as $post_data) {
            $post_id = $post_data->ID;
            global $wpdb;
            $table_name = $wpdb->prefix . 'geodir_gd_event_detail';
            $query = $wpdb->prepare(
                "SELECT * FROM $table_name WHERE `post_id` = $post_id"
            );
            $results = $wpdb->get_results($query);
            if (substr($results[0]->post_category, -1) === ',') {
                $query_value = rtrim($results[0]->post_category, ',');
            }
            else{
                $query_value = $results[0]->post_category;
            }
            
            $results[0]->post_category = explode(',', $query_value);
            $results[0]->event_dates = json_decode($results[0]->event_dates);
            $data->$post_id = $results[0];
        }
        
        return new WP_REST_Response(array('status' => true, 'data' => $data), 401);
    }
    else{
        return new WP_REST_Response(array('status' => false, 'message' => "Token validation failed."), 401);
    }
    
}


?>
