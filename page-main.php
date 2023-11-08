<?php
/* Template Name: CondoApp units theme */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <!-- Here will be the filter section -->
        <aside id="secondary" class="widget-area">
            <p>Filter section placeholder</p>
            <!-- Filters will go here -->
        </aside>

        <!-- The main content area where unit cards will be displayed -->
        <section id="unit-cards" class="container mt-5">
            <?php
                global $wpdb;
                $units = $wpdb->get_results("
                    SELECT
                        *
                    FROM condo_app.pre_con_unit_database_20230827_v4 u
                        LEFT JOIN condo_app.pre_con_pdf_jpg_database_20230827 j on j.pdf_link = u.floor_plan_link
                    LIMIT 5
                ");
                
                foreach ($units as $unit) {
                    // Default image if floor_plan_link is empty
                    $default_image = get_template_directory_uri() . '/images/default-floorplan.jpg';
                    $image = !empty($unit->jpg_link) ? esc_url($unit->jpg_link) : $default_image;
                    // Placeholder data for investment summary
                    $annualized_return = 'TBD'; // To be defined
                    $pre_occupancy_deposit = 'TBD';
                    $cash_on_cash_return = 'TBD';
                    $projected_rent = 'TBD';
                    $holding_period = 'TBD';
                    $projected_appreciation = 'TBD';
            ?>

            <div class="card mb-3">
                <div class="row no-gutters">
                    <!-- Floor Plan Image Section -->
                    <div class="col-md-4">
                        <div class="image-header">
                            <!-- Placeholder for the image header -->
                            <span><?php echo esc_html($unit->bedrooms); ?> beds | <?php echo esc_html($unit->bathrooms); ?> baths | <?php echo esc_html($unit->interior_size); ?> sqft interior</span>
                        </div>
                        <img src="<?php echo $image; ?>" class="card-img" alt="Floor Plan">
                    </div>

                    <!-- Unit Details Section -->
                    <div class="col-md-5">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo esc_html($unit->project); ?> | Model <?php echo esc_html($unit->model); ?> | Unit #<?php echo esc_html($unit->unit_number); ?></h5>
                            <p class="card-text">Starting from $<?php echo esc_html(number_format($unit->price)); ?></p>
                            <p class="card-text">Occupancy: <?php echo esc_html($unit->occupancy_date); ?></p>
                            <p class="card-text">Developer: <?php echo esc_html($unit->developer); ?></p>
                            <p class="card-text"><?php echo esc_html($unit->address); ?></p>
                            <!-- Add more unit-specific details here -->
                        </div>
                    </div>

                    <!-- Investment Summary Section -->
                    <div class="col-md-3">
                        <div class="card-body">
                            <h5 class="card-title">Investment Summary</h5>
                            <p class="card-text">+<?php echo $annualized_return; ?>% annualized return</p>
                            <p class="card-text"><?php echo $pre_occupancy_deposit; ?>% pre-occupancy deposit</p>
                            <p class="card-text">$<?php echo $cash_on_cash_return; ?> Cash-on-cash return</p>
                            <p class="card-text">$<?php echo $projected_rent; ?> projected rent</p>
                            <p class="card-text"><?php echo $holding_period; ?> year holding period</p>
                            <p class="card-text"><?php echo $projected_appreciation; ?>% Projected appreciation</p>
                            <!-- More investment details here -->
                            <button class="btn btn-primary">Speak to an Agent</button>
                            <!-- Icons for interaction -->
                        </div>
                    </div>
                </div>
            </div>

            <?php
                }
            ?>
        </section>



    </main><!-- #main -->
</div><!-- #primary -->

