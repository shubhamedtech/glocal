<?php $breadcrumbs = array_filter(explode("/", $_SERVER['REQUEST_URI'])); ?>

<!-- BEGIN SIDEBPANEL-->
<nav class="page-sidebar" data-pages="sidebar">

    <!-- BEGIN SIDEBAR MENU HEADER-->
    <div class="sidebar-header">
        <?php if (!empty($light_logo)) { ?>
            <img src="<?= $light_logo ?>" alt="logo" class="brand" data-src="<?= $light_logo ?>" data-src-retina="<?= $light_logo_retina ?>" width="60">
        <?php } ?>
        <div class="sidebar-header-controls">
            <button aria-label="Toggle Drawer" type="button" class="btn btn-icon-link invert sidebar-slide-toggle m-l-20 m-r-10" data-pages-toggle="#appMenu">
                <i class="pg-icon">chevron_down</i>
            </button>
            <button aria-label="Pin Menu" type="button" class="btn btn-icon-link invert d-lg-inline-block d-xlg-inline-block d-md-inline-block d-sm-none d-none" data-toggle-pin="sidebar">
                <i class="pg-icon"></i>
            </button>
        </div>
    </div>
    <!-- END SIDEBAR MENU HEADER-->
    <!-- START SIDEBAR MENU -->
    <div class="sidebar-menu">
        <!-- BEGIN SIDEBAR MENU ITEMS-->
        <ul class="menu-items">
            <?php if (!empty($_SESSION['Enrolment_Number'])) { ?>
                <li class="m-t-20 ">
                    <a href="/dashboard">
                        <span class="title">Dashboard</span>
                    </a>
                    <span class="icon-thumbnail-main"><i class="uil uil-home"></i></span>
                </li>
            <?php } ?>
        </ul>
        <div class="clearfix"></div>
    </div>
    <!-- END SIDEBAR MENU -->
</nav>
<!-- END SIDEBAR -->
<!-- END SIDEBPANEL-->