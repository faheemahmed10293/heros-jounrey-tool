jQuery(document).ready(function($) {
    $('#storyForm').on('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission
        
        // Show the default message and start animation
        $('#generatedStory').html('<p style="color:#fffefe">Hold tight! Your story is brewing<span class="dots"></span></p>');
        $('#generatedStory').show(); // Show the generatedStory div
     //   $('#copyButton').hide();  Hide the copy button initially

        var formData = {
            action: 'generate_story',
            company_name: $('#company_name').val(),
            services: $('#services').val(),
            industry: $('#industry').val(),
            target_audience: $('#target_audience').val(),
            competitor: $('#competitor').val(),
        };
        
        $.post(myAjax.ajaxurl, formData, function(response) {
            $('#generatedStory').html(response); // Display the story in the div
         //   $('#copyButton').show();  Show the copy button once content is loaded
        });
    });

/*     $('#copyButton').on('click', function() {
        var textToCopy = $('#generatedStory').text();
        var tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val(textToCopy).select();
        document.execCommand('copy');
        tempInput.remove();
        var $button = $(this);
        $button.text('Copied!');
        setTimeout(function() {
            $button.text('Copy Story');
        }, 3000); // 3 seconds delay before the text reverts back
    }); */
});
