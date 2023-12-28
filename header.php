<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <header id="masthead" class="site-header sticky-header">
        <div class="header-container">
            <div class="header-logo">
                <a href="<?php echo esc_url(home_url('/')); ?>">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/CondoApp logo - Nov 7 2023.png" alt="CondoApp Logo">
                </a>
            </div>

            <div class="header-right">
                <table class="header-buttons-table">
                    <tr>
                        <td class="speak-to-agent-cell">
                            <button id="speakToAgentButton">Speak to an Agent</button>
                        </td>
                        <td class="hamburger-menu-cell">
                            <!-- Hamburger Menu Button -->
                            <button class="hamburger-menu-button">
                                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/hamburger.png" alt="Menu">
                            </button>
                        </td>
                    </tr>
                </table>
            </div>

        <!-- Menu Items Container (Initially Hidden) -->
        <div class="hamburger-menu-content">
            <a href="#about-us">About Us</a>
            <a href="#sign-up">Sign Up</a>
            <a href="#blog">Blog</a>
            <!-- Add more links as needed -->
        </div>

        <?php include 'speak_agent.php'; ?>
    </header>

    <div id="content" class="site-content">