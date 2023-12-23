<div id="agentModal" class="modal">
    <div class="modal-content">
        <span id="closeModal" class="close">&times;</span>
        <form id="agentContactForm" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="submit_agent_form">
            <?php wp_nonce_field('condoapp_nonce'); ?>
            <input type="text" name="name" placeholder="Your Name" required><br>
            <input type="email" name="email" placeholder="Your Email" required><br>
            <input type="tel" name="phone" placeholder="Your Phone Number" required><br>
            <input type="submit" value="Submit">
            <p id="formSubmissionMessage"></p> <!-- Placeholder for the message -->
        </form>
    </div>
</div>
