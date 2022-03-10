<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE stylesheet [
<!ENTITY nbsp   "&#160;">
<!ENTITY lsaquo "&#8249;">
<!ENTITY rsaquo "&#8250;">
<!ENTITY laquo  "&#171;">
<!ENTITY raquo  "&#187;">
<!ENTITY copy   "&#169;">
<!ENTITY copy   "&#169;">
<!ENTITY rarr	"&#8594;">
]> 
<!-- 

Template1 common.xsl
-->
<xsl:stylesheet version="3.0" 
				xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
				xmlns:xs="http://www.w3.org/2001/XMLSchema" 
				xmlns:ou="http://omniupdate.com/XSL/Variables" 
				xmlns:fn="http://omniupdate.com/XSL/Functions" 
				xmlns:ouc="http://omniupdate.com/XSL/Variables" 
				xmlns:media="http://search.yahoo.com/mrss/"
				xmlns:functx="http://www.functx.com"
				exclude-result-prefixes="ou xsl xs fn ouc functx media">

	<xsl:import href="../_shared/ouvariables.xsl"/>
	<xsl:import href="../_shared/template-matches.xsl"/>
	<xsl:import href="../_shared/table-transformations.xsl"/>
	<xsl:import href="../_shared/functions.xsl"/>
	<xsl:import href="../_shared/functx-functions.xsl"/>
	<xsl:import href="../_shared/tag-management.xsl"/>
	<xsl:import href="../_shared/ougalleries.xsl"/>
	<xsl:import href="../_shared/ouforms.xsl"/>
	<xsl:import href="../_shared/breadcrumb.xsl"/>
	<xsl:import href="../_shared/cas.xsl"/>
	<xsl:import href="../_shared/social-meta.xsl"/>

	<xsl:import href="calendar/eits-calendar/xsl/calendar.xsl"/>
	<xsl:import href="calendar/uga-calendar/xsl/calendar.xsl"/>


	<!-- System Params - don't edit -->
	<xsl:param name="ou:action"/>
	<!-- Default: for HTML5 use below output declaration -->
	<xsl:output method="html" indent="yes" version="5.0" encoding="UTF-8" include-content-type="no"/>
	<xsl:strip-space elements="*"/>

	<xsl:template match="/document">
		<html lang="en">
			<head>
				<xsl:call-template name="common-headcode"/> <!-- from common.xsl -->
				<xsl:call-template name="template-headcode"/> <!-- xsl inheriting from common.xsl -->
				<xsl:apply-templates select="headcode/node()"/> <!-- pcf -->
			</head>
			<body>
				<xsl:call-template name="common-bodycode"/> <!-- from common.xsl -->
				<xsl:apply-templates select="bodycode/node()"/> <!-- pcf -->
				<!-- Page Structure -->
				<xsl:call-template name="common-header"/> <!-- from common.xsl -->
				<main id="maincontent" role="main">
					<xsl:call-template name="page-content"/> <!-- xsl inheriting from common.xsl -->
				</main>
				<xsl:call-template name="common-footer"/> <!-- from common.xsl -->
				<!-- Javascript -->
				<xsl:call-template name="common-footcode"/> <!-- from common.xsl -->
				<xsl:call-template name="template-footcode"/> <!-- xsl inheriting from common.xsl -->
				<xsl:apply-templates select="footcode/node()"/> <!-- pcf -->
			</body>
		</html>
	</xsl:template>

	<xsl:template name="common-headcode">
		<meta charset="UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<xsl:if test="normalize-space(ou:pcfparam('destination')) != ''">
			 <meta http-equiv="refresh" content="0; URL={ou:pcfparam('destination')}"/>
		</xsl:if>
		<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
		<xsl:call-template name="social-meta"/>
		<title>
			<xsl:call-template name="page-title"/>
		</title>
		<xsl:call-template name="headcode-css"/>
		<xsl:call-template name="headcode-js"/>
		<!-- Select lists header code -->
		<xsl:if test="$ou:action = 'edt'">
			<link rel="stylesheet" href="https://cdn.omniupdate.com/select-lists/v1/select-lists.min.css"/>
			<link rel="stylesheet" href="//cdn.omniupdate.com/table-separator/v1/table-separator.css" />
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="headcode-css">
		<link rel="stylesheet" href="/_resources/css/global.css" />
		<link rel="stylesheet" href="/_resources/css/style.css" />
	</xsl:template>
	
	<xsl:template name="headcode-js">
		<!-- Google Tag Manager -->
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
			new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
			j=d.createElement(s),dl=l!='dataLayer'?'&amp;l='+l:'';j.async=true;j.src=
			'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
			})(window,document,'script','dataLayer','GTM-KG2W2D8');</script>
		<!-- End Google Tag Manager -->
		<xsl:call-template name="form-headcode"/> <!-- Add Form Code -->
		<xsl:call-template name="calendar-headcode"/>
		<xsl:call-template name="ugacalendar-headcode" />
		<xsl:apply-templates select="ou:gallery-headcode(ou:pcfparam('gallery-type'))"/>
	</xsl:template>
	
	<!-- Fallback if there are no templates defined -->
	<xsl:template name="template-headcode"/>
	<xsl:template name="template-bodycode"/>
	<xsl:template name="template-footcode"/>

	<xsl:template name="page-content"/>

	<xsl:template name="redirect"/>

	

	<xsl:template name="common-bodycode"/>

	<xsl:template name="common-header">
		<a class="sr-only sr-only-focusable" href="#maincontent">Skip to main content</a>
		<header role="banner" id="page-header">
			<xsl:call-template name="uga-header"/>
			<div class="site-header container">
				<div class="row">
					<div class="logo col-md-6 col-sm-12">
						<a href="/">
							<img xmlns:svg="http://www.w3.org/2000/svg" class="img-responsive" src="/_resources/images/logos/PBK-Logo-Horz-Web.jpg" alt="Phi Beta Kappa at UGA" />
						</a>
					</div>
					<nav class="search-menu col-md-6 col-sm-12" role="navigation" aria-label="Site Header Navigation">
						<div class="navbar-left">
							<div class="header-searchbox-links">
								<ul class="nav">
									<li>
										<a href="/contact.html">
											Contact Us
										</a>
									</li>
									<li>
										<a href="/about-pbk.html">
											About Us
										</a>
									</li>
								</ul>
							</div>
						</div>
						<div class="custom-search navbar-right">
							<form accept-charset="UTF-8" action="/search" method="get">
								<div class="input-group">
									<label for="search-query" class="sr-only sr-only-focusable">Search by keyword(s)</label>
									<input id="search-query" type="text" name="q" class="form-control" placeholder="search by keyword(s)"/>
									<span class="input-group-btn">
										<button id="search-button" type="submit" class="btn btn-default">
											<svg xmlns="http://www.w3.org/2000/svg" class="bi bi-search" viewBox="0 0 16 16" fill="currentColor" role="img" focusable="false" width="16" height="16">
												<title>Search</title>
												<path fill-rule="evenodd" d="M10.442 10.442a1 1 0 0 1 1.415 0l3.85 3.85a1 1 0 0 1-1.414 1.415l-3.85-3.85a1 1 0 0 1 0-1.415z"></path>
												<path fill-rule="evenodd" d="M6.5 12a5.5 5.5 0 1 0 0-11 5.5 5.5 0 0 0 0 11zM13 6.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0z"></path>
											</svg>
											<span class="sr-only sr-only-focusable">Search</span>
										</button>
									</span>
								</div>
							</form>
						</div>
					</nav>
				</div>
			</div>
		</header>

		<nav id="dept-nav" class="navbar navbar-default navbar-bulldog-red" aria-label="Helpful links" role="navigation">
			<xsl:copy-of select="parse-xml-fragment(unparsed-text($ou:root || $ou:site || '/_resources/includes/dept-nav.inc'))"/>
		</nav>
	</xsl:template>

	<xsl:template name="uga-header">
		<nav id="uga-header" class="d1 navbar-default bg-transparent" aria-label="Helpful links" role="navigation">
			<div class="container-fluid quicklinks-container">
				<div class="container">
					<div id="quick-links" class="row collapse">
						<div class="col-md-3 col-sm-6 col-xs-12">
							<dt>About UGA</dt>
							<dl>
								<dd><a href="http://visit.uga.edu/">Visit UGAs</a></dd>
								<dd><a href="http://ovpi.uga.edu/about/instruction-units/#collapse9">Extended Campuses</a></dd>
								<dd><a href="http://www.architects.uga.edu/maps/current">Campus Map</a></dd>
								<dd><a href="http://calendar.uga.edu/">Master Calendar</a></dd>
								<dd><a href="http://www.hr.uga.edu/employees/employment">Employment</a></dd>
							</dl>
						</div>
						<div class="col-md-3 col-sm-6 col-xs-12">
							<dt>Student Links</dt>
							<dl>
								<dd><a href="https://my.uga.edu">MyUGA</a></dd>
								<dd><a href="https://athena.uga.edu/">Athena</a></dd>
								<dd><a href="https://www.elc.uga.edu/">eLearning Commons</a></dd>
								<dd><a href="http://www.bulletin.uga.edu/">UGA Bulletin</a></dd>
								<dd><a href="http://www.reg.uga.edu/calendars">Academic Calendars</a></dd>
							</dl>
						</div>
						<div class="col-md-3 col-sm-6 col-xs-12">
							<dt>Campus Life</dt>
							<dl>
								<dd><a href="http://ugamail.uga.edu/">UGA Mail</a></dd>
								<dd><a href="http://foodservice.uga.edu/">Food Services</a></dd>
								<dd><a href="http://dos.uga.edu/">Campus Life</a></dd>
								<dd><a href="http://www.recsports.uga.edu/">Recreational Sports</a></dd>
								<dd><a href="https://studentacct.uga.edu">Student Accounts</a></dd>
							</dl>
						</div>
						<div class="col-md-3 col-sm-6 col-xs-12">
							<dt>Helpful Links</dt>
							<dl>
								<dd><a href="http://osfa.uga.edu/">Financial Aid</a></dd>
								<dd><a href="http://international.uga.edu/">International Education</a></dd>
								<dd><a href="http://honors.uga.edu/">Honors Program</a></dd>
								<dd><a href="http://www.libs.uga.edu/">Library</a></dd>
							</dl>
						</div>
					</div>
				</div>
			</div>
			<div class="container-fluid">
				<div class="toplinks container">
					<div class="navbar-header"><button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#quicklinks-nav"><span class="sr-only">Toggle navigation</span><span aria-hidden="true" class="caret"></span></button><a class="navbar-brand uga-brand" href="https://www.uga.edu/"><img src="/_resources/images/header/GEORGIA-HW-1CB.svg" alt="University of Georgia"/></a></div>
						<div class="navbar-right">
							<div class="quicklinks-right">
								<ul id="quicklinks-nav" class="quicklinks-nav collapse navbar-collapse" aria-label="Header Links">
									<li class="quicklinks-nav-item"><a class="quicklinks-nav-link" href="https://news.uga.edu/">News</a></li>
									<li class="quicklinks-nav-item"><a class="quicklinks-nav-link" href="https://calendar.uga.edu/">Calendar</a></li>
									<li class="quicklinks-nav-item"><a id="quicklinks-nav-heading" class="accordion-toggle" role="button" data-toggle="collapse" href="#quick-links" aria-expanded="false" aria-controls="quick-links" aria-haspopup="true">UGA Links<span aria-hidden="true" class="caret"></span></a></li>
									<li class="quicklinks-nav-item quicklinks-nav-search">
										<form class="navbar-form navbar-left" action="https://uga.edu/search.php">
											<div class="form-group search-button-group"><button type="button" class="btn btn-search" data-toggle="collapse" href="#ugasearch-button" aria-expanded="false" aria-controls="ugasearch-button" aria-haspopup="true">
												<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" class="bi bi-search" fill="currentColor" aria-hidden="true" focusable="false" role="img">
													<path fill-rule="evenodd" d="M10.442 10.442a1 1 0 0 1 1.415 0l3.85 3.85a1 1 0 0 1-1.414 1.415l-3.85-3.85a1 1 0 0 1 0-1.415z"></path>
													<path fill-rule="evenodd" d="M6.5 12a5.5 5.5 0 1 0 0-11 5.5 5.5 0 0 0 0 11zM13 6.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0z"></path>
												</svg></button>
												<div id="ugasearch-button" class="search-button collapse">
													<label for="uga-search" class="sr-only sr-only-focusable">Search UGA Sites</label>
													<input type="text" id="uga-search" class="form-control" name="q" placeholder="Search UGA Sites" autocomplete="admissions"/>
													<button type="submit" class="btn sr-only">Submit</button>
												</div>
												</div>
												</form>
											</li>
										</ul>
									</div>
							</div>
						</div>
					</div>
					</nav>
	</xsl:template>
	
	<xsl:template name="common-footer">
		<footer id="page-footer" role="contentinfo">
			<div class="dept-footer container">
				<div class="col-sm-12 footer-columns" style="border-top: 1px solid #9EA2A2;">
					<div class="row">
						<div class="col-sm-6 col-xs-12 footer-center"><strong>Location</strong><address>Phi Beta Kappa<br />Office of Academic Programs<br /><span>University of Georgia</span><br />Administration Building<br />Athens, Georgia&nbsp;<span>30602-1651<br /><br /></span></address></div>
						<div class="col-sm-6 col-xs-12 footer-right">
							<div class="row">
								<div class="col-xs-12"><strong>Contact Information</strong></div>
								<div class="col-xs-12"><a title="Contact Phi Beta Kappa" href="mailto:phibetakappa@uga.edu">Email Us<br /></a><a title="Contact Phi Beta Kappa" href="tel:7065420383">(706) 542-0383</a></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="container-fluid breadcrumbs">
				<div class="container">
					<div class="row">
						<div class="col">
							<xsl:call-template name="breadcrumb-nav"/>
						</div>
					</div>
				</div>
			</div>
			<div class="ugafooter">
				<div class="ugafooter__container">
					<div class="ugafooter__row ugafooter__row--primary">                    
						<div class="ugafooter__logo">
							<a class="ugafooter__logo-link" href="https://www.uga.edu/">University of Georgia</a>
						</div>
						<nav class="ugafooter__links">
							<ul class="ugafooter__links-list">
								<li class="ugafooter__links-list-item">
									<a class="ugafooter__links-list-link" href="https://www.uga.edu/a-z/schools/">Schools and Colleges</a>
								</li>
								<li class="ugafooter__links-list-item">
									<a class="ugafooter__links-list-link" href="https://peoplesearch.uga.edu/">Directory</a>
								</li>
								<li class="ugafooter__links-list-item">
									<a class="ugafooter__links-list-link" href="https://my.uga.edu/">MyUGA</a>
								</li>
								<li class="ugafooter__links-list-item">
									<a class="ugafooter__links-list-link" href="http://hr.uga.edu/applicants/">Employment Opportunities</a>
								</li>
								<li class="ugafooter__links-list-item">
									<a class="ugafooter__links-list-link" href="https://mc.uga.edu/policy/trademark">Copyright and Trademarks</a>
								</li>
								<li class="ugafooter__links-list-item">
									<a class="ugafooter__links-list-link" href="https://eits.uga.edu/access_and_security/infosec/pols_regs/policies/privacy/">Privacy</a>
								</li>
							</ul>
						</nav>
					</div>
					<div class="ugafooter__row ugafooter__row--secondary">
						<nav class="ugafooter__social">
							<span class="ugafooter__social-label">#UGA on</span>
							<a class="ugafooter__social-link" aria-label="UGA on Facebook" href="https://www.facebook.com/universityofga/">
								<i class="fab fa-fw fa-facebook-f" title="Facebook" aria-hidden="true"></i>
							</a>
							<a class="ugafooter__social-link" aria-label="UGA on Twitter" href="https://twitter.com/universityofga">
								<i class="fab fa-fw fa-twitter" title="Twitter" aria-hidden="true"></i>
							</a>
							<a class="ugafooter__social-link" aria-label="UGA on Instagram" href="https://www.instagram.com/universityofga/">
								<i class="fab fa-fw fa-instagram" title="Instagram" aria-hidden="true"></i>
							</a>
							<a class="ugafooter__social-link" aria-label="UGA on Snapchat" href="https://www.snapchat.com/add/university-ga">
								<i class="fab fa-fw fa-snapchat-ghost" title="Snapchat" aria-hidden="true"></i>
							</a>
							<a class="ugafooter__social-link" aria-label="UGA on YouTube" href="https://www.youtube.com/user/UniversityOfGeorgia">
								<i class="fab fa-fw fa-youtube" title="YouTube" aria-hidden="true"></i>
							</a>
							<a class="ugafooter__social-link" aria-label="UGA on LinkedIn" href="https://www.linkedin.com/school/university-of-georgia/">
								<i class="fab fa-fw fa-linkedin-in" title="LinkedIn" aria-hidden="true"></i>
							</a>
						</nav>
						<div class="ugafooter__address">&#169; University of Georgia, Athens, GA 30602<br/>706&#8209;542&#8209;3000</div>
					</div>
				</div>
			</div>
		</footer>
	</xsl:template>

	<xsl:template name="common-footcode">
		<script src="/_resources/js/scripts.js"></script>
		<xsl:call-template name="form-footcode"/><!-- Add Form Code -->
		<xsl:call-template name="ugacalendar-footcode" />
		<xsl:call-template name="contact-form-footcode"/>
		<xsl:apply-templates select="ou:gallery-footcode(ou:pcfparam('gallery-type'))"/>
		<xsl:call-template name="calendar-footcode"/>
		<!-- Select lists footer code -->
		<xsl:if test="$ou:action = 'edt'">
			<!-- Jquery 1.5+ required.  Add here if not already using -->
			<script src="https://cdn.omniupdate.com/select-lists/v1/select-lists.min.js" defer="defer"></script>
			<script src="//cdn.omniupdate.com/table-separator/v1/table-separator.js" defer="defer"></script>
		</xsl:if>
	</xsl:template>

	
	<xsl:template name="contact-form-footcode">
		<xsl:if test="ouc:div[@label='maincontent']//form[@id='contactForm']">
			<xsl:variable name="settings-file" select="'/_resources/_admin/settings.json'"/>
			<!-- Insert Javascript to handle the form post -->
			<script>
				var contactFormParams = { destinationURL : "<xsl:value-of select="ou:settings-from-file($settings-file, 'contactFormSubmitURL')"/>" };
			</script>
			<script src="/_resources/js/contact-form.js"></script>
		</xsl:if>
	</xsl:template>

	<xsl:template name="page-title">
		<xsl:value-of select="$page-title || ' | ' || ou:settings('sitename')"/>
	</xsl:template>

</xsl:stylesheet>
