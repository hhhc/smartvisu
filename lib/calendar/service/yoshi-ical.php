<?php
/**
  * Achtung! class.iCalReader.php kann nur Wiederholungen mit ENDEDATUM, nicht mit der Anzahl!!!
  * jsaemann@firewire.franken.de
  */

require_once '../../../lib/includes.php';
require_once const_path_system.'calendar/calendar.php';
require_once const_path_system.'calendar/class.iCalReader.php';

class calendar_yoshi_ical extends calendar
{
	function sortICSbyDTSTART( $a, $b ) {
		return strtotime($a["DTSTART"]) - strtotime($b["DTSTART"]);
	}

	public function init($request) {
		parent::init($request);
	}

	public function run() {
		$icsdatei   = new ICal($this->url);
		$icsevents = $icsdatei->events();
		$this->debug($icsevents);

		if ($icsevents !== false)
		{
			# Sortiere ical-Datei nach Event-Startdatum
			usort($icsevents, array($this, "sortICSbyDTSTART"));

			$eventid = 0;
			foreach ($icsevents as $icsevent)
			{
				if ($eventid < $this->count) {
					$startstamp = strtotime($icsevent['DTSTART']);
					$endstamp = strtotime($icsevent['DTEND']);

					// only export entries in the future
					if ( $endstamp > date('U')) { 
						$this->data[$eventid] = array('pos' => $eventid,
							'start' => date('y-m-d', $startstamp).' '.date('H:i:s', $startstamp),
							'end' => date('y-m-d', $endstamp).' '.date('H:i:s', $endstamp),
							'title' => stripslashes(($icsevent['SUMMARY'])),
							'where' => stripslashes(($icsevent['LOCATION'])),
						);
                        switch ($icsevent['SUMMARY']) {
                                case "Hausmüll 14-tägig":                           
                                        $this->data[$eventid][icon] = "icons/ws/message_garbage.png";
                                        $this->data[$eventid][color] = "#333399";       
                                break;                                          
                                case "Grünabfall":                            
                                        $this->data[$eventid][icon] = "icons/ws/message_garbage.png";
                                        $this->data[$eventid][color] = "#cc00cc";       
                                break;                                          
                                case "Altpapier":                             
                                        $this->data[$eventid][icon] = "icons/ws/message_garbage.png";
                                        $this->data[$eventid][color] = "#0000ff";       
                                break;                                          
                                case "Gelber Sack":                             
                                        $this->data[$eventid][icon] = "icons/ws/message_garbage.png";
                                        $this->data[$eventid][color] = "#ffff00";       
                                break;                                          
                                default:        
                                        if (strpos($icsevent['DESCRIPTION'],'§') !== false) {
                                                $treffer = explode('§', $icsevent['DESCRIPTION']);
                                                foreach($treffer as $versenkt) {                
                                                        $felder = explode(' ', trim($versenkt, ' \n')); 
                                                        if (!empty($felder['1'])) {                     
                                                                $this->data[$eventid][$felder['0']] = stripslashes($felder['1']);
                                                        }                                               
                                                }                                               
                                        } else {                                        
                                                $this->data[$eventid]['content'] = $icsevent['DESCRIPTION'] . "desc_else";
                                        }                                               
                                break;                                          
                        }  
						$eventid++;
					}
				}
			}
		} else {
			$this->error('Calendar: ICAL', 'Calendar read request failed!');
		}
	}
}

$service = new calendar_yoshi_ical(array_merge($_GET, $_POST));
echo $service->json();
?>
