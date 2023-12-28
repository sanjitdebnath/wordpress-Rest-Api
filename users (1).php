<?php

function vietlist_user_route() {
    
    register_rest_route('vietlist/v1', '/login/', array(
        'methods' => 'POST',
        'callback' => 'vietlist_login_endpoint',
    ));
    
    register_rest_route('vietlist/v1', '/register/', array(
        'methods' => 'POST',
        'callback' => 'vietlist_register_endpoint',
    ));
    
    register_rest_route('vietlist/v1', '/sendOtp/', array(
        'methods' => 'POST',
        'callback' => 'vietlist_send_otp_endpoint',
    ));
    
    register_rest_route('vietlist/v1', '/validateOtp/', array(
        'methods' => 'POST',
        'callback' => 'vietlist_validate_opt_endpoint',
    ));
    
    register_rest_route('vietlist/v1', '/passwordReset/', array(
        'methods' => 'POST',
        'callback' => 'vietlist_password_reset_endpoint',
    ));
    
    register_rest_route('vietlist/v1', '/showUserProfile/', array(
        'methods' => 'POST',
        'callback' => 'vietlist_show_user_profile_endpoint',
    ));
    
    register_rest_route('vietlist/v1', '/updateUserProfile/', array(
        'methods' => 'POST',
        'callback' => 'vietlist_update_user_profile_endpoint',
        'permission_callback' => function ($request) {
            return is_user_logged_in();
        },
    ));
    
    register_rest_route('vietlist/v1', '/changeUserProfilePassword/', array(
        'methods' => 'POST',
        'callback' => 'vietlist_change_user_password_endpoint',
    ));
    
}

add_action('rest_api_init', 'vietlist_user_route');

// generate Token
function vietlist_generate_token($username,$password) {
    $ch = curl_init();
    $token_url = site_url('/wp-json/jwt-auth/v1/token');
    curl_setopt($ch, CURLOPT_URL,$token_url);
    curl_setopt($ch, CURLOPT_POST, 1);

    curl_setopt($ch, CURLOPT_POSTFIELDS, "username=$username&password=$password"); 

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec ($ch);
    if ($server_output === false) {
        die('Error getting JWT token on WordPress for API integration.');
    }
    $server_output = json_decode($server_output);

    if ($server_output === null && json_last_error() !== JSON_ERROR_NONE) {
        die('Invalid response getting JWT token on WordPress for API integration.');
    }

    if (!empty($server_output->token)) {
        $token = $server_output->token;
        curl_close ($ch);
        return $token;
    } else {
        die('Invalid response getting JWT token on WordPress for API integration.');
    }
    return false;
}


function vietlist_validate_token($headers) {
    $jwt_token = str_replace('Bearer ', '', $headers['authorization'][0]);
    $token_url = site_url('/wp-json/jwt-auth/v1/token/validate');
    $curl = curl_init();
    
    curl_setopt_array($curl, array(
      CURLOPT_URL => $token_url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer '.$jwt_token ,
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    $dataArray = json_decode($response, true);
    $status = $dataArray['data']['status'];
    
    if($status == "200")
    {
        return true;
    }
    else{
        return false;
    }
}



//Login Api
function vietlist_login_endpoint(WP_REST_Request $request) {
    $username = sanitize_user($request->get_param('username'));
    $password = $request->get_param('password');
    
    $user = wp_signon(array(
        'user_login'   => $username,
        'user_password' => $password,
        'remember'      => true,
    ));

    if (is_wp_error($user)) {
        return new WP_REST_Response(array('status' => false, 'message' => "Wrong Credentials"), 401);
    } else {
        $token = vietlist_generate_token($username,$password);
        $data = get_user_meta($user->data->ID);
        
        $user_image = (isset($data["user_image"][0])) ? $data["user_image"][0] : '';
        $user_name = $user->data->display_name;
        $phone_number = (isset($data["phone"][0])) ? $data["phone"][0] : '';
        $language = (isset($data["locale"][0])) ? $data["locale"][0] : '';
        $bookmark = (isset($data["bookmark"][0])) ? $data["bookmark"][0] : '';
        
        $user->data->user_role = $user->roles[0];
        $user->data->user_image = $user_image;
        $user->data->phone_number = $phone_number;
        $user->data->language = $language;
        $user->data->bookmark = $bookmark;
        
        $data = array("token" => $token, "user" => $user->data);
        if ($token) {
            return new WP_REST_Response(array('status' => true, 'data' => $data), 200);
        }
        
    }
}


//Register Api
function vietlist_register_endpoint(WP_REST_Request $request) {
    
    $username = sanitize_user($request->get_param('username'));
    $email = sanitize_email($request->get_param('email'));
    $password = $request->get_param('password');
    $user_role = $request->get_param('role');
    
    if (username_exists($username) || email_exists($email)) {
        return new WP_REST_Response(array('status' => false, 'message' => 'Username or email already exists'), 409);
    }
    
    $user_id = wp_create_user($username, $password, $email, $user_role);
    if (is_wp_error($user_id)) {
        return new WP_REST_Response(array('status' => false, 'message' => "User Not Found"), 404);
    } else {
        if (!empty($user_role) && array_key_exists($user_role, wp_roles()->get_names())) {
            $user = new WP_User($user_id);
            $user->set_role($user_role);
            
            $token = vietlist_generate_token($email,$password);
            
            $data = get_user_meta($user_id);
        
            $user_image = (isset($data["user_image"][0])) ? $data["user_image"][0] : '';
            $user_name = (isset($data["nickname"][0])) ? $data["nickname"][0] : '';
            $phone_number = (isset($data["phone"][0])) ? $data["phone"][0] : '';
            $language = (isset($data["locale"][0])) ? $data["locale"][0] : '';
            
            $user->data->user_role = $user_role;
            $user->data->user_image = $user_image;
            $user->data->phone_number = $phone_number;
            $user->data->language = $language;
            
            $data = array("token" => $token, "user" => $user->data);
            return new WP_REST_Response(array('status' => true, 'data' => $data), 200);
        }
    }
}


//reset user password endpoint
function vietlist_send_otp_endpoint(WP_REST_Request $request) {
    $email = sanitize_user($request->get_param('email'));
    $user_data = get_user_by('email', $email);

        if (!$user_data) {
            return new WP_REST_Response(array('status' => false, 'message' => 'User not found'), 404);
        }
    
        $optGwnerate = rand(1000,10000);
        
        $admin_to = $email;
        $admin_subject = 'testing';
        $admin_body = 'your opt is : '.$optGwnerate;
        $admin_headers = 'testing';
        $res = wp_mail( $admin_to, $admin_subject, $admin_body, $admin_headers );
    
        if($res)
        {
            update_user_meta( $user_data->data->ID, 'otp', $optGwnerate );
            return new WP_REST_Response(array('status' => true, 'message' => "email has been send"), 200);
        }
        else{
            return new WP_REST_Response(array('status' => false, 'message' => "email sending failed"), 500);
        }
    
}

//validate api
function vietlist_validate_opt_endpoint(WP_REST_Request $request) {
    $otp = sanitize_user($request->get_param('otp'));
    $email = sanitize_user($request->get_param('email'));

    $user_data = get_user_by('email', $email);
    
    if (!$user_data) {
        return new WP_REST_Response(array('status' => false, 'message' => 'User not found'), 404);
    }

    if ($user_data) {
        $user_id = $user_data->data->ID;
        
        $opt_value = get_user_meta($user_id, 'otp', true);
    
        if (!empty($otp) && isset($otp)) {
            if ($opt_value !== '' && $otp === $opt_value) {
                update_user_meta( $user_id, 'otp', null );
                return new WP_REST_Response(array('status' => true, 'message' => 'otp validate'), 200);
            }
            else{
                return new WP_REST_Response(array('status' => false, 'message' => 'Wrong otp'), 401);
            }
        } else {
            return new WP_REST_Response(array('status' => false, 'message' => 'Please Enter Otp'), 200);
        }
       
    }

}

function vietlist_password_reset_endpoint(WP_REST_Request $request) {
    $email = sanitize_user($request->get_param('email'));
    $new_password = sanitize_user($request->get_param('password'));
    
    $user = get_user_by('email', $email);
    if ($user) {
        wp_set_password($new_password, $user->data->ID);
        return new WP_REST_Response(array('status' => true, 'message' => 'Password updated successfully.'), 200);
    } else {
        return new WP_REST_Response(array('status' => false, 'message' => 'Password updated failed.'), 500);
    }
}

function vietlist_show_user_profile_endpoint(WP_REST_Request $request) {
    $email = sanitize_user($request->get_param('email'));
    $user = get_user_by('email', $email);

    $data = get_user_meta($user->data->ID);
    return new WP_REST_Response(array('status' => true, 'data' => $user), 200);
    
    
    
    if (!$user) {
        return new WP_REST_Response(array('status' => false, 'message' => 'User not found'), 404);
    }
    else{
        return new WP_REST_Response(array('status' => true, 'data' => $data), 200);
    }

}


function get_user_id_by_token($headers) {
    
    $token = str_replace('Bearer ', '', $headers['authorization'][0]);
    $token_parts = explode('.', $token);
    $token_payload = base64_decode($token_parts[1]);
    $user_data = json_decode($token_payload, true);
    
    $user_id = $user_data['data']['user']['id'];
    return $user_id;
}

function wp_usp_upload_file_by_url( $image_url ) {



    // it allows us to use download_url() and wp_handle_sideload() functions
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    

    // download to temp dir
    $temp_file = download_url( $image_url );


    if( is_wp_error( $temp_file ) ) {
        return false;
    }

    $image_info = getimagesize($temp_file);
    $mime_type = $image_info['mime'];
    
    // move the temp file into the uploads directory
    $file = array(
        'name'     => basename( $image_url ),
        'type'     => $mime_type,
        'tmp_name' => $temp_file,
        'size'     => filesize( $temp_file ),
    );

    
    $sideload = wp_handle_sideload(
        $file,
        array(
            'test_form'   => false // no needs to check 'action' parameter
        )
    );

    if( ! empty( $sideload[ 'error' ] ) ) {
        // you may return error message if you want
        return false;
    }

    // it is time to add our uploaded image into WordPress media library
    $attachment_id = wp_insert_attachment(
        array(
            'guid'           => $sideload[ 'url' ],
            'post_mime_type' => $sideload[ 'type' ],
            'post_title'     => basename( $sideload[ 'file' ] ),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ),
        $sideload[ 'file' ]
    );

    if( is_wp_error( $attachment_id ) || ! $attachment_id ) {
        return false;
    }

    // update medatata, regenerate image sizes
    require_once( ABSPATH . 'wp-admin/includes/image.php' );

    wp_update_attachment_metadata(
        $attachment_id,
        wp_generate_attachment_metadata( $attachment_id, $sideload[ 'file' ] )
    );

    return $attachment_id;

}


function vietlist_update_user_profile_endpoint(WP_REST_Request $request) {

    $user_name = sanitize_user($request->get_param('user_name'));
    $phone_number = sanitize_user($request->get_param('phone_number'));
    $email = sanitize_user($request->get_param('user_email'));
    $language = sanitize_user($request->get_param('language'));
    $delete_image = sanitize_user($request->get_param('delete_image'));
    $attachment_id = sanitize_user($request->get_param('attachment_id'));
    $file = $_FILES['user_image'];
    $headers = $request->get_headers();
    $token_validate = vietlist_validate_token($headers);
    if($token_validate)
    {
        $user_id = get_user_id_by_token($headers);
        if (!$user_id) {
            return new WP_REST_Response(array('status' => false, 'message' => 'User not found'), 404);
        }
        else{
            $profile_upload_status = '';
            if (!empty($file) && isset($file)) {
                if (strpos($file['type'], 'image/') === 0) {
                    $upload = wp_upload_bits($file['name'], null, file_get_contents($file['tmp_name']));
                    if (!$upload['error']) {
                        $attachment_id = wp_usp_upload_file_by_url( $upload['url'] );
                        update_user_meta($user_id, 'attachment_id', $attachment_id);
                        $newUrl = "/home/vietlist24/public_html/wp-content/uploads/2023/12/" . str_replace(site_url("/wp-content/uploads/2023/12/"),"",$upload['url']);
                        $newdisplayUrl = site_url("/wp-content/uploads/2023/12/") . str_replace("/home/vietlist24/public_html/wp-content/uploads/2023/12/","",get_attached_file($attachment_id));
                        update_user_meta($user_id, 'user_image', $newdisplayUrl);
                        unlink($newUrl);
                    } else {
                        $profile_upload_status =  'Failed to upload profile picture';
                    }
                }
            }
            else{
                if($delete_image == "true")
                {
                    wp_delete_attachment($attachment_id, true);
                    update_user_meta($user_id, 'user_image', "");
                }
            }

    
    
            $user = get_user_by('ID', $user_id);

            if(isset($user_name) && !empty($user_name))
            {
                $display_name = $user_name;
            }
            else{
                $display_name = $user->data->display_name;
            }
            
            
            if(isset($email) && !empty($email))
            {
                $user_email = $email;
            }
            else{
                $user_email = $user->data->user_email;
            }
            
            $updated_user_data = array(
                    'ID'           => $user_id,
                    'display_name'   => $display_name, 
                    'user_email' => $user_email
                    
                );
                
            $updated = wp_update_user($updated_user_data);
            
            
            if (is_wp_error($updated)) {
                return new WP_REST_Response(array('status' => false, 'message' => "Sorry, that email address is already used!"), 409);
            }
            
            $phone_number = (isset($phone_number)) ? $phone_number : '';
            if(!empty($phone_number))
            {
                update_user_meta( $user_id, 'phone', $phone_number );
            }
            
            $language = (isset($language)) ? $language : '';
            if(!empty($language))
            {
                update_user_meta( $user_id, 'locale', $language );
            }
            

            $user = get_user_by('ID', $user_id);
            $data = get_user_meta($user_id);
        
            $user_image = (isset($data["user_image"][0])) ? $data["user_image"][0] : '';
            $phone_number = (isset($data["phone"][0])) ? $data["phone"][0] : '';
            $language = (isset($data["locale"][0])) ? $data["locale"][0] : '';
            $attachment_id = (isset($data["attachment_id"][0])) ? $data["attachment_id"][0] : '';
            
            $user->data->user_role = $user->roles[0];
            $user->data->user_image = $user_image;
            $user->data->phone_number = $phone_number;
            $user->data->language = $language;
            $user->data->attachment_id = $attachment_id;
            
            $data = array("user" => $user->data);
            return new WP_REST_Response(array('status' => true, 'data' => $data), 200);
        }
    }
    else{
        return new WP_REST_Response(array('status' => false, 'message' => "Token validation failed."), 401);
    }
    
}

function vietlist_change_user_password_endpoint(WP_REST_Request $request) {
    $email = sanitize_user($request->get_param('email'));
    $old_password = sanitize_user($request->get_param('old_password'));
    $new_password = sanitize_user($request->get_param('new_password'));
    
    $headers = $request->get_headers();
    $token_validate = vietlist_validate_token($headers);
    if($token_validate)
    {
        $user = get_user_by('email', $email);
        
        if (!$user) {
            return new WP_REST_Response(array('status' => false, 'message' => 'User not found'), 404);
        }
        else
        {
            $user_id = $user->data->ID;
            $pass = $user->data->user_pass;
            
            if (!wp_check_password($old_password, $pass, $user_id)) {
                return new WP_REST_Response(array('status' => false, 'message' => 'Old password does not match the current password.'), 400);;
            }
            else{
                wp_set_password($new_password, $user_id);
                return new WP_REST_Response(array('status' => true, 'message' => 'Password updated successfully.'), 200);
            }
        }
    }
    else{
        return new WP_REST_Response(array('status' => false, 'message' => "Token validation failed."), 401);
    }
}



?>