<?php
/** Site header navigation */
?>
<header class="idlp-header">
            <div class="idlp-header-inner">
        
                <?php require_once get_template_directory() . '/includes/brand-logo.php'; ?>
                

                <div class="id-nav">
                    <div class="id-nav-item">
                        <a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">
                            <p>About</p>
                        </a>
                    </div>
                    <div class="id-nav-item collapsable">
                        <a href="<?php echo esc_url( home_url( '/services/' ) ); ?>">
                            <div class="id-nav-item-div">
                                <p>Services</p>
                                <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/home/down-arrow.svg" alt="Chevron down">
                                <div class="hover-bridge"></div>
                            </div>
                        </a>
                        <?php require_once get_template_directory() . '/includes/sections/services-megamenu.php'; ?>
                        
                    </div>
                    <div class="id-nav-item">
                        <a href="<?php echo esc_url( home_url( '/case-studies/' ) ); ?>">
                            <p>Case Studies</p>
                        </a>
                    </div>
                    <div class="id-nav-item">
                        <a href="<?php echo esc_url( home_url( '/portfolio/' ) ); ?>">
                            <p>Portfolio</p>
                        </a>
                    </div>
                    <div class="id-nav-item collapsable">
                        <a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">
                            <div class="id-nav-item-div">
                                <p>Resources</p>
                                <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/home/down-arrow.svg" alt="Chevron down">
                            </div>
                        </a>
                    </div>
                </div>
                <div class="id-header-actions">
                    <div class="id-header-btn">
                        <a href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>">
                            <div><p>Contact us</p></div>
                        </a>
                    </div>


                    <div class="hamburger" id="hamburger">
                        <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/home/hamburger-svgrepo-com.svg" alt="">
                    </div>
                </div>
            </div>
        </header>
