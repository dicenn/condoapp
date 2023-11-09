<?php
/* Template Name: CondoApp units theme */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <!-- Here will be the filter section -->
        <aside id="secondary" class="widget-area">
            <p>Filter section placeholder</p>
            <!-- Price Range Filter -->
            <div class="filter-section">
                <p class="filter-description">Select the price range for the units you are interested in:</p>
                <label for="price-range">Price Range:</label>
                <input type="text" id="price-range" name="price-range" value="" />
            </div>
        </aside>

        <!-- The main content area where unit cards will be displayed -->
        <section id="unit-cards" class="container mt-5">
            <?php
                global $wpdb;
                $limit = 10; // Set the number of units to load initially
                $units = $wpdb->get_results($wpdb->prepare("
                    SELECT
                        *
                    FROM condo_app.pre_con_unit_database_20230827_v4 u
                        LEFT JOIN condo_app.pre_con_pdf_jpg_database_20230827 j on j.pdf_link = u.floor_plan_link
                    LIMIT %d
                ", $limit));
                
                foreach ($units as $unit) {
                    echo condoapp_get_unit_card_html($unit);
            ?>

            <?php
                }
            ?>
        </section>
    </main><!-- #main -->
</div><!-- #primary -->

<?php get_footer(); ?>
