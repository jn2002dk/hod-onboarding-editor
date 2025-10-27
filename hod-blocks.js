( function( blocks, element, editor ) {
    var registerBlockType = blocks.registerBlockType;
    var createElement = element.createElement;
    var __ = wp.i18n.__;

    registerBlockType( 'hod/employee-form', {
        title: __( 'Employee Onboarding Form', 'hod-onboarding' ),
        icon: 'feedback',
        category: 'widgets',
        edit: function() {
            return createElement( 'div', { className: 'hod-block-preview' }, __( 'Employee Onboarding Form', 'hod-onboarding' ) );
        },
        save: function() {
            return null; // Dynamic block
        },
    } );

    registerBlockType( 'hod/dashboard', {
        title: __( 'HOD Onboarding Dashboard', 'hod-onboarding' ),
        icon: 'admin-users',
        category: 'widgets',
        edit: function() {
            return createElement( 'div', { className: 'hod-block-preview' }, __( 'HOD Onboarding Dashboard', 'hod-onboarding' ) );
        },
        save: function() {
            return null; // Dynamic block
        },
    } );
} )(
    window.wp.blocks,
    window.wp.element,
    window.wp.editor
);