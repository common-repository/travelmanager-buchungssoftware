
var dialoge={};
if (typeof jQuery !== 'undefined'){

    var $ = jQuery;
    var myTimeout;


    jQuery(document).ready(function( $ ) {
        travelmanagerKalenderInfotextOpen();

        //List - Categories Ansicht
        setTimeout(function(){
            if($(".listcategories").length>0){
                $.each($(".listcategories .linientyp_id"),function(){
                    loadListCategorie(this);
                });
            }
        },11);

        if($("#tm_dialoge").first().length>0){
            dialoge = JSON.parse(decodeURIComponent($("#tm_dialoge").first().val()));
        }

        //Im Fahrtfinder - Mit Signets (Die Top-liste)
        if($(".fahrtfinder2_container[signets]").length>0){
            $.each($(".fahrtfinder2_container[signets]"),function(){
                TMfahrtfinder2_finden($(this),false);
            });
        }
        if($(".fahrtfinder2_container #station_id").length>0){
            var objSelect = $(".fahrtfinder2_container #station_id");
            var val = parseInt($(objSelect).val());
            var zielstationID = $(".fahrtfinder2_container #station_id").attr("zielstation_id");
            if(val>0){
                TMfahrtfinder2_set_zielstation(objSelect,zielstationID);
                setTimeout(function(){
                    jQuery.blockUI({ message: dialoge["inhalt_laden"]+'...'});
                    $("#button_finden").trigger("click");
                    $("#ihre_touren").removeClass("hide");
                })
            }
        }

    });

    function travelmanagerKalenderInfotextOpen(){
        if($(".eventcalendar.godirect").length>0){
            return true;
        }
        $.each($("div.eventycalendar div.eventlink[infotext]"),function(){
            var div = $(this);
            var href = $(div).find("a").attr("href");

            $(this).on("click",function(){
                var title=decodeURIComponent($(div).attr("headline"));
                var infotext=decodeURIComponent($(div).attr("infotext"));

                infotext+="<br><br><center><a href='"+href+"' target='_blank' class='button'>Jetzt buchen</a></center>";

                showDialog(title,infotext);
                return false;
            });
        });
    }

    function loadListCategorie(obj){
        var container = $(obj).closest(".listcategories");
        var linie_typ_id = $(container).find(".linientyp_id").val();
        var ts = $(container).find(".monat").val();
        var data = {};
        data["linie_typ_id"] = linie_typ_id;
        data["ts"] = ts;
        data["action"] = 'travelmanager_listcategories';
        data["hash"]=jQuery(container).attr("hash");

        jQuery.blockUI({ message: dialoge["inhalt_laden"]+'...'});
        jQuery.post(ajaxurl, data, function(response) {
            jQuery.unblockUI();
            if(response.html){
                var html = response.html;
                html+="<div class='wp-block-columns'>";
                html+="<div class='wp-block-column'>";
                //Allow Prev Button
                if($(obj).closest(".listcategories").find(".monat").find("option:selected").prev().length==1){
                    html+="<button class='button' onclick='listCategorieJump(this,false);'>Zurück</button>";
                }
                html+="</div>";
                html+="<div class='wp-block-column text-right'>";
                html+="<button class='button' onclick='listCategorieJump(this,true);'>Weiter</button>";
                html+="</div>";
                html+="</div>";

                jQuery(container).find(".fahrplan").html(html);
            }
        });
    }
    function listCategorieJump(obj,forward){

        var select = $(obj).closest(".listcategories").find(".monat");

        if(forward){
            var option = $(select).find("option:selected").next();
        }
        else{
            var option = $(select).find("option:selected").prev();
        }

        $(select).val($(option).attr('value'));
        loadListCategorie(obj);
    }

    function showDialogListCategorie(obj){
        var title=decodeURIComponent($(obj).attr("linie"));
        var infotext=decodeURIComponent($(obj).attr("infotext"));
        infotext+="<div class='text-center buchung'><a href='"+$(obj).attr("href")+"' class='button buttonbuchung'>Buchen</a></div>";
        showDialog(title,infotext);
    }

    function showDialogTimetableList(title,content,bookingLink){

        if(bookingLink!==""){
            var button =     {
                text: dialoge["jetzt_buchen"],
                action: function () {
                    window.location.href=bookingLink;
                }
            }
        }
        else{
            var button = null;
        }
        showDialog(title, content, null, button);

    }

    function showDialog(title, vMeldung, onclose, optionalButton) {
        if ($(".dialog_meldung_popup").length == 0) {
            var vObj = $('<div class="dialog_meldung_popup"></div>');
        } else {
            var vObj = $(".dialog_meldung_popup");
        }
        if (typeof vMeldung === 'object') {
            $(vObj).append(vMeldung);
            var vDestroy = false;
        } else {
            $(vObj).html(vMeldung);
            var vDestroy = true;
        }

        var width = $(window).width();
        if (width > 880) {
            width = 880;
        } else if (width > 480) {
            width = 480;
        }

        var height = $(window).height();
        if (height > 580) {
            height = 580;
        }

        var vIntervall;

        $(vObj).dialog({
            height: height,
            width: width,
            title: title,
            position: {
                my: "center",
                at: "center",
                of: window
            },
            modal: true,
            buttons: {
                "Close": {
                    text: dialoge["schliessen"],
                    click: function () {
                        $(this).dialog("close");
                    },
                    class: "ui-button-left"
                },
                ...(optionalButton ? {
                    "Optional": {
                        text: optionalButton.text,
                        click: optionalButton.action,
                        class: "ui-button-right"
                    }
                } : {})
            },
            open: function (event, ui) {
                $("body").css({ overflow: 'hidden' });
                $(".ui-dialog-content").scrollTop(0);
            },
            close: function (event, ui) {
                clearInterval(vIntervall);
                $("body").css({ overflow: 'inherit' });

                if (vDestroy == true) {
                    $(this).remove();
                }

                if (onclose != undefined) {
                    eval(onclose);
                }
            }
        });
    }



    function TMchangeMonth(obj){
        var data = {};
        data["extra_months"] = $(obj).closest(".eventycalendar").find("select.zeitraum").val();
        data["action"] = 'travelmanager_eventcalendar';
        data["hash"]=jQuery(obj).closest(".eventycalendar").attr("hash");
        data["station_id"] = $(obj).closest(".eventycalendar").find("select.station").val();
        data["category_id"] = $(obj).closest(".eventycalendar").find("select.category").val();

        try {
            clearTimeout(myTimeout);
        }
        catch (e) {}

        jQuery.blockUI({ message: dialoge["inhalt_laden"]+'...'});
        myTimeout = setTimeout(function(){
            jQuery.post(ajaxurl, data, function(response) {
                jQuery.unblockUI();
                if(response.html){
                    jQuery(obj).closest(".eventycalendar").find(".calendercontainer").html(response.html);

                    travelmanagerKalenderInfotextOpen();
                }
            });

        }, 20);
    }

    function TMFindenInteraktiv(obj){
        var data = {};
        data["data"] = $(obj).closest("form").serialize();
        data["action"] = 'travelmanager_list';
        data["hash"]=$(obj).attr("hash");

        try {
            clearTimeout(myTimeout);
        }
        catch (e) {}

        jQuery.blockUI({ message: dialoge["inhalt_laden"]+'...'});
        myTimeout = setTimeout(function(){
            jQuery.post(ajaxurl, data, function(response) {
                jQuery.unblockUI();
                if(response.html){
                    jQuery(obj).closest(".listcontainer").find(".suchergebnis").html(response.html);
                }
            });

        }, 20);
    }

    function TMFindenTabs(obj){

        var data = {};
        data["linie_typ_id"] = $(obj).closest(".containerfind").find("[name='linie_typ_id']").val();
        data["action"] = 'travelmanager_tabs';
        data["hash"]=$(obj).attr("hash");

        try {
            clearTimeout(myTimeout);
        }
        catch (e) {}

        jQuery.blockUI({ message: dialoge["inhalt_laden"]+'...'});
        myTimeout = setTimeout(function(){
            jQuery.post(ajaxurl, data, function(response) {

                //Objekt leeren
                $(obj).closest(".containerfind").find( '.findencontainer' ).html("");


                jQuery.unblockUI();
                if(response.html){
                    $(obj).closest(".containerfind").find(".findencontainer").html(response.html);

                    //Tabs initialisieren
                    $(obj).closest(".containerfind").find( '.jqueryuitabs' ).tabs();
                }
            });

        }, 20);
    }

}