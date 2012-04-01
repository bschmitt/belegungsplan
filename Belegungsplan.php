<?php
/*
Plugin Name: Belegungsplan
Plugin URI: http://www.bseweb.de
Description: Einfacher Belegungsplan
Author: Björn Schmitt <bjoern@bseweb.de>
Version: 1.0.0
Author URI: http://www.bseweb.de
*/

error_reporting(E_ALL);
ini_set('display_errors', 1);

class Belegungsplan {

	/**
	 * @var string
	 */
	protected $pluginPath;

	/**
	 * @var string
	 */
	protected $pluginUrl;

	/**
	 * Wordpress DB object
	 * @var object
	 */
	protected $wpdb;

	/**
	 * @var string
	 */
	protected $table;

	/**
	 * @var string
	 */
	protected $infoEmail = 'agseite@koehlertage.de';

	/**
	 * Init
	 */
	public function __construct() {

		global $wpdb;

		// DB table
		$this->table = $wpdb->prefix . 'belegungsplan';

		// Set Plugin Path
		$this->pluginPath = dirname(__FILE__);

		// Set Plugin URL
		$this->pluginUrl = WP_PLUGIN_URL . '/belegungsplan';

		// WP DB object
		$this->wpdb = $wpdb;

		// Shortcode
		add_shortcode('Belegungsplan', array($this, 'shortcode'));

		// Add shortcode support for widgets
		add_filter('widget_text', 'do_shortcode');

		// Admin menu
		add_action('admin_menu', array($this, 'adminmenu'));

		// AJAX
		add_action('wp_ajax_nopriv_belegungsplan', array($this, 'wp_belegungsplan_ajax'));
		add_action('wp_ajax_belegungsplan', array($this, 'wp_belegungsplan_ajax'));

	}

	/**
	 * @return bool
	 */
	public function isAdmin() {

		global $user_ID;
		return $user_ID;
	}

	/**
	 * Page shortcodes
	 *
	 * @param $atts
	 * @return mixed
	 */
	public function shortcode($atts) {

		// extract the attributes into variables
		extract(shortcode_atts(array(
			'show' => 3,
			'width' => 50,
			'height' => 50,
			'caption' => true,
		), $atts));

		return $this->getCalendar();
	}

	/**
	 * Register admin menu
	 */
	public function adminmenu() {

		add_menu_page('Belebungsplan', 'Belegungsplan', 99, 'belegungsplan', array($this, 'getAdmin'), '', 22);
	}

	/**
	 * Retrive calendar items
	 *
	 * @param bool $raw
	 * @return array
	 */
	private function getCalendarItems($raw = false) {

		// get only future items
		$now = time() - 60 * 60 * 24 * 10;
		$res = $this->wpdb->get_results('SELECT * FROM ' . $this->table . ' WHERE end > "' . $now . '" ORDER BY start ASC');

		if ($raw) {
			return $res;
		}

		$items = array();
		foreach ($res as $i) {

			if (($i->private == 0 && $i->published == 1) || $this->isAdmin()) {
				$title = $i->title;
			} elseif ($i->published == 0) {
				$title = 'Angefragt';
			} else {
				$title = 'Privat';
			}

			$items[] = "id:" . $i->id . ", title:'" . $title . "',
			start:new Date(" . date('Y, n-1, d, g, G', $i->start) . "),
			end:new Date(" . date('Y, n-1, d, g, G', $i->end) . "),
			allDay:" . (($i->allday == 1) ? 'true' : 'false') . ",
			backgroundColor: '" . (($i->published == 0) ? '#eebb22' : '') . "'";
		}

		return $items;
	}

	/**
	 * Ajax callback function
	 */
	function wp_belegungsplan_ajax() {

		echo $this->handleRequest();
		exit;
	}

	/**
	 * Ajax request handler
	 *
	 * @return int|string
	 */
	public function handleRequest() {

		if (isset($_POST['bookingRequest'])) {

			$val = $data = array();
			$data['published'] = 0;
			$data['private'] = 0;

			foreach (array('start', 'end', 'name', 'email', 'title', 'private', 'phone', 'published') as $k) {
				if (in_array($k, array('private', 'published'))) {
					$val[$k] = "$k = '" . (!empty($_POST[$k]) ? 1 : 0) . "'";
					$data[$k] = (!empty($_POST[$k]) ? 1 : 0);
				}
				if (empty($_POST[$k])) {
					continue;
				}
				if (in_array($k, array('start', 'end'))) {
					$t = explode('.', $_POST[$k]);
					$val[$k] = "$k = '" . mktime(0, 0, 0, $t[1], $t[0], $t[2]) . "'";
					$data[$k] = mktime(0, 0, 0, $t[1], $t[0], $t[2]);
				} else {
					$val[$k] = "$k = '" . mysql_real_escape_string($_POST[$k]) . "'";
					$data[$k] = mysql_real_escape_string($_POST[$k]);
				}
			}

			if (is_numeric($_POST['edit']) && $this->isAdmin()) {
				$id = $_POST['edit'];
				if (isset($_POST['delete'])) {
					//echo "DELETE FROM " . $this->table . " WHERE id = '$id'";
					if ($this->wpdb->query("DELETE FROM " . $this->table . " WHERE id = '$id'")) {
						if (!empty($_POST['notify'])) {
							$this->sendStatusEmail($data, false);
						}
						return 1;
					}
				} else {
					//echo "UPDATE " . $this->table . " SET " . implode(',', $val) . ", allday = 1 WHERE id = '$id'";
					if (!empty($_POST['notify'])) {
						$this->sendStatusEmail($data, ($data['published'] == 1));
					}
					if ($this->wpdb->query("UPDATE " . $this->table . " SET " . implode(',', $val) . ", allday = 1 WHERE id = '$id'")) {
						return 1;
					}
				}
			} else {
				//echo "INSERT INTO " . $this->table . " SET " . implode(',', $val) . ", allday = 1";
				if ($this->wpdb->query("INSERT INTO " . $this->table . " SET " . implode(',', $val) . ", allday = 1")) {
					if (!$this->isAdmin()) {
						$this->sendInfoEmail($data);
					}
					return 1;
				}
			}
		}

		if (isset($_POST['getBookingRequest']) && $this->isAdmin()) {
			$id = $_POST['getBookingRequest'];
			if (is_numeric($id)) {
				$data = $this->wpdb->get_results("SELECT * FROM " . $this->table . " WHERE id = '$id'");
				return json_encode($data);
			}
		}

		return -1;
	}

	/**
	 * View for frontend
	 */
	public function getCalendar() {

		include($this->pluginPath . '/view/public.php');
	}

	/**
	 * View for backend
	 */
	public function getAdmin() {

		include($this->pluginPath . '/view/admin.php');
	}

	/**
	 * Send info email
	 *
	 * @param $data
	 */
	private function sendInfoEmail($data) {

		$msg = 'Köhlerhallenanfrage' . PHP_EOL;
		$msg .= '-------------------' . PHP_EOL . PHP_EOL;
		$msg .= 'Zeitraum:        ' . date('d.m.Y', $data['start']) . ' - ' . date('d.m.Y', $data['end']) . PHP_EOL;
		$msg .= 'Veranstaltung:   ' . $data['title'] . PHP_EOL;
		$msg .= 'Ansprechpartner: ' . $data['name'] . ' (Tel: ' . $data['phone'] . ')' . PHP_EOL;
		$msg .= 'Email:           ' . $data['email'] . PHP_EOL;
		$msg .= 'Privat:          ' . (($data['private'] == 1) ? 'Ja' : 'Nein') . PHP_EOL . PHP_EOL;
		$msg .= 'Verwaltung:      http://www.ag-walhausen.de/wp-admin/admin.php?page=belegungsplan';

		$header = 'From: Köhlerhalle <website@ag-walhausen.de>' . PHP_EOL .
			'Reply-To: ' . $data['email'] . PHP_EOL .
			'X-Mailer: PHP/' . phpversion();

		mail($this->infoEmail, 'Hallenanfrage', $msg, $header);
	}

	/**
	 * Send status email
	 *
	 * @param $data
	 * @param bool $accepted
	 */
	private function sendStatusEmail($data, $accepted = true) {

		$msg = 'Köhlerhallenanfrage' . PHP_EOL;
		$msg .= '-------------------' . PHP_EOL . PHP_EOL;
		$msg .= 'Zeitraum:        ' . date('d.m.Y', $data['start']) . ' - ' . date('d.m.Y', $data['end']) . PHP_EOL;
		$msg .= 'Veranstaltung:   ' . $data['title'] . PHP_EOL;
		$msg .= 'Ansprechpartner: ' . $data['name'] . ' (Tel: ' . $data['phone'] . ')' . PHP_EOL;
		$msg .= 'Email:           ' . $data['email'] . PHP_EOL;
		$msg .= 'Privat:          ' . (($data['private'] == 1) ? 'Ja' : 'Nein') . PHP_EOL . PHP_EOL;

		if ($accepted) {
			$msg .= 'Vielen Dank für Ihre Anfrage. Hiermit bestätigen wir die Buchung.' . PHP_EOL . PHP_EOL;
			$msg .= 'Die Schlüsselübergabe, Infos zum Ein- und Ausräumen und die Hallenübergabe erfolgt durch unseren Hausmeister Herrn Gerhard Kreuz: Auf der Ritzwiese 15, 66625 Nohfelden-Walhausen, Tel. 06852 81203 oder 06852 284965.' . PHP_EOL;
			$msg .= 'Bitte vereinbaren Sie dazu einen Termin.' . PHP_EOL . PHP_EOL;
			$msg .= 'Adresse Köhlerhalle: Brunnenstrasse 18a, Hallentelefon: 06852 82300' . PHP_EOL . PHP_EOL;
			$msg .= 'Weitere Informationen entnehmen Sie bitte unserer Webseite:' . PHP_EOL . 'http://www.ag-walhausen.de/kohlerhalle/';
		} else {
			$msg .= 'Vielen Dank für Ihre Anfrage. Leider ist eine Vermietung zum besagten Zeitraum nicht möglich. Wir bitten Sie dies zu entschuldigen.';
		}

		$msg .= PHP_EOL . PHP_EOL . 'Mit freundlichen Grüßen,' . PHP_EOL . 'Ihre Arbeitsgemeinschaft Walhauser Vereine';

		$header = 'From: Köhlerhalle <website@ag-walhausen.de>' . PHP_EOL .
			'Reply-To: ' . $data['email'] . PHP_EOL .
			'X-Mailer: PHP/' . phpversion();

		mail($this->infoEmail, 'Hallenanfrage', $msg, $header);
	}

	/**
	 * Importer
	 */
	public function import() {

		$res = $this->wpdb->get_results('select * from wp_agwv_booking');

		foreach ($res as $i) {

			$start = $this->wpdb->get_col('select min(booking_date) from wp_agwv_bookingdates where booking_id = ' . $i->booking_id);

			$t = explode(' ', $start[0]);
			$d = explode(' - ', $t[0]);
			$z = explode(':', $t[1]);

			$start = mktime($z[0], $z[1], 0, $d[1], $d[2], $d[0]);

			$end = $this->wpdb->get_col('select max(booking_date) from wp_agwv_bookingdates where booking_id = ' . $i->booking_id);

			$t = explode(' ', $end[0]);
			$d = explode(' - ', $t[0]);
			$z = explode(':', $t[1]);

			$end = mktime($z[0], $z[1], 0, $d[1], $d[2], $d[0]);

			/*
					* text^name1^Rosenmontagstreiben text^secondname1^AG Fasching~email^email1^bjoern@walhausen.de~text^phone1^~textarea^details1^Rosenmontagstreiben
					*/

			$t = explode('~', $i->form);

			$res = array();
			foreach ($t as $l) {
				$q = explode(" ^", $l);
				$res[$q[1]] = $q[2];
			}

			/*
					* Array ( [name1] => Rosenmontagstreiben [secondname1] => AG Fasching [email1] => bjoern@walhausen.de [phone1] => [details1] => Rosenmontagstreiben )
					*/

			print_r($res);

			$this->wpdb->query("INSERT INTO " . $this->table . " SET title = '$res[name1]', name = '$res[secondname1]', email = '$res[email1]', phone = '$res[phone1]', description = '$res[details1]', start = $start, end = $end, allday = 1, published = 1");

			//$this->wpdb->query('INSERT INTO '.$this->table.' ')

		}

		exit;

	}

}

/**
 * Initialize
 */
$wpBelegungsplan = new Belegungsplan();