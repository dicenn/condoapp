<?php
/* Template Name: CondoApp units theme */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

    <button id="toggleButton" onclick="toggleNav()">
        <div class="button-content">
            <div class="text-container">
                Filters
            </div>
            <div id="toggleArrow" class="arrow-container">
                &gt;
            </div>
        </div>
    </button>

    <!-- Side Panel for Filters -->
        <div id="mySidepanel" class="sidepanel">
            <aside id="secondary" class="widget-area">
                <div class="filter-section">
                    <button id="apply-filters-btn" class="btn btn-primary">Apply Filters</button>
                    <button id="clear-filters-btn" class="btn btn-secondary">Clear Filters</button>
                    
                    <label>Price</label>
                    <input type="text" id="price-range" name="price-range" value="" />

                    <label>Square footage</label>
                    <input type="text" id="square-footage-range" name="square-footage-range" value="" />

                    <label>Occupancy date</label>
                    <input type="text" id="occupancy-date-range" name="occupancy-date-range" value="" />

                    <label># of Bedrooms</label>
                    <select id="bedrooms-filter" multiple="multiple"></select>
                    
                    <label># of Baths</label>
                    <select id="bathrooms-filter" multiple="multiple"></select>

                    <label>Unit Type</label>
                    <select id="unit-type-filter" multiple="multiple"></select>

                    <label>Developer</label>
                    <select id="developer-filter" multiple="multiple"></select>

                    <label>Pre-occupancy Deposit</label>
                    <select id="pre-occupancy-deposit-filter" multiple="multiple"></select>
                    
                    <label>Project</label>
                    <select id="project-filter" multiple="multiple"></select>
                    
                    <label># of Dens</label>
                    <select id="den-filter" multiple="multiple"></select>

                </div>
            </aside>
        </div>

        <div id="sidepanel-overlay" class="modal"></div>

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

            <!-- Container for the "No more units" message -->
            <div id="no-more-units-message" style="text-align: center; display: none;">
                <p>No more units to show</p>
            </div>
        </section>
    </main><!-- #main -->
</div><!-- #primary -->

<!-- Modal Structure for Floor Plan Image -->
<div class="modal fade" id="floorPlanModal" tabindex="-1" role="dialog" aria-labelledby="floorPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <button type="button" class="close" aria-label="Close" onclick="closeModal()">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="modal-body">
                <img id="modalImage" src="" class="img-fluid" alt="Floor Plan">
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>