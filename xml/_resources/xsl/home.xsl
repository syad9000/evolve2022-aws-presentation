<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE stylesheet>
<xsl:stylesheet version="3.0" 
				xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
				xmlns:xs="http://www.w3.org/2001/XMLSchema" 
				xmlns:ou="http://omniupdate.com/XSL/Variables" 
				xmlns:fn="http://omniupdate.com/XSL/Functions" 
				xmlns:ouc="http://omniupdate.com/XSL/Variables" 
				exclude-result-prefixes="ou xsl xs fn ouc">

	<xsl:import href="common.xsl"/>
	<xsl:template name="breadcrumb-nav"/>

	<xsl:template name="page-content">
		<div class="container-fluid feature">
			<div class="container">
				<div class="row">
					<div class="col-md-6 feature_blurb">
						<a href="{feature/ouc:div[@label='feature-link']}">
							<h2><xsl:value-of select="substring(feature/ouc:div[@label='feature-title'], 0, 99)"/></h2>
							<p><xsl:value-of select="substring(feature/ouc:div[@label='feature-description'], 0, 299)"/></p>
							<p>Learn more &#9656;</p>
						</a>
					</div>
					<div class="col-md-6 center-block feature_image">
						<a href="{feature/ouc:div[@label='feature-link']}">
							<img class="img-responsive" alt="{feature/ouc:div[@label='feature-image']/img/@alt}" src="{feature/ouc:div[@label='feature-image']/img/@src}" />
						</a>
					</div>
				</div>
			</div>
		</div>
		<div class="container-fluid padd-top">
			<xsl:call-template name="home-cols"/>
		</div>
	</xsl:template>

	<xsl:template name="home-cols">
		<div class="container">
			<section class="row">
				<article class="{if(ou:pcfparam('layout') = 'two-column') then 'col-md-9 left-col' else 'col-md-4 left-col'}">
					<xsl:apply-templates select="ouc:div[@label='maincontent']" />
				</article>
				<xsl:if test="ou:pcfparam('layout') = 'three-column' or ou:pcfparam('layout') = ''">
					<nav class="col-md-5 middle-col" role="navigation" aria-labelledby="events-and-announcements">
						<h2 id="events-and-announcements" class="block-title">
							<xsl:choose>
								<xsl:when test="ou:pcfparam('middle-col-hdg')!=''">
									<xsl:apply-templates select="ou:pcfparam('middle-col-hdg')" />
								</xsl:when>
								<xsl:otherwise>Events &amp; Announcements</xsl:otherwise>
							</xsl:choose>
						</h2>
						<xsl:apply-templates select="ouc:div[@label='middlecontent']" />
					</nav>
				</xsl:if>
				<nav class="col-md-3 right-col" role="navigation" aria-labelledby="home-quicklinks">
					<h2 id="home-quicklinks" class="block-title">
						<xsl:choose>
							<xsl:when test="ou:pcfparam('right-col-hdg')!=''">
								<xsl:apply-templates select="ou:pcfparam('right-col-hdg')" />
							</xsl:when>
							<xsl:otherwise>Quicklinks</xsl:otherwise>
						</xsl:choose>
					</h2>
					<xsl:apply-templates select="ouc:div[@label='rightcontent']" />
				</nav>
			</section>
		</div>		
	</xsl:template>

	
	<xsl:template name="template-footcode">
		<xsl:variable name="rss-div" select="ouc:div[@label='middlecontent']//div[@id='rss-feed-api']"/>
		<xsl:if test="$rss-div">
			<script src="/_resources/js/mustache.min.js"></script>
			<script src="/_resources/js/date-formatter.min.js"></script>
			<script id="event-feed-template" type="x-tmpl-mustache">
				<ul class="media-list">
					{{#.}}
						<li class="media">
							<div class="media-left media-date">
								<a class="media-link" href="{{link}}"><span class="media-month">{{month}}</span><span class="media-day">{{day}}</span></a>
							</div>
							<div class="media-body">
								<h3 class="media-heading"><a href="{{link}}" title="{{title}}" class="text-dark">{{title}}</a></h3>
								<p style="font-size:13px">{{start_time}}</p>
							</div>
						</li>
					{{/.}}
					{{^.}}<li class="media">No Events</li>{{/.}}
				</ul>
			</script>
			<xsl:variable name="httproot" select="if($ou:action='pub') then '' else 'https://d1hylidhy5odtq.cloudfront.net'"/>
			<script>
				/**
				* Load the news feed from the local file system
				*/
				fetch("<xsl:value-of select="$httproot"/>/_resources/rss/news.json").then(function(r){return r.json()}).then(function(news){
					var lattr = parseInt(<xsl:value-of select="$rss-div/@limit"/>);
					var limit = lattr > 0 &amp;&amp; !isNaN(lattr) ? lattr : 5;
					var target = document.querySelector("#rss-feed-api");

					news.rss.channel.item.forEach(function(event, ittr){
						var sd = new Date(event.pubDate);
						event.month = sd.format("M");
						event.day = sd.getDate();
						event.date = sd.format("Y-m-d");
						event.datetime = sd.format("Y-m-d h:i:s");
						event.start_time = parseInt(sd.format("H")) > 0 ? sd.format("h:i:s a") : "";
					});
					target.innerHTML = Mustache.render(document.querySelector("#event-feed-template").innerHTML, news.rss.channel.item.slice(0, limit));
				});
			</script>
		</xsl:if>
	</xsl:template>
	
</xsl:stylesheet>
