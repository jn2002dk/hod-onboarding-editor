<?php
/*
Plugin Name: HoD Onboarding Editor
Description: Frontend dashboard for HoDs to edit and send onboarding entries, plus public employee form.
Version: 1.3
Author: Your Name
*/

// Create custom table on plugin activation
register_activation_hook( __FILE__, 'hod_create_table' );
function hod_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'hod_onboarding';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        start_date varchar(50) NOT NULL,
        status varchar(20) DEFAULT 'pending',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    
    // Log table creation result
    error_log( 'HOD Table Creation: ' . ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ? 'Success' : 'Failed: ' . $wpdb->last_error ) );
}

// Enqueue JS and CSS
define( 'HOD_ALLOWED_ROLE', 'administrator' ); // Replace with your HoD role
function hod_enqueue_scripts() {
    // Enqueue CSS for all pages with our shortcodes
    wp_enqueue_style( 'hod-styles', plugin_dir_url( __FILE__ ) . 'hod-styles.css', array(), '1.3' );
    
    // Enqueue JS for dashboard
    if ( is_user_logged_in() && current_user_can( HOD_ALLOWED_ROLE ) ) {
        wp_enqueue_script( 'hod-dashboard-js', plugin_dir_url( __FILE__ ) . 'hod-dashboard.js', array( 'jquery' ), '1.3', true );
        wp_localize_script( 'hod-dashboard-js', 'hod_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'hod_nonce' )
        ) );
    }
}
add_action( 'wp_enqueue_scripts', 'hod_enqueue_scripts' );

// Shortcode for employee form
function hod_employee_form_shortcode() {
    ob_start();
    ?>
    <form id="hod-employee-form">
        <label>Name</label>
        <input type="text" name="name-1" required>
        <label>Email</label>
        <input type="email" name="email-1" required>
        <label>Start Date</label>
        <input type="date" name="date-1" required>
        <button type="submit">Submit</button>
    </form>
    <div id="form-message"></div>
    <script>
        jQuery(document).ready(function($) {
            $('#hod-employee-form').on('submit', function(e) {
                e.preventDefault();
                const name = $('input[name="name-1"]').val().trim();
                const email = $('input[name="email-1"]').val().trim();
                const start_date = $('input[name="date-1"]').val().trim();
                
                if (!name || !email || !start_date) {
                    $('#form-message').removeClass('success').html('<p>Please fill all fields.</p>');
                    return;
                }
                
                const data = {
                    action: 'submit_hod_employee',
                    nonce: '<?php echo wp_create_nonce( 'hod_nonce' ); ?>',
                    name: name,
                    email: email,
                    start_date: start_date
                };
                
                $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function(response) {
                    if (response.success) {
                        $('#form-message').addClass('success').html('<p>Thank you for submitting!</p>');
                        $('#hod-employee-form')[0].reset();
                    } else {
                        $('#form-message').removeClass('success').html('<p>Error: ' + response.data + '</p>');
                    }
                });
            });
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode( 'employee_onboarding_form', 'hod_employee_form_shortcode' );

// AJAX: Handle employee form submission
add_action( 'wp_ajax_submit_hod_employee', 'submit_hod_employee' );
add_action( 'wp_ajax_nopriv_submit_hod_employee', 'submit_hod_employee' ); // Public access
function submit_hod_employee() {
    check_ajax_referer( 'hod_nonce', 'nonce' );
    global $wpdb;
    $table_name = $wpdb->prefix . 'hod_onboarding';
    
    $data = array(
        'name' => sanitize_text_field( $_POST['name'] ?? '' ),
        'email' => sanitize_email( $_POST['email'] ?? '' ),
        'start_date' => sanitize_text_field( $_POST['start_date'] ?? '' ),
        'status' => 'pending'
    );
    
    // Validate data
    if ( empty( $data['name'] ) || empty( $data['email'] ) || empty( $data['start_date'] ) ) {
        error_log( 'HOD Submit Validation Failed: ' . print_r( $data, true ) );
        wp_send_json_error( 'All fields are required' );
    }
    
    // Log incoming data
    error_log( 'HOD Submit Data: ' . print_r( $data, true ) );
    
    $result = $wpdb->insert(
        $table_name,
        $data,
        array( '%s', '%s', '%s', '%s' )
    );
    
    if ( $result === false ) {
        error_log( 'HOD Insert Error: ' . $wpdb->last_error );
        wp_send_json_error( 'Failed to save entry: ' . $wpdb->last_error );
    } else {
        wp_send_json_success();
    }
}

// Shortcode for dashboard
function hod_onboarding_dashboard_shortcode() {
    if ( ! is_user_logged_in() || ! current_user_can( HOD_ALLOWED_ROLE ) ) {
        return '<p>Access denied. Please log in as a department head.</p>';
    }
    ob_start();
    ?>
    <div id="hod-dashboard">
        <h2>Onboarding Entries</h2>
        <div id="entries-table"></div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'hod_onboarding_dashboard', 'hod_onboarding_dashboard_shortcode' );

// Register Gutenberg blocks
function hod_register_blocks() {
    wp_register_script( 'hod-blocks-js', plugin_dir_url( __FILE__ ) . 'hod-blocks.js', array( 'wp-blocks', 'wp-element', 'wp-editor' ), '1.3', true );
    
    register_block_type( 'hod/employee-form', array(
        'editor_script' => 'hod-blocks-js',
        'render_callback' => 'hod_employee_form_shortcode',
    ) );
    
    register_block_type( 'hod/dashboard', array(
        'editor_script' => 'hod-blocks-js',
        'render_callback' => 'hod_onboarding_dashboard_shortcode',
    ) );
}
add_action( 'init', 'hod_register_blocks' );

// Enqueue CSS for block editor
function hod_enqueue_block_editor_assets() {
    wp_enqueue_style( 'hod-styles', plugin_dir_url( __FILE__ ) . 'hod-styles.css', array(), '1.3' );
}
add_action( 'enqueue_block_editor_assets', 'hod_enqueue_block_editor_assets' );

// AJAX: Get entries
add_action( 'wp_ajax_get_hod_entries', 'get_hod_entries' );
function get_hod_entries() {
    check_ajax_referer( 'hod_nonce', 'nonce' );
    if ( ! current_user_can( HOD_ALLOWED_ROLE ) ) {
        wp_send_json_error( 'Access denied' );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'hod_onboarding';
    $entries = $wpdb->get_results( "SELECT * FROM $table_name WHERE status != 'sent'", ARRAY_A );
    
    if ( $wpdb->last_error ) {
        error_log( 'HOD Get Entries Error: ' . $wpdb->last_error );
        wp_send_json_error( 'Failed to fetch entries: ' . $wpdb->last_error );
    }
    
    wp_send_json_success( $entries );
}

// AJAX: Update entry
add_action( 'wp_ajax_update_hod_entry', 'update_hod_entry' );
function update_hod_entry() {
    check_ajax_referer( 'hod_nonce', 'nonce' );
    if ( ! current_user_can( HOD_ALLOWED_ROLE ) ) {
        wp_send_json_error( 'Access denied' );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'hod_onboarding';
    $entry_id = intval( $_POST['entry_id'] );
    $updates = $_POST['updates'];

    $data = array(
        'name' => sanitize_text_field( $updates['name-1'] ?? '' ),
        'email' => sanitize_email( $updates['email-1'] ?? '' ),
        'start_date' => sanitize_text_field( $updates['date-1'] ?? '' ),
        'status' => 'updated'
    );
    
    if ( empty( $data['name'] ) || empty( $data['email'] ) || empty( $data['start_date'] ) ) {
        error_log( 'HOD Update Validation Failed: ' . print_r( $data, true ) );
        wp_send_json_error( 'All fields are required' );
    }

    $result = $wpdb->update(
        $table_name,
        $data,
        array( 'id' => $entry_id ),
        array( '%s', '%s', '%s', '%s' ),
        array( '%d' )
    );

    if ( $result === false ) {
        error_log( 'HOD Update Error: ' . $wpdb->last_error );
        wp_send_json_error( 'Failed to update entry: ' . $wpdb->last_error );
    } else {
        wp_send_json_success();
    }
}

// AJAX: Send to Admin/IT
add_action( 'wp_ajax_send_hod_entry', 'send_hod_entry' );
function send_hod_entry() {
    check_ajax_referer( 'hod_nonce', 'nonce' );
    if ( ! current_user_can( HOD_ALLOWED_ROLE ) ) {
        wp_send_json_error( 'Access denied' );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'hod_onboarding';
    $entry_id = intval( $_POST['entry_id'] );
    $entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $entry_id ), ARRAY_A );

    if ( ! $entry ) {
        error_log( 'HOD Send Error: Entry not found for ID ' . $entry_id );
        wp_send_json_error( 'Entry not found' );
    }

    // Build email content
    $message = "Onboarding Entry Sent:\n\n";
    $message .= "Name: " . $entry['name'] . "\n";
    $message .= "Email: " . $entry['email'] . "\n";
    $message .= "Start Date: " . $entry['start_date'] . "\n";

    // Send email (replace recipients)
    $sent = wp_mail( 'admin@yourdomain.com, it@yourdomain.com', 'New Onboarding Ready', $message );

    if ( ! $sent ) {
        error_log( 'HOD Email Error: Failed to send email for entry ' . $entry_id );
        wp_send_json_error( 'Failed to send email' );
    }

    // Update status
    $result = $wpdb->update(
        $table_name,
        array( 'status' => 'sent' ),
        array( 'id' => $entry_id ),
        array( '%s' ),
        array( '%d' )
    );

    if ( $result === false ) {
        error_log( 'HOD Status Update Error: ' . $wpdb->last_error );
        wp_send_json_error( 'Failed to update status: ' . $wpdb->last_error );
    }

    wp_send_json_success();
}
?>