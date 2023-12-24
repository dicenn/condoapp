<?php
/* Template Name: CondoApp units theme */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

    <button id="toggleButton" onclick="toggleNav()">Filters</button>

    <!-- Side Panel for Filters -->
        <div id="mySidepanel" class="sidepanel">
            <aside id="secondary" class="widget-area">
                <div class="filter-section">
                    <input type="text" id="price-range" name="price-range" value="" />
                    <input type="text" id="square-footage-range" name="square-footage-range" value="" />
                    <input type="text" id="occupancy-date-range" name="occupancy-date-range" value="" />

                    <label for="bedrooms-filter"># of Bedrooms</label>
                    <select id="bedrooms-filter" multiple="multiple"></select>
                    
                    <label for="bathrooms-filter"># of Baths</label>
                    <select id="bathrooms-filter" multiple="multiple"></select>

                    <label for="unit-type-filter">Unit Type</label>
                    <select id="unit-type-filter" multiple="multiple"></select>

                    <label for="developer-filter">Developer</label>
                    <select id="developer-filter" multiple="multiple"></select>

                    <label for="pre-occupancy-deposit-filter">Pre-occupancy Deposit</label>
                    <select id="pre-occupancy-deposit-filter" multiple="multiple"></select>
                    
                    <label for="project-filter">Project</label>
                    <select id="project-filter" multiple="multiple"></select>
                    
                    <label for="den-filter"># of Dens</label>
                    <select id="den-filter" multiple="multiple"></select>

                    <button id="apply-filters-btn" class="btn btn-primary">Apply Filters</button>
                    <button id="clear-filters-btn" class="btn btn-secondary">Clear Filters</button>
                </div>
            </aside>
        </div>

        <!-- The main content area where unit cards will be displayed -->
        <section id="unit-cards" class="container mt-5">
            <?php
            $query_data = get_filtered_units_sql(array());
            // echo '<pre>SQL Query: ' . htmlspecialchars($query_data['sql']) . '</pre>';

            foreach ($query_data['results'] as $unit) {
                echo condoapp_get_unit_card_html($unit);
            }
            ?>
            <!-- Container for the loading spinner -->
            <div id="spinner-container" style="text-align: center; display: none;">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/loading_spinner.gif" alt="Loading..." style="width: 5%;">
            </div>
        </section>
    </main><!-- #main -->
</div><!-- #primary -->

<?php get_footer(); ?>
