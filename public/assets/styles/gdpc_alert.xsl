<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:cap="urn:oasis:names:tc:emergency:cap:1.2"
                version="1.0">
    <xsl:output encoding="UTF-8"
                indent="yes"
                method="html"
                media-type="text/html"
                omit-xml-declaration="yes"
                doctype-system="about:legacy-compat"/>
    <xsl:template match="cap:alert">
        <html>
            <head>
                <meta charset="utf-8" />
                <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

                <title>
                    <xsl:value-of select="cap:info/cap:event"/> -
                    Issued by: <xsl:value-of select="cap:info/cap:senderName"/> -
                    <xsl:value-of select="cap:identifier"/>
                </title>

                <link href="/assets/styles/normalize.css" rel="stylesheet" type="text/css"/>
                <link href="/assets/styles/gdpc_alert.css" rel="stylesheet" type="text/css"/>

                <xsl:if test="cap:info/cap:area/cap:polygon">
                    <script>

                        var polyString = '<xsl:value-of select="cap:info/cap:area/cap:polygon/text()"/>';

                        function initMap() {
                            var polyCoords = polyString.split(' ').map(function(pair){
                                var latLng = pair.split(',');
                                return {
                                    lat: parseFloat(latLng[0]),
                                    lng: parseFloat(latLng[1])
                                };
                            });

                            // Construct the polygon.
                            var polyObject = new google.maps.Polygon({
                                paths: polyCoords,
                                strokeColor: '#FF0000',
                                strokeOpacity: 0.8,
                                strokeWeight: 2,
                                fillColor: '#FF0000',
                                fillOpacity: 0.35
                            });

                            var bounds = new google.maps.LatLngBounds();
                            polyObject.getPath().getArray().forEach(function(latLng){
                                bounds.extend(latLng);
                            });

                            var map = new google.maps.Map(document.getElementById('map'), {
                              zoom: 5,
                              center: bounds.getCenter(),
                              mapTypeId: 'terrain'
                            });

                            map.fitBounds(bounds);
                            polyObject.setMap(map);
                        }
                    </script>
                </xsl:if>
            </head>
            <body>
                <div class="content">

                    <div class="cards">
                        <div class="card-outer">
                            <div class="card">
                                <!-- <img class="map" src="https://maps.googleapis.com/maps/api/staticmap?center=37.7788315,-122.1978302&zoom=8&size=458x180&scale=2" /> -->

                                <div class="card-info">
                                    <h3>
                                        <xsl:value-of select="cap:info/cap:event"/>
                                    </h3>
                                    <h4>
                                        <xsl:value-of select="cap:info/cap:senderName"/> -
                                        <xsl:value-of select="cap:identifier"/>
                                    </h4>

                                    <dl>
                                        <dt>Status</dt>
                                        <dd>
                                            <xsl:value-of select="cap:status"/>
                                        </dd>
                                        <dt>Type</dt>
                                        <dd>
                                            <xsl:value-of select="cap:msgType"/>
                                        </dd>
                                    </dl>

                                    <xsl:if test="cap:info/cap:headline">
                                        <p><xsl:value-of select="cap:info/cap:headline"/></p>
                                    </xsl:if>

                                    <dl>
                                        <dt>Sent</dt>
                                        <dd>
                                            <xsl:value-of select="cap:sent"/>
                                        </dd>
                                        <dt>Effective</dt>
                                        <dd>
                                            <xsl:value-of select="cap:info/cap:effective"/>
                                        </dd>
                                        <dt>Onset</dt>
                                        <dd>
                                            <xsl:value-of select="cap:info/cap:onset"/>
                                        </dd>
                                        <dt>Expires</dt>
                                        <dd>
                                            <xsl:value-of select="cap:info/cap:expires"/>
                                        </dd>
                                    </dl>

                                    <dl>
                                        <dt>Urgency</dt>
                                        <dd>
                                            <xsl:value-of select="cap:info/cap:urgency"/>
                                        </dd>
                                        <dt>Severity</dt>
                                        <dd>
                                            <xsl:value-of select="cap:info/cap:severity"/>
                                        </dd>
                                        <dt>Certainty</dt>
                                        <dd>
                                            <xsl:value-of select="cap:info/cap:certainty"/>
                                        </dd>
                                    </dl>

                                    <pre><xsl:value-of select="cap:info/cap:description"/></pre>

                                    <xsl:if test="cap:info/cap:area/cap:areaDesc">
                                        <h4>
                                            <xsl:value-of select="cap:info/cap:area/cap:areaDesc"/>
                                        </h4>
                                    </xsl:if>
                                </div>

                                <xsl:if test="cap:info/cap:area/cap:polygon">
                                    <div id="map" class="map"></div>
                                </xsl:if>
                            </div>
                        </div>
                    </div>
                </div>

                <xsl:if test="cap:info/cap:area/cap:polygon">
                    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC5kdivt-OAcfrpW87FMS8YeBWp3MSk8AA&amp;callback=initMap"></script>
                </xsl:if>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
