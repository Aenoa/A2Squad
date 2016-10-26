<?xml version='1.0' encoding='UTF-8' ?> 
<!-- was: <?xml version="1.0" encoding="ISO-8859-1"?> -->
<xsl:stylesheet
	version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:template match="text()">
	<xsl:value-of select="."/>
</xsl:template>
<xsl:template match="*">
	<xsl:apply-templates/>
</xsl:template>
<xsl:template match="/">
	<HTML>
		<HEAD>
			<TITLE> <xsl:value-of select="/squad/name" /> - A2Squad</TITLE>
			<LINK rel="stylesheet" type="text/css" href="A2Squad.css"></LINK>
			<LINK rel="shortcut icon" href="favicon.ico"></LINK>
		</HEAD>
		<BODY>
			
			<div id="header"></div>
			<div id="left_container">
				<h1>Squad Info</h1>
				<div id="centered">
					<h3>
						<xsl:value-of select="/squad/name" />
					</h3>
					<img width="256" align="center" height="256">
						<xsl:attribute name="src"><xsl:value-of select="/squad/picture" />.png</xsl:attribute>
					</img>
				<br /><br />
				<h4>Contact email:</h4>
				<a><xsl:attribute name="href">mailto:<xsl:value-of select="/squad/email" /></xsl:attribute><xsl:value-of select="/squad/email" /></a>
				<br />
				<h4>Clan website:</h4>
				<a><xsl:attribute name="href"><xsl:value-of select="/squad/web" /></xsl:attribute><xsl:value-of select="/squad/web" /></a>
				<br />
				<h4>Clan tag:</h4>
				<xsl:value-of select="/squad/title" />
				</div>
			</div>
			
			<div id="right_container">
				<h1>Members of the Squad</h1>
				<xsl:for-each select="/squad/member">
					<ul type="none" class="tainted">
						<li><xsl:value-of select="name" /></li>
						<li><a><xsl:attribute name="href">mailto:<xsl:value-of select="email" /></xsl:attribute>
						   <xsl:value-of select="email" />
						</a></li>
						<li><i><xsl:value-of select="remark" /></i></li>
					</ul>
				</xsl:for-each>
			</div>
			<div id="footer">
				<a><xsl:attribute name="href">http://dev.aenoa.net/A2Squad/</xsl:attribute>Arma 2 Squad (A2Squad)</a> by 
				<a><xsl:attribute name="href">http://aenoa.net/</xsl:attribute>Hugo '_Aenoa' Regibo</a>
				<br />
				current version: 
				<b>1.0</b> (Private, edited for <a href="http://sigclan.com/">SiG Clan</a>)
				<br />
				<a rel="license" href="http://creativecommons.org/licenses/by-nc/3.0/"><img alt="Licence Creative Commons" style="border-width:0" src="http://i.creativecommons.org/l/by-nc/3.0/80x15.png" /></a>
				
			</div>
		</BODY>
	</HTML>
	
</xsl:template>
</xsl:stylesheet>