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
            </td>
        </xsl:copy>
    </xsl:template>


</xsl:stylesheet>
