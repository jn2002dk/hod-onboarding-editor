# HoD Onboarding Editor

A WordPress plugin that provides a frontend dashboard for Heads of Departments (HoDs) to manage and edit onboarding entries, along with a public employee submission form. This plugin streamlines the onboarding process by allowing HoDs to review, update, and send entries to Admin/IT teams.

## Features

- **Employee Submission Form**: Public-facing form for employees to submit onboarding details (name, email, start date).
- **HoD Dashboard**: Secure dashboard for authorized users (e.g., administrators) to view, edit, and manage pending entries.
- **AJAX-Powered Interactions**: Real-time updates without page reloads for editing and sending entries.
- **Custom Database Table**: Stores onboarding data securely in a dedicated WordPress table.
- **Gutenberg Block Support**: Register blocks for easy integration into pages/posts.
- **Email Notifications**: Automatically sends entry details to Admin/IT upon approval.
- **Role-Based Access**: Restricted to users with the specified role (default: administrator).
- **Responsive Design**: Styled forms and tables for better usability.

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- jQuery (included with WordPress)
- MySQL (for database storage)

## Installation

1. Download the plugin files and upload them to your WordPress plugins directory (`wp-content/plugins/`).
2. Activate the plugin through the WordPress admin dashboard under **Plugins > Installed Plugins**.
3. The plugin will automatically create the necessary database table on activation.

### Manual Installation

- Clone or download the repository.
- Place the `hod-onboarding-editor` folder in `wp-content/plugins/`.
- Activate the plugin as described above.

## Usage

### Shortcodes

- **Employee Form**: Use `[employee_onboarding_form]` on any page to display the public submission form.
- **HoD Dashboard**: Use `[hod_onboarding_dashboard]` on a page accessible only to logged-in HoDs (e.g., administrators).

### Gutenberg Blocks

The plugin registers two blocks for use in the block editor:

- **Employee Onboarding Form**: Adds the submission form to a page/post.
- **HOD Onboarding Dashboard**: Adds the management dashboard (visible only to authorized users).

### Configuration

- **Allowed Role**: Modify `HOD_ALLOWED_ROLE` in `hod-onboarding-editor.php` to change the required user role (default: `administrator`).
- **Email Recipients**: Update the email addresses in the `send_hod_entry` function (currently set to `admin@yourdomain.com` and `it@yourdomain.com`).

### Workflow

1. Employees submit details via the form.
2. HoDs log in and view pending entries in the dashboard.
3. HoDs can edit entries or mark them as sent, triggering an email to Admin/IT.
4. Sent entries are hidden from the dashboard.

## Files Overview

- `hod-onboarding-editor.php`: Main plugin file with hooks, shortcodes, and AJAX handlers.
- `hod-dashboard.js`: JavaScript for dashboard interactions (edit, send).
- `hod-blocks.js`: Registers Gutenberg blocks.
- `hod-styles.css`: Styles for forms, tables, and modals.

## Changelog

### Version 1.3
- Added Gutenberg block support.
- Improved error logging and validation.
- Enhanced modal styling for editing.

### Version 1.2
- Initial public release with dashboard and form functionality.

## Support

For issues or feature requests, please open an issue on the [GitHub repository](https://github.com/jn2002dk/hod-onboarding-editor).

## License

This plugin is licensed under the GPL v2 or later.

---

**Note**: Ensure your WordPress site has proper email configuration for notifications to work. Test in a staging environment before production use.