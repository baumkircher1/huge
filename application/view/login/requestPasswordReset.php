<div class="container">
    <h1>Request a password reset</h1>
    <div class="box">

        <!-- echo out the system feedback (error and success messages) -->
        <?php $this->renderFeedbackMessages(); ?>

        <!-- request password reset form box -->
        <form method="post" action="<?php echo Config::get('URL'); ?>login/requestPasswordReset_action">
            <label for="user_name_or_email">
                Enter your username or email and you'll get a mail with instructions:
                <input type="text" name="user_name_or_email" required />
            </label>

            <!-- Google reCAPTCHA v2 widget -->
            <div class="g-recaptcha" data-sitekey="<?php echo Config::get('RECAPTCHA_SITE_KEY'); ?>" style="margin: 15px 0;"></div>

            <input type="submit" value="Send me a password-reset mail" />
        </form>

    </div>
</div>

<!-- Load the reCAPTCHA API script -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
