<?php
/*
Plugin Name: Heros Journey
Plugin URI: https://yourtabula.com/
Description: Create Powerful Sales Slides in Seconds with this Free AI Tool.
Version: 1.0
Author: Faheem Ahmed
*/
require_once plugin_dir_path(__FILE__) . 'config.php';
// Shortcode to display the form
function display_story_form() {
    ob_start();
    ?>
    <form id="storyForm" method="post" action="" aria-labelledby="storyFormHeading">
	 <h2 id="storyFormHeading" class="mt-4 text-white">Generate Your Hero's Journey Story</h2>
        <label class="mt-4 text-white" for="company_name">Add your company name</label>
        <input class="mt-2" type="text" id="company_name" name="company_name" required>
		<label class="mt-4 text-white" for="services">Add your product or service 
        <span class="tooltip-icon"><i class="fas fa-info-circle"></i>
            <span class="tooltip-text">Only add the product/service you want to pitch or sell on the slides. For instance, if you're an agency pitching social media content but also offer web development and paid ads, only add social media content. 
			If you are pitching multiple products or services, separate them using commas. i.e. "social media content, web development and paid ads"</span>
        </span>
		</label>
        <input class="mt-2" type="text" id="services" name="services" required>

			<label class="mt-4 text-white" for="target_audience">Add your target audience 
        <span class="tooltip-icon"><i class="fas fa-info-circle"></i>
            <span class="tooltip-text">Only add the target audience you are pitching to, not all the target audiences you serve. If you are pitching to multiple, 
			then add multiple audiences separated by commas. i.e. “event managers, marketing managers and trade show managers"</span>
        </span>
		</label>
        <input class="mt-2" type="text" id="target_audience" name="target_audience" required>
        
			<label class="mt-4 text-white" for="industry">Add your target audience’s industry 
        <span class="tooltip-icon"><i class="fas fa-info-circle"></i>
            <span class="tooltip-text">Only add the industry your potential customer is in, not all the industries you serve. 
			If you are pitching for multiple, then add multiple separated by commas. i.e. “finance, manufacturing and sales”</span>
		</span>
		</label>
        <input class="mt-2" type="text" id="industry" name="industry" required>
   
			<label class="mt-4 text-white" for="competitor">Add your competitive advantages/unique selling points 
        <span class="tooltip-icon"><i class="fas fa-info-circle"></i>
            <span class="tooltip-text">Add everything that makes your product or service unique or better than your competition. Feel free to add context if necessary. i.e. "We install our medical equipment faster than anyone in the industry because we have the most warehouses in the country and we have been in this business for over 30 years.
			We also have a lifetime satisfaction guarantee that no other competitor dares to do.”</span>
		</span>
		</label>
        <input class="mt-2" type="text" id="competitor" name="competitor" required>
		
		<!-- <label class="mt-4 text-white" for="email">Be the first to see our new free AI tools, Enter your email (optional)</label>
        <input class="mt-2" type="email" id="email" name="email"> -->
		
        <input class="primary-btn3 mt-4" type="submit" value="Generate Story" aria-label="Generate Story">
    </form>
    <div id="generatedStory" aria-live="polite"></div> <!-- Added aria-live -->
	<!-- <button id="copyButton" style="display:none;" aria-label="Copy the generated story">Copy Story</button> -->
    <?php
    return ob_get_clean();
}
add_shortcode('story_form', 'display_story_form');

// Send request to ChatGPT API and Get response //
function generate_short_story($company_name, $services, $industry, $target_audience, $competitor) {
    $api_key = OPENAI_API_KEY;
    $url = 'https://api.openai.com/v1/chat/completions';

    // Prepare the data for the API request
    $data = [
        'model' => 'gpt-4',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a marketing and sales expert who knows that the best way to sell is to focus on the customer pain points and communicate using benefits and easy-to-understand language.'
            ],
            [
                'role' => 'user',
                'content' => sprintf(
                    "Create a slide presentation for my company named %s, following the Hero's Journey framework, which offers %s to %s in the %s industry. The things that make %s better than competitors are %s. Each slide must include exactly the following 5 elements: 1. Slide Number: Slide number should be displayed as 'Slide #X'. 2. Title: Clear and compelling title of the slide. 3. Text: Main paragraph of the slide, explaining the key point. 4. Bullet Points: Between 3 to 6 concise bullet points. 5. Image: A recommended image description for the slide. **Important Requirements:** - Do **not** include any other elements in the output. - Each slide **must** have these 5 elements and **nothing more**. Using this format, create a slide presentation that persuades business owners to buy from us and overcomes possible objections. Remember to focus on benefits without being repetitive in your language, to add context and examples relevant to their industry and to use the Hero’s Journey framework to structure the slides without explicitly mentioning the framework.",
                    $company_name, $services, $target_audience, $industry, $company_name, $competitor
                )
            ]
        ],
        'max_tokens' => 2500,
        'temperature' => 0.7,
    ];
    // Send request to OpenAI API
    $response = wp_remote_post($url, [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key
        ],
        'body' => json_encode($data),
        'timeout' => 60
    ]);
    // Handle response errors
    if (is_wp_error($response)) {
        return 'An error occurred while generating the story: ' . $response->get_error_message();
    }
    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);
    // Check for API errors
    if (isset($result['error'])) {
        return 'An error occurred: ' . $result['error']['message'];
    }
    // Format and return the generated story
    return formatSlides($result['choices'][0]['message']['content'] ?? 'An error occurred while generating the story.');
}

function formatSlides($content) {
    // Split the content into individual slides
    $slides = explode("#", $content);
    $htmlOutput = '';

    foreach ($slides as $slide) {
        if (trim($slide) === '') continue; // Skip empty slides

        // Initialize placeholders for different sections
        $slideNumber = '';
        $slideTitle = '';
        $slideText = '';
        $slideBullets = [];
        $slideImage = '';

        // Use regex to extract various parts of the slide
        if (preg_match('/^(\d+)/', $slide, $numberMatches)) {
            $slideNumber = 'Slide #' . trim($numberMatches[1]);
        }
        if (preg_match('/Title:\s*(.*?)(?:\n|$)/', $slide, $titleMatches)) {
            $slideTitle = trim($titleMatches[1]);
        }
        if (preg_match('/Text:\s*(.*?)(?:\n|$)/', $slide, $textMatches)) {
            $slideText = trim($textMatches[1]);
        }
        
        // Extract bullet points
        if (preg_match_all('/(?:•|-|\*)\s*(.*?)(?:\n|$)/', $slide, $bulletMatches)) {
            $slideBullets = array_map('trim', $bulletMatches[1]);
        }

        // Extract image description and text after hyphen
        if (preg_match('/Image:\s*(.*?)(?:\n|$)/', $slide, $imageMatches)) {
            $slideImage = trim($imageMatches[1]);
            // Extract text after hyphen from the image description
            $imageText = '';
            if (strpos($slideImage, '-') !== false) {
                $imageText = trim(substr($slideImage, strpos($slideImage, '-') + 1));
            }
        }

        // Remove any bullet points that are also part of the text, image text, or title
		foreach ($slideBullets as $key => $bullet) {
		// Check if the bullet point exists in the slide text, image text, or title and remove it if it does
		if (stripos($slideText, $bullet) !== false || stripos($imageText, $bullet) !== false || stripos($slideTitle, $bullet) !== false) {
        unset($slideBullets[$key]);
		}
	}


        $slideBullets = array_values($slideBullets); // Re-index the array

        // Skip slides without content
        if (empty($slideNumber) && empty($slideTitle) && empty($slideText) && empty($slideBullets) && empty($slideImage)) {
            continue;
        }

        // Build HTML for the slide
        $htmlOutput .= "<div class='slide'>";
        $htmlOutput .= !empty($slideNumber) ? "<h2>$slideNumber</h2>" : '';
        $htmlOutput .= !empty($slideTitle) ? "<h3>$slideTitle</h3>" : '';
        $htmlOutput .= !empty($slideText) ? "<p><strong>Text:</strong> $slideText</p>" : '';
        
        if (!empty($slideBullets)) {
            $htmlOutput .= '<p><strong>Bullet Points:</strong></p><ul>';
            foreach ($slideBullets as $bullet) {
                $htmlOutput .= '<li>' . esc_html($bullet) . '</li>'; // Escaping for safety
            }
            $htmlOutput .= '</ul>';
        }
        
        $htmlOutput .= !empty($slideImage) ? "<p><strong>Image:</strong> $slideImage</p>" : '';
        $htmlOutput .= "</div>";
    }

    return $htmlOutput;
}

function ajax_generate_story() {
    if (isset($_POST['company_name'], $_POST['services'], $_POST['industry'], $_POST['target_audience'], $_POST['competitor'])) {
        $company_name = sanitize_text_field($_POST['company_name']);
        $services = sanitize_text_field($_POST['services']);
        $industry = sanitize_text_field($_POST['industry']);
        $target_audience = sanitize_text_field($_POST['target_audience']);
        $competitor = sanitize_text_field($_POST['competitor']);
        // Generate the story
        echo generate_short_story($company_name, $services, $industry, $target_audience, $competitor);
    } else {
        echo 'Missing required fields.';
    }
    wp_die();
}
// Hook for AJAX requests
add_action('wp_ajax_generate_story', 'ajax_generate_story');
add_action('wp_ajax_nopriv_generate_story', 'ajax_generate_story');

function my_custom_plugin_scripts() {
    // Register and enqueue custom.js
    wp_enqueue_script(
        'my-custom-script', 
        plugins_url('inc/custom.js', __FILE__), 
        array('jquery'), 
        null, 
        true
    );
    // Pass admin-ajax.php URL to the script
    wp_localize_script('my-custom-script', 'myAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));

    // Enqueue custom.css
    wp_enqueue_style(
        'my-custom-style', 
        plugins_url('inc/custom.css', __FILE__)
    );
}
add_action('wp_enqueue_scripts', 'my_custom_plugin_scripts');
