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
	 * @param        Object $object Object
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
				return $this->createEvent($object, $user);
			case 'TASK_MODIFY':
				$this->logTrigger($action, $object->id);
				return $this->modifyEvent($object, $user);
			case 'TASK_DELETE':
				$this->logTrigger($action, $object->id);
				return $this->deleteEvent($object, $user);
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
	 * @param Task $task The related task
	 * @param User $user The related user
	 * @return int
	 */
	protected function createEvent($task, $user) {
		require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
		$event = new ActionComm($this->db);
		$event->type_code = 'AC_OTH'; 	// FIXME: Deprecated but still needed parameter, oh well…
		$event->code = 'AC_TASKEVENT_CREATE';
		$event->elementtype = 'task';
		$event->fk_element = $task->id;
		$event->fk_project = $task->fk_project;
		$event->label = $task->label;
		$event->datep = $task->date_start;
		$event->datef = $task->date_end;
		$event->percentage = $task->progress;
		return $event->add($user);
	}

	/**
	 * Modifies/updates the event related to the task
	 *
	 * @param Task $task The related task
	 * @param User $user The related user
	 * @return int
	 */
	protected function modifyEvent($task, $user) {
		$event = $this->getEvent($task);
		$event->code = 'AC_TASKEVENT_MODIFY';
		$event->fk_project = $task->fk_project;
		$event->label = $task->label;
		$event->datep = $task->date_start;
		$event->datef = $task->date_end;
		$event->percentage = $task->progress;
		return $event->update($user);
	}

	/**
	 * Deletes the event related to the task
	 *
	 * @param Task $task The related task
	 * @param User $user The related user
	 * @return int
	 */
	protected function deleteEvent($task, $user) {
		$event = $this->getEvent($task);
		return $event->delete($user);
	}

	/**
	 * Get the event related to the task
	 *
	 * @param Task $task Task
	 * @return ActionComm
	 */
	protected function getEvent($task) {
		$events = ActionComm::getActions($this->db, 0, $task->id, 'task');
		if (count($events) !== 1) {
			// TODO: Error, there should not be more than one event linked to a task
			dol_syslog(
				"More than one event found, using only first.",
				LOG_ERR
			);
		}
		return $events[0];
	}
}
