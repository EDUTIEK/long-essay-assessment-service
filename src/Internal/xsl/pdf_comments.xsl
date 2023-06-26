<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
    <xsl:output method="xml" version="1.0" encoding="UTF-8"/>

    <!--  Basic rule: copy everything not specified and process the children -->
    <xsl:template match="@*|node()">
        <xsl:copy><xsl:apply-templates select="@*|node()" /></xsl:copy>
    </xsl:template>

    <!-- elements to leave out -->
    <xsl:template match="html|body">
        <xsl:apply-templates select="node()" />
    </xsl:template>

    <!-- remove the number attributes -->
    <xsl:template match="@long-essay-number">
    </xsl:template>
    
    
    <!-- add the comments column -->
    <xsl:template match="tr">
        <xsl:variable name="counter" select="php:function('Edutiek\LongEssayAssessmentService\Internal\HtmlProcessing::initCurrentComments')" />
        <xsl:copy>
            <xsl:copy-of select="@*" />
            <td style="width: 5%;">
                <!-- paragraph number -->
                <xsl:copy-of select="td[1]/node()" />
            </td>
            <td style="width: 60%;">
                <!-- text -->
                <xsl:apply-templates select="td[2]/node()" />
            </td>   
            <td style="width: 35%;">
                <!-- comments -->
                <xsl:for-each select="php:function('Edutiek\LongEssayAssessmentService\Internal\HtmlProcessing::getCurrentComments')/text()">
                    <p style="font-family: sans-serif; font-size:8px;">
                        <xsl:value-of select="." />
                    </p>
                </xsl:for-each>
            </td>
        </xsl:copy>
    </xsl:template>
    
    <!-- add the marking and label for comments -->
    <xsl:template match="span">
        <xsl:choose>
            <xsl:when test="@data-w">
                <xsl:variable name="label" select="php:function('Edutiek\LongEssayAssessmentService\Internal\HtmlProcessing::commentLabel',string(@data-w),string(@data-p))" />
                <xsl:variable name="color" select="php:function('Edutiek\LongEssayAssessmentService\Internal\HtmlProcessing::commentColor',string(@data-w))" />
                <xsl:choose>
                    <xsl:when test="$color">
                        <xsl:if test="$label">
                            <sup style="background-color: grey; color:white; padding:2px; font-family: sans-serif; font-size: 8px;">
                                <xsl:value-of select="$label" />
                            </sup>
                        </xsl:if>
                        <span>
                            <xsl:attribute name="style">background-color: <xsl:value-of select="$color" />;</xsl:attribute>
                            <xsl:value-of select="text()" />
                        </span>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:copy-of select="text()" />
                    </xsl:otherwise>
                </xsl:choose>
             </xsl:when>
            <xsl:otherwise>
                <xsl:copy-of select="." />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

</xsl:stylesheet>
