<style>
    @page {size: 800px auto; margin:6px!important; padding:0!important}
    body{
        font-family: 'Montserrat', sans-serif !important;
        margin:0px;
        font-size: 14px;
    }
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
    .maindiv {
        align-items: left;
    }
    .bd{
    /* display: flex;
    align-items: center;
    gap: 10px; */
    border: solid 1px #ccc;
    margin: 0px 10px;
    padding: 0px 8px;
    word-wrap: break-word;
    }
    .bd-left sdivong {
    font-weight: bold;
    word-wrap: break-word;
}
.bd-right {
    border-left: solid 1px #ccc;
    word-wrap: break-word;
    padding: 8px;
    text-align:left;
    float:left
}
.bd-left {
    /* font-weight: bold; */
    width: 160px;
    text-align: left;
    word-wrap: break-word;
    padding: 8px 0px;
    float:left
}
.bd sdivong {
    font-weight: bold;
    word-wrap: break-word;
    margin-bottom: 10px;
    text-align: left;
    display: block;
}
div p {
    text-align: left;
    word-wrap: break-word;
    padding: 0px 8px;
    margin: 8px 0 8px 0;
}
.bd div p{
    padding: 0px;
    word-wrap: break-word;
}
.footer{
    background-color:#00AEEF; padding:10px; position: absolute; bottom: 0;
    width: 98% !important;
}
</style>
<div style="width:100%; background-color:#eeeeee;" cellpadding="5" cellspacing="0" align="center" class="sep">
    <div>
        <div align="center"><div style="max-width:100%;width:800px;background-color:#ffffff" cellpadding="5" cellspacing="0" align="center">
            <div class="header" style="border-bottom: solid 1px #dddddd; padding: 10px">
                <div>
                    <div style="float: left; background-color:#ffffff;"><a href="<?php echo $site_url?>"><img src="https://d1n5x7e4cmsf36.cloudfront.net/public/config/upload-20210831131111-6-thumbnail.png" alt="<?php echo $site_title?>" /></a></div>
                    <div style="float: right; background-color:#ffffff; margin-top: 10px;"><sdivong>055 737 00 14</sdivong></div>
                </div>
                <div class="clr" style="clear:both;"></div>
            </div>
            <p>Hoi {{$booking->client_firstname}} {{$booking->client_lastname}} ,<br><br>Gefeliciteerd met het boeken van je vakantie! Hieronder vind je je boekingsbevestiging. Uiterlijk <span class="bold">vier weken voor verdivek ontvang je van ons inloggegevens</span> waarmee je alle relevante verdivekinformatie kunt doorlezen én kennis kunt maken met je reisgenoten.</p>
            <p>De volgende reis is definitief voor je geboekt:</p>
            <p>{{$booking->trip_name}}</p>
            <p>We gaan onze uiterste best doen om een absolute topvakantie voor je te organiseren. In deze mail vind je nogmaals de door jou opgegeven gegevens. Indien je een vliegreis hebt geboekt verzoeken we je zorgvuldig te condivoleren of je je <sdivong>eerste voornaam</sdivong>&nbsp;en je correcte achternaam aan ons doorgegeven hebt. Luchtvaartmaatschappijen berekenen kosten indien deze niet overeenkomen.&nbsp;</p>
            <p>Als je nog vragen hebt over je reis kun je contact met ons opnemen.</p>
            <p>Vergeet ons niet toe te voegen op Instagram: <a href="https://instagram.com/simireizen/" target="_blank" rel="noopener"><span style="color: #ff0000;"><sdivong>simireizen</sdivong></span></a></p>
            <p>Als je de aanbetaling nog niet via iDeal hebt gedaan, dan kun je deze alsnog doen via een normale bankoverschrijving, onder vermelding van je boekingsnummer <sdivong>{{$booking->booking_id}}</sdivong></p>
            <p>Kosteloos annuleren is na het klikken op de definitieve bevestiging niet mogelijk.&nbsp;</p>
            <p style="word-wrap: break-word;"><span style="color: #ff0000;"><sdivong>LET OP</sdivong>: </span><sdivong><br>indien je een vliegreis hebt geboekt, houd er rekening mee dat je ticket pas na enige tijd zal verschijnen in je Simi account. </sdivong></p>
            <p>Hieronder vind je nogmaals de door jou opgegeven informatie. We willen je vragen om even contact op te nemen als je hier nog fouten in ziet staan.</p>
            <div>
            <div class="clr" style="clear:both;"></div>
                <div>
                    <div class="bd">
                        <div class="bd-left"><sdivong>Boekingsnummer</sdivong></div>
                        <div class="bd-right"><sdivong>{{$booking->booking_id}}</sdivong></div>
                        <div class="clr" style="clear:both;"></div>
                    </div>
                    <div class="clr" style="clear:both;"></div>
                    <div class="bd">
                        <div>
                            <p><sdivong>Persoonlijke gegevens</sdivong></p>
                        </div>
                    </div>
                    <div class="bd">
                        <div class="bd-left">Voornaam deelnemer</div>
                        <div class="bd-right">{{$booking->client_firstname}}</div>
                        <div class="clr" style="clear:both;"></div>
                    </div>
                    <div class="clr" style="clear:both;"></div>
                    <div class="bd">
                        <div class="bd-left">Achternaam deelnemer</div>
                        <div class="bd-right">{{$booking->client_lastname}}</div>
                        <div class="clr" style="clear:both;"></div>
                    </div>
                    <div class="clr" style="clear:both;"></div>
                    <div class="bd">
                        <div class="bd-left">Geboortedatum</div>
                        <div class="bd-right">{{$booking->client_dob}}</div>
                        <div class="clr" style="clear:both;"></div>
                    </div>
                    <div class="clr" style="clear:both;"></div>
                    <div class="bd">
                        <div class="bd-left">Geslacht</div>
                        <div class="bd-right">{{$booking->client_gender}}</div>
                        <div class="clr" style="clear:both;"></div>
                    </div>
                    <div class="clr" style="clear:both;"></div>
                    <div class="bd">
                        <div class="bd-left">E-mailadres</div>
                        <div class="bd-right">{{$booking->client_parent_email}}</div>
                        <div class="clr" style="clear:both;"></div>
                    </div>
                    <div class="clr" style="clear:both;"></div>
                    <div class="bd">
                        <div colspan="2"><sdivong><br>Contactgegevens</sdivong></div>
                        <div class="clr" style="clear:both;"></div>
                    </div>
                    <div class="bd">
                        <div class="bd-left">Adres</div>
                        <div class="bd-right">{{$booking->client_address}}</div>
                        <div class="clr" style="clear:both;"></div>
                    </div>
                    <div class="bd">
                        <div class="bd-left">Telefoonnummer</div>
                        <div class="bd-right">{{$booking->client_phone}}</div>
                        <div class="clr" style="clear:both;"></div>
                    </div>
                    <div class="clr" style="clear:both;"></div>
                    <div class="bd">
                        <div class="bd-left">Contactpersoon</div>
                        <div class="bd-right">{{$booking->contact_person_name}}</div>
                        <div class="clr" style="clear:both;"></div>
                    </div>
                    <div class="clr" style="clear:both;"></div>
                    <div class="bd">
                        <div class="bd-left">Mobiel nummer contactpersoon</div>
                        <div class="bd-right">{{$booking->client_mobile}}</div>
                        <div class="clr" style="clear:both;"></div>
                    </div>
                    <div class="clr" style="clear:both;"></div>
                    <div class="bd">
                        <div colspan="2"><sdivong><br>Bijzonderheden</sdivong></div>
                        <div class="clr" style="clear:both;"></div>
                    </div>
                    <div class="clr" style="clear:both;"></div>
                    <div class="bd">
                        <div class="bd-left">Vertel iets over jezelf/bijzonderheden</div>
                        <div class="bd-right">{{$booking->client_about_child}}</div>
                        <div class="clr" style="clear:both;"></div>
                    </div>
                    <div class="clr" style="clear:both;"></div>
                    <div class="bd">
                        <div class="bd-left">Dieet</div>
                        <div class="bd-right">{{$booking->client_diet}}</div>
                        <div class="clr" style="clear:both;"></div>
                    </div>
                    <div class="clr" style="clear:both;"></div>
                    <div class="bd">
                        <div class="bd-left">Medicatie/allergieen</div>
                        <div class="bd-right">{{$booking->client_medication}}</div>
                        <div class="clr" style="clear:both;"></div>
                    </div>
                    <div class="clr" style="clear:both;"></div>
                    <div class="bd">
                        <div colspan="2"><sdivong><br>Boekingsinformatie</sdivong></div>
                    </div>
                    <div class="clr" style="clear:both;"></div>
                    <div class="bd">
                        <div class="bd-left">Verzekeringen afsluiten</div>
                        <div class="bd-right">
                            <p>{{$booking->booking_client_insurance}}</p>
                            <p><span style="color: #ff0000;"><sdivong>LET OP:</sdivong>&nbsp;</span>een reisverzekering kun je afsluiten tot 1 dag voor verdivek. Een annuleringsverzekering dient <br>uiterlijk binnen 7 dagen na boeking te worden <br>afgesloten.&nbsp;</p>
                        </div>
                        <div class="clr" style="clear:both;"></div>
                    </div>
                    <div class="clr" style="clear:both;"></div>
                    <div class="bd">
                        <div class="bd-left">Je gekozen opstapplaats</div>
                        <div class="bd-right">{{$booking->pickup_place}}</div>
                        <div class="clr" style="clear:both;"></div>
                    </div>
                    <div class="clr" style="clear:both;"></div>
                    <div class="bd">
                        <div class="bd-left">Totaal bedrag van de reservering</div>
                        <div class="bd-right">{{$booking->booking_total_amount}}</div>
                        <div class="clr" style="clear:both;"></div>
                    </div>
                    <div class="clr" style="clear:both;"></div>
                    <div class="bd">
                        <div class="bd-left">Bedrag aanbetaling</div>
                        <div class="bd-right">
                            <p>EUR {{$booking->deposite_amount}}&nbsp;</p>
                            <p>Let op: heb je nog niet betaald en boek je binnen 42 dagen voor verdivek, dan dien je de&nbsp;<sdivong>volledige reissom</sdivong> over te maken.&nbsp;</p>
                        </div>
                        <div class="clr" style="clear:both;"></div>
                    </div>
                    <div class="clr" style="clear:both;"></div>
                    <div class="bd">
                        <div class="bd-left">Boekingsdatum</div>
                        <div class="bd-right">
                            {{$booking->booking_date}} (Reisdata van: {{$booking->trip_startdate}} tot en met: {{$booking->trip_enddate}})</div>
                            <div class="clr" style="clear:both;"></div>
                    </div>
                    <div class="clr" style="clear:both;"></div>
                    <div class="bd">
                        <div class="bd-left">Betaalgegevens Simi Reizen:</div>
                        <div class="bd-right">IBAN: NL65RABO0136157009 tnv Simi Reizen BV 
                            onder vermelding van je boekingsnummer <br>{{$booking->booking_id}}.</div>
                            <div class="clr" style="clear:both;"></div>
                    </div>
                    <div class="clr" style="clear:both;"></div>
                </div>
                <div class="clr" style="clear:both;"></div>
            </div>
            <p>We willen je graag uitnodigen om alvast een <a href="https://facebook.com/SimiReizen">kijkje op facebook</a> te nemen en een berichtje te plaatsen. We hopen dat je al ontzettend veel zin hebt in je vakantie. Als je nog vragen hebt kun je altijd contact met ons opnemen! Vergeet ons niet toe te voegen op Instagram: simireizen</p>
            <div class="page" title="Page 2">
                <div class="layoutArea">
                    <div class="column">
                        <p>Op deze overeenkomst is de garantieregeling van SGR van toepassing. Je kunt de voorwaarden vinden op <span style="color: #0000ff;"><a href="http://sgr.nl/garantieregeling" target="_blank" rel="noopener">sgr.nl/garantieregeling</a></span>. Op verzoek stuurt SGR deze voorwaarden toe.</p>
                        <p>Op deze reisovereenkomst zijn onze algemene voorwaarden en aanvullende algemene voorwaarden van toepassing. Deze heb je tijdens je boeking gelezen en geaccepteerd.&nbsp;</p>
                    </div>
                </div>
            </div>
            <div class="clr" style="clear:both;"></div>
            <p>We wensen je alvast veel voorpret!</p>
            <p><br>Met vriendelijke groet,<br><br>Het team van Simi Reizen</p>
            <p>Tel: <sdivong>055-7370014</sdivong> (ma-vr 9:00 - 16:30)</p>
            <div class="footer">
                <div style="float: left;">
                    <div style="text-align:left; color:#ffffff;">Actieve groepsreizen voor jongeren. Jongerenreizen, singlereizen en meer!<br><a href="<?php echo $site_url?>'/simi/privacy-cookies.html" style="color:#ffffff;">Privacybeleid</a> | <a href="<?php echo $site_url?>'/simi/privacy-cookies.html" style="color:#ffffff;">Cookie-beleid</a> | <a href="<?php echo $site_url?>'/sitemap" style="color:#ffffff;">Sitemap</a></div>
                </div>
                <div style="float: right;  color:#ffffff;">&copy; '<?php echo date( "Y" )?>' Simi Reizen</div>
                <div class="clr" style="clear:both;"></div>
            </div>
        </div>
    </div>
</div>
