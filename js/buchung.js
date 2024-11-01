

$( document ).ready(function() {

    if($(".eventdatum").length==0){
        return;
    }

    //Es gibt kein Warenkorb - Fahrt kann nicht dargestellt werden
    if($(".travelmanager-basket").length==0)
    {
        $(".eventinfo_container").html(dialoge["kein_warenkorb"]);
        return;
    }

    //Lokale Sprachdatei für den Kalender setzen
    if(calendarLocale!==undefined){
        jQuery.datepicker.setDefaults(calendarLocale);
    }

    var vermittler_id="";
    if($(".eventinfo_container").first().attr("reference")!=""){
        vermittler_id = $(".eventinfo_container").first().attr("reference");
    }

    //Datepicker für Eventinfo Call
    $.each($(".eventdatum"),function(){
        var fahrplan = JSON.parse(decodeURIComponent($(this).attr("days")));
        var station = decodeURIComponent($(this).closest(".eventinfo_container").attr("hafen_start"));
        var station_stop = decodeURIComponent($(this).closest(".eventinfo_container").attr("hafen_stop"));
        var station_id = parseInt($(this).attr("location_start_id"));
        var station_stop_id = parseInt($(this).attr("location_stop_id"));
        var linie_id = parseInt($(this).attr("linie_id"));
        var only_tagesfahrt = parseInt($(this).attr("only_tagesfahrt"))==1;
        var datum_rueckfahrten = JSON.parse(decodeURIComponent($(this).attr("days_return")));
        var url_frontend = decodeURIComponent($(this).attr("url_frontend"));
        var vObjj = $(this);

        $(this).datepicker({
            minDate: new Date(fahrplan[0]),
            maxDate: new Date(fahrplan[fahrplan.length - 1]),

            beforeShowDay: function (date) {
                var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                return [fahrplan.indexOf(string) !== -1];
            },

            onSelect: function (dateText) {
                var container = $(vObjj).closest(".eventinfo_container").find(".booking_window");
                var date = $(vObjj).datepicker("getDate");
                var datumFormat = `${String(date.getDate()).padStart(2, '0')}.${String(date.getMonth() + 1).padStart(2, '0')}.${date.getFullYear()}`;


                var gesuchtesDatum = jQuery.datepicker.formatDate('yy-mm-dd', date);

                //Fahrzeiten für den gewählten Tag ermitteln
                var data = {};
                data['action'] = 'travelmanager_eventinfo_hinfahrt';
                data['datum'] = gesuchtesDatum;
                data['station_start_id'] = station_id;
                data['station_stop_id'] = station_stop_id;
                data['linie_id'] = linie_id;
                data['hash']=$(vObjj).attr("keyhash");

                jQuery.blockUI({ message: dialoge["inhalt_laden"]+'...'});
                jQuery.post(ajaxurl, data, function(fahrten) {
                    jQuery.unblockUI();

                    //Einfache Fahrt - direkte Darstellung des Buchungsfensters
                    if($(vObjj).attr("one_way")=="1"){
                        var url = url_frontend+"&hinfahrt_datum="+datumFormat+"&return_url="+encodeURIComponent(window.location.href)+"&session_id="+$(".eventinfo_container").first().attr("session_id");
                        var ulAbfahrt = $("<ul/>");
                        $(ulAbfahrt).addClass("uhrzeit_ul");

                        $.each(fahrten,function() {
                            var li = $("<li/>");
                            var abfahrt = $(this).attr("abfahrt")+" "+station+" - "+$(this).attr("ankunft")+" "+station_stop;

                            var lhf_id = $(this).attr("lhf_id");
                            if(!$(this).attr("available")){
                                $(li).addClass("soldout");
                            }
                            if ($.trim(abfahrt) == "") {
                                return;
                            }
                            if($(this).attr("available")){
                                $(li).click(function(){
                                    url+="&lhf_id="+lhf_id;
                                    if(vermittler_id!=""){
                                        url+="&vermittler_id="+vermittler_id;
                                    }
                                    $(container).removeClass("hide");
                                    $(container).find(".url").text(url);
                                    $(container).find("iframe").attr("src",url);
                                    $(".tmaktiv").removeClass("tmaktiv");
                                    $(li).addClass("tmaktiv");
                                });
                            }
                            $(li).html(abfahrt);
                            $(ulAbfahrt).append(li);
                        });
                        $(vObjj).closest(".eventinfo_container").find(".eventdatum_abfahrt").html("").append(ulAbfahrt);
                    }
                    //Hin- und Rückfahrt
                    else{
                        $(container).addClass("hide");
                        $(vObjj).closest(".eventinfo_container").find(".rueckfahrt_abfahrt").html("");
                        $(vObjj).closest(".eventinfo_container").find(".rueckfahrt").addClass("hide");

                        //Darstellung der Abfahrts-Uhrzeit
                        var ulAbfahrt = $("<ul/>");
                        $(ulAbfahrt).addClass("uhrzeit_ul");
                        $.each(fahrten,function(){
                            var li = $("<li/>");
                            var abfahrt = $(this).attr("abfahrt");
                            var ankunft = $(this).attr("ankunft");
                            if($.trim(abfahrt)==""){
                                return;
                            }
                            if(!$(this).attr("available")){
                                $(li).addClass("soldout");
                            }
                            var lhf_id = $(this).attr("lhf_id");
                            $(li).html(abfahrt+" "+station+" - "+ankunft+" "+station_stop);

                            if($(this).attr("available")){
                                $(li).click(function(){

                                    $(ulAbfahrt).find("li").removeClass("tmaktiv");
                                    $(li).addClass("tmaktiv");
                                    $(li).attr("ankunft",ankunft);

                                    $(vObjj).closest(".eventinfo_container").find(".rueckfahrt_abfahrt").html("");
                                    $(vObjj).closest(".eventinfo_container").find(".rueckfahrt").addClass("hide");

                                    //Kalender für Rückfahrt darstellen
                                    var vObjRf = $(vObjj).closest(".eventinfo_container").find(".rueckfahrt .rueckfahrt_datepicker");
                                    $(vObjj).closest(".eventinfo_container").find(".rueckfahrt").removeClass("hide");;
                                    $(vObjRf).datepicker("destroy");
                                    $(vObjRf).datepicker({
                                        minDate: new Date(date),
                                        maxDate: new Date(datum_rueckfahrten[datum_rueckfahrten.length - 1]),

                                        beforeShowDay: function (dateCurrent) {
                                            var string = jQuery.datepicker.formatDate('yy-mm-dd', dateCurrent);
                                            //Tagesfahrten - nur aktuelle Rückfahrdatum auswählbar
                                            if(only_tagesfahrt && string != gesuchtesDatum){
                                                return false;
                                            }
                                            return [datum_rueckfahrten.indexOf(string) !== -1];
                                        },
                                        onSelect: function (dateText) {
                                            //Laden der Abfahrtszeiten für Rückfahrt
                                            var date = $(vObjRf).datepicker("getDate");
                                            var datumFormatRF = `${String(date.getDate()).padStart(2, '0')}.${String(date.getMonth() + 1).padStart(2, '0')}.${date.getFullYear()}`;

                                            //Bei Abfahrt an einem anderen Tag, keine Abfahrtszeit mit übergeben
                                            if(datumFormatRF != datumFormat){
                                                abfahrt = "";
                                            }

                                            var data = {};
                                            data['datum'] = datumFormatRF;
                                            data['datum_hinfahrt'] = datumFormat;
                                            data['action'] = 'travelmanager_eventinfo_rueckfahrt';
                                            data['ankunft'] = ankunft;
                                            data['hinfahrt_linie_id'] = linie_id;
                                            data['station_start_id'] = $(vObjj).attr("location_start_id");
                                            data['station_stop_id'] = $(vObjj).attr("location_stop_id");
                                            data['hash']=$(vObjj).attr("keyhash");

                                            jQuery.blockUI({ message: dialoge["inhalt_laden"]+'...'});
                                            jQuery.post(ajaxurl, data, function(response) {
                                                jQuery.unblockUI();
                                                var containerRF = $(vObjj).closest(".eventinfo_container");
                                                $(containerRF).find(".rueckfahrt_abfahrt").html("");

                                                if($(response).length>0){

                                                    //Hinweisbox
                                                    var hinweisDiv=$("<div class='rueckfahrt_hinweis'>"+dialoge["klick_uhrzeit"]+"</div>");
                                                    $(containerRF).find(".rueckfahrt_abfahrt").append(hinweisDiv);

                                                    var ul=$("<ul/>");
                                                    $(ul).addClass("uhrzeit_ul");
                                                    $.each(response,function(){
                                                        var abfahrt=this["abfahrt"]+" "+station_stop+" - "+this["ankunft"]+" "+station;
                                                        var verbindungRF=this["verbindung"];
                                                        var li=$("<li/>");
                                                        $(li).html(abfahrt);

                                                        if(!this["available"]){
                                                            $(li).addClass("soldout");
                                                        }
                                                        else{
                                                            $(li).click(function(){

                                                                $(ul).find("li").removeClass("tmaktiv");
                                                                $(li).addClass("tmaktiv");

                                                                var url = url_frontend+"&hinfahrt_datum="+datumFormat+"&rueckfahrt_datum="+datumFormatRF+"&hide_nur_hinfahrt=1&rueckfahrt="+verbindungRF+"&lhf_id="+lhf_id+"&return_url="+encodeURIComponent(window.location.href)+"&session_id="+$(".eventinfo_container").first().attr("session_id");

                                                                if(vermittler_id!=""){
                                                                    url+="&vermittler_id="+vermittler_id;
                                                                }
                                                                $(container).removeClass("hide");
                                                                $(container).find(".url").text(url);
                                                                $(container).find("iframe").attr("src",url);
                                                            });
                                                        }

                                                        $(ul).append(li);
                                                    });
                                                    $(containerRF).find(".rueckfahrt_abfahrt").append(ul);
                                                }
                                                else{
                                                    $(containerRF).find(".rueckfahrt_abfahrt").append($("<div class='remark_single_ride rueckfahrt_hinweis'>"+dialoge["keine_rueckfahrt"]+"</div>"));
                                                }
                                            } );


                                        }
                                    });

                                    //Button für einfache Fahrt
                                    var url = url_frontend+"&hinfahrt_datum="+datumFormat+"&lhf_id="+lhf_id+"&return_url="+encodeURIComponent(window.location.href)+"&session_id="+$(".eventinfo_container").first().attr("session_id")+"&einfache_fahrt=true";
                                    if(vermittler_id!=""){
                                        url+="&vermittler_id="+vermittler_id;
                                    }
                                    var button = $("<input type='button' class='button' value='"+dialoge["einfache_fahrt"]+"'/>");
                                    $(button).on("click",function(){
                                        $(container).removeClass("hide");
                                        $(container).find(".url").text(url);
                                        $(container).find("iframe").attr("src",url);
                                    });
                                    var divContainer=$("<div class='button_container_single_ride'/>").append(button);
                                    $(divContainer).prepend("<div>"+dialoge["oder"]+"</div>");
                                    $(vObjRf).append(divContainer);

                                });
                            }

                            $(ulAbfahrt).append(li);
                        });

                        var hinweisDiv=$("<div class='abfahrt_hinweis'>"+dialoge["klick_uhrzeit"]+"</div>");
                        $(vObjj).closest(".eventinfo_container").find(".eventdatum_abfahrt").html("").append(hinweisDiv);
                        $(vObjj).closest(".eventinfo_container").find(".eventdatum_abfahrt").append(ulAbfahrt);
                    }

                } );
            }
        });

        //Initiales Datum wurde gewählt - im Datepicker setzen und direkt ausführen
        var initial_date = $(vObjj).attr("initial_date");
        if(initial_date!=""){
            $(vObjj).datepicker('setDate',initial_date);
            $(vObjj).datepicker('option', 'onSelect').call($(vObjj), initial_date);
        }

    });
});

