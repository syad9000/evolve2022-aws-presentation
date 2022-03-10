<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE stylesheet[
<!ENTITY nbsp   "&#160;">
<!ENTITY lsaquo "&#8249;">
<!ENTITY rsaquo "&#8250;">
<!ENTITY laquo  "&#171;">
<!ENTITY raquo  "&#187;">
<!ENTITY copy   "&#169;">
<!ENTITY rarr   "&#8594;">
]>
<!-- 
NEWS LISTING
A complex page type.
-->
<xsl:stylesheet version="3.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:ou="http://omniupdate.com/XSL/Variables" xmlns:fn="http://omniupdate.com/XSL/Functions" xmlns:ouc="http://omniupdate.com/XSL/Variables" exclude-result-prefixes="ou xsl xs fn ouc">
	<xsl:import href="../common.xsl"/>	
	<xsl:import href="../../navigation/sidenav.xsl"/>

	<xsl:template name="page-content">
		<div class="container">
			<div class="row">
				<div id="main-column" class="col-xs-12 {if (ou:pcfparam('layout') = 'two-column') then 'col-md-8' else 'col-md-12'}">
					<!-- 
					<xsl:choose>
						<xsl:when test="$ou:action = 'pub'">
							
							<xsl:processing-instruction name="php">
								$options = [];
								$options["feed"] = $_SERVER["DOCUMENT_ROOT"] . "<xsl:value-of select="$feed"/>";
								
								$options["limit"] = <xsl:value-of select="$limit" />;
								$options["images"] = "false";
								$options["dates"] = "true";
								$options["dateFormat"] = "F d, Y"; 
								$options["description"] = "false";
								$options["pagination"] = "<xsl:value-of select="$pagination"/>";
								$options["listStyle"] = "<xsl:value-of select="$list-style"/>";
								require_once( $_SERVER['DOCUMENT_ROOT'] . "/_resources/php/news.php");
								?</xsl:processing-instruction>
						</xsl:when>
						<xsl:otherwise>
							<div class="alert alert-info">
								<h4>News items will be displayed on publish.</h4>
								<p><strong>Feed: </strong><xsl:value-of select="concat($domain, $feed)" /></p>
								<p><strong>Limit: </strong><xsl:value-of select="$limit" /></p>
								<p><strong>List Style: </strong><xsl:value-of select="$list-style => replace('-', ' ') => ou:capital()" /></p>
								<p><strong>Pagination displayed: </strong><xsl:value-of select="$pagination"/></p>
							</div>
						</xsl:otherwise>
					</xsl:choose>
-->
				</div>
				<xsl:variable name="feed" select="if (ou:pcfparam('rss-feed') != '') then ou:pcfparam('rss-feed') else $ou:feed"/>
				<xsl:variable name="limit" select="if (ou:pcfparam('rss-limit') != '') then ou:pcfparam('rss-limit') else 5" />
				<xsl:variable name="pagination" select="if (ou:pcfparam('rss-pagination') != '') then ou:pcfparam('rss-pagination') else 'false'" />
				<xsl:variable name="tags" select="ou:get-page-tags()/tag/name"/>
				<xsl:variable name="query-string" select="$feed || '&amp;limit=' || $limit || '&amp;pagination=' || $pagination || '&amp;tags=' || $tags"/>
				<xsl:choose>
					<xsl:when test="$ou:action = 'pub'">
						<xsl:try>
							<script>
								(function(){
								var elem = document.querySelector("#main-column");
								elem.innerHTML = '<p><img src="/_resources/images/template/loading.gif" /></p>';
								fetch( "<xsl:value-of select="$query-string"/>" ).then(function(t){return t.text()}).then(function(resp){
								elem.innerHTML = resp;
								});
								})();
							</script>
							<xsl:catch>
								<div class="alert alert-warning">Feed Designed for use with an API</div>
							</xsl:catch>
						</xsl:try>

					</xsl:when>
					<xsl:otherwise>
						<div class="alert alert-info">News items will display on publish</div>
					</xsl:otherwise>
				</xsl:choose>				
				<xsl:call-template name="sidenav"/>
			</div>
		</div>
	</xsl:template>
	
</xsl:stylesheet>
