/**
 * NaniMade Suite Admin Scripts
 */

jQuery(document).ready(function($) {
    
    // Initialize color pickers
    if ($.fn.wpColorPicker) {
        $('.nanimade-color-picker').wpColorPicker({
            change: function(event, ui) {
                updatePreview();
            }
        });
    }
    
    // Live preview updates
    function updatePreview() {
        const primaryColor = $('#primary_color').val();
        const secondaryColor = $('#secondary_color').val();
        const accentColor = $('#accent_color').val();
        
        // Update CSS variables for preview
        if (primaryColor) {
            document.documentElement.style.setProperty('--nanimade-primary', primaryColor);
        }
        if (secondaryColor) {
            document.documentElement.style.setProperty('--nanimade-secondary', secondaryColor);
        }
        if (accentColor) {
            document.documentElement.style.setProperty('--nanimade-accent', accentColor);
        }
        
        // Update mobile preview
        updateMobilePreview();
    }
    
    function updateMobilePreview() {
        const style = $('#design_style').val();
        const preview = $('.nanimade-preview-menu');
        
        if (preview.length) {
            preview.removeClass('style-pickle-modern style-pickle-minimal style-pickle-classic style-pickle-gradient');
            preview.addClass('style-' + style);
        }
    }
    
    // Settings change handlers
    $('input, select').on('change', updatePreview);
    
    // Preview button
    $('#nanimadePreview').on('click', function(e) {
        e.preventDefault();
        // Open preview in new tab
        window.open('/?nanimade_preview=1', '_blank');
    });
    
    // Reset button
    $('#nanimadeReset').on('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to reset all settings to defaults?')) {
            // Reset form to defaults
            $('input[type="checkbox"]').prop('checked', true);
            $('#design_style').val('pickle-modern');
            
            if ($('#primary_color').length) {
                $('#primary_color').val('#ff6b35').trigger('change');
            }
            if ($('#secondary_color').length) {
                $('#secondary_color').val('#28a745').trigger('change');
            }
            if ($('#accent_color').length) {
                $('#accent_color').val('#ffc107').trigger('change');
            }
            
            updatePreview();
        }
    });
    
    // Initialize preview
    updatePreview();
    
    // Form validation
    $('form').on('submit', function(e) {
        // Basic validation
        const requiredFields = $(this).find('[required]');
        let isValid = true;
        
        requiredFields.each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('error');
            } else {
                $(this).removeClass('error');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });
});