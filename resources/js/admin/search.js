/**
 * Admin Search Module
 * 
 * Handles global search functionality across the admin panel including:
 * - Real-time search with debounce (300ms delay)
 * - Results for links, domains and users
 * - Keyboard navigation (Enter key support)
 * - Click-outside to close results
 * - Mobile sidebar toggle functionality
 * 
 * Features:
 * - Dynamic badge updates showing result counts
 * - Category-specific search redirects
 * - Error handling for failed requests
 * - Responsive design for all screen sizes
 * 
 * Dependencies:
 * - jQuery
 */
$(document).ready(function () {
    let searchTimer;
    const searchInput = $('#global-search');
    const searchResults = $('#search-results');
    const searchUrl = $('#global-search').data('search-url');
    let currentQuery = '';

    searchInput.on('input', function () {
        clearTimeout(searchTimer);
        const query = $(this).val().trim();
        currentQuery = query;

        if (query.length < 2) {
            searchResults.hide();
            return;
        }

        searchTimer = setTimeout(() => {
            $.ajax({
                url: searchUrl,
                method: 'GET',
                data: { query: query },
                beforeSend: function () {
                    searchResults.show();
                },
                success: function (response) {
                    console.log('Search response:', response);

                    // Update links count
                    const linksCount = response.links || 0;
                    const linksBadge = $('#search-results .search-category:nth-child(1) .badge');
                    linksBadge.text(linksCount);
                    linksBadge.toggleClass('bg-success', linksCount > 0);

                    // Update domains count
                    const domainsCount = response.domains || 0;
                    const domainsBadge = $('#search-results .search-category:nth-child(2) .badge');
                    domainsBadge.text(domainsCount);
                    domainsBadge.toggleClass('bg-success', domainsCount > 0);

                    // Update users count
                    const usersCount = response.users || 0;
                    const usersBadge = $('#search-results .search-category:nth-child(3) .badge');
                    usersBadge.text(usersCount);
                    usersBadge.toggleClass('bg-success', usersCount > 0);

                    // Show results container
                    searchResults.addClass('show');
                },
                error: function () {
                    searchResults.html('<div class="search-error">Error loading results</div>');
                }
            });
        }, 300);
    });

    $('.category-link').on('click', function (e) {
        e.preventDefault();

        const baseUrl = $(this).attr('href');
        const urlWithQuery = currentQuery
            ? `${baseUrl}?query=${encodeURIComponent(currentQuery)}`
            : baseUrl;

        window.location.href = urlWithQuery;
    });

    searchInput.on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            const firstCategoryUrl = $('.category-link').first().attr('href');
            const urlWithQuery = currentQuery
                ? `${firstCategoryUrl}?q=${encodeURIComponent(currentQuery)}`
                : firstCategoryUrl;

            window.location.href = urlWithQuery;
        }
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('.search-bar').length) {
            searchResults.hide();
        }
    });

    $('#burgerMenu').click(function(e) {
        e.stopPropagation();
        $('.sidebar').toggleClass('active');
    });
    
    $('#sidebarOverlay').click(function() {
        $('.sidebar').removeClass('active');
    });
    
    $('.sidebar .nav-item').click(function() {
        if ($(window).width() <= 992) {
            $('.sidebar').removeClass('active');
        }
    });

    $(document).click(function(e) {
        if (!$(e.target).closest('.sidebar, #burgerMenu').length && $('.sidebar').hasClass('active')) {
            $('.sidebar').removeClass('active');
        }
    });
});
