<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
    <xsl:output method="xml" version="1.0" encoding="UTF-8"/>
    <xsl:param name="service_version" select="0"/>
    <xsl:param name="add_paragraph_numbers" select="0"/>
    <xsl:param name="for_pdf" select="0"/>
    
    <!--  Basic rule: copy everything not specified and process the children -->
    <xsl:template match="@*|node()">
        <xsl:copy><xsl:apply-templates select="@*|node()" /></xsl:copy>
    </xsl:template>

    <!-- don't copy the html element -->
    <xsl:template match="html">
        <xsl:variable name="counter" select="php:function('Edutiek\LongEssayAssessmentService\Internal\HtmlProcessing::initParaCounter')" />
        <xsl:apply-templates select="node()" />
    </xsl:template>

    <!-- use specialized table elements for accessibility -->
    <xsl:template match="body">
        <xlas-table border="0" style="border-spacing: 10px;">
            <xsl:apply-templates select="node()" />
        </xlas-table>
    </xsl:template>

    <!--  Add numbers to the paragraph like elements -->
    <xsl:template match="body/h1|body/h2|body/h3|body/h4|body/h5|body/h6|body/p|body/ul|body/ol|body/pre">
        <xsl:variable name="counter" select="php:function('Edutiek\LongEssayAssessmentService\Internal\HtmlProcessing::nextParaCounter')" />
        <xsl:variable name="counterTag" select="php:function('Edutiek\LongEssayAssessmentService\Internal\HtmlProcessing::ParaCounterTag', local-name())" />
        <xsl:variable name="prefix" select="php:function('Edutiek\LongEssayAssessmentService\Internal\HtmlProcessing::nextHeadlinePrefix', local-name())" />
        <xlas-tr class="long-essay-content-row" style="vertical-align:top;">
            <xsl:attribute name="data-p">
                <xsl:value-of select="$counter" />
            </xsl:attribute>
            <xsl:if test="$add_paragraph_numbers = 1">
                <xlas-td class="long-essay-counter-column" style="text-align:left;">
                    <xsl:attribute name="style">
                        <xsl:if test="$for_pdf = 1">width: 8mm;</xsl:if>
                        <xsl:if test="$for_pdf = 0">width: 5%;</xsl:if>
                    </xsl:attribute>
                    <!-- for TCPDF, use correct wrapping element to set the correct line height -->
                    <xsl:element name="{$counterTag}">
                        <xsl:attribute name="style">text-align:left;</xsl:attribute>
                        <xsl:if test="$for_pdf = 0">
                            <span class="sr-only">Absatz</span>
                        </xsl:if>
                        <span class="ParagraphNumber" style="font-family:sans-serif; font-size:0.6em; font-weight:normal;">
                            <xsl:choose>
                                <xsl:when test="$service_version >= 20231218">
                                    <!-- from this version on paragraph numbers should be included to the word counter for comment markup  -->
                                    <xsl:call-template name="words">
                                        <xsl:with-param name="text">
                                            <xsl:value-of select="$counter" />
                                        </xsl:with-param>
                                    </xsl:call-template>
                                </xsl:when>
                                <xsl:otherwise>
                                    <xsl:value-of select="$counter" />
                                </xsl:otherwise>
                            </xsl:choose>
                        </span>
                        <!-- for TCPDF, use non-breaking space to set the correct line height -->
                        <xsl:if test="$for_pdf = 1">&#160;</xsl:if>
                    </xsl:element>
                </xlas-td>
            </xsl:if>
            <xlas-td class="long-essay-content-column">
                <xsl:if test="$add_paragraph_numbers = 1">
                    <xsl:attribute name="style">
                        <xsl:if test="$for_pdf = 1">width: 92mm;</xsl:if>
                    </xsl:attribute>
                </xsl:if>
                <xsl:copy>
                    <xsl:attribute name="class">long-essay-block</xsl:attribute>
                    <xsl:attribute name="long-essay-number">
                        <xsl:value-of select="$counter" />
                    </xsl:attribute>
                    
                    <xsl:choose>
                        <!-- from this version on headline prefixes should be included to the word counter for comment markup  -->
                        <xsl:when test="$service_version >= 20231218">
                            <xsl:call-template name="words">
                                <xsl:with-param name="text">
                                    <xsl:value-of select="$prefix" />
                                </xsl:with-param>
                            </xsl:call-template>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="$prefix" />
                        </xsl:otherwise>
                    </xsl:choose>

                    
                    <xsl:apply-templates select="node()" />
                </xsl:copy>
            </xlas-td>
        </xlas-tr>
    </xsl:template>


    <!-- wrap words in word counter elements -->
    <xsl:template match="text()">
        <xsl:call-template name="words">
            <xsl:with-param name="text">
                <xsl:value-of select="string(.)" />
            </xsl:with-param>
        </xsl:call-template>
    </xsl:template>

    
    <xsl:template name="words">
        <xsl:param name="text" />
        <xsl:variable name="para" select="php:function('Edutiek\LongEssayAssessmentService\Internal\HtmlProcessing::currentParaCounter')" />
        <xsl:for-each select="php:function('Edutiek\LongEssayAssessmentService\Internal\HtmlProcessing::splitWords', $text)/text()">
            <xsl:variable name="word" select="php:function('Edutiek\LongEssayAssessmentService\Internal\HtmlProcessing::nextWordCounter')" />
            <w-p>
                <xsl:attribute name="w"><xsl:value-of select="$word" /></xsl:attribute>
                <xsl:attribute name="p"><xsl:value-of select="$para" /></xsl:attribute>
                <xsl:value-of select="." />
            </w-p>
        </xsl:for-each>
    </xsl:template>
    

</xsl:stylesheet>
