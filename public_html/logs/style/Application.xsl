<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template match="/">
	<html>
	<head>
		<title>Application Error Log</title>
		<style type="text/css">
		body {
			font-family: Arial;
			font-size: 10px;
			width: 90%;
			margin: 2em auto;
		}
		h1 {
			color: #336699;
			font-size: 2.4em;
		}
		h2 {
			color: #336699;
			font-size: 2.0em;
		}
		a:link,
		a:visited {
			color: #6699FF;
			text-decoration: none;
			font-size: 1.4em;
		}
		a:hover,
		a:active {
			color: #336699;
		}
		table {
			width: 100%;
			margin: 10px auto;
			border: 1px solid #999999;
			font-size: 1.2em;
		}
		tr {
			margin: 1px 0;
			padding: 1px 0;
		}
		td.cTDTitle {
			font-weight: bold;
			width: 9.5%;
			background-color: #DDDDDD;
			padding: 0.25%;
			vertical-align: top;
		}
		td.cTDDetail {
			width: 83%;
			background-color: #EEEEEE;
			padding: 0.25% 1%;
		}
		a.cAExternalLink {
			display: inline-block;
			margin: 0 0 0 10px;
			padding: 0;
			font-size: 1em;
		}
		ul {
			list-style: none;
			margin: 0;
			padding: 0;
		}
		li {
			font-family: Courier;
			font-size: 10px;
			line-height: 16px;
			margin: 0;
			padding: 0;
		}
		</style>
	</head>
	<body>
		<a name="aTop"></a>
		<h1>Application Error Log</h1>
		<h2><xsl:value-of select="errors/@date"/></h2>
		<a href="#aLast">Go to last</a>
		<xsl:for-each select="errors/error">
		<table>
			<tr>
				<td class="cTDTitle">Message</td>
				<td class="cTDDetail">
					<xsl:value-of select="message"/>
				</td>
			</tr>
			<tr>
				<td class="cTDTitle">Time</td>
				<td class="cTDDetail">
					<xsl:value-of select="@time"/>
				</td>
			</tr>
			<tr>
				<td class="cTDTitle">URL</td>
				<td class="cTDDetail">
					<xsl:value-of select="url"/>
				</td>
			</tr>
			<xsl:if test="reference">
			<tr>
				<td class="cTDTitle">Reference</td>
				<td class="cTDDetail">
					<xsl:value-of select="reference"/>
					<a target="_blank" class="cAExternalLink"><xsl:attribute name="href">DatabaseErrors.xml#<xsl:value-of select="reference"/></xsl:attribute>View</a>
				</td>
			</tr>
			</xsl:if>
			<xsl:if test="trace">
			<tr>
				<td class="cTDTitle">Trace</td>
				<td class="cTDDetail">
					<ul>
					<xsl:for-each select="trace/line">
						<li>
							<xsl:value-of select="current()"/>
						</li>
					</xsl:for-each>
					</ul>
				</td>
			</tr>
			</xsl:if>
		</table>
		</xsl:for-each>
		<a name="aLast"></a>
		<a href="#aTop">Go to top</a>
	</body>
	</html>
</xsl:template>

</xsl:stylesheet>