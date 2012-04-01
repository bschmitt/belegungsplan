<link rel='stylesheet' type='text/css' href='<?php echo $this->pluginUrl; ?>/css/belegungsplan.css'/>
<link rel='stylesheet' type='text/css' href='<?php echo $this->pluginUrl; ?>/js/fullcalendar/fullcalendar.css'/>
<link rel='stylesheet' type='text/css'
      href='<?php echo $this->pluginUrl; ?>/js/jquery/smoothness/jquery-ui-1.8.18.custom.css'/>
<link rel='stylesheet' type='text/css' href='<?php echo $this->pluginUrl; ?>/js/fullcalendar/fullcalendar.print.css'
      media='print'/>
<script type='text/javascript' src='<?php echo $this->pluginUrl; ?>/js/jquery/jquery-1.7.1.min.js'></script>
<script type='text/javascript' src='<?php echo $this->pluginUrl; ?>/js/jquery/jquery-ui-1.8.18.custom.min.js'></script>
<script type='text/javascript' src='<?php echo $this->pluginUrl; ?>/js/fullcalendar/fullcalendar.min.js'></script>
<script type='text/javascript'>

    var selectStart;
    var selectEnd;
    var selectAllDay;

    $(function () {

        var date = new Date();
        var d = date.getDate();
        var m = date.getMonth();
        var y = date.getFullYear();

        var calendar = $('#calendar').fullCalendar({
            editable:true,
            height:500,
            firstDay:1,
            buttonText:{
                month:'Monat',
                week:'Woche',
                day:'Tag',
                today:'Heute',
                prev:'&lt;',
                next:'&gt;'
            },
            monthNames:['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
            monthNamesShort:['Jan', 'Feb', 'Mrz', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
            dayNames:['Sonntag', 'Montag', 'Diestag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
            dayNamesShort:['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
            timeFormat:{
                agenda:'H:mm{ - H:mm}',
                '':'H:mm'
            },

            selectable:true,
            selectHelper:true,
            select:function (start, end, allDay) {
                selectStart = start;
                selectEnd = end;
                selectAllDay = allDay;
                $('#title').focus();
                $('#start').val($.fullCalendar.formatDate(start, "dd.MM.yyyy"));
                $('#end').val($.fullCalendar.formatDate(start, "dd.MM.yyyy"));

                var dates = $("#start, #end").datepicker({
                    changeMonth:true,
                    onSelect:function (selectedDate) {
                        var option = this.id == "start" ? "minDate" : "maxDate",
                                instance = $(this).data("datepicker"),
                                date = $.datepicker.parseDate(
                                        instance.settings.dateFormat ||
                                                $.datepicker._defaults.dateFormat,
                                        selectedDate, instance.settings);
                        dates.not(this).datepicker("option", option, date);
                    }
                });

                $("#dialog-form").dialog("open");
            },
            events:[
            <?php
            $items = $this->getCalendarItems();
            if (!empty($items)) {
                echo '{' . implode('},{', $items) . '}';
            }
            ?>
            ]
        });

        $("#dialog:ui-dialog").dialog("destroy");

        var name = $("#name"),
                title = $("#title"),
                email = $("#email"),
                phone = $("#phone"),
                private = $("#private"),
                start = $("#start"),
                end = $("#end"),
                allFields = $([]).add(name).add(email).add(phone).add(title).add(private).add(start).add(end),
                tips = $(".validateTips");

        function updateTips(t) {
            tips.text(t).addClass("ui-state-highlight");
            setTimeout(function () {
                tips.removeClass("ui-state-highlight", 1500);
            }, 500);
        }

        function checkLength(o, n, min, max) {
            if (o.val().length > max || o.val().length < min) {
                o.addClass("ui-state-error");
                updateTips("Bitte geben Sie mindestens " + min + " Zeichen ein.");
                return false;
            } else {
                return true;
            }
        }

        function checkRegexp(o, regexp, n) {
            if (!( regexp.test(o.val()) )) {
                o.addClass("ui-state-error");
                updateTips(n);
                return false;
            } else {
                return true;
            }
        }

        $("#dialog-form").dialog({
            autoOpen:false,
            height:550,
            width:450,
            modal:true,
            buttons:{
                "Anfragen":function () {
                    var bValid = true;
                    allFields.removeClass("ui-state-error");
                    bValid = bValid && checkLength(name, "name", 3, 100);
                    bValid = bValid && checkRegexp(email, /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i, "Bitte geben Sie eine gültige Emailadresse ein.");
                    bValid = bValid && checkLength(phone, "phone", 2, 100);
                    if (bValid) {
                        calendar.fullCalendar('renderEvent',
                                {
                                    title:'Angefragt',
                                    start:selectStart,
                                    end:selectEnd,
                                    allDay:selectAllDay
                                },
                                true // make the event "stick"
                        );
                        calendar.fullCalendar('unselect');
                        $('#bookingRequest').val(1);
                        $.post('<?php echo admin_url('admin-ajax.php'); ?>', $("#bookingForm").serialize());
                        $(this).dialog("close");
                    }
                },
                "Abbrechen":function () {
                    $(this).dialog("close");
                }
            },
            close:function () {
                allFields.val("").removeClass("ui-state-error");
            }
        });

        $.datepicker.regional['de'] = {clearText:'löschen', clearStatus:'aktuelles Datum löschen',
            closeText:'schließen', closeStatus:'ohne Änderungen schließen',
            prevText:'<zurück', prevStatus:'letzten Monat zeigen',
            nextText:'Vor>', nextStatus:'nächsten Monat zeigen',
            currentText:'heute', currentStatus:'',
            monthNames:['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni',
                'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
            monthNamesShort:['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun',
                'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
            monthStatus:'anderen Monat anzeigen', yearStatus:'anderes Jahr anzeigen',
            weekHeader:'Wo', weekStatus:'Woche des Monats',
            dayNames:['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
            dayNamesShort:['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
            dayNamesMin:['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
            dayStatus:'Setze DD als ersten Wochentag', dateStatus:'Wähle D, M d',
            dateFormat:'dd.mm.yy', firstDay:1,
            initStatus:'Wähle ein Datum', isRTL:false};
        $.datepicker.setDefaults($.datepicker.regional['de']);

    });

</script>

<div class="clear"></div>
<div id='calendar'></div>
<div class="clear"></div>

<div id="dialog-form" title="Anfrage" style="display: none">
    <div class="validateTips">Damit wir Ihre Buchungsanfrage entgegennehmen können, benötigen wir die folgenden
        Angaben:
    </div>
    <form id="bookingForm">
        <fieldset>
            <label for="title">Art der Veranstaltung</label>
            <input type="text" name="title" id="title" class="text ui-widget-content ui-corner-all"/>
            <label for="start">Zeitraum</label>

            <div style="padding-bottom:10px">
                <input type="text" name="start" id="start" value="" class="date ui-widget-content ui-corner-all"
                       style="display: inline-block"/> bis
                <input type="text" name="end" id="end" value="" class="date ui-widget-content ui-corner-all"
                       style="display: inline-block"/>
            </div>
            <label for="name">Ansprechpartner</label>
            <input type="text" name="name" id="name" class="text ui-widget-content ui-corner-all"/>
            <label for="email">Emailadresse</label>
            <input type="text" name="email" id="email" value="" class="text ui-widget-content ui-corner-all"/>
            <label for="phone">Telefon</label>
            <input type="text" name="phone" id="phone" value="" class="text ui-widget-content ui-corner-all"/>
            <input type="checkbox" name="private" id="private" checked value="1"
                   class="checkbox ui-widget-content ui-corner-all" style="display: inline"/>
            Private Veranstaltung
            <input type="hidden" id="bookingRequest" name="bookingRequest" value=""/>
            <input type="hidden" name="action" value="belegungsplan"/>
        </fieldset>
    </form>
</div>

