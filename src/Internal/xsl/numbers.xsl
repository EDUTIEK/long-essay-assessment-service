<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
    <xsl:output method="xml" version="1.0" encoding="UTF-8"/>

    <!--  Basic rule: copy everything not specified and process the children -->
    <xsl:template match="@*|node()">
        <xsl:copy><xsl:apply-templates select="@*|node()" /></xsl:copy>
    </xsl:template>

    <!-- don't copy the html element -->
    <xsl:template match="html">
        <xsl:variable name="counter" select="php:function('Edutiek\LongEssayAssessmentService\Internal\HtmlProcessing::initParaCounter')" />
        <xsl:apply-templates select="node()" />
    </xsl:template>

    <xsl:template match="body">
        <table style="border-spacing: 10px;">
            <xsl:apply-templates select="node()" />
        </table>
    </xsl:template>

    <!--  Add numbers to the paragraph like elements -->
    <xsl:template match="body/h1|body/h2|body/h3|body/h4|body/h5|body/h6|body/p|body/ul|body/ol">
        <xsl:variable name="counter" select="php:function('Edutiek\LongEssayAssessmentService\Internal\HtmlProcessing::nextParaCounter')" />
        <tr style="vertical-align:top;">
            <td style="width: 10%;">
                <xsl:value-of select="$counter" />
             </td>
            <td style="width: 90%;">
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


    <!-- wrap words in word counter elements -->
    <xsl:template match="text()">
        <xsl:variable name="para" select="php:function('Edutiek\LongEssayAssessmentService\Internal\HtmlProcessing::currentParaCounter')" />
        <xsl:for-each select="php:function('Edutiek\LongEssayAssessmentService\Internal\HtmlProcessing::splitWords', string(.))/text()">
            <xsl:variable name="word" select="php:function('Edutiek\LongEssayAssessmentService\Internal\HtmlProcessing::nextWordCounter')" />
            <w-p>
                <xsl:attribute name="w"><xsl:value-of select="$word" /></xsl:attribute>
                <xsl:attribute name="p"><xsl:value-of select="$para" /></xsl:attribute>
                <xsl:value-of select="." />
            </w-p>
        </xsl:for-each>
    </xsl:template>


</xsl:stylesheet>
