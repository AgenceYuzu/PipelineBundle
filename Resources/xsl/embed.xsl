<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
        version="1.0"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
        xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"
        xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
        exclude-result-prefixes="xhtml custom image">

    <xsl:template match="embed">
        <div>

            <xsl:if test="@align='center'">
                <xsl:attribute name="class"><xsl:value-of select="concat('text-', @align)"/></xsl:attribute>
            </xsl:if>
            <xsl:if test="@align!='center'">
                <xsl:attribute name="class"><xsl:value-of select="concat('pull-', @align)"/></xsl:attribute>
            </xsl:if>
            <xsl:if test="@id">
                <xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
            </xsl:if>
            <xsl:value-of select="text()" disable-output-escaping="yes"/>
        </div>
    </xsl:template>

</xsl:stylesheet>