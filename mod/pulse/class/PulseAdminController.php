<?php

namespace pulse;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class PulseAdminController extends \Http\Controller
{

    public function get(\Request $request)
    {
        $data = array();
        $view = $this->getView($data, $request);
        $response = new \Response($view);
        return $response;
    }

    public function getHtmlView($data, \Request $request)
    {
        $cmd = $request->shiftCommand();
        if (empty($cmd)) {
            $cmd = 'pager';
        }
        //$this->loadMenu($cmd);

        switch ($cmd) {
            case 'pager':
                $template = $this->pager($request);
                break;
            case 'settings':
                $template = $this->settings($request);
                break;
        }

        $panel = $template->get();
        $view = new \View\HtmlView(\PHPWS_ControlPanel::display($panel));
        return $view;
    }

    public function getJsonView($data, \Request $request)
    {
        $cmd = $request->shiftCommand();
        switch ($cmd) {
            case 'pager':
                return $this->listSchedules($request);
                break;

            default:
                throw new \Exception('JSON command not found');
        }

        return parent::getJsonView($data, $request);
    }

    private function pager(\Request $request)
    {
        \Pager::prepare();
        $template = new \Template;
        $template->setModuleTemplate('pulse', 'pager.html');
        return $template;
    }

    private function listSchedules(\Request $request)
    {
        $db = \Database::getDB();
        $schedule_table = $db->addTable('pulse_schedule');

        $pager = new \DatabasePager($db);
        $pager->setId('schedule-list');

        $headers = array(
            'status' => 'Status',
            'id' => 'ID',
            'name' => 'Name',
            'interim' => 'Interim',
            'execute_after' => 'Execute after',
            'start_time' => 'Start time',
            'end_time' => 'End time'
        );
        $pager->setHeaders($headers);

        $table_headers['status'] = $schedule_table->getField('status');
        $table_headers['id'] = $schedule_table->getField('id');
        $table_headers['name'] = $schedule_table->getField('name');
        $table_headers['interim'] = $schedule_table->getField('interim');
        $table_headers['execute_after'] = $schedule_table->getField('execute_after');
        $table_headers['start_time'] = $schedule_table->getField('start_time');
        $table_headers['end_time'] = $schedule_table->getField('end_time');
        $pager->setTableHeaders($table_headers);
        $pager->setRowIdColumn('id');
        $pager->setCallback(array('pulse\\PulseFactory', 'pagerRows'));
        $data = $pager->getJson();
        return parent::getJsonView($data, $request);
    }

}
