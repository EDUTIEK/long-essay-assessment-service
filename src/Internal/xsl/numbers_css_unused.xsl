<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">

<!--
THIS STYLESHEET IS NOT USED!
-->

    <xsl:output method="xml" version="1.0" encoding="UTF-8"/>

    <!--  Basic rule: copy everything not specified and process the childs -->
    <xsl:template match="@*|node()">
        <xsl:copy><xsl:apply-templates select="@*|node()" /></xsl:copy>
    </xsl:template>

    <!-- don't copy the html element -->
    <xsl:template match="html">
        <xsl:variable name="counter" select="php:function('Edutiek\LongEssayAssessmentService\Internal\HtmlProcessing::initCounter')" />
        <xsl:apply-templates select="node()" />
    </xsl:template>

    <xsl:template match="body">
        <style>
            .long-essay-block {
                margin-left: 50px;
            }

            .long-essay-block::before {
                content: attr(long-essay-number);
                position: fixed;
                left: 0;
                font-family: monospace;
                font-size: 15px;
                white-space: pre;
            }
        </style>
        <xsl:apply-templates select="node()" />
    </xsl:template>


    <!--  Add numbers to the paragraph like elements -->
    <xsl:template match="h1|h2|h3|h4|h5|h6|p|ul|ol">
        <xsl:variable name="counter" select="php:function('Edutiek\LongEssayAssessmentService\Internal\HtmlProcessing::nextCounter')" />

        <xsl:copy>
            <xsl:attribute name="class">long-essay-block</xsl:attribute>
            <xsl:attribute name="long-essay-number">
                <xsl:value-of select="$counter" />
            </xsl:attribute>

            <xsl:apply-templates select="node()" />
        </xsl:copy>
    </xsl:template>


</xsl:stylesheet>