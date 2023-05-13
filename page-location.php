<?php 
/* Template Name:  location */
get_header();

?>
    <style>
 #loc_syracuse{
    background-image:url("/wp-content/uploads/2019/11/syracus.jpg");
    background-repeat:no-repeat;
}
 #loc_miami{
    background-image:url("/wp-content/uploads/2019/10/miami.jpg");
    background-repeat:no-repeat;
}

#loc_houston{
    background-image:url("/wp-content/uploads/2019/09/houston.png");
    background-repeat:no-repeat;
}
#loc_losangeles{
    background-image:url("/wp-content/uploads/2019/09/los_angles-1.png");
    background-repeat:no-repeat;
}
#loc_buffalo{
    background-image:url("/wp-content/uploads/2019/09/buffalo.png");
    background-repeat:no-repeat;
}
#loc_rochester{
    background-image:url("/wp-content/uploads/2019/09/rochester.png");
    background-repeat:no-repeat;
}
#loc_newyork{
    background-image:url("/wp-content/uploads/2019/10/houten.jpg");
    background-repeat:no-repeat;
}
#san_francisco{
    background-image:url("/wp-content/uploads/2020/02/san-francisco.jpg");
    background-repeat:no-repeat;
}
#washington{
    background-image:url("/wp-content/uploads/2020/02/washinton-dc.jpg");
    background-repeat:no-repeat;

}
.locationSection{
    background-size:cover;
    min-height: 335px;  
}
.locationSection form {
	padding: 20px;
    float: right;
    margin:4%
}
p.title {
    font-size: 22px;
}

#loc_houston .title{
	font-size: 74px !important;
    padding: 50px 0;
}
.locationSection p {
    color: white;
    
}
.white-background {
	background: rgba(2, 2, 2, 0.25);
}
.locationSection .loc-prov-header{
	font-size: 44px;
}

.locationSection .ourservice{
    color: white;
    font-size: 28px;
    font-weight: 600;
    line-height: 45px;
}
.locationSection h2 {	
    padding: 12px 42px 20px;
    color: white;
    font-weight: bold;
    font-size: 55px;
}
.locationSection input,textarea {
    color: black;
    padding:0px;
}
.wpcf7-submit{
    padding:8px !important;
}
</style>

                   
                    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
				
							<?php  the_content();?>
					<?php endwhile; endif; ?>
                 
   
<?php
get_footer();
?>