<?php

namespace pulse;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class PulseAdminController extends \Http\Controller
{

    public function get(\Canopy\Request $request)
    {
        $data = array();
        $view = $this->getView($data, $request);
        $response = new \Canopy\Response($view);
        return $response;
    }

    public function getHtmlView($data, \Canopy\Request $request)
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
        $view = new \phpws2\View\HtmlView(\PHPWS_ControlPanel::display($panel));
        return $view;
    }

    public function post(\Canopy\Request $request)
    {
        $cmd = $request->shiftCommand();

        switch ($cmd) {
            case 'toggleAllow':
                \phpws2\Settings::set('pulse', 'allow_web_access', (\phpws2\Settings::get('pulse', 'allow_web_access') - 1) * -1);
                $response = new \Http\SeeOtherResponse(\Canopy\Server::getSiteUrl() . 'pulse/admin/');
                break;
        }
        return $response;
        exit;
    }

    public function getJsonView($data, \Canopy\Request $request)
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

    private function pager(\Canopy\Request $request)
    {
        \Pager::prepare();
        $template = new \phpws2\Template;
        $template->setModuleTemplate('pulse', 'pager.html');
        if (\phpws2\Settings::get('pulse', 'allow_web_access')) {
            $template->add('button_class', 'btn-success');
            $template->add('button_status', 'Web Access Allowed');
            $template->add('button_icon', 'fa-check');
            $template->add('button_title', 'Pulse will process schedules via the web.');
        } else {
            $template->add('button_class', 'btn-danger');
            $template->add('button_status', 'Web Access Denied');
            $template->add('button_icon', 'fa-ban');
            $template->add('button_title', 'Pulse will not allow access via the web.');
        }
        return $template;
    }

    private function listSchedules(\Canopy\Request $request)
    {
        $db = \phpws2\Database::getDB();
        $schedule_table = $db->addTable('pulse_schedule');

        $pager = new \phpws2\DatabasePager($db);
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
