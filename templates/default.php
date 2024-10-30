<?php 
$uid = 'c4d-testimonial-slider-'.uniqid();
?>
<script>
	(function($){
		$(document).ready(function(){
			teamTestimonialSlider['<?php echo $uid; ?>'] = <?php echo json_encode($params); ?>;
		});	
	})(jQuery);
</script>
<div class="c4d-testimonial">
	<div class="c4d-testimonial__slider">
		<div id="<?php echo esc_attr($uid); ?>">
			<?php 
				while ( $q->have_posts() ) :
					$p = $q->the_post(); 
					?>
					<div class="item">
						<div class="item-inner">
							<?php 
								global $post;
								$pid = get_the_ID();
								$role = get_post_meta($pid, 'c4d_testimonial_role', true);
							?>
							<div class="quote">
								<div class="quote-inner">
									<?php the_content(); ?>	
								</div>
							</div>
							
							<div class="author-info">
								<div class="image">
									<?php the_post_thumbnail('thumbnail', array( 'alt' => get_the_title() )); ?>
								</div>
								<div class="info-text">
									<?php the_title( '<h3 class="title">', '</h3>'); ?>
									<div class="role"><?php echo $role; ?></div>
								</div>
							</div>
						</div>
					</div>
				<?php endwhile; // end of the loop. ?>
		</div>
	</div>
</div>
