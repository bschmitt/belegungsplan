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
		eventClick:function (event) {
			getData(event.id);
			return false;
		},
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
		tips
			.text(t)
			.addClass("ui-state-highlight");
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
			"Speichern":function () {
				var bValid = true;
				allFields.removeClass("ui-state-error");
				bValid = bValid && checkLength(name, "name", 3, 100);
				bValid = bValid && checkRegexp(email, /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i, "Bitte geben Sie eine gültige Emailadresse ein.");
				//bValid = bValid && checkLength(phone, "phone", 2, 100);
				if (bValid) {
					calendar.fullCalendar('renderEvent', {
							title:'Angefragt',
							start:selectStart,
							end:selectEnd,
							allDay:selectAllDay
						},
						true // make the event "stick"
					);
					calendar.fullCalendar('unselect');
					$('#bookingRequest').val(1);
					$.post('<?php echo admin_url('admin-ajax.php'); ?>', $("#bookingForm").serialize(), function (data) {
						$(this).dialog("close");
						document.location.reload();
					});
				}
			},
			"Löschen":function () {
				$('#bookingRequest').val(1);
				var check = confirm("Wirklich löschen?");
				if (check != false) {
					$.post('<?php echo admin_url('admin-ajax.php'); ?>', 'delete=1&' + $("#bookingForm").serialize(), function (data) {
						$(this).dialog("close");
						document.location.reload();
					});
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

function openDialog() {
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
	return false;
}

function getData(id) {
	$.ajax({
			url:'<?php echo admin_url('admin-ajax.php'); ?>',
			type:'POST',
			dataType:'json',
			data:'action=belegungsplan&getBookingRequest=' + id,
			success:function (data) {
				data = data[0];
				$("#edit").val(data.id);
				var start = new Date(data.start * 1000);
				var end = new Date(data.end * 1000);
				$('#start').val(start.getDate() + '.' + (start.getMonth() + 1) + '.' + start.getFullYear());
				$('#end').val(end.getDate() + '.' + (end.getMonth() + 1) + '.' + end.getFullYear());
				$("#name").val(data.name);
				$("#title").val(data.title);
				$("#email").val(data.email);
				$("#phone").val(data.phone);
				if (data.published == 1) {
					$('#published').attr('checked', true);
				}
				if (data.private == 1) {
					$('#private').attr('checked', true);
				}
				openDialog();
			}
		}
	);
	return false;
}

</script>

<h1>Belegungsplan</h1>

<div class="clear"></div>
<div id='calendar'></div>

<div class="clear"></div>

<p style="font-weight: bold; padding-bottom: 10px;"><a href="#" onclick="$('#edit').val(''); return openDialog();">+ Buchung hinzufügen</a></p>

<?php $items = $this->getCalendarItems(true); ?>

<?php
$unpublished = "";
$items = $this->getCalendarItems(true);
if (!empty($items)) {
	foreach ($items as $i) {
		if ($i->published != 1) {
			$unpublished .= '<tr onclick="getData(' . $i->id . ')" style="cursor:pointer"><td>' . date('d.m.Y', $i->start) . ' - ' . date('d.m.Y', $i->end) . '</td><td><a href="#" onclick="return getData(' . $i->id . ');">' . $i->title . '</a></td><td>' . $i->name . '</td><td>' . $i->phone . '</td><td>' . (($i->private == 1) ? 'Privat' : 'Öffentlich') . '</td></tr>';
		}
	}
}

if (!empty($unpublished)) {
	?>

<h1>Ausstehend</h1>

<table class="wp-list-table widefat fixed posts" style="width:98.4%">
	<thead>
	<tr>
		<th>Datum</th>
		<th>Art</th>
		<th>Name</th>
		<th>Tel</th>
		<th>Privat</th>
	</tr>
	</thead>
	<tbody id="the-list">
		<?php echo $unpublished; ?>
	</tbody>
</table>

<?php } ?>

<h1>Angenommen</h1>

<table class="wp-list-table widefat fixed posts" style="width:98.4%">
	<thead>
	<tr>
		<th>Datum</th>
		<th>Art</th>
		<th>Name</th>
		<th>Tel</th>
		<th>Privat</th>
	</tr>
	</thead>
	<tbody id="the-list">
	<?php
	$items = $this->getCalendarItems(true);
	if (!empty($items)) {
		foreach ($items as $i) {
			if ($i->published == 1) {
				echo '<tr onclick="getData(' . $i->id . ')" style="cursor:pointer"><td>' . date('d.m.Y', $i->start) . ' - ' . date('d.m.Y', $i->end) . '</td><td><a href="#" onclick="return getData(' . $i->id . ');">' . $i->title . '</a></td><td>' . $i->name . '</td><td>' . $i->phone . '</td><td>' . (($i->private == 1) ? 'Privat' : 'Öffentlich') . '</td></tr>';
			}
		}
	}
	?>
	</tbody>
</table>

<div id="dialog-form" title="anfrage" style="display: none">
	<div class="validateTips">
		Zum Annehmen der Buchung die beiden folgenden Auswahlfelder aktivieren. Zum Ablehnen nur das 2. Auswahlfeld aktivieren und die Anfrage löschen (unten).
		<br/>Falls der Anfragende nicht per Email informatiert werden soll, einfach das 2. Auswahlfeld nicht aktivieren.
	</div>
	<form id="bookingForm">
		<fieldset>

			<div style="background-color:#f0f0b8; padding: 5px;margin-bottom: 10px;">

				<input type="checkbox" name="published" id="published" value="1"
					   class="checkbox ui-widget-content ui-corner-all" style="display: inline"/>
				Angenommen/Veröffentlicht</br>

				<input type="checkbox" name="notify" id="notify" value="1"
					   class="checkbox ui-widget-content ui-corner-all" style="display: inline"/>
				Anfragenden per Email informieren

			</div>

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
			<input type="checkbox" name="private" id="private" value="1"
				   class="checkbox ui-widget-content ui-corner-all" style="display: inline"/>
			Private Veranstaltung
			<input type="hidden" id="bookingRequest" name="bookingRequest" value=""/>
			<input type="hidden" id="edit" name="edit" value=""/>
			<input type="hidden" name="action" value="belegungsplan"/>
		</fieldset>
	</form>
</div>

