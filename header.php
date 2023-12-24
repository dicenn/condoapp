<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <?php wp_head(); ?>
    <!-- prevent browser stack loading the site from cache on mobile -->
    <!-- <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Expires" content="0"> -->
</head>

<body <?php body_class(); ?>>
    <header id="masthead" class="site-header sticky-header">
        <div class="header-container">
            <div class="header-logo">
                <a href="<?php echo esc_url(home_url('/')); ?>">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/CondoApp logo - Nov 7 2023.png" alt="CondoApp Logo">
                </a>
            </div>

            <div class="header-navigation">
                <a href="#about-us">About Us</a>
                <a href="#sign-up">Sign Up</a>
                <a href="#blog">Blog</a>
                <button id="speakToAgentButton">Speak to an Agent</button>
            </div>
        </div>

        <?php include 'speak_agent.php'; ?>

    </header>

    <div id="content" class="site-content">
        <!-- Page content goes here -->
