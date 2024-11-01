
function TMfahrtfinder2_set_zielstation(obj, zielstation_id){
    var vContainer = $(obj).closest(".fahrtfinder2_container");
    $(vContainer).find(".stopstation select").addClass("hide");
    $(vContainer).find(".stopstation option").remove();
    var zielstationen = JSON.parse(decodeURIComponent($(obj).find("option:selected").attr("zielstationen")));
    $(vContainer).find(".stopstation select").append("<option value='0'> - - "+dialoge["zielstation"]+" - -</option>");
    if(zielstationen.length>0){
        $.each(zielstationen,function(){
            var station_id = this["station_id"];
            var station = this["bezeichnung"];
            var selected = "";

            if(zielstation_id !==undefined && parseInt(station_id) == parseInt(zielstation_id)){
                selected = " selected";
            }
            $(vContainer).find(".stopstation select").append("<option value='"+station_id+"'"+selected+">"+station+"</option>");
        });

        $(vContainer).find(".stopstation select").removeClass("hide");
    }
}

function TMfahrtfinder2_finden(obj,showWaitingScreen){
    if($(obj).hasClass("fahrtfinder2_container")){
        var container = $(obj);
    }
    else{
        var container = $(obj).closest(".fahrtfinder2_container");
    }
    var vHash=$(container).attr("hash2");

    if(showWaitingScreen === undefined){
        showWaitingScreen = true;
    }

    console.log("Finding for "+vHash);

    //Zielobjekt für die Ergebnisausgabe
    var target=$(container).attr("targetobj");
    if(target!==""){
        if($("#"+target).length==0){
            alert("Target Object "+target+" does not exist. Please check your configuration");
            return;
        }
        else{
            $("#"+target).wrap("<div class='tm_plugin'/>");
            $("#"+target).addClass("fahrtfinder2_result_container");
            $("#"+target).attr("hash2",vHash);
        }
    }

    var data = {};
    data["station_id"] = $(container).find("[name='station_id']").val();
    data["station_stop_id"] = 0;
    if($(container).find("[name='stop_station_id']").length>0){
        data["station_stop_id"] = $(container).find("[name='stop_station_id']").val();
    }
    data["hash"]=$(container).attr("hash");
    data['action'] = 'travelmanager_fahrtfinderv2_get_relations';
    data['linie_typ_id'] = $(container).attr("linie_typ_id");
    data['signets'] = $(container).attr("signets");
    data['linien_ids'] = $(container).attr("linien_ids");
    data['max'] = $(container).attr("max");
    data['vermittler_id'] = $(container).attr("ref");
    data['datum'] = $(container).find("[name='datum_abfahrt']").val();
    data['rand'] = Math.random();
    data['3days'] = $(container).find("[name='3days']:checked").length>0?1:0;

    if(showWaitingScreen){
        jQuery.blockUI({ message: dialoge["inhalt_laden"]+'...'});
    }
    myTimeout = setTimeout(function(){
        jQuery.post(ajaxurl, data, function(response) {

            $(".fahrtfinder2_result_container[hash2='"+vHash+"']").html("").removeClass("fehler");

            if($(response.result).length>0){

                var results = response.result;

                //Kategorien bestimmen
                var kategorien = {};
                for (result of results) {
                    if(result.linie_typ !== undefined){
                        kategorien[result.linie_typ_id]=result.linie_typ;
                    }
                }

                //Tabs
                if(Object.keys(kategorien).length>1 && $(container).hasClass("signet")==false){
                    var tabsContainer = $("<div class='tabscontainer' hash2='"+vHash+"'/>");
                    var ul = $("<ul/>");
                    for (kategorie_id in kategorien){
                        var li = $("<li><a href='#tabs-kategorie-id-"+kategorie_id+"-"+vHash+"'>"+kategorien[kategorie_id]+"</a></li>");
                        $(ul).append(li);
                    }
                    $(tabsContainer).append(ul);

                    for (kategorie_id in kategorien){
                        var div = $("<div id='tabs-kategorie-id-"+kategorie_id+"-"+vHash+"' class='fahrtfinder2_tabs_container' hash2='"+vHash+"'/>");
                        for (row of results) {
                            if(row.linie_typ_id == kategorie_id){
                                $(div).append(TMfahrtfinder2_finden_cellbuilder(row));
                            }
                        }
                        $(tabsContainer).append(div);
                    }
                    $(".fahrtfinder2_result_container[hash2='"+vHash+"']").append(tabsContainer);
                    $(tabsContainer).tabs();
                }
                //Keine Tabs - Alles in einen Container
                else{
                    $(".fahrtfinder2_result_container[hash2='"+vHash+"']").addClass("fahrtfinder2_tabs_container");
                    for (row of results) {
                        $(".fahrtfinder2_result_container[hash2='"+vHash+"']").append(TMfahrtfinder2_finden_cellbuilder(row));
                    }
                }

                //Repsonsive Ansicht
                $.each($(".fahrtfinder2_tabs_container[hash2='"+vHash+"']"),function(){
                    var obj=$(this);
                    var columns = $(this).find(".column").clone();
                    $(obj).find(".column").addClass("tm-show-medium-up");

                    //Karussel aufbauen
                    var slider = $("<div class='flexslider carousel'/>");
                    var ul = $('<ul class="slides"></ul>').appendTo($(slider));

                    // Jedes geklonte Element in ein <li> Element packen und zum <ul> hinzufügen
                    $(columns).each(function() {
                        var li = $('<li></li>');
                        $(li).append($(this));
                        $(ul).append(li);
                    });

                    $(slider).addClass("tm-show-small");
                    $(slider).flexslider({
                        animation: "slide",
                        prevText:"",
                        animationLoop: false,
                        nextText:"",
                        controlNav: false,
                        slideshow: false
                    });
                    $(obj).append($(slider));
                });
            }
            else{
                $(".fahrtfinder2_result_container[hash2='"+vHash+"']").html("<div class='fehler'>"+dialoge["no_result_was_found"]+"</div>");
            }

            //Div einblenden
            if($(obj).find(".qfinder").length>0){
                $("#ihre_touren").removeClass("hide");
            }

            jQuery.unblockUI();
        });

    }, 20);
}

function TMfahrtfinder2_finden_cellbuilder(row){
    var cell=$("<div class='column'></div>");

    //Bild
    var bildContainer = $("<div class='bildcontainer'></div>");
    if($(row.files).length>0){
        var bild = $("<img src='"+row.files[0]["thumbnail"]+"'/>");
        $(bildContainer).append(bild);
    }
    else{
        $(bildContainer).html(dialoge["kein_bild"]);
    }
    $(cell).append(bildContainer);

    //Headline
    var cellContent=$("<div class='container_content'></div>");
    $(cellContent).append($("<h2 class='tm_fahrtfinderv2_container_headline'>"+row["linie"]+"</h2>"));

    //Subheadline
    var subheadline = row["hafen_start"];
    if(row["hafen_start"]!=row["hafen_stop"]){
        subheadline+= " - "+row["hafen_stop"];
    }
    $(cellContent).append($("<h2 class='tm_fahrtfinderv2_container_subheadline'>"+subheadline+"</h2>"));

    //Beschreibung
    var beschreibung = row["beschreibung"];
    if(beschreibung==""){
        beschreibung = dialoge["no_infotext"];
    }
    $(cellContent).append($("<div class='tm_fahrtfinderv2_beschreibung'>"+beschreibung+"</div>"));
    $(cell).append(cellContent);

    //Schiff
    if(row["schiff"]!=="" && row["schiff"] != null && row["schiff"] !== undefined){
        $(cell).append($("<div class='tm_fahrtfinderv2_beschreibung_extra schiff'>"+row["schiff"]+"</div>"));
    }

    //Uhrzeiten
    if(row["abfahrtzeiten"].length>0){
        var abfahrtzeiten = row["abfahrtzeiten"].slice();
        var abfahrtzeitenString = abfahrtzeiten.join(" | ");
        $(cell).append($("<div class='tm_fahrtfinderv2_beschreibung_extra abfahrtzeiten'><span class='dialog_zeiten'>"+dialoge["zeiten"]+":</span> "+abfahrtzeitenString+"</div>"));
    }

    //Fahrtdauer
    var beschreibung = row["fahrtdauer"];
    if(beschreibung!=""){
        $(cell).append($("<div class='tm_fahrtfinderv2_beschreibung_extra fahrtdauer'>"+dialoge["fahrtdauer"]+": "+beschreibung+"</div>"));
    }

    //Deals
    var deals = row["beschreibung_extra"];
    if(deals!=""){
        $(cell).append($("<div class='tm_fahrtfinderv2_beschreibung_extra deals'>"+deals+"</div>"));
    }

    //Buchen Button
    var vAddTarget="";
    if($(".fahrtfinder2_container").attr("targeturl")!=""){
        vAddTarget = $(".fahrtfinder2_container").attr("targeturl");
    }

    var viewDate = "";
    if(row["abfahrtzeiten"].length>0){
        viewDate= row["datum"];
    }

    //Vermittler - ID
    var ref = $(".fahrtfinder2_container").first().attr("ref");

    var zielseite = vAddTarget+"?relation="+row["external_product_key"]+"&date="+viewDate+"&ref="+ref+"#booknow";
    var link = $("<div class='buchen_button'><a href='"+zielseite+"'>"+dialoge["more_info"]+"</a></div>");
    $(cell).append(link);

    //Trusted Shops
    var rating_code = row["rating_code"];
    if(rating_code!=""){
        $(cell).append($("<div class='tm_fahrtfinderv2_beschreibung_rating'>"+rating_code+"</div>"));
    }
    return cell;
}
