<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );
         
if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {
        wp_enqueue_style( 'chld_thm_cfg_separate', trailingslashit( get_stylesheet_directory_uri() ) . 'ctc-style.css', array(  ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 10 );

// END ENQUEUE PARENT ACTION

// functions.php
function vgf_post_filter_shortcode() {
    ob_start(); ?>

    <div class="vgf-container-title">
        <div class="vgf-left">
            <h2 class="vgf-filter-heading">Browse Categories</h2>
        </div>
        <div class="vgf-right">
            <div class="vgf-filter-toggle">
                <button class="vgf-filter-icon">
                    <i class="fa fa-filter" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="vgf-container">
        <div class="vgf-sidebar">
            <div class="vgf-filters">
                <?php echo vgf_render_filters(); ?>
            </div>
        </div>
        
        <div class="vgf-post-container">
            <div class="vgf-posts" id="vgf-posts">
                <?php echo vgf_get_posts(); // This should display all posts by default ?>
            </div>
        </div>
    </div>

    <?php return ob_get_clean();
}

add_shortcode('vgf_post_filter', 'vgf_post_filter_shortcode');


function vgf_render_filters() {
    $parent_categories = get_categories(array('parent' => 0, 'exclude' => 1));
    $output = '<div class="vgf-filters">'; // Wrap all in a filters container
    $output .= '<div class="vgf-tabs">';

    // Create tabs for parent categories
    foreach ($parent_categories as $index => $category) {
        $active_class = ($index === 0) ? 'active' : '';
        $output .= '<div class="vgf-tab ' . $active_class . '" data-category-id="' . $category->term_id . '">' . esc_html($category->name) . '</div>';
    }
    $output .= '</div>'; // Close tabs

    // Create accordions for sub-parent categories
    foreach ($parent_categories as $index => $category) {
        $subcategories = get_categories(array('parent' => $category->term_id, 'exclude' => 1));
        $accordion_class = ($index === 0) ? 'active' : '';
    
        // Start the accordion
        $output .= '<div class="vgf-accordion ' . $accordion_class . '" data-category-id="' . $category->term_id . '">';
    
        foreach ($subcategories as $subcat) {
            $children = get_categories(array('parent' => $subcat->term_id, 'exclude' => 1));
    
            // Only display subcategory if it has children
            if ($children) {
                $output .= '<div class="vgf-subcategory">';
                $output .= '<h4 class="vgf-subcategory-header">' . esc_html($subcat->name) . '<i class="fa-solid fa-chevron-down"></i></h4>';
                $output .= '<div class="vgf-children">';
    
                foreach ($children as $child) {
                    $output .= '<label><input type="checkbox" class="vgf-filter" value="' . $child->term_id . '"> ' . esc_html($child->name) . '</label>';
                }
                $output .= '</div></div>'; // Close subcategory and its children
            }
        }
    
        $output .= '</div>'; // Close accordion
    }
    

    // Add Apply and Cancel buttons for mobile only
    $output .= '<div class="vgf-filters-buttons mobile-only">'; // Class to show only on mobile
    $output .= '<button id="vgf-mobile-apply">Apply</button>';
    $output .= '<button id="vgf-mobile-cancel" class="cancel">Cancel</button>';
    $output .= '</div>'; // Close buttons

    $output .= '</div>'; // Close filters container
    return $output;
}


function vgf_get_posts($selected_categories = []) {
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 8,
        'tax_query' => array(
            array(
                'taxonomy' => 'category',
                'field'    => 'term_id',
                'terms'    => $selected_categories,
                'operator' => 'IN',
            ),
        ),
    );

    if (empty($selected_categories)) {
        unset($args['tax_query']);
    }

    $query = new WP_Query($args);
    $output = '';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $thumbnail = get_the_post_thumbnail(get_the_ID(), 'full');
            $title = get_the_title();
            $description = wp_trim_words(get_the_excerpt(), 15, '...');
            $author = get_the_author();
            $date = get_the_date();

            // Get the categories for the current post
            $categories = get_the_category();
            if ($categories) {
                $main_category = esc_html($categories[0]->name); // Get the main category
                $additional_count = count($categories) - 1; // Count additional categories
                $additional_text = $additional_count > 0 ? ' + ' . $additional_count . ' more' : '';
                $category_badge = '<span class="vgf-category-badge">' . $main_category . $additional_text . '</span>';
            } else {
                $category_badge = '';
            }

            $output .= '<div class="vgf-post-card" onclick="location.href=\'' . get_permalink() . '\'">';
            $output .= '<div class="vgf-post-image">' . $thumbnail . $category_badge . '</div>';
            $output .= '<div class="vgf-post-content">';
            $output .= '<h2 class="vgf-post-title">' . esc_html($title) . '</h2>';
            $output .= '<p class="vgf-post-description">' . esc_html($description) . '</p>';
            $output .= '<div class="vgf-post-meta"><span class="vgf-author"><i class="fa-solid fa-user"></i> ' . esc_html($author) . '</span> <span class="vgf-date">' . esc_html($date) . '</span></div>';
            $output .= '</div></div>'; // Close card
        }
        wp_reset_postdata();
    } else {
        $output .= '<p>No posts found.</p>';
    }

    return $output;
}

add_action('wp_ajax_vgf_filter_posts', 'vgf_filter_posts');
add_action('wp_ajax_nopriv_vgf_filter_posts', 'vgf_filter_posts');

function vgf_filter_posts() {
    $selected_categories = isset($_POST['categories']) ? $_POST['categories'] : [];
    echo vgf_get_posts($selected_categories); // Get filtered posts
    wp_die(); // Required to terminate immediately and return a proper response
}

function vgf_enqueue_scripts() {
    wp_enqueue_script('vgf-ajax', get_stylesheet_directory_uri() . '/js/vgf-ajax.js', array('jquery'), null, true);
    wp_localize_script('vgf-ajax', 'vgf_ajax_obj', array('ajaxurl' => admin_url('admin-ajax.php')));
    wp_enqueue_style('vgf-style', get_stylesheet_directory_uri() . '/css/vgf-style.css');
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
}
add_action('wp_enqueue_scripts', 'vgf_enqueue_scripts');



// Quiz Functionality

// New Quize functionality

// Register Quiz and Quiz Result custom post types
function register_quiz_post_types() {
    // Register Quiz Post Type
    register_post_type('quiz', array(
        'label' => 'Quizzes',
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor'),
        'menu_icon' => 'dashicons-welcome-learn-more',
    ));

    // Register Quiz Result Post Type (submenu under Quiz)
    register_post_type('quiz_result', array(
        'label' => 'Quiz Results',
        'public' => false,
        'show_ui' => true,
        'supports' => array('title', 'editor', 'custom-fields'),
        'show_in_menu' => 'edit.php?post_type=quiz',
        'menu_position' => 20,
    ));
}
add_action('init', 'register_quiz_post_types');

// Shortcode for displaying the quiz
function dynamic_quiz_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => '',
    ), $atts, 'multi_step_quiz');

    $quiz_id = $atts['id'];
    if (empty($quiz_id)) {
        return "Quiz ID is missing.";
    }

    $quiz_title = get_the_title($quiz_id);
	$button_name = get_field('button_name', $quiz_id);
    $questions = get_field('questions', $quiz_id);

    ob_start();
    ?>
    <button id="quizBtn_<?php echo esc_attr($quiz_id); ?>"><?php echo esc_html($button_name ? $button_name : $quiz_title); ?></button>
    <div id="quizPopup_<?php echo esc_attr($quiz_id); ?>" class="quiz-popup-overlay" style="display: none;">
        <div class="quiz-popup-content">
            <button id="closeQuizPopup_<?php echo esc_attr($quiz_id); ?>" class="quiz-popup-close">&times;</button>

            <!-- Left Section with Image -->
            <div class="left">
                <img src="http://157.245.104.64:2006/wp-content/uploads/2024/10/quiz.png" alt="Quiz Image">
            </div>

            <!-- Right Section with Form -->
<div class="right">
    <h3 class="quizHeading"><?php echo esc_html($quiz_title); ?></h3>
    
    <!-- Email Container (Visible Only Initially) -->
    <div id="emailContainer_<?php echo esc_attr($quiz_id); ?>" class="email-input-container">
        <p class="quizSubHeading">Enter your email to begin the quiz.</p>
        <label for="userEmail_<?php echo esc_attr($quiz_id); ?>">Email</label>
        <div class="email-input-wrapper">
            <input type="email" id="userEmail_<?php echo esc_attr($quiz_id); ?>" placeholder="Enter Email" required>
            <span class="email-icon">&#9993;</span> <!-- Email icon as a Unicode character -->
        </div>
        <button id="startQuiz_<?php echo esc_attr($quiz_id); ?>" class="gradient-button">Save & Proceed</button>
    </div>

    <!-- Quiz Content (Initially Hidden) -->
    <div id="quizContent_<?php echo esc_attr($quiz_id); ?>" style="display: none;">
        <div id="quizQuestionContainer_<?php echo esc_attr($quiz_id); ?>"></div>
        <div class="quizNavigationContainer">
            <div class="quizNavigationPrev">
                <button id="prevQuestion_<?php echo esc_attr($quiz_id); ?>" disabled>Previous</button>
            </div>
            <div class="quizNavigationSlider">
                <input type="range" id="quizSlider_<?php echo esc_attr($quiz_id); ?>" min="0" max="<?php echo count($questions) - 1; ?>" value="0" class="quiz-slider">
            </div>
            <div class="quizNavigationNext">
                <button id="nextQuestion_<?php echo esc_attr($quiz_id); ?>">Next</button>
            </div>
        </div>
    </div>
</div>

        </div>
    </div>

    <script>
        jQuery(document).ready(function($) {
            const questions = <?php echo json_encode($questions); ?>;
            let currentQuestionIndex = 0;
            let userAnswers = [];
            let userEmail = '';
            const quizId = <?php echo esc_js($quiz_id); ?>;

            $('#quizBtn_' + quizId).click(function() {
                $('#quizPopup_' + quizId).show();
            });

            $('#closeQuizPopup_' + quizId).click(function() {
                $('#quizPopup_' + quizId).hide();
            });

            $('#startQuiz_' + quizId).click(function() {
                userEmail = $('#userEmail_' + quizId).val();
                if (userEmail) {
                    $('#emailContainer_' + quizId).hide();
                    $('#quizContent_' + quizId).show();
                    loadQuestion(0);
                } else {
                    alert('Please enter your email to proceed.');
                }
            });

 function loadQuestion(index) {
    const questionData = questions[index];
    $('#quizQuestionContainer_' + quizId).html(`
        <h3 class="quizQuestions">${questionData.question_text}</h3>
        ${questionData.answers.map((answer, i) => `
            <label class="quiz-option">
                <input class="quiz-option-input" type="radio" name="quizAnswer" value="${i}">
                ${answer.answer_text} (Score: ${answer.score})
            </label>
        `).join('')}
    `);

    // Update slider position
    $('#quizSlider_' + quizId).val(index);

    // Always show the Previous button, disable if on the first question
    const prevButton = $('#prevQuestion_' + quizId);
    prevButton.prop('disabled', index === 0);
    prevButton.show();

    // Disable the Next button initially
    const nextButton = $('#nextQuestion_' + quizId);
    nextButton.prop('disabled', true); // Disable by default

    // Listen for answer selection to enable the Next button
    $('input[name="quizAnswer"]').change(function() {
        nextButton.prop('disabled', false); // Enable when an answer is selected
    });

    // Set Next button text for last question
    nextButton.text(index === questions.length - 1 ? 'Submit' : 'Next');
}



            // Slider change event
            $('#quizSlider_' + quizId).on('input', function() {
                const newIndex = parseInt($(this).val());
                if (newIndex !== currentQuestionIndex) {
                    currentQuestionIndex = newIndex;
                    loadQuestion(currentQuestionIndex);
                }
            });

            $('#nextQuestion_' + quizId).click(function() {
                const selectedAnswer = $('input[name="quizAnswer"]:checked').val();
                if (selectedAnswer === undefined) {
                    alert('Please select an answer.');
                    return;
                }

                userAnswers[currentQuestionIndex] = parseInt(selectedAnswer);

                if (currentQuestionIndex < questions.length - 1) {
                    currentQuestionIndex++;
                    loadQuestion(currentQuestionIndex);
                } else {
                    submitQuiz();
                }
            });

            $('#prevQuestion_' + quizId).click(function() {
                if (currentQuestionIndex > 0) {
                    currentQuestionIndex--;
                    loadQuestion(currentQuestionIndex);
                }
            });

            function submitQuiz() {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'save_quiz_result',
                        quiz_id: quizId,
                        email: userEmail,
                        answers: userAnswers
                    },
success: function(response) {
    const scorePercentage = parseFloat(response) || 0;

    $('#quizContent_' + quizId).html(`
        <div class="quizSVGresult-score-chart">
            <svg viewBox="0 0 36 36" class="quizSVGresult-circular-chart">
                <defs>
                    <linearGradient id="quizSVGresult-gradient" x1="1" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#f97300" />
                        <stop offset="100%" stop-color="#ff5e62" />
                    </linearGradient>
                </defs>
                <path class="quizSVGresult-circle-bg"
                    d="M18 2.0845
                       a 15.9155 15.9155 0 0 1 0 31.831
                       a 15.9155 15.9155 0 0 1 0 -31.831" />
                <path class="quizSVGresult-circle"
                    stroke="url(#quizSVGresult-gradient)" 
                    stroke-dasharray="${scorePercentage}, 100"
                    d="M18 2.0845
                       a 15.9155 15.9155 0 0 1 0 31.831
                       a 15.9155 15.9155 0 0 1 0 -31.831" />
                <text x="50%" y="50%" class="quizSVGresult-percentage" dy=".3em">${scorePercentage}</text>
            </svg>
        </div>
        <h2 class="QuizeResultScoreHeading">Your Score:</h2>
        <p class="QuizeResultScoreContent" >You've taken the first step towards better parenting, but there's room to grow. Our tailored workshops and expert guidance can help you close this gap.</p>

<p class="QuizeResultScoreContent">Don’t miss out on this opportunity to become the parent you aspire to be—sign up today and take your parenting journey to the next level!</p>
    `);
},
                    error: function() {
                        alert('There was an error saving your results.');
                    }
                });
            }
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('multi_step_quiz', 'dynamic_quiz_shortcode');


// AJAX Handler for saving quiz result
function save_quiz_result() {
    $quiz_id = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $answers = isset($_POST['answers']) ? $_POST['answers'] : array();
    $questions = get_field('questions', $quiz_id);  // Get the quiz questions from ACF

    if ($quiz_id && $email) {
        // Calculate Score
        $userScore = 0;
        $maxScore = 0;

        foreach ($questions as $index => $question) {
            $selectedAnswerIndex = isset($answers[$index]) ? $answers[$index] : null;
            if ($selectedAnswerIndex !== null && isset($question['answers'][$selectedAnswerIndex])) {
                $userScore += $question['answers'][$selectedAnswerIndex]['score'];
            }

            $questionMaxScore = max(array_column($question['answers'], 'score'));
            $maxScore += $questionMaxScore;
        }

        // Calculate percentage
        $percentage = $maxScore > 0 ? round(($userScore / $maxScore) * 100) : 0;

        // Save result as a custom post type entry
        $result_post_id = wp_insert_post(array(
            'post_type' => 'quiz_result',
            'post_title' => 'Result for Quiz ID ' . $quiz_id,
            'post_status' => 'publish',
        ));

        if ($result_post_id) {
            update_post_meta($result_post_id, 'quiz_id', $quiz_id);
            update_post_meta($result_post_id, 'email', $email);
            update_post_meta($result_post_id, 'selected_answers', $answers);
            update_post_meta($result_post_id, 'percentage_score', $percentage);

            echo $percentage; // Return percentage score for frontend display
        } else {
            echo 'Error saving result.';
        }
    }

    wp_die();
}
add_action('wp_ajax_save_quiz_result', 'save_quiz_result');
add_action('wp_ajax_nopriv_save_quiz_result', 'save_quiz_result');

// Add a custom column for shortcode in the Quiz admin list
function add_quiz_shortcode_column($columns) {
    $columns['quiz_shortcode'] = 'Shortcode';
    return $columns;
}
add_filter('manage_quiz_posts_columns', 'add_quiz_shortcode_column');

function show_quiz_shortcode_column_content($column, $post_id) {
    if ($column == 'quiz_shortcode') {
        echo '[multi_step_quiz id="' . $post_id . '"]';
    }
}
add_action('manage_quiz_posts_custom_column', 'show_quiz_shortcode_column_content', 10, 2);

// Customize the columns for Quiz Results
function add_quiz_result_columns($columns) {
    $columns['email'] = 'User Email';
    $columns['selected_answers'] = 'Selected Answers';
    $columns['percentage_score'] = 'Score (%)';
    return $columns;
}
add_filter('manage_quiz_result_posts_columns', 'add_quiz_result_columns');

function show_quiz_result_column_content($column, $post_id) {
    if ($column == 'email') {
        echo get_post_meta($post_id, 'email', true);
    } elseif ($column == 'selected_answers') {
        $selected_answers = get_post_meta($post_id, 'selected_answers', true);
        echo is_array($selected_answers) ? implode(', ', $selected_answers) : 'N/A';
    } elseif ($column == 'percentage_score') {
        echo get_post_meta($post_id, 'percentage_score', true) . '%';
    }
}
add_action('manage_quiz_result_posts_custom_column', 'show_quiz_result_column_content', 10, 2);

function enqueue_quiz_styles() {
    if (is_singular('quiz') || is_page()) { // Adjust this condition based on where you're using the quiz
        wp_enqueue_style('quiz-style', get_stylesheet_directory_uri() . '/css/newQuiz-style.css');
    }
}
add_action('wp_enqueue_scripts', 'enqueue_quiz_styles');