<?php
/* Module to manage resources into Dolibarr ERP/CRM
 * Copyright (C) 2014  Raphaël Doursenaud
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

dol_include_once('/resource/core/modules/modResource.class.php');

/**
 *    \file     core/triggers/interface_50_modResource_TaskEvents.class.php
 *    \ingroup  resource
 *    \brief    Project tasks events
 */

/**
 * Trigger class
 */
class InterfaceTaskEvents
{
	/**
	 * Database object
	 *
	 * @var DoliDB
	 */
	private $db;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $family;

	/**
	 * @var string
	 */
	public $description;

	/**
	 * @var string
	 */
	public $version;

	/**
	 * @var string
	 */
	public $picto;

	/**
	 * @var Task
	 */
	private $_task;

	/**
	 * Constructor
	 *
	 * @param        DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		$module = new modResource($db);
		$this->db = $db;
		$this->name = preg_replace('/^Interface/i', '', get_class($this));
		$this->family = $module->family;
		$this->description = "These triggers create events from tasks to help scheduling affected resources";
		$this->version = $module->version;
		//$this->picto = $module->picto;
	}

	/**
	 * Trigger name
	 *
	 * @return        string    Name of trigger file
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Trigger description
	 *
	 * @return        string    Description of trigger file
	 */
	public function getDesc()
	{
		return $this->description;
	}

	/**
	 * Trigger version
	 *
	 * @return        string    Version of trigger file
	 */
	public function getVersion()
	{
		global $langs;
		$langs->load("admin");

		if ($this->version == 'development') {
			return $langs->trans("Development");
		} elseif ($this->version == 'experimental') {
			return $langs->trans("Experimental");
		} elseif ($this->version == 'dolibarr') {
			return DOL_VERSION;
		} elseif ($this->version) {
			return $this->version;
		}
		else {
			return $langs->trans("Unknown");
		}
	}

	/**
	 * Function called when a Dolibarrr business event is done.
	 * All functions "run_trigger" are triggered if file
	 * is inside directory core/triggers
	 *
	 * @param        string $action Event action code
	 * @param        CommonObject $object Object
	 * @param        User $user Object user
	 * @param        Translate $langs Object langs
	 * @param        conf $conf Object conf
	 * @return        int                        <0 if KO, 0 if no triggered ran, >0 if OK
	 */
	public function run_trigger($action, $object, $user, $langs, $conf)
	{
		switch ($action) {
			case 'TASK_CREATE':
				$this->logTrigger($action, $object->id);
				$this->_task = $object;
				return $this->createEvent($user);
				// TODO: add project resources to the event
			case 'TASK_MODIFY':
				$this->logTrigger($action, $object->id);
				$this->_task = $object;
				return $this->modifyEvent($user);
			case 'TASK_DELETE':
				$this->logTrigger($action, $object->id);
				$this->_task = $object;
				return $this->deleteEvent();
			case 'ACTION_MODIFY':
				$this->logTrigger($action, $object->id);
				if($this->isEventTask($object)) {
					return $this->modifyTask($object, $user);
				}
				break;
			case 'ACTION_DELETE':
				$this->logTrigger($action, $object->id);
				if($this->isEventTask($object)) {
					return $this->deleteTask($object, $user);
				}
				break;
			case 'PROJECT_RESOURCE_ADD':
				$this->logTrigger($action, $object->id);
				// TODO: add resource to all project tasks
			case 'PROJECT_RESOURCE_MODIFY':
				$this->logTrigger($action, $object->id);
				// TODO: modify resources on all project tasks
			case 'PROJECT_RESOURCE_DELETE':
				$this->logTrigger($action, $object->id);
				// TODO: delete resource from all project tasks
			case 'ACTION_RESOURCE_ADD':
				$this->logTrigger($action, $object->id);
				// TODO: prevent adding resources to eventtasks
			case 'ACTION_RESOURCE_MODIFY':
				$this->logTrigger($action, $object->id);
				// TODO: prevent modifying resources on eventtasks
			case 'ACTION_RESOURCE_DELETE':
				$this->logTrigger($action, $object->id);
				// TODO: don't allow deleting resources on eventtasks
			default:
				return 0;
		}
	}

	/**
	 * Log the trigged event
	 *
	 * @param string $action Trigged action
	 * @param int $id Object id
	 * @return void
	 */
	protected function logTrigger($action, $id) {
		dol_syslog(
			"Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $id
		);
	}

	/**
	 * Creates a new event from the task
	 *
	 * @param User $user The related user
	 * @return int
	 */
	protected function createEvent($user) {
		require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
		$event = new ActionComm($this->db);
		$event->type_code = 'AC_OTH'; 	// FIXME: Deprecated but still needed parameter, oh well…
		$event->code = 'AC_TASKEVENT_CREATE';
		$event->elementtype = $this->_task->element;
		$event->fk_element = $this->_task->id;
		$event->fk_project = $this->_task->fk_project;
		$event->label = $this->_task->label;
		$event->datep = $this->_task->date_start;
		$event->datef = $this->_task->date_end;
		$event->percentage = $this->_task->progress;
		$event->note = $this->_task->description;
		return $event->add($user);
	}

	/**
	 * Modifies/updates the event related to the task
	 *
	 * @param User $user The related user
	 * @return int
	 */
	protected function modifyEvent($user) {
		$event = $this->getEvent();
		if(empty($event) === false) {
			$event->code = 'AC_TASKEVENT_MODIFY';
			$event->fk_project = $this->_task->fk_project;
			$event->label = $this->_task->label;
			$event->datep = $this->_task->date_start;
			$event->datef = $this->_task->date_end;
			$event->percentage = $this->_task->progress;
			$event->note = $this->_task->description;
			return $event->update($user, true);
		}
		// Could not get an event
		return -1;
	}

	/**
	 * Deletes the event related to the task
	 *
	 * @return int
	 */
	protected function deleteEvent() {
		$event = $this->getEvent();
		if(empty($event) === false) {
			return $event->delete();
		}
		// Could not get an event
		return -1;
	}

	/**
	 * Get the event related to the task
	 *
	 * @return ActionComm
	 */
	protected function getEvent() {
		require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
		$events = ActionComm::getActions($this->db, 0, $this->_task->id, $this->_task->element);
		// Only keep event/task events
		$events = array_filter($events, "self::isEventTask");
		if (count($events) !== 1) {
			// TODO: Error, there should not be more than one event linked to a task
			dol_syslog(
				"More than one event found, using only first.",
				LOG_ERR
			);
		}
		// Return ony the first event
		reset($events);
		return $events[key($events)];
	}

	/**
	 * Modifies/updates the task related to the event
	 *
	 * @param ActionComm $event The event
	 * @param User $user The related user
	 * @return int
	 */
	protected function modifyTask($event, $user) {
		$this->getTask($event);
		$this->_task->label = $event->label;
		$this->_task->date_start = $event->datep;
		$this->_task->date_end = $event->datef;
		$this->_task->progress = $event->percentage;
		$this->_task->description = $event->note;
		return $this->_task->update($user, true);
	}

	/**
	 * Deletes the task related to the event
	 *
	 * @param ActionComm $event The event
	 * @param User $user The related user
	 * @return int
	 */
	protected function deleteTask($event, $user) {
		$this->getTask($event);
		return $this->_task->delete($user, true);
	}

	/**
	 * Check if event is related to a task
	 *
	 * @param ActionComm $object The object to check
	 * @return bool
	 */
	protected function isEventTask($object) {
		// The passed object is partial, let's get it in full
		$object->fetch($object->id);
		if(strstr($object->code, 'AC_TASKEVENT') && $object->elementtype==='project_task') {
			return true;
		}
		return false;
	}

	/**
	 * Get the task related to the event
	 *
	 * @param ActionComm $event The event
	 * @return void
	 */
	protected function getTask($event) {
		require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
		$this->_task = new Task($this->db);
		$this->_task->fetch($event->fk_element);
	}
}
