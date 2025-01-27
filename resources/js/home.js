$(document).ready(function () {
    const shortenBtn = $("#shorten-btn");
    const urlInput = $("#url-input");
    const resultBlock = $("#result-block");
    const shortenedLink = $("#shortened-link");
    const copyBtn = $(".copy-btn");
    const csrfToken = $('input[name="_token"]').val();

    shortenBtn.on("click", function (e) {
        e.preventDefault();

        resetResultBlock();

        const url = urlInput.val().trim();
        const route = urlInput.data("route");

        if (!isValidUrl(url)) {
            showError("Please enter a valid URL (e.g., https://example.com).");
            return;
        }

        toggleLoadingState(shortenBtn, true);

        $.ajax({
            headers: { "X-CSRF-TOKEN": csrfToken },
            url: route,
            type: "POST",
            data: { url },
            success: handleSuccessResponse,
            error: handleError,
            complete: () => toggleLoadingState(shortenBtn, false),
        });
    });

    copyBtn.on("click", function () {
        const $button = $(this);
        const originalText = $button.text();

        copyToClipboard(shortenedLink.text())
            .then(() => updateButtonState($button, true, originalText))
            .catch((err) => {
                console.error("Failed to copy text: ", err);
                updateButtonState($button, false, originalText);
            });
    });

    /**
     * Validates a URL.
     * @param {string} url The URL to validate.
     * @returns {boolean} True if valid, false otherwise.
     */
    function isValidUrl(url) {
        const urlPattern = /^https?:\/\/.+$/;
        return urlPattern.test(url);
    }

    /**
     * Resets the result block to its default state.
     */
    function resetResultBlock() {
        resultBlock.hide();
        shortenedLink.text("");
        shortenedLink.removeAttr("href");
        copyBtn.hide();
    }

    /**
     * Displays an error message in the result block.
     * @param {string} message The error message to display.
     */
    function showError(message) {
        resultBlock.show();
        shortenedLink.html(message);
    }

    /**
     * Handles the success response from the server.
     * @param {Object} response The server response.
     */
    function handleSuccessResponse(response) {
        if (response?.short_name && response?.domain?.name) {
            const url = `https://${response.domain.name}/${response.short_name}`;
            shortenedLink.text(url);
            shortenedLink.attr("href", url);
            copyBtn.show();
            resultBlock.show();
        } else {
            showError("Error processing the request. Please try again.");
        }
    }

    /**
     * Handles errors during the AJAX request.
     * @param {Object} xhr The XMLHttpRequest object.
     * @param {string} status The status of the request.
     * @param {string} error The error message.
     */
    function handleError(xhr, status, error) {
        console.error("AJAX Error:", error);
        showError("Failed to shorten the URL. Please try again.");
    }

    /**
     * Toggles the loading state of a button.
     * @param {object} $button The button element.
     * @param {boolean} isLoading Whether to show loading state.
     */
    function toggleLoadingState($button, isLoading) {
        if (isLoading) {
            $button.prop("disabled", false).html('<div class="spinner-border text-light" role="status"><span class="visually-hidden">Shortening...</span></div>');
        } else {
            $button.prop("disabled", false).html("Shorten");
        }
    }

    /**
     * Copies text to the clipboard.
     * @param {string} text The text to copy.
     * @returns {Promise} Resolves when the text is copied.
     */
    function copyToClipboard(text) {
        return navigator.clipboard.writeText(text);
    }

    /**
     * Updates the copy button's state.
     * @param {object} $button The button element.
     * @param {boolean} isCopied Whether the link was successfully copied.
     * @param {string} [originalText] The original button text.
     */
    function updateButtonState($button, isCopied, originalText = "") {
        if (isCopied) {
            $button.html('<i class="bi bi-check"></i> Copied').prop("disabled", true);
            setTimeout(() => $button.html(originalText).prop("disabled", false), 5000);
        } else {
            $button.html(originalText).prop("disabled", false);
        }
    }
});
