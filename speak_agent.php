<div id="agentModal" class="modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Close Button -->
            <span id="closeModal" class="close">&times;</span>

            <div class="modal-header">
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/CondoApp logo - Nov 7 2023.png" alt="CondoApp Logo" class="mx-auto d-block">
                <p class="text-center w-100">Please fill out the form below to speak with an agent.</p>
            </div>

            <!-- Form -->
            <form id="agentContactForm" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="submit_agent_form">
                <?php wp_nonce_field('condoapp_nonce'); ?>
                <input type="text" name="name" placeholder="Your Name" required><br>
                <input type="email" name="email" placeholder="Your Email" required><br>
                <input type="tel" name="phone" placeholder="Your Phone Number" required><br>
                <input type="submit" value="Submit">
                <p id="formSubmissionMessage"></p> <!-- Message container -->
            </form>
        </div>
    </div>
</div>
