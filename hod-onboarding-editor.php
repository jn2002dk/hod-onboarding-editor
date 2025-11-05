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
        phone varchar(20),
        ice_name varchar(255),
        ice_phone varchar(20),
        bank_reg_nr varchar(50),
        bank_account_nr varchar(50),
        tax_type varchar(20),
        teaching_degree varchar(3),
        pedagogue_degree varchar(3),
        misc text,
        consent tinyint(1) DEFAULT 0,
        keys_30 tinyint(1) DEFAULT 0,
        keys_0 tinyint(1) DEFAULT 0,
        keys_music tinyint(1) DEFAULT 0,
        keys_gym tinyint(1) DEFAULT 0,
        flowers varchar(3) DEFAULT 'no',
        flower_delivery varchar(10) DEFAULT 'home',
        laptop varchar(3) DEFAULT 'no',
        temp_employment varchar(3) DEFAULT 'no',
        temp_reason text,
        employment_level varchar(3) DEFAULT '0',
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
        <div class="form-field">
            <label>Name</label>
            <input type="text" name="name-1" required>
        </div>
        <div class="form-field">
            <label>Email</label>
            <input type="email" name="email-1" required>
        </div>
        <div class="form-field">
            <label>Start Date</label>
            <input type="date" name="date-1" required>
        </div>
        <div class="form-field">
            <label>Phone</label>
            <input type="tel" name="phone" required>
        </div>
        <div class="form-field">
            <label>ICE Name</label>
            <input type="text" name="ice_name" required>
        </div>
        <div class="form-field">
            <label>ICE Phone</label>
            <input type="tel" name="ice_phone" required>
        </div>
        <div class="form-field">
            <label>Bank Reg Nr</label>
            <input type="text" name="bank_reg_nr" required>
        </div>
        <div class="form-field">
            <label>Bank Account Nr</label>
            <input type="text" name="bank_account_nr" required>
        </div>
        <div class="form-field">
            <label>Tax Type</label>
            <div class="radio-group">
                <input type="radio" name="tax_type" value="hoved" required> Hoved
                <input type="radio" name="tax_type" value="bikort" required> Bikort
            </div>
        </div>
        <div class="form-field">
            <label>Teaching Degree</label>
            <div class="radio-group">
                <input type="radio" name="teaching_degree" value="yes" required> Yes
                <input type="radio" name="teaching_degree" value="no" required> No
            </div>
        </div>
        <div class="form-field">
            <label>Pedagogue Degree</label>
            <div class="radio-group">
                <input type="radio" name="pedagogue_degree" value="yes" required> Yes
                <input type="radio" name="pedagogue_degree" value="no" required> No
            </div>
        </div>
        <div class="form-field full-width">
            <label>Misc</label>
            <textarea name="misc"></textarea>
        </div>
        <div class="form-field full-width">
            <label><input type="checkbox" name="consent" required> I consent</label>
        </div>
        <div class="form-field full-width">
            <button type="submit">Submit</button>
        </div>
    </form>
    <div id="form-message"></div>
    <script>
        jQuery(document).ready(function($) {
            $('#hod-employee-form').on('submit', function(e) {
                e.preventDefault();
                const name = $('input[name="name-1"]').val().trim();
                const email = $('input[name="email-1"]').val().trim();
                const start_date = $('input[name="date-1"]').val().trim();
                const phone = $('input[name="phone"]').val().trim();
                const ice_name = $('input[name="ice_name"]').val().trim();
                const ice_phone = $('input[name="ice_phone"]').val().trim();
                const bank_reg_nr = $('input[name="bank_reg_nr"]').val().trim();
                const bank_account_nr = $('input[name="bank_account_nr"]').val().trim();
                const tax_type = $('input[name="tax_type"]:checked').val();
                const teaching_degree = $('input[name="teaching_degree"]:checked').val();
                const pedagogue_degree = $('input[name="pedagogue_degree"]:checked').val();
                const misc = $('textarea[name="misc"]').val().trim();
                const consent = $('input[name="consent"]').is(':checked');
                
                if (!name || !email || !start_date || !phone || !ice_name || !ice_phone || !bank_reg_nr || !bank_account_nr || !tax_type || !teaching_degree || !pedagogue_degree || !consent) {
                    $('#form-message').removeClass('success').html('<p>Please fill all required fields and consent.</p>');
                    return;
                }
                
                const data = {
                    action: 'submit_hod_employee',
                    nonce: '<?php echo wp_create_nonce( 'hod_nonce' ); ?>',
                    name: name,
                    email: email,
                    start_date: start_date,
                    phone: phone,
                    ice_name: ice_name,
                    ice_phone: ice_phone,
                    bank_reg_nr: bank_reg_nr,
                    bank_account_nr: bank_account_nr,
                    tax_type: tax_type,
                    teaching_degree: teaching_degree,
                    pedagogue_degree: pedagogue_degree,
                    misc: misc,
                    consent: consent ? 1 : 0
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
        'phone' => sanitize_text_field( $_POST['phone'] ?? '' ),
        'ice_name' => sanitize_text_field( $_POST['ice_name'] ?? '' ),
        'ice_phone' => sanitize_text_field( $_POST['ice_phone'] ?? '' ),
        'bank_reg_nr' => sanitize_text_field( $_POST['bank_reg_nr'] ?? '' ),
        'bank_account_nr' => sanitize_text_field( $_POST['bank_account_nr'] ?? '' ),
        'tax_type' => sanitize_text_field( $_POST['tax_type'] ?? '' ),
        'teaching_degree' => sanitize_text_field( $_POST['teaching_degree'] ?? '' ),
        'pedagogue_degree' => sanitize_text_field( $_POST['pedagogue_degree'] ?? '' ),
        'misc' => sanitize_textarea_field( $_POST['misc'] ?? '' ),
        'consent' => intval( $_POST['consent'] ?? 0 ),
        'keys_30' => intval( $_POST['keys_30'] ?? 0 ),
        'keys_0' => intval( $_POST['keys_0'] ?? 0 ),
        'keys_music' => intval( $_POST['keys_music'] ?? 0 ),
        'keys_gym' => intval( $_POST['keys_gym'] ?? 0 ),
        'flowers' => sanitize_text_field( $_POST['flowers'] ?? 'no' ),
        'flower_delivery' => sanitize_text_field( $_POST['flower_delivery'] ?? 'home' ),
        'laptop' => sanitize_text_field( $_POST['laptop'] ?? 'no' ),
        'temp_employment' => sanitize_text_field( $_POST['temp_employment'] ?? 'no' ),
        'temp_reason' => sanitize_textarea_field( $_POST['temp_reason'] ?? '' ),
        'employment_level' => sanitize_text_field( $_POST['employment_level'] ?? '0' ),
        'status' => 'pending'
    );
    
    // Validate data
    if ( empty( $data['name'] ) || empty( $data['email'] ) || empty( $data['start_date'] ) || empty( $data['phone'] ) || empty( $data['ice_name'] ) || empty( $data['ice_phone'] ) || empty( $data['bank_reg_nr'] ) || empty( $data['bank_account_nr'] ) || empty( $data['tax_type'] ) || empty( $data['teaching_degree'] ) || empty( $data['pedagogue_degree'] ) || !$data['consent'] ) {
        error_log( 'HOD Submit Validation Failed: ' . print_r( $data, true ) );
        wp_send_json_error( 'All required fields are required and consent must be given' );
    }
    
    // Log incoming data
    error_log( 'HOD Submit Data: ' . print_r( $data, true ) );
    
    $result = $wpdb->insert(
        $table_name,
        $data,
        array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
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
        'phone' => sanitize_text_field( $updates['phone'] ?? '' ),
        'ice_name' => sanitize_text_field( $updates['ice_name'] ?? '' ),
        'ice_phone' => sanitize_text_field( $updates['ice_phone'] ?? '' ),
        'bank_reg_nr' => sanitize_text_field( $updates['bank_reg_nr'] ?? '' ),
        'bank_account_nr' => sanitize_text_field( $updates['bank_account_nr'] ?? '' ),
        'tax_type' => sanitize_text_field( $updates['tax_type'] ?? '' ),
        'teaching_degree' => sanitize_text_field( $updates['teaching_degree'] ?? '' ),
        'pedagogue_degree' => sanitize_text_field( $updates['pedagogue_degree'] ?? '' ),
        'misc' => sanitize_textarea_field( $updates['misc'] ?? '' ),
        'consent' => intval( $updates['consent'] ?? 0 ),
        'keys_30' => intval( $updates['keys_30'] ?? 0 ),
        'keys_0' => intval( $updates['keys_0'] ?? 0 ),
        'keys_music' => intval( $updates['keys_music'] ?? 0 ),
        'keys_gym' => intval( $updates['keys_gym'] ?? 0 ),
        'flowers' => sanitize_text_field( $updates['flowers'] ?? 'no' ),
        'flower_delivery' => sanitize_text_field( $updates['flower_delivery'] ?? 'home' ),
        'laptop' => sanitize_text_field( $updates['laptop'] ?? 'no' ),
        'temp_employment' => sanitize_text_field( $updates['temp_employment'] ?? 'no' ),
        'temp_reason' => sanitize_textarea_field( $updates['temp_reason'] ?? '' ),
        'employment_level' => sanitize_text_field( $updates['employment_level'] ?? '0' ),
        'status' => 'updated'
    );
    
    if ( empty( $data['name'] ) || empty( $data['email'] ) || empty( $data['start_date'] ) || empty( $data['phone'] ) || empty( $data['ice_name'] ) || empty( $data['ice_phone'] ) || empty( $data['bank_reg_nr'] ) || empty( $data['bank_account_nr'] ) || empty( $data['tax_type'] ) || empty( $data['teaching_degree'] ) || empty( $data['pedagogue_degree'] ) || !$data['consent'] ) {
        error_log( 'HOD Update Validation Failed: ' . print_r( $data, true ) );
        wp_send_json_error( 'All required fields are required and consent must be given' );
    }

    $result = $wpdb->update(
        $table_name,
        $data,
        array( 'id' => $entry_id ),
        array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ),
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
    $message .= "Phone: " . $entry['phone'] . "\n";
    $message .= "ICE Name: " . $entry['ice_name'] . "\n";
    $message .= "ICE Phone: " . $entry['ice_phone'] . "\n";
    $message .= "Bank Reg Nr: " . $entry['bank_reg_nr'] . "\n";
    $message .= "Bank Account Nr: " . $entry['bank_account_nr'] . "\n";
    $message .= "Tax Type: " . $entry['tax_type'] . "\n";
    $message .= "Teaching Degree: " . ($entry['teaching_degree'] == 'yes' ? 'Yes' : 'No') . "\n";
    $message .= "Pedagogue Degree: " . ($entry['pedagogue_degree'] == 'yes' ? 'Yes' : 'No') . "\n";
    $message .= "Misc: " . $entry['misc'] . "\n";
    $message .= "Consent: " . ($entry['consent'] ? 'Yes' : 'No') . "\n";
    $message .= "Keys 30: " . ($entry['keys_30'] ? 'Yes' : 'No') . "\n";
    $message .= "Keys 0: " . ($entry['keys_0'] ? 'Yes' : 'No') . "\n";
    $message .= "Keys Music: " . ($entry['keys_music'] ? 'Yes' : 'No') . "\n";
    $message .= "Keys Gym: " . ($entry['keys_gym'] ? 'Yes' : 'No') . "\n";
    $message .= "Flowers: " . ($entry['flowers'] == 'yes' ? 'Yes' : 'No') . "\n";
    $message .= "Flower Delivery: " . $entry['flower_delivery'] . "\n";
    $message .= "Laptop: " . ($entry['laptop'] == 'yes' ? 'Yes' : 'No') . "\n";
    $message .= "Temporary Employment: " . ($entry['temp_employment'] == 'yes' ? 'Yes' : 'No') . "\n";
    $message .= "Temp Employment Reason: " . $entry['temp_reason'] . "\n";
    $message .= "Employment Level: " . $entry['employment_level'] . "%\n";

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