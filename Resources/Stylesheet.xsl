<?xml version="1.0" encoding="utf-8"?>
<!--
* Simple OAI-PMH 2.0 Data Provider
* Copyright (C) 2006 Christopher Gutteridge <cjg@ecs.soton.ac.uk>
* Copyright (C) 2017 Sebastian Meyer <sebastian.meyer@opencultureconsulting.com>
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:oai="http://www.openarchives.org/OAI/2.0/">

  <xsl:output method="html" />

  <xsl:template name="style">
    * {
      font-family: "Lucida Sans Unicode", sans-serif;
    }
    body {
      margin: 1em 2em 1em 2em;
    }
    h1,
    h2,
    h3,
    h4 {
      clear: left;
    }
    h1 {
      padding-bottom: 4px;
      margin-bottom: 0px;
    }
    h2 {
      margin-bottom: 0.5em;
    }
    h3 {
      margin-bottom: 0.3em;
      font-size: medium;
    }
    h4 {
      margin-bottom: 0.2em;
      font-size: small;
    }
    table {
      margin-top: 10px;
    }
    td.key {
      background-color: #e0e0ff;
      padding: 3px;
      text-align: right;
      border: 1px solid #c0c0c0;
      white-space: nowrap;
      vertical-align: top;
    }
    td.value {
      vertical-align: center;
      padding-left: 1em;
      padding: 3px;
    }
    .link {
      border: 1px outset #88f;
      background-color: #c0c0ff;
      padding: 1px 4px;
      font-size: 80%;
      text-decoration: none;
      color: black;
    }
    .link:hover {
      color: gray;
    }
    .link:active {
      color: red;
      border: 1px inset #88f;
      background-color: #a0a0df;
    }
    .results {
      margin-bottom: 1.5em;
    }
    div.quicklinks {
      border-bottom: 2px solid #ccc;
      border-top: 2px solid #ccc;
    }
    ul {
      margin: 2px 0;
      padding: 4px;
      text-align: left;
      clear: left;
    }
    ul li {
      font-size: 80%;
      display: inline;
      list-style: none;
    }
    ol {
      padding: 0;
    }
    ol>li {
      list-style: none;
      padding: 0 5px 5px;
      margin: 0 0 1em;
      border: 1px solid #c0c0c0;
    }
    p {
      margin: 0;
      padding: 5px;
    }
    p.info {
      font-size: 80%;
    }
    .xmlSource {
      font-size: 70%;
      border: solid #c0c0a0 1px;
      background-color: #ffffe0;
      padding: 2em 2em 2em 0;
    }
    .xmlBlock {
      padding-left: 2em;
    }
    .xmlTagName {
      color: #800000;
      font-weight: bold;
    }
    .xmlAttrName {
      font-weight: bold;
    }
    .xmlAttrValue {
      color: #0000c0;
    }
  </xsl:template>

  <xsl:variable name='verb' select="/oai:OAI-PMH/oai:request/@verb"/>
  <xsl:variable name='metadataPrefix'>
    <xsl:choose>
      <xsl:when test="/oai:OAI-PMH/oai:request/@metadataPrefix != ''">
        <xsl:value-of select="/oai:OAI-PMH/oai:request/@metadataPrefix"/>
      </xsl:when>
      <xsl:when test="/oai:OAI-PMH/oai:request/@resumptionToken != ''">
        <xsl:value-of select="substring-after(/oai:OAI-PMH/oai:request/@resumptionToken,'_')"/>
      </xsl:when>
    </xsl:choose>
  </xsl:variable>
  <xsl:variable name='identifier' select="/oai:OAI-PMH/oai:request/@identifier"/>
  <xsl:variable name='from' select="/oai:OAI-PMH/oai:request/@from"/>
  <xsl:variable name='until' select="/oai:OAI-PMH/oai:request/@until"/>
  <xsl:variable name='resumptionToken' select="/oai:OAI-PMH/oai:request/@resumptionToken"/>

  <xsl:template match="/">
    <html>
      <head>
        <title>OAI-PMH 2.0 Request Results</title>
        <style><xsl:call-template name="style"/></style>
      </head>
      <body>
        <h1>OAI-PMH 2.0 Request Results</h1>
        <xsl:call-template name="quicklinks"/>
        <xsl:apply-templates select="/oai:OAI-PMH"/>
        <xsl:call-template name="quicklinks"/>
        <p class="info">You are viewing an HTML version of the XML OAI-PMH response. To see the underlying XML as it appears to any OAI-PMH harvester use your web browser's <em>view source</em> option or disable XSLT processing.</p>
        <p class="info">This XSL script was originally written by Christopher Gutteridge at <a href="https://www.southampton.ac.uk/">University of Southampton</a> for the <a href="https://www.eprints.org/">EPrints</a> project and was later adapted by Sebastian Meyer at <a href="https://www.opencultureconsulting.com/">Open Culture Consulting</a> to be more generally applicable to other OAI-PMH interfaces. It is available on <a href="https://github.com/opencultureconsulting/simple-oai-pmh">GitHub</a> for free!</p>
      </body>
    </html>
  </xsl:template>

  <xsl:template name="quicklinks">
    <div class="quicklinks">
      <ul>
        <li>&#187; <a class="link" href="?verb=Identify">Identify</a></li>
        <li>&#187; <a class="link" href="?verb=ListMetadataFormats">ListMetadataFormats</a></li>
        <xsl:if test="$identifier">
          <li>&#187; <a class="link" href="?verb=ListMetadataFormats&amp;identifier={$identifier}">ListMetadataFormats (<em><xsl:value-of select="$identifier"/></em>)</a></li>
        </xsl:if>
        <xsl:if test="$metadataPrefix != ''">
          <li>&#187; <a class="link" href="?verb=ListIdentifiers&amp;metadataPrefix={$metadataPrefix}">ListIdentifiers (<em><xsl:value-of select="$metadataPrefix"/></em>)</a></li>
          <li>&#187; <a class="link" href="?verb=ListRecords&amp;metadataPrefix={$metadataPrefix}">ListRecords (<em><xsl:value-of select="$metadataPrefix"/></em>)</a></li>
          <xsl:if test="$identifier">
            <li>&#187; <a class="link" href="?verb=GetRecord&amp;metadataPrefix={$metadataPrefix}&amp;identifier={$identifier}">GetRecord (<em><xsl:value-of select="$identifier"/></em> in <em><xsl:value-of select="$metadataPrefix"/></em>)</a></li>
          </xsl:if>
        </xsl:if>
        <xsl:if test="//oai:resumptionToken">
          <li>&#187; <a class="link" href="?verb={$verb}&amp;resumptionToken={//oai:resumptionToken}">Resume</a></li>
        </xsl:if>
      </ul>
    </div>
  </xsl:template>

  <xsl:template match="/oai:OAI-PMH">
    <table class="values">
      <tr><td class="key">Datestamp of Response</td>
      <td class="value"><xsl:value-of select="oai:responseDate"/></td></tr>
      <tr><td class="key">Request URL</td>
      <td class="value"><xsl:value-of select="oai:request"/></td></tr>
      <tr><td class="key">Request Parameters</td>
      <td class="value">
        <xsl:if test="oai:request/@verb">verb = <em><xsl:value-of select="$verb"/></em><br/></xsl:if>
        <xsl:if test="oai:request/@metadataPrefix">metadataPrefix = <em><xsl:value-of select="$metadataPrefix"/></em><br/></xsl:if>
        <xsl:if test="oai:request/@identifier">identifier = <em><xsl:value-of select="$identifier"/></em><br/></xsl:if>
        <xsl:if test="oai:request/@from">from = <em><xsl:value-of select="$from"/></em><br/></xsl:if>
        <xsl:if test="oai:request/@until">until = <em><xsl:value-of select="$until"/></em><br/></xsl:if>
        <xsl:if test="oai:request/@resumptionToken">resumptionToken = <em><xsl:value-of select="$resumptionToken"/></em><br/></xsl:if>
      </td></tr>
    </table>
    <xsl:choose>
      <xsl:when test="oai:error">
        <h2>Error</h2>
        <p>The request could not be completed due to the following error.</p>
        <div class="results">
          <xsl:apply-templates select="oai:error"/>
        </div>
      </xsl:when>
      <xsl:otherwise>
        <h2><xsl:value-of select="$verb"/></h2>
        <p>The request was completed with the following results.</p>
        <div class="results">
          <xsl:apply-templates select="oai:Identify" />
          <xsl:apply-templates select="oai:ListMetadataFormats"/>
          <xsl:apply-templates select="oai:ListIdentifiers"/>
          <xsl:apply-templates select="oai:ListRecords"/>
          <xsl:apply-templates select="oai:GetRecord"/>
        </div>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <!--
  Error
-->
<xsl:template match="oai:error">
  <table class="values">
    <tr><td class="key">Error Code</td>
    <td class="value"><xsl:value-of select="@code"/></td></tr>
  </table>
  <p class="error"><xsl:value-of select="."/></p>
</xsl:template>

<!--
Identify
-->
<xsl:template match="oai:Identify">
  <ol>
    <li>
      <h3>Repository Identification</h3>
      <table class="values">
        <tr><td class="key">Name</td>
        <td class="value"><xsl:value-of select="oai:repositoryName"/></td></tr>
        <tr><td class="key">Base URL</td>
        <td class="value"><a href="{oai:baseURL}"><xsl:value-of select="oai:baseURL"/></a></td></tr>
        <tr><td class="key">Protocol Version</td>
        <td class="value"><xsl:value-of select="oai:protocolVersion"/></td></tr>
        <tr><td class="key">Earliest Datestamp</td>
        <td class="value"><xsl:value-of select="oai:earliestDatestamp"/></td></tr>
        <tr><td class="key">Deleted Record Policy</td>
        <td class="value"><xsl:value-of select="oai:deletedRecord"/></td></tr>
        <tr><td class="key">Granularity</td>
        <td class="value"><xsl:value-of select="oai:granularity"/></td></tr>
        <tr><td class="key">Administrative Email</td>
        <td class="value"><a href="mailto:{oai:adminEmail}"><xsl:value-of select="oai:adminEmail"/></a></td></tr>
      </table>
    </li>
  </ol>
</xsl:template>

<!--
ListMetadataFormats
-->
<xsl:template match="oai:ListMetadataFormats">
  <xsl:choose>
    <xsl:when test="$identifier">
      <p class="info">This is a list of metadata formats available for the record <em><xsl:value-of select="$identifier"/></em>.</p>
    </xsl:when>
    <xsl:otherwise>
      <p class="info">This is a list of metadata formats available from this repository.</p>
    </xsl:otherwise>
  </xsl:choose>
  <ol>
    <xsl:apply-templates select="oai:metadataFormat"/>
  </ol>
</xsl:template>

<!--
Metadata Format Details
-->
<xsl:template match="oai:metadataFormat">
  <li>
    <h3>Metadata Format <em><xsl:value-of select="oai:metadataPrefix"/></em></h3>
    <ul>
      <li>&#187; <a class="link" href="?verb=ListIdentifiers&amp;metadataPrefix={oai:metadataPrefix}">ListIdentifiers</a></li>
      <li>&#187; <a class="link" href="?verb=ListRecords&amp;metadataPrefix={oai:metadataPrefix}">ListRecords</a></li>
      <xsl:if test="$identifier"><li>&#187; <a class="link" href="?verb=GetRecord&amp;metadataPrefix={oai:metadataPrefix}&amp;identifier={$identifier}">GetRecord</a></li></xsl:if>
    </ul>
    <table class="values">
      <tr><td class="key">Prefix</td>
      <td class="value"><xsl:value-of select="oai:metadataPrefix"/></td></tr>
      <tr><td class="key">Namespace</td>
      <td class="value"><xsl:value-of select="oai:metadataNamespace"/></td></tr>
      <tr><td class="key">Schema</td>
      <td class="value"><a href="{oai:schema}"><xsl:value-of select="oai:schema"/></a></td></tr>
    </table>
  </li>
</xsl:template>

<!--
ListIdentifiers
-->
<xsl:template match="oai:ListIdentifiers">
  <p class="info">This is a list of records' identifiers available for the metadata format <em><xsl:value-of select="$metadataPrefix"/></em>.</p>
  <ol>
    <xsl:apply-templates select="oai:header" />
  </ol>
  <xsl:apply-templates select="oai:resumptionToken" />
</xsl:template>

<xsl:template match="oai:ListIdentifiers/oai:header">
  <li>
    <h3>Record Header <em><xsl:value-of select="oai:identifier"/></em></h3>
    <ul>
      <li>&#187; <a class="link" href="?verb=ListMetadataFormats&amp;identifier={oai:identifier}">ListMetadataFormats</a></li>
      <li>&#187; <a class="link" href="?verb=GetRecord&amp;metadataPrefix={$metadataPrefix}&amp;identifier={oai:identifier}">GetRecord</a></li>
    </ul>
    <table class="values">
      <tr><td class="key">Identifier</td>
      <td class="value"><xsl:value-of select="oai:identifier"/></td></tr>
      <tr><td class="key">Datestamp</td>
      <td class="value"><xsl:value-of select="oai:datestamp"/></td></tr>
      <tr><td class="key">Deleted</td>
      <td class="value">
        <xsl:choose>
          <xsl:when test="@status = 'deleted'">yes</xsl:when>
          <xsl:otherwise>no</xsl:otherwise>
        </xsl:choose>
      </td></tr>
    </table>
  </li>
</xsl:template>

<!--
ListRecords
-->
<xsl:template match="oai:ListRecords">
  <p class="info">This is a list of records available for the metadata format <em><xsl:value-of select="$metadataPrefix"/></em>.</p>
  <ol>
    <xsl:apply-templates select="oai:record" />
  </ol>
  <xsl:apply-templates select="oai:resumptionToken" />
</xsl:template>

<!--
GetRecord
-->
<xsl:template match="oai:GetRecord">
  <p class="info">This is the record <em><xsl:value-of select="$identifier"/></em> in the metadata format <em><xsl:value-of select="$metadataPrefix"/></em>.</p>
  <ol>
    <xsl:apply-templates select="oai:record" />
  </ol>
</xsl:template>

<!--
Record Details
-->
<xsl:template match="oai:record">
  <li>
    <xsl:apply-templates select="oai:header" />
    <xsl:apply-templates select="oai:metadata" />
  </li>
</xsl:template>

<xsl:template match="oai:record/oai:header">
  <h3>Record <em><xsl:value-of select="oai:identifier"/></em></h3>
  <ul>
    <li>&#187; <a class="link" href="?verb=ListMetadataFormats&amp;identifier={oai:identifier}">ListMetadataFormats</a></li>
    <xsl:if test="$verb != 'GetRecord'"><li>&#187; <a class="link" href="?verb=GetRecord&amp;metadataPrefix={$metadataPrefix}&amp;identifier={oai:identifier}">GetRecord</a></li></xsl:if>
  </ul>
  <table class="values">
    <tr><td class="key">Identifier</td>
    <td class="value"><xsl:value-of select="oai:identifier"/></td></tr>
    <tr><td class="key">Datestamp</td>
    <td class="value"><xsl:value-of select="oai:datestamp"/></td></tr>
  </table>
  <xsl:if test="@status = 'deleted'"><h4>This record has been deleted.</h4></xsl:if>
</xsl:template>

<xsl:template match="oai:metadata">
  <xsl:apply-templates select="*" />
</xsl:template>

<!--
Resumption Token
-->
<xsl:template match="oai:resumptionToken">
  <p>There are more results.</p>
  <ul>
    <li>&#187; <a class="link" href="?verb={$verb}&amp;resumptionToken={.}">Resume</a></li>
  </ul>
  <table class="values">
    <tr><td class="key">Cursor Position</td>
    <td class="value"><xsl:value-of select="@cursor"/></td></tr>
    <tr><td class="key">Total Records</td>
    <td class="value"><xsl:value-of select="@completeListSize"/></td></tr>
    <tr><td class="key">Expiration Datestamp</td>
    <td class="value"><xsl:value-of select="@expirationDate"/></td></tr>
    <tr><td class="key">Resumption Token</td>
    <td class="value"><xsl:value-of select="."/></td></tr>
  </table>
</xsl:template>

<!--
Unknown Metadata
-->
<xsl:template match="oai:metadata/*" priority='-100'>
  <h4>Metadata Format <em><xsl:value-of select="$metadataPrefix"/></em></h4>
  <div class="xmlSource">
    <xsl:apply-templates select="." mode='xmlMarkup' />
  </div>
</xsl:template>

<!--
DublinCore Metadata
-->
<xsl:template match="oai_dc:dc" xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/">
  <h4>Metadata Format <em>DublinCore</em></h4>
  <table>
    <xsl:apply-templates select="*" />
  </table>
</xsl:template>

<xsl:template match="dc:title" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <tr><td class="key">Title</td><td class="value"><xsl:value-of select="."/></td></tr>
</xsl:template>

<xsl:template match="dc:creator" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <tr><td class="key">Author or Creator</td><td class="value"><xsl:value-of select="."/></td></tr>
</xsl:template>

<xsl:template match="dc:subject" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <tr><td class="key">Subject and Keywords</td><td class="value"><xsl:value-of select="."/></td></tr>
</xsl:template>

<xsl:template match="dc:description" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <tr><td class="key">Description</td><td class="value"><xsl:value-of select="."/></td></tr>
</xsl:template>

<xsl:template match="dc:publisher" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <tr><td class="key">Publisher</td><td class="value"><xsl:value-of select="."/></td></tr>
</xsl:template>

<xsl:template match="dc:contributor" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <tr><td class="key">Other Contributor</td><td class="value"><xsl:value-of select="."/></td></tr>
</xsl:template>

<xsl:template match="dc:date" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <tr><td class="key">Date</td><td class="value"><xsl:value-of select="."/></td></tr>
</xsl:template>

<xsl:template match="dc:type" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <tr><td class="key">Resource Type</td><td class="value"><xsl:value-of select="."/></td></tr>
</xsl:template>

<xsl:template match="dc:format" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <tr><td class="key">Format</td><td class="value"><xsl:value-of select="."/></td></tr>
</xsl:template>

<xsl:template match="dc:identifier" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <tr><td class="key">Resource Identifier</td><td class="value"><xsl:value-of select="."/></td></tr>
</xsl:template>

<xsl:template match="dc:source" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <tr><td class="key">Source</td><td class="value"><xsl:value-of select="."/></td></tr>
</xsl:template>

<xsl:template match="dc:language" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <tr><td class="key">Language</td><td class="value"><xsl:value-of select="."/></td></tr>
</xsl:template>

<xsl:template match="dc:relation" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <tr><td class="key">Relation</td><td class="value">
    <xsl:choose>
      <xsl:when test='starts-with(.,"http")'>
        <a href="{.}"><xsl:value-of select="."/></a>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="."/>
      </xsl:otherwise>
    </xsl:choose>
  </td></tr>
</xsl:template>

<xsl:template match="dc:coverage" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <tr><td class="key">Coverage</td><td class="value"><xsl:value-of select="."/></td></tr>
</xsl:template>

<xsl:template match="dc:rights" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <tr><td class="key">Rights Management</td><td class="value"><xsl:value-of select="."/></td></tr>
</xsl:template>

<!--
XML Pretty Maker
-->
<xsl:template match="node()" mode='xmlMarkup'>
  <div class="xmlBlock">
    &lt;<span class="xmlTagName"><xsl:value-of select='name(.)' /></span><xsl:apply-templates select="@*" mode='xmlMarkup'/>&gt;<xsl:apply-templates select="node()" mode='xmlMarkup' />&lt;/<span class="xmlTagName"><xsl:value-of select='name(.)' /></span>&gt;
  </div>
</xsl:template>

<xsl:template match="text()" mode='xmlMarkup'><span class="xmlText"><xsl:value-of select='.' /></span></xsl:template>

<xsl:template match="@*" mode='xmlMarkup'>
  <xsl:text> </xsl:text><span class="xmlAttrName"><xsl:value-of select='name()' /></span>="<span class="xmlAttrValue"><xsl:value-of select='.' /></span>"
</xsl:template>

</xsl:stylesheet>
