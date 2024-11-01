// js/vgf-ajax.js
jQuery(document).ready(function($) {
    // Tab click handler
    $('.vgf-tab').on('click', function() {
        var categoryId = $(this).data('category-id');
        
        // Show corresponding accordion
        $('.vgf-accordion').removeClass('active').hide();
        $('.vgf-accordion[data-category-id="' + categoryId + '"]').addClass('active').show();
        
        // Activate the clicked tab
        $('.vgf-tab').removeClass('active');
        $(this).addClass('active');
    });

    // Automatically show the first accordion on page load
    $('.vgf-accordion').first().addClass('active').show();

    // Subcategory header click handler for collapsing/expanding
    $('.vgf-subcategory-header').on('click', function() {
        var $children = $(this).next('.vgf-children');
        $children.slideToggle(200); // Set duration to 200 milliseconds
        $(this).find('.vgf-arrow').text($children.is(':visible') ? '▲' : '▼');
    });

    // Filter handling for desktop
    $('.vgf-filter').on('change', function() {
        if ($(window).width() > 768) {
            vgf_filter_posts();
        }
    });

    // Toggle filters visibility
    $('.vgf-filter-icon').on('click', function() {
        $('.vgf-filters').toggle();
    });

    // Filter handling for mobile
    $('#vgf-mobile-apply').on('click', function() {
        vgf_filter_posts(); // Call filter function
        $('.vgf-filters').hide(); // Hide filters after applying
    });

    $('#vgf-mobile-cancel').on('click', function() {
        $('.vgf-filters input:checkbox').prop('checked', false); // Reset checkboxes
        vgf_filter_posts(); // Apply the reset filter
        $('.vgf-filters').hide(); // Hide filters
    });

    function vgf_filter_posts() {
        var selected_categories = [];
        $('.vgf-filter:checked').each(function() {
            selected_categories.push($(this).val());
        });

        $.ajax({
            type: 'POST',
            url: vgf_ajax_obj.ajaxurl,
            data: {
                action: 'vgf_filter_posts',
                categories: selected_categories
            },
            success: function(data) {
                $('#vgf-posts').html(data); // Update posts section
            }
        });
    }
});
