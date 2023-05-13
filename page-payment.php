<?php

/**
 * Template Name: Payment after login
 */

get_header();

?>
    <section id="content">
			<div class="container">
				<div class="col-xs-12 col-sm-12" id="single">	
  <?php 
            global $user_login;

            // In case of a login error.
            if ( isset( $_GET['login'] ) && $_GET['login'] == 'failed' ) : ?>
    	            <div class="aa_error">
    		            <p><?php _e( 'FAILED: Try again!', 'AA' ); ?></p>
    	            </div>
            <?php 
                endif;

            // If user is already logged in.
            if ( is_user_logged_in() ) : 
            				
				 if (have_posts()) : while (have_posts()) : the_post(); ?>
					<article>
						
						<?php the_content();?>
					</article>
					<?php endwhile; endif; ?>
				
			
		</section>
        <?php
            else: 
                
                // Login form arguments.
                $args = array(
                    'echo'           => true,
                    'redirect'       => home_url( '/login/' ), 
                    'form_id'        => 'loginform',
                    'label_username' => __( 'Username' ),
                    'label_password' => __( 'Password' ),
                    'label_remember' => __( 'Remember Me' ),
                    'label_log_in'   => __( 'Log In' ),
                    'id_username'    => 'user_login',
                    'id_password'    => 'user_pass',
                    'id_remember'    => 'rememberme',
                    'id_submit'      => 'wp-submit',
                    'remember'       => true,
                    'value_username' => NULL,
                    'value_remember' => true
                ); 
                
                // Calling the login form.
                wp_login_form( $args );

            endif;
    ?> 
    </div>
</div>
				
    <?php get_footer(); ?>