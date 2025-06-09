/**
 * Admin Settings Module
 * 
 * Handles settings page functionality including:
 * - Password visibility toggling
 * - Form validation
 * - Secure input handling
 * 
 * Features:
 * - Toggle password visibility with eye icon
 * - Maintains security while improving UX
 * - Bootstrap Icons integration
 * 
 * Dependencies:
 * - jQuery
 * - Bootstrap Icons
 */
$(document).ready(function() {
    // Toggle password visibility
    $('.toggle-password').click(function() {
        const input = $(this).parent().find('input');
        const icon = $(this).find('i');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });
});
