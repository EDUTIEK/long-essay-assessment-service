<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
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
        <table cellspacing="10">
            <xsl:apply-templates select="node()" />
        </table>
    </xsl:template>

    <!--  Add numbers to the paragraph like elements -->
    <xsl:template match="body/h1|body/h2|body/h3|body/h4|body/h5|body/h6|body/p|body/ul|body/ol">
        <xsl:variable name="counter" select="php:function('Edutiek\LongEssayAssessmentService\Internal\HtmlProcessing::nextCounter')" />
        <tr style="vertical-align:top;">
            <td width="10%">
                <xsl:value-of select="$counter" />
             </td>
            <td width="90%">
                <xsl:copy>
                    <xsl:attribute name="class">long-essay-block</xsl:attribute>
                    <xsl:attribute name="long-essay-number">
                        <xsl:value-of select="$counter" />
                    </xsl:attribute>

                    <xsl:apply-templates select="node()" />
                </xsl:copy>
            </td>
        </tr>

    </xsl:template>


</xsl:stylesheet>