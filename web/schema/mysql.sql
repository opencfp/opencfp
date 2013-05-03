  


<!DOCTYPE html>
<html>
  <head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# githubog: http://ogp.me/ns/fb/githubog#">
    <meta charset='utf-8'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>sentry/schema/mysql.sql at master · cartalyst/sentry · GitHub</title>
    <link rel="search" type="application/opensearchdescription+xml" href="/opensearch.xml" title="GitHub" />
    <link rel="fluid-icon" href="https://github.com/fluidicon.png" title="GitHub" />
    <link rel="apple-touch-icon" sizes="57x57" href="/apple-touch-icon-114.png" />
    <link rel="apple-touch-icon" sizes="114x114" href="/apple-touch-icon-114.png" />
    <link rel="apple-touch-icon" sizes="72x72" href="/apple-touch-icon-144.png" />
    <link rel="apple-touch-icon" sizes="144x144" href="/apple-touch-icon-144.png" />
    <link rel="logo" type="image/svg" href="http://github-media-downloads.s3.amazonaws.com/github-logo.svg" />
    <link rel="xhr-socket" href="/_sockets" />


    <meta name="msapplication-TileImage" content="/windows-tile.png" />
    <meta name="msapplication-TileColor" content="#ffffff" />
    <meta name="selected-link" value="repo_source" data-pjax-transient />

    
    
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />

    <meta content="authenticity_token" name="csrf-param" />
<meta content="+VQFswqsHqqQxdYx0aSMQiTh08SLELe7su+3+5jRx8Q=" name="csrf-token" />

    <link href="https://a248.e.akamai.net/assets.github.com/assets/github-5b364eeaae0f96a33d6de9704239924ede95fbc2.css" media="all" rel="stylesheet" type="text/css" />
    <link href="https://a248.e.akamai.net/assets.github.com/assets/github2-749329f6bc4f0f2842535f983d87bfdf7d109c41.css" media="all" rel="stylesheet" type="text/css" />
    


      <script src="https://a248.e.akamai.net/assets.github.com/assets/frameworks-92d138f450f2960501e28397a2f63b0f100590f0.js" type="text/javascript"></script>
      <script src="https://a248.e.akamai.net/assets.github.com/assets/github-bc374985e8441015fc645eca5b08988b6eadc695.js" type="text/javascript"></script>
      
      <meta http-equiv="x-pjax-version" content="a83f1cf6e26760d57e6880aeba2ca38a">

        <link data-pjax-transient rel='permalink' href='/cartalyst/sentry/blob/825eca0be48ddecbb2a38b9238387943b834da69/schema/mysql.sql'>
    <meta property="og:title" content="sentry"/>
    <meta property="og:type" content="githubog:gitrepository"/>
    <meta property="og:url" content="https://github.com/cartalyst/sentry"/>
    <meta property="og:image" content="https://secure.gravatar.com/avatar/548f77e47fa0810f8caf88bdc6dac2bb?s=420&amp;d=https://a248.e.akamai.net/assets.github.com%2Fimages%2Fgravatars%2Fgravatar-user-420.png"/>
    <meta property="og:site_name" content="GitHub"/>
    <meta property="og:description" content="sentry - A framework agnostic authentication &amp; authorization system."/>
    <meta property="twitter:card" content="summary"/>
    <meta property="twitter:site" content="@GitHub">
    <meta property="twitter:title" content="cartalyst/sentry"/>

    <meta name="description" content="sentry - A framework agnostic authentication &amp; authorization system." />

  <link href="https://github.com/cartalyst/sentry/commits/master.atom" rel="alternate" title="Recent Commits to sentry:master" type="application/atom+xml" />

  </head>


  <body class="logged_out page-blob  vis-public env-production  ">
    <div id="wrapper">

      

      
      
      

      
      <div class="header header-logged-out">
  <div class="container clearfix">

      <a class="header-logo-wordmark" href="https://github.com/">Github</a>

    <div class="header-actions">
        <a class="button primary" href="https://github.com/signup">Sign up for free</a>
      <a class="button" href="https://github.com/login?return_to=%2Fcartalyst%2Fsentry%2Fblob%2Fmaster%2Fschema%2Fmysql.sql">Sign in</a>
    </div>

      <ul class="top-nav">
          <li class="explore"><a href="https://github.com/explore">Explore GitHub</a></li>
        <li class="search"><a href="https://github.com/search">Search</a></li>
        <li class="features"><a href="https://github.com/features">Features</a></li>
          <li class="blog"><a href="https://github.com/blog">Blog</a></li>
      </ul>

  </div>
</div>


      

      


            <div class="site hfeed" itemscope itemtype="http://schema.org/WebPage">
      <div class="hentry">
        
        <div class="pagehead repohead instapaper_ignore readability-menu ">
          <div class="container">
            <div class="title-actions-bar">
              

<ul class="pagehead-actions">



    <li>
      <a href="/login?return_to=%2Fcartalyst%2Fsentry"
        class="minibutton js-toggler-target star-button entice tooltipped upwards"
        title="You must be signed in to use this feature" rel="nofollow">
        <span class="mini-icon mini-icon-star"></span>Star
      </a>
      <a class="social-count js-social-count" href="/cartalyst/sentry/stargazers">
        343
      </a>
    </li>
    <li>
      <a href="/login?return_to=%2Fcartalyst%2Fsentry"
        class="minibutton js-toggler-target fork-button entice tooltipped upwards"
        title="You must be signed in to fork a repository" rel="nofollow">
        <span class="mini-icon mini-icon-fork"></span>Fork
      </a>
      <a href="/cartalyst/sentry/network" class="social-count">
        76
      </a>
    </li>
</ul>

              <h1 itemscope itemtype="http://data-vocabulary.org/Breadcrumb" class="entry-title public">
                <span class="repo-label"><span>public</span></span>
                <span class="mega-icon mega-icon-public-repo"></span>
                <span class="author vcard">
                  <a href="/cartalyst" class="url fn" itemprop="url" rel="author">
                  <span itemprop="title">cartalyst</span>
                  </a></span> /
                <strong><a href="/cartalyst/sentry" class="js-current-repository">sentry</a></strong>
              </h1>
            </div>

            
  <ul class="tabs">
    <li class="pulse-nav"><a href="/cartalyst/sentry/pulse" class="js-selected-navigation-item " data-selected-links="pulse /cartalyst/sentry/pulse" rel="nofollow"><span class="mini-icon mini-icon-pulse"></span></a></li>
    <li><a href="/cartalyst/sentry" class="js-selected-navigation-item selected" data-selected-links="repo_source repo_downloads repo_commits repo_tags repo_branches /cartalyst/sentry">Code</a></li>
    <li><a href="/cartalyst/sentry/network" class="js-selected-navigation-item " data-selected-links="repo_network /cartalyst/sentry/network">Network</a></li>
    <li><a href="/cartalyst/sentry/pulls" class="js-selected-navigation-item " data-selected-links="repo_pulls /cartalyst/sentry/pulls">Pull Requests <span class='counter'>1</span></a></li>

      <li><a href="/cartalyst/sentry/issues" class="js-selected-navigation-item " data-selected-links="repo_issues /cartalyst/sentry/issues">Issues <span class='counter'>11</span></a></li>

      <li><a href="/cartalyst/sentry/wiki" class="js-selected-navigation-item " data-selected-links="repo_wiki /cartalyst/sentry/wiki">Wiki</a></li>


    <li><a href="/cartalyst/sentry/graphs" class="js-selected-navigation-item " data-selected-links="repo_graphs repo_contributors /cartalyst/sentry/graphs">Graphs</a></li>


  </ul>
  
<div class="tabnav">

  <span class="tabnav-right">
    <ul class="tabnav-tabs">
          <li><a href="/cartalyst/sentry/tags" class="js-selected-navigation-item tabnav-tab" data-selected-links="repo_tags /cartalyst/sentry/tags">Tags <span class="counter ">23</span></a></li>
    </ul>
    
  </span>

  <div class="tabnav-widget scope">


    <div class="select-menu js-menu-container js-select-menu js-branch-menu">
      <a class="minibutton select-menu-button js-menu-target" data-hotkey="w" data-ref="master">
        <span class="mini-icon mini-icon-branch"></span>
        <i>branch:</i>
        <span class="js-select-button">master</span>
      </a>

      <div class="select-menu-modal-holder js-menu-content js-navigation-container">

        <div class="select-menu-modal">
          <div class="select-menu-header">
            <span class="select-menu-title">Switch branches/tags</span>
            <span class="mini-icon mini-icon-remove-close js-menu-close"></span>
          </div> <!-- /.select-menu-header -->

          <div class="select-menu-filters">
            <div class="select-menu-text-filter">
              <input type="text" id="commitish-filter-field" class="js-filterable-field js-navigation-enable" placeholder="Filter branches/tags">
            </div>
            <div class="select-menu-tabs">
              <ul>
                <li class="select-menu-tab">
                  <a href="#" data-tab-filter="branches" class="js-select-menu-tab">Branches</a>
                </li>
                <li class="select-menu-tab">
                  <a href="#" data-tab-filter="tags" class="js-select-menu-tab">Tags</a>
                </li>
              </ul>
            </div><!-- /.select-menu-tabs -->
          </div><!-- /.select-menu-filters -->

          <div class="select-menu-list select-menu-tab-bucket js-select-menu-tab-bucket css-truncate" data-tab-filter="branches">

            <div data-filterable-for="commitish-filter-field" data-filterable-type="substring">

                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/1.1/develop/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="1.1/develop" rel="nofollow" title="1.1/develop">1.1/develop</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/1.1/master/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="1.1/master" rel="nofollow" title="1.1/master">1.1/master</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/feature/pdo-drivers/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="feature/pdo-drivers" rel="nofollow" title="feature/pdo-drivers">feature/pdo-drivers</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/fuelphp/1.0/develop/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="fuelphp/1.0/develop" rel="nofollow" title="fuelphp/1.0/develop">fuelphp/1.0/develop</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/fuelphp/1.0/master/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="fuelphp/1.0/master" rel="nofollow" title="fuelphp/1.0/master">fuelphp/1.0/master</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/fuelphp/2.0/master/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="fuelphp/2.0/master" rel="nofollow" title="fuelphp/2.0/master">fuelphp/2.0/master</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/fuelphp/2.1/develop/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="fuelphp/2.1/develop" rel="nofollow" title="fuelphp/2.1/develop">fuelphp/2.1/develop</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item selected">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/master/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="master" rel="nofollow" title="master">master</a>
                </div> <!-- /.select-menu-item -->
            </div>

              <div class="select-menu-no-results">Nothing to show</div>
          </div> <!-- /.select-menu-list -->


          <div class="select-menu-list select-menu-tab-bucket js-select-menu-tab-bucket css-truncate" data-tab-filter="tags">
            <div data-filterable-for="commitish-filter-field" data-filterable-type="substring">

                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/v2.0.0-beta6/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="v2.0.0-beta6" rel="nofollow" title="v2.0.0-beta6">v2.0.0-beta6</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/v2.0.0-beta5/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="v2.0.0-beta5" rel="nofollow" title="v2.0.0-beta5">v2.0.0-beta5</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/v2.0.0-beta4/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="v2.0.0-beta4" rel="nofollow" title="v2.0.0-beta4">v2.0.0-beta4</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/v2.0.0-beta3/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="v2.0.0-beta3" rel="nofollow" title="v2.0.0-beta3">v2.0.0-beta3</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/v2.0.0-beta2/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="v2.0.0-beta2" rel="nofollow" title="v2.0.0-beta2">v2.0.0-beta2</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/v2.0.0-beta1/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="v2.0.0-beta1" rel="nofollow" title="v2.0.0-beta1">v2.0.0-beta1</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/v2.0.0-alpha8/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="v2.0.0-alpha8" rel="nofollow" title="v2.0.0-alpha8">v2.0.0-alpha8</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/v2.0.0-alpha7/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="v2.0.0-alpha7" rel="nofollow" title="v2.0.0-alpha7">v2.0.0-alpha7</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/v2.0.0-alpha6/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="v2.0.0-alpha6" rel="nofollow" title="v2.0.0-alpha6">v2.0.0-alpha6</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/v2.0.0-alpha5/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="v2.0.0-alpha5" rel="nofollow" title="v2.0.0-alpha5">v2.0.0-alpha5</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/v2.0.0-alpha4/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="v2.0.0-alpha4" rel="nofollow" title="v2.0.0-alpha4">v2.0.0-alpha4</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/v2.0.0-alpha3/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="v2.0.0-alpha3" rel="nofollow" title="v2.0.0-alpha3">v2.0.0-alpha3</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/v2.0.0-alpha2/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="v2.0.0-alpha2" rel="nofollow" title="v2.0.0-alpha2">v2.0.0-alpha2</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/v2.0.0-alpha1/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="v2.0.0-alpha1" rel="nofollow" title="v2.0.0-alpha1">v2.0.0-alpha1</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/v1.1.3/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="v1.1.3" rel="nofollow" title="v1.1.3">v1.1.3</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/v1.1.2/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="v1.1.2" rel="nofollow" title="v1.1.2">v1.1.2</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/v1.1.1/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="v1.1.1" rel="nofollow" title="v1.1.1">v1.1.1</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/v1.1.0/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="v1.1.0" rel="nofollow" title="v1.1.0">v1.1.0</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/v1.1/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="v1.1" rel="nofollow" title="v1.1">v1.1</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/v1.0-rc1/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="v1.0-rc1" rel="nofollow" title="v1.0-rc1">v1.0-rc1</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/v1.0.0-beta1/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="v1.0.0-beta1" rel="nofollow" title="v1.0.0-beta1">v1.0.0-beta1</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/v1.0.0/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="v1.0.0" rel="nofollow" title="v1.0.0">v1.0.0</a>
                </div> <!-- /.select-menu-item -->
                <div class="select-menu-item js-navigation-item ">
                  <span class="select-menu-item-icon mini-icon mini-icon-confirm"></span>
                  <a href="/cartalyst/sentry/blob/v1.0/schema/mysql.sql" class="js-navigation-open select-menu-item-text js-select-button-text css-truncate-target" data-name="v1.0" rel="nofollow" title="v1.0">v1.0</a>
                </div> <!-- /.select-menu-item -->
            </div>

            <div class="select-menu-no-results">Nothing to show</div>

          </div> <!-- /.select-menu-list -->

        </div> <!-- /.select-menu-modal -->
      </div> <!-- /.select-menu-modal-holder -->
    </div> <!-- /.select-menu -->

  </div> <!-- /.scope -->

  <ul class="tabnav-tabs">
    <li><a href="/cartalyst/sentry" class="selected js-selected-navigation-item tabnav-tab" data-selected-links="repo_source /cartalyst/sentry">Files</a></li>
    <li><a href="/cartalyst/sentry/commits/master" class="js-selected-navigation-item tabnav-tab" data-selected-links="repo_commits /cartalyst/sentry/commits/master">Commits</a></li>
    <li><a href="/cartalyst/sentry/branches" class="js-selected-navigation-item tabnav-tab" data-selected-links="repo_branches /cartalyst/sentry/branches" rel="nofollow">Branches <span class="counter ">8</span></a></li>
  </ul>

</div>

  
  
  


            
          </div>
        </div><!-- /.repohead -->

        <div id="js-repo-pjax-container" class="container context-loader-container" data-pjax-container>
          


<!-- blob contrib key: blob_contributors:v21:a6346ec7329d7835506b21b69f2a9657 -->
<!-- blob contrib frag key: views10/v8/blob_contributors:v21:a6346ec7329d7835506b21b69f2a9657 -->


<div id="slider">
    <div class="frame-meta">

      <p title="This is a placeholder element" class="js-history-link-replace hidden"></p>

        <div class="breadcrumb">
          <span class='bold'><span itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb"><a href="/cartalyst/sentry" class="js-slide-to" data-branch="master" data-direction="back" itemscope="url"><span itemprop="title">sentry</span></a></span></span><span class="separator"> / </span><span itemscope="" itemtype="http://data-vocabulary.org/Breadcrumb"><a href="/cartalyst/sentry/tree/master/schema" class="js-slide-to" data-branch="master" data-direction="back" itemscope="url"><span itemprop="title">schema</span></a></span><span class="separator"> / </span><strong class="final-path">mysql.sql</strong> <span class="js-zeroclipboard zeroclipboard-button" data-clipboard-text="schema/mysql.sql" data-copied-hint="copied!" title="copy to clipboard"><span class="mini-icon mini-icon-clipboard"></span></span>
        </div>

      <a href="/cartalyst/sentry/find/master" class="js-slide-to" data-hotkey="t" style="display:none">Show File Finder</a>


        
  <div class="commit file-history-tease">
    <img class="main-avatar" height="24" src="https://secure.gravatar.com/avatar/ace9008e72a1b72cc41d697e922faf17?s=140&amp;d=https://a248.e.akamai.net/assets.github.com%2Fimages%2Fgravatars%2Fgravatar-user-420.png" width="24" />
    <span class="author"><a href="/bencorlett" rel="author">bencorlett</a></span>
    <time class="js-relative-date" datetime="2013-05-02T16:45:13-07:00" title="2013-05-02 16:45:13">May 02, 2013</time>
    <div class="commit-title">
        <a href="/cartalyst/sentry/commit/b897af0a84d1caa453cbe2481b6ab5fae09b254c" class="message">Added a timestamp for when a user was banned.</a>
    </div>

    <div class="participation">
      <p class="quickstat"><a href="#blob_contributors_box" rel="facebox"><strong>2</strong> contributors</a></p>
          <a class="avatar tooltipped downwards" title="bencorlett" href="/cartalyst/sentry/commits/master/schema/mysql.sql?author=bencorlett"><img height="20" src="https://secure.gravatar.com/avatar/ace9008e72a1b72cc41d697e922faf17?s=140&amp;d=https://a248.e.akamai.net/assets.github.com%2Fimages%2Fgravatars%2Fgravatar-user-420.png" width="20" /></a>
    <a class="avatar tooltipped downwards" title="bstrahija" href="/cartalyst/sentry/commits/master/schema/mysql.sql?author=bstrahija"><img height="20" src="https://secure.gravatar.com/avatar/211dbf4c6372e1ecbc531af0b747f4e6?s=140&amp;d=https://a248.e.akamai.net/assets.github.com%2Fimages%2Fgravatars%2Fgravatar-user-420.png" width="20" /></a>


    </div>
    <div id="blob_contributors_box" style="display:none">
      <h2>Users on GitHub who have contributed to this file</h2>
      <ul class="facebox-user-list">
        <li>
          <img height="24" src="https://secure.gravatar.com/avatar/ace9008e72a1b72cc41d697e922faf17?s=140&amp;d=https://a248.e.akamai.net/assets.github.com%2Fimages%2Fgravatars%2Fgravatar-user-420.png" width="24" />
          <a href="/bencorlett">bencorlett</a>
        </li>
        <li>
          <img height="24" src="https://secure.gravatar.com/avatar/211dbf4c6372e1ecbc531af0b747f4e6?s=140&amp;d=https://a248.e.akamai.net/assets.github.com%2Fimages%2Fgravatars%2Fgravatar-user-420.png" width="24" />
          <a href="/bstrahija">bstrahija</a>
        </li>
      </ul>
    </div>
  </div>


    </div><!-- ./.frame-meta -->

    <div class="frames">
      <div class="frame" data-permalink-url="/cartalyst/sentry/blob/825eca0be48ddecbb2a38b9238387943b834da69/schema/mysql.sql" data-title="sentry/schema/mysql.sql at master · cartalyst/sentry · GitHub" data-type="blob">

        <div id="files" class="bubble">
          <div class="file">
            <div class="meta">
              <div class="info">
                <span class="icon"><b class="mini-icon mini-icon-text-file"></b></span>
                <span class="mode" title="File Mode">file</span>
                  <span>87 lines (64 sloc)</span>
                <span>3.034 kb</span>
              </div>
              <div class="actions">
                <div class="button-group">
                      <a class="minibutton js-entice" href=""
                         data-entice="You must be signed in and on a branch to make or propose changes">Edit</a>
                  <a href="/cartalyst/sentry/raw/master/schema/mysql.sql" class="button minibutton " id="raw-url">Raw</a>
                    <a href="/cartalyst/sentry/blame/master/schema/mysql.sql" class="button minibutton ">Blame</a>
                  <a href="/cartalyst/sentry/commits/master/schema/mysql.sql" class="button minibutton " rel="nofollow">History</a>
                </div><!-- /.button-group -->
              </div><!-- /.actions -->

            </div>
                <div class="blob-wrapper data type-sql js-blob-data">
      <table class="file-code file-diff">
        <tr class="file-code-line">
          <td class="blob-line-nums">
            <span id="L1" rel="#L1">1</span>
<span id="L2" rel="#L2">2</span>
<span id="L3" rel="#L3">3</span>
<span id="L4" rel="#L4">4</span>
<span id="L5" rel="#L5">5</span>
<span id="L6" rel="#L6">6</span>
<span id="L7" rel="#L7">7</span>
<span id="L8" rel="#L8">8</span>
<span id="L9" rel="#L9">9</span>
<span id="L10" rel="#L10">10</span>
<span id="L11" rel="#L11">11</span>
<span id="L12" rel="#L12">12</span>
<span id="L13" rel="#L13">13</span>
<span id="L14" rel="#L14">14</span>
<span id="L15" rel="#L15">15</span>
<span id="L16" rel="#L16">16</span>
<span id="L17" rel="#L17">17</span>
<span id="L18" rel="#L18">18</span>
<span id="L19" rel="#L19">19</span>
<span id="L20" rel="#L20">20</span>
<span id="L21" rel="#L21">21</span>
<span id="L22" rel="#L22">22</span>
<span id="L23" rel="#L23">23</span>
<span id="L24" rel="#L24">24</span>
<span id="L25" rel="#L25">25</span>
<span id="L26" rel="#L26">26</span>
<span id="L27" rel="#L27">27</span>
<span id="L28" rel="#L28">28</span>
<span id="L29" rel="#L29">29</span>
<span id="L30" rel="#L30">30</span>
<span id="L31" rel="#L31">31</span>
<span id="L32" rel="#L32">32</span>
<span id="L33" rel="#L33">33</span>
<span id="L34" rel="#L34">34</span>
<span id="L35" rel="#L35">35</span>
<span id="L36" rel="#L36">36</span>
<span id="L37" rel="#L37">37</span>
<span id="L38" rel="#L38">38</span>
<span id="L39" rel="#L39">39</span>
<span id="L40" rel="#L40">40</span>
<span id="L41" rel="#L41">41</span>
<span id="L42" rel="#L42">42</span>
<span id="L43" rel="#L43">43</span>
<span id="L44" rel="#L44">44</span>
<span id="L45" rel="#L45">45</span>
<span id="L46" rel="#L46">46</span>
<span id="L47" rel="#L47">47</span>
<span id="L48" rel="#L48">48</span>
<span id="L49" rel="#L49">49</span>
<span id="L50" rel="#L50">50</span>
<span id="L51" rel="#L51">51</span>
<span id="L52" rel="#L52">52</span>
<span id="L53" rel="#L53">53</span>
<span id="L54" rel="#L54">54</span>
<span id="L55" rel="#L55">55</span>
<span id="L56" rel="#L56">56</span>
<span id="L57" rel="#L57">57</span>
<span id="L58" rel="#L58">58</span>
<span id="L59" rel="#L59">59</span>
<span id="L60" rel="#L60">60</span>
<span id="L61" rel="#L61">61</span>
<span id="L62" rel="#L62">62</span>
<span id="L63" rel="#L63">63</span>
<span id="L64" rel="#L64">64</span>
<span id="L65" rel="#L65">65</span>
<span id="L66" rel="#L66">66</span>
<span id="L67" rel="#L67">67</span>
<span id="L68" rel="#L68">68</span>
<span id="L69" rel="#L69">69</span>
<span id="L70" rel="#L70">70</span>
<span id="L71" rel="#L71">71</span>
<span id="L72" rel="#L72">72</span>
<span id="L73" rel="#L73">73</span>
<span id="L74" rel="#L74">74</span>
<span id="L75" rel="#L75">75</span>
<span id="L76" rel="#L76">76</span>
<span id="L77" rel="#L77">77</span>
<span id="L78" rel="#L78">78</span>
<span id="L79" rel="#L79">79</span>
<span id="L80" rel="#L80">80</span>
<span id="L81" rel="#L81">81</span>
<span id="L82" rel="#L82">82</span>
<span id="L83" rel="#L83">83</span>
<span id="L84" rel="#L84">84</span>
<span id="L85" rel="#L85">85</span>
<span id="L86" rel="#L86">86</span>

          </td>
          <td class="blob-line-code">
                  <div class="highlight"><pre><div class='line' id='LC1'><span class="o">#</span> <span class="n">Dump</span> <span class="k">of</span> <span class="k">table</span> <span class="n">groups</span></div><div class='line' id='LC2'><span class="o">#</span> <span class="c1">------------------------------------------------------------</span></div><div class='line' id='LC3'><br/></div><div class='line' id='LC4'><span class="k">DROP</span> <span class="k">TABLE</span> <span class="n">IF</span> <span class="k">EXISTS</span> <span class="o">`</span><span class="n">groups</span><span class="o">`</span><span class="p">;</span></div><div class='line' id='LC5'><br/></div><div class='line' id='LC6'><span class="k">CREATE</span> <span class="k">TABLE</span> <span class="o">`</span><span class="n">groups</span><span class="o">`</span> <span class="p">(</span></div><div class='line' id='LC7'>&nbsp;&nbsp;<span class="o">`</span><span class="n">id</span><span class="o">`</span> <span class="nb">int</span><span class="p">(</span><span class="mi">10</span><span class="p">)</span> <span class="n">unsigned</span> <span class="k">NOT</span> <span class="k">NULL</span> <span class="n">AUTO_INCREMENT</span><span class="p">,</span></div><div class='line' id='LC8'>&nbsp;&nbsp;<span class="o">`</span><span class="n">name</span><span class="o">`</span> <span class="nb">varchar</span><span class="p">(</span><span class="mi">255</span><span class="p">)</span> <span class="k">COLLATE</span> <span class="n">utf8_unicode_ci</span> <span class="k">NOT</span> <span class="k">NULL</span><span class="p">,</span></div><div class='line' id='LC9'>&nbsp;&nbsp;<span class="o">`</span><span class="n">permissions</span><span class="o">`</span> <span class="nb">text</span> <span class="k">COLLATE</span> <span class="n">utf8_unicode_ci</span><span class="p">,</span></div><div class='line' id='LC10'>&nbsp;&nbsp;<span class="o">`</span><span class="n">created_at</span><span class="o">`</span> <span class="k">timestamp</span> <span class="k">NOT</span> <span class="k">NULL</span> <span class="k">DEFAULT</span> <span class="s1">&#39;0000-00-00 00:00:00&#39;</span><span class="p">,</span></div><div class='line' id='LC11'>&nbsp;&nbsp;<span class="o">`</span><span class="n">updated_at</span><span class="o">`</span> <span class="k">timestamp</span> <span class="k">NOT</span> <span class="k">NULL</span> <span class="k">DEFAULT</span> <span class="s1">&#39;0000-00-00 00:00:00&#39;</span><span class="p">,</span></div><div class='line' id='LC12'>&nbsp;&nbsp;<span class="k">PRIMARY</span> <span class="k">KEY</span> <span class="p">(</span><span class="o">`</span><span class="n">id</span><span class="o">`</span><span class="p">),</span></div><div class='line' id='LC13'>&nbsp;&nbsp;<span class="k">UNIQUE</span> <span class="k">KEY</span> <span class="o">`</span><span class="n">groups_name_unique</span><span class="o">`</span> <span class="p">(</span><span class="o">`</span><span class="n">name</span><span class="o">`</span><span class="p">)</span></div><div class='line' id='LC14'><span class="p">)</span> <span class="n">ENGINE</span><span class="o">=</span><span class="n">InnoDB</span> <span class="k">DEFAULT</span> <span class="n">CHARSET</span><span class="o">=</span><span class="n">utf8</span> <span class="k">COLLATE</span><span class="o">=</span><span class="n">utf8_unicode_ci</span><span class="p">;</span></div><div class='line' id='LC15'><br/></div><div class='line' id='LC16'><br/></div><div class='line' id='LC17'><br/></div><div class='line' id='LC18'><span class="o">#</span> <span class="n">Dump</span> <span class="k">of</span> <span class="k">table</span> <span class="n">migrations</span></div><div class='line' id='LC19'><span class="o">#</span> <span class="c1">------------------------------------------------------------</span></div><div class='line' id='LC20'><br/></div><div class='line' id='LC21'><span class="k">DROP</span> <span class="k">TABLE</span> <span class="n">IF</span> <span class="k">EXISTS</span> <span class="o">`</span><span class="n">migrations</span><span class="o">`</span><span class="p">;</span></div><div class='line' id='LC22'><br/></div><div class='line' id='LC23'><span class="k">CREATE</span> <span class="k">TABLE</span> <span class="o">`</span><span class="n">migrations</span><span class="o">`</span> <span class="p">(</span></div><div class='line' id='LC24'>&nbsp;&nbsp;<span class="o">`</span><span class="n">migration</span><span class="o">`</span> <span class="nb">varchar</span><span class="p">(</span><span class="mi">255</span><span class="p">)</span> <span class="k">COLLATE</span> <span class="n">utf8_unicode_ci</span> <span class="k">NOT</span> <span class="k">NULL</span><span class="p">,</span></div><div class='line' id='LC25'>&nbsp;&nbsp;<span class="o">`</span><span class="n">batch</span><span class="o">`</span> <span class="nb">int</span><span class="p">(</span><span class="mi">11</span><span class="p">)</span> <span class="k">NOT</span> <span class="k">NULL</span></div><div class='line' id='LC26'><span class="p">)</span> <span class="n">ENGINE</span><span class="o">=</span><span class="n">InnoDB</span> <span class="k">DEFAULT</span> <span class="n">CHARSET</span><span class="o">=</span><span class="n">utf8</span> <span class="k">COLLATE</span><span class="o">=</span><span class="n">utf8_unicode_ci</span><span class="p">;</span></div><div class='line' id='LC27'><br/></div><div class='line' id='LC28'><br/></div><div class='line' id='LC29'><br/></div><div class='line' id='LC30'><span class="o">#</span> <span class="n">Dump</span> <span class="k">of</span> <span class="k">table</span> <span class="n">throttle</span></div><div class='line' id='LC31'><span class="o">#</span> <span class="c1">------------------------------------------------------------</span></div><div class='line' id='LC32'><br/></div><div class='line' id='LC33'><span class="k">DROP</span> <span class="k">TABLE</span> <span class="n">IF</span> <span class="k">EXISTS</span> <span class="o">`</span><span class="n">throttle</span><span class="o">`</span><span class="p">;</span></div><div class='line' id='LC34'><br/></div><div class='line' id='LC35'><span class="k">CREATE</span> <span class="k">TABLE</span> <span class="o">`</span><span class="n">throttle</span><span class="o">`</span> <span class="p">(</span></div><div class='line' id='LC36'>&nbsp;&nbsp;<span class="o">`</span><span class="n">id</span><span class="o">`</span> <span class="nb">int</span><span class="p">(</span><span class="mi">10</span><span class="p">)</span> <span class="n">unsigned</span> <span class="k">NOT</span> <span class="k">NULL</span> <span class="n">AUTO_INCREMENT</span><span class="p">,</span></div><div class='line' id='LC37'>&nbsp;&nbsp;<span class="o">`</span><span class="n">user_id</span><span class="o">`</span> <span class="nb">int</span><span class="p">(</span><span class="mi">10</span><span class="p">)</span> <span class="n">unsigned</span> <span class="k">NOT</span> <span class="k">NULL</span><span class="p">,</span></div><div class='line' id='LC38'>&nbsp;&nbsp;<span class="o">`</span><span class="n">ip_address</span><span class="o">`</span> <span class="nb">varchar</span><span class="p">(</span><span class="mi">255</span><span class="p">)</span> <span class="k">COLLATE</span> <span class="n">utf8_unicode_ci</span> <span class="k">DEFAULT</span> <span class="k">NULL</span><span class="p">,</span></div><div class='line' id='LC39'>&nbsp;&nbsp;<span class="o">`</span><span class="n">attempts</span><span class="o">`</span> <span class="nb">int</span><span class="p">(</span><span class="mi">11</span><span class="p">)</span> <span class="k">NOT</span> <span class="k">NULL</span> <span class="k">DEFAULT</span> <span class="s1">&#39;0&#39;</span><span class="p">,</span></div><div class='line' id='LC40'>&nbsp;&nbsp;<span class="o">`</span><span class="n">suspended</span><span class="o">`</span> <span class="n">tinyint</span><span class="p">(</span><span class="mi">4</span><span class="p">)</span> <span class="k">NOT</span> <span class="k">NULL</span> <span class="k">DEFAULT</span> <span class="s1">&#39;0&#39;</span><span class="p">,</span></div><div class='line' id='LC41'>&nbsp;&nbsp;<span class="o">`</span><span class="n">banned</span><span class="o">`</span> <span class="n">tinyint</span><span class="p">(</span><span class="mi">4</span><span class="p">)</span> <span class="k">NOT</span> <span class="k">NULL</span> <span class="k">DEFAULT</span> <span class="s1">&#39;0&#39;</span><span class="p">,</span></div><div class='line' id='LC42'>&nbsp;&nbsp;<span class="o">`</span><span class="n">last_attempt_at</span><span class="o">`</span> <span class="k">timestamp</span> <span class="k">NULL</span> <span class="k">DEFAULT</span> <span class="k">NULL</span><span class="p">,</span></div><div class='line' id='LC43'>&nbsp;&nbsp;<span class="o">`</span><span class="n">suspended_at</span><span class="o">`</span> <span class="k">timestamp</span> <span class="k">NULL</span> <span class="k">DEFAULT</span> <span class="k">NULL</span><span class="p">,</span></div><div class='line' id='LC44'>&nbsp;&nbsp;<span class="o">`</span><span class="n">banned_at</span><span class="o">`</span> <span class="k">timestamp</span> <span class="k">NULL</span> <span class="k">DEFAULT</span> <span class="k">NULL</span><span class="p">,</span></div><div class='line' id='LC45'>&nbsp;&nbsp;<span class="k">PRIMARY</span> <span class="k">KEY</span> <span class="p">(</span><span class="o">`</span><span class="n">id</span><span class="o">`</span><span class="p">)</span></div><div class='line' id='LC46'><span class="p">)</span> <span class="n">ENGINE</span><span class="o">=</span><span class="n">InnoDB</span> <span class="k">DEFAULT</span> <span class="n">CHARSET</span><span class="o">=</span><span class="n">utf8</span> <span class="k">COLLATE</span><span class="o">=</span><span class="n">utf8_unicode_ci</span><span class="p">;</span></div><div class='line' id='LC47'><br/></div><div class='line' id='LC48'><br/></div><div class='line' id='LC49'><br/></div><div class='line' id='LC50'><span class="o">#</span> <span class="n">Dump</span> <span class="k">of</span> <span class="k">table</span> <span class="n">users</span></div><div class='line' id='LC51'><span class="o">#</span> <span class="c1">------------------------------------------------------------</span></div><div class='line' id='LC52'><br/></div><div class='line' id='LC53'><span class="k">DROP</span> <span class="k">TABLE</span> <span class="n">IF</span> <span class="k">EXISTS</span> <span class="o">`</span><span class="n">users</span><span class="o">`</span><span class="p">;</span></div><div class='line' id='LC54'><br/></div><div class='line' id='LC55'><span class="k">CREATE</span> <span class="k">TABLE</span> <span class="o">`</span><span class="n">users</span><span class="o">`</span> <span class="p">(</span></div><div class='line' id='LC56'>&nbsp;&nbsp;<span class="o">`</span><span class="n">id</span><span class="o">`</span> <span class="nb">int</span><span class="p">(</span><span class="mi">10</span><span class="p">)</span> <span class="n">unsigned</span> <span class="k">NOT</span> <span class="k">NULL</span> <span class="n">AUTO_INCREMENT</span><span class="p">,</span></div><div class='line' id='LC57'>&nbsp;&nbsp;<span class="o">`</span><span class="n">email</span><span class="o">`</span> <span class="nb">varchar</span><span class="p">(</span><span class="mi">255</span><span class="p">)</span> <span class="k">COLLATE</span> <span class="n">utf8_unicode_ci</span> <span class="k">NOT</span> <span class="k">NULL</span><span class="p">,</span></div><div class='line' id='LC58'>&nbsp;&nbsp;<span class="o">`</span><span class="n">password</span><span class="o">`</span> <span class="nb">varchar</span><span class="p">(</span><span class="mi">255</span><span class="p">)</span> <span class="k">COLLATE</span> <span class="n">utf8_unicode_ci</span> <span class="k">NOT</span> <span class="k">NULL</span><span class="p">,</span></div><div class='line' id='LC59'>&nbsp;&nbsp;<span class="o">`</span><span class="n">permissions</span><span class="o">`</span> <span class="nb">text</span> <span class="k">COLLATE</span> <span class="n">utf8_unicode_ci</span><span class="p">,</span></div><div class='line' id='LC60'>&nbsp;&nbsp;<span class="o">`</span><span class="n">activated</span><span class="o">`</span> <span class="n">tinyint</span><span class="p">(</span><span class="mi">4</span><span class="p">)</span> <span class="k">NOT</span> <span class="k">NULL</span> <span class="k">DEFAULT</span> <span class="s1">&#39;0&#39;</span><span class="p">,</span></div><div class='line' id='LC61'>&nbsp;&nbsp;<span class="o">`</span><span class="n">activation_code</span><span class="o">`</span> <span class="nb">varchar</span><span class="p">(</span><span class="mi">255</span><span class="p">)</span> <span class="k">COLLATE</span> <span class="n">utf8_unicode_ci</span> <span class="k">DEFAULT</span> <span class="k">NULL</span><span class="p">,</span></div><div class='line' id='LC62'>&nbsp;&nbsp;<span class="o">`</span><span class="n">activated_at</span><span class="o">`</span> <span class="nb">varchar</span><span class="p">(</span><span class="mi">255</span><span class="p">)</span> <span class="k">COLLATE</span> <span class="n">utf8_unicode_ci</span> <span class="k">DEFAULT</span> <span class="k">NULL</span><span class="p">,</span></div><div class='line' id='LC63'>&nbsp;&nbsp;<span class="o">`</span><span class="n">last_login</span><span class="o">`</span> <span class="nb">varchar</span><span class="p">(</span><span class="mi">255</span><span class="p">)</span> <span class="k">COLLATE</span> <span class="n">utf8_unicode_ci</span> <span class="k">DEFAULT</span> <span class="k">NULL</span><span class="p">,</span></div><div class='line' id='LC64'>&nbsp;&nbsp;<span class="o">`</span><span class="n">persist_code</span><span class="o">`</span> <span class="nb">varchar</span><span class="p">(</span><span class="mi">255</span><span class="p">)</span> <span class="k">COLLATE</span> <span class="n">utf8_unicode_ci</span> <span class="k">DEFAULT</span> <span class="k">NULL</span><span class="p">,</span></div><div class='line' id='LC65'>&nbsp;&nbsp;<span class="o">`</span><span class="n">reset_password_code</span><span class="o">`</span> <span class="nb">varchar</span><span class="p">(</span><span class="mi">255</span><span class="p">)</span> <span class="k">COLLATE</span> <span class="n">utf8_unicode_ci</span> <span class="k">DEFAULT</span> <span class="k">NULL</span><span class="p">,</span></div><div class='line' id='LC66'>&nbsp;&nbsp;<span class="o">`</span><span class="n">first_name</span><span class="o">`</span> <span class="nb">varchar</span><span class="p">(</span><span class="mi">255</span><span class="p">)</span> <span class="k">COLLATE</span> <span class="n">utf8_unicode_ci</span> <span class="k">DEFAULT</span> <span class="k">NULL</span><span class="p">,</span></div><div class='line' id='LC67'>&nbsp;&nbsp;<span class="o">`</span><span class="n">last_name</span><span class="o">`</span> <span class="nb">varchar</span><span class="p">(</span><span class="mi">255</span><span class="p">)</span> <span class="k">COLLATE</span> <span class="n">utf8_unicode_ci</span> <span class="k">DEFAULT</span> <span class="k">NULL</span><span class="p">,</span></div><div class='line' id='LC68'>&nbsp;&nbsp;<span class="o">`</span><span class="n">created_at</span><span class="o">`</span> <span class="k">timestamp</span> <span class="k">NOT</span> <span class="k">NULL</span> <span class="k">DEFAULT</span> <span class="s1">&#39;0000-00-00 00:00:00&#39;</span><span class="p">,</span></div><div class='line' id='LC69'>&nbsp;&nbsp;<span class="o">`</span><span class="n">updated_at</span><span class="o">`</span> <span class="k">timestamp</span> <span class="k">NOT</span> <span class="k">NULL</span> <span class="k">DEFAULT</span> <span class="s1">&#39;0000-00-00 00:00:00&#39;</span><span class="p">,</span></div><div class='line' id='LC70'>&nbsp;&nbsp;<span class="k">PRIMARY</span> <span class="k">KEY</span> <span class="p">(</span><span class="o">`</span><span class="n">id</span><span class="o">`</span><span class="p">),</span></div><div class='line' id='LC71'>&nbsp;&nbsp;<span class="k">UNIQUE</span> <span class="k">KEY</span> <span class="o">`</span><span class="n">users_email_unique</span><span class="o">`</span> <span class="p">(</span><span class="o">`</span><span class="n">email</span><span class="o">`</span><span class="p">)</span></div><div class='line' id='LC72'><span class="p">)</span> <span class="n">ENGINE</span><span class="o">=</span><span class="n">InnoDB</span> <span class="k">DEFAULT</span> <span class="n">CHARSET</span><span class="o">=</span><span class="n">utf8</span> <span class="k">COLLATE</span><span class="o">=</span><span class="n">utf8_unicode_ci</span><span class="p">;</span></div><div class='line' id='LC73'><br/></div><div class='line' id='LC74'><br/></div><div class='line' id='LC75'><br/></div><div class='line' id='LC76'><span class="o">#</span> <span class="n">Dump</span> <span class="k">of</span> <span class="k">table</span> <span class="n">users_groups</span></div><div class='line' id='LC77'><span class="o">#</span> <span class="c1">------------------------------------------------------------</span></div><div class='line' id='LC78'><br/></div><div class='line' id='LC79'><span class="k">DROP</span> <span class="k">TABLE</span> <span class="n">IF</span> <span class="k">EXISTS</span> <span class="o">`</span><span class="n">users_groups</span><span class="o">`</span><span class="p">;</span></div><div class='line' id='LC80'><br/></div><div class='line' id='LC81'><span class="k">CREATE</span> <span class="k">TABLE</span> <span class="o">`</span><span class="n">users_groups</span><span class="o">`</span> <span class="p">(</span></div><div class='line' id='LC82'>&nbsp;&nbsp;<span class="o">`</span><span class="n">id</span><span class="o">`</span> <span class="nb">int</span><span class="p">(</span><span class="mi">10</span><span class="p">)</span> <span class="n">unsigned</span> <span class="k">NOT</span> <span class="k">NULL</span> <span class="n">AUTO_INCREMENT</span><span class="p">,</span></div><div class='line' id='LC83'>&nbsp;&nbsp;<span class="o">`</span><span class="n">user_id</span><span class="o">`</span> <span class="nb">int</span><span class="p">(</span><span class="mi">10</span><span class="p">)</span> <span class="n">unsigned</span> <span class="k">NOT</span> <span class="k">NULL</span><span class="p">,</span></div><div class='line' id='LC84'>&nbsp;&nbsp;<span class="o">`</span><span class="n">group_id</span><span class="o">`</span> <span class="nb">int</span><span class="p">(</span><span class="mi">10</span><span class="p">)</span> <span class="n">unsigned</span> <span class="k">NOT</span> <span class="k">NULL</span><span class="p">,</span></div><div class='line' id='LC85'>&nbsp;&nbsp;<span class="k">PRIMARY</span> <span class="k">KEY</span> <span class="p">(</span><span class="o">`</span><span class="n">id</span><span class="o">`</span><span class="p">)</span></div><div class='line' id='LC86'><span class="p">)</span> <span class="n">ENGINE</span><span class="o">=</span><span class="n">InnoDB</span> <span class="k">DEFAULT</span> <span class="n">CHARSET</span><span class="o">=</span><span class="n">utf8</span> <span class="k">COLLATE</span><span class="o">=</span><span class="n">utf8_unicode_ci</span><span class="p">;</span></div></pre></div>
          </td>
        </tr>
      </table>
  </div>

          </div>
        </div>

        <a href="#jump-to-line" rel="facebox" data-hotkey="l" class="js-jump-to-line" style="display:none">Jump to Line</a>
        <div id="jump-to-line" style="display:none">
          <h2>Jump to Line</h2>
          <form accept-charset="UTF-8" class="js-jump-to-line-form">
            <input class="textfield js-jump-to-line-field" type="text">
            <div class="full-button">
              <button type="submit" class="button">Go</button>
            </div>
          </form>
        </div>

      </div>
    </div>
</div>

<div id="js-frame-loading-template" class="frame frame-loading large-loading-area" style="display:none;">
  <img class="js-frame-loading-spinner" src="https://a248.e.akamai.net/assets.github.com/images/spinners/octocat-spinner-128.gif?1347543528" height="64" width="64">
</div>


        </div>
      </div>
      <div class="context-overlay"></div>
    </div>

      <div id="footer-push"></div><!-- hack for sticky footer -->
    </div><!-- end of wrapper - hack for sticky footer -->

      <!-- footer -->
      <div id="footer">
  <div class="container clearfix">

      <dl class="footer_nav">
        <dt>GitHub</dt>
        <dd><a href="https://github.com/about">About us</a></dd>
        <dd><a href="https://github.com/blog">Blog</a></dd>
        <dd><a href="https://github.com/contact">Contact &amp; support</a></dd>
        <dd><a href="http://enterprise.github.com/">GitHub Enterprise</a></dd>
        <dd><a href="http://status.github.com/">Site status</a></dd>
      </dl>

      <dl class="footer_nav">
        <dt>Applications</dt>
        <dd><a href="http://mac.github.com/">GitHub for Mac</a></dd>
        <dd><a href="http://windows.github.com/">GitHub for Windows</a></dd>
        <dd><a href="http://eclipse.github.com/">GitHub for Eclipse</a></dd>
        <dd><a href="http://mobile.github.com/">GitHub mobile apps</a></dd>
      </dl>

      <dl class="footer_nav">
        <dt>Services</dt>
        <dd><a href="http://get.gaug.es/">Gauges: Web analytics</a></dd>
        <dd><a href="http://speakerdeck.com">Speaker Deck: Presentations</a></dd>
        <dd><a href="https://gist.github.com">Gist: Code snippets</a></dd>
        <dd><a href="http://jobs.github.com/">Job board</a></dd>
      </dl>

      <dl class="footer_nav">
        <dt>Documentation</dt>
        <dd><a href="http://help.github.com/">GitHub Help</a></dd>
        <dd><a href="http://developer.github.com/">Developer API</a></dd>
        <dd><a href="http://github.github.com/github-flavored-markdown/">GitHub Flavored Markdown</a></dd>
        <dd><a href="http://pages.github.com/">GitHub Pages</a></dd>
      </dl>

      <dl class="footer_nav">
        <dt>More</dt>
        <dd><a href="http://training.github.com/">Training</a></dd>
        <dd><a href="https://github.com/edu">Students &amp; teachers</a></dd>
        <dd><a href="http://shop.github.com">The Shop</a></dd>
        <dd><a href="/plans">Plans &amp; pricing</a></dd>
        <dd><a href="http://octodex.github.com/">The Octodex</a></dd>
      </dl>

      <hr class="footer-divider">


    <p class="right">&copy; 2013 <span title="0.04507s from fe1.rs.github.com">GitHub</span>, Inc. All rights reserved.</p>
    <a class="left" href="https://github.com/">
      <span class="mega-icon mega-icon-invertocat"></span>
    </a>
    <ul id="legal">
        <li><a href="https://github.com/site/terms">Terms of Service</a></li>
        <li><a href="https://github.com/site/privacy">Privacy</a></li>
        <li><a href="https://github.com/security">Security</a></li>
    </ul>

  </div><!-- /.container -->

</div><!-- /.#footer -->


    <div class="fullscreen-overlay js-fullscreen-overlay" id="fullscreen_overlay">
  <div class="fullscreen-container js-fullscreen-container">
    <div class="textarea-wrap">
      <textarea name="fullscreen-contents" id="fullscreen-contents" class="js-fullscreen-contents" placeholder="" data-suggester="fullscreen_suggester"></textarea>
          <div class="suggester-container">
              <div class="suggester fullscreen-suggester js-navigation-container" id="fullscreen_suggester"
                 data-url="/cartalyst/sentry/suggestions/commit">
              </div>
          </div>
    </div>
  </div>
  <div class="fullscreen-sidebar">
    <a href="#" class="exit-fullscreen js-exit-fullscreen tooltipped leftwards" title="Exit Zen Mode">
      <span class="mega-icon mega-icon-normalscreen"></span>
    </a>
    <a href="#" class="theme-switcher js-theme-switcher tooltipped leftwards"
      title="Switch themes">
      <span class="mini-icon mini-icon-brightness"></span>
    </a>
  </div>
</div>



    <div id="ajax-error-message" class="flash flash-error">
      <span class="mini-icon mini-icon-exclamation"></span>
      Something went wrong with that request. Please try again.
      <a href="#" class="mini-icon mini-icon-remove-close ajax-error-dismiss"></a>
    </div>

    
    
    <span id='server_response_time' data-time='0.04550' data-host='fe1'></span>
    
  </body>
</html>

