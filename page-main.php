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
                    // Ensure you have a default image in case the floor_plan_link is empty
                    $default_image = 'path_to_default_image.jpg'; // Replace with actual default image path
                    $image = !empty($unit->jpg_link) ? esc_url($unit->jpg_link) : $default_image;
                    ?>

                    <div class="card mb-3">
                        <div class="row no-gutters">
                            <div class="col-md-4">
                                <img src="<?php echo $image; ?>" class="card-img" alt="Floor Plan">
                            </div>
                            <div class="col-md-5">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo esc_html($unit->project); ?></h5>
                                    <p class="card-text"><?php echo esc_html($unit->model); ?></p>
                                    <p class="card-text"><small class="text-muted"><?php echo esc_html($unit->price); ?></small></p>
                                    <!-- Add more details here -->
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card-body">
                                    <h5 class="card-title">Investment Summary</h5>
                                    <p class="card-text">Annualized Return: Placeholder</p>
                                    <!-- More investment details here -->
                                    <button class="btn btn-primary">Speak to an Agent</button>
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

