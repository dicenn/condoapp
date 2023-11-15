<?php
/* Template Name: CondoApp units theme */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <!-- Here will be the filter section -->
        <aside id="secondary" class="widget-area">
            <!-- <p>Filter section placeholder</p> -->

            <!-- Price Range Filter -->
            <div class="filter-section">
                <!-- <p class="filter-description">Select the price range for the units you are interested in:</p> -->
                <!-- <label for="price-range">Price Range:</label> -->
                <input type="text" id="price-range" name="price-range" value="" />
                <input type="text" id="square-footage-range" name="square-footage-range" value="" />
            </div>
        </aside>

        <div class="dropdown">
            <button class="dropbtn">Bedrooms</button>
            <div class="dropdown-content">
                <form id="bedrooms-filter">
                    <label><input type="checkbox" name="bedrooms" value="1"> 1 Bedroom</label>
                    <label><input type="checkbox" name="bedrooms" value="2"> 2 Bedrooms</label>
                    <label><input type="checkbox" name="bedrooms" value="3"> 3 Bedrooms</label>
                    <!-- Add more options as needed -->
                </form>
            </div>
        </div>

        <!-- The main content area where unit cards will be displayed -->
        <section id="unit-cards" class="container mt-5">
            <?php
            $query_data = get_filtered_units_sql(array());
            echo '<pre>SQL Query: ' . htmlspecialchars($query_data['sql']) . '</pre>';

            foreach ($query_data['results'] as $unit) {
                echo condoapp_get_unit_card_html($unit);
            }
            ?>
        </section>
    </main><!-- #main -->
</div><!-- #primary -->

<?php get_footer(); ?>
