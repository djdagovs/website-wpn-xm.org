<!-- Activate the ScrollSpy for the NavBar -->
<body data-spy="scroll" data-target="#top-nav">

  <!-- Top Navigation Bar -->
  <nav class="navbar navbar-inverse navbar-fixed-top" id="section-home">

    <div class="container" id="top-nav">

        <div class="navbar-header">
          <!-- Mobile Nav Burger -->
          <button type="button" class="navbar-toggle"
                  data-toggle="collapse" data-target="#navigation-bar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <!-- Logo -->
          <a class="navbar-brand" href="#">
            <span class="brand-name">WPN-XM Server Stack</span>
            <img class="brand-icon" alt="WPN-XM" title="WPN-XM Icon"
                 src="images/logo-transparent.png" width="74" height="59"
                 style="display: none; opacity: 1;"/>
          </a>
        </div>

      <!-- Menu Items -->
      <div class="collapse navbar-collapse" id="navigation-bar">
        <ul class="nav navbar-nav">
          <li class="active"><a href="index.html#section-home">Home</a></li>
          <li><a href="index.html#section-about">About</a></li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Downloads <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="downloads.php">Installation Wizards</a></li>
              <li><a href="components.php">Web Components</a></li>
              <li role="separator" class="divider"></li>
              <li><a href="compare-installers.php">Compare Installers</a></li>
              <li><a href="stats.php">Project Statistics</a></li>
            </ul>
          </li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Community <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="https://groups.google.com/forum/#!forum/wpn-xm">Mailinglist</a></li>
              <li role="separator" class="divider"></li>
              <li><a href="index.html#section-getinvolved">Get Involved</a></li>
              <li><a href="https://github.com/WPN-XM/WPN-XM/blob/master/CONTRIBUTING.md#contributing-to-the-wpn-xm-server-stack">Contributing</a></li>
            </ul>
          </li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Documentation <span class="caret"></span></a>
            <ul class="dropdown-menu">
                <li><a href="http://wpn-xm.github.io/docs/">Overview</a></li>
                <li role="separator" class="divider"></li>
                <li><a href="http://wpn-xm.github.io/docs/user-manual/en/">User-Manual (en)</a></li>                
                <li><a href="http://wpn-xm.github.io/docs/faq/en/">FAQ (en)</a></li>
                <li><a href="http://wpn-xm.github.io/docs/faq/de/">FAQ (de)</a></li>  
                <li><a href="https://github.com/WPN-XM/WPN-XM/wiki">Wiki</a></li>
              </ul>
          </li>
          <li><a href="support.php">Support</a></li>
          <li><a href="index.html#section-donate">Donate</a></li>
          <li><a href="index.html#section-imprint">Imprint</a></li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
          <li><a href="https://github.com/WPN-XM/WPN-XM/issues/new">Report Issue</a></li>
          <li class="dropdown">
            <a id="git" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
              Github <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li><a href="https://github.com/WPN-XM/WPN-XM/">WPИ-XM Build Tools</a></li>
              <li role="separator" class="divider"></li>
              <li><a href="https://github.com/WPN-XM/registry">Registry</a></li>
              <li><a href="https://github.com/WPN-XM/updater">Updater</a></li>
              <li role="separator" class="divider"></li>
              <li><a href="https://github.com/WPN-XM/webinterface">Webinterface</a></li>
              <li><a href="https://github.com/WPN-XM/server-control-panel">Server Control Panel</a></li>
              <li role="separator" class="divider"></li>
              <li><a href="https://travis-ci.org/WPN-XM">Travis-CI Overview</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container" id="content">

    <div class="col-md-12">
      <div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">

          <?php if(defined('RENDER_WPNXM_HEADER_LOGO') && RENDER_WPNXM_HEADER_LOGO !== false) { ?>
          <!-- Logo -->
          <div class="header">
            <div id="logo"></div>
            <h1 style="visibility:hidden; line-height: 1px;">WPN-XM</h1>
            <h2><strong>WPИ-XM</strong> is a web server stack for PHP development on Windows<small><sup>&reg;</sup></small>.</h2>
          </div>

          <hr/>
          <?php } ?>
