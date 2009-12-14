<?php
require_once(PHPWS_SOURCE_DIR . 'core/class/Pager.php');

/**
 * Provides listing and selection functionality for generic Item handling.
 *
 * This object provides functions for managing any PHPWS_Item, from listing
 * items to mass updates of generic variables within those items.  Any item
 * which extends PHPWS_Item could also have a manager class which extends
 * PHPWS_Manager to manage those items.
 *
 * *********************************
 * * Manager Config File           *
 * *********************************
 * File Name: manager.php
 * This file must be located in the conf/ directory for the module passed to
 * PHPWS_Manager::setModule()
 * For an example look in the docs/developer/ directory of your phpwebsite base
 *
 * Note: this file was modified to work under phpWebSite 1.x.
 *
 * @version $Id$
 * @author  Steven Levin
 * @author  Adam Morton
 * @modified Matthew McNaney <mcnaney at gmail dot com>
 * @package Core
 */
class PHPWS_Manager {

    /**
     * The name of the current module extending manager.
     *
     * @var    string
     * @access private
     * @see    setModule()
     */
    var $_module = NULL;

    /**
     * The name of the table to pull items from.
     *
     * @var    string
     * @access private
     * @see    setTable()
     */
    var $_table = NULL;

    /**
     * The name of the request variable to pass the action.
     *
     * @var    string
     * @access private
     * @see    setRequest()
     */
    var $_request = NULL;

    /**
     * The class name of the oject to instantiate
     *
     * @var    string
     * @access private
     * @see    setClass()
     */
    var $_class = NULL;

    /**
     * The lists defined in the current modules manager config file.
     *
     * @var    array
     * @access private
     * @see    init(), getList()
     */
    var $_lists = array();

    /**
     * The name of the tables to use for each list defined in the list array.
     *
     * @var    array
     * @access private
     * @see    init(), getList()
     */
    var $_tables = array();

    /**
     * The templates defined in the current modules manager config file.
     *
     * @var    array
     * @access private
     * @see    init(), getList()
     */
    var $_templates = array();

    /**
     * The columns defined in the current modules manager config file
     *
     * @var    array
     * @access private
     * @see    init(), getList(), getItems()
     */
    var $_listColumns = array();

    /**
     * The actions for a defined list.
     *
     * @var    array
     * @access private
     * @see    init(), getList()
     */
    var $_listActions = array();

    /**
     * The permissions for the actions of a defined list.
     *
     * @var    array
     * @access private
     * @see    init(), getList()
     */
    var $_listPermissions = array();

    /**
     * Any extra labels for list items.
     *
     * @var    array
     * @access private
     * @see    init(), getList()
     */
    var $_listExtraLabels = array();

    /**
     * The pager settings for each of the lists defined.
     *
     * @var    array
     * @access private
     * @see    init(), getList()
     */
    var $_listPaging = array();

    /**
     * The values to use for the PHPWS_Items hidden and approved
     *
     * @var    array
     * @access private
     * @see    init(), getList()
     */
    var $_listValues = array();

    /**
     * An array of groups to limit item lists by.
     *
     * If this variable is set, PHPWS_Manager will limit it's item lists
     * by only showing the items that belong to a group in this array.
     *
     * @var    array
     * @access private
     */
    var $_groups = array();

    /**
     * An sql where clause used to sort items in lists
     *
     * @var    string
     * @access private
     * @see    setSort(), getSort()
     */
    var $_sort = NULL;

    /**
     * Sql ORDER BY passed in by the developer
     *
     * @var    array
     * @access private
     * @see    setOrder(), getOrder()
     */
    var $_order = NULL;

    /**
     * Properties for the sql ORDER BY to order items in lists
     *
     * @var    array
     * @access private
     * @see    catchOrder(), getOrder
     */
    var $_overrideOrder = NULL;

    /**
     * Stores the pager objects
     *
     * @var    array
     * @access pivate
     * @see    getList()
     */
    var $_pagers = array();

    /**
     * Flag whether or not to add anchors to the list for linking
     *
     * @var    boolean
     * @access private
     */
    var $_anchor = FALSE;

    /**
     * The name of the list function implemented in PHPWS_Manager's child class.
     *
     * This is used to call the appropriate function when a user chooses to list items
     * or is bounced back to the list after an operation.
     *
     * It is recommended you implement your list function with the name given
     * defaultly to this variable.  Otherwise, you will need to set this via the
     * setListFunction() method.
     *
     * @var    string
     * @access private
     * @see    setListFunction(), managerAction()
     */
    var $_listFunction = '_list';

    /**
     * The name of the view function implemented in PHPWS_Manager's child class.
     *
     * This is used to call the appropriate function when a user chooses to view
     * items from a list.
     *
     * It is recommended you implement your view function with the name given
     * defaultly to this variable.  Otherwise, you will need to set this via the
     * setViewFunction() method.
     *
     * @var    string
     * @access private
     */
    var $_viewFunction = '_view';

    /**
     * The name of the edit function implemented in PHPWS_Manager's child class.
     *
     * This is used to call the appropriate function when a user chooses to edit
     * items from a list.
     *
     * It is recommended you implement your edit function with the name given
     * defaultly to this variable.  Otherwise, you will need to set this via the
     * setEditFunction() method.
     *
     * @var    string
     * @access private
     * @see    setEditFunction(), managerAction()
     */
    var $_editFunction = '_edit';

    /**
     * The name of the delete function implemented in PHPWS_Manager's child class.
     *
     * This is used to call the appropriate delete function when items are selected
     * from a list for deletion.
     *
     * It is recommended you implement your delete function with the name given
     * defaultly to this variable.  Otherwise, you will need to set this via the
     * setDeleteFunction() method.
     *
     * @var    string
     * @access private
     * @see    setDeleteFunction(), managerAction()
     */
    var $_deleteFunction = '_delete';

    /**
     * The name of the group function implemented in PHPWS_Manager's child class.
     *
     * This is used to call the appropriate group function to edit which groups have
     * access to the item.
     *
     * @var    string
     * @access private
     */
    var $_groupFunction = '_group';

    /**
     * The name of the current list being generated for the child
     *
     * Passed to getList() by programmer to reference list defined in manager config file
     *
     * @var    string
     * @access public
     * @see    getList(), getItems()
     */
    var $listName = NULL;

    /**
     * Initializes this manager.
     *
     * @access public
     */
    function init() {

        if(!isset($this->_module)) {
            $message = _('Manager cannot initialize, the module was not set.');
            $error = new PHPWS_Error('core', 'PHPWS_Manager::init()', $message, 'exit', 1);
            $error->message(NULL);
        }

        $config = PHPWS_SOURCE_DIR . 'mod/' . $this->_module . '/conf/manager.php';
        if(!file_exists($config)) {
            $message = sprintf(_('Manager configuration file not found for module: %s'), $this->_module);
            $error = new PHPWS_Error('core', 'PHPWS_Manager::init()', $message, 'exit', 1);
            $error->message(NULL);
        }

        include($config);

        if(!is_array($lists) || !is_array($templates)) {
            $message = _('Manager configuration file is an improper format.');
            $error = new PHPWS_Error('core', 'PHPWS_Manager::init()', $message, 'exit', 1);
            $error->message(NULL);
        }

        if(isset($tables) && is_array($tables)) {
            $this->_tables = $tables;
        }

        $this->_lists = $lists;
        $this->_templates = $templates;

        if(isset($hiddenValues) && is_array($hiddenValues)) {
            $this->_listValues['hidden'] = $hiddenValues;
        }
        if(isset($approvedValues) && is_array($approvedValues)) {
            $this->_listValues['approved'] = $approvedValues;
        }

        foreach($this->_lists as $listName => $listClause) {
            $columns = $listName . 'Columns';
            $actions = $listName . 'Actions';
            $permissions = $listName . 'Permissions';
            $extraLabels = $listName . 'ExtraLabels';
            $paging = $listName . 'Paging';

            if(!is_array($$columns)
               || (isset($$actions) && !is_array($$actions))
               || (isset($$permissions) && !is_array($$permissions))
               || (isset($$extraLabels) && !is_array($$extraLabels))
               || (isset($$paging) && !is_array($$paging))) {

                $message = _('Manager configuration file is an improper format.');
                $error = new PHPWS_Error('core', 'PHPWS_Manager::init()', $message, 'exit', 1);
                $error->message(NULL);
            }

            $this->_listColumns[$listName] = $$columns;

            if(isset($$actions)) {
                $this->_listActions[$listName] = $$actions;
            }
            if(isset($$permissions)) {
                $this->_listPermissions[$listName] = $$permissions;
            }

            if(isset($$extraLabels)) {
                $this->_listExtraLabels = array();
                foreach($$extraLabels as $key=>$value) {
                    $this->_listExtraLabels[strtoupper($key)] = $value;
                }
            }
            if(isset($$paging)) {
                $this->_listPaging[$listName] = $$paging;
            }
        }

    }

    /**
     * Returns a list of items based on the table currently set in this manager
     *
     * @param  string  $listName The name of the list wanting to be returned
     * @param  string  $title    The title of the list
     * @param  boolean $makeForm Flag whether or not to make a form out of the list
     * @access public
     */
    function getList($listName, $title=NULL, $makeForm=TRUE, $overRideOp=NULL) {

        $this->listName = $listName;

        if(!isset($this->_table) && !isset($this->_request)) {
            $message = _('Manager was not fully initialized to get a list.');
            $error = new PHPWS_Error('core', 'PHPWS_Manager::getList()', $message, 'exit', 1);
            $error->message(NULL);
        }

        $theme = Layout::getCurrentTheme();

        $themeModuleRowTpl = "themes/$theme/templates/" . $this->_module . '/' . $this->_templates[$this->listName] . '/row.tpl';
        $moduleRowTpl = PHPWS_SOURCE_DIR . 'mod/' . $this->_module . '/templates/' . $this->_templates[$this->listName] . '/row.tpl';
        $themeCoreRowTpl = 'themes/' . $theme . '/templates/core/defaultRow.tpl';
        $coreRowTpl = PHPWS_SOURCE_DIR . 'templates/defaultRow.tpl';

        $themeModuleListTpl = "themes/$theme/templates/" . $this->_module . '/' . $this->_templates[$this->listName] . '/list.tpl';
        $moduleListTpl = PHPWS_SOURCE_DIR . 'mod/' . $this->_module . '/templates/' . $this->_templates[$this->listName] . '/list.tpl';
        $themeCoreListTpl = "themes/$theme/templates/core/defaultList.tpl";
        $coreListTpl = PHPWS_SOURCE_DIR . 'templates/defaultList.tpl';

        if(file_exists($themeModuleRowTpl)) {
            $rowTpl = $themeModuleRowTpl;
        } else if(file_exists($moduleRowTpl)) {
            $rowTpl = $moduleRowTpl;
        } else if(file_exists($themeCoreRowTpl)) {
            $rowTpl = $themeCoreRowTpl;
        } else {
            $rowTpl = $coreRowTpl;
        }

        if(file_exists($themeModuleListTpl)) {
            $listTpl = $themeModuleListTpl;
        } else if(file_exists($moduleListTpl)) {
            $listTpl = $moduleListTpl;
        } else if(file_exists($themeCoreListTpl)) {
            $listTpl = $themeCoreListTpl;
        } else {
            $listTpl = $coreListTpl;
        }

        if(isset($_REQUEST['PHPWS_MAN_LIST']) && ($this->listName == $_REQUEST['PHPWS_MAN_LIST'])) {
            $this->catchOrder();
        }

        if(isset($overRideOp)) {
            $op = $overRideOp;
        } else {
            if(isset($this->_listPaging[$this->listName]['op'])) {
                $op = $this->_listPaging[$this->listName]['op'];
            }
        }

        if(isset($this->_listPaging[$this->listName]) && is_array($this->_listPaging[$this->listName])) {
            if(!isset($this->_pagers[$this->listName])) {
                $this->_pagers[$this->listName] = new PHPWS_Pager;
                $this->_pagers[$this->listName]->setLinkBack('./index.php?module=' . $this->_module . '&amp;' . $op . '&amp;PHPWS_MAN_PAGE=' . $this->listName);
                $this->_pagers[$this->listName]->setLimits($this->_listPaging[$this->listName]['limits']);
                $this->_pagers[$this->listName]->makeArray(TRUE);

                if($this->_anchor) {
                    $this->_pagers[$this->listName]->setAnchor('#' . $this->listName);
                }

                $this->_pagers[$this->listName]->limit = $this->_listPaging[$this->listName]['limit'];
            }

            $this->_pagers[$this->listName]->setData($this->_getIds());

            if(isset($_REQUEST['PHPWS_MAN_PAGE']) && ($this->listName == $_REQUEST['PHPWS_MAN_PAGE'])) {
                $this->_pagers[$this->listName]->pageData();
            } else {
                $this->_pagers[$this->listName]->pageData(FALSE);
            }

            if(isset($this->_class)) {
                $items = $this->getItems($this->_pagers[$this->listName]->getData(), FALSE, TRUE);
            } else {
                $items = $this->getItems($this->_pagers[$this->listName]->getData());
            }
            $totalItems = count($items);
            //            $totalItems = $this->_pagers[$this->listName]->getNumRows();
        } else {
            if(isset($this->_class)) {
                $items = $this->getItems(NULL, FALSE, TRUE);
            } else {
                $items = $this->getItems();
            }
            $totalItems = sizeof($items);
        }

        /* Begin building main list tags array for processTemplate() */
        $listTags = array();
        if(isset($this->_listExtraLabels) && is_array($this->_listExtraLabels)) {
            $listTags = $this->_listExtraLabels;
        }

        $listTags['TITLE'] = $title;
        $listTags['ANCHOR'] = '<a id="' . $this->listName . '" name="' . $this->listName . '"></a>';

        if($makeForm) {
            $listTags['SELECT_LABEL'] = '&#160;';
        }

        $columns = 0;
        foreach($this->_listColumns[$this->listName] as $listColumn => $listLabel) {
            $column = strtoupper($listColumn);
            $key0 = $column . '_LABEL';
            $key1 = $column . '_ORDER_LINK';

            $listTags[$key0] = NULL;
            $listTags[$key1] = NULL;

            $listTags[$key0] = $listLabel;

            if(isset($overRideOp)) {
                $request = $overRideOp;
            } else if(isset($this->_listPaging[$this->listName]['op'])) {
                $request = $this->_listPaging[$this->listName]['op'];
            } else {
                $request = $this->_request . '=list';
            }

            if($totalItems > 0) {
                $anchor = '';
                if($this->_anchor) {
                    $anchor = '#' . $this->listName;
                }

                if (isset($this->_overrideOrder[$this->listName][$listColumn][0]))
                    $overRide = $this->_overrideOrder[$this->listName][$listColumn][0];
                else
                    $overRide = 'default';

                if(isset($this->_listPaging[$this->listName]))
                    switch($overRide) {
                    case 0:
                        $listTags[$key1] .= '<a href="./index.php?module=' . $this->_module . '&amp;' . $request . '&amp;PHPWS_MAN_LIST=' . $this->listName .
                            '&amp;PHPWS_MAN_COLUMN=' . $listColumn . '&amp;PHPWS_MAN_ORDER=1&amp;' .
                            'PHPWS_MAN_PAGE='. $this->listName . '&amp;' .
                            'PAGER_limit=' . $this->_pagers[$this->listName]->limit . '&amp;' .
                            'PAGER_start=' . $this->_pagers[$this->listName]->start . '&amp;' .
                            'PAGER_section=' . $this->_pagers[$this->listName]->section .
                            $anchor . '">';
                        $listTags[$key1] .= Icon::show('sort') . '</a>';
                        break;

                    case 1:
                        $listTags[$key1] .= '<a href="./index.php?module=' . $this->_module . '&amp;' . $request . '&amp;PHPWS_MAN_LIST=' . $this->listName . '&amp;PHPWS_MAN_COLUMN=' . $listColumn . '&amp;PHPWS_MAN_ORDER=2&amp;' .
                            'PHPWS_MAN_PAGE='. $this->listName . '&amp;' .
                            'PAGER_limit=' . $this->_pagers[$this->listName]->limit . '&amp;' .
                            'PAGER_start=' . $this->_pagers[$this->listName]->start . '&amp;' .
                            'PAGER_section=' . $this->_pagers[$this->listName]->section .
                            $anchor . '">';
                        $listTags[$key1] .= Icon::show('sort-up') . '</a>';
                        break;

                    case 2:
                        $listTags[$key1] .= '<a href="./index.php?module=' . $this->_module . '&amp;' . $request . '&amp;PHPWS_MAN_LIST=' . $this->listName . '&amp;PHPWS_MAN_COLUMN=' . $listColumn . '&amp;PHPWS_MAN_ORDER=0&amp;' .
                            'PHPWS_MAN_PAGE=' . $this->listName . '&amp;' .
                            'PAGER_limit=' . $this->_pagers[$this->listName]->limit . '&amp;' .
                            'PAGER_start=' . $this->_pagers[$this->listName]->start . '&amp;' .
                            'PAGER_section=' . $this->_pagers[$this->listName]->section .
                            $anchor . '">';
                        $listTags[$key1] .= Icon::show('sort-down') . '</a>';
                        break;

                    default:
                        $listTags[$key1] .= '<a href="./index.php?module=' . $this->_module . '&amp;' . $request . '&amp;PHPWS_MAN_LIST=' . $this->listName . '&amp;PHPWS_MAN_COLUMN=' . $listColumn . '&amp;PHPWS_MAN_ORDER=1&amp;' .
                            'PHPWS_MAN_PAGE=' . $this->listName . '&amp;' .
                            'PAGER_limit=' . $this->_pagers[$this->listName]->limit . '&amp;' .
                            'PAGER_start=' . $this->_pagers[$this->listName]->start . '&amp;' .
                            'PAGER_section=' . $this->_pagers[$this->listName]->section .
                            $anchor . '">';
                        $listTags[$key1] .= Icon::show('sort') . '</a>';
                    }
            }

            $columns++;
        }

        /* Build each item's row */
        $listTags['LIST_ITEMS'] = NULL;
        if($totalItems > 0) {
            $tog = 1;
            foreach($items as $item) {
                $object = NULL;
                if(isset($this->_class)) {
                    $object = new $this->_class($item);
                    $className = get_class($object);
                    $classMethods = get_class_methods($className);
                    @array_walk($classMethods, 'manager_lower_methods');

                    $objectVars = get_object_vars($object);

                    if(is_array($objectVars)) {
                        $item = $objectVars;
                        foreach($item as $key => $value) {
                            if($key[0] == '_') {
                                $key = substr($key, 1, strlen($key));
                                $item[$key] = $value;
                            }
                        }
                    }
                }

                if ($tog%2) {
                    $row_class = ' class="bgcolor1"';
                } else {
                    $row_class = null;
                }
                $tog++;
                /* Build row tags array for processTemplate() */
                $rowTags = array();
                if(isset($this->_listExtraLabels) && is_array($this->_listExtraLabels)) {
                    $rowTags = $this->_listExtraLabels;
                }

                $rowTags['ROW_CLASS'] = $row_class;
                if($makeForm) {
                    $ele = & new Form_CheckBox('PHPWS_MAN_ITEMS[]', $item['id']);
                    $rowTags['SELECT'] = $ele->get();
                }

                foreach($this->_listColumns[$this->listName] as $listColumn => $listLabel) {
                    $column = strtoupper($listColumn);
                    if($listColumn == 'created') {
                        /* Set created date using phpwebsite's default date and time formats */
                        $rowTags['CREATED'] = date(PHPWS_DATE_FORMAT . ' ' . PHPWS_TIME_FORMAT, $item['created']);
                    } else if($listColumn == 'updated') {
                        /* Set updated date using phpwebsite's default date and time formats */
                        $rowTags['UPDATED'] = date(PHPWS_DATE_FORMAT . ' ' . PHPWS_TIME_FORMAT, $item['updated']);
                    } else if($listColumn == 'hidden') {
                        /* Setting message depending if this item is hidden or not */
                        if(isset($this->_listValues['hidden'])) {
                            $rowTags['HIDDEN'] = $this->_listValues['hidden'][$item['hidden']];
                        } else {
                            if($item['hidden'] == 1)
                                $rowTags['HIDDEN'] = _('Hidden');
                            else
                                $rowTags['HIDDEN'] = _('Visible');
                        }
                    } else if($listColumn == 'approved') {
                        /* Setting message depending if this item is approved or not */
                        if(isset($this->_listValues['hidden'])) {
                            $rowTags['APPROVED'] = $this->_listValues['approved'][$item['approved']];
                        } else {
                            if($item['approved'] == 1)
                                $rowTags['APPROVED'] = _('Approved');
                            else
                                $rowTags['APPROVED'] = _('Unapproved');
                        }
                    } else if($listColumn == 'groups') {
                        $groups = unserialize($item['groups']);
                        if(is_array($groups) && sizeof($groups) > 0) {
                            /* Set flag to check whether to add a comma or not */
                            $flag = FALSE;
                            /* Create a string of group names the current item belongs to */
                            foreach($groups as $group) {
                                if($flag)
                                    $rowTags['GROUPS'] .= ', ';

                                $rowTags['GROUPS'] .= $group;
                                $flag = TRUE;
                            }
                        } else {
                            $rowTags['GROUPS'] = _('All');
                        }
                    } else {
                        $method = 'get' . $listColumn;

                        if(is_object($object) && in_array($method, $classMethods)) {
                            $rowTags[$column] = $object->$method();
                        } else {
                            $rowTags[$column] = $item[$listColumn];
                        }
                    }
                }

                /* Process this item and concatenate onto the current list of items */
                $listTags['LIST_ITEMS'] .= PHPWS_Template::processTemplate($rowTags, 'core', $rowTpl, FALSE);
            }

            if(isset($this->_listPaging[$this->listName]) && is_array($this->_listPaging[$this->listName]) && (sizeof($this->_listPaging[$this->listName]) > 0)) {
                $listTags['NAV_BACKWARD'] = $this->_pagers[$this->listName]->getBackLink($this->_listPaging[$this->listName]['back']);
                $listTags['NAV_FORWARD'] = $this->_pagers[$this->listName]->getForwardLink($this->_listPaging[$this->listName]['forward']);
                if(isset($this->_listPaging[$this->listName]['section'])) {
                    $listTags['NAV_SECTIONS'] = $this->_pagers[$this->listName]->getSectionLinks();
                }
                $listTags['NAV_LIMITS'] = $this->_pagers[$this->listName]->getLimitLinks();
                $listTags['NAV_INFO'] = $this->_pagers[$this->listName]->getSectionInfo();
            }

            $actions = array();
            if(isset($this->_listActions[$this->listName]) && is_array($this->_listActions[$this->listName])) {
                foreach($this->_listActions[$this->listName] as $actionString => $actionLabel) {
                    if (isset($this->_listPermissions[$this->listName][$actionString]))
                        $permission = $this->_listPermissions[$this->listName][$actionString];

                    if(isset($permission)) {
                        if(Current_User::allow($this->_module, $permission)) {
                            $actions[$actionString] = $actionLabel;
                        }
                    } else {
                        $actions[$actionString] = $actionLabel;
                    }
                }
            }

            if($makeForm) {
                /* Create action select and Go button */
                $ele = & new Form_Select($this->_request, $actions);
                $listTags['ACTION_SELECT'] = $ele->get();
                $listTags['ACTION_BUTTON'] = sprintf('<input type="submit" value="%s" />', _('Go'));
                $listTags['TOGGLE_ALL'] = javascript('check_all', array('FORM_NAME' => 'PHPWS_MAN_LIST_' . $this->listName));

                /* Add hidden variable to designate the current module */
                $ele = & new Form_Hidden('module', $this->_module);
                $elements[0] = $ele->get();
                $elements[0] .= PHPWS_Template::processTemplate($listTags, 'core', $listTpl, FALSE);

                /* Create final form and dump it into a content variable to be returned */
                $content = sprintf('<form name="%s" action="index.php" method="post">%s</form>', 'PHPWS_MAN_LIST_' . $this->listName, implode("\n", $elements));
            } else {
                $content = PHPWS_Template::processTemplate($listTags, 'core', $listTpl, FALSE);
            }

        } else {
            $listTags['LIST_ITEMS'] = '<tr><td colspan="' . $columns . '">' . _('No items for the current list.') . '</td></tr>';
            $content = PHPWS_Template::processTemplate($listTags, 'core', $listTpl, FALSE);
        }

        /* reinitialize sort and order before next list */
        $this->setSort(NULL);
        $this->setOrder(NULL);
        $this->_class = NULL;

        return $content;
    }// END FUNC getList()

    /**
     * Creates a 2 dimensional array of items from the current table.
     *
     * This function creates an sql statement based on variables currently set in
     * this object.  The statement is then executed on the current table and it's
     * result is returned as the list of current items.
     *
     * @param  boolean $filterGroups Flag whether or not to filter items that are not
     *                 associated with a users group
     * @return mixed   A 2-dimentional array of items or FALSE on failure.
     * @access public
     * @see    getList()
     */
    function getItems($ids=NULL, $filterGroups=FALSE, $everything=FALSE) {
        if(isset($this->_table)) {
            $table = $this->_table;
        } else {
            $table = $this->_tables[$this->listName];
        }

        /* Make sure the table name is set before continuing */
        if(isset($table)) {
            if(is_array($this->_listColumns[$this->listName])) {
                if($everything) {
                    $sql = 'SELECT *';
                } else {
                    $sql = 'SELECT id, ';
                    foreach($this->_listColumns[$this->listName] as $listColumn => $listLabel) {
                        if ($listColumn != 'id' ) {
                            $sql .= $listColumn . ', ';
                        }
                    }

                    $sql = substr($sql, 0, strlen($sql) - 2);
                }
                $sql .= ' FROM ' . $table;
            } else {
                $error = new PHPWS_Error('core', 'PHPWS_Manager:getItems()', 'Format error in config file.', 'exit', 1);
                $error->message(NULL);
            }
        } else {
            $error = new PHPWS_Error('core', 'PHPWS_Manager:getItems()', 'Table not set!', 'exit', 1);
            $error->message(NULL);
        }

        $whereFlag = FALSE;
        $sort = $this->getSort();
        if(isset($sort)) {
            $sql .= $sort;
            $whereFlag = TRUE;
        }

        if(is_array($ids) && (sizeof($ids) > 0)) {
            if($whereFlag) {
                $sql .= ' AND (';
            } else {
                $sql .= ' WHERE (';
            }

            foreach($ids as $id) {
                $sql .= " id='$id' OR ";
            }
            $sql = substr($sql, 0, strlen($sql)-4) . ')';
        }

        $order = $this->getOrder();
        if(isset($order)) {
            $sql .= $order;
        }

        /* Set associative mode for db and execute query */
        $result = PHPWS_DB::getAll($sql);

        if($filterGroups) {
            $size = sizeof($result);
            for($i = 0; $i < $size; $i++) {
                $groups = unserialize($result[$i]['groups']);
                if(is_array($groups)) {
                    foreach($groups as $value) {
                        if(!$_SESSION['OBJ_user']->userInGroup($value)) {
                            unset($result[$i]);
                        }
                    }
                }
            }

            $result = PHPWS_Array::reIndex($result);
        }

        /* Return result of query */
        return $result;
    }// END FUNC getItems()

    function _getIds() {
        if(isset($this->_table)) {
            $table = $this->_table;
        } else {
            $table = $this->_tables[$this->listName];
        }

        $sql = 'SELECT id FROM ' . $table;

        $sort = $this->getSort();
        if(isset($sort)) {
            $sql .= $sort;
        }

        $order = $this->getOrder();
        if(isset($order)) {
            $sql .= $order;
        }

        return PHPWS_DB::getCol($sql);
    }

    /**
     * Updates simple attributes for multiple items at once.
     *
     * This function is called when multiple items are requested to be hidden, approved, or
     * visable.  It simply creates an sql statement based on the type of request on the item
     * ids contained in the $_REQUEST['PHPWS_MAN_ITEMS'] array and executes it on the database.
     * Note: should only be called by managerAction()
     *
     * @param  string  $column The name of the column to update.
     * @param  mixed   $value  The value to set the column to.
     * @return boolean TRUE on success and FALSE on failure.
     * @access private
     */
    function _doMassUpdate($column, $value) {
        if(is_array($_REQUEST['PHPWS_MAN_ITEMS']) && sizeof($_REQUEST['PHPWS_MAN_ITEMS']) > 0) {
            if(isset($this->_table)) {
                $table = $this->_table;
            } else {
                $table = $this->_tables[$this->listName];
            }

            /* Begin sql update statement */
            $sql = 'UPDATE ' . $table .
                " SET $column='$value' WHERE id='";

            /* Set flag to know when to add sql for checking against extra ids */
            $flag = FALSE;
            foreach($_REQUEST['PHPWS_MAN_ITEMS'] as $itemId) {
                if($flag)
                    $sql .= " OR id='";

                $sql .= $itemId . "'";
                $flag = TRUE;
            }

            /* Execute query and test for failure */
            $result = PHPWS_DB::query($sql);
            if($result)
                return TRUE;
            else
                return FALSE;
        }
    }// END FUNC _doMassUpdate()

    /**
     * Sets the name of the current module extending manger.
     *
     * @param  string  $module The name of the module.
     * @return boolean TRUE on success and FALSE on failure.
     * @access public
     */
    function setModule($module) {
        /* Make sure module is a string and set it */
        if($module && is_string($module)) {
            $this->_module = $module;
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Sets the name of the table in the database to pull items from.
     *
     * @param  string  $table The name of the table to pull items from.
     * @return boolean TRUE on success and FALSE on failure.
     * @access public
     */
    function setTable($table) {
        /* Make sure table is a string and set it */
        if($table && is_string($table)) {
            $this->_table = $table;
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function getTable() {
        return $this->_table;
    }

    /**
     * Sets the name of the request variable to use to pass the action.
     *
     * @param  string  $request The name of the request.
     * @return boolean TRUE on success and FALSE on failure.
     * @access public
     */
    function setRequest($request) {
        /* Make sure request is a string and set it */
        if($request && is_string($request)) {
            $this->_request = $request;
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Set the name of the class to instantiate
     *
     * @param  string  $class The name of the class
     * @return boolean TRUE on success and FALSE on failure
     * @access public
     */
    function setClass($class) {
        /* make sure that the class exists */
        if(class_exists($class)) {
            $this->_class = $class;
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Sets the sort clause for this manager for listing.
     *
     * @param  string $sort The sql to add to a select for sorting lists.
     * @access public
     */
    function setSort($sort) {
        $this->_sort = $sort;
    }

    /**
     * Sets the ORDER BY clause for this manager for listing.
     *
     * @param  string $order The sql to add to a select for ordering lists.
     * @access public
     */
    function setOrder($order) {
        $this->_order = $order;
    }

    /**
     * Sets the name of the owner to limit item lists by.
     *
     * @param  string  $owner The username of the owner to limit the lists by.
     * @return boolean TRUE on success and FALSE on failure.
     * @access public
     */
    function setOwner($owner) {
        /* Make sure owner is a string and set it */
        if($owner && is_string($owner)) {
            $this->_owner = $owner;
            return TRUE;
        } else
            return FALSE;
    }

    /**
     * Sets the list function for the module extending this manager.
     *
     * This function is used to handle flow of control between this object and the
     * object which inherits it.
     *
     * @param  string  $name The name of the function to use (i.e.: _list)
     * @return boolean TRUE on success and FALSE on failure.
     * @access public
     */
    function setListFunction($name) {
        if(is_string($name)) {
            $this->_listFunction = $name;
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Sets the view function for the module extending this manager.
     *
     * This function is used to handle flow of control between this object and the
     * object which inherits it.
     *
     * @param  string  $name The name of the function to use (i.e.: _view)
     * @return boolean TRUE on success and FALSE on failure.
     * @access public
     */
    function setViewFunction($name) {
        if(is_string($name)) {
            $this->_viewFunction = $name;
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Sets the edit function for the module extending this manager.
     *
     * This function is used to handle flow of control between this object and the
     * object which inherits it.
     *
     * @param  string  $name The name of the function to use (i.e.: _edit)
     * @return boolean TRUE on success and FALSE on failure.
     * @access public
     */
    function setEditFunction($name) {
        if(is_string($name)) {
            $this->_editFunction = $name;
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Sets the delete function for the module extending this manager.
     *
     * This function is used to handle flow of control between this object and the
     * object which inherits it.
     *
     * @param  string  $name The name of the function to use (i.e.: _delete)
     * @return boolean TRUE on success and FALSE on failure.
     * @access public
     */
    function setDeleteFunction($name) {
        if(is_string($name)) {
            $this->_deleteFunction = $name;
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Catches an order changed passed by a list, this order will override
     * any order set by the pregrammer
     *
     * @access public
     * @see    getList()
     */
    function catchOrder() {
        unset($this->_overrideOrder[$_REQUEST['PHPWS_MAN_LIST']]);
        $this->_overrideOrder[$_REQUEST['PHPWS_MAN_LIST']][$_REQUEST['PHPWS_MAN_COLUMN']][0] = $_REQUEST['PHPWS_MAN_ORDER'];
        switch($this->_overrideOrder[$_REQUEST['PHPWS_MAN_LIST']][$_REQUEST['PHPWS_MAN_COLUMN']][0]) {
        case 0:
            $this->_overrideOrder[$_REQUEST['PHPWS_MAN_LIST']][$_REQUEST['PHPWS_MAN_COLUMN']][1] = NULL;
            break;

        case 1:
            $this->_overrideOrder[$_REQUEST['PHPWS_MAN_LIST']][$_REQUEST['PHPWS_MAN_COLUMN']][1] = $_REQUEST['PHPWS_MAN_COLUMN'] . ' DESC';
            break;

        case 2:
            $this->_overrideOrder[$_REQUEST['PHPWS_MAN_LIST']][$_REQUEST['PHPWS_MAN_COLUMN']][1] = $_REQUEST['PHPWS_MAN_COLUMN'] . ' ASC';
            break;
        }
        return TRUE;
    }

    function getSort() {
        if(isset($this->_lists[$this->listName])) {
            $sql = ' WHERE (' . $this->_lists[$this->listName] . ')';

            if(isset($this->_sort)) {
                $sql .= ' AND (' . $this->_sort . ')';
            }

            return $sql;
        } else if(isset($this->_sort)) {
            return ' WHERE (' . $this->_sort . ')';
        } else {
            return NULL;
        }
    }

    function getOrder() {
        foreach($this->_listColumns[$this->listName] as $listColumn => $listLabel) {
            if(isset($this->_overrideOrder[$this->listName][$listColumn][1])) {
                $order = $this->_overrideOrder[$this->listName][$listColumn][1];
                break;
            }
        }

        if(isset($order)) {
            return ' ORDER BY ' . $order;
        } else if(isset($this->_order)) {
            return ' ORDER BY ' . $this->_order;
        } else {
            return NULL;
        }
    }

    function anchorOn() {
        $this->_anchor = TRUE;
    }

    function anchorOff() {
        $this->_anchor = FALSE;
    }

    /**
     * This function executes commands for the PHPWS_Manager
     *
     * Executes various commands for the manager like delete, hide, and show.
     * Commands like list, edit, and view must be set by the programmer before
     * the init call.
     *
     * @return mixed  Returns any output recieved during execution of an action.
     * @access public
     */
    function managerAction() {
        switch($_REQUEST[$this->_request]) {
        case 'list':
            $list = $this->_listFunction;
            $this->$list();
            break;

        case 'edit':
            if(isset($_REQUEST['PHPWS_MAN_ITEMS']) &&
               is_array($_REQUEST['PHPWS_MAN_ITEMS']) &&
               sizeof($_REQUEST['PHPWS_MAN_ITEMS']) > 0) {
                $edit = $this->_editFunction;
                $this->$edit($_REQUEST['PHPWS_MAN_ITEMS']);
            } else {
                $list = $this->_listFunction;
                $this->$list();
            }
            break;

        case 'view':
            if(isset($_REQUEST['PHPWS_MAN_ITEMS']) &&
               is_array($_REQUEST['PHPWS_MAN_ITEMS']) &&
               sizeof($_REQUEST['PHPWS_MAN_ITEMS']) > 0) {
                $view = $this->_viewFunction;
                $this->$view($_REQUEST['PHPWS_MAN_ITEMS']);
            } else {
                $list = $this->_listFunction;
                $this->$list();
            }
            break;

        case 'hide':
            $this->_doMassUpdate('hidden', 1);
            $list = $this->_listFunction;
            $this->$list();
            break;

        case 'show':
            $this->_doMassUpdate('hidden', 0);
            $list = $this->_listFunction;
            $this->$list();
            break;

        case 'approve':
            $this->_doMassUpdate('approved', 1);
            $list = $this->_listFunction;
            $this->$list();
            break;

        case 'delete':
            if(is_array($_REQUEST['PHPWS_MAN_ITEMS']) && sizeof($_REQUEST['PHPWS_MAN_ITEMS']) > 0) {
                $delete = $this->_deleteFunction;
                $this->$delete($_REQUEST['PHPWS_MAN_ITEMS']);
            } else {
                $list = $this->_listFunction;
                $this->$list();
            }
            break;
        }
    }// END FUNC managerAction()

}// END CLASS PHPWS_Manager

function manager_lower_methods(&$item, $key)
{
    $item = strtolower($item);
}

?>